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
include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/website.ctrl.php");
#include_once(SP_CTRLPATH."/language.ctrl.php");
include_once(SP_CTRLPATH."/searchengine.ctrl.php");
$controller = New KeywordController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->spTextKeyword = $controller->getLanguageTexts('keyword', $_SESSION['lang_code']);
$controller->set('spTextKeyword', $controller->spTextKeyword);


$userId = isLoggedIn();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch($_POST['sec']){
		case "create":
			$controller->createKeyword($_POST);
			break;

		case "update":
			$controller->updateKeyword($_POST);
			break;

		case "import":
			$controller->createImportedKeywords($_POST);
			break;

		case "activateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 1);
    		    }
		    }
			$controller->listKeywords($_POST);
		    break;

		case "inactivateall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__changeStatus($id, 0);
    		    }
		    }
			$controller->listKeywords($_POST);
		    break;

		case "deleteall":
		    if (!empty($_POST['ids'])) {
    		    foreach($_POST['ids'] as $id) {
    		        $controller->__deleteKeyword($id);
    		    }
		    }
			$controller->listKeywords($_POST);
		    break;

		default:
			$controller->listKeywords($_POST);
			break;
	}

}else{
	switch($_GET['sec']){

		case "Activate":
			$controller->__changeStatus($_GET['keywordId'], 1);
			$controller->listKeywords($_GET);
			break;

		case "Inactivate":
			$controller->__changeStatus($_GET['keywordId'], 0);
			$controller->listKeywords($_GET);
			break;

		case "reports":
			$controller->showKeywordReports($_GET['keywordId']);
			break;

		case "delete":
			$controller->__deleteKeyword($_GET['keywordId']);
			$controller->listKeywords($_GET);
			break;

		case "edit":
			$controller->editKeyword($_GET['keywordId']);
			break;

		case "new":
			$controller->set('post', $_GET);
			$controller->newKeyword();
			break;

		case "import":
			$controller->set('post', $_GET);
			$controller->importKeywords();
			break;

		case "keywordbox":
			$controller->set('keyNull', $_GET['keyNull']);
			$controller->showKeywordSelectBox($userId, $_GET['website_id']);
			break;

		default:
			$controller->listKeywords($_GET);
			break;
	}
}

?>