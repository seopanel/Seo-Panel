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

include_once("includes/sp-load.php");
if(empty($_SERVER['REQUEST_METHOD'])){

	// get all proxies
	include_once(SP_CTRLPATH."/proxy.ctrl.php");
	$proxyCtrler = new ProxyController();
	$proxyList = $proxyCtrler->__getAllProxys(false);

	foreach ($proxyList as $proxyInfo) {
		$proxyCtrler->checkStatus($proxyInfo['id']);
		echo "checking proxy: ". $proxyInfo['proxy'].":".$proxyInfo['port']."...\n";
	}

}else{
	showErrorMsg("<p style='color:red'>You don't have permission to access this page!</p>");
}
?>