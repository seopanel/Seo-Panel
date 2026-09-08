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

// Per-user MCP (Model Context Protocol) token management - lets a website
// owner generate/revoke their own personal access token for their own AI
// agent to query their own SEO Panel data. See controllers/mcp.ctrl.php
// and api/mcp.php.
include_once("includes/sp-load.php");
include_once(SP_CTRLPATH . "/mcp.ctrl.php");

checkLoggedIn();
$controller = New MCPController();
$controller->view->menu = 'my-profile';
$controller->layout = 'ajax';
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextMyAccount = $controller->getLanguageTexts('myaccount', $_SESSION['lang_code']);
$controller->set('spTextMyAccount', $controller->spTextMyAccount);

$userId = isLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_POST['sec']) {
		case "create":
			$controller->createToken($userId, $_POST['label'] ?? '');
			break;

		case "revoke":
			$controller->revokeToken($userId, intval($_POST['token_id'] ?? 0));
			break;

		default:
			$controller->listTokens($userId);
			break;
	}
} else {
	$controller->listTokens($userId);
}
?>
