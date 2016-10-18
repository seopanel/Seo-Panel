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
checkLoggedIn();
include_once(SP_CTRLPATH."/rank.ctrl.php");
$controller = New RankController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->spTextRank = $controller->getLanguageTexts('rank', $_SESSION['lang_code']);
$controller->set('spTextRank', $controller->spTextRank);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
		
		case "quickrank":
			$controller->findQuickRank($_POST);
			break;
			
		case "generate":
			$controller->generateReports($_POST);
			break;
			
		case "reports":
			$controller->showReports($_POST);
			break;
			
		case "showpr":
			$controller->printGooglePageRank(urldecode($_POST['url']));
			break;
		
		case "showalexa":
			$controller->printAlexaRank(urldecode($_POST['url']));
			break;
			
		case "showmozrank":
			$controller->printMOZRank(urldecode($_POST['url']));
			break;
		
		default:
			$controller->findQuickRank($_POST);
			break;
	}
	
}else{
	switch($_GET['sec']){
		
		case "quickrank":
			$controller->showQuickRankChecker();
			break;
			
		case "showpr":
			$controller->printGooglePageRank(urldecode($_GET['url']));
			break;
			
		case "showmozrank":
			$controller->printMOZRank(urldecode($_GET['url']));
			break;
		
		case "showalexa":
			$controller->printAlexaRank(urldecode($_GET['url']));
			break;
			
		case "alexaimg":
			$controller->printAlexaRankImg($_GET['rank']);
			break;
			
		case "generate":
			$controller->showGenerateReports($_GET);
			break;
			
		case "reports":
			$controller->showReports($_GET);
			break;

		default:
			$controller->showQuickRankChecker();
			break;
	}
}

?>