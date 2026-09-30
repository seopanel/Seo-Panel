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

// AI Schema Markup Generator - turns a form into valid schema.org
// (JSON-LD) markup for the types most directly tied to AI answer-engine
// citation. Entirely self-hosted - see controllers/schemagenerator.ctrl.php.
include_once("includes/sp-load.php");
include_once(SP_CTRLPATH . "/schemagenerator.ctrl.php");
include_once(SP_CTRLPATH . "/website.ctrl.php");

checkLoggedIn();
$controller = New SchemaGeneratorController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_POST['sec'] ?? '') {
		case "generate":
			$controller->generateSchema($_POST);
			break;

		case "remove":
			$controller->removeSchema($_POST);
			break;

		default:
			$controller->showGenerator($_POST);
			break;
	}
} else {
	$controller->showGenerator($_GET);
}
?>
