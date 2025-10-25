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
checkLoggedIn();
include_once(SP_CTRLPATH."/adminpanel.ctrl.php");
$controller = New AdminPanelController();
$controller->view->menu = 'adminpanel';

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
$siteName = !empty($custSiteInfo['site_name']) ? $custSiteInfo['site_name'] : "Seo Panel";
$controller->set('spTitle', "$siteName: User control panel for manage settings");
$controller->set('spDescription', "$siteName user control panel for manage settings");
$controller->set('spKeywords', "$siteName settings,User control panel,manage $siteName settings");
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$info = $_REQUEST;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
	}
	
}else{
	switch($_GET['sec']){
		case "newweb":
			$info['start_script'] = 'websites.php?sec=new&check=1';
			$controller->index($info);
			break;
		
		case "myprofile":
			$info['menu_selected'] = 'my-profile';
			$info['start_script'] = 'users.php?sec=my-profile';
			$controller->index($info);
			break;
		
		case "connections":
			$info['menu_selected'] = 'my-profile';
			$info['start_script'] = 'connections.php';
			$controller->index($info);
			break;
		
		case "settings":
			$info['menu_selected'] = 'settings';
			$info['start_script'] = 'settings.php';
			$controller->index($info);
			break;
		
		case "moz-settings":
			$info['menu_selected'] = 'settings';
			$info['start_script'] = 'settings.php?category=moz';
			$controller->index($info);
			break;
		
		case "google-settings":
			$info['menu_selected'] = 'settings';
			$info['start_script'] = 'settings.php?category=google';
			$controller->index($info);
			break;
			
		case "alerts":
		    $info['menu_selected'] = 'my-profile';
		    $info['start_script'] = 'alerts.php';
		    $controller->index($info);
		    break;

		default:
			$_GET['sec'] = addslashes($_GET['sec']);
			$controller->index($_GET);
			break;
	}
}

?>