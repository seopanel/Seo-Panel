<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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

include_once(SP_CTRLPATH."/components/review_base.ctrl.php");

# class defines all review manager controller functions
class ReviewManagerController extends ReviewBase{
    
    var $linkTable = "review_links";
    var $linkReportTable = "review_link_results";
    var $layout = "ajax";
    var $pageScriptPath = 'review.php';
    var $serviceList;
    var $colList;
    var $spTextRM;
        
    function __construct() {
        parent::__construct();
        
    	$this->set('pageScriptPath', $this->pageScriptPath);
    	$this->set( 'serviceList', $this->serviceList );
    	$this->set( 'pageNo', $_REQUEST['pageno']);
		
		$this->colList = array(
			'name' => $_SESSION['text']['common']['Name'],
			'reviews' => $_SESSION['text']['label']['Reviews'],
			'rating' => $_SESSION['text']['label']['Rating'],
		);
    }
    
    function showReviewLinks($searchInfo = '') {
    	$userId = isLoggedIn();
    	$this->set('searchInfo', $searchInfo);
    	$sql = "select l.*, w.name as website_name from $this->linkTable l, websites w where l.website_id=w.id";
    	
    	if (!isAdmin()) {
    	    $sql .= " and w.user_id=$userId";
    	}
    	
    	// search conditions
    	$sql .= !empty($searchInfo['name']) ? " and l.name like '%".addslashes($searchInfo['name'])."%'" : "";
    	$sql .= !empty($searchInfo['website_id']) ? " and l.website_id=".intval($searchInfo['website_id']) : "";
    	$sql .= !empty($searchInfo['type']) ? " and `type`='".addslashes($searchInfo['type'])."'" : "";
    	
    	if (!empty($searchInfo['status'])) {
    	    $sql .= ($searchInfo['status'] == 'active') ? " and l.status=1" : " and l.status=0"; 
    	}
    	
    	$webSiteCtrler = new WebsiteController();
    	$websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
    	$this->set( 'websiteList', $websiteList );
    	 
    	// pagination setup
    	$this->db->query( $sql, true );
    	$this->paging->setDivClass( 'pagingdiv' );
    	$this->paging->loadPaging( $this->db->noRows, SP_PAGINGNO );
    	$pagingDiv = $this->paging->printPages( $this->pageScriptPath, 'searchForm', 'scriptDoLoadPost', 'content', '' );
    	$this->set( 'pagingDiv', $pagingDiv );
    	$sql .= " limit " . $this->paging->start . "," . $this->paging->per_page;
    	
    	$linkList = $this->db->select( $sql );
    	$this->set( 'list', $linkList );
    	$this->render( 'review/show_review_links');
    }
    
    function __checkName($name, $websiteId, $linkId = false){
        $whereCond = "name='".addslashes($name)."'";
        $whereCond .= " and website_id='".intval($websiteId)."'";
        $whereCond .= !empty($linkId) ? " and id!=".intval($linkId) : "";
        $listInfo = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return empty($listInfo['id']) ? false :  $listInfo['id'];
    }
    
    function __checkUrl($url, $websiteId, $linkId = false){
        $whereCond = "url='".addslashes($url)."'";
        $whereCond .= " and website_id=".intval($websiteId);
        $whereCond .= !empty($linkId) ? " and id!=".intval($linkId) : "";
        $listInfo = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return empty($listInfo['id']) ? false :  $listInfo['id'];
    }
    
    function validateReviewLink($listInfo) {        
        $errMsg = [];
        $errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
        $errMsg['url'] = formatErrorMsg($this->validate->checkBlank($listInfo['url']));
        $errMsg['website_id'] = formatErrorMsg($this->validate->checkBlank($listInfo['website_id']));
        $errMsg['type'] = formatErrorMsg($this->validate->checkBlank($listInfo['type']));
        
        if(!$this->validate->flagErr){            
            if ($this->__checkName($listInfo['name'], $listInfo['website_id'], $listInfo['id'])) {
                $errMsg['name'] = formatErrorMsg($_SESSION['text']['label']['already exist']);
                $this->validate->flagErr = true;
            }
        }
        
        if(!$this->validate->flagErr){
            if ($this->__checkUrl($listInfo['url'], $listInfo['website_id'], $listInfo['id'])) {
                $errMsg['url'] = formatErrorMsg($_SESSION['text']['label']['already exist']);
                $this->validate->flagErr = true;
            }
        }
        
        if(!$this->validate->flagErr){
            if (!stristr($listInfo['url'], $listInfo['type'])) {
                $errMsg['url'] = formatErrorMsg($_SESSION['text']['common']["Invalid value"]);
                $this->validate->flagErr = true;
            }
        }
        
        // Validate link count
        if(!$this->validate->flagErr){
            $websiteCtrl = new WebsiteController();
            $websiteInfo = $websiteCtrl->__getWebsiteInfo($listInfo['website_id']);
            $newCount = !empty($listInfo['id']) ? 0 : 1;
            if (! $this->validateReviewLinkCount($websiteInfo['user_id'], $newCount)) {
                $this->set('validationMsg', 'Your review link count already reached the limit');
                $this->validate->flagErr = true;
            }
        }
        
        return $errMsg;        
    }
    
    // Function to check / validate the user type review count
    function validateReviewLinkCount($userId, $newCount = 1) {
        $userCtrler = new UserController();
        
        // if admin user id return true
        if ($userCtrler->isAdminUserId($userId)) {
            return true;
        }
        
        $userTypeCtrlr = new UserTypeController();
        $userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
        
        $whereCond = "l.website_id=w.id and w.user_id=".intval($userId);
        $existingInfo = $this->dbHelper->getRow("$this->linkTable l, websites w", $whereCond, "count(*) count");
        $userSMLinkCount = $existingInfo['count'];
        $userSMLinkCount += $newCount;
        
        // if limit is set and not -1
        if (isset($userTypeDetails['review_link_count']) && $userTypeDetails['review_link_count'] >= 0) {
            
            // check whether count greater than limit
            if ($userSMLinkCount <= $userTypeDetails['review_link_count']) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return true;
        }
        
    }
    
    function newReviewLink($info=[]) {
        $userId = isLoggedIn();
        $this->set('post', $info);
        $webSiteCtrler = new WebsiteController();
        $websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
        $this->set( 'websiteList', $websiteList );                
        $this->set('editAction', 'createReviewLink');
        $this->render( 'review/edit_review_link');   
    }
    
    function createReviewLink($listInfo = '') {
        $listInfo['name'] = trim($listInfo['name']);
        $listInfo['url'] = trim($listInfo['url']);
        $errMsg = $this->validateReviewLink($listInfo);
        
        // if no error occured
        if (!$this->validate->flagErr) {
            $dataList = [
                'name' => $listInfo['name'],
                'url' => addHttpToUrl($listInfo['url']),
                'type' => $listInfo['type'],
                'website_id|int' => $listInfo['website_id'],
            ];
            $this->dbHelper->insertRow($this->linkTable, $dataList);
            $this->showReviewLinks(['name' => $listInfo['name']]);
            exit;
        }
        
        $this->set('errMsg', $errMsg);
        $this->newReviewLink($listInfo);
    }
    
    function editReviewLink($linkId, $listInfo = '') {
        
        if (!empty($linkId)) {
            $userId = isLoggedIn();
            $webSiteCtrler = new WebsiteController();
            $websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
            $this->set( 'websiteList', $websiteList );
            
            if(empty($listInfo)){
                $listInfo = $this->__getReviewLinkInfo($linkId);
            }
            
            $this->set('post', $listInfo);           
            $this->set('editAction', 'updateReviewLink');
            $this->render( 'review/edit_review_link');
        }
        
    }
    
    function updateReviewLink($listInfo) {
        $listInfo['name'] = trim($listInfo['name']);
        $listInfo['url'] = trim($listInfo['url']);
        $this->set('post', $listInfo);
        $errMsg = $this->validateReviewLink($listInfo);
        
        if (!$this->validate->flagErr) {
            $dataList = [
                'name' => $listInfo['name'],
                'url' => addHttpToUrl($listInfo['url']),
                'type' => $listInfo['type'],
                'website_id|int' => $listInfo['website_id'],
            ];
            $this->dbHelper->updateRow($this->linkTable, $dataList, "id=".intval($listInfo['id']));
            $this->showReviewLinks(['name' => $listInfo['name']]);
            exit;
        }
        
        $this->set('errMsg', $errMsg);
        $this->editReviewLink($listInfo['id'], $listInfo);
    }
    
    function deleteReviewLink($linkId) {
        $this->dbHelper->deleteRows($this->linkTable, "id=" . intval($linkId));
        $this->showReviewLinks();
    }
    
    function __changeStatus($linkId, $status){
        $linkId = intval($linkId);
        $this->dbHelper->updateRow($this->linkTable, ['status|int' => $status], "id=$linkId");
    }
    
    function __getReviewLinkInfo($linkId) {
        $whereCond = "id=".intval($linkId);
        $info = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return $info;
    }
    
    function verifyActionAllowed($linkId) {
        $allowed = true;
        
        // if not admin, check the permissions
        if (!isAdmin()) {
            $userId = isLoggedIn();
            $linkInfo = $this->__getReviewLinkInfo($linkId);
            $webSiteCtrler = new WebsiteController();
            $webSiteInfo = $webSiteCtrler->__getWebsiteInfo($linkInfo['website_id']);
            $allowed = ($userId == $webSiteInfo['user_id']) ? true : false;
        }
        
        if (!$allowed) {
            showErrorMsg($_SESSION['text']['label']['Access denied']); 
        }
                
    }

	function viewQuickChecker($info=[]) {
		$this->render('review/quick_checker');
	}

	function doQuickChecker($listInfo = '') {
		
		if (!stristr($listInfo['url'], $listInfo['type'])) {
			$errorMsg = formatErrorMsg($_SESSION['text']['common']["Invalid value"]);
			$this->validate->flagErr = true;
		}
		
		// if no error occured find review details
		if (!$this->validate->flagErr) {
			$smLink = addHttpToUrl($listInfo['url']);
			$result = $this->getReviewDetails($listInfo['type'], $smLink);
			
			// if call is success
			if ($result['status']) {
				$this->set('smType', $listInfo['type']);
				$this->set('smLink', $smLink);
				$this->set('statInfo', $result);
				$this->render('review/quick_checker_results');
				exit;
			} else {
				$errorMsg = $result['msg'];
			}
			
		}
		
		$errorMsg = !empty($errorMsg) ? $errorMsg : $_SESSION['text']['common']['Internal error occured'];
		showErrorMsg($errorMsg);
		
	}	
	
	function getReviewDetails($smType, $smLink) {
		$result = ['status' => 0, 'reviews' => 0, 'rating' => 0, 'msg' => $_SESSION['text']['common']['Internal error occured']];
		$smInfo = $this->serviceList[$smType];
		
		if (!empty($smInfo) && !empty($smLink)) {
			
			// if params needs to be added with url
			if (!empty($smInfo['url_part'])) {
				$smLink .= stristr($smLink, '?') ? str_replace("?", "&", $smInfo['url_part']) : $smInfo['url_part'];
			}
			
			$smContentInfo = $this->spider->getContent($smLink);
			
			// testing val
			/*$myfile = fopen(SP_TMPPATH . "/gbusiness.html", "w") or die("Unable to open file!");
			fwrite($myfile, $smContentInfo['page']);
			fclose($myfile);
			exit;

			$smContentInfo = [];
			$myfile = fopen(SP_TMPPATH . "/gbusiness.html", "r") or die("Unable to open file!");
			$smContentInfo['page'] = fread($myfile,filesize(SP_TMPPATH . "/gbusiness.html"));
			fclose($myfile);*/
			
			if (!empty($smContentInfo['page'])) {
			    $matches = [];

				// find reviews
				if (!empty($smInfo['regex']['reviews'])) {
				    preg_match($smInfo['regex']['reviews'], $smContentInfo['page'], $matches);
					
					if (!empty($matches[1])) {
						$result['status'] = 1;
						$result['reviews'] = formatNumber($matches[1]);
					}
				}
				
				// find rating
				if (!empty($smInfo['regex']['rating'])) {
					preg_match($smInfo['regex']['rating'], $smContentInfo['page'], $matches);
						
					if (!empty($matches[1])) {
						$result['status'] = 1;
						$result['rating'] = round(formatNumber($matches[1]), 2);
					}	
				}
				
				// if not found any details
				if (!$result['status']) {
				    $result['msg'] = "Review page details not found.";
				} else {
				    $result['msg'] = "Review page details fetched successfully.";
				}
			} else {
				$result['msg'] = $smContentInfo['errmsg'];
			}
		}
		
		return $result;
		
	}
	
	/*
	 * function to get all links with out reports for a day
	 */
	function getAllLinksWithOutReports($websiteId, $date) {
		$websiteId = intval($websiteId);
		$date = addslashes($date);
		$sql = "select link.*, lr.id result_id from review_links link left join 
			review_link_results lr on (link.id=lr.review_link_id and lr.report_date='$date') 
			where link.status=1 and link.website_id=$websiteId and lr.id is NULL";
		
		$linkList = $this->db->select($sql);
		return $linkList;
		
	}
	
	function saveReviewLinkResults($linkId, $linkInfo) {		
		$dataList = [
			'review_link_id|int' => $linkId,
			'reviews|int' => $linkInfo['reviews'],
			'rating|float' => $linkInfo['rating'],
			'report_date' => date('Y-m-d'),
		];
		
		$this->dbHelper->insertRow($this->linkReportTable, $dataList);		
	}
	
	/*
	 * func to show report summary
	 */ 
	function viewReportSummary($searchInfo = '', $summaryPage = false, $cronUserId=false) {
	
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$this->set('summaryPage', $summaryPage);
		$this->set('searchInfo', $searchInfo);
		$this->set('cronUserId', $cronUserId);
	
		$exportVersion = false;
		switch($searchInfo['doc_type']){
	
			case "export":
				$exportVersion = true;
				$exportContent = "";
				break;
					
			case "pdf":
				$this->set('pdfVersion', true);
				break;
					
			case "print":
				$this->set('printVersion', true);
				break;
		}
	
		$fromTime = !empty($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-1 days'));
		$toTime = !empty($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d');
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
	
		$websiteController = New WebsiteController();
		$wList = $websiteController->__getAllWebsites($userId, true);
		$websiteList = [];
		foreach ($wList as $wInfo) $websiteList[$wInfo['id']] = $wInfo;
		$websiteList = count($websiteList) ? $websiteList : array(0);
		$this->set('websiteList', $websiteList);
		$websiteId = intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		// to find order col
		if (!empty($searchInfo['order_col'])) {
			$orderCol = $searchInfo['order_col'];
			$orderVal = getOrderByVal($searchInfo['order_val']);
		} else {
			$orderCol = "rating";
			$orderVal = 'DESC';
		}
	
		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);
		$scriptName = $summaryPage ? "archive.php" : $this->pageScriptPath;
		$scriptPath = SP_WEBPATH . "/$scriptName?sec=reportSummary&website_id=$websiteId";
		$scriptPath .= "&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'] . "&type=" . $searchInfo['type'];
		$scriptPath .= "&order_col=$orderCol&order_val=$orderVal&report_type=social-media-reports";
	
		// set website id to get exact keywords of a user
		if (!empty($websiteId)) {
			$conditions = " and sml.website_id=$websiteId";
		} else {
			$conditions = " and sml.website_id in (".implode(',', array_keys($websiteList)).")";
		}
	
		$conditions .= !empty($searchInfo['search_name']) ? " and sml.name like '%".addslashes($searchInfo['search_name'])."%'" : "";
		$conditions .= !empty($searchInfo['type']) ? " and sml.type='".addslashes($searchInfo['type'])."'" : "";
	
		$subSql = "select [cols] from $this->linkTable sml, $this->linkReportTable r where sml.id=r.review_link_id
		and sml.status=1 $conditions and r.report_date='$toTime'";
	
		$sql = "
		(" . str_replace("[cols]", "sml.id,sml.url,sml.name,sml.website_id,sml.type,r.reviews,r.rating", $subSql) . ")
			UNION
			(select sml.id,sml.url,sml.name,sml.website_id,sml.type,0,0 from $this->linkTable sml where sml.status=1 $conditions
			and sml.id not in (". str_replace("[cols]", "distinct(sml.id)", $subSql) ."))
		order by " . addslashes($orderCol) . " " . addslashes($orderVal);
	
		if ($orderCol != 'name') $sql .= ", name";
	
		// pagination setup, if not from cron job email send function, pdf and export action
		if (!in_array($searchInfo['doc_type'], array("pdf", "export"))) {
			$this->db->query($sql, true);
			$this->paging->setDivClass('pagingdiv');
			$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
			$pagingDiv = $this->paging->printPages($scriptPath, '', 'scriptDoLoad', 'content', "");
			$this->set('pagingDiv', $pagingDiv);
			$this->set('pageNo', $searchInfo['pageno']);
			$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		}
	
		# set report list
		$baseReportList = $this->db->select($sql);
		$this->set('baseReportList', $baseReportList);
		$this->set('colList', $this->colList);
	
		// if keywords existing
		if (!empty($baseReportList)) {
				
			$keywordIdList = array();
			foreach ($baseReportList as $info) {
				$keywordIdList[] = $info['id'];
			}
	
			$sql = "select sml.id,sml.url,sml.website_id,sml.type,r.rating,r.reviews
			from $this->linkTable sml, $this->linkReportTable r where sml.id=r.review_link_id
			and sml.status=1 $conditions and r.report_date='$fromTime'";
			$sql .= " and sml.id in(" . implode(",", $keywordIdList) . ")";
			$reportList = $this->db->select($sql);
			$compareReportList = array();
				
			foreach ($reportList as $info) {
				$compareReportList[$info['id']] = $info;
			}
				
			$this->set('compareReportList', $compareReportList);
				
		}
	
		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$reportHeading =  $this->spTextTools['Review Report Summary']."($fromTime - $toTime)";
			$exportContent .= createExportContent( array('', $reportHeading, ''));
			$exportContent .= createExportContent( array());
			$headList = array($spText['common']['Website'], $spText['common']['Url'], $spText['label']['Type']);
	
			$pTxt = str_replace("-", "/", substr($fromTime, -5));
			$cTxt = str_replace("-", "/", substr($toTime, -5));
			foreach ($this->colList as $colKey => $colLabel) {
				if ($colKey == 'name') continue;
				$headList[] = $colLabel . "($pTxt)";
				$headList[] = $colLabel . "($cTxt)";
				$headList[] = $colLabel . "(+/-)";
			}
	
			$exportContent .= createExportContent($headList);
			foreach($baseReportList as $listInfo){
	
			    $valueList = array($websiteList[$listInfo['website_id']]['url'], $listInfo['url'], $this->serviceList[$listInfo['type']]['label']);
				foreach ($this->colList as $colName => $colVal) {
					if ($colName == 'name') continue;
						
					$currRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
					$prevRank = isset($compareReportList[$listInfo['id']][$colName]) ? $compareReportList[$listInfo['id']][$colName] : 0;
					$rankDiff = "";
	
					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $currRank - $prevRank;
					}
	
					$valueList[] = $prevRank;
					$valueList[] = $currRank;
					$valueList[] = $rankDiff;
				}
	
				$exportContent .= createExportContent( $valueList);
			}
				
			if ($summaryPage) {
				return $exportContent;
			} else {
				exportToCsv('review_report_summary', $exportContent);
			}
				
		} else {
				
			// if pdf export
			if ($summaryPage) {
				return $this->getViewContent('review/review_report_summary');
			} else {
				// if pdf export
				if ($searchInfo['doc_type'] == "pdf") {
					exportToPdf($this->getViewContent('review/review_report_summary'), "review_report_summary_$fromTime-$toTime.pdf");
				} else {
					$this->set('searchInfo', $searchInfo);
					$this->render('review/review_report_summary');
				}
			}
				
		}
	}
	
	function __getReviewLinks($whereCond = false) {
	    $linkList = $this->dbHelper->getAllRows($this->linkTable, $whereCond);
	    return !empty($linkList) ? $linkList : false;
	}
	
	// func to show detailed reports
	function viewDetailedReports($searchInfo = '') {
	
		$userId = isLoggedIn();

		if (!empty ($searchInfo['from_time'])) {
			$fromTimeDate = addslashes($searchInfo['from_time']);
		} else {
			$fromTimeDate = date('Y-m-d', strtotime('-15 days'));
		}
		
		if (!empty ($searchInfo['to_time'])) {
			$toTimeDate = addslashes($searchInfo['to_time']);
		} else {
			$toTimeDate = date('Y-m-d');
		}
		
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
	
	    if(!empty($searchInfo['link_id']) && !empty($searchInfo['rep'])){				
			$searchInfo['link_id'] = intval($searchInfo['link_id']);
			$linkInfo = $this->__getReviewLinkInfo($searchInfo['link_id']);
			$searchInfo['website_id'] = $linkInfo['website_id'];
		}
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$linkList = $this->__getReviewLinks("website_id=$websiteId and status=1 order by name");
		$this->set('linkList', $linkList);
		$linkId = empty($searchInfo['link_id']) ? $linkList[0]['id'] : intval($searchInfo['link_id']);
		$this->set('linkId', $linkId);
	
		$list = [];
		if (!empty($linkId)) {
		
    		$sql = "select s.* from $this->linkReportTable s
    		where report_date>='$fromTimeDate' and report_date<='$toTimeDate' and s.review_link_id=$linkId
    		order by s.report_date";
    		$reportList = $this->db->select($sql);
    		
    		$colList = array_keys($this->colList);
    		array_shift($colList);
    		foreach ($colList as $col) $prevRank[$col] = 0;
    		
    		# loop through rank
    		foreach ($reportList as $key => $repInfo) {
    			
    			// if not the first row, find differences in rank
    			if ($key)  {
    				
    				foreach ($colList as $col) $rankDiff[$col] = '';
    					
    				foreach ($colList as $col) {
    					$rankDiff[$col] = round($repInfo[$col] - $prevRank[$col], 2);
    					if (empty($rankDiff[$col])) continue;
    					
    					if ($col == "average_position" ) $rankDiff[$col] = $rankDiff[$col] * -1;
    					$rankClass = ($rankDiff[$col] > 0) ? 'green' : 'red';
    					
    					$rankDiff[$col] = "<font class='$rankClass'>($rankDiff[$col])</font>";
    					$reportList[$key]['rank_diff_'.$col] = empty($rankDiff[$col]) ? '' : $rankDiff[$col];			
    				}
    				
    			}
    				
    			foreach ($colList as $col) $prevRank[$col] = $repInfo[$col];
    		
    		}
    		
    		$list = array_reverse($reportList, true);
		}
		
		$this->set('list', $list);				
		$this->render('review/review_reports');
		
	}
	
	// func to show review link select box
	function showReviewLinkSelectBox($websiteId, $linkId = ""){
	    $websiteId = intval($websiteId);
	    $this->set('linkList', $this->__getReviewLinks("website_id=$websiteId and status=1 order by name"));
	    $this->set('linkId', $linkId);
	    $this->render('review/review_link_select_box');
	}
	
	// func to show link search reports in graph
	function viewGraphReports($searchInfo = '') {
	    
	    $userId = isLoggedIn();
	    
	    if (!empty ($searchInfo['from_time'])) {
	        $fromTimeDate = addslashes($searchInfo['from_time']);
	    } else {
	        $fromTimeDate = date('Y-m-d', strtotime('-15 days'));
	    }
	    
	    if (!empty ($searchInfo['to_time'])) {
	        $toTimeDate = addslashes($searchInfo['to_time']);
	    } else {
	        $toTimeDate = date('Y-m-d');
	    }
	    
	    $this->set('fromTime', $fromTimeDate);
	    $this->set('toTime', $toTimeDate);
	    
	    if(!empty($searchInfo['link_id']) && !empty($searchInfo['rep'])){
	        $searchInfo['link_id'] = intval($searchInfo['link_id']);
	        $linkInfo = $this->__getReviewLinkInfo($searchInfo['link_id']);
	        $searchInfo['website_id'] = $linkInfo['website_id'];
	    }
	    
	    $websiteController = New WebsiteController();
	    $websiteList = $websiteController->__getAllWebsites($userId, true);
	    $this->set('websiteList', $websiteList);
	    $websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
	    $this->set('websiteId', $websiteId);
	    
	    $linkList = $this->__getReviewLinks("website_id=$websiteId and status=1 order by name");
	    $this->set('linkList', $linkList);
	    $linkId = empty($searchInfo['link_id']) ? $linkList[0]['id'] : intval($searchInfo['link_id']);
	    $this->set('linkId', $linkId);
	    
	    // if reports not empty
	    $colList = $this->colList;
	    array_shift($colList);
	    $this->set('colList', $colList);
	    $this->set('searchInfo', $searchInfo);
	
	    $graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
	    if (!empty($linkId)) {
	        
	        $sql = "select s.* from $this->linkReportTable s
    		where report_date>='$fromTimeDate' and report_date<='$toTimeDate'  and s.review_link_id=$linkId
    		order by s.report_date";
	        $reportList = $this->db->select($sql);
    		
    		$graphColList = array();
    		if (!empty($searchInfo['attr_type'])) {
    			$graphColList[$searchInfo['attr_type']] = $colList[$searchInfo['attr_type']];
    		} else {
    			//array_pop($colList);
    			$graphColList = $colList;
    		}
    		
    		if (!empty($reportList)) {
    				
    			$dataArr = "['Date', '" . implode("', '", array_values($graphColList)) . "']";
    			 
    			// loop through data list
    			foreach ($reportList as $dataInfo) {
    	
    				$valStr = "";
    				foreach ($graphColList as $seId => $seVal) {
    					$valStr .= ", ";
    					$valStr .= !empty($dataInfo[$seId]) ? $dataInfo[$seId] : 0;
    				}
    	
    				$dataArr .= ", ['{$dataInfo['report_date']}' $valStr]";
    			}
    			 
    			$this->set('dataArr', $dataArr);
    			$this->set('graphTitle', $this->spTextTools['Graphical Reports']);
    			$graphContent = $this->getViewContent('report/graph');
    		} else {
    			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
    		}
    		
	    }
		
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('review/graphicalreport');
		
	}
	
}
?>