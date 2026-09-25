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

# downloads the latest Seo Panel release and overlays its files onto the
# current installation. Never touches the database - the admin always
# finishes via the existing install/upgrade.php wizard for the schema
# migration step. Stateless by design (no session/$_POST dependency) so it
# can be unit-tested directly.
class OnlineUpgrade {

    var $backupDir;

    # entry point - runs the full download -> extract -> overlay flow
    function run() {
        try {
            $this->__cleanupOldBackups();
            $zipPath = $this->__download();
            $stagingDir = $this->__validateAndExtract($zipPath);
            $sourceRoot = $this->__resolveSourceRoot($stagingDir);
            $this->__overlay($sourceRoot);
            $this->__cleanup($zipPath, $stagingDir);
        } catch (Exception $e) {
            return [false, $e->getMessage()];
        }

        return [true, ''];
    }

    # remove backup directories left behind by previous online-upgrade runs,
    # so only the most recent attempt's backup is ever kept around
    function __cleanupOldBackups() {
        foreach (glob(SP_TMPPATH."/sp_online_upgrade_backup_*", GLOB_ONLYDIR) ?: [] as $oldBackupDir) {
            $this->__rrmdir($oldBackupDir);
        }

        // leftover downloads/staging dirs from a crashed prior run
        foreach (glob(SP_TMPPATH."/sp_online_upgrade_*.zip") ?: [] as $oldZip) {
            @unlink($oldZip);
        }
        foreach (glob(SP_TMPPATH."/sp_online_upgrade_staging_*", GLOB_ONLYDIR) ?: [] as $oldStaging) {
            $this->__rrmdir($oldStaging);
        }
    }

    # download the release zip. SSL verification is intentionally left ON
    # here (unlike the SP API calls elsewhere in this app, which disable it)
    # because this response is executable code that gets written into the
    # application's own directory tree.
    function __download() {
        if (!function_exists('curl_init')) {
            throw new Exception('The curl PHP extension is required for online upgrade.');
        }

        $url = defined('SP_ONLINE_UPGRADE_URL') ? SP_ONLINE_UPGRADE_URL : 'https://www.seopanel.org/spdownload/';
        $zipPath = SP_TMPPATH."/sp_online_upgrade_".time().".zip";

        $fp = fopen($zipPath, 'w');
        if (!$fp) {
            throw new Exception('Could not create a temporary file for the download.');
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($httpCode != 200 || !empty($curlErr)) {
            @unlink($zipPath);
            throw new Exception('Could not download the update package (HTTP '.$httpCode.'). '.$curlErr);
        }

        $fileSize = filesize($zipPath);
        if ($fileSize < 102400 || $fileSize > 209715200) {
            @unlink($zipPath);
            throw new Exception('The downloaded update package looks invalid (unexpected file size).');
        }

        return $zipPath;
    }

    # validate the zip is well-formed and free of path-traversal entries,
    # then extract it to a staging directory - never directly into the live
    # application tree
    function __validateAndExtract($zipPath) {
        if (!class_exists('ZipArchive')) {
            @unlink($zipPath);
            throw new Exception('The ZipArchive PHP extension is required for online upgrade.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            @unlink($zipPath);
            throw new Exception('The downloaded update package is corrupt or not a valid zip file.');
        }

        $stagingDir = SP_TMPPATH."/sp_online_upgrade_staging_".time();
        $realStagingParent = realpath(SP_TMPPATH);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->statIndex($i)['name'];
            if (strpos($entryName, '..') !== false || strpos($entryName, "\0") !== false) {
                $zip->close();
                @unlink($zipPath);
                throw new Exception('The downloaded update package contains an unsafe file path.');
            }

            $resolved = $realStagingParent.'/'.basename($stagingDir).'/'.$entryName;
            $normalized = str_replace('\\', '/', $resolved);
            if (strpos($normalized, $realStagingParent.'/'.basename($stagingDir).'/') !== 0) {
                $zip->close();
                @unlink($zipPath);
                throw new Exception('The downloaded update package contains an unsafe file path.');
            }
        }

        if (!$zip->extractTo($stagingDir)) {
            $zip->close();
            @unlink($zipPath);
            throw new Exception('Could not extract the update package.');
        }

        $zip->close();

        return $stagingDir;
    }

    # the release zip always wraps its contents in a single top-level
    # "seopanel" folder (see build/build-release.sh) - resolve that as the
    # real source root and sanity-check it looks like a genuine release
    # before trusting it
    function __resolveSourceRoot($stagingDir) {
        $entries = array_values(array_diff(scandir($stagingDir) ?: [], ['.', '..']));
        $sourceRoot = null;

        if (count($entries) === 1 && is_dir($stagingDir.'/'.$entries[0])) {
            $sourceRoot = $stagingDir.'/'.$entries[0];
        } else {
            $sourceRoot = $stagingDir;
        }

        if (!file_exists($sourceRoot.'/includes/sp-load.php') || !file_exists($sourceRoot.'/install/install.class.php')) {
            throw new Exception('The downloaded update package does not look like a valid Seo Panel release.');
        }

        return $sourceRoot;
    }

    # recursively copy the new release's files onto the live application
    # root. This only ever creates/overwrites files present in the release -
    # it never deletes anything absent from it, so commercial plugins, the
    # business theme, and config/sp-config.php (never shipped in the public
    # release zip to begin with) all survive automatically.
    function __overlay($sourceRoot) {
        $this->backupDir = SP_TMPPATH."/sp_online_upgrade_backup_".date('Ymd_His');
        $realAppRoot = realpath(SP_ABSPATH);

        $this->__copyRecursive($sourceRoot, SP_ABSPATH, $realAppRoot);
    }

    function __copyRecursive($source, $destination, $realAppRoot) {
        $entries = array_diff(scandir($source) ?: [], ['.', '..']);

        foreach ($entries as $entry) {
            $sourcePath = $source.'/'.$entry;
            $destPath = $destination.'/'.$entry;
            $relativePath = ltrim(str_replace($realAppRoot, '', realpath($destination) ?: $destination).'/'.$entry, '/');

            // never touch the live config - it isn't in the release zip
            // anyway, this is defense in depth
            if ($relativePath === 'config/sp-config.php') {
                continue;
            }

            $normalizedDest = str_replace('\\', '/', $destPath);
            if (strpos($normalizedDest, $realAppRoot) !== 0) {
                throw new Exception('Refusing to write outside the application directory: '.$relativePath);
            }

            if (is_dir($sourcePath)) {
                if (!is_dir($destPath)) {
                    if (!mkdir($destPath, 0755, true)) {
                        throw new Exception('Could not create directory: '.$relativePath);
                    }
                }
                $this->__copyRecursive($sourcePath, $destPath, $realAppRoot);
            } else {
                if (file_exists($destPath)) {
                    $this->__backupFile($destPath, $relativePath);
                }
                if (!copy($sourcePath, $destPath)) {
                    throw new Exception('Could not write file: '.$relativePath);
                }
            }
        }
    }

    function __backupFile($destPath, $relativePath) {
        $backupPath = $this->backupDir.'/'.$relativePath;
        $backupParent = dirname($backupPath);
        if (!is_dir($backupParent)) {
            mkdir($backupParent, 0755, true);
        }
        copy($destPath, $backupPath);
    }

    # remove the downloaded zip and staging directory. The backup directory
    # is intentionally left in place - that's its whole purpose - until the
    # next online-upgrade run's __cleanupOldBackups() removes it.
    function __cleanup($zipPath, $stagingDir) {
        @unlink($zipPath);
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
}
?>
