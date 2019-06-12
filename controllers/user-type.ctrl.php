<?php
/**************************************************************************
*   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	          *
*   sendtogeo@gmail.com   											      *
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

include_once(SP_CTRLPATH . "/seotools.ctrl.php");

/**
 * Class defines all user type controller functions
 */
class UserTypeController extends Controller {
	
	public $userSpecFields = array(
	    'price', 'free_trial_period', 'keywordcount','websitecount', 'social_media_link_count', 'searchengine_count', 
	    'directory_submit_limit', 'directory_submit_daily_limit', 'site_auditor_max_page_limit', 'enable_email_activation',
	);
	
	/**
	 * constructor
	 */
    function __construct() {

    	// call parent constructor
    	parent::__construct();
    	
    	// get plugin access list
    	$pluginAccessList = $this->getPluginAccessSettings();
    	
    	// assign new fields to user spec for plugin access
    	foreach ($pluginAccessList as $pluginInfo) {
    		$this->userSpecFields[] = $pluginInfo['name'];
    	}
    	
    	// get seo tool access list
    	$toolAccessList = $this->getSeoToolAccessSettings();

    	// assign new fields to user spec for seo tool access
    	foreach ($toolAccessList as $toolInfo) {
    		$this->userSpecFields[] = $toolInfo['name'];
    	}
		
	}
	
	
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
		
		// Set the spec details for user type
		foreach ($userTypeList as $key => $userType) {
			$sql = "select * from user_specs where user_type_id=" . $userType['id'];
			$userTypeSpecList = $this->db->select($sql);
			
			foreach ($userTypeSpecList as $userTypeSpec) {
				$userType[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
			}
			$userTypeList[$key] = $userType;
		}
		
		// if subscription plugin active
		if ($this->isPluginSubsActive) {
			$currencyCtrler = new CurrencyController();
			$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
		}
		
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
			
			$listInfo['websitecount'] = stripslashes($listInfo['websitecount']);
			$listInfo['description'] = stripslashes($listInfo['description']);
			$listInfo['keywordcount'] = stripslashes($listInfo['keywordcount']);
			$listInfo['price'] = stripslashes($listInfo['price']);
			$listInfo['status'] = stripslashes($listInfo['status']);
			$this->set('post', $listInfo);
		
			// if subscription plugin active
			if ($this->isPluginSubsActive) {
				$currencyCtrler = new CurrencyController();
				$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
			}

			// get all plugin access list
			$pluginAccessList = $this->getPluginAccessSettings($userTypeId);
			$this->set('pluginAccessList', $pluginAccessList);

			// get all seo tool access list
			$toolAccessList = $this->getSeoToolAccessSettings($userTypeId);
			$this->set('toolAccessList', $toolAccessList);
			
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

		// Set the spec details for user type
		$sql = "select * from user_specs where user_type_id=" . $listInfo['id'];
		$userTypeSpecList = $this->db->select($sql);
			
		foreach ($userTypeSpecList as $userTypeSpec) {
			$listInfo[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
		}
		
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
		$errMsg['websitecount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['websitecount'])));
		$errMsg['keywordcount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['keywordcount'])));
		$errMsg['social_media_link_count'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['social_media_link_count'])));
		$errMsg['searchengine_count'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['searchengine_count'])));
		$errMsg['directory_submit_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['directory_submit_limit'])));
		$errMsg['directory_submit_daily_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['directory_submit_daily_limit'])));
		$errMsg['site_auditor_max_page_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['site_auditor_max_page_limit'])));
		
		// if subscription plugin active
		if ($this->isPluginSubsActive) {
			$errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
		}		
		
		// if no errors occured
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
						status = '".intval($listInfo['user_type_status'])."'
						where id={$listInfo['id']}";
				
				if ($this->db->query($sql)) {
					
					// Delete the exisiting spec values
					$sql = "delete from user_specs where user_type_id={$listInfo['id']}";
					$this->db->query($sql);
				
					// Get user type id of the current request
					$sql = "insert into user_specs(user_type_id,spec_column,spec_value) values ";
				
					// Loop through the data passed and create the value set
					foreach ($this->userSpecFields as $field) {
						$valueSet .=  "(" . $listInfo['id'] . ",'" . $field . "','" . addslashes($listInfo[$field]) . "'),";
					}
				
					$valueSet = trim($valueSet, ",");
					$sql .= $valueSet;
				
					$this->db->query($sql);
					$this->listUserTypes();
					exit;
				}
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
		$sql = "select id from usertypes where user_type='" . addslashes($userType) . "'";
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
		
		$valueSet = "";
		$this->set('post', $listInfo);
		$errMsg['user_type'] = formatErrorMsg($this->validate->checkBlank(trim($listInfo['user_type'])));
		$errMsg['websitecount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['websitecount'])));
		$errMsg['keywordcount'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['keywordcount'])));
		$errMsg['social_media_link_count'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['social_media_link_count'])));
		$errMsg['searchengine_count'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['searchengine_count'])));
		$errMsg['directory_submit_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['directory_submit_limit'])));
		$errMsg['directory_submit_daily_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['directory_submit_daily_limit'])));
		$errMsg['site_auditor_max_page_limit'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['site_auditor_max_page_limit'])));
		
		// if subscription plugin active
		if ($this->isPluginSubsActive) {
			$errMsg['price'] = formatErrorMsg($this->validate->checkNumber(trim($listInfo['price'])));
		}
				
		if(!$this->validate->flagErr){
			if (!$this->__checkUserType($listInfo['user_type'])) {
				
					// Set status value and sql
					$status = $listInfo['user_type_status'] == "" ? 1 : intval($listInfo['user_type_status']);				
					$sql = "insert into usertypes(user_type,description,status)
    				values('".addslashes($listInfo['user_type'])."','".addslashes($listInfo['description'])."',"
    						 . $status . ")";
					
					if ($this->db->query($sql)) {

						// Get user type id of the current request
						$userTypeId = $this->db->getMaxId('usertypes');						
						$sql = "insert into user_specs(user_type_id,spec_column,spec_value) values ";
						
						// Loop through the data passed and create the value set
						foreach ($this->userSpecFields as $field) {							
							$valueSet .=  "(" . $userTypeId . ",'" . $field . "','" . addslashes($listInfo[$field]) . "'),";
						}
						
						$valueSet = trim($valueSet, ",");
						$sql .= $valueSet;
						
						$this->db->query($sql);
						$this->listUserTypes();
						exit;
					}
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
		
		// if subscription plugin active
		if ($this->isPluginSubsActive) {
			$currencyCtrler = new CurrencyController();
			$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
		}
		
		// get all plugin access list
		$pluginAccessList = $this->getPluginAccessSettings();
		$this->set('pluginAccessList', $pluginAccessList);

		// get all seo tool access list
		$toolAccessList = $this->getSeoToolAccessSettings();
		$this->set('toolAccessList', $toolAccessList);
			
		$this->render('usertypes/new');
	}
	
	/*
	 * function to get plugin access id
	 */
	function getPluginAccessSettings($userTypeId = false) {
		
		$pluginAccessList = array();
		$pluginCtrler = new SeoPluginsController();
		$pluginList = $pluginCtrler->__getAllSeoPlugins();
		
		// if user type is passed
		if ($userTypeId) {
			$userTypeSettingList = $this->getUserTypeSpec($userTypeId, "system");
		}
		
		// loop through plugin list
		foreach ($pluginList as $i => $pluginInfo) {
			$pluginCol = 'plugin_' . $pluginInfo['id'];
			$pluginAccessList[$pluginInfo['id']] = array(
				'name' => $pluginCol,
				'label' => $pluginInfo['label'],
				'status' => $pluginInfo['status'],
				'value' => isset($userTypeSettingList[$pluginCol]) ? $userTypeSettingList[$pluginCol] : 0,
			);
			
		}
		
		return $pluginAccessList;
		
	}
	
	/*
	 * function to get plugin access id
	 */
	function getSeoToolAccessSettings($userTypeId = false, $toolId = false) {
		
		$toolAccessList = array();
		$toolCtrler = new SeoToolsController();
		$whereCond = $toolId ? "id=" . intval($toolId) : "1=1";
		$toolList = $toolCtrler->__getAllSeoTools($whereCond);
		
		// if user type is passed
		if ($userTypeId) {
			$userTypeSettingList = $this->getUserTypeSpec($userTypeId, "system");
		}
		
		// loop through tools list
		foreach ($toolList as $i => $toolInfo) {
			$toolCol = 'seotool_' . $toolInfo['id'];
			$toolAccessList[$toolInfo['id']] = array(
				'name' => $toolCol,
				'label' => $toolInfo['name'],
				'status' => $toolInfo['status'],
				'url_section' => $toolInfo['url_section'],
				'value' => isset($userTypeSettingList[$toolCol]) ? $userTypeSettingList[$toolCol] : 0,
			);
			
		}
		
		return $toolAccessList;
		
	}
	
	/*
	 * function to chekc whethere user type have access to seo tool
	 */
	function isUserTypeHaveAccessToSeoTool($userTypeId, $toolId) {

		// chekc for admin
		if (isAdmin()) {
			return true;
		} else {
			$toolAccessList = $this->getSeoToolAccessSettings($userTypeId, $toolId);
			return $toolAccessList[$toolId]['value'] ? true : false;
		}
		
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
		$sql .= " order by id";
		$uTypeList = $this->db->select($sql);
		$userTypeList = array();
		$priceList = array();
		
		// Set the spec details for user type
		foreach ($uTypeList as $userType) {
			$sql = "select * from user_specs where spec_category='system' and user_type_id=" . $userType['id'];
			$userTypeSpecList = $this->db->select($sql);
				
			foreach ($userTypeSpecList as $userTypeSpec) {
				$userType[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
				
				// if price column
				if ($userTypeSpec['spec_column'] == 'price') {
					$priceList[$userType['id']] = $userTypeSpec['spec_value'];
				}
				
			}
			
			$userTypeList[$userType['id']] = $userType;
		}
		
		// sort and create new array
		asort($priceList);
		$sortUserTypeList = array();
		
		foreach ($priceList as $usertTypeId => $price) {
			$sortUserTypeList[] = $userTypeList[$usertTypeId];
		}
		
		return $sortUserTypeList;
	}
	
	/**
	 * Function to get the user type spec details
	 */
	function getUserTypeSpec($userTypeId, $specCategory = '') {
		$specCategory = addslashes($specCategory);
		$userTypeId = intval($userTypeId);
		$sql = "select * from user_specs where user_type_id=" . $userTypeId;
		$sql .= !empty($specCategory) ? " and spec_category='$specCategory'" : "";
		$userTypeSpecList = $this->db->select($sql);
			
		foreach ($userTypeSpecList as $userTypeSpec) {
			$userTypeSpecData[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
		}
		
		return $userTypeSpecData;		
	}
	
	/**
	 * Function to get the user type spec details by user
	 */
	function getUserTypeSpecByUser($userId, $specCategory = '') {
		$sql = "select * from users where id=" . $userId;
		$userDetails = $this->db->select($sql);
		
		$sql = "select * from user_specs where user_type_id=" . $userDetails[0]['utype_id'];
		$sql .= !empty($specCategory) ? " and spec_category='$specCategory'" : "";
		$userTypeSpecList = $this->db->select($sql);
			
		foreach ($userTypeSpecList as $userTypeSpec) {
			$userTypeSpecData[$userTypeSpec['spec_column']] = $userTypeSpec['spec_value'];
		}
		
		return $userTypeSpecData;		
	}
	
	# function to get default user type
	function getDefaultUserTypeId() {
		$userTypeList = $this->getAllUserTypes();
		$defaultUserTypeId = !empty($userTypeList[0]['id']) ? $userTypeList[0]['id'] : false;
		return  $defaultUserTypeId;	
	}
	
	# function to get admin user ype id
	function getAdminUserTypeId() {
		$sql = "select id from usertypes where user_type='admin'"; 
		$userTypeInfo = $this->db->select($sql, true);
		return $userTypeInfo['id'];
	}
	
	/**
	 * Function to edit the user type plugin settings
	 * @params : $userTypeId - user type id, $pluginId, $className
	 * @return : Display the edit form
	 */
	function editPluginUserTypeSettings($userTypeId, $pluginId, $className, $post = array()) {
		
		// create plugin object
		$basePluginObj = new SeoPluginsController();
		$pluginInfo = $basePluginObj->__getSeoPluginInfo($pluginId);
		$pluginObj = $basePluginObj->createPluginObject($pluginInfo['name']);
		$pluginUserTypeObj = $pluginObj->createHelper($className);
	
		$userTypeList = $this->getAllUserTypes();
		$this->set('userTypeList', $userTypeList);		
		$userTypeId = !empty($userTypeId) ? $userTypeId : $userTypeList[0]['id'];
		
		// if user type id found
		if ($userTypeId) {
			$userTypeSpecList = $this->getUserTypeSpec($userTypeId, $pluginUserTypeObj->specCategory);
			
			// loop through the plugin spec col list and assign values
			$specColList = array();
			foreach ($pluginUserTypeObj->specColList as $specCol => $specColInfo) {
				$specColList[$specCol] = $specColInfo;
				
				// check for post request
				if (isset($post[$specCol])) {
					$specColList[$specCol]['spec_value'] = $post[$specCol];
				} else {
					$specColList[$specCol]['spec_value'] = isset($userTypeSpecList[$specCol]) ? $userTypeSpecList[$specCol] : $specColInfo['default'];
				}
				
			}
			
			$this->set('pluginId', $pluginId);
			$this->set('className', $className);
			$this->set('userTypeId', $userTypeId);
			$this->set('specColList', $specColList);
			$this->set('specText', $pluginUserTypeObj->pluginText);
			$this->set('spTextPanel', $this->getLanguageTexts('panel', $_SESSION['lang_code']));
			$this->render('usertypes/editpluginusertypesettings');
		}
		
	}

	/**
	 * Function to update plugin user type settings
	 */
	function updatePluginUserTypeSettings($settingsInfo) {

		$pluginId = intval($settingsInfo['plugin_id']);
		$basePluginObj = new SeoPluginsController();
		$pluginInfo = $basePluginObj->__getSeoPluginInfo($pluginId);
		$pluginObj = $basePluginObj->createPluginObject($pluginInfo['name']);
		$pluginUserTypeObj = $pluginObj->createHelper($settingsInfo['class_name']);
		
		// loop through plugin user type settings and validate
		foreach ($pluginUserTypeObj->specColList as $specCol => $specColInfo) {
			
			// if validation is set
			if (!empty($specColInfo['custom_validation'])) {
				$errMsg[$specCol] = formatErrorMsg($specColInfo['custom_validation']->$specColInfo['validation']($settingsInfo[$specCol]));
			} else if (!empty($specColInfo['validation'])) {
				$errMsg[$specCol] = formatErrorMsg($this->validate->$specColInfo['validation']($settingsInfo[$specCol]));
			}	
			
			// if error occured
			if (!empty($this->validate->flagErr) || !empty($errMsg[$specCol])) {
				$this->set('errMsg', $errMsg);
				$this->editPluginUserTypeSettings($settingsInfo['user_type_id'], $pluginId, $settingsInfo['class_name'], $settingsInfo);
				exit;
			}
			
		}		
		
		// loop through plugin user type settings
		foreach ($pluginUserTypeObj->specColList as $specCol => $specColInfo) {
			$this->updateUserTypeSpec($settingsInfo['user_type_id'], $specCol, $settingsInfo[$specCol], $pluginUserTypeObj->specCategory, $specColInfo['type']);	
		}
		
		// show the plugin user type settings
		$this->set('spTextSettings', $this->getLanguageTexts('settings', $_SESSION['lang_code']));
		$this->set('saved', true);
		$this->editPluginUserTypeSettings($settingsInfo['user_type_id'], $pluginId, $settingsInfo['class_name']);
		
	}
	
	/**
	 * update user type spec
	 */
	function updateUserTypeSpec($userTypeId, $specColumn, $specValue, $specCategory, $dataType) {		
		$specValue = Database::escapeData($specValue, $dataType);
		$specColumn = addslashes($specColumn);
		$specCategory = addslashes($specCategory);
		$userTypeId = intval($userTypeId);
		$sql = "Insert into user_specs(user_type_id, spec_column, spec_value, spec_category) values($userTypeId, '$specColumn', '$specValue', '$specCategory') 
				ON DUPLICATE KEY UPDATE spec_value='$specValue'";
		$this->db->query($sql);
	}
	
	/**
	 * function to get a particular user type spec value
	 */
	function __getUserTypeSpecValue($userTypeId, $specName) {
		$userTypeSpecList = $this->__getUserTypeInfo($userTypeId);
		return $userTypeSpecList[$specName] ? isset($userTypeSpecList[$specName]) : false;
	}
	
	/*
	 * function to check whether email activation enabled for the user
	 */
	function isEmailActivationEnabledForUserType($userTypeId) {
		$specValue = $this->__getUserTypeSpecValue($userTypeId, "enable_email_activation");
		return $specValue;
	}
	
	/**
	 * function to get renew user typelist
	 */
	function getRenewUserTypeList($userTypeId) {
		
		$userTypeCtrler = new UserTypeController();
		$userTypeInfo = $userTypeCtrler->__getUserTypeInfo($userTypeId);
		$typeList = $userTypeCtrler->getAllUserTypes();
		$userTypeList = array();
			
		// loop through the list to find the exact user types - remove the plans below current plan, disable free trial plans
		$startAdd = false;
		foreach ($typeList as $typeInfo) {
		
			// same user type selected
			if ($typeInfo['id'] == $userTypeId) {
				$startAdd = true;
				if ($userTypeInfo['free_trial_period'] > 0) continue;
			}
		
			// start to add
			if ($startAdd) {
				$userTypeList[$typeInfo['id']] = $typeInfo;
			}
		
		}
		
		return $userTypeList;
		
	}
	
	/*
	 * function to get all user types
	 */
	function __getAllUserTypeList() {
		$list = $this->dbHelper->getAllRows("usertypes");
		return $list;
	}
	
}
?>