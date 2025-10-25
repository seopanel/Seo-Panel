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
include_once(SP_CTRLPATH."/index.ctrl.php");
$controller = New IndexController();
$controller->view->menu = 'support';
$blogContent = getCustomizerPage('support');

$controller->set('spTitle', !empty($blogContent['meta_title']) ? $blogContent['meta_title'] : 'Seo Panel: Support System provides latest seo services');
$controller->set('spDescription', !empty($blogContent['meta_description']) ? $blogContent['meta_description'] : 'Seo Panel support system will provides 1000 Directory Package,New Search Engines,New Seo Tools,New Seo Plugins,New Skin,Customization,Report Bugs,Support Tickets');
$controller->set('spKeywords', !empty($blogContent['meta_keywords']) ? $blogContent['meta_keywords'] : 'seo panel support,1000 Directory Package,New Search Engines,New Seo Tools,New Seo Plugins,New Skin,Customization,Report Bugs,Support Tickets');
if($_SERVER['REQUEST_METHOD'] == 'GET'){

	switch($_GET['sec']){

		default:
		    $controller->set('blogContent', $blogContent);
			$controller->showSupport();
			break;
	}
}

?>