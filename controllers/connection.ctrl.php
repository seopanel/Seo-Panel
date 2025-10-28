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

include_once(SP_CTRLPATH . "/googleapi.ctrl.php");

# class defines all connection functions
class ConnectionController extends Controller {
	
	var $sourceList =  array(
		'google' => 'GoogleAPIController',
	);
	
		
	/**
	 * Function to display connections
	 */
	function listConnections($info=[]){
		
		$userId = isLoggedIn();
		$sourceList = array();
		$userTokenCtrler = new UserTokenController();
		
		// loop through the list
		foreach ($this->sourceList as $name => $class ) {
			
			// check connection status
			$tokenInfo = $userTokenCtrler->getUserToken($userId, $name);
			
			// if token exists
			if (!empty($tokenInfo['id'])) {
				$status = true;
			} else {
				$status = false;
				
				if (SP_DEMO) {
					$authUrlInfo = array('auth_url' => '#');
				} else {
					$sourceCtrler = new $class();
					$authUrlInfo = $sourceCtrler->getAPIAuthUrl($userId);
				}
			}
			
			$sourceList[] = array('name' => $name, 'status' => $status, 'auth_url_info' => $authUrlInfo);
		}
		
		$this->set('list', $sourceList);
		$this->render('myaccount/connection_list');
	}
	
	/*
	 * process connection return action
	 */
	function processConnectionReturn($info) {
		
		$userId = isLoggedIn();
		$className = $this->sourceList[$info['category']];
		
		// if class existing for process
		if (!empty($className)) {
			$sourceCtrler = new $className();
			$ret = $sourceCtrler->createUserAuthToken($userId, $info['code']);
			
			// if token created successfull
			if ($ret['status']) {
				$this->set('successMsg', "Successfully connected to " . $info['category']);
			} else {
				$this->set('errorMsg', $ret['msg']);
			}
			
		} else {
			$this->set('errorMsg', "Class not found to process requested action.");
		}
		
		$this->listConnections();
		
	}
	
	/*
	 * process disconnection action
	 */
	function processDisconnection($info) {
		$userId = isLoggedIn();$className = $this->sourceList[$info['category']];
		
		// if class existing for process
		if (class_exists($className)) {
			$sourceCtrler = new $className();
			$ret = $sourceCtrler->removeUserAuthToken($userId);
			$this->set('successMsg', "Successfully disconnected from " . $info['category']);
		} else {
			$this->set('errorMsg', "Class not found to process requested action.");
		}
		
		$this->listConnections();
	}
	
}
?>