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

// AI Perception Check - lets a website owner ask a real hosted AI model
// (their own OpenAI/Anthropic/Google API key) what it knows about their
// own website. See controllers/aiperception.ctrl.php for the "this sends
// data to a third party, unlike the rest of AI Visibility" boundary this
// feature deliberately keeps separate.
include_once("includes/sp-load.php");
include_once(SP_CTRLPATH . "/aiperception.ctrl.php");
include_once(SP_CTRLPATH . "/website.ctrl.php");

checkLoggedIn();
$controller = New AiPerceptionController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_POST['sec']) {
		case "save-key":
			$controller->saveApiKey($_POST);
			break;

		case "remove-key":
			$controller->removeApiKey($_POST);
			break;

		case "add-prompt":
			$controller->addPrompt($_POST);
			break;

		case "remove-prompt":
			$controller->removePrompt($_POST);
			break;

		default:
			$controller->showSettings();
			break;
	}
} else {
	switch ($_GET['sec'] ?? '') {
		case "check":
			$controller->showCheck($_GET);
			break;

		case "ask":
			$controller->askAboutWebsite($_GET);
			break;

		case "tracking":
			$controller->showTracking($_GET);
			break;

		default:
			$controller->showSettings();
			break;
	}
}
?>
