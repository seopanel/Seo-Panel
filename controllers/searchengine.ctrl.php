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

# class defines all search engine controller functions
class SearchEngineController extends Controller{
    var $seTable = "searchengines";
	
	# func to get all search engines
	function __getAllSearchEngines(){
		$sql = "select * from searchengines where status=1";
		$seList = $this->db->select($sql);
		return $seList;
	}
	
	function __searchSerachEngines($searchInfo=[]) {
	    $cond = "1=1";
	    $cond .= isset($searchInfo['status']) ? " and status=".intval($searchInfo['status']) : "";
	    $cond .= isset($searchInfo['search']) ? " and url like '%".addslashes($searchInfo['search'])."%'" : "";
	    return $this->dbHelper->getAllRows('searchengines', $cond);
	}
	
	# func to get search engine info
	function __getsearchEngineInfo($seId){
		$sql = "select * from searchengines where id=$seId";
		$seList = $this->db->select($sql, true);
		return $seList;
	}
	
	# func to get all search engines
	function __getAllCrawlFormatedSearchEngines(){
		$sql = "select * from searchengines where status=1";
		$list = $this->db->select($sql);
		$seList = array();
		foreach($list as $seInfo){
			$seId = $seInfo['id'];
			$seInfo['regex'] = "/".$seInfo['regex']."/is";
			$search = array('[--num--]');
			$replace = array($seInfo['no_of_results_page']);
			$seInfo['url'] = str_replace($search, $replace, $seInfo['url']);
			$seList[$seId] = $seInfo;
		}
		
		return $seList;
	}
	
	# func to show search engines
	function listSE($info=[]){
		$info = sanitizeData($info);
		$info['stscheck'] = isset($info['stscheck']) ? intval($info['stscheck']) : 1;
		$pageScriptPath = 'searchengine.php?stscheck=' . $info['stscheck'];
		$sql = "select * from searchengines where status='{$info['stscheck']}'";
		
		// search for search engine name
		if (!empty($info['se_name'])) {
			$sql .= " and domain like '%".addslashes($info['se_name'])."%'";
			$pageScriptPath .= "&se_name=" . $info['se_name'];
		}
		
		$sql .= " order by id"; 
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content', 'layout=ajax');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		$seList = $this->db->select($sql);
		$this->set('seList', $seList);

		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);
		$this->set('pageScriptPath', $pageScriptPath);
		$this->set('pageNo', $info['pageno']);		
		$this->render('searchengine/list', 'ajax');
	}
	
	// func to change status of search engine
	function __changeStatus($seId, $status){		
		$seId = intval($seId);
// 		$sql = "update searchengines 
//                 set status=$status 
//                 where id=$seId";
		
		
		
		
// 		$this->db->query($sql);
		
		
		$dataList = [
		    'status|int' => $status,
		    'updated' => "NOW()",
		];
		$this->dbHelper->updateRow($this->seTable, $dataList, "id=$seId");
	}
	
	# func to delete search engine
	function __deleteSearchEngine($seId){
		$seId = intval($seId);
		$sql = "delete from searchengines where id=$seId";
		$this->db->query($sql);
		
		$sql = "select id from searchresults where searchengine_id=$seId";
		$recordList = $this->db->select($sql);
		
		if(count($recordList) > 0) {
			foreach($recordList as $recordInfo){
				$sql = "delete from searchresultdetails where searchresult_id=".$recordInfo['id'];
				$this->db->query($sql);
			}
			
			$sql = "delete from searchresults where searchengine_id=$seId";
			$this->db->query($sql);
		}
	}
	
	# function to check whether captcha found in search engine results
	public static function isCaptchInSearchResults($searchContent) {
		$captchFound = false;
		
		// if captcha input field is found
		if (stristr($searchContent, 'name="captcha"') || stristr($searchContent, 'id="captcha"') || stristr($searchContent, 'recaptcha/api')) {
			$captchFound = true;
		}
		
		return $captchFound;
	}
	
	// Function to check / validate the user type searh engine count
	public static function validateSearchEngineCount($userId, $count) {
		$userCtrler = new UserController();
		$validation = array('error' => false);

		// if admin user id return true
		if ($userCtrler->isAdminUserId($userId)) {
			return $validation;
		}
		
		$userTypeCtrlr = new UserTypeController();
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		
		// if limit is set and not -1
		if (isset($userTypeDetails['searchengine_count']) && $userTypeDetails['searchengine_count'] >= 0) {
		
			// check whether count greater than limit
			if ($count > $userTypeDetails['searchengine_count']) {
				$validation['error'] = true;
				$spTextSubs = $userTypeCtrlr->getLanguageTexts('subscription', $_SESSION['lang_code']);
				$validation['msg'] = formatErrorMsg(str_replace("[limit]", $userTypeDetails['searchengine_count'], $spTextSubs['total_count_greater_account_limit']));
			}
			
		}
		
		return $validation;
	}
	
	// func to show sync search engines
	function showSyncSearchEngines($info=[]) {	    
	    $pageScriptPath = 'searchengine.php?sec=sync-se';
	    $sql = "select * from sync_searchengines order by sync_time DESC";
	    
	    # pagination setup
	    $this->db->query($sql, true);
	    $this->paging->setDivClass('pagingdiv');
	    $this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
	    $pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content', 'layout=ajax');
	    $this->set('pagingDiv', $pagingDiv);
	    $sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
	    $syncList = $this->db->select($sql);
	    $this->set('syncList', $syncList);
	    
	    $this->set('pageScriptPath', $pageScriptPath);
	    $this->set('pageNo', $info['pageno']);
	    $this->render('searchengine/list_sync_searchengines');
	}
	
	// do sync search engines from sp main website
	function doSyncSearchEngines($checkAlreadyExecuted = false, $cronJob = false) {
	    // check whether already executed sync
	    if ($checkAlreadyExecuted) {
	        $row = $this->dbHelper->getRow("sync_searchengines", "sync_time>TIMESTAMP(DATE_SUB(NOW(), INTERVAL ". SP_SYNC_SE_INTERVAL ." day))");
	        if (!empty($row['id'])) {
	            return ['status' => false, 'result' => "Search engines already synced."];
	        }
	    }
	    
	    $dataList = ['status' => 0];
	    $syncUrl = SP_MAIN_SITE . "/get_searchengine_updates.php";
	    $ret = $this->spider->getContent($syncUrl);
	    if (!empty($ret['page'])) {
	        
	        // check whethere required content exists in the page crawled
	        if (stristr($ret['page'], 'UPDATE ')) {
	            // add some formatting to content to prevent any sql injections
	            $searchTags = ["drop ", "truncate ", "delete ", "alter "];
	            $ret['page'] = str_ireplace($searchTags, "", $ret['page']);
	            
	            $queryList = explode(';', $ret['page']);
	            foreach ($queryList as $query) {
	                $query = trim($query);
	                if (!empty($query)) {
	                   $this->db->query(trim($query));
	                }
	            }
	            
	            $dataList['result'] = "Search engines successfully synced.";
	            $dataList['status'] = 1;
	            
	            // update admin alerts section
	            if ($cronJob) {
	                $alertCtrl = new AlertController();
	                $alertInfo = array(
	                    'alert_subject' => "Search Engine Sync",
	                    'alert_message' => $dataList['result'],
	                    'alert_category' => "reports",
	                    'alert_url' => SP_WEBPATH,
	                );
	                $alertCtrl->createAlert($alertInfo, false, true);
	            }	            
	        } else {
	            $dataList['result'] = "Internal error occured during search engine sync.";
	        }	        
	    } else {
	        $dataList['result'] = $ret['errmsg'];
	    }
	    
	    $this->dbHelper->insertRow("sync_searchengines", $dataList);
	    return $dataList;
	}
	
	function editSearchEngine($seId, $listInfo=[]) {
	    $seId = intval($seId);
	    $this->set('editAction', "updateSearchEngine");
	    if(!empty($seId)) {
	        if(empty($listInfo)) {
	            $listInfo = $this->__getsearchEngineInfo($seId);
	        }
	        
	        $this->set('post', $listInfo);
	        $this->render('searchengine/edit_searchengine');
	        exit;
	    }
	    
	    $this->listSE();
	}
	
	function updateSearchEngine($listInfo) {
	    $seId = intval($listInfo['id']);
	    $seInfo = $this->__getsearchEngineInfo($seId);
	    $this->set("post", $seInfo);
	    $errMsg = [];
	    
	    // validate form
	    $errMsg['max_results'] = formatErrorMsg($this->validate->checkNumber($listInfo['max_results']));
	    if ( ($listInfo['max_results'] < 1) || ($listInfo['max_results'] > 100)) {
	        $this->validate->flagErr = TRUE;
	        $errMsg['max_results'] = formatErrorMsg("Please enter value in between 1 to 100");
	    }
	    
	    if (!$this->validate->flagErr && !empty($seInfo)) {
	        $dataList = [
	            'max_results|int' => $listInfo['max_results'],
	            'updated' => "NOW()",
	        ];
	        
	        $this->dbHelper->updateRow($this->seTable, $dataList, "id=$seId");
	        $this->listSE(['se_name' => $seInfo['domain']]);
	        return TRUE;
	    }
	    
	    $this->set('errMsg', $errMsg);
	    $this->editSearchEngine($seId, $listInfo);
	}
}
?>