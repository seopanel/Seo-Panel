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

# installs or upgrades a theme from an admin-uploaded zip file - see
# ZipUploadInstaller for the shared staging/validation/install logic this
# only configures.
class ThemeUploadInstaller extends ZipUploadInstaller {

    function __getDestBasePath() {
        return SP_THEMEPATH;
    }

    function __getArtifactLabel() {
        return 'theme';
    }

    function __getStagingPrefix() {
        return 'sp_theme_upload';
    }

    function __getExampleName() {
        return 'classic';
    }

    function __getInfoFileName() {
        return SP_THEMEINFOFILE;
    }

    # sanity-check this actually looks like a Seo Panel theme, not an
    # arbitrary zip - theme.xml is what ThemesController::
    # parseThemeInfoFile() requires, and every real theme's views live
    # directly under its own views/ directory (see
    # SeoPluginsController::getPluginViewPath() and sp-load.php's own
    # SP_VIEWPATH resolution for the active theme)
    function __validateStructure($sourceRoot, $dirName) {
        if (!file_exists($sourceRoot.'/'.SP_THEMEINFOFILE)) {
            throw new Exception('The uploaded zip does not look like a valid Seo Panel theme (missing '.SP_THEMEINFOFILE.').');
        }
        if (!is_dir($sourceRoot.'/views')) {
            throw new Exception('The uploaded zip does not look like a valid Seo Panel theme (missing a views directory).');
        }
    }
}
?>
