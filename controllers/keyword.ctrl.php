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

# class defines all keyword controller functions
class KeywordController extends Controller{

	# func to show keywords
	function listKeywords($info=''){		
		
	    $userId = isLoggedIn();
		$websiteController = New WebsiteController();
		
		$urlParams = "";
		$websiteId = empty($info['website_id']) ? "" : intval($info['website_id']);
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$this->set('websiteId', $websiteId);
		if ($websiteId) {
		     $conditions = " and k.website_id=$websiteId";
		     $urlParams = "website_id=$websiteId";
		} else {
		    $conditions = "";   
		}
		
		if (isset($info['status'])) {
		    if (($info['status']== 'active') || ($info['status']== 'inactive')) {
		        $statVal = ($info['status']=='active') ? 1 : 0;
		        $conditions .= " and k.status=$statVal";
		        $urlParams .= "&status=".$info['status'];
		    }    
		} else {
		    $info['status'] = '';
		}
		$this->set('statVal', $info['status']);
		
		if (empty($info['keyword'])) {
		    $info['keyword'] =  '';
		} else {
		    $info['keyword'] = urldecode($info['keyword']);
		    $conditions .= " and k.name like '%".addslashes($info['keyword'])."%'";
		    $urlParams .= "&keyword=".urlencode($info['keyword']);    
		}		
		$this->set('keyword', $info['keyword']);
		
		$sql = "select k.*,w.name website,w.status webstatus from keywords k,websites w where k.website_id=w.id and w.status=1";
		$sql .= isAdmin() ? "" : " and w.user_id=$userId";
		$sql .= " $conditions order by k.name";
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('keywords.php', '', 'scriptDoLoad', 'content', $urlParams);		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		
		# set keywords list
		$keywordList = $this->db->select($sql);
		$this->set('pageNo', $info['pageno']);
		$langCtrler = New LanguageController();
		$countryCtrler = New CountryController();
		foreach ($keywordList as $i => $keyInfo) {
			$info = $langCtrler->__getLanguageInfo($keyInfo['lang_code']);
			$keywordList[$i]['lang_name'] = $info['lang_name'];
			$info = $countryCtrler->__getCountryInfo($keyInfo['country_code']); 
			$keywordList[$i]['country_name'] = $info['country_name'];
		}
		$this->set('list', $keywordList);
		$this->render('keyword/list');
	}

	# func to show keyword select box
	function showKeywordSelectBox($userId='', $websiteId='', $keywordId=''){
		$this->set('keywordList', $this->__getAllKeywords($userId, $websiteId, true));
		$this->set('wkeywordId', $keywordId);
		$this->render('keyword/keywordselectbox');
	}
	
	# func to change status
	function __changeStatus($keywordId, $status){
		
		$keywordId = intval($keywordId);
		$sql = "update keywords set status=$status where id=$keywordId";
		$this->db->query($sql);
	}

	# func to change status
	function __deleteKeyword($keywordId){
		
		$keywordId = intval($keywordId);
		$sql = "delete from keywords where id=$keywordId";
		$this->db->query($sql);
		
		// delete related data
		$sql = "delete sd.*, s.* from searchresults s, searchresultdetails sd where s.id=sd.searchresult_id and s.keyword_id=$keywordId";
		$this->db->query($sql);
	}

	function newKeyword(){		
		
		$userId = isLoggedIn();
		
		# Validate keyword count
		if (!$this->validateKeywordCount($userId)) {
			$this->set('validationMsg', $this->spTextKeyword['Your keyword count already reached the limit']);
		}
		
		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$langController = New LanguageController();
		$this->set('langList', $langController->__getAllLanguages());
		$this->set('langNull', true);
		$countryController = New CountryController();
		$this->set('countryList', $countryController->__getAllCountries());
		$this->set('countryNull', true);
		$seController = New SearchEngineController();
		$this->set('seList', $seController->__getAllSearchEngines());
		$this->render('keyword/new');
	}

	# create new keyword function
	function createKeyword($listInfo, $apiCall = false){
		
		$userId = isLoggedIn();
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['website_id'] = formatErrorMsg($this->validate->checkBlank($listInfo['website_id']));
		if (!is_array($listInfo['searchengines'])) $listInfo['searchengines'] = array(); 		
		$errMsg['searchengines'] = formatErrorMsg($this->validate->checkBlank(implode('', $listInfo['searchengines'])));
		$statusVal = isset($listInfo['status']) ? intval($listInfo['status']) : 1;
		$seStr = is_array($listInfo['searchengines']) ? implode(':', $listInfo['searchengines']) : $listInfo['searchengines'];		
		
		// verify the limit count for the user
		if (!$this->validate->flagErr) {
			
			// Get the website user id, if admin is logged in
			if (isAdmin() || $apiCall) {
				$websiteCtrler = new WebsiteController();
				$websiteInfo = $websiteCtrler->__getWebsiteInfo($listInfo['website_id']);
				$webUserId = $websiteInfo['user_id'];
			} else {
				$webUserId = $userId;
			}
			
			// Validate keyword count
			if (! $this->validateKeywordCount($webUserId)) {
				$validationMsg = $this->spTextKeyword['Your keyword count already reached the limit'];
				$this->set('validationMsg', $validationMsg);
				$errMsg['limit_error'] = $validationMsg;
				$this->validate->flagErr = true;
			}
		}
		
		// verify the form elements
		if(!$this->validate->flagErr){
			$keyword = addslashes(trim($listInfo['name']));
			if (!$this->__checkName($keyword, $listInfo['website_id'])) {
				$listInfo['searchengines'] = is_array($listInfo['searchengines']) ? $listInfo['searchengines'] : array();
				$sql = "insert into keywords(name,lang_code,country_code,website_id,searchengines,status)
				values('$keyword', '".addslashes($listInfo['lang_code'])."', '".addslashes($listInfo['country_code'])."',
				".intval($listInfo['website_id']).", '".addslashes($seStr)."', $statusVal)";
				$this->db->query($sql);
				
				// if api call
				if ($apiCall) {
					return array('success', 'Successfully created keyword');
				} else {
					$this->listKeywords();
					exit;
				}
				
			}else{
				$errMsg['name'] = formatErrorMsg($this->spTextKeyword['Keyword already exist']);
			}
		}
		
		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->newKeyword();
		}
		
	}
	
	# function to import keywords
	function importKeywords(){		
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		
		if (empty($_POST['website_id'])) {
		    $listInfo['website_id'] = $websiteList[0]['id'];
		    $this->set('post', $listInfo);
		}
		
		$langController = New LanguageController();
		$this->set('langList', $langController->__getAllLanguages());
		$this->set('langNull', true);
		
		$countryController = New CountryController();
		$this->set('countryList', $countryController->__getAllCountries());
		$this->set('countryNull', true);
		
		$seController = New SearchEngineController();
		$this->set('seList', $seController->__getAllSearchEngines());
		
		// Check the user website count for validation
		if (!isAdmin()) {
			$this->setValidationMessageForLimit($userId);	
		}
		
		$this->render('keyword/importkeywords');
	}
	
	# function to set validation message for the limit
	function setValidationMessageForLimit($userId) {
		
		// Check the user website count for validation
		$userTypeCtrlr = new UserTypeController();
		$userKeywordCount = count($this->__getAllKeywords($userId));
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		$validCount = $userTypeDetails['keywordcount'] - $userKeywordCount;
		$validCount = $validCount > 0 ? $validCount : 0;
		$validationMsg = str_replace("[keywordcount]", "<b>$validCount</b>", $this->spTextKeyword['You can add only keywordcount keywords more']);
		$this->set('validationMsg', $validationMsg);
		
	}
	
	# function to import keywords to the seo panel
	function createImportedKeywords($listInfo){
		
		$userId = isLoggedIn();
		$this->set('post', $listInfo);
		$errMsg['keywords'] = formatErrorMsg($this->validate->checkBlank($listInfo['keywords']));
		if (!is_array($listInfo['searchengines'])) $listInfo['searchengines'] = array(); 		
		$errMsg['searchengines'] = formatErrorMsg($this->validate->checkBlank(implode('', $listInfo['searchengines'])));
		
		if(!$this->validate->flagErr){
			
			$listInfo['website_id'] = intval($listInfo['website_id']);
			$keywords = explode(",", $listInfo['keywords']);
			$keyExist = false;
			$keywordList = array();
			foreach ($keywords as $i => $keyword) {
				$keyword = addslashes(trim($keyword));
				if ($this->__checkName($keyword, $listInfo['website_id'])) {
					$errMsg['keywords'] = formatErrorMsg($_SESSION['text']['common']['Keyword']." '<b>$keyword</b>' ". $_SESSION['text']['label']['already exist']);
					$keyExist = true;
					break;
				}
				
				// if keyword is not empty
				if (!empty($keyword)) {
					$keywordList[$i] = $keyword;
				}
			}			
			
			// Check the user website count for validation
			if (isAdmin()) {
				$websiteCtrler = new WebsiteController();
				$websiteInfo = $websiteCtrler->__getWebsiteInfo($listInfo['website_id']);
				$webUserId = $websiteInfo['user_id'];
			} else {
				$webUserId = $userId;
			}
			
			// check whether keyword count exeeds the limit
			if (!$this->validateKeywordCount($webUserId, count($keywordList))) {
				$this->setValidationMessageForLimit($webUserId);
				$keyExist = true;
			}
			
			// if no error exists save keyword
			if (!$keyExist) {
				$listInfo['searchengines'] = is_array($listInfo['searchengines']) ? $listInfo['searchengines'] : array();
				foreach ($keywordList as $keyword) {				
					$sql = "insert into keywords(name,lang_code,country_code,website_id,searchengines,status)
					values('$keyword','".addslashes($listInfo['lang_code'])."','".addslashes($listInfo['country_code'])."',
					".intval($listInfo['website_id']).",'".implode(':', $listInfo['searchengines'])."',1)";
					$this->db->query($sql);
				}
				
				$this->listKeywords($listInfo);
				exit;
			}			
			
		}
		$this->set('errMsg', $errMsg);
		$this->importKeywords();
	}

	function __checkName($name, $websiteId){
		
		$sql = "select id from keywords where name='$name' and website_id=$websiteId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	# func to get all keywords
	function __getAllKeywords($userId='', $websiteId='', $isAdminCheck=false, $orderByWeb=false, $orderByValue='ASC', $searchName = ''){
		$sql = "select k.*,w.name website,w.url weburl from keywords k,websites w where k.website_id=w.id and k.status=1";		
		if(!$isAdminCheck || !isAdmin() ){
			if(!empty($userId)) $sql .= " and w.user_id=$userId";
		}
		
		if(!empty($websiteId)) $sql .= " and k.website_id=$websiteId";
		
		if (!empty($searchName)) {
			$sql .= " and k.name like '%".addslashes($searchName)."%'";
		}
		
		$sql .= $orderByWeb ? " order by w.id, k.name $orderByValue" : " order by k.name $orderByValue";
		$keywordList = $this->db->select($sql);
		return $keywordList;
	}

	function __getKeywordInfo($keywordId){
		
		$keywordId = intval($keywordId);
		$sql = "select * from keywords where id=$keywordId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	function editKeyword($keywordId, $listInfo=''){	
					
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$langController = New LanguageController();
		$this->set('langList', $langController->__getAllLanguages());
		$this->set('langNull', true);
		$countryController = New CountryController();
		$this->set('countryList', $countryController->__getAllCountries());
		$this->set('countryNull', true);
		$seController = New SearchEngineController();
		$this->set('seList', $seController->__getAllSearchEngines());
		if(!empty($keywordId)){
			if(empty($listInfo)){
				$listInfo = $this->__getKeywordInfo($keywordId);
				$listInfo['oldName'] = $listInfo['name'];
				$listInfo['searchengines'] = explode(':', $listInfo['searchengines']);
			}
			$this->set('post', $listInfo);
			$this->render('keyword/edit');
			exit;
		}
		$this->listKeywords();
	}

	function updateKeyword($listInfo, $apiCall = false){
		$userId = isLoggedIn();
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['searchengines'] = formatErrorMsg($this->validate->checkBlank(implode('', $listInfo['searchengines'])));
		$seStr = is_array($listInfo['searchengines']) ? implode(':', $listInfo['searchengines']) : $listInfo['searchengines'];
		$statusVal = isset($listInfo['status']) ? "status = " . intval($listInfo['status']) ."," : "";	
		
		//validate form
		if(!$this->validate->flagErr){

			$listInfo['website_id'] = intval($listInfo['website_id']);
			$listInfo['id'] = intval($listInfo['id']);
			$keyword = addslashes(trim($listInfo['name']));
			if($listInfo['name'] != $listInfo['oldName']){				
				if ($this->__checkName($keyword, $listInfo['website_id'])) {
					$errMsg['name'] = formatErrorMsg($this->spTextKeyword['Keyword already exist']);
					$this->validate->flagErr = true;
				}
			}

			if (!$this->validate->flagErr) {				
				$listInfo['searchengines'] = is_array($listInfo['searchengines']) ? $listInfo['searchengines'] : array();
				$sql = "update keywords set
						name = '$keyword',
						lang_code = '".addslashes($listInfo['lang_code'])."',
						country_code = '".addslashes($listInfo['country_code'])."',
						website_id = {$listInfo['website_id']},
						$statusVal
						searchengines = '".addslashes($seStr)."'
						where id={$listInfo['id']}";
				$this->db->query($sql);
				
				// if api call
				if ($apiCall) {
					return array('success', 'Successfully updated keyword');
				} else {
					$this->listKeywords();
					exit;
				}	
					
			}
		}

		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->editKeyword($listInfo['id'], $listInfo);
		}
	}
	
	function showKeywordReports($keywordId) {
	    $keywordId = intval($keywordId);
		$this->checkUserIsObjectOwner($keywordId, 'keyword');
		echo "<script>scriptDoLoad('reports.php', 'content', 'keyword_id=$keywordId&rep=1')</script>";
	}
	
	// Function to check / validate the user type keuword count
	function validateKeywordCount($userId, $newCount = 1) {
		$userCtrler = new UserController();

		// if admin user id return true
		if ($userCtrler->isAdminUserId($userId)) {
			return true;
		}
		
		$userTypeCtrlr = new UserTypeController();
		$userKeywordCount = count($this->__getAllKeywords($userId));
		$userKeywordCount += $newCount;
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		
		// check whether count greater than limit
		if ($userKeywordCount <= $userTypeDetails['keywordcount']) {
			return true;
		} else {
			return false;	
		}
	}
	
}
?>