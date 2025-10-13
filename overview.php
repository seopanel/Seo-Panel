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
include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/overview.ctrl.php");
$controller = New OverviewController();
$controller->view->menu = 'home';
$controller->layout = 'ajax';

$spTextHome = $controller->getLanguageTexts('home', $_SESSION['lang_code']);
$controller->set('spTextHome', $spTextHome);

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
if (!empty($custSiteInfo['site_title'])) $controller->set('spTitle', $custSiteInfo['site_title']);
if (!empty($custSiteInfo['site_description'])) $controller->set('spDescription', $custSiteInfo['site_description']);
if (!empty($custSiteInfo['site_keywords'])) $controller->set('spKeywords', $custSiteInfo['site_keywords']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    switch($_GET['sec']) {
        
        case "page-overview":
            $controller->showPageOverview($_GET['website_id'], $_GET['from_time'], $_GET['to_time']);
            break;
            
        case "page-overview-data":
            $controller->showPageOverviewData($_GET);
            break;

	    case "keyword-overview":
	        $controller->showKeywordOverview($_GET['website_id'], $_GET['from_time'], $_GET['to_time']);
	        break;
	        
	    case "keyword-overview-data":
	        $controller->showKeywordOverviewData($_GET);
	        break;
	    
	    default:
	        $controller->layout = 'default';
		    $controller->showOverView($_GET);
			break;
			
	}
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch($_POST['sec']) {	    
	    case "reports_dashboard":
	        $controller->layout = 'default';
	        $controller->showOverView($_POST);
	        break;
	    
	    default:
	        $controller->layout = 'default';
	        $controller->showOverView($_POST);
			break;			
	}
}
?>