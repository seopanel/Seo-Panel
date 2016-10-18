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

if( $_GET['sec'] == 'aboutus'){
	isLoggedIn();
}else{
	checkAdminLoggedIn();
}

include_once(SP_CTRLPATH."/settings.ctrl.php");
$controller = New SettingsController();
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextSettings = $controller->getLanguageTexts('settings', $_SESSION['lang_code']);
$controller->set('spTextSettings', $controller->spTextSettings);
$controller->spTextSubscription = $controller->getLanguageTexts('subscription', $_SESSION['lang_code']);
$controller->set('spTextSubscription', $controller->spTextSubscription);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
		
		case "update":
			$controller->updateSystemSettings($_POST);
			break;
	}
	
}else{
	switch($_GET['sec']){
		
		case "reportsettings":
			$controller->showSystemSettings('report');
			break;
		
		case "apisettings":
			$controller->showSystemSettings('api');
			break;
		
		case "proxysettings":
			$controller->showSystemSettings('proxy');
			break;
		
		case "aboutus":
			$controller->showAboutUs();
			break;
		
		case "version":
			$controller->showVersion();
			break;
		
		case "checkversion":
			$controller->checkVersion();
			break;
		
		case "checkMozCon":
			
			if (empty($_GET['access_id']) || empty($_GET['secret_key'])) {
				print "<span class='error'>{$_SESSION['text']['label']['Fail']}</span>";
			} else {
			
				include_once(SP_CTRLPATH."/rank.ctrl.php");
				$rankObj = new RankController();
				$urlList = array("http://moz.com");
				list($rankInfo, $logInfo) = $rankObj->__getMozRank($urlList, $_GET['access_id'], $_GET['secret_key'], true);
				
				// if error occured
				if (isset($logInfo['crawl_status']) && ($logInfo['crawl_status'] == 0)) {
					print "<span class='error'>{$logInfo['log_message']}</span>";
				} else {
					print "<span class='success'>{$_SESSION['text']['label']['Success']}</span>";
				}
			}
			
			break;

		default:
		    $category = empty($_GET['category']) ? 'system' : $_GET['category']; 
			$controller->showSystemSettings($category);
			break;
	}
}

?>