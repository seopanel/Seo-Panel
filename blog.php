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
include_once(SP_CTRLPATH."/blog.ctrl.php");
$controller = New BlogController();
$controller->view->menu = 'blog';
$custSiteInfo = getCustomizerDetails();
$siteName = !empty($custSiteInfo['site_name']) ? $custSiteInfo['site_name'] : "Seo Panel";
$controller->set('spTitle', "$siteName: Blog");
$controller->set('spDescription', "$siteName: Blog with latest news");
$controller->set('spKeywords', "$siteName blog, $siteName latest news");
$controller->set('spBlogText', $controller->getLanguageTexts('blog', $_SESSION['lang_code']));

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	switch($_POST['sec']){
	
		default:
			$controller->listBlogs($_POST);
			break;
			
	}
	
} else if($_SERVER['REQUEST_METHOD'] == 'GET'){

	switch($_GET['sec']){

		default:
			if (!empty($_GET['id'])) {
				$controller->showBlog($_GET['id']);
			} else {
				$controller->listBlogs($_GET);
			}
			break;
	}
}

?>