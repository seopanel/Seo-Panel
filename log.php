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
checkAdminLoggedIn();
include_once(SP_CTRLPATH."/crawllog.ctrl.php");
include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/searchengine.ctrl.php");
$controller = new CrawlLogController();
$controller->view->menu = 'adminpanel';
$controller->layout = 'ajax';
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextLog = $controller->getLanguageTexts('log', $_SESSION['lang_code']);
$controller->set('spTextLog', $controller->spTextLog);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
            
        case "delete_all_crawl_log":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->deleteCrawlLog($id);
                }
            }
            
            $controller->listCrawlLog($_POST);
            break;
        
        default:
            $controller->listCrawlLog($_POST);
            break;
        
            
    }
} else {
    switch ($_GET['sec']) {
        
        case "clear_all_log":
            $controller->clearAllLog();
            $controller->listCrawlLog($_GET);
            break;
        
        case "delete_crawl_log":
            $controller->deleteCrawlLog($_GET['id']);
            $controller->listCrawlLog($_GET);
            break;
        
        case "crawl_log_details":
            $controller->showCrawlLogDetails($_GET['id']);
            break;
        
        default:
            $controller->listCrawlLog($_GET);
            break;
    }
}
