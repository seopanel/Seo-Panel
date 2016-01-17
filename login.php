<?php
/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   *
 *   sendtogeo@gmail.com   						   *
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
if (isLoggedIn() && ($_GET['sec'] != 'logout')) {
    redirectUrl(SP_WEBPATH."/");
}
include_once(SP_CTRLPATH."/user.ctrl.php");
$controller = new UserController();
$controller->view->menu = 'login';

$controller->set('spTitle', 'Seo Panel: Login section');
$controller->set('spDescription', 'Login to Seo Panel and utilise seo tools and plugins to increase the perfomance of your site.');
$controller->set('spKeywords', 'Seo Panel Login section');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['sec']) {
        
        case "login":
            $controller->login();
            break;

        case "requestpass":
            $controller->set('spTitle', 'Seo panel forgot password');
            $controller->requestPassword($_POST['email']);
            break;
        
        default:
            $controller->index();
            break;
    }
} else {
    switch ($_GET['sec']) {
        
        case "logout":
            $controller->logout();
            break;
        
        case "forgot":
            $controller->set('spTitle', 'Seo Panel forgot password');
            $controller->forgotPasswordForm();
            break;

        default:
            $controller->index($_GET);
            break;
    }
}
