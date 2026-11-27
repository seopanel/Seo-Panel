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

// check for access to seo tool
isUserHaveAccessToSeoTool("web-analytics");

include_once(SP_CTRLPATH."/analytics.ctrl.php");
$controller = New AnalyticsController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']) {
			
		case "doQuickChecker":
			$controller->doQuickChecker($_POST);
			break;
		    
		case "viewAnalyticsSummary":
		    $controller->viewAnalyticsSummary($_POST);
		    break;
		    
		case "viewAnalyticsReports":
		    $controller->viewAnalyticsReports($_POST);
		    break;
		    
		case "viewAnalyticsGraphReports":
		    $controller->viewAnalyticsGraphReports($_POST);
		    break;
		    
		default:
		    $controller->viewAnalyticsSummary($_POST);
		    break;
	}
	
} else {
	
	switch($_GET['sec']) {
			
		case "quickChecker":
			$controller->viewQuickChecker($_GET);
			break;
		    
		case "viewAnalyticsSummary":
		    $controller->viewAnalyticsSummary($_GET);
		    break;
		    
		case "viewAnalyticsReports":
		    $controller->viewAnalyticsReports($_GET);
		    break;
		    
		case "viewAnalyticsGraphReports":
		    $controller->viewAnalyticsGraphReports($_GET);
		    break;
		    
		case "source_box":
		    $controller->showSourceSelectBox($_GET['website_id']);
		    break;
		    
		default:
		    // bug fix: this is the GET branch (inside switch($_GET['sec'])) -
		    // $_POST is empty on a plain GET request, so any query-string
		    // filters (website_id, from_time, etc.) silently got dropped
		    // whenever sec was missing/mistyped/unrecognized, unlike every
		    // sibling entry point's own GET-branch default
		    // (webmaster-tools.php/social_media.php/review.php all use $_GET
		    // here).
		    $controller->viewAnalyticsSummary($_GET);
		    break;

	}
	
}
?>