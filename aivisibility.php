<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	           *
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
isUserHaveAccessToSeoTool("ai-visibility");

include_once(SP_CTRLPATH."/aivisibility.ctrl.php");
$controller = New AIVisibilityController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextAIV = $controller->getLanguageTexts('aivisibility', $_SESSION['lang_code']);
$controller->set('spTextAIV', $controller->spTextAIV);
$controller->set('spTextKeyword', $controller->getLanguageTexts('keyword', $_SESSION['lang_code']));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	switch ($_POST['sec']) {

		case "installstatus":
			$controller->showInstallStatus($_POST);
			break;

		case "save-platform":
			$controller->savePlatform($_POST);
			break;

		case "toggle-platform":
			$controller->togglePlatformField($_POST);
			break;

		case "delete-platform":
			$controller->deletePlatform($_POST);
			break;

		case "save-site-access":
			$controller->saveSiteAccessConfig($_POST);
			break;

		case "toggle-robots-rule":
			$controller->toggleRobotsRule($_POST);
			break;

		case "regenerate-llms":
			$controller->regenerateLlmsTxt($_POST);
			break;

		case "install-wp-collector":
			$controller->installWordPressCollector($_POST);
			break;

		case "save-htaccess-config":
			$controller->saveHtaccessConfig($_POST);
			break;

		default:
			$controller->showSetup($_POST);
			break;
	}

} else {
	switch ($_GET['sec']) {

		case "report":
			$controller->showReport($_GET);
			break;

		case "export-report":
			$controller->exportReportCsv($_GET);
			break;

		case "aioverview":
			$controller->showAIOverviewReport($_GET);
			break;

		case "botreport":
			$controller->showBotReport($_GET);
			break;

		case "export-botreport":
			$controller->exportBotReportCsv($_GET);
			break;

		case "export-robots-audit":
			$controller->exportRobotsAuditLog($_GET);
			break;

		case "installstatus":
			$controller->showInstallStatus($_GET);
			break;

		case "platforms":
			$controller->listPlatforms($_GET);
			break;

		case "overview":
			$controller->showOverview($_GET);
			break;

		case "export-overview":
			$controller->exportOverviewCsv($_GET);
			break;

		case "suggest-llms-description":
			$userId = isLoggedIn();
			$websiteId = intval($_GET['website_id'] ?? 0);
			header('Content-Type: application/json');
			include_once(SP_CTRLPATH . "/localai.ctrl.php");
			$localAiCtrler = new LocalAIController();
			echo json_encode($localAiCtrler->suggestLlmsTxtDescription($websiteId, $userId));
			exit;

		default:
			$controller->showSetup($_GET);
			break;
	}
}

?>
