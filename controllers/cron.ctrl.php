<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seofreetools.net)  	   *
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

include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/moz.ctrl.php");

# class defines all cron controller functions
class CronController extends Controller {
    
	var $cronList;			    // the array includes all tools avialable for cron
	var $repTools;			    // the array includes all tools avialable for report generation
	var $debug = true;		    // to show debug message or not
	var $layout = 'ajax';       // ajax layout or not
	var $timeStamp;             // timestamp for storing reports
	var $checkedKeywords = 0;   // the number of keywords checked in cron, this is used for split cron execution feature 
	var $checkedWebsites = 0;   // the number of websites checked in cron, this is used for split cron execution feature       	
	
	# function to load all tools required for report generation 
	function loadReportGenerationTools($includeList=array()){
		$includeList = formatSQLParamList($includeList);
		$sql = "select * from seotools where status=1 and reportgen=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$this->repTools = $this->db->select($sql);
	}
	
	# function to load all tools required for cron job
	function loadCronJobTools($includeList=array()){
		$sql = "select * from seotools where status=1 and cron=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$sql .= " order by id ASC";
		$this->cronList = $this->db->select($sql);
	}	
	
	# function to show report generation manager
	function showReportGenerationManager(){
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('websiteNull', false);
		
		$this->loadReportGenerationTools();
		$this->set('repTools', $this->repTools);
		
		$this->render('report/reportgenerationmanager');
	}

	# function to show cron command
	function showCronCommand(){
		
		$this->render('report/croncommand');
	}
	
	# common report generation function
	function executeReportGenerationScript($info=[]) {	    
	    if(empty($info['repTools']) || count($info['repTools']) <= 0){
			showErrorMsg($this->spTextKeyword['pleaseselecttool']."!");
		}
		
		$websiteCtrler = New WebsiteController();
		if(!empty($info['website_id'])){
			$allWebsiteList[] = $websiteCtrler->__getWebsiteInfo($info['website_id']);
		}else{
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();		
			$allWebsiteList = array();
			foreach($userList as $userInfo){
				
				$websiteList = $websiteCtrler->__getAllWebsites($userInfo['id']);			
				foreach($websiteList as $websiteInfo){
					$allWebsiteList[] = $websiteInfo;				
				}
			}	
		}

		if(count($allWebsiteList) <= 0){
			showErrorMsg($_SESSION['text']['common']['nowebsites']."!");
		}
		
		$this->set('allWebsiteList', $allWebsiteList);
		$this->set('repTools', implode(':', $info['repTools']));
		$this->render('report/reportgenerator');
	}
	
	// common cron execute function
	function executeCron($includeList=array(), $userSelectList=array()) {
		$this->loadCronJobTools($includeList);
		$lastGenerated = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		$userCtrler = New UserController();
		
		// if user list selected is not empty
		if (!empty($userSelectList)) {
			$userList = $userSelectList;
		} else {
			$userList = $userCtrler->__getAllUsers(true, true, "utype_id DESC");
		}
		
		foreach($userList as $userInfo){
			
			// check whethere user id is existing
			if (empty($userInfo['id'])) continue;
			
			// check whether user expired 
			if (!$userCtrler->isUserExpired($userInfo['id'])) {
				continue;
			}
		    
		    // create report controller
		    $reportCtrler = New ReportController();
		    
		    // check for user report schedule
		    $repSetInfo = $reportCtrler->isGenerateReportsForUser($userInfo['id']);
		    
			if (!empty($repSetInfo['generate_report'])) {
			    $websiteCtrler = New WebsiteController();
			    $sql = "select * from websites where status=1 and user_id=" . $userInfo['id'] . " and crawled=0 order by name";
			    $websiteList = $websiteCtrler->db->select($sql);
			    $websiteCount = count($websiteList);
    			
    			// if websites are available
    			if ($websiteCount > 0) {
        			foreach($websiteList as $websiteInfo){
        				$this->websiteInfo = $websiteInfo;
        				$this->routeCronJob($websiteInfo['id'], '', true);
        				$this->checkedWebsites++;
        				
        				// change website crawl status
        				$sql = "update websites set crawled=1 where id=" . $websiteInfo['id'];
        				$websiteList = $websiteCtrler->db->query($sql);
        				
        				// if all websites checked, mark as report generated for the day
        				if ($this->checkedWebsites != $websiteCount) {
        					        				
	        				// to implement split cron execution feature
	        				if ( SP_NUMBER_WEBSITES_CRON > 0) {        					
	        					if ($this->checkedWebsites == SP_NUMBER_WEBSITES_CRON) {
	        						die("Reached total number of allowed websites(" . SP_NUMBER_WEBSITES_CRON. ") in each cron job");
	        					}
	        				}
	        				
        				}
        				
        			}
        			
        			// save report generated time
    				$reportCtrler->updateUserReportSetting($userInfo['id'], 'last_generated', $lastGenerated);
    				
    				// update report generation logs
    				$reportCtrler->updateUserReportGenerationLogs($userInfo['id'], date('Y-m-d H:i:s'));
    				
    				// update user alerts section
    				$alertCtrl = new AlertController();
    				$reportTxt = $this->getLanguageTexts('reports', $_SESSION['lang_code']);
    				$alertInfo = array(
    					'alert_subject' => $reportTxt["Reports Generated Successfully"],
    					'alert_message' => $reportTxt['report_email_subject'],
    					'alert_category' => "reports",
    					'alert_url' => SP_WEBPATH,
    				);
    				$alertCtrl->createAlert($alertInfo, $userInfo['id']);
    				
    				// send email notification if enabled
    				if (SP_REPORT_EMAIL_NOTIFICATION && $repSetInfo['email_notification']) {
    					$reportCtrler->spTextTools = $this->getLanguageTexts('seotools', $_SESSION['lang_code']);
    					$reportCtrler->set('spTextTools', $reportCtrler->spTextTools);
    				    $reportCtrler->sentEmailNotificationForReportGen($userInfo, $repSetInfo['last_generated'], $lastGenerated);
    				}
    				
    			}
    			
			}
		}
		
		// if user selected list empty
		if (empty($userSelectList)) {
			
			// reset all keywords crawl status
			$keywordCtrler = New KeywordController();
			$keywordCtrler->__changeCrawledStatus(0);
			$this->debugMsg("Reset all keywords crawl status\n");
	
			// change all website crawl status
			$sql = "update websites set crawled=0";
			$keywordCtrler->db->query($sql);
			$this->debugMsg("Change all websites crawl status\n");
		}
		
	}
	
	// function to route the cronjobs to different methods
	function routeCronJob($websiteId, $repTools='', $cron=false) {
		$websiteId = intval($websiteId);
		if(empty($this->websiteInfo)){
			$websiteCtrler = New WebsiteController();
			$this->websiteInfo = $websiteCtrler->__getWebsiteInfo($websiteId);
		}
		
		if($cron){			
			if(empty($this->cronList)){
				$this->loadCronJobTools();
			}
			
			$seoTools = $this->cronList;	
		}else{			
			$this->loadReportGenerationTools(explode(':', $repTools));
			$seoTools = $this->repTools;
		}
		
		// check whethre user access to seo tools and plugins
		$userCtrler = New UserController();
		$userInfo = $userCtrler->__getUserInfo($this->websiteInfo['user_id']);
		$userTypeCtrler = new UserTypeController();
		
		// check whethere user is admin
		if ($userInfo['utype_id'] == $userTypeCtrler->getAdminUserTypeId()) {
		    $isAdmin = true;
		} else {
		    $isAdmin = false;
		    $toolAccessList = $userTypeCtrler->getSeoToolAccessSettings($userInfo['utype_id']);
		}		
		
		foreach ($seoTools as $cronInfo) {
		    
		    // check whether user have acccess to the tool
		    if (!$isAdmin && empty($toolAccessList[$cronInfo['id']]['value']) ) {
		        continue;
		    }
		    
			switch($cronInfo['url_section']){
				
				case "webmaster-tools":
					$this->webmasterToolsCron($websiteId);
					break;
				
				case "keyword-position-checker":
					$this->keywordPositionCheckerCron($websiteId);
					break;
					
				case "rank-checker":
					$this->rankCheckerCron($websiteId);
					break;
					
				case "backlink-checker":
					$this->backlinkCheckerCron($websiteId);
					break;
					
				case "saturation-checker":
					$this->saturationCheckerCron($websiteId);
					break;
					
				case "pagespeed":
					$this->pageSpeedCheckerCron($websiteId);
					break;
					
				case "sm-checker":
					$this->socialMediaCheckerCron($websiteId);
					break;
					
				case "review-manager":
					$this->reviewCheckerCron($websiteId);
					break;
					
				case "web-analytics":
					$this->analyticsCron($websiteId);
					break;
			}
		}
		
	}
	
	# func to generate search engine saturation reports from cron
	function saturationCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
		$this->debugMsg("Starting Search engine saturation Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$saturationCtrler = New SaturationCheckerController();
		$websiteInfo = $this->websiteInfo;
		
		if (SP_MULTIPLE_CRON_EXEC && $saturationCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;
		
		$saturationCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
		foreach ($saturationCtrler->colList as $col => $dbCol) {
			$websiteInfo[$col] = $saturationCtrler->__getSaturationRank($col, true);
		}
			
		$saturationCtrler->saveRankResults($websiteInfo, true);			
		echo "Saved Search Engine Saturation results of <b>$websiteUrl</b>.....</br>\n";
		
	}
	
	# func to generate pagespeed reports from cron
	function pageSpeedCheckerCron($websiteId){
	
		include_once(SP_CTRLPATH."/pagespeed.ctrl.php");
		$this->debugMsg("Starting page speed Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
	
		$pageSpeedCtrler = New PageSpeedController();
		$websiteInfo = $this->websiteInfo;
	
		if (SP_MULTIPLE_CRON_EXEC && $pageSpeedCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;
		
		$userCtrler = new UserController();
		$userInfo = $userCtrler->__getUserInfo($websiteInfo['user_id']);
		$langCode = $userInfo['lang_code'];
		
		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$params = array('screenshot' => false, 'strategy' => 'desktop', 'locale' => $langCode);
		$websiteInfo['desktop'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);
		$params = array('screenshot' => false, 'strategy' => 'mobile', 'locale' => $langCode);
		$websiteInfo['mobile'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);
		
		$pageSpeedCtrler->savePageSpeedResults($websiteInfo, true);
		echo "Saved page speed results of <b>$websiteUrl</b>.....</br>\n";
	
	}
	
	# func to generate social media checker reports from cron
	function socialMediaCheckerCron($websiteId){
	
		include_once(SP_CTRLPATH."/social_media.ctrl.php");
		$this->debugMsg("Starting social media Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
	
		$socialMediaCtrler = New SocialMediaController();
		$websiteInfo = $this->websiteInfo;
		
		$linkList = $socialMediaCtrler->getAllLinksWithOutReports($websiteInfo['id'], date('Y-m-d', $this->timeStamp));
		if (SP_MULTIPLE_CRON_EXEC && empty($linkList)) {
			$this->debugMsg("No social media links left to generate report for website: {$this->websiteInfo['name']}....<br>\n");
			return true;
		}
		
		// loop through link list and save the data
		foreach ($linkList as $linkInfo) {
			$result = $socialMediaCtrler->getSocialMediaDetails($linkInfo['type'], $linkInfo['url']);
			
			if ($result['status']) {
				echo "Crawled social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
			} else {
				echo "Failed Crawling of social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
				echo $result['msg'];
			}
			
			// save the social media data
			$socialMediaCtrler->saveSocialMediaLinkResults($linkInfo['id'], $result);
			sleep(SP_CRAWL_DELAY + 5);
		}
		
		echo "Saved social media results of website id: <b>$websiteId</b>.....</br>\n";
	
	}
	
	# func to generate review checker reports from cron
	function reviewCheckerCron($websiteId) {
		include_once(SP_CTRLPATH."/review_manager.ctrl.php");
		$this->debugMsg("Starting review Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
	
		$reviewController = New ReviewManagerController();
		$websiteInfo = $this->websiteInfo;
		
		$linkList = $reviewController->getAllLinksWithOutReports($websiteInfo['id'], date('Y-m-d', $this->timeStamp));
		if (SP_MULTIPLE_CRON_EXEC && empty($linkList)) {
			$this->debugMsg("No review links left to generate report for website: {$this->websiteInfo['name']}....<br>\n");
			return true;
		}
		
		// loop through link list and save the data
		foreach ($linkList as $linkInfo) {
			$result = $reviewController->getReviewDetails($linkInfo['type'], $linkInfo['url']);
			
			if ($result['status']) {
				echo "Crawled review results of <b>{$linkInfo['name']}</b>.....</br>\n";
			} else {
				echo "Failed Crawling of review results of <b>{$linkInfo['name']}</b>.....</br>\n";
				echo $result['msg'];
			}
			
			// save the review data
			$reviewController->saveReviewLinkResults($linkInfo['id'], $result);
			sleep(SP_CRAWL_DELAY + 5);
		}
		
		echo "Saved review results of website id: <b>$websiteId</b>.....</br>\n";
	}	
	
	# func to generate backlink reports from cron
	function backlinkCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		$this->debugMsg("Starting Backlink Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$backlinkCtrler = New BacklinkController();
		$websiteInfo = $this->websiteInfo;
		
		if (SP_MULTIPLE_CRON_EXEC && $backlinkCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;
		
		$backlinkCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
		foreach ($backlinkCtrler->colList as $col => $dbCol) {
			$websiteInfo[$col] = $backlinkCtrler->__getBacklinks($col);
		}
		
		$backlinkCtrler->saveRankResults($websiteInfo, true);			
		echo "Saved backlink results of <b>$websiteUrl</b>.....</br>\n";
		
	}	
	
	// func to generate rank reports from cron
	function rankCheckerCron($websiteId) {
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		$this->debugMsg("Starting Rank Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$rankCtrler = New RankController();
		$websiteInfo = $this->websiteInfo;
		if (SP_MULTIPLE_CRON_EXEC && $rankCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) {
		    return;
		}
		
		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$mozCtrler = new MozController();
		$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));
		
		$websiteInfo['moz_rank'] = !empty($mozRankInfo[0]['moz_rank']) ? $mozRankInfo[0]['moz_rank'] : 0;
		$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;$mozRankInfo[0]['page_authority'];
		$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;$mozRankInfo[0]['domain_authority'];
		$rankCtrler->saveRankResults($websiteInfo, true);			
		$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");
	}
	
	# func to find the keyword position checker
	function keywordPositionCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/searchengine.ctrl.php");
		include_once(SP_CTRLPATH."/report.ctrl.php");
		
		$reportController = New ReportController();
		$keywordCtrler = New KeywordController();
		
		$seController = New SearchEngineController();
		$reportController->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		// get keywords not to be checked
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$sql = "select distinct(keyword_id) from keywordcrontracker kc, keywords k where k.id=kc.keyword_id and k.website_id=$websiteId and time=$time";
		$keyList = $this->db->select($sql);
		$excludeKeyList = array(0);
		foreach ($keyList as $info) {
			$excludeKeyList[] = $info['keyword_id'];
		}
		
		// get keywords needs to be checked
		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1 and k.crawled=0";		
		$sql .= " and k.id not in(".implode(",", $excludeKeyList).") order by k.name";
		$keywordList = $reportController->db->select($sql);
		$this->debugMsg("Starting keyword position checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		// loop through each keyword			
		foreach ( $keywordList as $keywordInfo ) {
			$reportController->seFound = 0;
			$crawlResult = $reportController->crawlKeyword($keywordInfo, '', true);
			foreach($crawlResult as $sengineId => $matchList){
				if($matchList['status']){				    
					foreach($matchList['matched'] as $i => $matchInfo){
						$remove = ($i == 0) ? true : false;						
						$matchInfo['se_id'] = $sengineId;						
						$matchInfo['keyword_id'] = $keywordInfo['id'];
						
						$repCtrler = New ReportController();
						$repCtrler->saveMatchedKeywordInfo($matchInfo, $remove);
					}
					$this->debugMsg("Successfully crawled keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain'].".....<br>\n");
				}else{
					$this->debugMsg("Crawling keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain']." failed......<br>\n");
				}
			}
			
			$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);
			
			// to implement split cron execution feature
			if ( (SP_NUMBER_KEYWORDS_CRON > 0) && !empty($crawlResult) ) {
			    $this->checkedKeywords++;
			    if ($this->checkedKeywords == SP_NUMBER_KEYWORDS_CRON) {
			        die("Reached total number of allowed keywords(".SP_NUMBER_KEYWORDS_CRON.") in each cron job");
			    }
			}
			
			if(empty($reportController->seFound)){
				$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
			}
			sleep(SP_CRAWL_DELAY);
		}
	}	
	
	# func to generate webmaster tools reports from cron
	function webmasterToolsCron($websiteId){
		
		include_once(SP_CTRLPATH."/webmaster.ctrl.php");
		$this->debugMsg("Starting webmaster tools cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$wmCtrler = New WebMasterController();
		$websiteInfo = $this->websiteInfo;
		
		// check whether old reports are not generated. Then generate from it.
		for ($i=4; $i>=2; $i--) {
		
			// report date should be less than 2 days, then only reports will be generated
			$reportDate = date('Y-m-d', $this->timeStamp - ($i * 60 * 60 * 24));
			
			// loop through source list
			foreach ($wmCtrler->sourceList as $source) {
				
				// check whether reports already existing 
				if (SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate, $source)) {
					$this->debugMsg("Skip webmaster tools report($reportDate) generation of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
					continue;
				}
				
				// store results
				$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate, $source);
			}		
	
			$this->debugMsg("Saved webmaster tools report($reportDate) of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
		}
		
		// update webmaster tools sitemaps
		$websiteController = New WebsiteController();
		$websiteController->importWebmasterToolsSitemaps($websiteId, true);
		$this->debugMsg("Saved webmaster tools sitemaps of <b>{$this->websiteInfo['name']}</b>.....<br>\n");		
		
	}	
	
	// func to generate analytics reports from cron
	function analyticsCron($websiteId){
		
		include_once(SP_CTRLPATH."/analytics.ctrl.php");
		$this->debugMsg("Starting analytics cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$wmCtrler = New AnalyticsController();
		$websiteInfo = $this->websiteInfo;
		$reportDate = date('Y-m-d', $this->timeStamp - (60 * 60 * 24));
			
		// check whether reports already existing 
		if (SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate)) {
		    $this->debugMsg("Analytics results already generated of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
		    return FALSE;
		}
			
		// store results
		$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate);
		$this->debugMsg("Saved analytics results of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
	}
	
	// func to show debug messages
	function debugMsg($msg='') {
		if($this->debug == true) print $msg;
	}
	
}
?>