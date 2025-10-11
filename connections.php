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
include_once(SP_CTRLPATH . "/connection.ctrl.php");

checkLoggedIn();
$controller = New ConnectionController();
$controller->view->menu = 'connections';
$controller->layout = 'ajax';
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextMyAccount = $controller->getLanguageTexts('myaccount', $_SESSION['lang_code']);
$controller->set('spTextMyAccount', $controller->spTextMyAccount);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
		
		default:
			$controller->listConnections($_POST);
			break;
			
	}
	
} else {
	
	switch($_GET['action']) {
		
		case "connect_return":
			$controller->processConnectionReturn($_GET);
			break;
		
		case "disconnect":
			$controller->processDisconnection($_GET);
			break;
		
		default:			
			$controller->listConnections($_GET);
			break;
	}
	
}
?>