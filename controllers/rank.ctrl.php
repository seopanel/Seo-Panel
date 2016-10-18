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

# class defines all rank controller functions
class RankController extends Controller{

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
		
		$mozRankList = $this->__getMozRank($list);
		$this->set('mozRankList', $mozRankList);

		$this->set('list', $list);
		$this->render('rank/findquickrank');
	}

	function printGooglePageRank($url){
		$pageRank = $this->__getGooglePageRank($url);
		if($pageRank >= 0){
			$imageUrl = SP_IMGPATH."/pr/pr".$pageRank.".gif";
		}else{
			$imageUrl = SP_IMGPATH."/pr/pr.gif";
		}

		print "<img src='$imageUrl'>";
	}

	function printMOZRank($url){
		$pageRank = $this->__getMozRank($url);
		if($pageRank >= 0){
			$imageUrl = SP_IMGPATH."/pr/pr".$pageRank.".gif";
		}else{
			$imageUrl = SP_IMGPATH."/pr/pr.gif";
		}

		print "<img src='$imageUrl'>";
	}

	function __getGooglePageRank ($url) {
		
		return 0;

		// commented due to gooogle stops this service
		/*
	    if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return 0;	
	    $websiteUrl =  $url;   
		$url = "http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=".$this->CheckHash($this->hashURL($url))."&features=Rank&q=info:".$url."&num=100&filter=0";
		$ret = $this->spider->getContent($url);
		$rank = 0;
		
		// parse rank from the page
		if (!empty($ret['page'])) {
			if (preg_match('/Rank_([0-9]+):([0-9]+):([0-9]+)/si', $ret['page'], $matches) ) {
				$rank = empty($matches[3]) ? 0 : $matches[3];
			} else {
				$crawlInfo['crawl_status'] = 0;
				$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($ret['page']) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
			}
		}
		
		// update crawl log
		$crawlLogCtrl = new CrawlLogController();
		$crawlInfo['crawl_type'] = 'rank';
		$crawlInfo['ref_id'] = $websiteUrl;
		$crawlInfo['subject'] = "google";
		$crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
		
		return $rank;
		*/
	}

	function printAlexaRank($url){
		$alexaRank = $this->__getAlexaRank($url);
		$imageUrl = SP_WEBPATH."/rank.php?sec=alexaimg&rank=$alexaRank";

		print "<img src='$imageUrl'>";
	}

	function printAlexaRankImg($alexaRank) {
		$rankImage = SP_IMGPATH."/alexa-rank.jpeg";
		
		$im = imagecreatefromjpeg ($rankImage);
		$textColor = imagecolorallocate($im, 0, 0, 255);
		$width = imagesx($im);
		$height = imagesy($im);
		$leftTextPos = ( $width - (7 * strlen($alexaRank)) )/2;
		imagestring($im, 3, $leftTextPos, 35, $alexaRank, $textColor);

		ob_end_clean();
		Header('Content-type: image/jpeg');
		imagejpeg($im);
		exit;
	}

	# alexa_rank
	function __getAlexaRank ($url) {
	    if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return 0;	
	    $websiteUrl =  $url;
		$url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url=' . urlencode($url);
		$ret = $this->spider->getContent($url);
		$rank = 0;
		
		// parse rank from teh page
		if(!empty($ret['page'])){
			if (preg_match('/\<popularity url\="(.*?)" TEXT\="([0-9]+)"/si', $ret['page'], $matches) ) {
				$rank = empty($matches[2]) ? 0 : $matches[2];	
			} else {
				$crawlInfo['crawl_status'] = 0;
				$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($ret['page']) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
			}
			
		}
		
		// update crawl log
		$crawlLogCtrl = new CrawlLogController();
		$crawlInfo['crawl_type'] = 'rank';
		$crawlInfo['ref_id'] = $websiteUrl;
		$crawlInfo['subject'] = "alexa";
		$crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
		
		return $rank;
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
	

	function strToNum($Str, $Check, $Magic) {
		$Int32Unit = 4294967296;
		$length = strlen($Str);
		for ($i = 0; $i < $length; $i++) {
			$Check *= $Magic;
			if ($Check >= $Int32Unit) {
				$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
				$Check = ($Check < -2147483648)? ($Check + $Int32Unit) : $Check;
			}
			$Check += ord($Str{$i});
		}
		return $Check;
	}

	function hashURL($String) {
		$Check1 = $this->strToNum($String, 0x1505, 0x21);
		$Check2 = $this->strToNum($String, 0, 0x1003F);

		$Check1 >>= 2;
		$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
		$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
		$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);

		$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
		$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

		return ($T1 | $T2);
	}

	function checkHash($Hashnum) {
		$CheckByte = 0;
		$Flag = 0;

		$HashStr = sprintf('%u', $Hashnum) ;
		$length = strlen($HashStr);

		for ($i = $length - 1; $i >= 0; $i --) {
			$Re = $HashStr{$i};
			if (1 === ($Flag % 2)) {
				$Re += $Re;
				$Re = (int)($Re / 10) + ($Re % 10);
			}
			$CheckByte += $Re;
			$Flag ++;
		}

		$CheckByte %= 10;
		if (0!== $CheckByte) {
			$CheckByte = 10 - $CheckByte;
			if (1 === ($Flag % 2) ) {
				if (1 === ($CheckByte % 2)) {$CheckByte += 9;}
				$CheckByte >>= 1;
			}
		}

		return '7'.$CheckByte.$HashStr;
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
						
		$this->render('rank/generatereport');
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
		
		$urlList = array();
		foreach ($websiteList as $websiteInfo) {
			$urlList[] = addHttpToUrl($websiteInfo['url']);
		}
		
		// get moz ranks
		$mozRankList = $this->__getMozRank($urlList);
				
		// loop through each websites			
		foreach ( $websiteList as $i => $websiteInfo ) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			$websiteInfo['alexaRank'] = $this->__getAlexaRank($websiteUrl);
			$websiteInfo['moz_rank'] = !empty($mozRankList[$i]) ? $mozRankList[$i] : 0;
			
			$this->saveRankResults($websiteInfo, true);			
			echo "<p class='note notesuccess'>".$this->spTextRank['Saved rank results of']." <b>$websiteUrl</b>.....</p>";
		}	
	}
	
	# function to save rank details
	function saveRankResults($matchInfo, $remove=false) {
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		if($remove){				
			$sql = "delete from rankresults where website_id={$matchInfo['id']} and result_time=$time";
			$this->db->query($sql);
		}
		
		$sql = "insert into rankresults(website_id,moz_rank,alexa_rank,result_time)
				values({$matchInfo['id']},{$matchInfo['moz_rank']},{$matchInfo['alexaRank']},$time)";
		$this->db->query($sql);
	}
	
	# function check whether reports already saved
	function isReportsExists($websiteId, $time) {
	    $sql = "select website_id from rankresults where website_id=$websiteId and result_time=$time";
	    $info = $this->db->select($sql, true);
	    return empty($info['website_id']) ? false : true;
	}
	
	# func to show reports
	function showReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
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
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";		
		$sql = "select s.* ,w.name
								from rankresults s,websites w 
								where s.website_id=w.id 
								and result_time>= $fromTime and result_time<=$toTime $conditions  
								order by result_time";
		$reportList = $this->db->select($sql);
		
		$i = 0;
		$colList = array('moz' => 'moz_rank', 'alexa' => 'alexa_rank');
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
					$signVal = -1;
					$greaterClass = 'green';
					$lessClass = 'red';
					if($col == 'alexa'){						
						$signVal = 1;
						$greaterClass = 'green';
						$lessClass = 'red';
					}
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * $signVal;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='$greaterClass'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='$lessClass'>($rankDiff[$col])</font>";
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
		$this->render('rank/rankreport');
	}
	
	
	# func to show reports for a particular website
	function __getWebsiteRankReport($websiteId, $fromTime, $toTime) {

		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		$sql = "select s.* ,w.name
				from rankresults s,websites w 
				where s.website_id=w.id 
				and s.website_id=$websiteId
				and (FROM_UNIXTIME(result_time, '%Y-%m-%d')='$fromTimeLabel' or FROM_UNIXTIME(result_time, '%Y-%m-%d')='$toTimeLabel')
				order by result_time DESC
				Limit 0, 2";
		$reportList = $this->db->select($sql);
		$reportList = array_reverse($reportList);
		
		$i = 0;
		$colList = array('moz' => 'moz_rank', 'alexa' => 'alexa_rank');
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
					$signVal = -1;
					$greaterClass = 'green';
					$lessClass = 'red';
					if($col == 'alexa'){						
						$signVal = 1;
						$greaterClass = 'green';
						$lessClass = 'red';
					}
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * $signVal;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='$greaterClass'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='$lessClass'>($rankDiff[$col])</font>";
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