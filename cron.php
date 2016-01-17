<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seofreetools.net)  	   *
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

if (!empty($_SERVER['REQUEST_METHOD'])) {
    
    # to section to generate report from admin area
    include_once("includes/sp-load.php");
    checkAdminLoggedIn();
    include_once(SP_CTRLPATH."/cron.ctrl.php");
    $controller = new CronController();
    $controller->timeStamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    $controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
    $controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
    $controller->spTextKeyword = $controller->getLanguageTexts('keyword', $_SESSION['lang_code']);
    $controller->set('spTextKeyword', $controller->spTextKeyword);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        switch ($_POST['sec']) {
            
            case "generate":
                $controller->executeReportGenerationScript($_POST);
                break;
        }
    } else {
        switch ($_GET['sec']) {
            
            case "generate":
                $controller->routeCronJob($_GET['website_id'], $_GET['repTools']);
                break;
            
            case "croncommand":
                $controller->showCronCommand();
                break;
    
            default:
                $controller->showReportGenerationManager();
                break;
        }
    }
} else {
    
    # the section for generate reports using system cron job
    include_once("includes/sp-load.php");
    include_once(SP_CTRLPATH."/cron.ctrl.php");
    include_once(SP_CTRLPATH."/report.ctrl.php");
    include_once(SP_CTRLPATH."/searchengine.ctrl.php");
    include_once(SP_CTRLPATH."/keyword.ctrl.php");
    $controller = new CronController();
    $controller->timeStamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    
    $includeList = array();   // the only included seo tools id 
    $controller->executeCron($includeList);
    
    // delete crawl logs before 2 months
    include_once(SP_CTRLPATH."/crawllog.ctrl.php");
    $crawlLog = new CrawlLogController();
    $crawlLog->clearCrawlLog(SP_CRAWL_LOG_CLEAR_TIME);
    echo "Clearing crawl logs before " . SP_CRAWL_LOG_CLEAR_TIME . " days";
}
