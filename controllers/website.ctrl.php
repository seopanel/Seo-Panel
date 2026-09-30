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

# class defines all website controller functions
class WebsiteController extends Controller{

	# func to show websites
	function listWebsites($info=[]) {		
		$userId = isLoggedIn();		
		$info['pageno'] = intval($info['pageno']);
		$pageScriptPath = 'websites.php?stscheck=';
		$pageScriptPath .= isset($info['stscheck']) ? $info['stscheck'] : "select";
		
		// if admin add user filter
		if(isAdmin()){
			$sql = "select w.*,u.username from websites w,users u where u.id=w.user_id";
			$this->set('isAdmin', 1);
			
			// if user id is not empty
			if (!empty($info['userid'])) {
				$sql .= " and w.user_id=".intval($info['userid']);
				$pageScriptPath .= "&userid=" . intval($info['userid']);
			}
			
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			
		}else{
			$sql = "select * from websites w where user_id=$userId";	
		}

		// search for user name
		if (!empty($info['search_name'])) {
			$sql .= " and (w.name like '%".addslashes($info['search_name'])."%'
			or w.url like '%".addslashes($info['search_name'])."%')";
			$pageScriptPath .= "&search_name=" . $info['search_name'];
		}
		
		// if status set
		if (isset($info['stscheck']) && $info['stscheck'] != 'select') {
			$info['stscheck'] = intval($info['stscheck']);
			$sql .= " and w.status='{$info['stscheck']}'";
		}

		$sql .= " order by w.name";
		$this->set('userId', empty($info['userid']) ? 0 : $info['userid']);		
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath);		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;

		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);
		
		$propertyList = $this->__getAllAnalyticProperties(TRUE);
		$this->set('propertyList', $propertyList);
				
		$websiteList = $this->db->select($sql);	
		$this->set('pageNo', $info['pageno']);		
		$this->set('list', $websiteList);
		$this->render('website/list');
	}

	# func to get all Websites
	function __getAllWebsites($userId = '', $isAdminCheck = false, $searchName = '') {
		$sql = "select * from websites where status=1";
		if(!$isAdminCheck || !isAdmin() ) {
			if(!empty($userId)) {
				$sql .= $this->getWebsiteUserAccessCondition($userId, "id", "user_id");
			}
		} 
		
		// if search string is not empty
		if (!empty($searchName)) {
			$sql .= " and (name like '%".addslashes($searchName)."%' or url like '%".addslashes($searchName)."%')";
		}
		
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}
	
	function __getUserWebsites($userId, $searchInfo=[]) {
	    $cond = "user_id=".intval($userId);
	    $cond .= isset($searchInfo['status']) ? " and status=".intval($searchInfo['status']) : "";
	    $cond .= isset($searchInfo['search']) ? " and url like '%".addslashes($searchInfo['search'])."%'" : "";
        return $this->dbHelper->getAllRows('websites', $cond);
	}
	
	# func to get all Websites
	function __getCountAllWebsites($userId='', $statusCheck = true, $statusVal = 1){

		$sql = "select count(*) count from websites where 1=1";
		$sql .= $statusCheck ? " and status=" . $statusVal : "";
		
		if (!empty($userId)) {
			$sql .= " and user_id=" . intval($userId);
		}
		
		$countInfo = $this->db->select($sql, true);
		$count = empty($countInfo['count']) ? 0 : $countInfo['count']; 
		return $count;
	}
	
	# func to get all Websites having active keywords
	function __getAllWebsitesWithActiveKeywords($userId='', $isAdminCheck=false){
		$sql = "select w.* from websites w,keywords k where w.id=k.website_id and w.status=1 and k.status=1";
		if(!$isAdminCheck || !isAdmin() ){
			if(!empty($userId)) {
				$sql .= $this->getWebsiteUserAccessCondition($userId);
			}
		} 
		
		$sql .= " group by w.id order by w.name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}
	
	function getWebsiteUserAccessCondition($userId, $col="w.id", $userCol = "w.user_id") {
		if (SP_CUSTOM_DEV) {
			$userCtrl = new UserController();
			$webAccessList = $userCtrl->getUserWebsiteAccessList($userId);
			$webIdList = array_keys($webAccessList);
			$webIdList = !empty($webIdList) ? $webIdList : array(0);
			$cond = " and ($userCol=" . intval($userId) . " or $col in (".implode($webIdList, ',')."))";
		} else {
			$cond = " and $userCol=" . intval($userId);
		}
		
		return $cond;
	}

		/*
	 * func to verify the logged-in caller owns (or is admin over) a
	 * website - shared by __changeStatus()/__deleteWebsite()/
	 * updateWebsite()/editWebsite(), none of which checked this before:
	 * any logged-in non-admin could activate/deactivate, delete, rename/
	 * reassign, or view the edit form of ANY other user's website just
	 * by supplying its id, via the normal web UI (websites.php), no API
	 * key or admin session needed.
	 *
	 * Deliberately returns a bool rather than calling showErrorMsg()
	 * itself: __changeStatus()/__deleteWebsite() can be invoked in a
	 * bulk loop (websites.php's activateall/inactivateall/deleteall),
	 * where showErrorMsg()'s exit() on the first foreign id would abort
	 * the rest of a legitimate batch too - callers silently skip what
	 * they don't own instead. editWebsite()/updateWebsite() are
	 * single-target and call showErrorMsg() themselves on a false
	 * return.
	 *
	 * Also covers the __deleteUser()/__deleteWebsite() cascade
	 * (controllers/user.ctrl.php) and the REST API's deleteWebsite()/
	 * updateWebsite(): both only ever reach here from an already-admin
	 * session (users.php gates user deletion with checkAdminLoggedIn();
	 * the API's shared key is admin-equivalent by design, see
	 * api.ctrl.php), so the isAdmin() bypass below covers them
	 * correctly without needing a separate code path.
	 */
	function __verifyWebsiteOwnership($websiteId) {
		if (isAdmin()) return true;
		$userId = isLoggedIn();
		$websiteInfo = $this->dbHelper->getRow('websites', "id=" . intval($websiteId));
		return !empty($websiteInfo) && intval($websiteInfo['user_id']) === intval($userId);
	}

	# func to change status
	function __changeStatus($websiteId, $status){
		if (!$this->__verifyWebsiteOwnership($websiteId)) {
			return;
		}

		$websiteId = intval($websiteId);
		$sql = "update websites set status=$status where id=$websiteId";
		$this->db->query($sql);

		$sql = "update keywords set status=$status where website_id=$websiteId";
		$this->db->query($sql);
	}

	# func to delete website
	function __deleteWebsite($websiteId){
		if (!$this->__verifyWebsiteOwnership($websiteId)) {
			return;
		}

		$websiteId = intval($websiteId);

		// fetched BEFORE any of the cascading deletes below - the audit
		// log needs a readable label (name/url) that survives
		// independently of the row it describes being gone
		$websiteAuditInfo = $this->db->select("select name, url from websites where id=$websiteId", true);

		// delete all cascading child records FIRST, while the website row
		// still exists - __deleteKeyword() now re-verifies ownership via
		// its keyword's parent website (see KeywordController::
		// __verifyKeywordOwnership()), so deleting the website row before
		// this loop would make every one of those calls fail that check
		// against an already-gone website. Same fix shape as
		// SiteAuditorController::__deleteProject()'s own reordering
		// earlier this session.
		# delete all keywords under this website
		$sql = "select id from keywords where website_id=$websiteId";
		$keywordList = $this->db->select($sql);
		$keywordCtrler = New KeywordController();
		foreach($keywordList as $keywordInfo){
			$keywordCtrler->__deleteKeyword($keywordInfo['id']);
		}

		# remove rank results
		$sql = "delete from rankresults where website_id=$websiteId";
		$this->db->query($sql);

		# remove backlink results
		$sql = "delete from backlinkresults where website_id=$websiteId";
		$this->db->query($sql);

		# remove saturation results
		$sql = "delete from saturationresults where website_id=$websiteId";
		$this->db->query($sql);

		# remove site auditor results
		$sql = "select id from auditorprojects where website_id=$websiteId";
		$info = $this->db->select($sql, true);
		if (!empty($info['id'])) {
		    $auditorObj = $this->createController('SiteAuditor');
		    $auditorObj->__deleteProject($info['id']);
		}

		#remove directory results
		$sql = "delete from dirsubmitinfo where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from skipdirectories where website_id=$websiteId";
		$this->db->query($sql);

		// bug fix: these tables all carry a website_id but had no cleanup
		// path at all (review_links/social_media_links/user_website_access/
		// website_analytics are already DB-level ON DELETE CASCADE - see
		// install/data/seopanel.sql's ALTER TABLE block - so those are
		// correctly left out here). Without this, every deleted website
		// left permanent orphans in all of these: AI Visibility site
		// registration/bot-hit/referral history, AI Perception prompts
		// (+ their own child results), robots.txt rule state, dashboard
		// recommendations, Webmaster Tools sitemap/keyword data, and
		// PageSpeed history.
		#remove pagespeed results
		$sql = "delete from pagespeeddetails where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from pagespeedresults where website_id=$websiteId";
		$this->db->query($sql);

		#remove AI Visibility data
		$sql = "delete from ai_visibility_sites where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from ai_bot_hits where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from ai_referrals where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from ai_visibility_site_access where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from ai_visibility_robots_rules where website_id=$websiteId";
		$this->db->query($sql);
		// ai_visibility_htaccess_audit_log / ai_visibility_robots_audit_log
		// are deliberately NOT cleaned up here - both are documented,
		// append-only compliance/audit trails (see their CREATE TABLE
		// comments in install/data/seopanel.sql) meant to prove what
		// happened while the website existed, not live operational data.

		#remove AI Perception prompts and their results
		$sql = "delete from llm_perception_results where prompt_id in (select id from llm_perception_prompts where website_id=$websiteId)";
		$this->db->query($sql);
		$sql = "delete from llm_perception_prompts where website_id=$websiteId";
		$this->db->query($sql);

		#remove dashboard recommendations
		$sql = "delete from sp_recommendations where website_id=$websiteId";
		$this->db->query($sql);

		#remove webmaster tools data
		$sql = "delete from webmaster_keywords where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from webmaster_sitemaps where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from website_search_analytics where website_id=$websiteId";
		$this->db->query($sql);

		# the website row itself, last
		$sql = "delete from websites where id=$websiteId";
		$this->db->query($sql);

		$label = !empty($websiteAuditInfo['name']) ? $websiteAuditInfo['name'] : ($websiteAuditInfo['url'] ?? null);
		$this->logAuditEvent('website.delete', 'website', $websiteId, $label);
	}

	function newWebsite($info=[]) {
		$userId = isLoggedIn();
		
		// check whether user have only readonly website access 
		if (SP_CUSTOM_DEV) {
		    redirectUrlByScript(SP_WEBPATH . "/admin-panel.php");
		    exit;
		}
		
		if(!empty($info['check']) && !$this->__getCountAllWebsites($userId)){
			$this->set('warningMsg', $this->spTextWeb['plscrtwebsite'].'<br>Please <a href="javascript:void(0);" onclick="scriptDoLoad(\'websites.php\', \'content\')">'.strtolower($_SESSION['text']['common']['Activate']).'</a> '.$this->spTextWeb['yourwebalreday']);
		}
		
		# Validate website count
		if (!$this->validateWebsiteCount($userId)) {
			$this->set('validationMsg', $this->spTextWeb['Your website count already reached the limit']);
		}
		
		# get all users
		if(isAdmin()){
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);  			
			$this->set('isAdmin', 1);
		}
		
		$propertyList = $this->__getAllAnalyticProperties(TRUE);
		$this->set('propertyList', $propertyList);
		$this->set('editAction', 'create');
		$this->render('website/edit_website');
	}

	function __checkName($name, $userId, $websiteId=FALSE) {
	    $userId = intval($userId);
	    $websiteId = intval($websiteId);
		$sql = "select id 
                from websites
                where name='".addslashes($name)."' and user_id=$userId";
		$sql .= !empty($websiteId) ? " and id!=$websiteId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function __checkWebsiteUrl($url, $websiteId=0) {
	    $websiteId = intval($websiteId);
		$sql = "select id from websites where url='".addslashes($url)."'";
		$sql .= $websiteId ? " and id!=$websiteId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function createWebsite($listInfo, $apiCall=false) {
	    $listInfo['url'] = trim($listInfo['url']);
	    $listInfo['name'] = trim($listInfo['name']);
		
		// add user id when using as admin or calling api
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['userid']) ? isLoggedIn() : intval($listInfo['userid']);	
		} else {
			$userId = isLoggedIn();
		}

		$errMsg = [];
		$listInfo['name'] = strip_tags($listInfo['name']);
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkUrl($listInfo['url']));
		$listInfo['url'] = addHttpToUrl($listInfo['url']);
		$statusVal = isset($listInfo['status']) ? intval($listInfo['status']) : 1;
		
		// verify the limit for the user
		if (!$this->validateWebsiteCount($userId)) {
			$this->set('validationMsg', $this->spTextWeb["Your website count already reached the limit"]);
			$this->validate->flagErr = true;
			$errMsg['limit_error'] = $this->spTextWeb["Your website count already reached the limit"];
		}
		
		// validate website creation
		if(!$this->validate->flagErr){
			if (!$this->__checkName($listInfo['name'], $userId)) {
			    if (!$this->__checkWebsiteUrl($listInfo['url'])) {
			        $listInfo['title'] = substr($listInfo['title'], 0, 100);
			        $listInfo['description'] = substr($listInfo['description'], 0, 500);
			        $listInfo['keywords'] = substr($listInfo['keywords'], 0, 500);
    				$sql = "insert into websites(name,url,title,description,analytics_view_id,keywords,user_id,status)
    				values('".addslashes($listInfo['name'])."','".addslashes($listInfo['url'])."','".
    				addslashes($listInfo['title'])."','".addslashes($listInfo['description'])."', '".addslashes($listInfo['analytics_view_id'])."', '".
    				addslashes($listInfo['keywords'])."', $userId, $statusVal)";
    				$insertOk = $this->db->query($sql);

    				// bug fix: a second request racing this same __checkWebsiteUrl()
    				// check (TOCTOU) can still hit the DB-level UNIQUE constraint on
    				// websites.url and fail here - the insert's result was never
    				// checked before, so this reported success with nothing actually
    				// persisted. Only the actual duplicate-key error (1062) is
    				// treated as the same "already exists" case; any other insert
    				// failure falls through to the generic error path below instead
    				// of being mislabeled as a duplicate.
    				if (!$insertOk && mysqli_errno($this->db->connectionId) == 1062) {
    				    $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
    				} else if ($apiCall) {
    					return array('success', 'Successfully created website');
    				} else {
	    				$this->listWebsites([]);
	    				exit;
    				}

			    } else {
			        $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
			    }
			}else{
				$errMsg['name'] = formatErrorMsg($this->spTextWeb['Website already exist']);
			}
		}		
		
		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->newWebsite($listInfo);
		}
	}

	function __getWebsiteInfo($websiteId){
		$websiteId = intval($websiteId);
		$sql = "select * from websites where id=$websiteId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	function editWebsite($websiteId, $listInfo=[]) {
		$websiteId = intval($websiteId);
		if (!empty($websiteId) && !$this->__verifyWebsiteOwnership($websiteId)) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}
		if(!empty($websiteId)){
			if(empty($listInfo)){
				$listInfo = $this->__getWebsiteInfo($websiteId);
			}
			
			$listInfo['title'] = stripslashes($listInfo['title']);
			$listInfo['description'] = stripslashes($listInfo['description']);
			$listInfo['keywords'] = stripslashes($listInfo['keywords']);
			$this->set('post', $listInfo);
			
			// get all users
			if(isAdmin()) {
				$userCtrler = New UserController();
				$userList = $userCtrler->__getAllUsers();
				$this->set('userList', $userList);  			
				$this->set('isAdmin', 1);
			}
			
			$propertyList = $this->__getAllAnalyticProperties(TRUE);
			$this->set('propertyList', $propertyList);
			
			$this->set('editAction', 'update');
			$this->render('website/edit_website');
			exit;
		}		
		
		$this->listWebsites([]);
	}

	function updateWebsite($listInfo, $apiCall=false) {
	    $listInfo['url'] = trim($listInfo['url']);
	    $listInfo['name'] = trim($listInfo['name']);
		
		// check whether admin or api calll
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['user_id']) ? isLoggedIn() : $listInfo['user_id'];	
		} else {
			$userId = isLoggedIn();
		}
		
		$listInfo['id'] = intval($listInfo['id']);

		// the web-UI path (not the REST API, which is admin-equivalent by
		// design - see api.ctrl.php) previously never verified the caller
		// owned the website being edited at all - only which user_id it
		// gets REASSIGNED to (above) was ever checked, and only for admins
		if (!$apiCall && !$this->__verifyWebsiteOwnership($listInfo['id'])) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$listInfo['name'] = strip_tags($listInfo['name']);
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkUrl($listInfo['url']));
		$listInfo['url'] = addHttpToUrl($listInfo['url']);
		$statusVal = isset($listInfo['status']) ? "status = " . intval($listInfo['status']) ."," : "";
		
		// check limit
		if(!$this->validate->flagErr && !empty($listInfo['user_id'])){
			$websiteInfo = $this->__getWebsiteInfo($listInfo['id']);
			
			// if user is changed for editing
			if ($websiteInfo['user_id'] != $listInfo['user_id']) {
				
				// verify the limit for the user
				if (!$this->validateWebsiteCount($listInfo['user_id'])) {
					$this->set('validationMsg', $this->spTextWeb["Your website count already reached the limit"]);
					$this->validate->flagErr = true;
					$errMsg['limit_error'] = $this->spTextWeb["Your website count already reached the limit"];
				}	
			}
		}
		
		// verify the form
		if(!$this->validate->flagErr) {

		    if ($this->__checkName($listInfo['name'], $userId, $listInfo['id'])) {
				$errMsg['name'] = formatErrorMsg($this->spTextWeb['Website already exist']);
				$this->validate->flagErr = true;
			}
			
			if ($this->__checkWebsiteUrl($listInfo['url'], $listInfo['id'])) {
			    $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
				$this->validate->flagErr = true;
			}

			if (!$this->validate->flagErr) {
			    $listInfo['title'] = substr($listInfo['title'], 0, 100);
			    $listInfo['description'] = substr($listInfo['description'], 0, 500);
			    $listInfo['keywords'] = substr($listInfo['keywords'], 0, 500);
				$sql = "update websites set
						name = '".addslashes($listInfo['name'])."',
						url = '".addslashes($listInfo['url'])."',
						user_id = $userId,
						title = '".addslashes($listInfo['title'])."',
						description = '".addslashes($listInfo['description'])."',
						analytics_view_id = '".addslashes($listInfo['analytics_view_id'])."',
						$statusVal
						keywords = '".addslashes($listInfo['keywords'])."'
						where id={$listInfo['id']}";
				$this->db->query($sql);
				
				// if api call
				if ($apiCall) {
					return array('success', 'Successfully updated website');
				} else {
					$this->listWebsites([]);
					exit;
				}
				
			}
		}
		
		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->editWebsite($listInfo['id'], $listInfo);
		}		
	}
	
	# func to crawl meta data of a website
	public static function crawlMetaData($websiteUrl, $keyInput='', $pageContent='', $returVal=false) {
	    if (empty($pageContent)) {
    		if(!preg_match('/\w+/', $websiteUrl)) return;
    		$websiteUrl = addHttpToUrl($websiteUrl);
    		$spider = New Spider();
    		$ret = $spider->getContent($websiteUrl);
	    } else {
	        $ret['page'] = $pageContent;
	        $metaInfo = array();
	    }
	    
	    $matches = [];
		if(!empty($ret['page'])) {			
		    if (empty($keyInput)) {		    
    			# meta title
    			preg_match('/<TITLE>(.*?)<\/TITLE>/si', $ret['page'], $matches);
    			if(!empty($matches[1])){
    			    if ($returVal) {
    			        $metaInfo['page_title'] = $matches[1];
    			    } else {
    				    WebsiteController::addInputValue($matches[1], 'webtitle');
    			    }
    			}
    			
    			# meta description
    			preg_match('/<META.*?name="description".*?content="(.*?)"/si', $ret['page'], $matches);		
    			if(empty($matches[1])){
    				preg_match("/<META.*?name='description'.*?content='(.*?)'/si", $ret['page'], $matches);			
    			}
    			
    			if(empty($matches[1])){
    				preg_match('/<META content="(.*?)" name="description"/si', $ret['page'], $matches);					
    			}
    			
    			if(!empty($matches[1])){
    			    if ($returVal) {
    			        $metaInfo['page_description'] = $matches[1];
    			    } else {
    				    WebsiteController::addInputValue($matches[1], 'webdescription');
    			    }
    			}
		    }
			
			# meta keywords
			preg_match('/<META.*?name="keywords".*?content="(.*?)"/si', $ret['page'], $matches);		
			if(empty($matches[1])){
				preg_match("/<META.*?name='keywords'.*?content='(.*?)'/si", $ret['page'], $matches);			
			}
			
			if(empty($matches[1])){
				preg_match('/<META content="(.*?)" name="keywords"/si', $ret['page'], $matches);			
			}
			
			if(!empty($matches[1])){
    	        if ($returVal) {
    			    $metaInfo['page_keywords'] = $matches[1];
    			} else {
				    WebsiteController::addInputValue($matches[1], 'webkeywords');
    			}
			}

			// Extract canonical URL
			if ($returVal) {
				preg_match('/<link.*?rel=["\']canonical["\'].*?href=["\']([^"\']+)["\']/si', $ret['page'], $matches);
				if (empty($matches[1])) {
					preg_match('/<link.*?href=["\']([^"\']+)["\'].*?rel=["\']canonical["\']/si', $ret['page'], $matches);
				}
				if (!empty($matches[1])) {
					$metaInfo['canonical_url'] = trim($matches[1]);
				}

			// Check AI robot compatibility
			$metaInfo['ai_robot_allowed'] = 1; // Default: allowed

			// Check for robots meta tag that blocks AI bots
			// Common AI bot blocking patterns: noindex, nofollow, noarchive, nosnippet
			$aiBlockingBots = ['GPTBot', 'ChatGPT-User', 'Google-Extended', 'anthropic-ai', 'Claude-Web', 'CCBot', 'PerplexityBot', 'Omgilibot'];

			// Check for general robots meta tag
			preg_match('/<meta\s+name=["\']robots["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']robots["\']/si', $ret['page'], $matches);
			}

			if (!empty($matches[1])) {
				$robotsContent = strtolower(trim($matches[1]));
				// If noindex or none is present, AI robots are blocked
				if (stristr($robotsContent, 'noindex') || stristr($robotsContent, 'none')) {
					$metaInfo['ai_robot_allowed'] = 0;
				}
			}

			// Check for specific AI bot blocking meta tags
			foreach ($aiBlockingBots as $botName) {
				preg_match('/<meta\s+name=["\']' . preg_quote($botName, '/') . '["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
				if (empty($matches[1])) {
					preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']' . preg_quote($botName, '/') . '["\']/si', $ret['page'], $matches);
				}

				if (!empty($matches[1])) {
					$botContent = strtolower(trim($matches[1]));
					// If noindex, nofollow, or none is present for AI bots
					if (stristr($botContent, 'noindex') || stristr($botContent, 'nofollow') || stristr($botContent, 'none')) {
						$metaInfo['ai_robot_allowed'] = 0;
						break;
					}
				}
			}

			// Check Mobile-Friendliness (viewport meta tag)
			$metaInfo['mobile_friendly'] = 0; // Default: not mobile-friendly
			preg_match('/<meta\s+name=["\']viewport["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']viewport["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) {
				// Check if viewport has width=device-width or initial-scale
				$viewportContent = strtolower($matches[1]);
				if (stristr($viewportContent, 'width=device-width') || stristr($viewportContent, 'initial-scale')) {
					$metaInfo['mobile_friendly'] = 1;
				}
			}

			// Check HTTPS/SSL Security
			$metaInfo['https_secure'] = 0; // Default: not secure
			if (stristr($websiteUrl, 'https://')) {
				$metaInfo['https_secure'] = 1;
			}

			// Check Open Graph Tags
			$metaInfo['has_og_tags'] = 0; // Default: no OG tags
			$ogTagsFound = 0;
			
			// Check for og:title
			preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+property=["\']og:title["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $ogTagsFound++;

			// Check for og:description
			preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+property=["\']og:description["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $ogTagsFound++;

			// Check for og:image
			preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+property=["\']og:image["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $ogTagsFound++;

			// If at least 2 OG tags found, consider it has OG tags
			if ($ogTagsFound >= 2) {
				$metaInfo['has_og_tags'] = 1;
			}

			// Check Twitter Card Tags
			$metaInfo['has_twitter_cards'] = 0; // Default: no Twitter cards
			$twitterTagsFound = 0;

			// Check for twitter:card
			preg_match('/<meta\s+name=["\']twitter:card["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']twitter:card["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $twitterTagsFound++;

			// Check for twitter:title
			preg_match('/<meta\s+name=["\']twitter:title["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']twitter:title["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $twitterTagsFound++;

			// Check for twitter:description
			preg_match('/<meta\s+name=["\']twitter:description["\']\s+content=["\'](.*?)["\']/si', $ret['page'], $matches);
			if (empty($matches[1])) {
				preg_match('/<meta\s+content=["\'](.*?)["\']\s+name=["\']twitter:description["\']/si', $ret['page'], $matches);
			}
			if (!empty($matches[1])) $twitterTagsFound++;

			// If at least 2 Twitter tags found, consider it has Twitter cards
			if ($twitterTagsFound >= 2) {
				$metaInfo['has_twitter_cards'] = 1;
			}

			// Check Structured Data (JSON-LD schema.org markup) - the
			// machine-facing fact layer AI answer engines (ChatGPT,
			// Perplexity, Google AI Overview) parse to understand what an
			// entity/product/page actually is, distinct from the OG/Twitter
			// tags above which only affect social share previews.
			$metaInfo['has_structured_data'] = 0; // Default: no structured data found
			preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $ret['page'], $jsonLdMatches);
			if (!empty($jsonLdMatches[1])) {
				foreach ($jsonLdMatches[1] as $jsonLdBlock) {
					// require a recognized @type key - an empty or malformed
					// <script> tag would otherwise false-positive as "has
					// structured data"
					if (preg_match('/"@type"\s*:\s*"[^"]+"/i', $jsonLdBlock)) {
						$metaInfo['has_structured_data'] = 1;
						break;
					}
				}
			}

			// Check heading structure - AI answer engines and modern SEO
			// both favor a single H1 (the page's primary topic) with
			// content organized under H2 sections, which makes it easier
			// to "chunk" into extractable passages. Not an attempt at full
			// semantic nesting validation, just the two most impactful
			// signals.
			preg_match_all('/<h1[^>]*>.*?<\/h1>/si', $ret['page'], $h1Matches);
			preg_match_all('/<h2[^>]*>.*?<\/h2>/si', $ret['page'], $h2Matches);
			$metaInfo['heading_structure_ok'] = (count($h1Matches[0]) === 1 && count($h2Matches[0]) >= 1) ? 1 : 0;

			// Check for FAQ-style content - headings phrased as questions
			// are a strong, explainable signal that a page has directly-
			// answerable content AI answer engines can lift verbatim,
			// distinct from the FAQPage JSON-LD check above (a page can
			// have Q&A-style headings without ever marking them up as
			// schema).
			preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/si', $ret['page'], $headingMatches);
			$questionHeadingCount = 0;
			foreach ($headingMatches[1] as $headingText) {
				if (mb_substr(trim(strip_tags($headingText)), -1) === '?') {
					$questionHeadingCount++;
				}
			}
			$metaInfo['has_faq_content'] = ($questionHeadingCount >= 2) ? 1 : 0;

			// Check content depth (word count) - thin pages give AI answer
			// engines (and search engines) little to extract or cite.
			// Strips scripts/styles/tags first so markup and inline JS/CSS
			// don't inflate the count.
			$bodyText = preg_replace('/<script\b[^>]*>.*?<\/script>/si', ' ', $ret['page']);
			$bodyText = preg_replace('/<style\b[^>]*>.*?<\/style>/si', ' ', $bodyText);
			$bodyText = html_entity_decode(strip_tags($bodyText), ENT_QUOTES);
			$bodyText = trim(preg_replace('/\s+/u', ' ', $bodyText));
			$metaInfo['word_count'] = empty($bodyText) ? 0 : count(preg_split('/\s+/u', $bodyText));

			// Check if page is blocked by robots.txt
			$metaInfo['blocked_by_robots'] = Spider::isBlockedByRobotsTxt($websiteUrl, $websiteUrl);
		}
	} else if (empty($pageContent) && !$returVal) {
		// UX fix: the live "Crawl Meta Data" button call (empty
		// $pageContent, $returVal=false) previously echoed NOTHING at
		// all when the crawl failed (bad/unreachable URL, timeout,
		// blocked by the SSRF guard, a non-HTML response, etc.) - the
		// AJAX call still completed, so the loading spinner cleared,
		// but the #crawlstats div was simply replaced with an empty
		// string and the user had no idea whether it worked, failed, or
		// why. Echoes a visible error into that same div instead, using
		// the same addInputValue()-style pattern (a <script> block that
		// sets the target element directly) already used elsewhere in
		// this exact method.
		// this URL comes straight from $_POST['url']/$_GET['url'] with
		// zero validation (unlike checkUrl()-gated website registration),
		// and curl error messages commonly echo the failing host/URL
		// back verbatim - htmlspecialchars(), not just quote-escaping,
		// since the string below is assigned via innerHTML (which DOES
		// get HTML-parsed), unlike addInputValue()'s .value assignment
		// elsewhere in this method (never HTML-parsed, safe by nature)
		$errorText = !empty($ret['errmsg']) ? $ret['errmsg'] : 'Could not fetch the URL. Please check it and try again.';
		$errorText = htmlspecialchars(removeNewLines($errorText), ENT_QUOTES);
		?>
		<script type="text/javascript">
		document.getElementById('crawlstats').innerHTML = '<span class="text-danger"><i class="ri-error-warning-line"></i> <?php echo $errorText; ?></span>';
		</script>
		<?php
	}

	return $metaInfo;
	}
	
	public static function addInputValue($value, $col) {
		$value = removeNewLines($value);
		?>
		<script type="text/javascript">
			document.getElementById('<?php echo $col;?>').value = '<?php echo str_replace("'", "\'", $value);?>';
		</script>
		<?php
	}
	
	function showImportWebsites() {
		$userId = isLoggedIn();
		
		# get all users
		if(isAdmin()){
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);
			$this->set('isAdmin', 1);
		}

		// Check the user website count for validation
		if (!isAdmin()) {
			$this->setValidationMessageForLimit($userId);
		}
		
		$this->set('delimiter', ',');
		$this->set('enclosure', '"');
		$this->set('escape', '\\');
		$this->render('website/importwebsites');
	}

	# function to set validation message for the limit
	function setValidationMessageForLimit($userId) {
	
		// Check the user website count for validation
		$userTypeCtrlr = new UserTypeController();
		$userWebsiteCount = $this->__getCountAllWebsites($userId, false);
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		$validCount = $userTypeDetails['websitecount'] - $userWebsiteCount;
		$validCount = $validCount > 0 ? $validCount : 0;
		$validationMsg = str_replace("[websitecount]", "<b>$validCount</b>", $this->spTextWeb['You can add only websitecount websites more']);
		$this->set('validationMsg', $validationMsg);
		return $validationMsg;
			
	}
	
	function importWebsiteFromCsv($info) {
		
		// if csv file is not uploaded
		if (empty($_FILES['website_csv_file']['name'])) {
			print "<script>alert('".$this->spTextWeb['Please enter CSV file']."')</script>";
			return False;
		}
		
		// if csv file is not uploaded
		$spCsvMime = mime_content_type($_FILES['website_csv_file']['tmp_name']);
		if (!in_array($spCsvMime, array('text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'))) {
		    print "<script>alert('".$this->spTextWeb['Please enter CSV file']."')</script>";
		    return False;
		}
		
		$userId = isAdmin() ? intval($info['userid']) : isLoggedIn();
		$count = 0;
		$resultInfo = array(
			'total' => 0,
			'valid' => 0,
			'invalid' => 0,
		);
				 
		// process file upload option
		$fileInfo = $_FILES['website_csv_file'];
		if (!empty($fileInfo['name']) && !empty($userId)) {
			$uploadedExt = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
			if ($uploadedExt === 'csv' && ($fileInfo["type"] == "text/csv" || $fileInfo["type"] == "application/vnd.ms-excel")) {
				// Use a random server-generated filename to prevent attacker-controlled filenames
				$safeFilename = uniqid('import_', true) . '.csv';
				$targetFile = SP_TMPPATH . "/" . $safeFilename;
				if(move_uploaded_file($fileInfo['tmp_name'], $targetFile)) {

					$delimiterChar = empty($info['delimiter']) ? ',' : $info['delimiter'];
					$enclosureChar = empty($info['enclosure']) ? '"' : $info['enclosure'];
					$escapeChar = empty($info['escape']) ? '\\' : $info['escape'];

					// open file read through csv file
					if (($handle = fopen($targetFile, "r")) !== FALSE) {

						// loop through the data row
						while (($websiteInfo = fgetcsv($handle, 4096, $delimiterChar, $enclosureChar, $escapeChar)) !== FALSE) {
							if (empty($websiteInfo[0])) continue;
							$count++;
						}

						fclose($handle);
					}

					// Check the user website count for validation
					if (!$this->validateWebsiteCount($userId, $count)) {
						@unlink($targetFile);
						$validationMag = strip_tags($this->setValidationMessageForLimit($userId));
						print "<script>alert('$validationMag')</script>";
						return False;
					}

					// open file read through csv file
					if (($handle = fopen($targetFile, "r")) !== FALSE) {

						// loop through the data row
						while (($websiteInfo = fgetcsv($handle, 4096, $delimiterChar, $enclosureChar, $escapeChar)) !== FALSE) {
							if (empty($websiteInfo[0])) continue;
							$status = $this->importWebsite($websiteInfo, $userId);
							$resultInfo[$status] += 1;
							$resultInfo['total'] += 1;
						}

						fclose($handle);
					}

					@unlink($targetFile);
				}
			}
		}


		$text = "<p class=\'note\' id=\'note\'><b>Website import process started. It will take some time depends on the number of websites needs to be imported!</b></p><div id=\'subcontmed\'></div>";
		print "<script type='text/javascript'>parent.document.getElementById('import_website_div').innerHTML = '$text';</script>";
		print "<script>parent.showLoadingIcon('subcontmed', 0)</script>";
		$spText = $_SESSION['text'];
		$resText = '<table width="40%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab" align="center">'.
		'<tr><td class="topheader" colspan="10">Import Summary</td></tr>'.
				'<tr><th class="leftcell">'.$spText['common']['Total'].':</th><td>'.$resultInfo['total'].'</td><th>Valid:</th><td>'.$resultInfo['valid'].'</td></tr>'.
				'<tr><th>Invalid:</th><td>'.$resultInfo['invalid'].'</td><th>&nbsp;</th><td>&nbsp;</td></tr>'.
		'</table>';
		echo "<script type='text/javascript'>parent.document.getElementById('subcontmed').innerHTML = '$resText'</script>";
        echo "<script type='text/javascript'>parent.document.getElementById('note').style.display='none';</script>";
	}
	
	function importWebsite($wInfo, $userId) {
		$status = 'invalid';
		
		if (!empty($wInfo[0]) && !empty($wInfo[1])) {
		    $listInfo = [];
			$listInfo['name'] = trim($wInfo[0]);
			$listInfo['url'] = trim($wInfo[1]);
			$listInfo['title'] = $wInfo[2] ? trim($wInfo[2]) : "";
			$listInfo['description'] = $wInfo[3] ? trim($wInfo[3]) : "";
			$listInfo['keywords'] = $wInfo[4] ? trim($wInfo[4]) : "";
			$listInfo['status'] = intval($wInfo[5]);
			$listInfo['analytics_view_id'] = $wInfo[6] ? trim($wInfo[6]) : "";
			$listInfo['userid'] = $userId;
			$return = $this->createWebsite($listInfo, true);
			
			if ($return[0] == 'success') {
				$status = "valid";
			}
		}
		
		return $status;
	}
	
	// Function to check / validate the user type website count
	function validateWebsiteCount($userId, $newCount = 1) {
		$userCtrler = new UserController();

		// if admin user id return true
		if ($userCtrler->isAdminUserId($userId)) {
			return true;
		}
		
		$userTypeCtrlr = new UserTypeController();
		$userWebsiteCount = $this->__getCountAllWebsites($userId, false);
		$userWebsiteCount += $newCount;
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);

		// if limit is set and not -1
		if (isset($userTypeDetails['websitecount']) && $userTypeDetails['websitecount'] >= 0) {
			
			// check whether count greater than limit
			if ($userWebsiteCount <= $userTypeDetails['websitecount']) {
				return true;	
			} else {
				return false;	
			}
			
		} else {
			return true;
		}			
	}
	
	function showimportWebmasterToolsWebsites() {		
		$userId = isLoggedIn();
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$userCtrler = New UserController();
		
		// get all users
		if(isAdmin()){
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);
			$this->set('isAdmin', 1);
		} else {
			$userInfo = $userCtrler->__getUserInfo($userId);
			$this->set('userName', $userInfo['username']);
		}

		// Check the user website count for validation
		if (!isAdmin()) {
			$this->setValidationMessageForLimit($userId);
		}
		
		$this->render('website/import_webmaster_tools_websites');
	}
	
	function importWebmasterToolsWebsites($info) {
		$userId = isAdmin() ? intval($info['userid']) : isLoggedIn();
		$limitReached = false;
		$importList = array();
	
		// verify the limit for the user
		if (!$this->validateWebsiteCount($userId)) {
			showErrorMsg($this->spTextWeb["Your website count already reached the limit"]);
		}
	
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->getAllSites($userId);
		
		// check whether error occured while api call
		if (!$result['status']) {
			showErrorMsg($result['msg']);
		}
	
		// loop through website list
		foreach ($result['resultList'] as $websiteInfo) {
				
			if ($websiteInfo->permissionLevel != 'siteOwner') continue;
				
			// chekc whether website existing or not
			if (!$this->__checkWebsiteUrl($websiteInfo->siteUrl) && !$this->__checkWebsiteUrl(Spider::removeTrailingSlash($websiteInfo->siteUrl))) {
				$websiteName = formatUrl($websiteInfo->siteUrl, false);
				$websiteName = Spider::removeTrailingSlash($websiteName);
				$listInfo['name'] = $websiteName;
				$listInfo['url'] = $websiteInfo->siteUrl;
				$listInfo['title'] = $websiteName;
				$listInfo['description'] = $websiteName;
				$listInfo['keywords'] = $websiteName;
				$listInfo['status'] = 1;
				$listInfo['userid'] = $userId;
				$return = $this->createWebsite($listInfo, true);
	
				// if success, check of number of websites can be added
				if ($return[0] == 'success') {
					$importList[] = $websiteInfo->siteUrl;

					// if reached website add limit
					if (!$this->validateWebsiteCount($userId)) {
						$limitReached = true;
						break;
					}
						
				}
	
			}
				
		}
		
		// show results	
		showSuccessMsg("<b>".$this->spTextWeb["Successfully imported following websites"]."</b>:", false);
		foreach ($importList as $url) showSuccessMsg($url, false);
		
		// if website add limit reached
		if ($limitReached) {
			showErrorMsg($this->spTextWeb["Your website count already reached the limit"]);
		}		
	
	}
	
	function addToWebmasterTools($websiteId) {
		$webisteInfo = $this->__getWebsiteInfo($websiteId);
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->addWebsite($webisteInfo['url'], $webisteInfo['user_id']);
		
		// chekc whether error occured while api call
		if ($result['status']) {
			$activateUrl = "https://www.google.com/webmasters/verification/verification?tid=alternate&siteUrl=" . $webisteInfo['url'];
			$successMsg = $this->spTextWeb["Website successfully added to webmaster tools"] . ": " . $webisteInfo['url'] . "<br><br>";
			$successMsg .= "<a href='$activateUrl' target='_blank'>Click Here</a> to activate the website in webmaster tools.";
			showSuccessMsg($successMsg, false);
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}
	
	// func to list sitemaps
	function listSitemap($info, $summaryPage = false, $cronUserId=false) {
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$this->set('spTextSitemap', $this->getLanguageTexts('sitemap', $_SESSION['lang_code']));
		$this->set('spTextHome', $this->getLanguageTexts('home', $_SESSION['lang_code']));
		$this->set('spTextDirectory', $this->getLanguageTexts('directory', $_SESSION['lang_code']));
		
		$websiteList = $this->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
		$this->set('websiteId', $websiteId);
		
		$whereCond = "status=1";
		
		// check for wbsite id
		if (empty($websiteId)) {
			$wIdList = [0];
			foreach ($websiteList as $websiteInfo) $wIdList[] = $websiteInfo['id'];
			$whereCond .= " and website_id in (".implode(',', $wIdList).")";
		} else {
			$whereCond .= " and website_id=$websiteId";
		}
		
		$sitemapList = $this->dbHelper->getAllRows("webmaster_sitemaps", $whereCond);
		
		$this->set('list', $sitemapList);
		$this->set('summaryPage', $summaryPage);
		
		// if pdf export
		if ($summaryPage) {
			return $this->getViewContent('sitemap/list_webmaster_sitemap_list');
		} else {
			$this->render('sitemap/list_webmaster_sitemap_list');
		}
		
	}
	
	// func to import webmaster tools sitemaps
	function importWebmasterToolsSitemaps($websiteId, $cronJob = false) {
		$websiteId = intval($websiteId);
		$webisteInfo = $this->__getWebsiteInfo($websiteId);

		// call webmaster api
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->getAllSitemap($webisteInfo['url'], $webisteInfo['user_id']);
		
		// check whether error occured while api call
		if ($result['status']) {
			
			// change status of all sitemaps
			$dataList = array('status' => 0);
			$this->dbHelper->updateRow("webmaster_sitemaps", $dataList, " website_id=$websiteId");
			
			// loop through webmaster tools list
			foreach ($result['resultList'] as $sitemapInfo) {
				
				$dataList = array(
					'last_submitted' => date('Y-m-d H:i:s', strtotime($sitemapInfo->lastSubmitted)),
					'last_downloaded' => date('Y-m-d H:i:s', strtotime($sitemapInfo->lastDownloaded)),
					'is_pending|int' => $sitemapInfo->isPending,
					'warnings|int' => $sitemapInfo->warnings,
					'errors|int' => $sitemapInfo->errors,
					'submitted|int' => $sitemapInfo->contents[0]['submitted'],
					'indexed|int' => $sitemapInfo->contents[0]['indexed'],
					'status' => 1,
				);
				
				$rowInfo = $this->dbHelper->getRow("webmaster_sitemaps", " website_id=$websiteId and path='$sitemapInfo->path'");
				if (!empty($rowInfo['id'])) {
					$this->dbHelper->updateRow("webmaster_sitemaps", $dataList, " id=" . $rowInfo['id']);
				} else {
					$dataList['website_id|int'] = $websiteId;
					$dataList['path'] = $sitemapInfo->path;
					$this->dbHelper->insertRow("webmaster_sitemaps", $dataList);
				}
				
			}
			
			if ($cronJob) {
				echo $this->spTextWeb["Successfully sync sitemaps from webmaster tools"] . "<br>\n";
			} else {
				showSuccessMsg($this->spTextWeb["Successfully sync sitemaps from webmaster tools"], false);
			}
			
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}
	
	// func to show submit sitemap form
	function showSubmitSitemap($info) {
		$userId = isLoggedIn();
		$this->set('websiteList', $this->__getAllWebsites($userId, true));
		$this->set('websiteId', intval($info['website_id']));
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$this->render('sitemap/submit_sitemap');
	}
	
	// func to submit sitemap
	function submitSitemap($info) {		
		$webisteInfo = $this->__getWebsiteInfo($info['website_id']);
		$spTextWebproxy = $this->getLanguageTexts('QuickWebProxy', $_SESSION['lang_code']);		
		
		if (empty($info['sitemap_url'])) {
			showErrorMsg($spTextWebproxy["Please enter a valid url"]);
		}
		
		$info['sitemap_url'] = addHttpToUrl($info['sitemap_url']);
		
		// if website url not correct
		if (!preg_match("/". preg_quote($webisteInfo['url'], '/') ."/i", $info['sitemap_url'])) {
			showErrorMsg($spTextWebproxy["Please enter a valid url"]);
		}
		
		// call webmaster api
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->submitSitemap($webisteInfo['url'], $info['sitemap_url'], $webisteInfo['user_id']);
		
		// check whether error occured while api call
		if ($result['status']) {
			showSuccessMsg($this->spTextWeb["Sitemap successfully added to webmaster tools"] . ": " . $info['sitemap_url'], false);
			
			// update seo panel webmaster tool sitemaps
			$this->importWebmasterToolsSitemaps($webisteInfo['id']);
			
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}

	function deleteWebmasterToolSitemap($sitemapId) {
		$sitemapId = intval($sitemapId);
		$sitemapInfo = $this->dbHelper->getRow("webmaster_sitemaps", "id=$sitemapId");
		
		if (empty($sitemapInfo['id'])) {
			showErrorMsg("Please provide a valid sitemap id");
		}
		
		$webisteInfo = $this->__getWebsiteInfo($sitemapInfo['website_id']);
		$gapiCtrler = new WebMasterController();		
		$result = $gapiCtrler->deleteWebsiteSitemap($webisteInfo['url'], $sitemapInfo['path'], $webisteInfo['user_id']);
	
		// check whether error occured while api call
		if ($result['status']) {
			$this->dbHelper->updateRow("webmaster_sitemaps", array('status' => 0), "id=$sitemapId");
			showSuccessMsg($this->spTextWeb["Successfully deleted sitemap from webmaster tools"], false);
		} else {
			showErrorMsg($result['msg'], false);
		}
		
		$this->listSitemap(array('website_id' => $sitemapInfo['website_id']));	
	}	
	
	function syncGoogleAnalyticProperties($userId) {
	    $debug = [];
	    $debug[] = "--- syncGoogleAnalyticProperties START ---";

	    $userId = intval($userId);
	    $debug[] = "User ID: $userId";

	    $analyticList = $this->dbHelper->getAllRows("analytics_properties", "user_id=$userId");
	    $debug[] = "Existing DB properties count: " . count($analyticList);

	    $propertyList = createSelectList($analyticList, "ALL", 'property_id');

	    $GoogleCtrl  = new GoogleAPIController();
	    $debug[] = "Calling getanalyticWebsitesPropertyIds...";

	    [$status, $result, $errMsg, $apiDebug] = $GoogleCtrl->getanalyticWebsitesPropertyIds($userId);
	    $debug = array_merge($debug, $apiDebug);
	    $debug[] = "API call status: " . ($status ? 'TRUE' : 'FALSE');
	    $debug[] = "API msg: $errMsg";
	    $debug[] = "Properties returned from API: " . count($result);

	    if($status && !empty($result)) {
	        foreach ($result as $data) {
	            $propertyId = $data['property_id'];
	            $debug[] = "Processing property: {$data['property_name']} (ID: $propertyId, Account: {$data['account_name']})";

	            if (!empty($propertyList[$propertyId])) {
	                $propertyDbId = $propertyList[$propertyId]['id'];
	                $debug[] = "  -> EXISTS in DB (db_id=$propertyDbId), updating...";
	                $dataList = [
	                    'user_id' => $userId,
	                    'account_name' => $data['account_name'],
	                    'account_id' => $data['account_id'],
	                    'property_name' => $data['property_name'],
	                    'datetime_updated' => date('Y-m-d H:i:s'),
	                ];

	                $this->dbHelper->updateRow('analytics_properties', $dataList, "id=$propertyDbId");
	                $debug[] = "  -> Updated OK";
	            } else {
	                $debug[] = "  -> NEW property, inserting...";
	                $dataList = [
	                    'user_id' => $userId,
	                    'account_name' => $data['account_name'],
	                    'account_id' => $data['account_id'],
	                    'property_name' => $data['property_name'],
	                    'property_id' => $data['property_id'],
	                ];

	                $this->dbHelper->insertRow('analytics_properties', $dataList);
	                $debug[] = "  -> Inserted OK";
	            }
	        }
	    } else {
	        $debug[] = "No properties returned or API call failed.";
	    }

	    $debug[] = "--- syncGoogleAnalyticProperties END ---";
	    return [$status, $errMsg, $debug];
	}
	
	function fetchGoogleAnalyticProperties() {
	    $debug = [];
	    $debug[] = "[" . date('Y-m-d H:i:s') . "] fetchGoogleAnalyticProperties triggered";

	    $response = ['status' => 0, 'data' => [], 'msg' => "API Error", 'debug' => []];
	    $userId = isLoggedIn();
	    $debug[] = "Logged-in user ID: $userId";

	    // sync google analytics properties using the connection
	    [$status, $errMsg, $syncDebug] = $this->syncGoogleAnalyticProperties($userId);
	    $debug = array_merge($debug, $syncDebug);
	    $debug[] = "syncGoogleAnalyticProperties returned status: " . ($status ? 'TRUE' : 'FALSE') . ", msg: $errMsg";

	    if ($status) {
	        $propertyList = $this->__getAllAnalyticProperties(TRUE);
	        $debug[] = "Final property list count for dropdown: " . count($propertyList);
	        $response['data'] = $propertyList;
	        $response['status'] = TRUE;
	    } else {
	        $response['msg'] = $errMsg;
	        $debug[] = "Returning error response: $errMsg";
	    }

	    // Internal diagnostics only — never expose account/property IDs or API
	    // error internals to the browser outside of debug mode.
	    $response['debug'] = (defined('SP_DEBUG') && SP_DEBUG) ? $debug : [];
	    return $response;
	}
	
	function __getUserAnalyticProperties($userId) {
	    $userId = intval($userId);
	    $sql = "SELECT *
                FROM analytics_properties
                WHERE user_id= $userId ORDER BY account_name, property_name, property_id";
	    $list = $this->db->select($sql);
	    return $list;
	}
	
	function __getAllAnalyticProperties($asSelectList=FALSE) {
	    $sql = "SELECT *
                FROM analytics_properties
                ORDER BY account_name, property_name, property_id";
	    $list = $this->db->select($sql);
	    
	    // if return as property list
	    if ($asSelectList) {
	        $propertyList = [];
	        foreach ($list as $item) {
	            if (empty($item['view_id'])) {
	                $propertyList[$item['property_id']] = $item['account_name'].' - '.$item['property_name'] ."({$item['property_id']})";
	            }
	        }
	        
	        return $propertyList;
	    } else {
	        return $list;
	    }
	}
}
?>