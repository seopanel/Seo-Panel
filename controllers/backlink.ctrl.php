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

# class defines all backlink controller functions
class BacklinkController extends Controller{
	var $url;
	var $colList = array("external_pages_to_page" => "external_pages_to_page", "external_pages_to_root_domain" => "external_pages_to_root_domain");

	function showBacklink() {
		$this->render('backlink/showbacklink');
	}
	
	function findBacklink($searchInfo) {
		$urlList = explode("\n", $searchInfo['website_urls']);
		$list = array();
		$i = 1;
		foreach ($urlList as $url) {
		    $url = sanitizeData($url);
		    if(!preg_match('/\w+/', $url)) {
		        continue;
		    }
		    
			if (SP_DEMO) {
			    if ($i++ > 10) break;
			}

			$url = addHttpToUrl($url);
			$list[] = str_replace(array("\n", "\r", "\r\n", "\n\r"), "", trim($url));
		}
		
		$mozCtrler = new MozController();
		$mozRankList = $mozCtrler->__getMozRankInfo($list);

		$this->set('mozRankList', $mozRankList);
		$this->set('list', $list);
		$this->render('backlink/findbacklink');
	}
	
	function printBacklink($backlinkInfo){
		$metric = !empty($backlinkInfo['metric']) ? $backlinkInfo['metric'] : 'external_pages_to_page';
		$backlinkCount = $this->__getBacklinks($backlinkInfo['url'], $metric);
		echo $backlinkCount;
	}
	
	function __getBacklinks ($url, $metric = 'external_pages_to_page') {
		if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return 0;

		// Use sample API data if enabled (saves API credits)
		if (defined('SP_USE_SAMPLE_API_DATA') && SP_USE_SAMPLE_API_DATA) {
			return rand(100, 50000);
		}

		// Get backlink data from Moz API (matches generateReports logic)
		include_once(SP_CTRLPATH."/moz.ctrl.php");
		$mozCtrler = new MozController();
		$mozRankInfo = $mozCtrler->__getMozRankInfo(array($url));

		// Extract backlink count based on metric
		$backlinkCount = !empty($mozRankInfo[0][$metric]) ? $mozRankInfo[0][$metric] : 0;

		return $backlinkCount;
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo=[]) {				
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
						
		$this->render('backlink/generatereport');
	}
	
	# func to generate reports
	function generateReports($searchInfo=[]) {
		$userId = isLoggedIn();
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);

		$sql = "select id,url from websites where status=1";
		if(!empty($userId) && !isAdmin()) {
		    $sql .= " and user_id=$userId";
		}
		
		if(!empty($websiteId)) {
		    $sql .= " and id=$websiteId";
		}
		
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);
		if(count($websiteList) <= 0) {
			echo "<p class='note'>".$_SESSION['text']['common']['nowebsites']."!</p>";
			exit;
		}

		# loop through each websites
		$rankCtrler = New RankController();
		foreach ( $websiteList as $websiteInfo ) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);

			// Get all Moz data in one API call
			include_once(SP_CTRLPATH."/moz.ctrl.php");
			$mozCtrler = new MozController();
			$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));

			// Extract backlink data
			$websiteInfo['external_pages_to_page'] = !empty($mozRankInfo[0]['external_pages_to_page']) ? $mozRankInfo[0]['external_pages_to_page'] : 0;
			$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankInfo[0]['external_pages_to_root_domain']) ? $mozRankInfo[0]['external_pages_to_root_domain'] : 0;
			$this->saveRankResults($websiteInfo, true);
			echo "<p class='note notesuccess'>".$this->spTextBack['Saved backlink results of']." <b>$websiteUrl</b>.....</p>";

			// Also save rank data
			$websiteInfo['spam_score'] = !empty($mozRankInfo[0]['spam_score']) ? $mozRankInfo[0]['spam_score'] : 0;
			$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;
			$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;
			$rankCtrler->saveRankResults($websiteInfo, true);
		}
	}
	
	# function to save rank details
	function saveRankResults($matchInfo, $remove=false) {
		$resultDate = date('Y-m-d');

		if($remove){
			$sql = "delete from backlinkresults where website_id={$matchInfo['id']} and result_date='$resultDate'";
			$this->db->query($sql);
		}

		$columns = array();
		$values = array();
		foreach ($this->colList as $col => $dbCol) {
			$columns[] = $dbCol;
			$values[] = !empty($matchInfo[$col]) ? intval($matchInfo[$col]) : 0;
		}

		// broken_backlinks is DataForSEO-only - NULL means "not measured by
		// this row's provider" (the Moz path never sets it), same convention
		// as the aio_* columns on searchresults.
		$columns[] = 'broken_backlinks';
		$values[] = array_key_exists('broken_backlinks', $matchInfo) ? intval($matchInfo['broken_backlinks']) : 'NULL';

		$sql = "insert into backlinkresults(website_id," . implode(',', $columns) . ",result_date)
		values({$matchInfo['id']}," . implode(',', $values) . ", '$resultDate')";
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
		// view ANY other user's backlink history just by passing an
		// arbitrary website_id.
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = '';
		}
		if (empty($websiteId)) $websiteId = $websiteList[0]['id'] ?? '';
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

		$hasBrokenBacklinks = false;
		foreach ($reportList as $repInfo) {
			if ($repInfo['broken_backlinks'] !== null) {
				$hasBrokenBacklinks = true;
				break;
			}
		}
		$this->set('hasBrokenBacklinks', $hasBrokenBacklinks);

		$this->set('list', array_reverse($reportList, true));

		include_once(SP_CTRLPATH . '/settings.ctrl.php');
		$this->set('localAiAvailable', SettingsController::isLocalAIEnabled());

		$this->render('backlink/backlinkreport');
	}

	/*
	 * AJAX action: on-demand Local AI (Ollama) plain-language summary of
	 * this website's backlink/referring-domain (+ broken backlinks, where
	 * measured) trend over the selected date range - see
	 * LocalAIController::summarizeBacklinkTrend(). Never auto-fired;
	 * returns JSON for the "Summarize with AI" button in
	 * backlinkreport.ctp.php. Ownership is enforced by
	 * summarizeBacklinkTrend() itself, not re-checked here.
	 */
	function summarizeTrend($info) {
		$userId = isLoggedIn();
		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		include_once(SP_CTRLPATH . '/localai.ctrl.php');
		$result = (new LocalAIController())->summarizeBacklinkTrend($info['website_id'], $userId, $fromTime, $toTime);
		header('Content-Type: application/json');
		print json_encode($result);
	}
	
	# func to get backlink report for a website
	function __getWebsitebacklinkReport($websiteId, $fromTime, $toTime) {
		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);	
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

		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";
		$sql = "select s.* ,w.name from backlinkresults s,websites w where s.website_id=w.id
		and result_date >= '$fromTime' and result_date <= '$toTime' $conditions order by result_date";
		$reportList = $this->db->select($sql);

		// if reports not empty
		$colList = $this->colList;
		if (!empty($reportList)) {
			// Create readable labels for graph
			$graphLabels = array(
				'external_pages_to_page' => $this->spTextBack['Backlink Count'],
				'external_pages_to_root_domain' => $this->spTextBack['Domain Backlink Count']
			);

			$dataArr = "['Date', '" . implode("', '", array_values($graphLabels)) . "']";
			 
			// loop through data list
			foreach ($reportList as $dataInfo) {	
				$valStr = "";
				foreach (array_keys($colList) as $seId) {
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