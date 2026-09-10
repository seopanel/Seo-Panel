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

# class defines all rank controller functions
class RankController extends Controller{
	
	var $colList = array('spam_score' => 'spam_score', 'domain_authority' => 'domain_authority', 'page_authority' => 'page_authority');
	
	# func to show quick rank checker
	function showQuickRankChecker() {
		$this->render('rank/showquickrank');
	}
	
	function findQuickRank($searchInfo) {
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
		
		$mozCtrler = new MozController();
		$mozRankList = $mozCtrler->__getMozRankInfo($list);
		
		/*mozRankList = $this->__getMozRank($list);*/
		$this->set('mozRankList', $mozRankList);

		$this->set('list', $list);
		$this->render('rank/findquickrank');
	}

	// function to get moz rank
	function __getMozRank ($urlList = array(), $accessID = "", $secretKey = "", $returnLog = false) {
		$mozRankList = array();
		
		if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return $mozRankList;
		
		if (empty($urlList)) return $mozRankList;
		
		// Get your access id and secret key here: https://moz.com/products/api/keys
		$accessID = !empty($accessID) ? $accessID : SP_MOZ_API_ACCESS_ID;
		$secretKey = !empty($secretKey) ? $secretKey : SP_MOZ_API_SECRET;
		
		// if empty no need to crawl
		if (empty($accessID) || empty($secretKey)) return $mozRankList;
		
		// Set your expires times for several minutes into the future.
		// An expires time excessively far in the future will not be honored by the Mozscape API.
		$expires = time() + 300;
		
		// Put each parameter on a new line.
		$stringToSign = $accessID."\n".$expires;
		
		// Get the "raw" or binary output of the hmac hash.
		$binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
		
		// Base64-encode it and then url-encode that.
		$urlSafeSignature = urlencode(base64_encode($binarySignature));
		
		// Add up all the bit flags you want returned.
		// Learn more here: https://moz.com/help/guides/moz-api/mozscape/api-reference/url-metrics
		$cols = "16384";
		
		// Put it all together and you get your request URL.
		$requestUrl = SP_MOZ_API_LINK . "/url-metrics/?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;
		
		// Put your URLS into an array and json_encode them.
		$encodedDomains = json_encode($urlList);
		
		$spider = new Spider();
		$spider->_CURLOPT_POSTFIELDS = $encodedDomains;
		$ret = $spider->getContent($requestUrl);
		
		// parse rank from the page
		if (!empty($ret['page'])) {
			$rankList = json_decode($ret['page']);
			
			// if no errors occured
			if (empty($rankList->error_message)) {
			
				// loop through rank list
				foreach ($rankList as $rankInfo) {
					$mozRankList[] = round($rankInfo->umrp, 2);
				}
				
			} else {
				$crawlInfo['crawl_status'] = 0;
				$crawlInfo['log_message'] = $rankList->error_message;
			}
			
		} else {
			$crawlInfo['crawl_status'] = 0;
			$crawlInfo['log_message'] = $ret['errmsg'];
		}
	
		// update crawl log
		$crawlLogCtrl = new CrawlLogController();
		$crawlInfo['crawl_type'] = 'rank';
		$crawlInfo['ref_id'] = $encodedDomains;
		$crawlInfo['subject'] = "moz";
		$crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
	
		return $returnLog ? array($mozRankList, $crawlInfo) : $mozRankList;
	}
	

	# func to show genearte reports interface
	function showGenerateReports($searchInfo=[]) {		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);						
		$this->render('rank/generatereport');
	}
	
	# func to generate reports
	function generateReports($searchInfo=[]) {				
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
		
		$urlList = array();
		foreach ($websiteList as $websiteInfo) {
			$urlList[] = addHttpToUrl($websiteInfo['url']);
		}
		
		// get moz ranks
		/*$mozRankList = $this->__getMozRank($urlList);*/
		
		$mozCtrler = new MozController();
		$mozRankList = $mozCtrler->__getMozRankInfo($urlList);
				
		// loop through each websites
		foreach ( $websiteList as $i => $websiteInfo ) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			$websiteInfo['spam_score'] = !empty($mozRankList[$i]['spam_score']) ? $mozRankList[$i]['spam_score'] : 0;
			$websiteInfo['domain_authority'] = !empty($mozRankList[$i]['domain_authority']) ? $mozRankList[$i]['domain_authority'] : 0;
			$websiteInfo['page_authority'] = !empty($mozRankList[$i]['page_authority']) ? $mozRankList[$i]['page_authority'] : 0;

			$this->saveRankResults($websiteInfo, true);			
			echo "<p class='note notesuccess'>".$this->spTextRank['Saved rank results of']." <b>$websiteUrl</b>.....</p>";

			// Also save backlink data from Moz API
			$backlinkCtrler = New BacklinkController();
			$websiteInfo['external_pages_to_page'] = !empty($mozRankList[$i]['external_pages_to_page']) ? $mozRankList[$i]['external_pages_to_page'] : 0;
			$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankList[$i]['external_pages_to_root_domain']) ? $mozRankList[$i]['external_pages_to_root_domain'] : 0;
			$backlinkCtrler->saveRankResults($websiteInfo, true);
		}
	}
	
	# function to save rank details
	function saveRankResults($matchInfo, $remove=false) {
		$resultDate = date('Y-m-d');
		
		if($remove){				
			$sql = "delete from rankresults where website_id={$matchInfo['id']} and result_date='$resultDate'";
			$this->db->query($sql);
		}
		
		$spamScore = floatval($matchInfo['spam_score']);
		$domainAuthority = floatval($matchInfo['domain_authority']);
		$pageAuthority = floatval($matchInfo['page_authority']);
		$sql = "insert into rankresults(website_id, spam_score, domain_authority, page_authority, result_date)
			values({$matchInfo['id']}, $spamScore, $domainAuthority, $pageAuthority, '$resultDate')";
		$this->db->query($sql);
	}
	
	# function check whether reports already saved
	function isReportsExists($websiteId, $time) {
		$resultDate = date('Y-m-d', $time);
	    $sql = "select website_id from rankresults where website_id=$websiteId and result_date='$resultDate'";
	    $info = $this->db->select($sql, true);
	    return empty($info['website_id']) ? false : true;
	}
	
	# func to show reports
	function showReports($searchInfo=[]) {		
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
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		// a caller-supplied website_id must belong to one of the caller's
		// own (already-scoped) websites for a non-admin - otherwise fall
		// back to their own first website, same as when none is given at
		// all. Previously this was never checked, so any non-admin could
		// view ANY other user's domain/page authority history just by
		// passing an arbitrary website_id.
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = '';
		}
		if (empty($websiteId)) $websiteId = $websiteList[0]['id'] ?? '';
		$this->set('websiteId', $websiteId);

		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name from rankresults s,websites w  where s.website_id=w.id
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
					// lower is better for spam_score, so its sign is inverted
					$signVal = ($col == 'spam_score') ? 1 : -1;
					$greaterClass = 'green';
					$lessClass = 'red';
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * $signVal;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='$greaterClass'>(" . round($rankDiff[$col], 2) . ")</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='$lessClass'>(" . round($rankDiff[$col], 2) . ")</font>";
					}
				}
				$reportList[$key]['rank_diff_'.$col] = empty ($rankDiff[$col]) ? '' : $rankDiff[$col];
			}

			foreach ($colList as $col => $dbCol) {
				$prevRank[$col] = $repInfo[$dbCol];
			}

			$i++;
		}

		$this->set('list', array_reverse($reportList, true));

		include_once(SP_CTRLPATH . '/settings.ctrl.php');
		$this->set('localAiAvailable', SettingsController::isLocalAIEnabled());

		$this->render('rank/rankreport');
	}

	/*
	 * AJAX action: on-demand Local AI (Ollama) plain-language summary of
	 * this website's domain/page authority + spam score trend over the
	 * selected date range - see LocalAIController::
	 * summarizeAuthorityTrend(). Never auto-fired; returns JSON for the
	 * "Summarize with AI" button in rankreport.ctp.php. Ownership is
	 * enforced by summarizeAuthorityTrend() itself, not re-checked here.
	 */
	function summarizeTrend($info) {
		$userId = isLoggedIn();
		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		include_once(SP_CTRLPATH . '/localai.ctrl.php');
		$result = (new LocalAIController())->summarizeAuthorityTrend($info['website_id'], $userId, $fromTime, $toTime);
		header('Content-Type: application/json');
		print json_encode($result);
	}
	
	// func to show reports for a particular website
	function __getWebsiteRankReport($websiteId, $fromTime, $toTime) {
		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		$sql = "select s.* ,w.name
				from rankresults s,websites w 
				where s.website_id=w.id 
				and s.website_id=$websiteId
				and (result_date='$fromTimeLabel' or result_date='$toTimeLabel')
				order by result_date DESC
				Limit 0, 2";
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
					// lower is better for spam_score, so its sign is inverted
					$signVal = ($col == 'spam_score') ? 1 : -1;
					$greaterClass = 'green';
					$lessClass = 'red';

					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * $signVal;

					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='$greaterClass'>(" . round($rankDiff[$col], 2) . ")</font>";
					} elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='$lessClass'>(" . round($rankDiff[$col], 2) . ")</font>";
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
	function showGraphicalReports($searchInfo=[]) {	
		$userId = isLoggedIn();
		$fromTime = !empty($searchInfo['from_time']) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty ($searchInfo['to_time']) ? $searchInfo['to_time'] : date("Y-m-d");
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
		
        $websiteController = New WebsiteController();
        $websiteList = $websiteController->__getAllWebsites($userId, true);
        $this->set('websiteList', $websiteList);
        $websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
        // same ownership check as showReports() above - a non-admin's
        // caller-supplied website_id must be one of their own websites
        if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
            $websiteId = '';
        }
        if (empty($websiteId)) $websiteId = $websiteList[0]['id'] ?? '';
        $this->set('websiteId', $websiteId);

        $searchEngine = !empty($searchInfo['search_engine']) ? $searchInfo['search_engine'] : "spam_score";
        $this->set('searchEngine', $searchEngine);
        
        $conditions = empty($websiteId) ? "" : " and s.website_id=$websiteId";
        $sql = "select s.* ,w.name from rankresults s,websites w  where s.website_id=w.id
        and result_date >= '$fromTime' and result_date <= '$toTime' $conditions order by result_date";
        $reportList = $this->db->select($sql);
        
        $colLabelList = array(
        	'spam_score' => $_SESSION['text']['common']['Spam Score'],
        	'domain_authority' => $_SESSION['text']['common']['Domain Authority'],
        	'page_authority' => $_SESSION['text']['common']['Page Authority'],
        );    
        
        // loop through col list - only show the selected metric
        $colList = array();
        foreach ($this->colList as $seId => $seVal) {

        	// only include the selected search engine metric
        	if ($seId == $searchEngine) {
        		$colList[$seVal] = $colLabelList[$seVal];
        	}

        }
		
		// if reports not empty
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
	       
	        // spam_score uses reverse ranking (lower is better)
	        if ($searchEngine == 'spam_score') {
	        	$this->set('reverseDir', true);
	        }
	        
	        $this->set('dataArr', $dataArr);
	        $this->set('graphTitle', $this->spTextTools['Rank Reports']);
	        $graphContent = $this->getViewContent('report/graph');
				
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
		
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('rank/graphicalreport');
	}
	
}
?>