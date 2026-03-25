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
isUserHaveAccessToSeoTool("review-manager");

include_once(SP_CTRLPATH."/review_manager.ctrl.php");
$controller = New ReviewManagerController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->spTextRM = $controller->getLanguageTexts('review', $_SESSION['lang_code']);
$controller->set('spTextRM', $controller->spTextRM);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    switch($_POST['sec']){

		case "doQuickChecker":
			$controller->doQuickChecker($_POST);
			break;

		case "checkQuickCheckerStatus":
			$controller->checkQuickCheckerStatus($_POST);
			break;
        
        case "updateReviewLink":
            $controller->verifyActionAllowed($_POST['id']);
            $controller->updateReviewLink($_POST);
            break;
        
        case "createReviewLink":
            $controller->createReviewLink($_POST);
            break;
	    
	    case "reportSummary":
	        $controller->viewReportSummary($_POST);
	        break;
	        
	    case "viewDetailedReports":
	        $controller->viewDetailedReports($_POST);
	        break;
	        
	    case "viewGraphReports":
	        $controller->viewGraphReports($_POST);
	        break;
			
	    default:
	        $controller->showReviewLinks($_POST);
			break;
	}
	
} else {
	
    switch($_GET['sec']) {
			
		case "quickChecker":
			$controller->viewQuickChecker($_GET);
			break;
        
        case "Activate":
            $controller->verifyActionAllowed($_GET['id']);
            $controller->__changeStatus($_GET['id'], 1);
            $controller->showReviewLinks($_GET);
            break;
            
        case "Inactivate":
            $controller->verifyActionAllowed($_GET['id']);
            $controller->__changeStatus($_GET['id'], 0);
            $controller->showReviewLinks($_GET);
            break;
        
        case "delete":
            $controller->verifyActionAllowed($_GET['id']);
            $controller->deleteReviewLink($_GET['id']);
            break;
        
        case "edit":
            $controller->editReviewLink($_GET['id']);
            break;
	    
	    case "newReviewLink":
	        $controller->newReviewLink($_GET);
	        break;
	    
	    case "reportSummary":
	        $controller->viewReportSummary($_GET);
	        break;
			
		case "viewDetailedReports":
			$controller->viewDetailedReports($_GET);
			break;
			
		case "viewGraphReports":
			$controller->viewGraphReports($_GET);
			break;
			
		case "linkSelectBox":
		    $controller->showReviewLinkSelectBox($_GET['website_id']);
		    break;
		
		default:
			$controller->showReviewLinks($_GET);
			break;
	}	
}
?>