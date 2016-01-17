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
include_once(SP_CTRLPATH."/adminpanel.ctrl.php");
$controller = new AdminPanelController();
$controller->view->menu = 'adminpanel';

$controller->set('spTitle', 'Seo Panel: User control panel for manage settings');
$controller->set('spDescription', 'User control panel for manage settings');
$controller->set('spKeywords', 'Seo Panel settings,User control panel,manage seo panel settings');
$controller->spTextPanel = $controller->getLanguageTexts('panel', $_SESSION['lang_code']);
$controller->set('spTextPanel', $controller->spTextPanel);
$info = $_REQUEST;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
    }
} else {
    switch ($_GET['sec']) {
        case "newweb":
            $info['start_script'] = 'websites.php?sec=new&check=1';
            $controller->index($info);
            break;
        
        case "myprofile":
            $info['menu_selected'] = 'my-profile';
            $info['start_script'] = 'users.php?sec=my-profile';
            $controller->index($info);
            break;

        default:
            $controller->index($_GET);
            break;
    }
}
