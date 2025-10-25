<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
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
include_once(SP_CTRLPATH."/index.ctrl.php");
include_once(SP_CTRLPATH."/directory.ctrl.php");
$controller = New IndexController();
$controller->view->menu = 'home';

// set site details according to customizer plugin
$custSiteInfo = getCustomizerDetails();
if (!empty($custSiteInfo['site_title'])) $controller->set('spTitle', $custSiteInfo['site_title']);
if (!empty($custSiteInfo['site_description'])) $controller->set('spDescription', $custSiteInfo['site_description']);
if (!empty($custSiteInfo['site_keywords'])) $controller->set('spKeywords', $custSiteInfo['site_keywords']);

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	switch($_GET['sec']) {		
		case "news":
			include_once(SP_CTRLPATH."/information.ctrl.php");
			$infoCtrler = new InformationController();
			$infoCtrler->showNews($_GET);
			break;
		
		case "showdiv":
			$areaId = $_GET['div_id'];
			?>
			<script type="text/javascript">
			$('#dialogContent').html($("#<?php echo $areaId;?>").html());
			</script>
			<?php
			break;
			
        case "sync_all_se":
            include_once(SP_CTRLPATH."/searchengine.ctrl.php");
            $seCtrler = new SearchEngineController();
            $seCtrler->doSyncSearchEngines(true, true);
            break;

		default:
			$controller->index($_GET);
			break;
	}
} elseif($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch($_POST['sec']) {	    
	    default:
			$controller->index($_POST);
			break;
	}
}
?>