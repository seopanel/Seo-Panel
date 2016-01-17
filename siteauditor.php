<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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
include_once(SP_CTRLPATH."/siteauditor.ctrl.php");
$controller = new SiteAuditorController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('spTextTools', $controller->getLanguageTexts('seotools', $_SESSION['lang_code']));
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->spTextSA = $controller->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
$controller->set('spTextSA', $controller->spTextSA);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        case "create":
            $controller->set('spTextSettings', $controller->getLanguageTexts('settings', $_SESSION['lang_code']));
            $controller->createProject($_POST);
            break;
            
        case "update":
            $controller->set('spTextSettings', $controller->getLanguageTexts('settings', $_SESSION['lang_code']));
            $controller->updateProject($_POST);
            break;
            
        case "activateall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__changeStatus($id, 1);
                }
            }
            $controller->showAuditorProjects($_POST);
            break;
            
        case "inactivateall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__changeStatus($id, 0);
                }
            }
            $controller->showAuditorProjects($_POST);
            break;
            
        case "deleteall":
            if (!empty($_POST['ids'])) {
                foreach ($_POST['ids'] as $id) {
                    $controller->__deleteProject($id);
                }
            }
            $controller->showAuditorProjects($_POST);
            break;
            
        case "showreport":
            $controller->showProjectReport($_POST);
            break;
            
        case "importlinks":
            $controller->importLinks($_POST);
            break;
        
        default:
            $controller->showAuditorProjects($_POST);
            break;
    }
} else {
    switch ($_GET['sec']) {
        
        case "Activate":
            $controller->__changeStatus($_GET['project_id'], 1);
            $controller->showAuditorProjects($_GET);
            break;
        
        case "Inactivate":
            $controller->__changeStatus($_GET['project_id'], 0);
            $controller->showAuditorProjects($_GET);
            break;
        
        case "delete":
            $controller->__deleteProject($_GET['project_id']);
            $controller->showAuditorProjects($_GET);
            break;
        
        case "new":
            $controller->set('spTextSettings', $controller->getLanguageTexts('settings', $_SESSION['lang_code']));
            $controller->newProject($_GET);
            break;
        
        case "edit":
            $controller->set('spTextSettings', $controller->getLanguageTexts('settings', $_SESSION['lang_code']));
            $controller->editProject($_GET['project_id']);
            break;
        
        case "showrunproject":
            $controller->showRunProject($_GET['project_id']);
            break;
        
        case "runproject":
            $controller->runProject($_GET['project_id']);
            break;
            
        case "viewreports":
            $controller->viewReports($_GET);
            break;
        
        case "pagedetails":
            $controller->viewPageDetails($_GET);
            break;
            
        case "recheckreport":
            $controller->recheckReportPages($_GET['project_id']);
            $controller->showRunProject($_GET['project_id']);
            break;
            
        case "showreport":
            $controller->showProjectReport($_GET);
            break;
            
        case "checkscore":
            $controller->checkPageScore($_GET);
            break;
            
        case "deletepage":
            $controller->__deleteReportPage($_GET['report_id']);
            $controller->loadReportsPage($_GET);
            break;
            
        case "croncommand":
            $controller->showCronCommand();
            break;

        case "showsettings":
            $settingCtrler = $controller->createController('Settings');
            $settingCtrler->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
            $settingCtrler->spTextSettings = $controller->getLanguageTexts('settings', $_SESSION['lang_code']);
            $settingCtrler->set('spTextSettings', $settingCtrler->spTextSettings);
            $settingCtrler->set('headLabel', $controller->spTextSA['Site Auditor Settings']);
            $settingCtrler->showSystemSettings('siteauditor');
            break;
        
        case "importlinks":
            $controller->showImportProjectLinks($_GET);
            break;

        default:
            $controller->showAuditorProjects($_GET);
            break;
    }
}
