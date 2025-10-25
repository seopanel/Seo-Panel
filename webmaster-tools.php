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
isUserHaveAccessToSeoTool("webmaster-tools");

include_once(SP_CTRLPATH."/webmaster.ctrl.php");
include_once(SP_CTRLPATH."/keyword.ctrl.php");
$controller = New WebMasterController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
			
		case "doQuickChecker":
			$controller->doQuickChecker($_POST);
			break;
			
		case "viewWebsiteSearchReports":
			$controller->viewWebsiteSearchReports($_POST);
			break;
			
		case "viewKeywordSearchReports":
			$controller->viewKeywordSearchReports($_POST);
			break;
			
		case "viewKeywordSearchSummary":
			$controller->viewKeywordSearchSummary($_POST);
			break;
			
		case "viewWebsiteSearchSummary":
			$controller->viewWebsiteSearchSummary($_POST);
			break;
			
		case "viewKeywordSearchGraphReports":
			$controller->viewKeywordSearchGraphReports($_POST);
			break;
			
		case "viewWebsiteSearchGraphReports":
			$controller->viewWebsiteSearchGraphReports($_POST);
			break;
		
		default:
			$controller->viewKeywordSearchSummary($_POST);
			break;
	}
	
} else {
	
	switch($_GET['sec']){
			
		case "quickChecker":
			$controller->viewQuickChecker($_GET);
			break;
			
		case "viewWebsiteSearchReports":
			$controller->viewWebsiteSearchReports($_GET);
			break;
			
		case "viewKeywordSearchReports":
			$controller->viewKeywordSearchReports($_GET);
			break;
			
		case "viewKeywordSearchSummary":
			$controller->viewKeywordSearchSummary($_GET);
			break;
			
		case "viewWebsiteSearchSummary":
			$controller->viewWebsiteSearchSummary($_GET);
			break;
			
		case "viewKeywordSearchGraphReports":
			$controller->viewKeywordSearchGraphReports($_GET);
			break;
			
		case "viewWebsiteSearchGraphReports":
			$controller->viewWebsiteSearchGraphReports($_GET);
			break;
			
		case "keywordbox":
			$controller->showKeywordSelectBox($_GET['website_id']);
			break;

		default:
			$controller->viewKeywordSearchSummary($_GET);
			break;
	}
	
}
?>