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

# class defines all user controller functions
class UserController extends Controller{	
	
	# index function
	function index($info=[]){
		
		if(!isset($info['referer'])) {
			$info['red_referer'] = isValidReferer($_SERVER['HTTP_REFERER']);
			$this->set('post', $info);
		}
				
		$this->render('common/login');
	}
	
	# function to set login session items
	function setLoginSession($userInfo) {
		@Session::setSession('userInfo', $userInfo);
		@Session::setSession('lang_code', $userInfo['lang_code']);
		@Session::setSession('text', '');
	}
	
	# login function
	function login(){	    
	    
	    $_POST['userName'] = sanitizeData($_POST['userName']);
		$this->set('post', $_POST);
		$errMsg['userName'] = formatErrorMsg($this->validate->checkBlank($_POST['userName']));
		$errMsg['password'] = formatErrorMsg($this->validate->checkBlank($_POST['password']));
		if(!$this->validate->flagErr){
			$sql = "select u.*,ut.user_type from users u,usertypes ut where u.utype_id=ut.id and u.username='".addslashes($_POST['userName'])."'";
			$userInfo = $this->db->select($sql, true);
			if(!empty($userInfo['id'])){
				if($userInfo['password'] == md5($_POST['password'])){
					
					// get user type spec details and verify whether to check activation or not
					$activationStatus = true;
					$userTypeCtrler = new UserTypeController();
					if ($userTypeCtrler->isEmailActivationEnabledForUserType($userInfo['utype_id'])) {
						if ($userInfo['confirm'] == 0) {
							$activationStatus = false;
						}
					}
					
					// check for user status and activation
					if($userInfo['status'] && $activationStatus){
					    
    					// if login after first installation
                	    if (!empty($_POST['lang_code']) && ($_POST['lang_code'] != 'en')) {
                	    	$_POST['lang_code'] = addslashes($_POST['lang_code']);
                	        $sql = "UPDATE `settings` SET set_val='".addslashes($_POST['lang_code'])."' WHERE set_name='SP_DEFAULTLANG'";
                	        $this->db->query($sql);
                	        
                	        $sql = "UPDATE users SET lang_code='".addslashes($_POST['lang_code'])."' WHERE id=1";
                	        $this->db->query($sql);
                	        
                	        $userInfo['lang_code'] = $_POST['lang_code'];
                	    }
                	    
                	    // update timezone
                	    if (!empty($_POST['time_zone'])) {
                	    	$sql = "UPDATE `settings` SET set_val='".addslashes($_POST['time_zone'])."' WHERE set_name='SP_TIME_ZONE'";
                	    	$this->db->query($sql);
                	    }
					    
						$uInfo['userId'] = $userInfo['id'];
						$uInfo['userType'] = $userInfo['user_type'];
						$uInfo['userTypeId'] = $userInfo['utype_id'];
						$uInfo['lang_code'] = $userInfo['lang_code'];
						$this->setLoginSession($uInfo);
						
						if ($referer = isValidReferer($_POST['red_referer'])) {
							redirectUrl($referer);
						} else {
						    $extArgs = !empty($_POST['source']) ? "?source=" . $_POST['source'] : "";
							redirectUrl(SP_WEBPATH."/" . $extArgs);
						}
												
					}else{
						$msgTxt = $activationStatus ? $_SESSION['text']['login']["User inactive"] : $_SESSION['text']['login']["user_not_activated_msg"];
						$errMsg['userName'] = formatErrorMsg($msgTxt);
					}
				}else{
					$errMsg['password'] = formatErrorMsg($_SESSION['text']['login']["Password incorrect"]);
				}
			}else{
				$errMsg['userName'] = formatErrorMsg($_SESSION['text']['login']["Login incorrect"]);
			}
		}
		$this->set('errMsg', $errMsg);
		$this->index($_POST);
	}
	
	# func to confirm the user registration
	function confirmUser($confirmCode) {
		$confirmCode = addslashes($confirmCode);
		$sql = "select id from users where confirm_code='$confirmCode'";
		$userInfo = $this->db->select($sql, true);
		$error = "";
		
		if(!empty($userInfo['id'])){
			
			$sql = "update users set confirm=1,status=1 where id=".$userInfo['id'];
			if($this->db->query($sql)){
				$this->set('confirm', true);
			}else{
				$error = showErrorMsg($this->spTextRegister['user_confirm_content_1'], false, true);
			}
			
		} else {
			$error = showErrorMsg($this->spTextRegister['user_confirm_content_1'], false, true);
		}
		
		$this->set('error', $error);
		$this->render('common/registerconfirm');
	}
	
	# register function
	function register($info=[]){
		
		$seopluginCtrler =  new SeoPluginsController();
		$subscriptionActive = false;
		$utypeCtrler = new UserTypeController();
		$this->set('post', $info);
		
		// check whetehr plugin installed or not
		if ($seopluginCtrler->isPluginActive("Subscription")) {
			$subscriptionActive = true;
			$userTypeList = $utypeCtrler->getAllUserTypes();
			$this->set('userTypeList', $userTypeList);
			
			// include available payment gateways
			include SP_PLUGINPATH . "/Subscription/paymentgateway.ctrl.php";
			$pgCtrler = new PaymentGateway();
			$pgList = $pgCtrler->__getAllPaymentGateway();
			$this->set('pgList', $pgList);
			$this->set('defaultPgId', $pgCtrler->__getDefaultPaymentGateway());
			$this->set('spTextSubscription', $this->getLanguageTexts('subscription', $_SESSION['lang_code']));
    		
    		$currencyCtrler = new CurrencyController();
    		$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
			
		} else {
			$this->set('defaultUserTypeId', $utypeCtrler->getDefaultUserTypeId());	
		}
		
		$this->set('subscriptionActive', $subscriptionActive);
		$this->render('common/register');
	}
	
	# function to show pricing
	function showPricing(){
		
		$seopluginCtrler =  new SeoPluginsController();
		$utypeCtrler = new UserTypeController();
		
		// check whetehr plugin installed or not
		if ($seopluginCtrler->isPluginActive("Subscription")) {
			$userSpecFields = $utypeCtrler->userSpecFields;
			$userTypeList = $utypeCtrler->getAllUserTypes();
			$list = array();
			foreach ($userTypeList as $userType) $list[$userType['id']] = $userType;
			$this->set('list', $list);
			
			$spTextSubscription = $this->getLanguageTexts('subscription', $_SESSION['lang_code']);
			$spTextTools = $this->getLanguageTexts('seotools', $_SESSION['lang_code']);
			$this->set('spTextSubscription', $spTextSubscription);
			
			// get all plugin access list
			$pluginAccessList = $utypeCtrler->getPluginAccessSettings();
			$pluginNameList = array();
			foreach ($pluginAccessList as $pluginAccessInfo) {
				if ($pluginAccessInfo['status'] == 0) continue;
				$pluginNameList[$pluginAccessInfo['name']] = $pluginAccessInfo['label'];
			}
				
			// get all seo tool access list
			$toolAccessList = $utypeCtrler->getSeoToolAccessSettings();
			$toolNameList = array();
			foreach ($toolAccessList as $toolAccessInfo) {
				if ($toolAccessInfo['status'] == 0) continue;
				$toolNameList[$toolAccessInfo['name']] = $spTextTools[$toolAccessInfo['url_section']];
			}
			
			$utypeSpecList = array();
			$spText = $_SESSION['text'];
			foreach ($userSpecFields as $specName) {
				
				if (in_array($specName, array('enable_email_activation'))) continue;
				
				if (stristr($specName, 'plugin_')) {
					if (empty($pluginNameList[$specName])) continue;
					$utypeSpecList[$specName] = $pluginNameList[$specName];
					continue;
				}
				
				if (stristr($specName, 'seotool_')) {
					if (empty($toolNameList[$specName])) continue;
					$utypeSpecList[$specName] = $toolNameList[$specName];
					continue;
				}
				
				switch ($specName) {
					case "price":
						$utypeSpecList[$specName] = $spText['common']['Price'];
						break;
					case "keywordcount":
						$utypeSpecList[$specName] = $spText['common']['Keywords Count'];
						break;
					case "websitecount":
						$utypeSpecList[$specName] = $spText['common']['Websites Count'];
						break;
					case "searchengine_count":
						$utypeSpecList[$specName] = $spText['common']['Search Engine Count'];
						break;
					case "directory_submit_limit":
						$utypeSpecList[$specName] = $spTextSubscription['Directory Submit Limit'];
						break;
					case "directory_submit_daily_limit":
					    $utypeSpecList[$specName] = $spTextSubscription['Directory Submit Daily Limit'];
					    break;
					case "social_media_link_count":
						$utypeSpecList[$specName] = $spTextSubscription['Social Media Link Count'];
						break;
					default:
						$utypeSpecList[$specName] = $spTextSubscription[$specName];
						
				}
			}

			$this->set('utypeSpecList', $utypeSpecList);						
			$currencyCtrler = new CurrencyController();
			$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
			$this->render('common/pricing');
		} else {
			redirectUrl(SP_WEBPATH . "/register.php");
		}	
		
	}
	
	# function to start registration
	function startRegistration(){
		$utypeCtrler = New UserTypeController();
	    $_POST = sanitizeData($_POST);
		$this->set('post', $_POST);
		$userInfo = $_POST;
		$subscriptionActive = false;
		$userStatus = 1;
		
		$errMsg['userName'] = formatErrorMsg($this->validate->checkUname($userInfo['userName']));
		$errMsg['password'] = formatErrorMsg($this->validate->checkPasswords($userInfo['password'], $userInfo['confirmPassword']));
		$errMsg['firstName'] = formatErrorMsg($this->validate->checkBlank($userInfo['firstName']));
		$errMsg['lastName'] = formatErrorMsg($this->validate->checkBlank($userInfo['lastName']));
		$errMsg['email'] = formatErrorMsg($this->validate->checkEmail($userInfo['email']));
		$errMsg['code'] = formatErrorMsg($this->validate->checkCaptcha());
		$errMsg['utype_id'] = formatErrorMsg($this->validate->checkNumber($userInfo['utype_id']));
		
		// if admin user type selected, show error
		$adminTypeId = $utypeCtrler->getAdminUserTypeId();
		if ($adminTypeId == $userInfo['utype_id']) {
			$this->validate->flagErr = true;
			$errMsg['userName'] = formatErrorMsg("You can not register as admin!!");
		}
		
		// if payment plugin installed check whether valid payment gateway found
		$seopluginCtrler =  new SeoPluginsController();
		if ($seopluginCtrler->isPluginActive("Subscription")) {
			$subscriptionActive = true;
			$errMsg['pg_id'] = formatErrorMsg($this->validate->checkNumber($userInfo['pg_id']));
			$userStatus = 0;
		}
		
		if(!$this->validate->flagErr){
			if (!$this->__checkUserName($userInfo['userName'])) {
				if (!$this->__checkEmail($userInfo['email'])) {
					$utypeId = intval($userInfo['utype_id']);
					$sql = "insert into users
					(utype_id,username,password,first_name,last_name,email,created,status) 
					values ($utypeId,'".addslashes($userInfo['userName'])."','".md5($userInfo['password'])."',
					'".addslashes($userInfo['firstName'])."','".addslashes($userInfo['lastName'])."',
					'".addslashes($userInfo['email'])."',UNIX_TIMESTAMP(),$userStatus)";
					$this->db->query($sql);
					
					// get user id created
					$userId = $this->db->getMaxId('users');
					$error = 0;
					
					// check whether subscription is active
					if ($subscriptionActive and $userId) {
						$utypeInfo = $utypeCtrler->__getUserTypeInfo($utypeId);
						
						// if it is paid subscription, proceed with payment
						if ($utypeInfo['price'] > 0) {
							$paymentPluginId = intval($userInfo['pg_id']);
							@Session::setSession('payment_plugin_id', $paymentPluginId);
							$quantity = intval($userInfo['quantity']);
							$pluginCtrler = $seopluginCtrler->createPluginObject("Subscription");
							$paymentForm = $pluginCtrler->pgCtrler->getPaymentForm($paymentPluginId, $userId, $utypeInfo, $quantity);
							$this->set('paymentForm', $paymentForm);							
						} else {
							$this->__changeStatus($userId, 1);
							
							// if trial period is set for user type
							if (!empty($utypeInfo['free_trial_period'])) {
								$totalDays = intval($utypeInfo['free_trial_period']);
								$day = date('d') + $totalDays;
								$expiryTimeStamp = mktime(23, 59, 59, date('m'), $day, date('Y'));
								$expiryDate = date('Y-m-d', $expiryTimeStamp);
								$this->updateUserInfo($userId, 'expiry_date', $expiryDate);
							}
							
						}						
					}
					
					# get confirm code
					if ($utypeCtrler->isEmailActivationEnabledForUserType($utypeId)) {
						$this->__changeStatus($userId, 0);
						$cfm = str_shuffle($userId . $userInfo['userName']);
						$sql = "update users set confirm_code='$cfm' where id=$userId";
						$this->db->query($sql);
						$this->set('confirmLink', SP_WEBPATH . "/register.php?sec=confirm&code=$cfm");
						
						// get mail details
						$adminInfo = $this->__getAdminInfo();
						$adminName = $adminInfo['first_name']." ".$adminInfo['last_name'];
						$this->set('name', $userInfo['firstName']." ".$userInfo['lastName']);
						$subject = SP_COMPANY_NAME . " " . $this->spTextRegister['Registration'];
						$content = $this->getViewContent('email/accountconfirmation');
						
						if(!sendMail($adminInfo['email'], $adminName, $userInfo['email'], $subject, $content)){
							$error = showErrorMsg(
								'An internal error occured while sending confirmation mail! Please <a href="'.SP_CONTACT_LINK.'">contact</a> seo panel team.',
								false
							);
						}						
					}
					
					$this->set('error', $error);
					$this->render('common/registerconfirm');
					return True;
					
				}else{
					$errMsg['email'] = formatErrorMsg($_SESSION['text']['login']['emailexist']);
				}
			}else{
				$errMsg['userName'] = formatErrorMsg($_SESSION['text']['login']['usernameexist']);
			}
		}
		
		$this->set('errMsg', $errMsg);
		$this->register($userInfo);
	}
	
	# function for logout
	function logout(){
	    Session::destroySession();
		redirectUrl(SP_WEBPATH."/login.php");
	}
	
	# func to show users
	function listUsers($info=[]) {
	    $info['pageno'] = intval($info['pageno']);
		$pageScriptPath = 'users.php?stscheck=';
		$pageScriptPath .= isset($info['stscheck']) ? $info['stscheck'] : "select";
		$sql = "select * from users where utype_id!=1";

		// if status set
		if (isset($info['stscheck']) && $info['stscheck'] != 'select') {
			$info['stscheck'] = intval($info['stscheck']);
			$sql .= " and status='{$info['stscheck']}'";
		}
		
		if (!empty($info["user_type_id"])) {
		    $sql .= " and utype_id=" . intval($info["user_type_id"]);
		    $pageScriptPath .= "&user_type_id=" . $info['user_type_id'];
		}
		
		// search for user name
		if (!empty($info['user_name'])) {
			$sql .= " and (username like '%".addslashes($info['user_name'])."%' 
			or first_name like '%".addslashes($info['user_name'])."%'
			or last_name like '%".addslashes($info['user_name'])."%')";
			$pageScriptPath .= "&user_name=" . $info['user_name'];
		}
		
		$sql .= " order by username";
		
		// pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content', 'layout=ajax');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;		
		$userList = $this->db->select($sql);
		$this->set('userList', $userList);

		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);

		$userTypeCtrler = new UserTypeController();
		$userTypeList = [];
		$uTypeList = $userTypeCtrler->__getAllUserTypeList();
		foreach ($uTypeList as $uTypeInfo) {
		    if ($uTypeInfo['id'] != 1) {
    			$userTypeList[$uTypeInfo['id']] = [
    				'user_type' => $uTypeInfo['user_type'],
    				'access_type' => $uTypeInfo['access_type'],
    			];
		    }
		}

		$this->set('userTypeList', $userTypeList);
		$this->set('isSubscriptionActive', isPluginActivated("Subscription"));
		$this->set('pageNo', $info['pageno']);
		$this->render('user/list', 'ajax');
	}
	
	# func to change status
	function __changeStatus($userId, $status){
		$userId = intval($userId);
		$confirmStr = !empty($status) ? ",confirm=1" : "";
		$sql = "update users set status=$status $confirmStr where id=$userId";
		$this->db->query($sql);
		
		# deaactivate all websites under this user
		if(empty($status)){
			$websiteCtrler = New WebsiteController();
			$websiteList = $websiteCtrler->__getAllWebsites($userId);
			foreach ($websiteList as $websiteInfo){
				$websiteCtrler->__changeStatus($websiteInfo['id'], 0);
			}
		}
	}
	
	# func to change status
	function __deleteUser($userId){
		
		$userId = intval($userId);
		$sql = "delete from users where id=$userId";
		$this->db->query($sql);
		
		$sql = "select id from websites where user_id=$userId";
		$webisteList = $this->db->select($sql);
		$webisteCtrler = New WebsiteController();
		foreach($webisteList as $webisteInfo){
			$webisteCtrler->__deleteWebsite($webisteInfo['id']);
		}
	}
	
	function newUser(){	
			
		// Get the user types
		$userTypeCtlr = new UserTypeController();
		$userTypeList = $userTypeCtlr->getAllUserTypes();
		$this->set('userTypeList', $userTypeList);
		$this->render('user/new', 'ajax');
	}
	
	function __checkUserName($username){
	    $username = addslashes($username);
		$sql = "select id from users where username='$username'";
		$userInfo = $this->db->select($sql, true);
		return empty($userInfo['id']) ? false :  $userInfo['id'];
	}
	
	function __checkEmail($email){
		
		$sql = "select id from users where email='".addslashes($email)."'";
		$userInfo = $this->db->select($sql, true);
		return empty($userInfo['id']) ? false :  $userInfo['id'];
	}
	
	function __getUserInfo($userId){
		
		$userId = intval($userId);
		$sql = "select * from users where id=$userId";
		$userInfo = $this->db->select($sql, true);
		return empty($userInfo['id']) ? false :  $userInfo;
	}
	
	# get admin user details
	function __getAdminInfo(){
		$sql = "select * from users where utype_id=1";
		$userInfo = $this->db->select($sql, true);
		return empty($userInfo['id']) ? false :  $userInfo;
	}
	
	#function to get all users	
	function __getAllUsers($active=1,$admin=true, $orderByCol = "username"){
		$sql = "select * from users where status=$active";
		$sql .= $admin ? "" : " and utype_id!=1";
		$sql .= " order by " . addslashes($orderByCol); 
		$userList = $this->db->select($sql);
		return $userList;
	}
	
	// function get all users with read access	
	function __getAllUsersWithReadAccess() {
		$sql = "select u.*,ut.user_type from users u, usertypes ut
				where u.utype_id=ut.id and u.status=1 and u.utype_id!=1 and ut.access_type='read'
				order by username";
		$userList = $this->db->select($sql);
		return $userList;
	}
	
	#function to get all users having website	
	function __getAllUsersHavingWebsite($active=1,$admin=true){
		$sql = "select u.* from users u,websites w where w.user_id=u.id and u.status=$active and w.status=1";
		$sql .= $admin ? "" : " and utype_id!=1";
		$sql .= " group by u.id order by username"; 
		$userList = $this->db->select($sql);
		return $userList;
	}
	
	function createUser($userInfo, $renderResults = true){
	    $userInfo = sanitizeData($userInfo);
		$this->set('post', $userInfo);
		$errMsg['userName'] = formatErrorMsg($this->validate->checkUname($userInfo['userName']));
		$errMsg['password'] = formatErrorMsg($this->validate->checkPasswords($userInfo['password'], $userInfo['confirmPassword']));
		$errMsg['firstName'] = formatErrorMsg($this->validate->checkBlank($userInfo['firstName']));
		$errMsg['lastName'] = formatErrorMsg($this->validate->checkBlank($userInfo['lastName']));
		$errMsg['email'] = formatErrorMsg($this->validate->checkEmail($userInfo['email']));
		$userTypeId = empty($userInfo['userType']) ? 2 : intval($userInfo['userType']);
		$userStatus = isset($userInfo['status']) ? intval($userInfo['status']) : 1;
		
		// if expiry date is not empty
		if (!empty($userInfo['expiry_date'])) {
			$errMsg['expiry_date'] = formatErrorMsg($this->validate->checkDate($userInfo['expiry_date']));
			$userInfo['expiry_date'] = "'".addslashes($userInfo['expiry_date'])."'";
		} else {
			$userInfo['expiry_date'] = "NULL";
		}
		
		// check error flag is on
		if(!$this->validate->flagErr){
			if (!$this->__checkUserName($userInfo['userName'])) {
				if (!$this->__checkEmail($userInfo['email'])) {
					$sql = "insert into users(utype_id,username,password,first_name,last_name,email,created,status, expiry_date, confirm) 
						values($userTypeId,'".addslashes($userInfo['userName'])."','".md5($userInfo['password'])."'
						,'".addslashes($userInfo['firstName'])."', '".addslashes($userInfo['lastName'])."'
						,'".addslashes($userInfo['email'])."',UNIX_TIMESTAMP(),$userStatus, {$userInfo['expiry_date']}, 1)";
					$this->db->query($sql);
					
					// if render results
					if ($renderResults) {					
						$this->listUsers();
						exit;
					} else {
						return array('success', 'Successfully created user');
					}
				}else{
					$errMsg['email'] = formatErrorMsg($_SESSION['text']['login']['emailexist']);
				}
			}else{
				$errMsg['userName'] = formatErrorMsg($_SESSION['text']['login']['usernameexist']);
			}
		}
		
		// if render results
		if ($renderResults) {
			$this->set('errMsg', $errMsg);
			$this->newUser();
		} else {
			return array('error', $errMsg);
		}
	}
	
	function editUser($userId, $userInfo=''){		
		
		if(!empty($userId)){
			if(empty($userInfo)){
				$userInfo = $this->__getUserInfo($userId);
				$userInfo['userName'] = $userInfo['username'];
				$userInfo['firstName'] = $userInfo['first_name'];
				$userInfo['lastName'] = $userInfo['last_name'];
				$userInfo['oldName'] = $userInfo['username'];
				$userInfo['oldEmail'] = $userInfo['email'];
				$userInfo['userType'] = $userInfo['utype_id'];
				$userInfo['expiry_date'] = formatDate($userInfo['expiry_date']);
			}

			// Get the user types
			$userTypeCtlr = new UserTypeController();
			$userTypeList = $userTypeCtlr->getAllUserTypes();
			
			$userInfo['password'] = '';					
			$this->set('post', $userInfo);		
			$this->set('userTypeList', $userTypeList);
			$this->render('user/edit', 'ajax');
			exit;
		}
		
		$this->listUsers();
	}
	
	function updateUser($userInfo, $renderResults = true){
	    $userInfo = sanitizeData($userInfo);
		$userInfo['id'] = intval($userInfo['id']);
		$this->set('post', $userInfo);
		$errMsg['userName'] = formatErrorMsg($this->validate->checkUname($userInfo['userName']));
		
		// if expiry date is not empty
		if (!empty($userInfo['expiry_date'])) {
			$errMsg['expiry_date'] = formatErrorMsg($this->validate->checkDate($userInfo['expiry_date']));
			$expiryStr = "expiry_date='".addslashes($userInfo['expiry_date'])."',";
		} else {
			$expiryStr = "expiry_date=NULL,";
		}

		// if password needs to be reset
		if(!empty($userInfo['password'])){
			$errMsg['password'] = formatErrorMsg($this->validate->checkPasswords($userInfo['password'], $userInfo['confirmPassword']));
			$passStr = "password = '".md5($userInfo['password'])."',";
		}
		
		// if change status of user
		if (isset($userInfo['status'])) {
			$activeStr = "status = '".intval($userInfo['status'])."',";
		}
		
		$errMsg['firstName'] = formatErrorMsg($this->validate->checkBlank($userInfo['firstName']));
		$errMsg['lastName'] = formatErrorMsg($this->validate->checkBlank($userInfo['lastName']));
		$errMsg['email'] = formatErrorMsg($this->validate->checkEmail($userInfo['email']));
		if(!$this->validate->flagErr){
			
			if($userInfo['userName'] != $userInfo['oldName']){
				if ($this->__checkUserName($userInfo['userName'])) {
					$errMsg['userName'] = formatErrorMsg($_SESSION['text']['login']['usernameexist']);
					$this->validate->flagErr = true;
				}
			}
			
			if($userInfo['email'] != $userInfo['oldEmail']){
				if ($this->__checkEmail($userInfo['email'])) {
					$errMsg['email'] = formatErrorMsg($_SESSION['text']['login']['emailexist']);
					$this->validate->flagErr = true;
				}
			}
			
			// if no error to inputs
			if (!$this->validate->flagErr) {
				$sql = "update users set
						username = '".addslashes($userInfo['userName'])."',
						first_name = '".addslashes($userInfo['firstName'])."',
						last_name = '".addslashes($userInfo['lastName'])."',
						$passStr
						$activeStr
						$expiryStr
						email = '".addslashes($userInfo['email'])."',
						utype_id = ".addslashes($userInfo['userType'])."
						where id={$userInfo['id']}";
				$this->db->query($sql);
				
				// if render results
				if ($renderResults) {
					$this->listUsers();
					exit;
				} else {
					return array('success', 'Successfully updated user');
				}
				
			}
		}
		
		if ($renderResults) {
			$this->set('errMsg', $errMsg);
			$this->editUser($userInfo['id'], $userInfo);
		} else {
			return array('error', $errMsg);
		}
		
		
	}
	
	function showMyProfile($info=[]){
		$userId = isLoggedIn();		
		if(!empty($userId)){
			$userInfo = $this->__getUserInfo($userId);
			$this->set('userInfo', $userInfo);			
			$userTypeCtrler = new UserTypeController();
			$userTypeInfo = $userTypeCtrler->__getUserTypeInfo($userInfo['utype_id']);
			$this->set('userTypeInfo', $userTypeInfo);
			$seopluginCtrler =  new SeoPluginsController();
			$this->set('subscriptionActive', $seopluginCtrler->isPluginActive("Subscription"));			
			$spTextSubscription = $this->getLanguageTexts('subscription', $_SESSION['lang_code']);
			$this->set('spTextSubscription', $spTextSubscription);
			$this->render('user/showmyprofile', 'ajax');
		}	
	}
	
	# function to renew membership subscription
	function renewMyProfile($info=[]){
		$userId = isLoggedIn();
		$seopluginCtrler =  new SeoPluginsController();
		
		// if logged in and plugin is active
		if(!empty($userId) && $seopluginCtrler->isPluginActive("Subscription") && !isAdmin()){
			$userInfo = $this->__getUserInfo($userId);
			$this->set('userInfo', $userInfo);
			
			$userTypeCtrler = new UserTypeController();
			$userTypeInfo = $userTypeCtrler->__getUserTypeInfo($userInfo['utype_id']);
			$this->set('userTypeInfo', $userTypeInfo);
			
			$spTextSubscription = $this->getLanguageTexts('subscription', $_SESSION['lang_code']);
			$this->set('spTextSubscription', $spTextSubscription);
			include_once(SP_PLUGINPATH . "/Subscription/paymentgateway.ctrl.php");
			
			$userTypeList = $userTypeCtrler->getRenewUserTypeList($userInfo['utype_id']);
			$this->set('userTypeList', $userTypeList);
			
			$currencyCtrler = new CurrencyController();
			$this->set('currencyList', $currencyCtrler->getCurrencyCodeMapList());
				
			// include available payment gateways
			$pgCtrler = new PaymentGateway();
			$pgList = $pgCtrler->__getAllPaymentGateway();
			$this->set('pgList', $pgList);
			$this->set('defaultPgId', $pgCtrler->__getDefaultPaymentGateway());
			$this->render('user/renewmyprofile', 'ajax');
		} else {
			redirectUrlByScript(SP_WEBPATH . "/admin-panel.php?sec=myprofile");
		}
	}
	
	# function to update membership subscription
	function updateSubscription($userInfo = ''){
		$userId = isLoggedIn();
		$seopluginCtrler =  new SeoPluginsController();
		
		// if logged in and plugin is active
		if(!empty($userId) && $seopluginCtrler->isPluginActive("Subscription") && !isAdmin()){
			$utypeCtrler = New UserTypeController();
			$_POST = sanitizeData($_POST);
			$errMsg['utype_id'] = formatErrorMsg($this->validate->checkNumber($userInfo['utype_id']));
			$errMsg['pg_id'] = formatErrorMsg($this->validate->checkNumber($userInfo['pg_id']));
			
			// if admin user type selected, show error
			$adminTypeId = $utypeCtrler->getAdminUserTypeId();
			if ($adminTypeId == $userInfo['utype_id']) {
				$this->validate->flagErr = true;
				$errMsg['utype_id'] = formatErrorMsg("You can not register as admin.");
			}
			
			// get renew usertype list
			$userTypeList = $utypeCtrler->getRenewUserTypeList($userInfo['utype_id']);
			if (!in_array($userInfo['utype_id'], array_keys($userTypeList))) {
				$this->validate->flagErr = true;
				$errMsg['utype_id'] = formatErrorMsg("You are not allowed to upgrade to this plan.");
			}
			
			// if all form inputs are valid
			if (!$this->validate->flagErr) {
				$utypeId = intval($userInfo['utype_id']);
				$userId = isLoggedIn();
				$utypeInfo = $utypeCtrler->__getUserTypeInfo($utypeId);
		
				// if it is paid subscription, proceed with payment
				if ($utypeInfo['price'] > 0) {
					$paymentPluginId = intval($userInfo['pg_id']);
					@Session::setSession('payment_plugin_id', $paymentPluginId);
					$quantity = intval($userInfo['quantity']);
					$pluginCtrler = $seopluginCtrler->createPluginObject("Subscription");
					$paymentForm = $pluginCtrler->pgCtrler->getPaymentForm($paymentPluginId, $userId, $utypeInfo, $quantity, "renew");
					$this->set('paymentForm', $paymentForm);
				} else {
					$this->updateUserInfo($userId, 'utype_id', $userInfo['utype_id']);
					$expiryDate = $this->calculateUserExpiryDate($userInfo['quantity']);
					$this->updateUserInfo($userId, 'expiry_date', $expiryDate);
					redirectUrlByScript(SP_WEBPATH . "/admin-panel.php?sec=myprofile");
					exit;
				}
				
				$this->render('user/renewmyprofile', 'ajax');
				
			} else {
				$this->set('errMsg', $errMsg);
				$this->renewMyProfile($_POST);
			}
			
		} else {
			redirectUrlByScript(SP_WEBPATH . "/admin-panel.php?sec=myprofile");
		}
	}
	
	function editMyProfile($userInfo=''){
		$userId = isLoggedIn();		
		if(!empty($userId)){
			if(empty($userInfo)){
				$userInfo = $this->__getUserInfo($userId);
				
				$userInfo['userName'] = $userInfo['username'];
				$userInfo['firstName'] = $userInfo['first_name'];
				$userInfo['lastName'] = $userInfo['last_name'];
				$userInfo['oldName'] = $userInfo['username'];
				$userInfo['oldEmail'] = $userInfo['email'];
			}
			
			$userInfo['password'] = '';					
			$this->set('post', $userInfo);			
			$this->render('user/editmyprofile', 'ajax');
			exit;
		}	
	}
	
	function updateMyProfile($userInfo){
		$userInfo = sanitizeData($userInfo);
		$userId = isLoggedIn();	
		$this->set('post', $userInfo);
		$errMsg['userName'] = formatErrorMsg($this->validate->checkUname($userInfo['userName']));
		if(!empty($userInfo['password'])){
			$errMsg['password'] = formatErrorMsg($this->validate->checkPasswords($userInfo['password'], $userInfo['confirmPassword']));
			$passStr = "password = '".md5($userInfo['password'])."',";
		}
		$errMsg['firstName'] = formatErrorMsg($this->validate->checkBlank($userInfo['firstName']));
		$errMsg['lastName'] = formatErrorMsg($this->validate->checkBlank($userInfo['lastName']));
		$errMsg['email'] = formatErrorMsg($this->validate->checkEmail($userInfo['email']));
		if(!$this->validate->flagErr){
			
			if($userInfo['userName'] != $userInfo['oldName']){
				if ($this->__checkUserName($userInfo['userName'])) {
					$errMsg['userName'] = formatErrorMsg($_SESSION['text']['login']['usernameexist']);
					$this->validate->flagErr = true;
				}
			}
			
			if($userInfo['email'] != $userInfo['oldEmail']){
				if ($this->__checkEmail($userInfo['email'])) {
					$errMsg['email'] = formatErrorMsg($_SESSION['text']['login']['emailexist']);
					$this->validate->flagErr = true;
				}
			}
			
			if (!$this->validate->flagErr) {
				$sql = "update users set
						username = '".addslashes($userInfo['userName'])."',
						first_name = '".addslashes($userInfo['firstName'])."',
						last_name = '".addslashes($userInfo['lastName'])."',
						$passStr
						email = '".addslashes($userInfo['email'])."'
						where id=$userId";
				$this->db->query($sql);
				$this->set('msg', $this->spTextUser['Saved My Profile Details']);
				$this->showMyProfile();
				exit;
			}
		}
		
		$this->set('errMsg', $errMsg);
		$this->editMyProfile($userInfo);
	}
	
	# forgot password function
	function forgotPasswordForm(){		
		$this->render('common/forgot');
	}
	
	# reset password of user
    function requestPassword($userEmail) {
        
		$errMsg['email'] = formatErrorMsg($this->validate->checkEmail($userEmail));
		$errMsg['code'] = formatErrorMsg($this->validate->checkCaptcha());
		$this->set('post', $_POST);
		if(!$this->validate->flagErr){
	        $userId = $this->__checkEmail($userEmail);
	        if(!empty($userId)){
	            $userInfo = $this->__getUserInfo($userId);
	        	$rand = str_shuffle(rand().$userInfo['username']);

	            // get admin details
	            $adminInfo = $this->__getAdminInfo();
	            
	            # send password to user
	            $error = 0;
	           	$this->set('rand', $rand);
	           	$name = $userInfo['first_name']." ".$userInfo['last_name'];
	           	$this->set('name', $name);
	           	$this->set('userName', $userInfo['username']);
	           	$content = $this->getViewContent('email/passwordreset');
	           	$subject = "Seo panel password reset";
	           	
	           	if(!sendMail($adminInfo["email"], $name, $userEmail, $subject, $content)){
	           		$error = $_SESSION['text']['login']['internal_error_mail_send'];
	           	} else {
	           		
	           		// update password in DB
	           		$sql = "update users set password=md5('$rand') where id={$userInfo['id']}";
	           		$this->db->query($sql);
	           		
	           	}
	           	
	           	$this->set('error', $error);
	           	$this->render('common/forgotconfirm');
	           	exit;
	        }else{
	            $errMsg['email'] = formatErrorMsg($_SESSION['text']['login']['user_email_not_exist']);
	        }
		}
		$this->set('errMsg', $errMsg);
		$this->forgotPasswordForm();
	}
	
	# function to check whether user expired
	function isUserExpired($userId) {
		$excludeSecList = array("myprofile", "renew-profile", "update-subscription");
		
		// if not admin user and not in section pages
		if (!isAdmin() && !in_array($_REQUEST['sec'], $excludeSecList)) {
			$userInfo = $this->__getUserInfo($userId);
			$userInfo['expiry_date'] = formatDate($userInfo['expiry_date']);
			
			// if expiry date set for user
			if (!empty($userInfo['expiry_date'])) {
				$today = date("Y-m-d");
				$todayTime = strtotime($today);
				$expireTime = strtotime($userInfo['expiry_date']);
				
				// current date greater than expiry date
				if ($todayTime > $expireTime) {
					return false;
				}
			}
		}
		
		return true;
		
	}
	
	# function to get admin user id
	function getAdminUserId() {
		$userTypeCtrlr = new UserTypeController();
		$adminUserTypeId = $userTypeCtrlr->getAdminUserTypeId();
		$sql = "select * from users where utype_id=" . $adminUserTypeId;
		$userInfo = $this->db->select($sql, true);
		return $userInfo['id'];
	}
	
	# function to check passed user id is admin user id
	function isAdminUserId($userId) {
		$adminUserId = $this->getAdminUserId();
		
		// if admin user id return true
		if ($userId == $adminUserId) {
			return true;
		} else {
			return false;
		}
		
	}
	
	# function to update user info
	function updateUserInfo($userId, $col, $value) {
		$sql = "update users set $col='".addslashes($value)."' where id=" . intval($userId);
		$this->db->query($sql);
	}
	
	# function to calculate user expiry date
	function calculateUserExpiryDate($quantity) {
		$month = date('m') + $quantity;
		$expiryTimeStamp = mktime(23, 59, 59, $month, date('d'), date('Y'));
		$expiryDate = date('Y-m-d', $expiryTimeStamp);
		return $expiryDate;
	}
	
	function manageWebsiteAccessManager($info=[]) {
	    $userList = $this->__getAllUsersWithReadAccess();
	    $userId = isset($info['wam_user']) ? intval($info['wam_user']) : $userList[0]['id'];

        if (isset($info['action'])) {
            $sql = "delete from user_website_access where user_id=" . $info['wam_user'];
            $this->db->query($sql);
            
            foreach($info['check_ws'] as $key => $val) {
                $sql = "insert into user_website_access(user_id,website_id) values(". $userId . ", " . intval($val) . ")";
                $this->db->query($sql);
            }
            $this->set("msg", formatSuccessMsg("Updated user website access!"));
        }

	    $loggedinUserId = isLoggedIn();
	    $sql = "select w.*,uwa.id  as uwa_id,uwa.access from websites w left join user_website_access uwa on w.id=uwa.website_id and w.user_id=$loggedinUserId";
	    $sql .= !empty($userId) ? " and uwa.user_id=$userId" : ""; 
	    $userWebsiteList = $this->db->select($sql);
	    $this->set("userWebsiteList", $userWebsiteList);
	    $this->set("userId", $userId);
	    $this->set("userList", $userList);
	    $this->render('user/websiteAccessManager');
	}
	
	function getUserWebsiteAccessList($userId) {
		$accessList = array();
		$cond = "user_id=".intval($userId);
		$list = $this->dbHelper->getAllRows("user_website_access", $cond);
		foreach ($list as $listInfo) {
			$accessList[$listInfo['website_id']] = $listInfo;	
		}
		
		return $accessList;
	}
	
	function getUserWebsiteAccessCount($userId) {
		$cond = "user_id=".intval($userId);
		$info = $this->dbHelper->getRow("user_website_access", $cond, "count(*) count");
		return $info['count'];
	}
	
}
?>