<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   *
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

include_once("includes/sp-load.php");
checkAdminLoggedIn();
include_once(SP_CTRLPATH."/seoplugins.ctrl.php");
$controller = new SeoPluginsController();
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextPlugin = $controller->getLanguageTexts('plugin', $_SESSION['lang_code']);
$controller->set('spTextPlugin', $controller->spTextPlugin);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        case "update":
            $controller->updateSeoPlugin($_POST);
            break;
    }
} else {
    switch ($_GET['sec']) {
        
        case "changestatus":
            $status = empty($_GET['status']) ? 1 : 0;
            $controller->changeStatus($_GET['seoplugin_id'], $status);
            $controller->listSeoPlugins();
            break;
            
        case "edit":
            $controller->editSeoPlugin($_GET);
            break;
            
        case "listinfo":
            $controller->listPluginInfo($_GET['pid']);
            break;
            
        case "upgrade":
            $controller->upgradeSeoPlugin($_GET['pid']);
            break;
            
        case "reinstall":
            $controller->reInstallSeoPlugin($_GET['pid']);
            break;

        default:
            $controller->listSeoPlugins();
            break;
    }
}
