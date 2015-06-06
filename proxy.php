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
include_once(SP_CTRLPATH."/proxy.ctrl.php");
$controller = New ProxyController();
$controller->view->menu = 'adminpanel';
$controller->layout = 'ajax';
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextProxy = $controller->getLanguageTexts('proxy', $_SESSION['lang_code']);
$controller->set('spTextProxy', $controller->spTextProxy);
$controller->set('spTextSA', $controller->getLanguageTexts('siteauditor', $_SESSION['lang_code']));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	switch ($_POST['sec']) {

		case "create":
		    $_POST = sanitizeData($_POST, true, true);
			$controller->createProxy($_POST);
			break;

		case "update":
		    $_POST = sanitizeData($_POST, true, true);
			$controller->updateProxy($_POST);
			break;

		case "activateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 1);
    		    }
		    }
			$controller->listProxy($_POST);
		    break;

		case "inactivateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 0);
    		    }
		    }
			$controller->listProxy($_POST);
		    break;

		case "deleteall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__deleteProxy($id);
    		    }
		    }
			$controller->listProxy($_POST);
		    break;

	    case "checkall":
	    	if (!empty($_POST['ids'])) {
	    		foreach($_POST['ids'] as $id) {
	    			$controller->checkStatus($id);
	    		}
	    	}
	    	$controller->listProxy($_POST);
	    	break;

		case "checkAllstatus":
			$controller->checkAllProxyStatus($_POST);
			break;

		case "importproxy":
			$controller->importProxy($_POST);
			break;

		case "perfomance":
			$controller->showProxyPerfomance($_POST);
			break;

		default:
			$controller->listProxy($_POST);
			break;

	}

} else {

	switch ($_GET['sec']) {

		case "Activate":
			$controller->__changeStatus($_GET['proxyId'], 1);
			$controller->listProxy($_GET);
			break;

		case "Inactivate":
			$controller->__changeStatus($_GET['proxyId'], 0);
			$controller->listProxy($_GET);
			break;

		case "delete":
			$controller->__deleteProxy($_GET['proxyId']);
			$controller->listProxy($_GET);
			break;

		case "edit":
			$controller->editProxy($_GET['proxyId']);
			break;

		case "new":
			$controller->newProxy($_GET);
			break;

		case "checkstatus":
			$controller->checkStatus($_GET['proxyId']);
			$controller->listProxy($_GET);
			break;

		case "checkAllstatus":
			$controller->showcheckAllStatus($_GET);
			break;

		case "runcheckstatus":
			$controller->runCheckStatus($_GET);
			break;

		case "import":
			$controller->showImportProxy($_GET);
			break;

		case "croncommand":
			$controller->showCronCommand();
			break;

		case "perfomance":
			$controller->showProxyPerfomance($_GET);
			break;

		default:
			$controller->listProxy($_GET);
			break;
	}
}
?>