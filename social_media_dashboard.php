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

include_once(SP_CTRLPATH."/dashboard.ctrl.php");
include_once(SP_CTRLPATH."/report.ctrl.php");
include_once(SP_CTRLPATH."/webmaster.ctrl.php");
include_once(SP_CTRLPATH."/social_media.ctrl.php");
include_once(SP_CTRLPATH."/review_manager.ctrl.php");
$controller = New DashboardController();
$controller->layout = __assign($_REQUEST, 'layout', 'ajax');

$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);
$controller->spTextKeyword = $controller->getLanguageTexts('keyword', $_SESSION['lang_code']);
$controller->set('spTextKeyword', $controller->spTextKeyword);
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextHome = $controller->getLanguageTexts('home', $_SESSION['lang_code']);
$controller->set('spTextHome', $controller->spTextHome);
$controller->spTextDashboard = $controller->getLanguageTexts('dashboard', $_SESSION['lang_code']);
$controller->set('spTextDashboard', $controller->spTextDashboard);
$controller->spTextSocialMedia = $controller->getLanguageTexts('socialmedia', $_SESSION['lang_code']);
$controller->set('spTextSocialMedia', $controller->spTextSocialMedia);

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
if (!empty($custSiteInfo['site_title'])) $controller->set('spTitle', $custSiteInfo['site_title']);
if (!empty($custSiteInfo['site_description'])) $controller->set('spDescription', $custSiteInfo['site_description']);
if (!empty($custSiteInfo['site_keywords'])) $controller->set('spKeywords', $custSiteInfo['site_keywords']);

if(!empty($_SERVER['REQUEST_METHOD'])) {
	switch($_REQUEST['sec']) {
		default:
			$controller->showSocialMediaDashboard($_REQUEST);
			break;
	}
}
?>
