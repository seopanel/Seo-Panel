<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   		   *
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

# class defines all site auditor controller functions
class SiteAuditorController extends Controller{
    
    var $cron = false;                    // to identify whether it is executed through cron
    var $seArr = array('google', 'bing'); // the array contains search engines 
    
	function showAuditorProjects($info=[]) {
		$info['userid'] = intval($info['userid']);
	    $userId = isLoggedIn();
		if(isAdmin()){
			$sql = "select ap.*,w.name,u.username from websites w,users u,auditorprojects ap where ap.website_id=w.id and u.id=w.user_id";
			$sql .= empty($info['userid']) ? "" : " and w.user_id=".$info['userid']; 
			$sql .= " order by ap.score DESC,ap.id";			
			$this->set('isAdmin', 1);
			
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			
		}else{
			$sql = "select w.name,ap.* from websites w, auditorprojects ap where ap.website_id=w.id and user_id=$userId order by ap.id";	
		}		
		$this->set('userId', empty($info['userid']) ? 0 : $info['userid']);		
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('siteauditor.php?userid='.$info['userid']);		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
				
		$projectList = $this->db->select($sql);
		foreach ($projectList as $i => $projectInfo) {
		    $projectList[$i]['total_links'] = $this->getCountcrawledLinks($projectInfo['id']);
		    $projectList[$i]['crawled_links'] = $this->getCountcrawledLinks($projectInfo['id'], true);
		    $projectList[$i]['last_updated'] = $this->getProjectLastUpdate($projectInfo['id']);
		}	
		$this->set('pageNo', $info['pageno']);		
		$this->set('list', $projectList);
		$this->render('siteauditor/list');    
	}

	// func to create new project
	function newProject($info=[]){
						
		$userId = isLoggedIn();		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);		
		$websiteId = empty($info['website_id']) ? $websiteList[0]['id'] : intval($info['website_id']);
		$this->set('websiteId', $websiteId);
		
		if (!isset($info['website_id'])) {
		    $info['max_links'] = SA_MAX_NO_PAGES;
		    $info['cron'] = 1;
		    $this->set('post', $info);
		}
		
		$this->render('siteauditor/new');
	}	
	
	// func to create project
	function createProject($listInfo){
	    
		$userId = isLoggedIn();		
		$this->set('post', $listInfo);
		$listInfo['website_id'] = intval($listInfo['website_id']);
		$listInfo['max_links'] = intval($listInfo['max_links']);
		
		$errMsg['website_id'] = formatErrorMsg($this->validate->checkBlank($listInfo['website_id']));
		$errMsg['max_links'] = formatErrorMsg($this->validate->checkNumber($listInfo['max_links']));
		if(!$this->validate->flagErr){
		    $errorFlag = 0;		    
		    if ($listInfo['max_links'] > SA_MAX_NO_PAGES) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = formatErrorMsg($this->spTextSA["Number of pages is greater than"]. " ". SA_MAX_NO_PAGES);
		    }
		    
		    if ($listInfo['max_links'] <= 0) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = formatErrorMsg($this->spTextSA["Number of pages should be greater than"]. " 0");
		    }
		    
		    $webCtrler = New WebsiteController();
		    $websiteInfo = $webCtrler->__getWebsiteInfo($listInfo['website_id']);
		    
		    // commented to exclude site auditor with query arguments also by with out adding fulll url
		    /*$excludeInfo = $this->checkExcludeLinks($listInfo['exclude_links'], $websiteInfo['url']);
		    if (!empty($excludeInfo['err_msg'])) {
		        $errorFlag = 1;
		        $errMsg['exclude_links'] = formatErrorMsg($excludeInfo['err_msg']);		        
		    }
		    $listInfo['exclude_links'] = $excludeInfo['exclude_links'];*/
		    
		    // check for max links allowed for the account type
		    $maxValidInfo = $this->validateMaxLinkCount($websiteInfo['user_id'], $listInfo['max_links']);
		    if ($maxValidInfo['error']) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = $maxValidInfo['msg'];
		    }

		    // Validate sitemap URL if provided
		    if (!empty($listInfo['sitemap_url'])) {
		        $sitemapUrl = trim($listInfo['sitemap_url']);
		        if (!filter_var($sitemapUrl, FILTER_VALIDATE_URL)) {
		            $errorFlag = 1;
		            $errMsg['sitemap_url'] = formatErrorMsg("Invalid sitemap URL format");
		        }
		    }

		    if (!$errorFlag) {
    			if (!$this->isProjectExists($listInfo['website_id'])) {
    				$sql = "insert into auditorprojects(website_id,max_links,exclude_links,exclude_extensions,sitemap_url,check_pr,check_backlinks,check_indexed,store_links_in_page,check_brocken,cron)
							values({$listInfo['website_id']},{$listInfo['max_links']},'".addslashes($listInfo['exclude_links'])."','".addslashes($listInfo['exclude_extensions'])."','".addslashes(trim($listInfo['sitemap_url']))."',
							".intval($listInfo['check_pr']).",".intval($listInfo['check_backlinks']).",".intval($listInfo['check_indexed']).",
							".intval($listInfo['store_links_in_page']).",".intval($listInfo['check_brocken']).",".intval($listInfo['cron']).")";
    				$this->db->query($sql);
    				$this->showAuditorProjects();
    				exit;
    			}else{			
    				$errMsg['website_id'] = formatErrorMsg($this->spTextSA['projectalreadyexist']);
    			}
		    }
		}
		$this->set('errMsg', $errMsg);
		$this->newProject($listInfo);
	}
	
	// check entered excluded list
	function checkExcludeLinks($excludeLinks, $websiteUrl, $exclude=true) {
	    $linkList = explode(',', $excludeLinks);
	    $newList = array();
	    $errMsg = "";
	    $checkUrl = formatUrl($websiteUrl);
	    foreach ($linkList as $link) {
	        $link = str_replace(' ', '+', trim($link));
	        $linkUrl = formatUrl($link);
	        if (!empty($linkUrl)) {
	            $newList[] = $link;
	            if (empty($errMsg)) {
	                if (!preg_match("/^".preg_quote($checkUrl, '/')."/", $linkUrl)) {
	                    $linkLabel = $exclude ? $_SESSION['text']['label']["Exclude"] : $_SESSION['text']['label']["Include"];
	                    $errMsg = "$linkLabel link <b>'$link'</b> {$this->spTextSA['should start with']} <b>'$websiteUrl'</b>";
	                }
	            }
	        }
	    }
	    $excludeLinks = implode(',', $newList);
	    $retInfo['exclude_links'] = $excludeLinks;
	    $retInfo['err_msg'] = $errMsg;
	    return $retInfo;
	}

	// function check project already exists
	function isProjectExists($websiteId, $projectId=false){		
		$sql = "select id from auditorprojects where website_id=$websiteId";
		$sql .= $projectId ? " and id!=$projectId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	// func to get project info
	function __getProjectInfo($projectId) {
		$sql = "select p.*,w.url,w.name from auditorprojects p,websites w where  p.website_id=w.id and p.id=$projectId";
		$info = $this->db->select($sql, true);
		$info['url'] = @Spider::removeTrailingSlash($info['url']);
		return $info;
	}
	
	// func to edit project
	function editProject($projectId, $listInfo=''){		
	    $userId = isLoggedIn();
	    $projectId = intval($projectId);
		if(!empty($projectId)){			
			if(empty($listInfo)){
				$listInfo = $this->__getProjectInfo($projectId);
				$listInfo['oldName'] = $listInfo['keyword'];
			}
			$this->set('post', $listInfo);
			
			$websiteController = New WebsiteController();
    		$websiteList = $websiteController->__getAllWebsites($userId, true);
    		$this->set('websiteList', $websiteList);		
    		$websiteId = empty($listInfo['website_id']) ? $websiteList[0]['id'] : intval($listInfo['website_id']);
    		$this->set('websiteId', $websiteId);
    		
    		$langController = New LanguageController();
    		$this->set('langList', $langController->__getAllLanguages());
			$this->render('siteauditor/edit');
			exit;
		}		
	}
	
	// func to update project
	function updateProject($listInfo){
		$listInfo['id'] = intval($listInfo['id']);
		$userId = isLoggedIn();
		$listInfo['website_id'] = intval($listInfo['website_id']);
		$listInfo['max_links'] = intval($listInfo['max_links']);		
		$this->set('post', $listInfo);
		$errMsg['website_id'] = formatErrorMsg($this->validate->checkBlank($listInfo['website_id']));
		$errMsg['max_links'] = formatErrorMsg($this->validate->checkNumber($listInfo['max_links']));
		if(!$this->validate->flagErr){
		    $errorFlag = 0;		    
		    if ($listInfo['max_links'] > SA_MAX_NO_PAGES) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = formatErrorMsg($this->spTextSA["Number of pages is greater than"]. " ". SA_MAX_NO_PAGES);
		    }
		    
		    if ($listInfo['max_links'] <= 0) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = formatErrorMsg($this->spTextSA["Number of pages should be greater than"]. " 0");
		    }
		    
		    $webCtrler = New WebsiteController();
		    $websiteInfo = $webCtrler->__getWebsiteInfo($listInfo['website_id']);
		    
		    // commented to exclude site auditor with query arguments also by with out adding fulll url
		    /*$excludeInfo = $this->checkExcludeLinks($listInfo['exclude_links'], $websiteInfo['url']);
		    if (!empty($excludeInfo['err_msg'])) {
		        $errorFlag = 1;
		        $errMsg['exclude_links'] = formatErrorMsg($excludeInfo['err_msg']);		        
		    }
		    $listInfo['exclude_links'] = $excludeInfo['exclude_links'];*/
		    
		    // check for max links allowed for the account type
		    $maxValidInfo = $this->validateMaxLinkCount($websiteInfo['user_id'], $listInfo['max_links']);
		    if ($maxValidInfo['error']) {
		        $errorFlag = 1;
		        $errMsg['max_links'] = $maxValidInfo['msg'];
		    }

		    // Validate sitemap URL if provided
		    if (!empty($listInfo['sitemap_url'])) {
		        $sitemapUrl = trim($listInfo['sitemap_url']);
		        if (!filter_var($sitemapUrl, FILTER_VALIDATE_URL)) {
		            $errorFlag = 1;
		            $errMsg['sitemap_url'] = formatErrorMsg("Invalid sitemap URL format");
		        }
		    }
		    
		    // if error occured
		    if (!$errorFlag) {
    			if (!$this->isProjectExists($listInfo['website_id'], $listInfo['id'])) {
    				$sql = "Update auditorprojects set
    						website_id={$listInfo['website_id']},
    						max_links={$listInfo['max_links']},
    						check_pr=".intval($listInfo['check_pr']).",
    						check_backlinks=".intval($listInfo['check_backlinks']).",
    						check_indexed=".intval($listInfo['check_indexed']).",
    						store_links_in_page=".intval($listInfo['store_links_in_page']).",
    						check_brocken='".intval($listInfo['check_brocken'])."',
    						cron='".intval($listInfo['cron'])."',
    						exclude_links='".addslashes($listInfo['exclude_links'])."',
    						exclude_extensions='".addslashes($listInfo['exclude_extensions'])."',
    						sitemap_url='".addslashes(trim($listInfo['sitemap_url']))."'
    						where id=".$listInfo['id'];
    				$this->db->query($sql);
    				$this->showAuditorProjects();
    				exit;
    			}else{			
    				$errMsg['website_id'] = formatErrorMsg($this->spTextSA['projectalreadyexist']);
    			}
		    }
		}
		$this->set('errMsg', $errMsg);
		$this->editProject($listInfo['id'], $listInfo);
	}

	// func to change status
	function __changeStatus($projectId, $status){		
		$projectId = intval($projectId);
		$sql = "update auditorprojects set status=$status where id=$projectId";
		$this->db->query($sql);
	}

	// func to delete project
	function __deleteProject($projectId){
	    // delete teh project
		$projectId = intval($projectId);
		$sql = "delete from auditorprojects where id=$projectId";
		$this->db->query($sql);
		
		// delete all pages found in reports
		$sql = "select id from auditorreports where project_id=$projectId";
		$repList = $this->db->select($sql);		
		foreach ($repList as $repInfo) {
		    $this->__deleteReportPage($repInfo['id']);
		}
	}

	// function to get number of links of a project
	function getCountcrawledLinks($projectId, $statusCheck=false, $statusVal=1, $conditions='') {
		$sql = "select count(*) count from auditorreports r where r.project_id=$projectId";
		$sql .= $statusCheck ? " and crawled=$statusVal" : "";
		$sql .= $conditions;
		$info = $this->db->select($sql, true);
		return $info['count'] ? $info['count'] : 0;
	}
	
    // function to get all projects of user
	function getAllProjects($where='') {
		$sql = "select ap.*,w.url,w.name from auditorprojects ap,websites w where w.id=ap.website_id and w.status=1 and ap.status=1 $where";
		$projectList = $this->db->select($sql);
		return $projectList;
	}

	// function to get number of links of a project
	function getProjectLastUpdate($projectId) {
		$sql = "select max(updated) updated from auditorreports r where r.project_id=$projectId";
		$info = $this->db->select($sql, true);
		return empty($info['updated']) ? "Not Started" : $info['updated'];
	}

	// function to show interface to run a project
	function showRunProject($projectId) {
	    $projectId = intval($projectId);
	    $projectInfo = $this->__getProjectInfo($projectId);
	    $projectInfo['total_links'] = $this->getCountcrawledLinks($projectInfo['id']);
	    $projectInfo['crawled_links'] = $this->getCountcrawledLinks($projectInfo['id'], true);
		$projectInfo['last_updated'] = $this->getProjectLastUpdate($projectInfo['id']);
		$projectInfo['crawling_url'] =  $this->getProjectRandomUrl($projectId); 
	    $this->set('projectInfo', $projectInfo);
	    $this->render('siteauditor/showrunproject');
	}
	
	// fucntion to load reports page after teh actions
	function loadReportsPage($info=[]) {
	    print "<script>scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&pageno={$info['pageno']}&order_col={$info['order_col']}&order_val={$info['order_val']}')</script>";
	}
	
	// function to check page score
	function checkPageScore($info=[]) {
	    if (!empty($info['report_id'])) {
	        $reportId = intval($info['report_id']);
	        $auditorComp = $this->createComponent('AuditorComponent');
	        $reportInfo = $auditorComp->getReportInfo(" and id=$reportId");
	        $projectInfo = $this->__getProjectInfo($reportInfo['project_id']);
	        $auditorComp->runReport($reportInfo['page_url'], $projectInfo, $this->getCountcrawledLinks($projectInfo['id']));
	        $this->loadReportsPage($info);
	    }    
	}

	// func to delete page of report
	function __deleteReportPage($reportId){
	    if (!empty($reportId)) {
	        $reportId = intval($reportId);
	        
	        // delete report page
	        $sql = "delete from auditorreports where id=$reportId";
	        $this->db->query($sql);
	        
	        // delete all links under this page
	        $sql = "delete from auditorpagelinks where report_id=$reportId";
	        $this->db->query($sql);
	    }
	}
	
	// function to recheck report pages of project
	function recheckReportPages($projectId) {
	    $projectId = intval($projectId);
	    if (!empty($projectId)) {
	        $sql = "update auditorreports set crawled=0 where project_id=$projectId";
	        $this->db->query($sql);
	        
	        
    	    // delete all pages found in reports
    		$sql = "select id from auditorreports where project_id=$projectId";
    		$repList = $this->db->select($sql);		
    		foreach ($repList as $repInfo) {
    		    // delete all links under this page
	            $sql = "delete from auditorpagelinks where report_id=".$repInfo['id'];
	            $this->db->query($sql);    
    		}
	        
	        
	    }    
	}
	
	// function to run project, save blog links to database
	function runProject($projectId) {
	    $projectId = intval($projectId);
        $projectInfo = $this->__getProjectInfo($projectId);
	    $completed = 0;
	    $errorMsg = '';	    
	    if ($reportUrl = $this->getProjectRandomUrl($projectId)) {
	        $auditorComp = $this->createComponent('AuditorComponent');
	        $crawledUrl = $auditorComp->runReport($reportUrl, $projectInfo, $this->getCountcrawledLinks($projectId));
	        $this->set('crawledUrl', $crawledUrl);	        
	        if (!$crawlUrl = $this->getProjectRandomUrl($projectId)) {
	            $completed = 1;
	        } else {
	            if (!$this->cron) updateJsLocation('crawling_url', $crawledUrl);
	        }	        
	    } else {
	        $completed = 1;
	    }
	    
	    // if execution not through cron
	    if (!$this->cron) {
    	    updateJsLocation('last_updated', date('Y-m-d H:i:s'));
    	    updateJsLocation('total_links', $this->getCountcrawledLinks($projectId));
    	    updateJsLocation('crawled_pages', $this->getCountcrawledLinks($projectId, true));	    
    	    $this->set('completed', $completed);	    
    	    $this->set('projectId', $projectId);
    	    $this->set('errorMsg', $errorMsg);
    	    $this->set('projectInfo', $projectInfo);
    	    $this->render('siteauditor/runproject');
	    } else {
	        return $crawledUrl;
	    }
	}
    
	// function to get random url of a project
	function getProjectRandomUrl($projectId) {	    
	    $sql = "SELECT page_url FROM auditorreports where project_id=$projectId and crawled=0 ORDER BY RAND() LIMIT 1";
		$listInfo = $this->db->select($sql, true);
		if (empty($listInfo['page_url'])) {
		    $totalLinks = $this->getCountcrawledLinks($projectId);
		    if ($totalLinks == 0) {
		        // First run - discover and parse sitemaps
		        $auditorComp = $this->createComponent('AuditorComponent');
		        $projectInfo = $this->__getProjectInfo($projectId);

		        // Add initial project URL
		        $reportInfo['page_url'] = Spider::formatUrl($projectInfo['url']);
		        $reportInfo['project_id'] = $projectId;
		        $reportInfo['discovered_via'] = 'crawl';
		        $auditorComp->saveReportInfo($reportInfo);

		        // Discover and parse sitemaps
		        $this->discoverAndParseSitemaps($projectInfo);

		        return $reportInfo['page_url'];
		    } else {
		        return false;
		    }
		} else {
		    return $listInfo['page_url'];
		}
	}
	
	// function to view the reports
	function viewReports($info=[]) {	    
	    $userId = isLoggedIn();
	    $where = isAdmin() ? "" : " and w.user_id=$userId";
	    $pList = $this->getAllProjects($where);
	    $projectList = array();
	    foreach($pList as $pInfo) {
		    $pInfo['total_links'] = $this->getCountcrawledLinks($pInfo['id']);
		    if ($pInfo['total_links'] > 0) {
	            $projectList[] = $pInfo;
		    }
	    }	    
	    
	    if (empty($info['project_id'])) {
            $projectId = $projectList[0]['id']; 
	    } else {
	        $projectId = intval($info['project_id']);
	    }
	    
	    $this->set('projectId', $projectId);
	    $this->set('projectList', $projectList);
	    $reportTypes = array(
			'rp_summary' => $this->spTextSA["Report Summary"],
			'rp_links' => $this->spTextSA["Link Reports"],
			'page_title' => $this->spTextSA["Duplicate Title"],
			'page_description' => $this->spTextSA["Duplicate Description"],
			'page_keywords' => $this->spTextSA["Duplicate Keywords"],
		);

		$this->set('reportTypes', $reportTypes);
		$this->set('repType', empty($info['report_type']) ? "rp_summary" : $info['report_type']);	    
	    $this->render('siteauditor/viewreports');
	}
	
	//function to show the reports by using view reportss filters
	function showProjectReport($data='') {	    
	    $data['project_id'] = intval($data['project_id']);
	    $projectInfo = $this->__getProjectInfo($data['project_id']);
	    $projectInfo['last_updated'] = $this->getProjectLastUpdate($data['project_id']);
	    $this->set('projectId', $data['project_id']);
		$this->set('projectInfo', $projectInfo);
	    		
		$exportVersion = false;
		switch($data['doc_type']){
						
			case "export":
				$exportVersion = true;
				break;
			
			case "pdf":
				$this->set('pdfVersion', true);
				break;
			
			case "print":
				$this->set('printVersion', true);
				break;
		}
		
		switch($data['report_type']) {
			
			case "rp_summary":
				$this->showReportSummary($data, $exportVersion, $projectInfo);
				break;
			
			case "page_title":
			case "page_description":
			case "page_keywords":
				$this->showDuplicateMetaInfo($data, $exportVersion, $projectInfo);
				break;

			case "rp_links":
			default:
				$this->showLinksReport($data, $exportVersion, $projectInfo);
				break;
			
		}
	}
	
	// function to show links reports of a auditor project
    function showLinksReport($data, $exportVersion, $projectInfo) {
		
		$projectId = intval($data['project_id']);		
		$sql = "select * from auditorreports where project_id=$projectId";
		$filter = "";
		
		// check for page rank
		if(isset($data['pagerank']) && ($data['pagerank'] != -1)) {
			$prMax = intval($data['pagerank']) + 0.5;
			$prMin = intval($data['pagerank']) - 0.5;
			$sql .= " and pagerank<$prMax and pagerank>=$prMin";
			$filter .= "&pagerank=".$data['pagerank'];
		}	
		
		// check for page url
		if(!empty($data['page_url'])) {
			$pageLink = urldecode($data['page_url']);
			$filter .= "&page_url=".urlencode($pageLink);
			$sql .= " and page_url like '%".addslashes($pageLink)."%'"; 
		}
		
        // check for page url
		if(isset($data['crawled']) && ($data['crawled'] != -1) ) {
		    $data['crawled'] = intval($data['crawled']);
			$filter .= "&crawled=".$data['crawled'];
			$sql .= " and crawled=".$data['crawled'];
		}

		// check for blocked_by_robots
		if(isset($data['blocked_by_robots']) && ($data['blocked_by_robots'] != -1) ) {
		    $data['blocked_by_robots'] = intval($data['blocked_by_robots']);
			$filter .= "&blocked_by_robots=".$data['blocked_by_robots'];
			$sql .= " and blocked_by_robots=".$data['blocked_by_robots'];
		}

		// check for ai_robot_allowed
		if(isset($data['ai_robot_allowed']) && ($data['ai_robot_allowed'] != -1) ) {
		    $data['ai_robot_allowed'] = intval($data['ai_robot_allowed']);
			$filter .= "&ai_robot_allowed=".$data['ai_robot_allowed'];
			$sql .= " and ai_robot_allowed=".$data['ai_robot_allowed'];
		}

		// check for mobile_friendly
		if(isset($data['mobile_friendly']) && ($data['mobile_friendly'] != -1) ) {
		    $data['mobile_friendly'] = intval($data['mobile_friendly']);
			$filter .= "&mobile_friendly=".$data['mobile_friendly'];
			$sql .= " and mobile_friendly=".$data['mobile_friendly'];
		}

		// check for https_secure
		if(isset($data['https_secure']) && ($data['https_secure'] != -1) ) {
		    $data['https_secure'] = intval($data['https_secure']);
			$filter .= "&https_secure=".$data['https_secure'];
			$sql .= " and https_secure=".$data['https_secure'];
		}

		// check for has_og_tags
		if(isset($data['has_og_tags']) && ($data['has_og_tags'] != -1) ) {
		    $data['has_og_tags'] = intval($data['has_og_tags']);
			$filter .= "&has_og_tags=".$data['has_og_tags'];
			$sql .= " and has_og_tags=".$data['has_og_tags'];
		}

		// check for has_twitter_cards
		if(isset($data['has_twitter_cards']) && ($data['has_twitter_cards'] != -1) ) {
		    $data['has_twitter_cards'] = intval($data['has_twitter_cards']);
			$filter .= "&has_twitter_cards=".$data['has_twitter_cards'];
			$sql .= " and has_twitter_cards=".$data['has_twitter_cards'];
		}

		// check for discovered_via
		if(!empty($data['discovered_via'])) {
			$discoveredVia = addslashes($data['discovered_via']);
			$filter .= "&discovered_via=".$data['discovered_via'];
			$sql .= " and discovered_via='$discoveredVia'";
		}

		// check for google_indexed
		if(isset($data['google_indexed']) && ($data['google_indexed'] != -1) ) {
		    $data['google_indexed'] = intval($data['google_indexed']);
			$filter .= "&google_indexed=".$data['google_indexed'];
			$sql .= " and google_indexed=".$data['google_indexed'];
		}

		// check for bing_indexed
		if(isset($data['bing_indexed']) && ($data['bing_indexed'] != -1) ) {
		    $data['bing_indexed'] = intval($data['bing_indexed']);
			$filter .= "&bing_indexed=".$data['bing_indexed'];
			$sql .= " and bing_indexed=".$data['bing_indexed'];
		}

		// check for brocken
		if(isset($data['brocken']) && ($data['brocken'] != -1) ) {
		    $data['brocken'] = intval($data['brocken']);
			$filter .= "&brocken=".$data['brocken'];
			$sql .= " and brocken=".$data['brocken'];
		}

		// check for no backlinks
		if(isset($data['no_backlinks']) && ($data['no_backlinks'] != -1) ) {
		    $data['no_backlinks'] = intval($data['no_backlinks']);
			$filter .= "&no_backlinks=".$data['no_backlinks'];
			$sql .= " and google_backlinks=0";
		}

		// check for has backlinks
		if(isset($data['has_backlinks']) && ($data['has_backlinks'] != -1) ) {
		    $data['has_backlinks'] = intval($data['has_backlinks']);
			$filter .= "&has_backlinks=".$data['has_backlinks'];
			$sql .= " and google_backlinks>0";
		}

		// check for backlinks filter (from dropdown)
		if(!empty($data['backlinks_filter'])) {
			$blFilter = addslashes($data['backlinks_filter']);
			$filter .= "&backlinks_filter=$blFilter";
			if($blFilter == 'has') {
				$sql .= " and google_backlinks > 0";
			} else if($blFilter == 'no') {
				$sql .= " and google_backlinks = 0";
			}
		}

		// check for indexed filter (from dropdown)
		if(!empty($data['indexed_filter'])) {
			$idxFilter = addslashes($data['indexed_filter']);
			$filter .= "&indexed_filter=$idxFilter";
			switch($idxFilter) {
				case 'google_yes':
					$sql .= " and google_indexed > 0";
					break;
				case 'google_no':
					$sql .= " and google_indexed = 0";
					break;
				case 'bing_yes':
					$sql .= " and bing_indexed > 0";
					break;
				case 'bing_no':
					$sql .= " and bing_indexed = 0";
					break;
			}
		}

		// Page Authority filters
		$paLevelFirst = defined('SA_PA_CHECK_LEVEL_FIRST') ? SA_PA_CHECK_LEVEL_FIRST : 40;
		$paLevelSecond = defined('SA_PA_CHECK_LEVEL_SECOND') ? SA_PA_CHECK_LEVEL_SECOND : 75;

		// check for PA type filter (from dropdown)
		if(!empty($data['pa_type'])) {
			$paType = addslashes($data['pa_type']);
			$filter .= "&pa_type=$paType";
			switch($paType) {
				case 'excellent':
					$sql .= " and page_authority >= $paLevelSecond";
					break;
				case 'good':
					$sql .= " and page_authority >= $paLevelFirst and page_authority < $paLevelSecond";
					break;
				case 'low':
					$sql .= " and page_authority > 0 and page_authority < $paLevelFirst";
					break;
				case 'none':
					$sql .= " and page_authority = 0";
					break;
			}
		}

		// check for excellent PA (>=75) - for card links
		if(isset($data['pa_excellent']) && ($data['pa_excellent'] != -1) ) {
			$filter .= "&pa_excellent=1";
			$sql .= " and page_authority >= $paLevelSecond";
		}

		// check for good PA (>=40 and <75) - for card links
		if(isset($data['pa_good']) && ($data['pa_good'] != -1) ) {
			$filter .= "&pa_good=1";
			$sql .= " and page_authority >= $paLevelFirst and page_authority < $paLevelSecond";
		}

		// check for low PA (>0 and <40) - for card links
		if(isset($data['pa_low']) && ($data['pa_low'] != -1) ) {
			$filter .= "&pa_low=1";
			$sql .= " and page_authority > 0 and page_authority < $paLevelFirst";
		}

		// check for no PA (=0) - for card links
		if(isset($data['pa_none']) && ($data['pa_none'] != -1) ) {
			$filter .= "&pa_none=1";
			$sql .= " and page_authority = 0";
		}

		// check for mobile friendly
		if(isset($data['mobile_friendly']) && ($data['mobile_friendly'] != -1) ) {
		    $data['mobile_friendly'] = intval($data['mobile_friendly']);
			$filter .= "&mobile_friendly=".$data['mobile_friendly'];
			$sql .= " and mobile_friendly=".$data['mobile_friendly'];
		}

		// check for https secure
		if(isset($data['https_secure']) && ($data['https_secure'] != -1) ) {
		    $data['https_secure'] = intval($data['https_secure']);
			$filter .= "&https_secure=".$data['https_secure'];
			$sql .= " and https_secure=".$data['https_secure'];
		}

		// check for ai robot allowed
		if(isset($data['ai_robot_allowed']) && ($data['ai_robot_allowed'] != -1) ) {
		    $data['ai_robot_allowed'] = intval($data['ai_robot_allowed']);
			$filter .= "&ai_robot_allowed=".$data['ai_robot_allowed'];
			$sql .= " and ai_robot_allowed=".$data['ai_robot_allowed'];
		}

		// check for open graph tags
		if(isset($data['has_og_tags']) && ($data['has_og_tags'] != -1) ) {
		    $data['has_og_tags'] = intval($data['has_og_tags']);
			$filter .= "&has_og_tags=".$data['has_og_tags'];
			$sql .= " and has_og_tags=".$data['has_og_tags'];
		}

		// check for twitter cards
		if(isset($data['has_twitter_cards']) && ($data['has_twitter_cards'] != -1) ) {
		    $data['has_twitter_cards'] = intval($data['has_twitter_cards']);
			$filter .= "&has_twitter_cards=".$data['has_twitter_cards'];
			$sql .= " and has_twitter_cards=".$data['has_twitter_cards'];
		}

		// check for robots.txt allowed
		if(isset($data['allowed_by_robots']) && ($data['allowed_by_robots'] != -1) ) {
		    $data['allowed_by_robots'] = intval($data['allowed_by_robots']);
			$filter .= "&allowed_by_robots=".$data['allowed_by_robots'];
			$sql .= " and allowed_by_robots=".$data['allowed_by_robots'];
		}

		// to find order col
		$validLinkOrderCols = array('page_url', 'page_authority', 'score', 'brocken', 'external_links', 'total_links', 'google_backlinks', 'indexed', 'crawled', 'page_title', 'page_description', 'page_keywords', 'comments');
        if (!empty($data['order_col']) && in_array($data['order_col'], $validLinkOrderCols)) {
		    $orderCol = $data['order_col'];
		    $orderVal = (strtoupper($data['order_val']) === 'ASC') ? 'ASC' : 'DESC';
		} else {
		    $orderCol = 'score';
		    $orderVal = 'DESC';
		}
		$filter .= "&order_col=$orderCol&order_val=$orderVal";
		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);

		$pgScriptPath = "siteauditor.php?sec=showreport&report_type=rp_links&project_id=$projectId".$filter;
		$this->set('filter', $filter);

		// pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pgScriptPath, '', 'scriptDoLoad', 'subcontent', 'layout=ajax');
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " order by $orderCol $orderVal";		
		
		$sql .= in_array($data['doc_type'], array('pdf', 'print', 'export')) ? "" : " limit ".$this->paging->start .",". $this->paging->per_page; 
		
		$reportList = $this->db->select($sql);
		$spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
		$headArr =  array(
        	'page_url' => $this->spTextSA["Page Link"],
        	'page_authority' => $_SESSION['text']['common']['Page Authority'],
        	'score' => $_SESSION['text']['label']["Score"],
        	'brocken' => $_SESSION['text']['label']["Brocken"],
            'external_links' => $this->spTextSA["External Links"],
        	'total_links' => $this->spTextSA["Total Links"],
            'google_backlinks' => $spTextHome['Backlinks'],
            'indexed' => $spTextHome['Indexed'],
		    'crawled' => $this->spTextSA['Crawled'],
		    'brocken' => $_SESSION['text']['label']['Brocken'],
		    'page_title' => $_SESSION['text']['label']['Title'],
		    'page_description' => $_SESSION['text']['label']['Description'],
		    'page_keywords' => $_SESSION['text']['label']['Keywords'],
		    'comments' => $_SESSION['text']['label']['Comments'],
        );
		
		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$exportContent = createExportContent(array('', $this->spTextSA["Link Reports"], ''));
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array($this->spTextSA['Project Url'], $projectInfo['url']));
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Updated'], $projectInfo['last_updated']));
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Total Results'], $this->db->noRows));
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array(
				$spText['common']['No'],
				$headArr['page_url'],
				$headArr['page_authority'],
				$headArr['google_backlinks'],
				$headArr['indexed'],
				'Robots Allowed',
				'AI Bot Allowed',
				'Mobile Friendly',
				'HTTPS Secure',
				'OG Tags',
				'Twitter Cards',
				$headArr['external_links'],
				$headArr['total_links'],
				$headArr['score'],
				$headArr['brocken'],
				$headArr['crawled'],
				$headArr['page_title'],
				$headArr['page_description'],
				$headArr['page_keywords'],
				$headArr['comments']
			));
			$auditorComp = $this->createComponent('AuditorComponent');
			foreach($reportList as $i => $listInfo) {
			    if ($listInfo['crawled']) {
			        $auditorComp->countReportPageScore($listInfo);
			        $comments = strip_tags(implode("\n", $auditorComp->commentInfo));
			    } else {
			        $comments = "";
			    }
			    $listInfo['crawled'] = $listInfo['crawled'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['brocken'] = $listInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['robots_allowed'] = !$listInfo['blocked_by_robots'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['google_indexed'] = $listInfo['google_indexed'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['ai_robot_allowed'] = $listInfo['ai_robot_allowed'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['mobile_friendly'] = $listInfo['mobile_friendly'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['https_secure'] = $listInfo['https_secure'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['has_og_tags'] = $listInfo['has_og_tags'] ? $spText['common']['Yes'] : $spText['common']['No'];
			    $listInfo['has_twitter_cards'] = $listInfo['has_twitter_cards'] ? $spText['common']['Yes'] : $spText['common']['No'];
				$exportContent .= createExportContent(array(
					$i+1,
					$listInfo['page_url'],
					$listInfo['page_authority'],
					$listInfo['google_backlinks'],
					$listInfo['google_indexed'],
					$listInfo['robots_allowed'],
					$listInfo['ai_robot_allowed'],
					$listInfo['mobile_friendly'],
					$listInfo['https_secure'],
					$listInfo['has_og_tags'],
					$listInfo['has_twitter_cards'],
					$listInfo['external_links'],
					$listInfo['total_links'],
					$listInfo['score'],
					$listInfo['brocken'],
					$listInfo['crawled'],
					$listInfo['page_title'],
					$listInfo['page_description'],
					$listInfo['page_keywords'],
					$comments
				));
			}
			exportToCsv('siteauditor_report', $exportContent);
		} else {					
			$this->set('totalResults', $this->db->noRows);					
			$this->set('list', $reportList);
			$this->set('pageNo', $_GET['pageno']);
			$this->set('data', $data);
    	    $this->set('headArr', $headArr);

    	    // if pdf export
    	    if ($data['doc_type'] == "pdf") {
    	    	exportToPdf($this->getViewContent('siteauditor/reportlinks'), "site_auditor_report_links.pdf");
    	    } else {
				$this->render('siteauditor/reportlinks');
    	    }
		}
		
	}
	
	# function show the details of a page
	function viewPageDetails($info=[]) {
	    $reportId = intval($info['report_id']);
	    if (!empty($reportId)) {
	        $auditorComp = $this->createComponent('AuditorComponent');
	        $reportInfo = $auditorComp->getReportInfo(" and id=$reportId");
	        $reportInfo['score'] = array_sum($auditorComp->countReportPageScore($reportInfo));
	        $reportInfo['comments'] = $comments = implode("<br>", $auditorComp->commentInfo);
	        $this->set('spTextHome', $this->getLanguageTexts('home', $_SESSION['lang_code']));
	        $this->set('reportInfo', $reportInfo);
	        $this->set('linkList', $auditorComp->getAllLinksPage($reportId));
	        $this->set('post', $info);
	        $this->render('siteauditor/pagedetails');
	    }    
	}
	
	// function to show reports summary of a project
    function showReportSummary($data, $exportVersion, $projectInfo) {        
        
	    $projectInfo['total_links'] = $this->getCountcrawledLinks($projectInfo['id']);
	    $projectInfo['crawled_links'] = $this->getCountcrawledLinks($projectInfo['id'], true);
	    $mainLink = SP_WEBPATH."/siteauditor.php?project_id=".$projectInfo['id']."&sec=showreport&report_type=rp_summary";
	    
	    // check for page url
        $statusCheck = false;
        $statusVal = 0;
		if(isset($data['crawled']) && ($data['crawled'] != -1) ) {
		    $statusCheck = true;
		    $statusVal = intval($data['crawled']);
		    $mainLink .= "&crawled=$statusVal";
		}
	    
		// check for brocken
		$conditions = " and brocken=1";
	    $projectInfo['brocken'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

		// check for backlinks
	    foreach ($this->seArr as $se) {
		    $conditions = " and $se"."_backlinks>0";
		    $projectInfo[$se."_backlinks"] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);	        
	    }

	    // check for no backlinks
	    $conditions = " and google_backlinks=0";
	    $projectInfo['no_backlinks'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);
	    
        // check for indexed
	    foreach ($this->seArr as $se) {
		    $conditions = " and $se"."_indexed>0";
		    $projectInfo[$se."_indexed"] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

		    // check for NOT indexed
		    $conditions = " and $se"."_indexed=0";
		    $projectInfo[$se."_not_indexed"] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);
	    }
	    
	    // duplicate titles,descriptions and keywords
	    $metaArr = array('page_title' => $this->spTextSA["Duplicate Title"], 'page_description' => $this->spTextSA['Duplicate Description'], 'page_keywords' => $this->spTextSA['Duplicate Keywords']);
	    foreach ($metaArr as $meta => $val) {
	        $auditorComp = $this->createComponent('AuditorComponent');
	        $projectInfo["duplicate_".$meta] = $auditorComp->getDuplicateMetaInfoCount($projectInfo['id'], $meta, $statusCheck, $statusVal);
	    }

	    // Modern SEO features - Mobile, HTTPS, AI Robot, Social Media
	    $conditions = " and mobile_friendly=1";
	    $projectInfo['mobile_friendly'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and https_secure=1";
	    $projectInfo['https_secure'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and ai_robot_allowed=1";
	    $projectInfo['ai_robot_allowed'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and has_og_tags=1";
	    $projectInfo['has_og_tags'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and has_twitter_cards=1";
	    $projectInfo['has_twitter_cards'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and blocked_by_robots=0";
	    $projectInfo['allowed_by_robots'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    // Page Authority metrics based on thresholds
	    $paLevelFirst = defined('SA_PA_CHECK_LEVEL_FIRST') ? SA_PA_CHECK_LEVEL_FIRST : 40;
	    $paLevelSecond = defined('SA_PA_CHECK_LEVEL_SECOND') ? SA_PA_CHECK_LEVEL_SECOND : 75;

	    $conditions = " and page_authority >= $paLevelSecond";
	    $projectInfo['pa_excellent'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and page_authority >= $paLevelFirst and page_authority < $paLevelSecond";
	    $projectInfo['pa_good'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and page_authority > 0 and page_authority < $paLevelFirst";
	    $projectInfo['pa_low'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $conditions = " and page_authority = 0";
	    $projectInfo['pa_none'] = $this->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

	    $spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
	    $this->set('spTextHome', $spTextHome);	    
	    
	    if ($exportVersion) {
			$exportContent = createExportContent(array('', $this->spTextSA['Project Summary'], ''));
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array($this->spTextSA['Project Url'], $projectInfo['url']));
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Updated'], $projectInfo['last_updated']));			
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Score'], $projectInfo['score']));		
			$exportContent .= createExportContent(array($this->spTextSA['Maximum Pages'], $projectInfo['max_links']));		
			$exportContent .= createExportContent(array($this->spTextSA['Pages Found'], $projectInfo['total_links']));		
			$exportContent .= createExportContent(array($this->spTextSA['Crawled Pages'], $projectInfo['crawled_links']));

			$exportContent .= createExportContent(array('Has Backlinks', $projectInfo['google_backlinks']));

			$exportContent .= createExportContent(array($spTextHome['Indexed'], $projectInfo['google_indexed']));
			$exportContent .= createExportContent(array('Not Indexed', $projectInfo['google_not_indexed']));
			
			foreach ($metaArr as $meta => $val) {
			    $exportContent .= createExportContent(array($val, $projectInfo["duplicate_".$meta]));
	        }

    		$exportContent .= createExportContent(array($_SESSION['text']['label']['Brocken'], $projectInfo['brocken']));
    		$exportContent .= createExportContent(array('No Backlinks', $projectInfo['no_backlinks']));

			// Export modern SEO features
			$exportContent .= createExportContent(array($this->spTextSA['Mobile Friendly'], $projectInfo['mobile_friendly']));
			$exportContent .= createExportContent(array($this->spTextSA['HTTPS Secure'], $projectInfo['https_secure']));
			$exportContent .= createExportContent(array($this->spTextSA['AI Robot Compatibility'], $projectInfo['ai_robot_allowed']));
			$exportContent .= createExportContent(array($this->spTextSA['Open Graph Tags'], $projectInfo['has_og_tags']));
			$exportContent .= createExportContent(array($this->spTextSA['Twitter Cards'], $projectInfo['has_twitter_cards']));
			$exportContent .= createExportContent(array($this->spTextSA['Robots.txt Allowed'], $projectInfo['allowed_by_robots']));

			// Export Page Authority metrics
			$exportContent .= createExportContent(array('Excellent Page Authority (>=75)', $projectInfo['pa_excellent']));
			$exportContent .= createExportContent(array('Good Page Authority (40-74)', $projectInfo['pa_good']));
			$exportContent .= createExportContent(array('Low Page Authority (1-39)', $projectInfo['pa_low']));
			$exportContent .= createExportContent(array('No Page Authority (0)', $projectInfo['pa_none']));

			exportToCsv('siteauditor_report_summary', $exportContent);
		} else {
    		$this->set('projectInfo', $projectInfo);
    		$this->set('metaArr', $metaArr);
    		$this->set('seArr', $this->seArr);
    		$this->set('mainLink', $mainLink);            
            
            // if pdf export
            if ($data['doc_type'] == "pdf") {
            	exportToPdf($this->getViewContent('siteauditor/projectreportsummary'), "site_auditor_report_summary.pdf");
            } else {
            	$this->render('siteauditor/projectreportsummary');
            }
		}
    }
    
    // function to show reports summary of a project
    function showDuplicateMetaInfo($data, $exportVersion, $projectInfo) {
        $repType = addslashes($data['report_type']);
        $projectId = $projectInfo['id'];
        $sql = "select $repType,count(*) as count from auditorreports where project_id=$projectId and $repType!=''";
        $filter = "";
        
        // check for page url
		if(isset($data['crawled']) && ($data['crawled'] != -1) ) {
		    $data['crawled'] = intval($data['crawled']);
			$filter .= "&crawled=".$data['crawled'];
			$sql .= " and crawled=".$data['crawled']; 
		}
	    
	    // to find order col
		$validDupMetaOrderCols = array('page_title', 'page_description', 'page_keywords', 'count');
        if (!empty($data['order_col']) && in_array($data['order_col'], $validDupMetaOrderCols)) {
		    $orderCol = $data['order_col'];
		    $orderVal = (strtoupper($data['order_val']) === 'ASC') ? 'ASC' : 'DESC';
		} else {
		    $orderCol = 'count';
		    $orderVal = 'DESC';
		}
		$filter .= "&order_col=$orderCol&order_val=$orderVal";
		$pgScriptPath = SP_WEBPATH."/siteauditor.php?sec=showreport&report_type=$repType&project_id=".$projectId.$filter;

		// pagination setup
		$sql .= " group by $repType having count>1";
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pgScriptPath, '', 'scriptDoLoad', 'subcontent', 'layout=ajax');
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " order by $orderCol $orderVal";		
		$sql .= in_array($data['doc_type'], array('pdf', 'print', 'export')) ? "" : " limit ".$this->paging->start .",". $this->paging->per_page;
		
	    $totalResults = $this->db->noRows;
	    $headArr =  array(
        	'page_title' => $this->spTextSA["Duplicate Title"],
        	'page_description' => $this->spTextSA["Duplicate Description"],
        	'page_keywords' => $this->spTextSA["Duplicate Keywords"],
        	'page_urls' => $this->spTextSA["Page Links"],
        	'count' => $_SESSION['text']['label']["Count"],
        );
	    
        $list = $this->db->select($sql);
        $dupInfo[$repType] = $list;
        $auditorComp = $this->createComponent('AuditorComponent');
        foreach($dupInfo[$repType] as $i => $listInfo) {
            $dupInfo[$repType][$i]['page_urls'] = $auditorComp->getAllreportPages(" and project_id=$projectId and $repType='".addslashes($listInfo[$repType])."'", 'id,page_url');   
        }
	    
	    if ($exportVersion) {			
			$spText = $_SESSION['text'];
			$exportContent = createExportContent(array('', $headArr[$repType]." ".$_SESSION['text']['common']['Reports'], ''));
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array($this->spTextSA['Project Url'], $projectInfo['url']));
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Updated'], $projectInfo['last_updated']));
			$exportContent .= createExportContent(array($_SESSION['text']['label']['Total Results'], $totalResults));
			$exportContent .= createExportContent(array());
			$exportContent .= createExportContent(array($spText['common']['No'], $headArr[$repType], $headArr["page_urls"], $headArr["count"]));
			foreach($dupInfo[$repType] as $i => $listInfo) {
			    $pageUrls = "";
			    foreach($listInfo['page_urls'] as $urlInfo) $pageUrls .= $urlInfo['page_url'] . "\n";   
				$exportContent .= createExportContent(array($i+1, $listInfo[$repType], $pageUrls, $listInfo['count']));
			}
			exportToCsv('siteauditor_duplicate_'.$repType, $exportContent);
		} else {
    		$this->set('orderCol', $orderCol);
    		$this->set('orderVal', $orderVal);
    		$this->set('filter', $filter);
			$this->set('pageNo', $_GET['pageno']);
    	    $this->set('totalResults', $totalResults);
    	    $this->set('list', $dupInfo[$repType]);
    	    $this->set('repType', $repType);
    	    $this->set('headArr', $headArr);
    	    
    	    // if pdf export
    	    if ($data['doc_type'] == "pdf") {
    	    	exportToPdf($this->getViewContent('siteauditor/showduplicatemetainfo'), "site_auditor_report_duplicate_meta.pdf");
    	    } else {
    	    	$this->render('siteauditor/showduplicatemetainfo');
    	    }
		}        
    }
    
    // function to show cron command
    function showCronCommand(){		
		$this->render('siteauditor/croncommand');
	}
    
    // function toexecute cron job
    function executeCron() {
        $sql = "select id from auditorprojects where cron=1 and status=1";
		$prList = $this->db->select($sql);
		$urlFound = false;
		foreach ($prList as $info) {
	        if ($this->getProjectRandomUrl($info['id']) ) {
	            $urlFound = true;
	            $reportUrl = $this->runProject($info['id']);
	            break;
	        }    
		}

		if ($urlFound) {
            echo "\n==='$reportUrl' crawled successfully!===";		    
		} else {
		    echo "\n===No projects found to execute!===";
		}
    }
    
    // function to show import links to a project
    function showImportProjectLinks($info=[]) {
        $userId = isLoggedIn();
        $where = isAdmin() ? "" : " and w.user_id=$userId";
	    $projectList = $this->getAllProjects($where);
	    $this->set('projectList', $projectList);
	    
	    if(empty($projectList)) {
	        showErrorMsg($this->spTextSA['No active projects found'].'!');
        } 
	    
	    $projectId = empty($info['project_id']) ? 0 : $info['project_id'];
	    $this->set('projectId', $projectId);
        
        $this->render('siteauditor/importlinks');
    }
    
    // function to import links
    function importLinks($listInfo) {
        $userId = isLoggedIn();
        $listInfo['project_id'] = intval($listInfo['project_id']);
		$this->set('post', $listInfo);		
		$errMsg['links'] = formatErrorMsg($this->validate->checkBlank($listInfo['links']));
				
		if (!$this->validate->flagErr) {

    		$totalLinks = $this->getCountcrawledLinks($listInfo['project_id']);
    		$projectInfo = $this->__getProjectInfo($listInfo['project_id']);
    		// if total links greater than max links of a project
    		if ($totalLinks >= $projectInfo['max_links']) {
		        $errMsg['links'] = formatErrorMsg($this->spTextSA['totallinksgreaterallowed']." - {$projectInfo['max_links']}");
    		} else {
                // check whether links are pages of website
        		$linkInfo = $this->checkExcludeLinks($listInfo['links'], $projectInfo['url'], false);
        		if (!empty($linkInfo['err_msg'])) {
        		    $errMsg['links'] = formatErrorMsg($linkInfo['err_msg']);		        
        		} else {
        		    $auditorComp = $this->createComponent('AuditorComponent');
        		    $links = explode(",", $listInfo['links']);
        			$error = false;
        			$linkList = array();
        			foreach ($links as $i => $link) {
        				$link = Spider::formatUrl(trim($link));
        				if (empty($link)) continue;
        				if ($auditorComp->isExcludeLink($link, $projectInfo['exclude_links'])) continue;
        				
        				// check whether url exists or not
        				if ($auditorComp->getReportInfo(" and project_id={$projectInfo['id']} and page_url='".addslashes($link)."'")) {
        					$errMsg['links'] = formatErrorMsg($this->spTextSA['Page Link']." '<b>$link</b>' ". $_SESSION['text']['label']['already exist']);
        					$error = true;
        					break;
        				} else {
        				    $totalLinks++;
        				    // if total links greater than max links of a project
        				    if ($totalLinks > $projectInfo['max_links']) {
        				        $error = true;
        				        $errMsg['links'] = formatErrorMsg($this->spTextSA['totallinksgreaterallowed']." - {$projectInfo['max_links']}");
        				        break;
        				    }
        				}
        				$linkList[$link] = 1;
        			}
        			
        			// to save the page if no error occurs
        			if (!$error) {        			    
        			    foreach ($linkList as $link => $val) {
            		        $reportInfo['page_url'] = $link;
            		        $reportInfo['project_id'] = $projectInfo['id'];
            		        $auditorComp->saveReportInfo($reportInfo);            		        
        			    }
        			    $this->showAuditorProjects();
            		    exit;
        			}
        		}
    		}   		
    		
		}		    
		$this->set('errMsg', $errMsg);
		$this->showImportProjectLinks();
    }
    
    // Function to check / validate the user type site auditor project maximum list 
    function validateMaxLinkCount($userId, $count) {
    	$userCtrler = new UserController();
    	$validation = array('error' => false);
    
    	// if admin user id return true
    	if ($userCtrler->isAdminUserId($userId)) {
    		return $validation;
    	}
    
    	$userTypeCtrlr = new UserTypeController();
    	$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
    
    	// if limit is set and not -1
    	if (isset($userTypeDetails['site_auditor_max_page_limit']) && $userTypeDetails['site_auditor_max_page_limit'] >= 0) {
    
    		// check whether count greater than limit
    		if ($count > $userTypeDetails['site_auditor_max_page_limit']) {
    			$spTextSubs = $userTypeCtrlr->getLanguageTexts('subscription', $_SESSION['lang_code']);
    			$validation['error'] = true;
    			$validation['msg'] = formatErrorMsg(str_replace("[limit]", $userTypeDetails['site_auditor_max_page_limit'], $spTextSubs['total_count_greater_account_limit']));
    		}
    
    	}
    
    	return $validation;
    }

    /**
     * Discover and parse sitemaps for a project
     *
     * @param array $projectInfo Project information
     * @return void
     */
    function discoverAndParseSitemaps($projectInfo) {
        $sitemapParser = $this->createComponent('SitemapParser');
        $sitemapUrls = array();

        // PRIORITY 1: Check for manual sitemap URL in project settings
        if (!empty($projectInfo['sitemap_url'])) {
            $manualSitemap = trim($projectInfo['sitemap_url']);
            $sitemapUrls[] = $manualSitemap;
        }

        // PRIORITY 2: Check robots.txt for sitemaps
        $robotsSitemaps = $sitemapParser->discoverSitemapsFromRobots($projectInfo['url']);
        $sitemapUrls = array_merge($sitemapUrls, $robotsSitemaps);

        // PRIORITY 3: Try common sitemap locations if no sitemaps found yet
        if (count($sitemapUrls) <= 1) { // Only manual sitemap or none at all
            $commonSitemaps = $sitemapParser->discoverCommonSitemaps($projectInfo['url']);
            $sitemapUrls = array_merge($sitemapUrls, $commonSitemaps);
        }

        // Parse all discovered sitemaps in priority order
        $auditorComp = $this->createComponent('AuditorComponent');
        $addedCount = 0;

        foreach ($sitemapUrls as $sitemapUrl) {
            // Save sitemap info
            $sitemapParser->saveSitemapInfo($projectInfo['id'], $sitemapUrl, 'xml');

            // Parse sitemap
            $urls = $sitemapParser->parseSitemap($sitemapUrl, $projectInfo['id']);

            // Add URLs to crawl queue
            foreach ($urls as $url) {
                $url = Spider::formatUrl($url);

                // Check if excluded
                if ($auditorComp->isExcludeLink($url, $projectInfo['exclude_links'])) {
                    continue;
                }

                // Check if already exists
                if ($auditorComp->getReportInfo(" and project_id={$projectInfo['id']} and page_url='".addslashes($url)."'")) {
                    continue;
                }

                // Check max links limit
                $totalLinks = $this->getCountcrawledLinks($projectInfo['id']);
                if ($totalLinks >= $projectInfo['max_links']) {
                    break 2; // Break out of both loops
                }

                // Add to queue
                $reportInfo = array(
                    'page_url' => $url,
                    'project_id' => $projectInfo['id'],
                    'discovered_via' => 'sitemap'
                );
                $auditorComp->saveReportInfo($reportInfo);
                $addedCount++;
            }

            // Update sitemap URL count
            $sitemapParser->updateSitemapUrlCount($projectInfo['id'], $sitemapUrl, count($urls));
        }
    }

    // Show score information page
    function showScoreInfo() {
        $this->render('siteauditor/scoreinfo');
    }

}
?>