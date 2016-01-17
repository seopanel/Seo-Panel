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
checkLoggedIn();
include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/website.ctrl.php");
include_once(SP_CTRLPATH."/searchengine.ctrl.php");
include_once(SP_CTRLPATH."/report.ctrl.php");
$controller = new ReportController();
$controller->view->menu = 'seotools';
$controller->spTextTools = $controller->getLanguageTexts('seotools', $_SESSION['lang_code']);
$controller->set('spTextTools', $controller->spTextTools);
$controller->spTextKeyword = $controller->getLanguageTexts('keyword', $_SESSION['lang_code']);
$controller->set('spTextKeyword', $controller->spTextKeyword);
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$controller->spTextReport = $controller->getLanguageTexts('report', $_SESSION['lang_code']);
$controller->set('spTextReport', $controller->spTextReport);

$controller->layout = 'ajax';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        case "kwchecker":
            $controller->showQuickRankChecker($_POST);
            break;
        
        case "reportsum":
            $controller->showKeywordReportSummary($_POST);
            break;

        case "schedule":
            $controller->saveReportSchedule($_POST);
            break;
            
        default:
            $controller->showReports($_POST);
            break;
    }
} else {
    switch ($_GET['sec']) {
        
        case "show-info":
            $controller->showTimeReport($_GET);
            break;
            
        case "kwchecker":
            $controller->quickRankChecker($_GET);
            break;
            
        case "reportsum":
            $controller->showKeywordReportSummary($_GET);
            break;

        case "schedule":
            $controller->showReportsScheduler(false, $_GET);
            break;
                        
        default:
            $controller->showReports($_GET);
            break;
    }
}
