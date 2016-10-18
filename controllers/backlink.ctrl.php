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
	var $colList = array('google' => 'google', 'alexa' => 'alexa', 'msn' => 'msn');
	var $backUrlList = array(
		'google' => 'http://www.google.com/search?hl=en&q=link%3A',
		'alexa' => 'http://www.alexa.com/siteinfo/',
		'msn' => 'http://www.bing.com/search?setmkt=en-us&q=link%3A',
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
	
	function __getBacklinks ($engine) {
		if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return 0;
		$backlinkCount = 0;
		switch ($engine) {
			
			#google
			case 'google':
				$url = $this->backUrlList[$engine] . urlencode($this->url);			
				$v = $this->spider->getContent($url);
				$pageContent = empty($v['page']) ? '' :  $v['page'];
				if (preg_match('/about ([0-9\,]+) result/si', $pageContent, $r)) {					
				} elseif (preg_match('/<div id=resultStats>([0-9\,]+) result/si', $pageContent, $r)) {					
				} elseif (preg_match('/([0-9\,]+) result/si', $pageContent, $r)) {					
				} elseif (preg_match('/about <b>([0-9\,]+)<\/b> linking/si', $pageContent, $r)) {					
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
		        if (preg_match('/([0-9\,]+) results/si', $pageContent, $r)) {
				} elseif (preg_match('/id="count".*?>.*?\(([0-9\,]+).*?\)/si', $pageContent, $r)) {
				} elseif (preg_match('/id="count".*?>.*?([0-9\,]+).*?/si', $pageContent, $r)) {
				} elseif (preg_match('/class="sb_count".*?>.*?([0-9\,]+).*?<\/span>/si', $pageContent, $r)) {
				} else {
					$crawlInfo['crawl_status'] = 0;
					$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($pageContent) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
				}
				
				$backlinkCount = !empty($r[1]) ? str_replace(',', '', $r[1]) : 0;
				break;
				
			# alexa
			case 'alexa':
				/*$url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url=' . urlencode($this->url);*/
				$url = $this->backUrlList[$engine] . urlencode($this->url);
				$v = $this->spider->getContent($url);
				$pageContent = empty($v['page']) ? '' :  $v['page'];
				
				/*if (preg_match('/<LINKSIN NUM="(.*?)"/si', $pageContent, $r) ) {
				}*/
				
				if (preg_match('/id="linksin-panel-content".*>([0-9,]+)<\/span>/si', $pageContent, $r)) {
				} else {
					$crawlInfo['crawl_status'] = 0;
					$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($pageContent) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
				}
							
				$backlinkCount = !empty($r[1]) ? intval(str_replace(",", "", $r[1])) : 0;
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
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		if($remove){				
			$sql = "delete from backlinkresults where website_id={$matchInfo['id']} and result_time=$time";
			$this->db->query($sql);
		}
		
		$sql = "insert into backlinkresults(website_id,google,alexa,msn,result_time)
				values({$matchInfo['id']},{$matchInfo['google']},{$matchInfo['alexa']},{$matchInfo['msn']},$time)";
		$this->db->query($sql);
	}
	
	# function check whether reports already saved
	function isReportsExists($websiteId, $time) {
	    $sql = "select website_id from backlinkresults where website_id=$websiteId and result_time=$time";
	    $info = $this->db->select($sql, true);
	    return empty($info['website_id']) ? false : true;
	}
	
	# func to show reports
	function showReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {
			$fromTime = @mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
		}
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 23:59:59');
		} else {
			$toTime = @mktime();
		}
		$this->set('fromTime', date('Y-m-d', $fromTime));
		$this->set('toTime', date('Y-m-d', $toTime));

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval( $searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";		
		$sql = "select s.* ,w.name
								from backlinkresults s,websites w 
								where s.website_id=w.id 
								and result_time>= $fromTime and result_time<=$toTime $conditions  
								order by result_time";
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
		    'alexa' => $this->backUrlList['alexa'] . $websiteUrl,
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
				and (FROM_UNIXTIME(result_time, '%Y-%m-%d')='$fromTimeLabel' or FROM_UNIXTIME(result_time, '%Y-%m-%d')='$toTimeLabel')
				order by result_time DESC
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
	
}
?>