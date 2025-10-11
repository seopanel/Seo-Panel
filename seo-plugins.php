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
isHavingWebsite();
include_once(SP_CTRLPATH."/seoplugins.ctrl.php");
$controller = New SeoPluginsController();
$controller->view->menu = 'seoplugins';
$controller->spTextPlugin = $controller->getLanguageTexts('plugin', $_SESSION['lang_code']);
$controller->set('spTextPlugin', $controller->spTextPlugin);

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
$siteName = !empty($custSiteInfo['site_name']) ? $custSiteInfo['site_name'] : "Seo Panel";
$controller->set('spTitle', "$siteName: Provides latest seo plugins to increase and track the performace your website");
$controller->set('spDescription', "Its an open source software and also you can develop your own seo plugins for $siteName. Download new seo plugins and install into your $siteName software and increase your site perfomance.");
$controller->set('spKeywords', "$siteName plugins,latest seo plugins,download seo plugins,install seo plugins,develop seo plugins");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
			
		default:
			$controller->manageSeoPlugins($_REQUEST, 'post');
			break;
	}
	
}else{
	switch($_GET['sec']){

		case "show":
			$controller->showSeoPlugins($_GET);
			break;
			
		default:
			
			// if plugin id existing present
			if (!empty($_GET['pid'])) {
				$controller->manageSeoPlugins($_REQUEST, 'get');
			} else {
				$controller->showSeoPlugins($_GET);
			}
			
			break;
	}
}
?>
