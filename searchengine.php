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
include_once(SP_CTRLPATH."/searchengine.ctrl.php");
$controller = new SearchEngineController();
$controller->view->menu = 'se-manager';
$controller->layout = 'ajax';
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextUser = $controller->getLanguageTexts('searchengine', $_SESSION['lang_code']);
$controller->set('spTextSE', $controller->spTextUser);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
            
        case "activateall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__changeStatus($id, 1);
                }
            }
            $controller->listSE($_POST);
            break;
            
        case "inactivateall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__changeStatus($id, 0);
                }
            }
            $controller->listSE($_POST);
            break;
            
        case "deleteall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__deleteSearchEngine($id);
                }
            }
            $controller->listSE($_POST);
            break;

        default:
            $controller->listSE($_POST);
            break;
    }
} else {
    switch ($_GET['sec']) {
        
        case "Activate":
            $controller->__changeStatus($_GET['seId'], 1);
            $controller->listSE($_GET);
            break;
        
        case "Inactivate":
            $controller->__changeStatus($_GET['seId'], 0);
            $controller->listSE($_GET);
            break;
        
        case "delete":
            $controller->__deleteSearchEngine($_GET['seId']);
            $controller->listSE($_GET);
            break;

        default:
            $controller->listSE($_GET);
            break;
    }
}
