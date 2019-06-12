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

// include google api module
include_once(SP_CTRLPATH . "/googleapi.ctrl.php");

// class defines all google webmaster tool api controller functions
class WebMasterController extends GoogleAPIController {
	
	var $rowLimit = 5000;
	var $sourceList = array('google');
	var $colList = array();	
	
    function __construct() {
		parent::__construct();

		$this->spTextWB = $this->getLanguageTexts('webmaster', $_SESSION['lang_code']);
		$this->set('spTextWB', $this->spTextWB);
		$this->tokenCtrler = new UserTokenController();
		
		$this->colList = array(
			'name' => $_SESSION['text']['common']['Keyword'],
			'clicks' => $_SESSION['text']['label']['Clicks'],
			'impressions' => $_SESSION['text']['label']['Impressions'],
			'ctr' => "CTR",
			'average_position' => $this->spTextWB['Average Position'],
		);
		
	}
	
	/*
	 * function to get webmaster tool query search result
	 */
	function getQueryResults($userId, $siteUrl, $paramList, $limit = false) {
		$result = array('status' => false);
		
		try {
			
			$client = $this->getAuthClient($userId);
			
			// check whether client created successfully
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
			
			$service = new Google_Service_Webmasters($client);
			$serviceRquest = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
			$serviceRquest->startDate = $paramList['startDate'];
			$serviceRquest->endDate = $paramList['endDate'];
			
			if (!empty($paramList['dimensions'])) {
				$serviceRquest->dimensions = $paramList['dimensions'];
			}
			
			if (!empty($paramList['dimensionFilterGroups'])) {
				$serviceRquest->dimensionFilterGroups = $paramList['dimensionFilterGroups'];
			}
			
			$resultList = array();
			$startRow = 0;
			$limit = $limit ? $limit : $this->rowLimit;
			
			while (count($resultList) < $limit) {
				$serviceRquest->startRow = $startRow;
				$serviceRquest->rowLimit = $this->rowLimit;
				$statRes = $service->searchanalytics->query($siteUrl, $serviceRquest);
				$rowList = $statRes->getRows();
				$resultList = array_merge($resultList, $rowList);
				
				// if the result count is less than expected in a call
				if (count($rowList) < $this->rowLimit) {
					break;
				}
				
				$startRow += $this->rowLimit;
			}
			
			$result['status'] = true;
			$result['resultList'] = $resultList;
			
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: search query analytics - $err";
		}
		
		return $result;
		
	}
	
	/*
	 * function to get all sites added in webmaster tools
	 */
	function getAllSites($userId) {
		$result = array('status' => false);
	
		try {
				
			$client = $this->getAuthClient($userId);
				
			// check whether client created successfully
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
				
			$service = new Google_Service_Webmasters($client);
			$resultList = $service->sites->listSites();
			$result['status'] = true;
			$result['resultList'] = $resultList['siteEntry'];
				
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: get all sites - $err";
		}
	
		return $result;
	
	}
	
	/*
	 * function to submit website to webmaster tools
	 */
	function addWebsite($siteUrl, $userId) {
		$result = array('status' => false);
	
		try {
				
			$client = $this->getAuthClient($userId);
				
			// check whether client created successfully
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
				
			$service = new Google_Service_Webmasters($client);
			$resultList = $service->sites->add($siteUrl);
			$result['status'] = true;				
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: add website - $err";
		}
	
		return $result;
	
	}
	
	/*
	 * function to submit sitemap to webmaster tools
	 */
	function submitSitemap($siteUrl, $sitemapUrl, $userId) {
		$result = array('status' => false);
	
		try {
				
			$client = $this->getAuthClient($userId);
				
			// check whether client created successfully
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
				
			$service = new Google_Service_Webmasters($client);
			$resultList = $service->sitemaps->submit($siteUrl, $sitemapUrl);
			$result['status'] = true;				
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: submit sitemap - $err";
		}
	
		return $result;
	
	}
	
	/*
	 * function to get all sitemaps submitted in webmaster tools
	 */
	function getAllSitemap($siteUrl, $userId) {
		$result = array('status' => false);
	
		try {
				
			$client = $this->getAuthClient($userId);
				
			// check whether client created successfully
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
				
			$service = new Google_Service_Webmasters($client);
			$resultList = $service->sitemaps->listSitemaps($siteUrl);			
			$result['resultList'] = $resultList['sitemap'];
			$result['status'] = true;
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: get all sitemaps - $err";
		}
	
		return $result;
	
	}
	/*
	 * function to submit sitemap to webmaster tools
	 */
	function deleteWebsiteSitemap($siteUrl, $sitemapUrl, $userId) {
		$result = array('status' => false);
	
		try {
			
			// check whether client created successfully
			$client = $this->getAuthClient($userId);
			if (!is_object($client)) {
				$result['msg'] = $client;
				return $result;
			}
				
			$service = new Google_Service_Webmasters($client);
			$resultList = $service->sitemaps->delete($siteUrl, $sitemapUrl);
			$result['status'] = true;
		}  catch (Exception $e) {
			$err = $e->getMessage();
			$result['msg'] = "Error: delete sitemap - $err";
		}
	
		return $result;
	
	}
	
	
	function __generateKeywordId($websiteId, $keywordName) {
		$keywordId = 0;
		$dataList = array(
			'website_id|int' => $websiteId,
			'name' => $keywordName,
			'status' => 1,
		);
		
		if ($this->dbHelper->insertRow("webmaster_keywords", $dataList) ) {
			$keywordId = $this->db->getMaxId("webmaster_keywords");
		}
		
		return $keywordId;
	}

	function __getWebmasterKeywordInfo($keywordId){
		$keywordId = intval($keywordId);
		$listInfo = $this->dbHelper->getRow("webmaster_keywords", " id=$keywordId");
		return !empty($listInfo['id']) ? $listInfo : false;
	}

	function __getWebmasterKeywords($whereCond = false) {
		$keywordList = $this->dbHelper->getAllRows("webmaster_keywords", $whereCond);
		return !empty($keywordList) ? $keywordList : false;
	}

	/*
	 * function to store website results
	 */
	function storeWebsiteAnalytics($websiteId, $reportDate, $source = "google") {		
		$websiteId = intval($websiteId);
		$websiteCtrler = new WebsiteController();
		$websiteInfo = $websiteCtrler->__getWebsiteInfo($websiteId);
		$list = $this->__getWebmasterKeywords("website_id=$websiteId and status=1");
		$keywordList = array();
		if (!empty($list)) {foreach ($list as $info) $keywordList[$info['name']] = $info;}
		$result['status'] = true;
			
		$paramList = array(
			'startDate' => $reportDate,
			'endDate' => $reportDate,
			'dimensions' => ['query'],
		);
		
		// query results from api and verify no error occured
		$result = $this->getQueryResults($websiteInfo['user_id'], $websiteInfo['url'], $paramList);
		if ($result['status']) {
			
			// loop through the result list
			foreach ($result['resultList'] as $reportInfo) {
				$keywordName = $reportInfo['keys'][0];
				
				// check if keyword is already existing in the db table, else insert it
				$keywordId = isset($keywordList[$keywordName]) ? intval($keywordList[$keywordName]['id']) : 0;
				if ($keywordId == 0) {$keywordId = $this->__generateKeywordId($websiteInfo['id'], $keywordName);}
				
				$info = array(
					'clicks' => $reportInfo['clicks'],
					'impressions' => $reportInfo['impressions'],
					'ctr' => round($reportInfo['ctr'] * 100, 2),
					'average_position' => $reportInfo['position'],
					'report_date' => $reportDate,
					'source' => $source,
				);
				
				$this->insertKeywordAnalytics($keywordId, $info);
				
			}
						
		}

		// if keyword report generated successfully
		if ($result['status']) {
			
			// store website analytics
			$paramList = array(
				'startDate' => $reportDate,
				'endDate' => $reportDate,
			);
				
			// query results from api and verify no error occured
			$result = $this->getQueryResults($websiteInfo['user_id'], $websiteInfo['url'], $paramList);
			
			// if status is success
			if ($result['status']) {
				$reportInfo = !empty($result['resultList'][0]) ? $result['resultList'][0] : array();
				$info = array(
					'clicks' => !empty($reportInfo->clicks) ? $reportInfo->clicks : 0,
					'impressions' => !empty($reportInfo->impressions) ? $reportInfo->impressions : 0,
					'ctr' => !empty($reportInfo->ctr) ? $reportInfo->ctr * 100 : 0,
					'average_position' => !empty($reportInfo->position) ? $reportInfo->position : 0,
					'report_date' => $reportDate,
					'source' => $source,
				);
					
				$this->insertWebsiteAnalytics($websiteId, $info);
			}
			
		}
		
		return $result;
		
	}
	
	/*
	 * function to insert keyword analytics
	 */
	function insertKeywordAnalytics($keywordId, $reportInfo, $clearExisting = true) {
		$keywordId = intval($keywordId);
		$source = addslashes($reportInfo['source']);
		$resultDate = addslashes($reportInfo['report_date']);		
		
		if ($clearExisting) {
			$whereCond = "keyword_id=$keywordId and report_date='$resultDate' and source='$source'";
			$this->dbHelper->deleteRows('keyword_analytics', $whereCond);
		}
		
		$dataList = array(
			'keyword_id' => $keywordId,
			'clicks|int' => $reportInfo['clicks'],
			'impressions|int' => $reportInfo['impressions'],
			'ctr|float' => round($reportInfo['ctr'], 2),
			'average_position|float' => round($reportInfo['average_position'], 2),
			'report_date' => $resultDate,
			'source' => $source,
		);
		
		$this->dbHelper->insertRow('keyword_analytics', $dataList);
		
	}
	
	/*
	 * function to insert website analytics
	 */
	function insertWebsiteAnalytics($websiteId, $reportInfo, $clearExisting = true) {
		$websiteId = intval($websiteId);
		$source = addslashes($reportInfo['source']);
		$resultDate = addslashes($reportInfo['report_date']);
		
		if ($clearExisting) {
			$whereCond = "website_id=$websiteId and report_date='$resultDate' and source='$source'";
			$this->dbHelper->deleteRows('website_search_analytics', $whereCond);
		}
		
		$dataList = array(
			'website_id' => $websiteId,
			'clicks|int' => $reportInfo['clicks'],
			'impressions|int' => $reportInfo['impressions'],
			'ctr|float' => round($reportInfo['ctr'], 2),
			'average_position|float' => round($reportInfo['average_position'], 2),
			'report_date' => $resultDate,
			'source' => $source,
		);
		
		$this->dbHelper->insertRow('website_search_analytics', $dataList);
		
	}

	# function check whether webmaster reports already saved
	function isReportsExists($websiteId, $resultDate, $source = "google") {
		$websiteId = intval($websiteId);
		$source = addslashes($source);
		$resultDate = addslashes($resultDate);
		$whereCond = "website_id=$websiteId and report_date='$resultDate' and source='$source'";
		$info = $this->dbHelper->getRow("website_search_analytics", $whereCond, "website_id");
		return !empty($info['website_id']) ? true : false;
	}
	
	# func to show webmasterkeyword report summary
	function viewKeywordSearchSummary($searchInfo = '', $summaryPage = false, $cronUserId=false) {
	
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$keywordController = New KeywordController();
		$source = $this->sourceList[0];
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

		$fromTime = !empty($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-4 days'));
		$toTime = !empty($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d', strtotime('-3 days'));
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
			$orderCol = "clicks";
			$orderVal = 'DESC';
		}
	
		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);
		$scriptName = $summaryPage ? "archive.php" : "webmaster-tools.php";
		$scriptPath = SP_WEBPATH . "/$scriptName?sec=viewKeywordReports&website_id=$websiteId";
		$scriptPath .= "&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
		$scriptPath .= "&order_col=$orderCol&order_val=$orderVal&report_type=keyword-search-reports";
		
		// set website id to get exact keywords of a user
		if (!empty($websiteId)) {
			$conditions = " and k.website_id=$websiteId";
		} else {
			$conditions = " and k.website_id in (".implode(',', array_keys($websiteList)).")";
		}
		
		$conditions .= !empty($searchInfo['search_name']) ? " and k.name like '%".addslashes($searchInfo['search_name'])."%'" : "";
		
		$subSql = "select [cols] from webmaster_keywords k, keyword_analytics r where k.id=r.keyword_id
		and k.status=1 $conditions and r.source='$source' and r.report_date='$toTime'";
		
		$sql = "
		(" . str_replace("[cols]", "k.id,k.name,k.website_id,r.clicks,r.impressions,r.ctr,r.average_position", $subSql) . ")
		UNION
		(select k.id,k.name,k.website_id,0,0,0,0 from webmaster_keywords k where k.status=1 $conditions 
		and k.id not in (". str_replace("[cols]", "distinct(k.id)", $subSql) ."))
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

			$sql = "select k.id,k.name,k.website_id,r.clicks,r.impressions,r.ctr,r.average_position 
			from webmaster_keywords k, keyword_analytics r where k.id=r.keyword_id
			and k.status=1 $conditions and r.source='$source' and r.report_date='$fromTime'";
			$sql .= " and k.id in(" . implode(",", $keywordIdList) . ")";
			$reportList = $this->db->select($sql);
			$compareReportList = array();
			
			foreach ($reportList as $info) {
				$compareReportList[$info['id']] = $info;	
			}
			
			$this->set('compareReportList', $compareReportList);
			
		}
	
		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$reportHeading =  $this->spTextTools['Keyword Search Summary']."($fromTime - $toTime)";
			$exportContent .= createExportContent( array('', $reportHeading, ''));
			$exportContent .= createExportContent( array());
			$headList = array($spText['common']['Website'], $spText['common']['Keyword']);
	
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
	
				$valueList = array($websiteList[$listInfo['website_id']]['url'], $listInfo['name']);
				foreach ($this->colList as $colName => $colVal) {
					if ($colName == 'name') continue;
					
					$currRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
					$prevRank = isset($compareReportList[$listInfo['id']][$colName]) ? $compareReportList[$listInfo['id']][$colName] : 0;
					$rankDiff = "";
	
					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $currRank - $prevRank;
						if ($colName == 'average_position') $rankDiff = $rankDiff * -1;
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
				exportToCsv('keyword_search_summary', $exportContent);
			}
			
		} else {
			
			// if pdf export
			if ($summaryPage) {
				return $this->getViewContent('webmaster/keyword_search_analytics_summary');
			} else {
				// if pdf export
				if ($searchInfo['doc_type'] == "pdf") {
					exportToPdf($this->getViewContent('webmaster/keyword_search_analytics_summary'), "keyword_search_summary_$fromTime-$toTime.pdf");
				} else {
					$this->set('searchInfo', $searchInfo);
					$this->render('webmaster/keyword_search_analytics_summary');
				}
			}
			
		}
	}
	
	# func to show website search report summary
	function viewWebsiteSearchSummary($searchInfo = '', $summaryPage = false, $cronUserId=false) {
	
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$websiteController = New WebsiteController();
		$exportVersion = false;
		$source = $this->sourceList[0];
		$this->set('summaryPage', $summaryPage);
		$this->set('searchInfo', $searchInfo);
		
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

		$fromTime = !empty($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-4 days'));
		$toTime = !empty($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d', strtotime('-3 days'));
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
	
		// to find order col
		if (!empty($searchInfo['order_col'])) {
			$orderCol = $searchInfo['order_col'];
			$orderVal = getOrderByVal($searchInfo['order_val']);
		} else {
			$orderCol = "clicks";
			$orderVal = 'DESC';
		}
	
		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);
		$scriptName = $summaryPage ? "archive.php" : "webmaster-tools.php";
		$scriptPath = SP_WEBPATH . "/$scriptName?sec=viewWebsiteSearchSummary&website_id=$websiteId";
		$scriptPath .= "&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
		$scriptPath .= "&order_col=$orderCol&order_val=$orderVal&report_type=website-search-reports";
		
		$websiteId = intval($searchInfo['website_id']);
		$conditions = !empty($websiteId) ? " and w.id=$websiteId" : "";
		$conditions .= isAdmin() ? "" : " and w.user_id=$userId";
		$conditions .= !empty($searchInfo['search_name']) ? " and w.url like '%".addslashes($searchInfo['search_name'])."%'" : "";
		$this->set('websiteId', $websiteId);
		
		$subSql = "select [cols] from websites w, website_search_analytics r where w.id=r.website_id
		and w.status=1 $conditions and r.source='$source' and r.report_date='$fromTime'";
		
		$unionOrderCol = ($orderCol == 'name') ? "url" : $orderCol;
		$sql = "
		(" . str_replace("[cols]", "w.id,w.url,w.name,r.clicks,r.impressions,r.ctr,r.average_position", $subSql) . ")
		UNION
		(select w.id,w.url,w.name,0,0,0,0 from websites w where w.status=1 $conditions 
		and w.id not in (". str_replace("[cols]", "distinct(w.id)", $subSql) ."))
		order by " . addslashes($unionOrderCol) . " " . addslashes($orderVal);
		
		if ($orderCol != 'name') $sql .= ", url";
		
		// pagination setup, if not from cron job email send function, pdf and export action
		if (!in_array($searchInfo['doc_type'], array("pdf", "export")) && !$cronUserId) {

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
			
			$websiteIdList = array();
			foreach ($baseReportList as $info) {
				$websiteIdList[] = $info['id'];
			}

			$sql = "select w.id,w.name,w.url,r.clicks,r.impressions,r.ctr,r.average_position 
			from websites w, website_search_analytics r where w.id=r.website_id
			and w.status=1 $conditions and r.source='$source' and r.report_date='$toTime'";
			$sql .= " and w.id in(" . implode(",", $websiteIdList) . ")";
			$reportList = $this->db->select($sql);
			$compareReportList = array();
			
			foreach ($reportList as $info) {
				$compareReportList[$info['id']] = $info;	
			}
			
			$this->set('compareReportList', $compareReportList);
			
		}
	
		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$reportHeading =  $this->spTextTools['Website Search Summary']."($fromTime - $toTime)";
			$exportContent .= createExportContent( array());
			$exportContent .= createExportContent( array());
			$exportContent .= createExportContent( array('', $reportHeading, ''));
			$exportContent .= createExportContent( array());
			$headList = array($spText['common']['Website']);
	
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
	
				$valueList = array($listInfo['url']);
				foreach ($this->colList as $colName => $colVal) {
					if ($colName == 'name') continue;
					
					$prevRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
					$currRank = isset($compareReportList[$listInfo['id']][$colName]) ? $compareReportList[$listInfo['id']][$colName] : 0;
					$rankDiff = "";
	
					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $currRank - $prevRank;
						if ($colName == 'average_position') $rankDiff = $rankDiff * -1;
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
				exportToCsv('website_search_summary', $exportContent);
			}
			
		} else {
				
			// if pdf export
			if ($summaryPage) {
				return $this->getViewContent('webmaster/website_search_analytics_summary');
			} else {
				
			    $websiteList = $websiteController->__getAllWebsites($userId, true);
				$this->set('websiteList', $websiteList);
				
				if ($searchInfo['doc_type'] == "pdf") {
					exportToPdf($this->getViewContent('webmaster/website_search_analytics_summary'), "website_search_summary_$fromTime-$toTime.pdf");
				} else {
					$this->set('searchInfo', $searchInfo);
					$this->render('webmaster/website_search_analytics_summary');
				}
			}
			
		}
	}
	
	# func to show website search reports
	function viewWebsiteSearchReports($searchInfo = '') {
	
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = addslashes($searchInfo['from_time']);
		} else {
			$fromTime = date('Y-m-d', strtotime('-17 days'));
		}
	
		if (!empty ($searchInfo['to_time'])) {
			$toTime = addslashes($searchInfo['to_time']);
		} else {
			$toTime = date('Y-m-d', strtotime('-3 days'));
		}
		
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
		
		$source = $this->sourceList[0];
		$this->set('source', $source);
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$conditions = empty($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name from website_search_analytics s,websites w  where s.website_id=w.id
		and report_date >= '$fromTime' and report_date <= '$toTime' and source='$source' $conditions order by report_date";
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
		
		$this->set('list', array_reverse($reportList, true));
		$this->render('webmaster/website_search_reports');
	}
	
	# func to show keyword search reports
	function viewKeywordSearchReports($searchInfo = '') {
	
		$userId = isLoggedIn();

		if (!empty ($searchInfo['from_time'])) {
			$fromTimeDate = addslashes($searchInfo['from_time']);
		} else {
			$fromTimeDate = date('Y-m-d', strtotime('-17 days'));
		}
		
		if (!empty ($searchInfo['to_time'])) {
			$toTimeDate = addslashes($searchInfo['to_time']);
		} else {
			$toTimeDate = date('Y-m-d', strtotime('-3 days'));
		}
		
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
	
		if(!empty($searchInfo['keyword_id']) && !empty($searchInfo['rep'])){				
			$searchInfo['keyword_id'] = intval($searchInfo['keyword_id']);
			$keywordInfo = $this->__getWebmasterKeywordInfo($searchInfo['keyword_id']);
			$searchInfo['website_id'] = $keywordInfo['website_id'];
		}
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$keywordList = $this->__getWebmasterKeywords("website_id=$websiteId  and status=1 order by name");
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);
	
		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$sql = "select s.* from keyword_analytics s
		where report_date>='$fromTimeDate' and report_date<='$toTimeDate' $conditions
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
		
		$this->set('list', array_reverse($reportList, true));				
		$this->render('webmaster/keyword_search_reports');
		
	}
	
	# func to show keyword search reports in graph
	function viewKeywordSearchGraphReports($searchInfo = '') {
	
		$userId = isLoggedIn();

		if (!empty ($searchInfo['from_time'])) {
			$fromTimeDate = addslashes($searchInfo['from_time']);
		} else {
			$fromTimeDate = date('Y-m-d', strtotime('-17 days'));
		}
		
		if (!empty ($searchInfo['to_time'])) {
			$toTimeDate = addslashes($searchInfo['to_time']);
		} else {
			$toTimeDate = date('Y-m-d', strtotime('-3 days'));
		}
		
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
	
		if(!empty($searchInfo['keyword_id']) && !empty($searchInfo['rep'])){				
			$searchInfo['keyword_id'] = intval($searchInfo['keyword_id']);
			$keywordInfo = $this->__getWebmasterKeywordInfo($searchInfo['keyword_id']);
			$searchInfo['website_id'] = $keywordInfo['website_id'];
		}
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$keywordList = $this->__getWebmasterKeywords("website_id=$websiteId  and status=1 order by name");
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);
	
		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$sql = "select s.* from keyword_analytics s
		where report_date>='$fromTimeDate' and report_date<='$toTimeDate' $conditions
		order by s.report_date";
		$reportList = $this->db->select($sql);

		// if reports not empty
		$colList = $this->colList;
		array_shift($colList);
		$this->set('colList', $colList);
		$this->set('searchInfo', $searchInfo);
		
		$graphColList = array();
		if (!empty($searchInfo['attr_type'])) {
			$graphColList[$searchInfo['attr_type']] = $colList[$searchInfo['attr_type']];
			if ($searchInfo['attr_type'] == 'average_position') { $this->set('reverseDir', true);}
		} else {
			array_pop($colList);
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
			$this->set('graphTitle', $this->spTextTools['Keyword Search Reports']);
			$graphContent = $this->getViewContent('report/graph');
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
		
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('webmaster/graphicalreport');
		
	}

	# func to show website search reports in graph
	function viewWebsiteSearchGraphReports($searchInfo = '') {
	
		$userId = isLoggedIn();
	
		if (!empty ($searchInfo['from_time'])) {
			$fromTimeDate = addslashes($searchInfo['from_time']);
		} else {
			$fromTimeDate = date('Y-m-d', strtotime('-17 days'));
		}
	
		if (!empty ($searchInfo['to_time'])) {
			$toTimeDate = addslashes($searchInfo['to_time']);
		} else {
			$toTimeDate = date('Y-m-d', strtotime('-3 days'));
		}
	
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* from website_search_analytics s
		where report_date>='$fromTimeDate' and report_date<='$toTimeDate' $conditions
		order by s.report_date";
		$reportList = $this->db->select($sql);
	
		// if reports not empty
		$colList = $this->colList;
		array_shift($colList);
		$this->set('colList', $colList);
		$this->set('searchInfo', $searchInfo);
		
		$graphColList = array();
		if (!empty($searchInfo['attr_type'])) {
			$graphColList[$searchInfo['attr_type']] = $colList[$searchInfo['attr_type']];
			if ($searchInfo['attr_type'] == 'average_position') { $this->set('reverseDir', true);}
		} else {
			array_pop($colList);
			$graphColList = $colList;	
		}
		
		// format report list
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
			$this->set('graphTitle', $this->spTextTools['Website Search Reports']);
			$graphContent = $this->getViewContent('report/graph');
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
	
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('webmaster/website_graphical_report');
	
	}

	# func to show quick checker
	function viewQuickChecker($keywordInfo='') {	
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		$this->set('fromTime', date('Y-m-d', strtotime('-4 days')));
		$this->set('toTime', date('Y-m-d', strtotime('-3 days')));
		$this->render('webmaster/quick_checker');
	}

	# func to do quick report
	function doQuickChecker($searchInfo = '') {
	
		if (!empty($searchInfo['website_id'])) {
			$websiteId = intval($searchInfo['website_id']);
			$websiteController = New WebsiteController();
			$websiteInfo = $websiteController->__getWebsiteInfo($websiteId);
			$this->set('websiteInfo', $websiteInfo);			
			
			if (!empty($websiteInfo['url'])) {
				$reportStartDate = !empty($searchInfo['from_time']) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-10 days'));
				$reportEndDate = !empty($searchInfo['to_time']) ? $searchInfo['to_time'] : date('Y-m-d', strtotime('-3 days'));
				
				// store website analytics
				$paramList = array(
					'startDate' => $reportStartDate,
					'endDate' => $reportEndDate,
				);
				
				// query results from api and verify no error occured
				$result = $this->getQueryResults($websiteInfo['user_id'], $websiteInfo['url'], $paramList);
					
				// if status is success
				if ($result['status']) {
					$reportInfo = !empty($result['resultList'][0]) ? $result['resultList'][0] : array();
					$websiteReport = array(
						'clicks' => !empty($reportInfo->clicks) ? $reportInfo->clicks : 0,
						'impressions' => !empty($reportInfo->impressions) ? $reportInfo->impressions : 0,
						'ctr' => !empty($reportInfo->ctr) ? $reportInfo->ctr * 100 : 0,
						'average_position' => !empty($reportInfo->position) ? $reportInfo->position : 0,
						'source' => $source,
					);
					
					$this->set('websiteReport', $websiteReport);
					
					// find keyword reports
					$paramList = array(
						'startDate' => $reportStartDate,
						'endDate' => $reportEndDate,
						'dimensions' => ['query'],
					);
						
					// query results from api and verify no error occured
					$result = $this->getQueryResults($websiteInfo['user_id'], $websiteInfo['url'], $paramList);
					if ($result['status']) {
					
						$keywordAnalytics = array();
						foreach ($result['resultList'] as $resInfo) {
							$keywordAnalytics[$resInfo['keys'][0]] = $resInfo;
						}
						
						$this->set('keywordAnalytics', $keywordAnalytics);
					}
					
					$this->set('searchInfo', $searchInfo);
					$this->render('webmaster/quick_checker_results');
					return true;
					
				}
			}
		} 
		
		$errorMsg = !empty($result['msg']) ? $result['msg'] : "Internal error occured while accessing webmaster tools."; 
		showErrorMsg($errorMsg);
		
	}

	# func to show keyword select box
	function showKeywordSelectBox($websiteId, $keywordId = ""){
		$websiteId = intval($websiteId);
		$this->set('keywordList', $this->__getWebmasterKeywords("website_id=$websiteId and status=1 order by name"));
		$this->set('keywordId', $keywordId);
		$this->render('keyword/keywordselectbox');
	}
	
}
?>