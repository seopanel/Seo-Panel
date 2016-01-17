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


switch ($_GET['sec']) {

    case "subscription":
        
        // if logged in
        if (isLoggedIn()) {
            include_once(SP_CTRLPATH."/seoplugins.ctrl.php");
            $seopluginCtrler = new SeoPluginsController();
            
            if ($seopluginCtrler->isPluginActive("Subscription")) {
                    
                // verify the payment plugin id and invoice id in the session
                if (!empty($_SESSION['payment_plugin_id']) && !empty($_SESSION['invoice_id'])) {
                    $pluginCtrler = $seopluginCtrler->createPluginObject("Subscription");
                    $info['status'] ="cancelled";
                    $pluginCtrler->orderCtrler->updateInvoiceInfo($_SESSION['invoice_id'], $info);
                }
                
                redirectUrl(SP_WEBPATH."/admin-panel.php?sec=myprofile&cancel=1");
            } else {
                showErrorMsg("Not authorized to access this page!");
            }
        } else {
            redirectUrl(SP_WEBPATH . "/register.php?cancel=1");
        }
        
        break;
    
    default:
        echo "Not authorized to access this page!";
}
