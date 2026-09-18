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

include_once(SP_CTRLPATH . "/setup_wizard.ctrl.php");
$controller = new SetupWizardController();

switch ($_REQUEST['sec']) {
    case 'save_step':
        $controller->saveWizardStep($_REQUEST);
        break;
    case 'dismiss':
        $controller->dismissWizard();
        break;
    case 'create_website':
        $controller->wizardCreateWebsite($_REQUEST);
        break;
}
?>
