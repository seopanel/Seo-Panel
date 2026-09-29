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

include_once(SP_CTRLPATH . "/feature_tour.ctrl.php");
$controller = new FeatureTourController();

// state-changing action - POST only (a GET-triggered dismiss would be
// forgeable by a bare <img src="..."> on any page a logged-in user has
// open; see websites.php's own move off GET for the same reasoning)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['sec']) {
        case 'dismiss':
            $controller->dismissTour();
            break;
    }
} else {
    // read-only status check - no state change, so GET is fine here
    switch ($_GET['sec']) {
        case 'connection_status':
            $controller->getConnectionStatus();
            break;
    }
}
?>
