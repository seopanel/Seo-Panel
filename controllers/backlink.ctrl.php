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

# class defines all backlink controller functions
class BacklinkController extends Controller{
	var $url;
	var $colList = array('google' => 'google', 'msn' => 'bing');
	var $backUrlList = array(
		'google' => 'http://www.google.com/search?hl=en&q=link%3A',
		'msn' => 'http://www.bing.com/search?q=link%3A',
	);
	
	function showBacklink() {
		$this->render('backlink/showbacklink');
	}
	
	function findBacklink($searchInfo) {
		$urlList = explode("\n", $searchInfo['website_urls']);
		$list = array();
		$i = 1;
		foreach ($urlList as $url) {
		    $url = sanitizeData($url);
			if(!preg_match('/\w+/', $url)) continue;
			if (SP_DEMO) {
			    if ($i++ > 10) break;
			}

			$url = addHttpToUrl($url);
			$list[] = str_replace(array("\n", "\r", "\r\n", "\n\r"), "", trim($url));
		}

		$this->set('list', $list);
		$this->render('backlink/findbacklink');
	}
	
	function printBacklink($backlinkInfo){
		$this->url = $backlinkInfo['url'];
		$backlinkCount = $this->__getBacklinks($backlinkInfo['engine']);
		
		// if msn engine
		if ($backlinkInfo['engine'] == 'msn') {
			$websiteUrl = addHttpToUrl($backlinkInfo['url']);
		} else {		
			$websiteUrl = @Spider::removeTrailingSlash(formatUrl($backlinkInfo['url']));
		}

		$websiteUrl = urldecode($websiteUrl);
		$backlinkUrl = $this->backUrlList[$backlinkInfo['engine']] . $websiteUrl;
		echo "<a href='$backlinkUrl' target='_blank'>$backlinkCount</a>";
	}
	
	function __getBacklinks ($engine, $cron=false) {
		if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return 0;
		
		// check whether any api source is enabled for crawl keyword
		if ($engine != 'alexa') {
    		$searchInfo = ['name' => "link:$this->url", "engine" => $engine];
    		list($resDataStatus, $resData) = SettingsController::getSearchResultCount($searchInfo, $cron);
    		if ($resDataStatus) {
    		    return $resData['count'];
    		}
		}
		
		$backlinkCount = 0;
		switch ($engine) {
			
			#google
			case 'google':
				$url = $this->backUrlList[$engine] . urlencode($this->url);			
				$v = $this->spider->getContent($url);
				$pageContent = empty($v['page']) ? '' :  $v['page'];
				$r = [];
				$engineInfo = Spider::getCrawlEngineInfo('google', 'backlink');
				if (preg_match($engineInfo['regex1'], $pageContent, $r)) {					
				} elseif (preg_match($engineInfo['regex2'], $pageContent, $r)) {					
				} elseif (preg_match($engineInfo['regex3'], $pageContent, $r)) {					
				} elseif (preg_match($engineInfo['regex4'], $pageContent, $r)) {					
				} else {
					$crawlInfo['crawl_status'] = 0;
					$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($pageContent) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";					
				}
				
				$backlinkCount = !empty($r[1]) ? str_replace(',', '', $r[1]) : 0;
				break;
				
			#msn
			case 'msn':
			    $url = formatUrl($this->url, false);
				$url = $this->backUrlList[$engine] . urlencode(addHttpToUrl($url));
				$v = $this->spider->getContent($url);
				$pageContent = empty($v['page']) ? '' :  $v['page'];
				$r = [];
				$engineInfo = Spider::getCrawlEngineInfo('bing', 'backlink');
				if (preg_match($engineInfo['regex1'], $pageContent, $r)) {
				} elseif (preg_match($engineInfo['regex2'], $pageContent, $r)) {
				} elseif (preg_match($engineInfo['regex3'], $pageContent, $r)) {
				} elseif (preg_match($engineInfo['regex4'], $pageContent, $r)) {
				} else {
					$crawlInfo['crawl_status'] = 0;
					$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($pageContent) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
				}
				
				$backlinkCount = !empty($r[1]) ? str_replace(',', '', $r[1]) : 0;
				break;
				
			// alexa
			case 'alexa':
			    $backlinkCount = 0;
				break;
		}

		// update crawl log
		$crawlLogCtrl = new CrawlLogController();
		$crawlInfo['crawl_type'] = 'backlink';
		$crawlInfo['ref_id'] = $this->url;
		$crawlInfo['subject'] = $engine;
		$crawlLogCtrl->updateCrawlLog($v['log_id'], $crawlInfo);
		
		return $backlinkCount;
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
				
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
						
		$this->render('backlink/generatereport');
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
			$this->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
			foreach ($this->colList as $col => $dbCol) {
				$websiteInfo[$col] = $this->__getBacklinks($col);
			}
			
			$this->saveRankResults($websiteInfo, true);			
			echo "<p class='note notesuccess'>".$this->spTextBack['Saved backlink results of']." <b>$websiteUrl</b>.....</p>";
		}	
	}
	
	# function to save rank details
	function saveRankResults($matchInfo, $remove=false) {
		$resultDate = date('Y-m-d');
		
		if($remove){				
			$sql = "delete from backlinkresults where website_id={$matchInfo['id']} and result_date='$resultDate'";
			$this->db->query($sql);
		}
		
		$sql = "insert into backlinkresults(website_id,google,msn,result_date)
		values({$matchInfo['id']},{$matchInfo['google']},{$matchInfo['msn']}, '$resultDate')";
		$this->db->query($sql);
		
	}
	
	# function check whether reports already saved
	function isReportsExists($websiteId, $time) {
		$resultDate = date('Y-m-d', $time);
	    $sql = "select website_id from backlinkresults where website_id=$websiteId and result_date='$resultDate'";
	    $info = $this->db->select($sql, true);
	    return empty($info['website_id']) ? false : true;
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
		$sql = "select s.* ,w.name from backlinkresults s,websites w where s.website_id=w.id
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
		
		$websiteInfo = $websiteController->__getWebsiteInfo($websiteId);
		$websiteUrl =  @Spider::removeTrailingSlash(formatUrl($websiteInfo['url']));
		$websiteUrl = urldecode($websiteUrl);
		$this->set('directLinkList', array(
		    'google' => $this->backUrlList['google'] . $websiteUrl,		    
		    'msn' => $this->backUrlList['msn'] . $websiteUrl,
		));

		$this->set('list', array_reverse($reportList, true));
		$this->render('backlink/backlinkreport');
	}
	
	# func to get backlink report for a website
	function __getWebsitebacklinkReport($websiteId, $fromTime, $toTime) {

		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";		
		$sql = "select s.* ,w.name
				from backlinkresults s,websites w 
				where s.website_id=w.id 
				and s.website_id=$websiteId
				and (result_date='$fromTimeLabel' or result_date='$toTimeLabel')
				order by result_date DESC
				Limit 0,2";
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
		$sql = "select s.* ,w.name from backlinkresults s,websites w where s.website_id=w.id
		and result_date >= '$fromTime' and result_date <= '$toTime' $conditions order by result_date";
		$reportList = $this->db->select($sql);
	
		// if reports not empty
		$colList = $this->colList;
		if (!empty($reportList)) {
				
			$dataArr = "['Date', '" . implode("', '", array_values($colList)) . "']";
			 
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
		$this->render('backlink/graphicalreport');
	}
	
}
?>