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

/**
 * Class defines all functions for managing user API
 * 
 * @author Seo panel
 *
 */
class UserAPI extends Seopanel{
	
	/**
	 * the main controller to get details for api
	 * @var Object
	 */
	var $ctrler;
	
	function UserAPI() {
		include_once(SP_CTRLPATH . "/user.ctrl.php");
		$this->ctrler = new UserController();
	}

	/**
	 * function to get user information 
	 * @param Array $info			The input details to process the api
	 * 		$info['id']  		    The id of the user	- Mandatory
	 * @return Array $returnInfo  	Contains informations about user
	 */
	function getUserInfo($info) {
		$userId = intval($info['id']);
		$returnInfo = array();
		
		// validate the user ifd and user info
		if (!empty($userId)) {
			if ($userInfo = $this->ctrler->__getUserInfo($userId)) {
				$userInfo['password'] = '';
				$returnInfo['response'] = 'success';
				$returnInfo['result'] = $userInfo;
				return $returnInfo;
			}
		}
		
		$returnInfo['response'] = 'Error';
		$returnInfo['error_msg'] = "The invalid user id provided";		
		return 	$returnInfo;
	}
	
	/**
	 * function to create user
	 * @param Array $info			The input details to process the api
	 * 		$info['username']		The username of the user	- Mandatory
	 * 		$info['password']		The password of the user	- Mandatory
	 * 		$info['first_name']		The first name f the user	- Mandatory
	 * 		$info['last_name']		The last name of user	- Optional
	 * 		$info['email']			The user email	- Mandatory
	 * 		$info['type_id']		The user type id of user - default[2]	- Optional
	 * 		$info['status']			The status of the user - default[1]	- Optional
	 * @return Array $returnInfo  	Contains details about the operation succes or not
	 */
	function createUser($info) {
		$userInfo = $info;
		$userInfo['userName'] = $info['username'];
		$userInfo['firstName'] = $info['first_name'];
		$userInfo['lastName'] = $info['last_name'];
		$userInfo['confirmPassword'] = $userInfo['password'];
		$return = $this->ctrler->createUser($userInfo, false);
		
		// if user creation is success
		if ($return[0] == 'success') {
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = $return[1];
			$returnInfo['user_id'] = $this->ctrler->db->getMaxId('users');
		} else {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = $return[1];
		}
		
		return 	$returnInfo;
		
	}
	
	/**
	 * function to update user
	 * @param Array $info			The input details to process the api
	 * 		$info['id']				The id of the user	- Mandatory
	 * 		$info['username']		The username of the user	- Optional
	 * 		$info['password']		The password of the user	- Optional
	 * 		$info['first_name']		The first name f the user	- Optional
	 * 		$info['last_name']		The last name of user	- Optional
	 * 		$info['email']			The user email	- Optional
	 * 		$info['type_id']		The user type id of user 	- Optional
	 * 		$info['status']			The status of the user	- Optional
	 * @return Array $returnInfo  	Contains details about the operation succes or not
	 */
	function updateUser($info) {
		
		$userId = intval($info['id']);
		
		// if user exists
		if ($userInfo = $this->ctrler->__getUserInfo($userId)) {
			
			$userInfo['oldName'] = $userInfo['username'];
			$userInfo['oldEmail'] = $userInfo['email'];
			
			// loop through inputs
			foreach ($info as $key => $val) {
				$userInfo[$key] = $val;
			}
			
			// updte user info
			$userInfo['userName'] = $userInfo['username'];
			$userInfo['firstName'] = $userInfo['first_name'];
			$userInfo['lastName'] = $userInfo['last_name'];
			$userInfo['confirmPassword'] = $userInfo['password'];
			$return = $this->ctrler->updateUser($userInfo, false);
			
			// if user creation is success
			if ($return[0] == 'success') {
				$returnInfo['response'] = 'success';
				$returnInfo['result'] = $return[1];
			} else {
				$returnInfo['response'] = 'Error';
				$returnInfo['error_msg'] = $return[1];
			}
			
		} else {

			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "The invalid user id provided";
		}
		
		return 	$returnInfo;
		
	}
	
	/**
	 * function to delete user
	 * @param Array $info				The input details to process the api
	 * 		$info['id']					The id of the user	- Mandatory
	 * @return Array $returnInfo  	Contains details about the operation success or not
	 */
	function deleteUser($info) {
		
		$userId = intval($info['id']);
		
		// if user exists
		if ( ($userId != 1) && $userInfo = $this->ctrler->__getUserInfo($userId)) {
			
			// update user call as api call
			$this->ctrler->__deleteUser($userId);
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = "Successfully deleted user";
			
		} else {
	
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "The invalid user id provided";
		}
	
		return 	$returnInfo;
		
	}
	
	/**
	 * function added by Rex, 2020Media.com
	 * function to get user id from username
	 * @param Array $info			The input details to process the api
	 * 		$info['username']    	The username of the user  - Mandatory
	 * @return Array $returnInfo    Contains id of user
	 */
	function getUserName($info) {
	 	
 		$username = $info['username'];
		$returnInfo = array();
	
		// validate the user ifd and user info
		if (!empty($username)) {
			if ($userInfo = $this->ctrler->__checkUserName($username)) {
				$returnInfo['response'] = 'success';
				$returnInfo['result'] = $userInfo;
				return $returnInfo;
			}
		}
	
		$returnInfo['response'] = 'Error';
		$returnInfo['error_msg'] = "Invalid username provided";
		return  $returnInfo;
	}
	
}
?>