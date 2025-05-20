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

include_once(SP_LIBPATH . "/analyticsdata/vendor/autoload.php");
include_once(SP_CTRLPATH . "/user-token.ctrl.php");

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\Dimension;

// class defines all google analytics api controller functions
class AnalyticsController extends Controller {
	
    var $spTextGA;
    var $metrics;
	var $metricList;
	var $defaultMetricName = "users";
	var $dimensionName = "sourceMedium";
	var $sourceTypeList = ['referral', 'direct', 'organic', 'cpc', 'social'];
	var $socialNetworkList;
	var $totalLabel = "total";
	var $tokenCtrler;
	var $sourceName = 'google';
	
	function __construct() {
	    parent::__construct();
	    $this->spTextGA = $this->getLanguageTexts('analytics', $_SESSION['lang_code']);
	    $this->set('spTextGA', $this->spTextGA);
	    $this->metrics = array(
	        'users' => $this->spTextGA['Users'],
	        'newUsers' => $this->spTextGA['New Users'],
	        'sessions' => $this->spTextGA['Sessions'],
	        'bounceRate' => $this->spTextGA['Bounce Rate'],
	        'avgSessionDuration' => $this->spTextGA['Avg. Session Duration'],
	        'goalCompletionsAll' => $this->spTextGA['Goal Completions'],
	    );
	    
	    $this->dimensions = array(
	        'sourceMedium' => "Source",
	        'pagePath' => "Page",
	        'country' => "Country",
	        /*'socialNetwork' => "Social Network",*/
	        'deviceCategory' => "Device",
	        'language' => "Language",
	        'browser' => "Browser",
	        'operatingSystem' => "Operating System",
	    );
	    
	    $this->set('dimensions', $this->dimensions);
	    $this->set('metricColList', $this->metrics);
	    $this->metricList = array_keys($this->metrics);
	}
	
	/**
	 * function to get google analytics GA4 auth client
	 */
	function getGoogleAnalyticsGA4AuthClient($userId) {
	    // get user token info
	    $tokenCtrler = new UserTokenController();
	    $tokenInfo = $tokenCtrler->getUserToken($userId, $this->sourceName);
	    
	    // if token not set for the user
	    if (empty($tokenInfo['refresh_token'])) {
	        $spTextWebmaster = $this->getLanguageTexts('webmaster', $_SESSION['lang_code']);
	        $errorText = $spTextWebmaster["Error: Google api connection failed"] . ". ";
	        $errorText .= "<a href='".SP_WEBPATH ."/admin-panel.php?sec=connections' target='_blank'>{$spTextWebmaster['Click here to connect to your google account']}.</a>";
	        $alertCtler = new AlertController();
	        $alertInfo = array(
	            'alert_subject' => $spTextWebmaster['Click here to connect to your google account'],
	            'alert_message' => $spTextWebmaster["Error: Google api connection failed"],
	            'alert_url' => SP_WEBPATH ."/admin-panel.php?sec=connections",
	            'alert_type' => "danger",
	            'alert_category' => "reports",
	        );
	        $alertCtler->createAlert($alertInfo, $userId);
	        return $errorText;
	    }
	    
	    try {
	        $credentials = new UserRefreshCredentials(['https://www.googleapis.com/auth/analytics.readonly'], [
	            'client_id' => SP_GOOGLE_API_CLIENT_ID,
	            'client_secret' => SP_GOOGLE_API_CLIENT_SECRET,
	            'refresh_token' => $tokenInfo['refresh_token'],
	        ]);
	        
	        // Initialize the client
	        $ga4Client = new BetaAnalyticsDataClient(['credentials' => $credentials]);
	        return $ga4Client;
	    } catch (Exception $e) {
	        $err = $e->getMessage();
	        return "Error: Google analytics GA4 client creation - $err";
	    }
	}
	
	/*
	 * function to get analytics query result
	 */
	function getAnalyticsResults($userId, $propertyId, $startDate, $endDate, $dimensionName="sourceMedium") {
	    $result = array('status' => false);
	    if (empty($propertyId)) {
	        $result['msg'] = $this->spTextGA['view_id_not_found_error'];
	        $alertCtler = new AlertController();
	        $alertInfo = array(
	            'alert_subject' => $this->spTextGA['view_id_not_found_error'],
	            'alert_message' => "",
	            'alert_url' => SP_WEBPATH ."/admin-panel.php",
	            'alert_type' => "danger",
	            'alert_category' => "reports",
	        );
	        $alertCtler->createAlert($alertInfo, $userId);
	        return $result;
	    }
	    
	    try {
	        // create GA4 client
	        $client = $this->getGoogleAnalyticsGA4AuthClient($userId);
	        if (!is_object($client)) {
	            $result['msg'] = $client;
	            return $result;
	        }
	        
	        // Define the date range for your query
	        $dateRange = new DateRange();
	        $dateRange->setStartDate($startDate);
	        $dateRange->setEndDate($endDate);
	        
	        // create the dimension.
	        $dimensionName = !empty($dimensionName) ? $dimensionName : $this->dimensionName;
	        $dimension = [
	            new Dimension(['name' => self::getActualQueryDimensionName($dimensionName)]),
	        ];
	        
	        $metricObjList = [];
	        foreach ($this->metricList as $metricName) {
	            $metricObjList[] = new Metric(['name' => self::getActualQueryMetricName($metricName)]);
	        }
	        
	        // Make the request
	        $resultList = [];
	        $response = $client->runReport([
	            'property' => 'properties/' . $propertyId,
	            'dateRanges' => [$dateRange],
	            'dimensions' => $dimension,
	            'metrics' => $metricObjList,
	        ]);
	        
	        // Extract and print the results
	        $rows = $response->getRows();
	        foreach ($rows as $row) {
	            $dimensionValues = $row->getDimensionValues();
	            $metricValues = $row->getMetricValues();
	            
	            $dimensionVal = $dimensionValues[0]->getValue();
	            if (!empty($dimensionVal)) {
	                $metricData = [];
	                $i = 0;
	                foreach ($this->metricList as $metricName) {
	                    $metricDataVal = $metricValues[$i]->getValue();
	                    $metricData[$metricName] = self::formatMetricValue($metricDataVal, $metricName);
	                    $i++;
	                }
	                
	                $resultList[$dimensionVal] = $metricData;
	            }
	        }
	        
	        $result['status'] = true;
	        $result['resultList'] = $resultList;
	    } catch (Exception $e) {
	        $err = $e->getMessage();
	        $result['msg'] = "Error: search query analytics - $err";
	    }
	    
	    return $result;
	}
	
	public static function getActualQueryMetricName($metricName) {
	    $queryMetricName = $metricName;
	    if ($metricName == "users") {
	        $queryMetricName = "activeUsers";
	    }
	    
	    if ($metricName == "avgSessionDuration") {
	        $queryMetricName = "averageSessionDuration";
	    }
	    
	    if ($metricName == "goalCompletionsAll") {
	        $queryMetricName = "conversions";
	    }
	    
	    return $queryMetricName;
	}
	
	public static function getActualQueryDimensionName($dimensionName) {
	    $queryDimensionName = $dimensionName;
	    if ($dimensionName == "sourceMedium") {
	        $queryDimensionName = "sessionSourceMedium";
	    }
	    
	    return $queryDimensionName;
	}
	
	public static function formatMetricValue($value, $metricName) {
	    if (in_array($metricName, ["bounceRate", "goalCompletionsAll"])) {
	        $value = round($value, 2);
	    }
	    
	    if ($metricName == "avgSessionDuration") {
	        $value = round(($value/60), 2);
	    }
	    
	    return $value;
	}
	
	function getAnalyticsSourceList() {
	    $sourceList = [];
	    $list = $this->dbHelper->getAllRows("analytic_sources");
	    foreach ($list as $listInfo) {
	        $sourceList[$listInfo['source_name']] = $listInfo['id'];
	    }
	    
	    return $sourceList;
	}
	
	function generateSource($sourceName) {
	    $sourceId = false;
	    if ($this->dbHelper->insertRow("analytic_sources", array("source_name" => $sourceName))) {
	        $sourceId = $this->db->getMaxId("analytic_sources");
	    }
	    
	    return $sourceId;
	}
	
	/*
	 * function to store website results
	 */
	function storeWebsiteAnalytics($websiteId, $reportDate) {
		$websiteId = intval($websiteId);
		$websiteCtrler = new WebsiteController();
		$websiteInfo = $websiteCtrler->__getWebsiteInfo($websiteId);
		
		// query results from api and verify no error occured
		$result = $this->getAnalyticsResults($websiteInfo['user_id'], $websiteInfo['analytics_view_id'], $reportDate, $reportDate);
		if ($result['status']) {
		    $sourceList = $this->getAnalyticsSourceList();
				
			// loop through the result list
			foreach ($result['resultList'] as $sourceName => $reportInfo) {
			    
			    // generate source list, if not set it yet
			    if (!isset($sourceList[$sourceName])) {
			        $sourceId = $this->generateSource($sourceName);
			    } else {
			        $sourceId = $sourceList[$sourceName];
			    }
	
			    if (!empty($sourceId)) {
			        $this->insertWebsiteAnalytics($websiteId, $sourceId, $reportInfo, $reportDate);
			    } else {
			        $result['msg'] .= "Error: Analytics source id not found";
			    }
			}
		}
	
		return $result;
	
	}
	
	/*
	 * function to insert website analytics
	 */
	function insertWebsiteAnalytics($websiteId, $sourceId, $reportInfo, $resultDate, $clearExisting = true) {
		$websiteId = intval($websiteId);
		$sourceId = intval($sourceId);
		$resultDate = addslashes($resultDate);
	
		if ($clearExisting) {
			$whereCond = "website_id=$websiteId and report_date='$resultDate' and source_id='$sourceId'";
			$this->dbHelper->deleteRows('website_analytics', $whereCond);
		}
		
		$reportInfo['website_id'] = $websiteId;
		$reportInfo['source_id'] = $sourceId;
		$reportInfo['report_date'] = $resultDate;
		$this->dbHelper->insertRow('website_analytics', $reportInfo);
	}

	// func to show quick checker
	function viewQuickChecker($searchInfo='') {	
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		$this->set('fromTime', date('Y-m-d', strtotime('-1 days')));
		$this->set('toTime', date('Y-m-d'));
		$this->render('analytics/quick_checker');
	}

	// func to do quick report
	function doQuickChecker($searchInfo=[]) {
	    
		if (!empty($searchInfo['website_id'])) {
			$websiteId = intval($searchInfo['website_id']);
			$websiteController = New WebsiteController();
			$websiteInfo = $websiteController->__getWebsiteInfo($websiteId);
			$this->set('websiteInfo', $websiteInfo);			
			
			if (!empty($websiteInfo['url'])) {
				$reportStartDate = !empty($searchInfo['from_time']) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-1 days'));
				$reportEndDate = !empty($searchInfo['to_time']) ? $searchInfo['to_time'] : date('Y-m-d');
								
				// query results from api and verify no error occured
				$result = $this->getAnalyticsResults($websiteInfo['user_id'], $websiteInfo['analytics_view_id'], $reportStartDate, $reportEndDate);
				
				// if status is success
				if ($result['status']) {
					$websiteReport = array_shift($result['resultList']);
					$sourceReport = $result['resultList'];
					$this->set('websiteReport', $websiteReport);
					$this->set('sourceReport', $sourceReport);
					
					$this->set('searchInfo', $searchInfo);
					$this->render('analytics/quick_checker_results');
					return true;
				}
			}
		} 
		
		$errorMsg = !empty($result['msg']) ? $result['msg'] : "Internal error occured while accessing webmaster tools."; 
		showErrorMsg($errorMsg);		
	}

	// function check whether analytics reports already saved
	function isReportsExists($websiteId, $resultDate) {
		$websiteId = intval($websiteId);
		$resultDate = addslashes($resultDate);
		$whereCond = "website_id=$websiteId and report_date='$resultDate'";
		$info = $this->dbHelper->getRow("website_analytics", $whereCond, "website_id");
		return !empty($info['website_id']) ? true : false;
	}
	
	# func to show analytics report summary
	function viewAnalyticsSummary($searchInfo = '', $summaryPage = false, $cronUserId=false) {
	    
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
	    
	    $fromTime = !empty($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-2 days'));
	    $toTime = !empty($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d', strtotime('-1 days'));
	    $this->set('fromTime', $fromTime);
	    $this->set('toTime', $toTime);
	    
	    $websiteController = New WebsiteController();
	    $wList = $websiteController->__getAllWebsites($userId, true);
	    $websiteList = [];
	    foreach ($wList as $wInfo) {
	        $websiteList[$wInfo['id']] = $wInfo;
	    }
	    
	    $websiteList = count($websiteList) ? $websiteList : array(0);
	    $this->set('websiteList', $websiteList);
	    $websiteId = intval($searchInfo['website_id']);
	    $this->set('websiteId', $websiteId);
	    
	    // to find order col
	    if (!empty($searchInfo['order_col'])) {
	        $orderCol = $searchInfo['order_col'];
	        $orderVal = getOrderByVal($searchInfo['order_val']);
	    } else {
	        $orderCol = "users";
	        $orderVal = 'DESC';
	    }
	    
	    $this->set('orderCol', $orderCol);
	    $this->set('orderVal', $orderVal);
	    $scriptName = $summaryPage ? "archive.php" : "analytics.php";
	    $scriptPath = SP_WEBPATH . "/$scriptName?sec=viewAnalyticsSummary&website_id=$websiteId";
	    $scriptPath .= "&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
	    $scriptPath .= "&order_col=$orderCol&order_val=$orderVal&report_type=analytics-reports";
	    
	    $conditions = !empty($searchInfo['search_name']) ? " and k.source_name like '%".addslashes($searchInfo['search_name'])."%'" : "";
	    
	    // set website id to get exact keywords of a user
	    if (!empty($websiteId)) {
	        $conditions .= " and r.website_id=$websiteId";
	    } else {
	        $conditions .= " and r.website_id in (".implode(',', array_keys($websiteList)).")";
	    }
	    
	    $analyticsCols = implode(",", array_keys($this->metrics));	    
	    $sql = "select k.id,k.source_name,r.website_id,$analyticsCols 
            from analytic_sources k, website_analytics r 
            where k.id=r.source_id $conditions and r.report_date='$toTime'
            order by " . addslashes($orderCol) . " " . addslashes($orderVal);
	    
	    if ($orderCol != 'users') $sql .= ", users";
	    
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
	        
	        $sourceIdList = array();
	        foreach ($baseReportList as $info) {
	            $sourceIdList[] = $info['id'];
	        }
	        
	        $sql = "select k.id,k.source_name,r.website_id, $analyticsCols
			from analytic_sources k, website_analytics r where k.id=r.source_id
			$conditions and r.report_date='$fromTime'";
	        $sql .= " and k.id in(" . implode(",", $sourceIdList) . ")";
	        $reportList = $this->db->select($sql);
	        $compareReportList = array();
	        
	        foreach ($reportList as $info) {
	            $compareReportList[$info['website_id']][$info['id']] = $info;
	        }
	        
	        $this->set('compareReportList', $compareReportList);
	    }
	    
	    if ($exportVersion) {
	        $spText = $_SESSION['text'];
	        $reportHeading =  $this->spTextTools['Website Analytics Summary']."($fromTime - $toTime)";
	        $exportContent .= createExportContent( array('', $reportHeading, ''));
	        $exportContent .= createExportContent( array());
	        $headList = array($spText['common']['Website'], $spText['common']['Source']);
	        
	        $pTxt = str_replace("-", "/", substr($fromTime, -5));
	        $cTxt = str_replace("-", "/", substr($toTime, -5));
	        foreach ($this->metrics as $colKey => $colLabel) {
	            if ($colKey == 'name') continue;
	            $headList[] = $colLabel . "($pTxt)";
	            $headList[] = $colLabel . "($cTxt)";
	            $headList[] = $colLabel . "(+/-)";
	        }
	        
	        $exportContent .= createExportContent($headList);
	        foreach($baseReportList as $listInfo){
	            
	            $valueList = array($websiteList[$listInfo['website_id']]['url'], $listInfo['source_name']);
	            foreach ($this->metrics as $colName => $colVal) {
	                if ($colName == 'name') continue;
	                
	                $currRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
	                $prevRank = isset($compareReportList[$listInfo['website_id']][$listInfo['id']][$colName]) ? $compareReportList[$listInfo['website_id']][$listInfo['id']][$colName] : 0;
	                $rankDiff = "";
	                
	                // if both ranks are existing
	                if ($prevRank != '' && $currRank != '') {
	                    $rankDiff = $currRank - $prevRank;
	                    if ($colName == 'bounceRate') $rankDiff = $rankDiff * -1;
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
	            exportToCsv('analytics_summary', $exportContent);
	        }
	        
	    } else {
	        
	        // if pdf export
	        if ($summaryPage) {
	            return $this->getViewContent('analytics/analytics_summary');
	        } else {
	            // if pdf export
	            if ($searchInfo['doc_type'] == "pdf") {
	                exportToPdf($this->getViewContent('analytics/analytics_summary'), "analytics_summary_$fromTime-$toTime.pdf");
	            } else {
	                $this->set('searchInfo', $searchInfo);
	                $this->render('analytics/analytics_summary');
	            }
	        }
	        
	    }
	}
	
	function __getWebsiteSourceList($websiteId) {
	    $websiteId = intval($websiteId);
	    $sql = "select * from analytic_sources where 
            id in (select distinct source_id from website_analytics where website_id=$websiteId)
            order by source_name";
	    $sourceList = $this->db->select($sql);
	    return $sourceList;
	}
	
	// func to show analytics reports
	function viewAnalyticsReports($searchInfo = '') {
	    
	    $userId = isLoggedIn();
	    
	    if (!empty ($searchInfo['from_time'])) {
	        $fromTimeDate = addslashes($searchInfo['from_time']);
	    } else {
	        $fromTimeDate = date('Y-m-d', strtotime('-17 days'));
	    }
	    
	    if (!empty ($searchInfo['to_time'])) {
	        $toTimeDate = addslashes($searchInfo['to_time']);
	    } else {
	        $toTimeDate = date('Y-m-d', strtotime('-1 days'));
	    }
	    
	    $this->set('fromTime', $fromTimeDate);
	    $this->set('toTime', $toTimeDate);
	    
	    $websiteController = New WebsiteController();
	    $websiteList = $websiteController->__getAllWebsites($userId, true);
	    $this->set('websiteList', $websiteList);
	    $websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
	    $this->set('websiteId', $websiteId);
	    
	    $sourceList = $this->__getWebsiteSourceList($websiteId);
	    $this->set('sourceList', $sourceList);
	    $sourceId = empty ($searchInfo['source_id']) ? $sourceList[0]['id'] : $searchInfo['source_id'];
	    $this->set('sourceId', $sourceId);
	    
	    $conditions = " and s.website_id=$websiteId";
	    $conditions .= empty ($sourceId) ? "" : " and s.source_id=$sourceId";
	    $sql = "select s.* from website_analytics s
		  where report_date>='$fromTimeDate' and report_date<='$toTimeDate' $conditions
		  order by s.report_date";
	    $reportList = $this->db->select($sql);
	    
	    $colList = array_keys($this->metrics);
	    $prevRank = [];
	    $rankDiff = [];
	    foreach ($colList as $col) {
	        $prevRank[$col] = 0;
	    }
	    
	    // loop through rank
	    foreach ($reportList as $key => $repInfo) {
	        
	        // exclude first row
	        if ($key) {
                foreach ($colList as $col) {
                    $rankDiff[$col] = '';
                }
           
                foreach ($colList as $col) {
                    $rankDiff[$col] = round($repInfo[$col] - $prevRank[$col], 2);
                    if (empty($rankDiff[$col])) {
                        continue;
                    }
                    
                    if ($col == "bounceRate" ) {
                        $rankDiff[$col] = $rankDiff[$col] * -1;
                    }
                    
                    $rankClass = ($rankDiff[$col] > 0) ? 'green' : 'red';
                    $rankDiff[$col] = "<font class='$rankClass'>($rankDiff[$col])</font>";
                    $reportList[$key]['rank_diff_'.$col] = empty($rankDiff[$col]) ? '' : $rankDiff[$col];
                }
            }
	        
	        foreach ($colList as $col) {
	            $prevRank[$col] = $repInfo[$col];
	        }	        
	    }
	    
	    $this->set('list', array_reverse($reportList, true));
	    $this->render('analytics/analytics_reports');
	    
	}
	
	// func to show analytics reports in graph
	function viewAnalyticsGraphReports($searchInfo = '') {
	    $userId = isLoggedIn();
	    
	    if (!empty ($searchInfo['from_time'])) {
	        $fromTimeDate = addslashes($searchInfo['from_time']);
	    } else {
	        $fromTimeDate = date('Y-m-d', strtotime('-17 days'));
	    }
	    
	    if (!empty ($searchInfo['to_time'])) {
	        $toTimeDate = addslashes($searchInfo['to_time']);
	    } else {
	        $toTimeDate = date('Y-m-d', strtotime('-1 days'));
	    }
	    
	    $this->set('fromTime', $fromTimeDate);
	    $this->set('toTime', $toTimeDate);
	    
	    $websiteController = New WebsiteController();
	    $websiteList = $websiteController->__getAllWebsites($userId, true);
	    $this->set('websiteList', $websiteList);
	    $websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
	    $this->set('websiteId', $websiteId);
	    
	    $sourceList = $this->__getWebsiteSourceList($websiteId);
	    $this->set('sourceList', $sourceList);
	    $sourceId = empty ($searchInfo['source_id']) ? $sourceList[0]['id'] : $searchInfo['source_id'];
	    $this->set('sourceId', $sourceId);
	    
	    $conditions = " and s.website_id=$websiteId";
	    $conditions .= empty ($sourceId) ? "" : " and s.source_id=$sourceId";
	    $sql = "select s.* from website_analytics s
		  where report_date>='$fromTimeDate' and report_date<='$toTimeDate' $conditions
		  order by s.report_date";
	    $reportList = $this->db->select($sql);
	    
	    // if reports not empty
	    $colList = $this->metrics;
	    $this->set('colList', $colList);
	    $this->set('searchInfo', $searchInfo);
	    
	    $graphColList = array();
	    if (!empty($searchInfo['attr_type'])) {
	        $graphColList[$searchInfo['attr_type']] = $colList[$searchInfo['attr_type']];
	        if ($searchInfo['attr_type'] == 'bounceRate') { 
	            $this->set('reverseDir', true);
	        }
	    } else {
	        $graphColList = $colList;
	        unset($graphColList['bounceRate']);
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
	        $this->set('graphTitle', $this->spTextTools['Website Analytics Summary']);
	        $graphContent = $this->getViewContent('report/graph');
	    } else {
	        $graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
	    }
	    
	    // get graph content
	    $this->set('graphContent', $graphContent);
	    $this->render('analytics/graphicalreport');
	    
	}
	
	// func to show keyword select box
	function showSourceSelectBox($websiteId){
	    $websiteId = intval($websiteId);
	    $this->set('sourceList', $this->__getWebsiteSourceList($websiteId));
	    $this->render('analytics/source_select_box', 'ajax');
	}
	
}
?>