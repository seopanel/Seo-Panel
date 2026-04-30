<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)             *
 *   sendtogeo@gmail.com                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 ***************************************************************************/

include_once("includes/sp-load.php");
checkLoggedIn();

include_once(SP_CTRLPATH."/website.ctrl.php");
include_once(SP_CTRLPATH."/recommendations.ctrl.php");

$controller = new RecommendationsController();
$controller->layout = __assign($_REQUEST, 'layout', 'ajax');

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
if (!empty($custSiteInfo['site_title']))       $controller->set('spTitle',       $custSiteInfo['site_title']);
if (!empty($custSiteInfo['site_description'])) $controller->set('spDescription', $custSiteInfo['site_description']);
if (!empty($custSiteInfo['site_keywords']))    $controller->set('spKeywords',     $custSiteInfo['site_keywords']);

if (!empty($_SERVER['REQUEST_METHOD'])) {
    switch ($_REQUEST['sec']) {
        case 'refresh':
            $controller->refreshRecommendations($_REQUEST);
            break;
        default:
            $controller->showRecommendationsDashboard($_REQUEST);
            break;
    }
}
?>
