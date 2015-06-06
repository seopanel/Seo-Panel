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
 * Class defines all user type controller functions
 */
class UserTypeController extends Controller {

	/**
	 * Function to list all the available user types
	 * @params : Array of values to be passed
	 * @return : Display the list
	 */
	function listUserTypes($info = '') {
		$info['pageno'] = intval($info['pageno']);
		$sql = "SELECT * FROM usertypes where id!=1";

		# pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('user-types-manager.php?userid='.$info['userid']);
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		$userTypeList = $this->db->select($sql);
		$this->set('pageNo', $info['pageno']);
		$this->set('list', $userTypeList);
		$this->render('usertypes/list');
	}

	/**
	 * Function to edit the user type selected
	 * @params : $userTypeId - user type id, $listInfo - data array
	 * @return : Display the edit form
	 */
	function editUserType($userTypeId, $listInfo='') {

		$userTypeId = intval($userTypeId);
		if (!empty($userTypeId)) {
			if (empty($listInfo)) {
				$listInfo = $this->__getUserTypeInfo($userTypeId);
				$listInfo['old_user_type'] = $listInfo['user_type'];
			}
			$listInfo['num_websites'] = stripslashes($listInfo['num_websites']);
			$listInfo['description'] = stripslashes($listInfo['description']);
			$listInfo['num_keywords'] = stripslashes($listInfo['num_keywords']);
			$listInfo['price'] = stripslashes($listInfo['price']);
			$listInfo['status'] = stripslashes($listInfo['status']);
			$this->set('post', $listInfo);

			$this->render('usertypes/edit');
			exit;
		}
		$this->listUserTypes();
	}

	/**
	 * Function to edit the user type selected
	 * @params : $userTypeId - user type id
	 * @return : returns the data
	 */
	function __getUserTypeInfo($userTypeId) {
		$userTypeId = intval($userTypeId);
		$sql = "select * from usertypes where id=$userTypeId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	/**
	 * Function to update the user type selected
	 * @params : $listInfo - post values
	 * @return : returns the data
	 */
	function updateUserType($listInfo) {

		$listInfo['id'] = intval($listInfo['id']);
		$this->set('post', $listInfo);
		$errMsg['user_type'] = formatErrorMsg($this->validate->checkBlank(trim($listInfo['user_type'])));
		$errMsg['num_websites'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['num_websites'])));
		$errMsg['num_keywords'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['num_keywords'])));
		$errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
		if (!$this->validate->flagErr){

			if($listInfo['user_type'] != $listInfo['old_user_type']){
				if ($this->__checkUserType($listInfo['user_type'], $listInfo['id'])) {
					$errMsg['user_type'] = formatErrorMsg($this->spTextWeb['User Type already exist']);
					$this->validate->flagErr = true;
				}
			}

			if (!$this->validate->flagErr) {
				$sql = "update usertypes set
						user_type = '".addslashes($listInfo['user_type'])."',
						description = '".addslashes($listInfo['description'])."',
						num_websites = '".intval($listInfo['num_websites'])."',
						num_keywords = '".intval($listInfo['num_keywords'])."',
						price = '".floatval($listInfo['price'])."',
						status = '".intval($listInfo['user_type_status'])."'
						where id={$listInfo['id']}";

				$this->db->query($sql);
				$this->listUserTypes();
				exit;
			}
		}
		$this->set('errMsg', $errMsg);
		$this->editUserType($listInfo['id'], $listInfo);
	}

	/**
	 * Function to check the user type selected
	 * @params : $userType
	 * @return : boolean
	 */
	function __checkUserType($userType, $userTypeId = false) {
		$sql = "select id from usertypes where user_type='".addslashes($userType)."'";
		$sql .= empty($userTypeId) ? "" : " and id!=" . intval($userTypeId);
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	/**
	 * Function to create a new user type
	 * @params : $listInfo - post values
	 * @return : returns the data
	 */
	function createUserType($listInfo) {

		$this->set('post', $listInfo);
		$errMsg['user_type'] = formatErrorMsg($this->validate->checkBlank(trim($listInfo['user_type'])));
		$errMsg['num_websites'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['num_websites'])));
		$errMsg['num_keywords'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['num_keywords'])));
		$errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
		if(!$this->validate->flagErr){
			if (!$this->__checkUserType($listInfo['user_type'])) {
					$sql = "insert into usertypes(user_type,description,num_websites,num_keywords,price,status)
    				values('".addslashes($listInfo['user_type'])."','".addslashes($listInfo['description'])."',
    				'".intval($listInfo['num_websites'])."','".intval($listInfo['num_keywords'])."',
    				'".floatval($listInfo['price'])."'," . intval($listInfo['user_type_status']) . ")";
					$this->db->query($sql);
					$this->listUserTypes();
					exit;
			} else {
				$errMsg['user_type'] = formatErrorMsg($this->spTextWeb['User Type already exist']);
			}
		}
		$this->set('errMsg', $errMsg);
		$this->newUserType($listInfo);
	}

	/**
	 * Function to display add user type  form
	 * @params : $info - data values array
	 * @return : returns the data
	 */
	function newUserType($info='') {
		$this->render('usertypes/new');
	}

	/**
	 * Function to change the user type status
	 * @params :  - $userTypeId - user type id, $status - user type id to be updated
	 * @return : None
	 */
	function __changeStatus($userTypeId, $status){

		$userTypeId = intval($userTypeId);
		$sql = "update usertypes set status=$status where id=$userTypeId";
		$this->db->query($sql);
	}

	/**
	 * Function to delete the selected user type
	 * @params : $userTypeId - user type id to be deleted
	 * @return : None
	 */
	function __deleteUserType($userTypeId){

		$userTypeId = intval($userTypeId);
		$sql = "delete from usertypes where id=$userTypeId";
		$this->db->query($sql);
	}

	/**
	 * function to get all user types
	 */
	function getAllUserTypes($includeAdmin = false) {
		$sql = "select * from usertypes where status=1";
		$sql .= empty($includeAdmin) ? " and id!=1" : "";
		$userTypeList = $this->db->select($sql);
		return $userTypeList;
	}
}
?>