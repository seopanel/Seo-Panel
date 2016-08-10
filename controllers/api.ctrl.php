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

// class defines all api controller functions
class APIController extends Controller {
	
	// function to show api connection details
	function showAPIConnectionManager($info) {
		$settingCtrler = new SettingsController();
		$stnList = $settingCtrler->__getAllSettings(true, 1, 'api');
		
		// loop through settings
		$apiInfo = array();
		foreach ($stnList as $settingInfo) {
			$apiInfo[$settingInfo['set_name']] = $settingInfo['set_val'];
		}
		
		$apiInfo['api_url'] = SP_WEBPATH . "/" . SP_API_FILE;
		$this->set('apiInfo', $apiInfo);
		$this->render('api/showapiconnect');
	}
	
	// get api credentails of the system
	function getAPICredentials() {
		$apiCredInfo =  array();
		$settingCtrler = new SettingsController();
		$stList = $settingCtrler->__getAllSettings(true, 1, 'api');
		
		// loop through settings values
		foreach ($stList as $stInfo) {
			$apiCredInfo[$stInfo['set_name']] = $stInfo['set_val'];
		}
		
		return $apiCredInfo;
	}
	
	// function to verify api credentials passed
	function verifyAPICredentials($info) {
		$apiCredInfo = $this->getAPICredentials();
		
		if ( ($apiCredInfo['SP_API_KEY'] == $info['SP_API_KEY']) && ($apiCredInfo['API_SECRET'] == $info['API_SECRET']) ) {
			return true;
		}
		
		return false;
	}
	
}
?>