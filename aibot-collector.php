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

// Logged-in gated (NOT public) - this generates a file the site owner takes
// away and hosts on their OWN external server. It is not itself served to
// the public; aibot-collect.php is the public ingest endpoint that receives
// hits FROM copies of this generated file once installed elsewhere.
include_once("includes/sp-load.php");
checkLoggedIn();
isUserHaveAccessToSeoTool("ai-visibility");

include_once(SP_CTRLPATH."/website.ctrl.php");
include_once(SP_CTRLPATH."/aivisibility.ctrl.php");

$userId = isLoggedIn();
$websiteController = new WebsiteController();
$websiteList = $websiteController->__getAllWebsites($userId, true);

$controller = new AIVisibilityController();
$requestedId = !empty($_GET['website_id']) ? intval($_GET['website_id']) : 0;
$websiteId = 0;
foreach ($websiteList as $w) {
	if ($w['id'] == $requestedId) { $websiteId = $requestedId; break; }
}
if (empty($websiteId)) {
	http_response_code(403);
	exit;
}

// lazily create the site row (token) if it doesn't exist yet, same as showSetup()
$controller->__getOrCreateSite($websiteId, $websiteList);
$script = $controller->generateBotCollectorScript($websiteId);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="ai-bot-collector.php"');
header('Content-Length: ' . strlen($script));
echo $script;
exit;
