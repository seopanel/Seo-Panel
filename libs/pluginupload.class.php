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

include_once(SP_LIBPATH.'/zipuploadinstaller.class.php');

# installs or upgrades a plugin from an admin-uploaded zip file - see
# ZipUploadInstaller for the shared staging/validation/install logic this
# only configures.
class PluginUploadInstaller extends ZipUploadInstaller {

    function __getDestBasePath() {
        return SP_PLUGINPATH;
    }

    function __getArtifactLabel() {
        return 'plugin';
    }

    function __getStagingPrefix() {
        return 'sp_plugin_upload';
    }

    function __getExampleName() {
        return 'QuickWebProxy';
    }

    function __getInfoFileName() {
        return SP_PLUGININFOFILE;
    }

    # sanity-check this actually looks like a Seo Panel plugin, not an
    # arbitrary zip - same two files manageSeoPlugins() itself requires to
    # load a plugin (parsePluginInfoFile() for plugin.xml, then
    # include_once("$pluginDirName.ctrl.php") - see
    # controllers/seoplugins.ctrl.php)
    function __validateStructure($sourceRoot, $dirName) {
        if (!file_exists($sourceRoot.'/'.SP_PLUGININFOFILE)) {
            throw new Exception('The uploaded zip does not look like a valid Seo Panel plugin (missing '.SP_PLUGININFOFILE.').');
        }
        if (!file_exists($sourceRoot.'/'.$dirName.'.ctrl.php')) {
            throw new Exception('The uploaded zip does not look like a valid Seo Panel plugin (missing '.$dirName.'.ctrl.php).');
        }
    }
}
?>
