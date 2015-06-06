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
isHavingWebsite();
include_once(SP_CTRLPATH."/seotools.ctrl.php");
$controller = New SeoToolsController();
$controller->view->menu = 'seotools';

$controller->set('spTitle', 'Seo Panel: Provides lots of hot seo tools to increase and track the performace your websites');
$controller->set('spDescription', 'The major features of Seo Panel are Automatic Directory Submission,Keyword position checker,Sitemap Generator,Rank Checker,Backlinks Checker,Meta Tag Generator.');
$controller->set('spKeywords', 'seo panel tools,Automatic Directory Submission,Keyword position checker,Sitemap Generator,Rank Checker,Backlinks Checker,Meta Tag Generator');
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->set('spTextKeyword', $controller->getLanguageTexts('keyword', $_SESSION['lang_code']));
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch($_POST['sec']){
	}

}else{
	switch($_GET['sec']){

		default:
			$controller->index($_GET);
			break;
	}
}

?>