<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
 *   sendtogeo@gmail.com   												   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

# shared base for PluginUploadInstaller and ThemeUploadInstaller
# (libs/pluginupload.class.php, libs/themeupload.class.php) - installs or
# upgrades a plugin/theme from an admin-uploaded zip file. Mirrors
# OnlineUpgrade's (libs/onlineupgrade.class.php) staging/validation
# approach - never extracts straight into the live plugins/ or themes/
# tree. The zip-slip check in particular is deliberately NOT duplicated
# per-subclass: it's identical in both cases, and two independent copies
# of the same security check is how one of them quietly goes stale while
# the other gets fixed - exactly the class of drift this project's own
# review process keeps finding elsewhere in older code, not something to
# introduce fresh here. Stateless by design (no session/$_POST dependency)
# so it can be unit-tested directly; the calling controller is the only
# thing that must be admin-gated - installing a plugin or theme means
# running arbitrary PHP (plugins) or at minimum untrusted view/JS (themes)
# with the app's own privileges, same trust level as the manual-FTP
# install this is a convenience wrapper around, not a new one.
#
# Subclasses must implement: __getDestBasePath(), __getArtifactLabel(),
# __getStagingPrefix(), __getExampleName(), __validateStructure().
abstract class ZipUploadInstaller {

    var $backupPath;

    # the uploaded artifact's own version/name, parsed from its info file
    # as soon as __resolveSourceRoot() confirms it exists - i.e. BEFORE
    # the install step that might still fail (e.g. a permissions error).
    # Lets the calling controller report "attempted to upgrade from X to
    # Y, but..." accurately even when run() ultimately returns failure,
    # not just on success.
    var $uploadedVersion;
    var $uploadedLabel;

    # entry point - runs the full validate -> extract -> install flow.
    # Returns [success, message, dirName]
    function run($uploadedTmpPath, $originalFileName) {
        $dirName = null;

        try {
            $this->__preflight();
            $this->__cleanupOldArtifacts();
            $this->__validateUploadedFile($uploadedTmpPath, $originalFileName);
            $stagingDir = $this->__validateAndExtract($uploadedTmpPath);
            list($sourceRoot, $dirName) = $this->__resolveSourceRoot($stagingDir);
            $this->__install($dirName, $sourceRoot);
            $this->__cleanup($stagingDir);
        } catch (Exception $e) {
            return [false, $e->getMessage(), $dirName];
        }

        return [true, '', $dirName];
    }

    # fail fast with a clear message rather than deep inside __install() -
    # same reasoning as OnlineUpgrade's own __onlineUpgradePreflight()
    function __preflight() {
        $failed = [];
        if (!class_exists('ZipArchive')) {
            $failed[] = 'ZipArchive extension';
        }
        if (!is_writable(SP_TMPPATH)) {
            $failed[] = 'tmp directory ('.SP_TMPPATH.')';
        }
        if (!is_writable($this->__getDestBasePath())) {
            $failed[] = $this->__getArtifactLabel().'s directory ('.$this->__getDestBasePath().')';
        }
        if (!empty($failed)) {
            throw new Exception('Cannot upload a '.$this->__getArtifactLabel().' - missing requirements: '.implode(', ', $failed).'.');
        }
    }

    # remove staging/backup directories left behind by a previous run, so
    # only the most recent attempt's backup is ever kept around (same
    # retention policy as OnlineUpgrade's own cleanup). Only removes ones
    # older than 5 minutes - two admins (or two tabs) uploading within
    # the same short window would otherwise race: the second upload's own
    # cleanup pass could delete the FIRST upload's still-in-progress
    # staging directory out from under it, since both share the same glob
    # pattern. A real previous run's leftovers are always much older than
    # this by the time the next upload happens.
    function __cleanupOldArtifacts() {
        $prefix = $this->__getStagingPrefix();
        $cutoff = time() - 300;
        foreach (glob(SP_TMPPATH."/{$prefix}_staging_*", GLOB_ONLYDIR) ?: [] as $old) {
            if ((@filemtime($old) ?: 0) < $cutoff) {
                $this->__rrmdir($old);
            }
        }
        foreach (glob(SP_TMPPATH."/{$prefix}_backup_*", GLOB_ONLYDIR) ?: [] as $old) {
            if ((@filemtime($old) ?: 0) < $cutoff) {
                $this->__rrmdir($old);
            }
        }
    }

    function __validateUploadedFile($tmpPath, $originalName) {
        if (empty($tmpPath) || !is_uploaded_file($tmpPath)) {
            throw new Exception('No valid file was uploaded.');
        }
        if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'zip') {
            throw new Exception('Please upload a .zip file.');
        }

        $fileSize = filesize($tmpPath);
        if ($fileSize <= 0 || $fileSize > 52428800) {
            throw new Exception('The uploaded file looks invalid or is too large (50MB max).');
        }
    }

    # validate the zip is well-formed and free of path-traversal entries,
    # then extract it to a staging directory - never directly into the
    # live plugins/ or themes/ tree. Identical zip-slip check to
    # OnlineUpgrade's own __validateAndExtract() - keep all three in sync
    # if this logic ever needs to change.
    function __validateAndExtract($zipPath) {
        if (!class_exists('ZipArchive')) {
            throw new Exception('The ZipArchive PHP extension is required for this upload.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception('The uploaded file is corrupt or not a valid zip file.');
        }

        $stagingDir = SP_TMPPATH."/".$this->__getStagingPrefix()."_staging_".time()."_".mt_rand(1000, 9999);
        $realStagingParent = realpath(SP_TMPPATH);

        // the upload size check in __validateUploadedFile() only looks at
        // the COMPRESSED file on disk - a small zip can still expand to
        // an enormous amount of data (a "zip bomb") via a highly
        // compressible payload, exhausting disk space on extraction. Sum
        // each entry's own uncompressed size up front, before extracting
        // any of it, and cap the entry count too (a zip with a huge
        // number of tiny/empty entries is its own, cheaper way to burn
        // resources via sheer filesystem-entry creation, independent of
        // total byte size).
        if ($zip->numFiles > 5000) {
            $zip->close();
            throw new Exception('The uploaded zip has too many files (max 5000).');
        }

        $totalUncompressedSize = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->statIndex($i)['name'];
            if (strpos($entryName, '..') !== false || strpos($entryName, "\0") !== false) {
                $zip->close();
                throw new Exception('The uploaded zip contains an unsafe file path.');
            }

            $resolved = $realStagingParent.'/'.basename($stagingDir).'/'.$entryName;
            $normalized = str_replace('\\', '/', $resolved);
            if (strpos($normalized, $realStagingParent.'/'.basename($stagingDir).'/') !== 0) {
                $zip->close();
                throw new Exception('The uploaded zip contains an unsafe file path.');
            }

            $totalUncompressedSize += $zip->statIndex($i)['size'];
            if ($totalUncompressedSize > 262144000) {
                $zip->close();
                throw new Exception('The uploaded zip is too large once uncompressed (250MB max).');
            }
        }

        if (!$zip->extractTo($stagingDir)) {
            $zip->close();
            throw new Exception('Could not extract the uploaded zip.');
        }

        $zip->close();

        return $stagingDir;
    }

    # Plugin/theme zips are expected to wrap their contents in a single
    # top-level folder named after the plugin/theme (e.g. QuickWebProxy/
    # QuickWebProxy.ctrl.php, or classic/theme.xml) - the same convention
    # this project's own release zips use (git archive --prefix=<Name>/).
    # That folder name becomes a literal filesystem path component (and,
    # for plugins specifically, a PHP class name too - see
    # PluginUploadInstaller::__validateStructure()), so it's restricted to
    # safe characters here regardless of which subclass is running.
    function __resolveSourceRoot($stagingDir) {
        $entries = array_values(array_diff(scandir($stagingDir) ?: [], ['.', '..']));

        if (count($entries) !== 1 || !is_dir($stagingDir.'/'.$entries[0])) {
            throw new Exception('The uploaded zip must contain exactly one top-level folder, named after the '.$this->__getArtifactLabel().' (e.g. '.$this->__getExampleName().'/...).');
        }

        $dirName = $entries[0];

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $dirName)) {
            throw new Exception('The '.$this->__getArtifactLabel().' folder name contains invalid characters - only letters, numbers, and underscores are allowed.');
        }

        $sourceRoot = $stagingDir.'/'.$dirName;

        // subclass hook - throws its own Exception on failure
        $this->__validateStructure($sourceRoot, $dirName);

        // capture what we're ABOUT to install, before the (possibly
        // failing) __install() step - see $uploadedVersion's own comment
        $parsedInfo = $this->__parseInfoFile($sourceRoot.'/'.$this->__getInfoFileName());
        $this->uploadedVersion = !empty($parsedInfo['version']) ? $parsedInfo['version'] : '1.0.0';
        $this->uploadedLabel = !empty($parsedInfo['label']) ? $parsedInfo['label'] : (!empty($parsedInfo['name']) ? $parsedInfo['name'] : $dirName);

        return [$sourceRoot, $dirName];
    }

    # mirrors SeoPluginsController::parsePluginInfoFile()/
    # ThemesController::parseThemeInfoFile() - both parse the exact same
    # flat XML shape (<plugin>/<theme> with <version>/<label-or-name>/...
    # child tags), just from the live install path instead of a staging
    # one. Deliberately read-only/best-effort here: __validateStructure()
    # already confirmed the file exists, so a parse failure at this point
    # (malformed XML) just means $uploadedVersion/$uploadedLabel stay
    # empty - not a reason to fail the whole upload, since the real
    # parsePluginInfoFile()/parseThemeInfoFile() call that runs after a
    # successful install is what the DB row actually ends up trusting.
    function __parseInfoFile($infoFilePath) {
        $info = [];
        if (!file_exists($infoFilePath)) {
            return $info;
        }

        try {
            $xml = new XML_Parser;
            $parsed = $xml->parse($infoFilePath);
            if (!empty($parsed[0]['child'])) {
                foreach ($parsed[0]['child'] as $child) {
                    $info[strtolower($child['name'])] = $child['content'];
                }
            }
        } catch (Exception $e) {
            // best-effort, see function comment
        }

        return $info;
    }

    # back up any existing plugin/theme directory of the same name (so a
    # bad upload can be rolled back by hand), then copy the new one into
    # place. Uses a recursive copy rather than rename() for the final
    # move, since SP_TMPPATH and the destination aren't guaranteed to be
    # on the same filesystem/mount in every deployment (rename() across
    # devices fails) - same reasoning OnlineUpgrade's own __overlay() uses
    # copy() rather than rename() for its file-level writes.
    function __install($dirName, $sourceRoot) {
        $destPath = $this->__getDestBasePath().'/'.$dirName;

        if (is_dir($destPath)) {
            $this->backupPath = SP_TMPPATH."/".$this->__getStagingPrefix()."_backup_".$dirName."_".date('Ymd_His');
            if (!rename($destPath, $this->backupPath)) {
                throw new Exception('Could not back up the existing '.$this->__getArtifactLabel().' directory - check permissions on '.$this->__getDestBasePath().'.');
            }
        }

        try {
            $this->__copyDirRecursive($sourceRoot, $destPath);
        } catch (Exception $e) {
            // roll back: remove whatever partial copy landed, restore the backup
            $this->__rrmdir($destPath);
            if (!empty($this->backupPath) && is_dir($this->backupPath)) {
                @rename($this->backupPath, $destPath);
            }
            throw $e;
        }
    }

    function __copyDirRecursive($source, $dest) {
        if (!is_dir($dest) && !mkdir($dest, 0755, true)) {
            throw new Exception('Could not create directory: '.$dest);
        }

        foreach (array_diff(scandir($source) ?: [], ['.', '..']) as $entry) {
            $s = $source.'/'.$entry;
            $d = $dest.'/'.$entry;
            if (is_dir($s)) {
                $this->__copyDirRecursive($s, $d);
            } else {
                if (!copy($s, $d)) {
                    throw new Exception('Could not write file: '.$d);
                }
            }
        }
    }

    # remove the staging directory. The backup directory (if one was
    # made) is intentionally left in place until the NEXT upload's
    # __cleanupOldArtifacts() removes it - same "keep exactly one"
    # retention OnlineUpgrade uses.
    function __cleanup($stagingDir) {
        $this->__rrmdir($stagingDir);
    }

    function __rrmdir($dir) {
        if (!is_dir($dir)) {
            return;
        }

        foreach (array_diff(scandir($dir) ?: [], ['.', '..']) as $entry) {
            $path = $dir.'/'.$entry;
            if (is_dir($path)) {
                $this->__rrmdir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    # --- override points ---

    abstract function __getDestBasePath();
    abstract function __getArtifactLabel();
    abstract function __getStagingPrefix();
    abstract function __getExampleName();
    abstract function __getInfoFileName();
    abstract function __validateStructure($sourceRoot, $dirName);
}
?>
