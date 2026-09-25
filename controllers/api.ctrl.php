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
	
	/*
	 * function to verify api credentials passed - fails closed if either
	 * stored credential is empty (an empty API_SECRET previously
	 * authenticated successfully against a request that also omitted
	 * API_SECRET, since '' == '' is true), rate-limits repeated failures
	 * per calling IP (reuses AIVisibilityController's existing rate-limit
	 * bucket, same idiom as MCP/Local AI/AI Perception), and compares
	 * with hash_equals() instead of == (constant-time, not vulnerable to
	 * a timing side-channel on the secret).
	 */
	function verifyAPICredentials($info) {
		$apiCredInfo = $this->getAPICredentials();

		if (empty($apiCredInfo['SP_API_KEY']) || empty($apiCredInfo['API_SECRET'])) {
			return false;
		}

		if (!$this->__checkApiAuthRateLimit()) {
			return false;
		}

		$suppliedKey = (string) ($info['SP_API_KEY'] ?? '');
		$suppliedSecret = (string) ($info['API_SECRET'] ?? '');

		return hash_equals($apiCredInfo['SP_API_KEY'], $suppliedKey) && hash_equals($apiCredInfo['API_SECRET'], $suppliedSecret);
	}

	// func to check+increment this caller IP's API-auth attempt bucket,
	// reusing AIVisibilityController's existing rate-limit table/logic -
	// bounds how many credential guesses an attacker can throw at this
	// endpoint per minute regardless of source
	function __checkApiAuthRateLimit() {
		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$aivCtrler = new AIVisibilityController();
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		return $aivCtrler->__checkRateLimit('api-auth:' . $ip, 30);
	}

	// func to regenerate SP_API_KEY - POST-only, admin-only (apimanager.php
	// already gates the whole request lifecycle with checkAdminLoggedIn()).
	// Does not touch API_SECRET, kept separate so rotating one never
	// accidentally rotates the other - same pattern as the scheduler ping
	// secret's saveSchedulePingSettings()/regeneratePingSecret() split.
	function regenerateAPIKey() {
		$apiKey = bin2hex(random_bytes(24));
		$this->db->query("UPDATE settings SET set_val='" . addslashes($apiKey) . "' WHERE set_name='SP_API_KEY'");
		$this->showAPIConnectionManager([]);
	}

	// func to regenerate API_SECRET - see regenerateAPIKey() above
	function regenerateAPISecret() {
		$apiSecret = bin2hex(random_bytes(24));
		$this->db->query("UPDATE settings SET set_val='" . addslashes($apiSecret) . "' WHERE set_name='API_SECRET'");
		$this->showAPIConnectionManager([]);
	}

}
?>