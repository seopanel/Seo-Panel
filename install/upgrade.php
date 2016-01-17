<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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
include_once 'install.class.php';
include_once 'db.class.php';

session_start();
@ini_set("display_erros", "Off");
@ini_set("display_startup_errors", "Off");
error_reporting(0);

$install = new Install();

# installation configiration
define("SP_UPGRADE_VERSION", '2.0.0');
define("SP_INSTALL_DIR", getcwd());
define("SP_CONFIG_FILE", "config/sp-config.php");
define("SP_UPGRADE_DB_FILE", SP_INSTALL_DIR."/data/upgrade.sql");
define("SP_UPGRADE_DB_LANG_FILE", SP_INSTALL_DIR."/data/textlang.sql");
define("SP_INSTALL_CONFIG_FILE", SP_INSTALL_DIR."/../".SP_CONFIG_FILE);

$install->showDefaultHeader();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        case "proceedupgrade":
            $install->proceedUpgrade($_POST);
            break;
    }
} else {
    switch ($_GET['sec']) {

        default:
            $install->checkUpgradeRequirements();
            break;
    }
}
$install->showDefaultFooter();
