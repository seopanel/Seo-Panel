<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   		   *
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
include_once(SP_CTRLPATH."/api.ctrl.php");
include_once(SP_CTRLPATH."/settings.ctrl.php");
$controller = new APIController();
$controller->view->menu = 'adminpanel';
$controller->layout = 'ajax';
$controller->set('spTextPanel', $controller->getLanguageTexts('panel', $_SESSION['lang_code']));
$controller->set('spTextSettings', $controller->getLanguageTexts('settings', $_SESSION['lang_code']));
$controller->spTextAPI = $controller->getLanguageTexts('api', $_SESSION['lang_code']);
$controller->set('spTextAPI', $controller->spTextAPI);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        default:
            $controller->showAPIConnectionManager($_POST);
            break;
            
    }
} else {
    switch ($_GET['sec']) {
        
        default:
            $controller->showAPIConnectionManager($_GET);
            break;
    }
}
