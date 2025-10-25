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
isUserHaveAccessToSeoTool("sm-checker");

include_once(SP_CTRLPATH."/social_media.ctrl.php");
$controller = New SocialMediaController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->spTextSMC = $controller->getLanguageTexts('socialmedia', $_SESSION['lang_code']);
$controller->set('spTextSMC', $controller->spTextSMC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
    switch($_POST['sec']){
			
		case "doQuickChecker":
			$controller->doQuickChecker($_POST);
			break;
        
        case "updateSocialMediaLink":
            $controller->verifyActionAllowed($_POST['id']);
            $controller->updateSocialMediaLink($_POST);
            break;
        
        case "createSocialMediaLink":
            $controller->createSocialMediaLink($_POST);
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
	        $controller->showSocialMediaLinks($_POST);
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
            $controller->showSocialMediaLinks($_GET);
            break;
            
        case "Inactivate":
            $controller->verifyActionAllowed($_GET['id']);
            $controller->__changeStatus($_GET['id'], 0);
            $controller->showSocialMediaLinks($_GET);
            break;
        
        case "delete":
            $controller->verifyActionAllowed($_GET['id']);
            $controller->deleteSocialMediaLink($_GET['id']);
            break;
        
        case "edit":
            $controller->editSocialMediaLink($_GET['id']);
            break;
	    
	    case "newSocialMediaLink":
	        $controller->newSocialMediaLink($_GET);
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
		    $controller->showSocialMediaLinkSelectBox($_GET['website_id']);
		    break;
		
		default:
			$controller->showSocialMediaLinks($_GET);
			break;
	}
	
}
?>