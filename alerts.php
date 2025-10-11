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

include_once("includes/sp-load.php");
include_once(SP_CTRLPATH . "/alerts.ctrl.php");

checkLoggedIn();
$controller = New AlertController();
$controller->view->menu = 'alerts';
$controller->layout = 'ajax';
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextMyAccount = $controller->getLanguageTexts('myaccount', $_SESSION['lang_code']);
$controller->set('spTextMyAccount', $controller->spTextMyAccount);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
    switch($_POST['sec']) {
        
        case "fetch_alerts":
            $controller->fetchAlerts($_POST);
            break;
        
        case "delete_all_alerts":
            if (!empty($_POST['ids'])) {
                foreach($_POST['ids'] as $id) {
                    $controller->deleteAlert($id);
                }
            }
            
            $controller->listAlerts($_POST);
            break;
		
		default:
		    $controller->listAlerts($_POST);
			break;
			
	}
	
} else {
	
    switch($_GET['sec']) {
        
        case "alert_info":
            $controller->showAlertInfo($_GET['id']);
            break;
        
        case "delete_alert":
            $controller->deleteAlert($_GET['id']);
            $controller->listAlerts($_GET);
            break;
            
		default:			
			$controller->listAlerts($_GET);
			break;
	}	
}
?>