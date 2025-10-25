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

# class defines all pagespeed api controller functions
class PageSpeedController extends Controller{
	

	var $colList = array(
		'desktop_speed_score' => 'desktop_speed_score',
		'mobile_speed_score' => 'mobile_speed_score',
	);
	
	// function to get moz rank
	function __getPageSpeedInfo ($url, $params = array(), $apiKey = '', $returnLog = false) {
		
		include_once(SP_LIBPATH . "/google-api-php-client/vendor/autoload.php");
		$pageSpeedInfo = array();
		$crawlInfo = array();
		
		$apiKey = !empty($apiKey) ? $apiKey : SP_GOOGLE_API_KEY;
		
		// if empty no need to crawl
		if (!empty($apiKey)) {
		
			$client = new Google_Client();
			$client->setApplicationName("SP_CHECKER");		
			$client->setDeveloperKey($apiKey);
			
			try {
				
				// split and select main language if sub language selected
				if (stristr($params['locale'], '-')) {
					list($params['locale'], $tmpVar) = explode('-', $params['locale']);
				}
				
				$service = new Google_Service_Pagespeedonline($client);
				$pageSpeedInfo = $service->pagespeedapi->runpagespeed($url, $params);
				$pageSpeedInfo = self::formatPageSpeedData($pageSpeedInfo);
			} catch (Exception $e) {
				$err = $e->getMessage();
				$errData = json_decode($err);
				$crawlInfo['crawl_status'] = 0;
				$crawlInfo['log_message'] = $_SESSION['text']['label']['Fail'];
				$crawlInfo['log_message'] .= !empty($errData->error->errors[0]->reason) ? ": " . $errData->error->errors[0]->reason . " :: " . $errData->error->errors[0]->message : "";
			}
			
		} else {
			$crawlInfo['crawl_status'] = 0;
			$crawlInfo['log_message'] = "Google api key not set.";

			$alertCtler = new AlertController();
			$alertInfo = array(
				'alert_subject' => "Click here to enter Google API key",
				'alert_message' => "Error: Google API key not found",
				'alert_url' => SP_WEBPATH ."/admin-panel.php?sec=google-settings",
				'alert_type' => "danger",
				'alert_category' => "reports",
			);
			$alertCtler->createAlert($alertInfo, false, true);
			
		}
		
		return $returnLog ? array($pageSpeedInfo, $crawlInfo) : $pageSpeedInfo;
		
	}
	
	public static function formatPageSpeedData($pageSpeedInfo) {

		$pageSpeedData = array(
			'speed_score' => !empty($pageSpeedInfo['lighthouseResult']['categories']['performance']['score']) ? $pageSpeedInfo['lighthouseResult']['categories']['performance']['score'] * 100 : 0,
			'usability_score' => !empty($pageSpeedInfo['lighthouseResult']['categories']['performance']['score']) ? $pageSpeedInfo['lighthouseResult']['categories']['performance']['score'] * 100 : 0,
		);
		
		$detailsInfo = array();
		
		// commented for API v5 version
		/*foreach ($pageSpeedInfo['formattedResults']['ruleResults'] as $ruleSet => $ruleSetInfo) {
		
			$detailsInfo[$ruleSet] = array(
				'localizedRuleName' => $ruleSetInfo['localizedRuleName'],
				'ruleImpact' => $ruleSetInfo['ruleImpact'],
				'impactGroup' => implode(',', $ruleSetInfo['groups']),
				'summary' => self::formatSummaryText($ruleSetInfo['summary']),
				'urlBlocks' => self::formatUrlBlock($ruleSetInfo['urlBlocks']),
			);		
		
		}*/
		
		$pageSpeedData['details'] = $detailsInfo;
		return $pageSpeedData;
		
	}
	
	public static function formatUrlBlock($urlBlockList) {
		$urlList = array();
	
		foreach ($urlBlockList as $urlBlockInfo) {
			$info['header'] = self::formatSummaryText($urlBlockInfo['header']);
			$info['urls'] = array();
				
			foreach ($urlBlockInfo['urls'] as $urlInfo) {
				$info['urls'][] = self::formatSummaryText($urlInfo['result']);
			}
				
			$urlList[] = $info;
				
		}
	
		return $urlList;
	
	}
	
	public static function formatSummaryText($summaryInfo) {
	
		$formatTxt = $summaryInfo['format'];
	
		// loop through arg information list
		foreach ($summaryInfo['args'] as $argInfo) {
				
			switch ($argInfo['type']) {
	
				case "HYPERLINK":
					$formatTxt = str_replace('{{BEGIN_LINK}}', "<a href='{$argInfo['value']}' target='_blank'>", $formatTxt);
					$formatTxt = str_replace('{{END_LINK}}', "</a>", $formatTxt);
					break;
					
				case "URL":
					$formatTxt = str_replace('{{' . $argInfo['key'] . '}}', "<a>{$argInfo['value']}</a>" , $formatTxt);
					break;
	
				default:
					$formatTxt = str_replace('{{' . $argInfo['key'] . '}}', $argInfo['value'], $formatTxt);
					break;
	
			}
				
		}
	
		return $formatTxt;
	
	}	
	
	// function to show pagespeed checker
	function showQuickChecker() {
		$this->render('pagespeed/showquickchecker');
	}
	
	function findPageSpeedInfo($searchInfo) {
		
		// check google api setup
		SettingsController::showCheckCategorySettings('google', true);
		
		$urlList = explode("\n", $searchInfo['website_urls']);
		$list = array();
		$reportList = array();
		
		$i = 1;
		foreach ($urlList as $url) {
			$url = sanitizeData($url);
			if(!preg_match('/\w+/', $url)) continue;
			if ($i++ > 5) break;
			$url = addHttpToUrl($url);
			$list[] = str_replace(array("\n", "\r", "\r\n", "\n\r"), "", trim($url));
		}
		
		// loop through the list
		foreach ($list as $url) {
			$reportList[$url] = array();
			$params = array('screenshot' => false, 'strategy' => 'desktop', 'locale' => $_SESSION['lang_code']);
			$reportList[$url]['desktop'] = $this->__getPageSpeedInfo($url, $params);
			$params = array('screenshot' => false, 'strategy' => 'mobile', 'locale' => $_SESSION['lang_code']);
			$reportList[$url]['mobile'] = $this->__getPageSpeedInfo($url, $params);
		}
		
		$this->set('reportList', $reportList);
		$this->set('list', $list);
		$this->render('pagespeed/findpagespeedinfo');
		
	}

	# func to show reports
	function showReports($searchInfo = '') {
	
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = $searchInfo['from_time'];
		} else {
			$fromTime = date('Y-m-d', strtotime('-30 days'));
		}
	
		if (!empty ($searchInfo['to_time'])) {
			$toTime = $searchInfo['to_time'];
		} else {
			$toTime = date('Y-m-d');
		}
	
		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval( $searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name from pagespeedresults s,websites w where s.website_id=w.id
		and result_date >= '$fromTime' and result_date <= '$toTime' $conditions order by result_date";
		$reportList = $this->db->select($sql);
	
		$i = 0;
		$colList = $this->colList;
		foreach ($colList as $col => $dbCol) {
			$prevRank[$col] = 0;
		}
	
		# loop throgh rank
		foreach ($reportList as $key => $repInfo) {
			
			foreach ($colList as $col => $dbCol) {
				$rankDiff[$col] = '';
			}
				
			foreach ($colList as $col => $dbCol) {
				if ($i > 0) {
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * -1;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='green'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='red'>($rankDiff[$col])</font>";
					}
				}
				$reportList[$key]['rank_diff_'.$col] = empty ($rankDiff[$col]) ? '' : $rankDiff[$col];
			}
				
			foreach ($colList as $col => $dbCol) {
				$prevRank[$col] = $repInfo[$dbCol];
			}
				
			$i++;
			
		}
		
		$detailsInfo = $this->dbHelper->getRow("pagespeeddetails", "website_id=$websiteId");
		$this->set('detailsInfo', $detailsInfo);
		
		$this->set('fromPopUp', $searchInfo['fromPopUp']);
		$this->set('list', array_reverse($reportList, true));
		$this->render('pagespeed/pagespeedreport');
	}
	
	# func to get backlink report for a website
	function __getWebsitePageSpeedReport($websiteId, $fromTime, $toTime) {
	
		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name 	from pagespeedresults s,websites w
		where s.website_id=w.id" . $conditions . "
		and (result_date='$fromTimeLabel' or result_date='$toTimeLabel')
		order by result_date DESC Limit 0,2";
		$reportList = $this->db->select($sql);
		$reportList = array_reverse($reportList);
	
		$i = 0;
		$colList = $this->colList;
		foreach ($colList as $col => $dbCol) {
			$prevRank[$col] = 0;
		}
	
		# loop throgh rank
		foreach ($reportList as $key => $repInfo) {
			foreach ($colList as $col => $dbCol) {
				$rankDiff[$col] = '';
			}
				
			foreach ($colList as $col => $dbCol) {
				if ($i > 0) {
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * -1;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='green'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='red'>($rankDiff[$col])</font>";
					}
				}
				$reportList[$key]['rank_diff_'.$col] = empty ($rankDiff[$col]) ? '' : $rankDiff[$col];
			}
				
			foreach ($colList as $col => $dbCol) {
				$prevRank[$col] = $repInfo[$dbCol];
			}
				
			$i++;
		}
	
		$reportList = array_reverse(array_slice($reportList, count($reportList) - 1));
		return $reportList;
	}
	
	# func to show graphical reports
	function showGraphicalReports($searchInfo = '') {	
		$userId = isLoggedIn();
		$fromTime = !empty($searchInfo['from_time']) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty ($searchInfo['to_time']) ? $searchInfo['to_time'] : date("Y-m-d");
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
	
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name from pagespeedresults s,websites w where s.website_id=w.id
		and result_date >= '$fromTime' and result_date <= '$toTime' $conditions order by result_date";
		$reportList = $this->db->select($sql);
	
		// if reports not empty
		$colList = $this->colList;
		if (!empty($reportList)) {
			
			$colLableList = array($this->spTextPS['Desktop Speed'], $this->spTextPS['Mobile Speed']);
			$dataArr = "['Date', '" . implode("', '", $colLableList) . "']";
	
			// loop through data list
			foreach ($reportList as $dataInfo) {
	
				$valStr = "";
				foreach ($colList as $seId => $seVal) {
					$valStr .= ", ";
					$valStr .= !empty($dataInfo[$seId])    ? $dataInfo[$seId] : 0;
				}
	
				$dataArr .= ", ['{$dataInfo['result_date']}' $valStr]";
			}
	
			$this->set('dataArr', $dataArr);
			$this->set('graphTitle', $this->spTextTools['Backlinks Reports']);
			$graphContent = $this->getViewContent('report/graph');
	
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
	
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('pagespeed/graphicalreport');
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
		
		// check google api setup
		SettingsController::showCheckCategorySettings('google', true);
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->render('pagespeed/generatereport');
		
	}

	# func to generate reports
	function generateReports( $searchInfo='' ) {
		$userId = isLoggedIn();
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
	
		$sql = "select id,url from websites where status=1";
		if(!empty($userId) && !isAdmin()) $sql .= " and user_id=$userId";
		if(!empty($websiteId)) $sql .= " and id=$websiteId";
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);
	
		if(count($websiteList) <= 0){
			echo "<p class='note'>".$_SESSION['text']['common']['nowebsites']."!</p>";
			exit;
		}
	
		# loop through each websites
		foreach ( $websiteList as $websiteInfo ) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			
			$params = array('screenshot' => false, 'strategy' => 'desktop', 'locale' => $_SESSION['lang_code']);
			$websiteInfo['desktop'] = $this->__getPageSpeedInfo($websiteUrl, $params);
			$params = array('screenshot' => false, 'strategy' => 'mobile', 'locale' => $_SESSION['lang_code']);
			$websiteInfo['mobile'] = $this->__getPageSpeedInfo($websiteUrl, $params);
				
			$this->savePageSpeedResults($websiteInfo, true);
			
			echo "<p class='note notesuccess'>".$this->spTextPS['Saved page speed results of']." <b>$websiteUrl</b>.....</p>";
		}
		
	}

	# function to save rank details
	function savePageSpeedResults($matchInfo, $remove=false) {
		$resultDate = date('Y-m-d');
	
		if($remove){
			$sql = "delete from pagespeedresults where website_id={$matchInfo['id']} and result_date='$resultDate'";
			$this->db->query($sql);
			$sql = "delete from pagespeeddetails where website_id={$matchInfo['id']}";
			$this->db->query($sql);
		}
	
		$matchInfo['id'] = intval($matchInfo['id']);
		$matchInfo['desktop']['speed_score'] = intval($matchInfo['desktop']['speed_score']);
		$matchInfo['mobile']['speed_score'] = intval($matchInfo['mobile']['speed_score']);
		$matchInfo['mobile']['usability_score'] = intval($matchInfo['mobile']['usability_score']);
		
		$sql = "insert into pagespeedresults(website_id, desktop_speed_score, mobile_speed_score, mobile_usability_score, result_date)
		values({$matchInfo['id']},{$matchInfo['desktop']['speed_score']},{$matchInfo['mobile']['speed_score']},{$matchInfo['mobile']['usability_score']}, '$resultDate')";
		$this->db->query($sql);
		
		$sql = "insert into pagespeeddetails(website_id, desktop_score_details, mobile_score_details, result_date)
		values({$matchInfo['id']},'" . addslashes(serialize($matchInfo['desktop']['details'])) . "',
		'" . addslashes(serialize($matchInfo['mobile']['details'])) . "', '$resultDate')";
		$this->db->query($sql);
	
	}
	
	# function check whether reports already saved
	function isReportsExists($websiteId, $time) {
		$resultDate = date('Y-m-d', $time);
		$sql = "select website_id from pagespeedresults where website_id=$websiteId and result_date='$resultDate'";
		$info = $this->db->select($sql, true);
		return empty($info['website_id']) ? false : true;
	}
	
}
?>