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
include_once(SP_CTRLPATH . "/user-type.ctrl.php");
$controller = New UserTypeController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextWeb = $controller->getLanguageTexts('website', $_SESSION['lang_code']);
$controller->set('spTextWeb', $controller->spTextWeb);

if($_SERVER['REQUEST_METHOD'] == 'POST') {

	switch($_POST['sec']) {

		case "create":
			$controller->createUserType($_POST);
			break;

		case "update":
			$controller->updateUserType($_POST);
			break;

		case "activateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 1);
    		    }
		    }
			$controller->listUserTypes($_POST);
		    break;

		case "inactivateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 0);
    		    }
		    }
			$controller->listUserTypes($_POST);
		    break;

		case "deleteall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__deleteUserType($id);
    		    }
		    }
			$controller->listUserTypes($_POST);
		    break;

	}
} else {

	switch($_GET['sec']) {

		case "new":
			$controller->newUserType($_GET);
			break;

		case "edit":
			$controller->editUserType($_GET['userTypeId']);
			break;

		case "Activate":
			$controller->__changeStatus($_GET['userTypeId'], 1);
			$controller->listUserTypes($_GET);
			break;

		case "Inactivate":
			$controller->__changeStatus($_GET['userTypeId'], 0);
			$controller->listUserTypes($_GET);
			break;

		case "delete":
			$controller->__deleteUserType($_GET['userTypeId']);
			$controller->listUserTypes($_GET);
			break;

		default:
			$controller->listUserTypes($_GET);
			break;


	}

}
?>