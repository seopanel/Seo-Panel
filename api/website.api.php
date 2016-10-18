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

/**
 * Class defines all functions for managing website API
 * 
 * @author Seo panel
 *
 */
class WebsiteAPI extends Seopanel{
	
	/**
	 * the main controller to get details for api
	 * @var Object
	 */
	var $ctrler;
	
	/**
	 * The list contains search engine details
	 * @var Array
	 */
	var $seList;
	
	/**
	 * The constructor of API
	 */
	function WebsiteAPI() {
		$this->ctrler = new WebsiteController();
		$seController = New SearchEngineController();
		$list = $seController->__getAllSearchEngines();
		$this->seList = array();
		
		// loop through the search engine and assign id as key
		foreach ($list as $listInfo) {
			$this->seList[$listInfo['id']] = $listInfo;
		}
		
		include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		include_once(SP_CTRLPATH."/directory.ctrl.php");
		include_once(SP_CTRLPATH."/keyword.ctrl.php");
	}

	/**
	 * function to get website reports rank, backlinks, saturation and directory submission
	 * @param id $websiteInfo		The information about website
	 * @param int $fromTime			The time stamp of from time
	 * @param int $toTime			The time stamp of to time
	 * @return $websiteInfo			The formatted website info with position details
	 */
	function getFormattedReport($websiteInfo, $fromTime, $toTime) {
		
		// create required controllers
		$rankCtrler = New RankController();
		$backlinlCtrler = New BacklinkController();
		$saturationCtrler = New SaturationCheckerController();
		$dirCtrler = New DirectoryController();
		
		// rank reports
		$report = $rankCtrler->__getWebsiteRankReport($websiteInfo['id'], $fromTime, $toTime);
		$report = $report[0];
		$toTimeDate =  date('Y-m-d', $toTime);
		$websiteInfo['alexa']['alexarank']['rank'] = empty($report['alexa_rank']) ? "-" : $report['alexa_rank'];
		$websiteInfo['alexa']['alexarank']['diff'] = removeBraces($report['rank_diff_alexa']);
		$websiteInfo['alexa']['alexarank']['date'] = $toTimeDate; 
		$websiteInfo['moz']['pagerank']['rank'] = empty($report['moz_rank']) ? "-" : $report['moz_rank'];
		$websiteInfo['moz']['pagerank']['diff'] = removeBraces($report['rank_diff_moz']);
		$websiteInfo['noz']['pagerank']['date'] = $toTimeDate;
		
		// back links reports
		$report = $backlinlCtrler->__getWebsitebacklinkReport($websiteInfo['id'], $fromTime, $toTime);
		$report = $report[0];
		$websiteInfo['google']['backlinks']['rank'] = empty($report['google']) ? "-" : $report['google'];
		$websiteInfo['google']['backlinks']['diff'] = $report['rank_diff_google'];
		$websiteInfo['google']['backlinks']['date'] = $toTimeDate;
		$websiteInfo['alexa']['backlinks']['rank'] = empty($report['alexa']) ? "-" : $report['alexa'];
		$websiteInfo['alexa']['backlinks']['diff'] = $report['rank_diff_alexa'];
		$websiteInfo['alexa']['backlinks']['date'] = $toTimeDate;
		$websiteInfo['bing']['backlinks']['rank'] = empty($report['msn']) ? "-" : $report['msn'];
		$websiteInfo['bing']['backlinks']['diff'] = $report['rank_diff_msn'];
		$websiteInfo['bing']['backlinks']['date'] = $toTimeDate;
			
		// saturaton rank reports
		$report = $saturationCtrler->__getWebsiteSaturationReport($websiteInfo['id'], $fromTime, $toTime);
		$report = $report[0];
		$websiteInfo['google']['indexed']['rank'] = empty($report['google']) ? "-" : $report['google'];
		$websiteInfo['google']['indexed']['diff'] = $report['rank_diff_google'];
		$websiteInfo['google']['indexed']['date'] = $toTimeDate;
		$websiteInfo['bing']['indexed']['rank'] = empty($report['msn']) ? "-" : $report['msn'];
		$websiteInfo['bing']['indexed']['diff'] = $report['rank_diff_msn'];
		$websiteInfo['bing']['indexed']['date'] = $toTimeDate;
			
		// directory submission stats
		$websiteInfo['dirsub']['total'] = $dirCtrler->__getTotalSubmitInfo($websiteInfo['id']);
		$websiteInfo['dirsub']['active'] = $dirCtrler->__getTotalSubmitInfo($websiteInfo['id'], true);
		$websiteInfo['dirsub']['date'] = $toTimeDate;
		
		return $websiteInfo; 
	}	
	
	/**
	 * function to get website report using website id
	 * @param Array $info			The input details to process the api
	 * 		$info['id']  			The id of the website	- Mandatory
	 * 		$info['from_time']  	The from time of report in (yyyy-mm-dd) Eg: 2014-12-24	- Optional - (default => Yesterday)
	 * 		$info['to_time']  		The to time of report in (yyyy-mm-dd) Eg: 2014-12-28	- Optional - (default => Today)
	 * @return Array $returnInfo  	Contains informations about website reports
	 */
	function getReportById($info) {
		
		$fromTime = getFromTime($info);
		$toTime = getToTime($info);
		$websiteInfo = $this->ctrler->__getWebsiteInfo($info['id']);
		
		// if website not exists
		if (empty($websiteInfo['id'])) {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "The invalid website id provided";
		} else {
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = $this->getFormattedReport($websiteInfo, $fromTime, $toTime);
		}
			
		return 	$returnInfo;
		
	}
	
	
	/**
	 * function to get website report using user id
	 * @param Array $info			The input details to process the api
	 * 		$info['id']  			The id of the user	- Mandatory
	 * 		$info['from_time']  	The from time of report in (yyyy-mm-dd) Eg: 2014-12-24	- Optional - (default => Yesterday)
	 * 		$info['to_time']  		The to time of report in (yyyy-mm-dd) Eg: 2014-12-28	- Optional - (default => Today)
	 * @return Array $returnInfo  	Contains informations about website reports
	 */
	function getReportByUserId($info) {
		
		// if not valid user
		$userId = intval($info['id']);
		if (empty($userId)) {
			return array(
				'response' => 'Error',
				'result' => 'Invalid user id'
			);
		}		
		
		// get all active websites
		$wbList = $this->ctrler->__getAllWebsites($userId);
		$fromTime = getFromTime($info);
		$toTime = getToTime($info);
				
		// loop through websites
		$websiteList = array();
		foreach ($wbList as $websiteInfo) {
			$websiteList[$websiteInfo['id']] = $this->getFormattedReport($websiteInfo, $fromTime, $toTime);
		}

		// if position information is not empty
		if (empty($websiteList)) {
			$returnInfo['response'] = 'Error';
			$returnInfo['result'] = "No reports found!";
		} else {
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = $websiteList;
		}
		
		return 	$returnInfo;
		
	}
	
	
	/**
	 * function to get website information
	 * @param Array $info			The input details to process the api
	 * 		$info['id']  		    The id of the website	- Mandatory
	 * @return Array $returnInfo  	Contains informations about website
	 */
	function getWebsiteInfo($info) {
		$websiteId = intval($info['id']);
		$returnInfo = array();
	
		// validate the website ifd and website info
		if (!empty($websiteId)) {
			if ($websiteInfo = $this->ctrler->__getWebsiteInfo($websiteId)) {
				$returnInfo['response'] = 'success';
				$returnInfo['result'] = $websiteInfo;
				return $returnInfo;
			}
		}
	
		$returnInfo['response'] = 'Error';
		$returnInfo['error_msg'] = "The invalid website id provided";
		return 	$returnInfo;
	}
	
	/**
	 * function to create website
	 * @param Array $info				The input details to process the api
	 * 		$info['name']				The name of the website	- Mandatory
	 * 		$info['url']				The url of the website	- Mandatory
	 * 		$info['user_id']			The user id of website - Mandatory
	 * 		$info['title']				The title of the website - Optional
	 * 		$info['description']		The description of website - Optional
	 * 		$info['keywords']			The keyword of the website	- Optional
	 * 		$info['status']				The status of the website - default[1]	- Optional
	 * @return Array $returnInfo  	Contains details about the operation succes or not
	 */
	function createWebsite($info) {
		$websiteInfo = $info;
		
		// check for user id
		if (empty($info['user_id'])) {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = 'Invalid user id';
			return $returnInfo;
		}
		
		$websiteInfo['userid'] = $info['user_id'];
		$this->ctrler->spTextWeb = $this->ctrler->getLanguageTexts('website', SP_API_LANG_CODE);
		$return = $this->ctrler->createWebsite($websiteInfo, true);
	
		// if website creation is success
		if ($return[0] == 'success') {
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = $return[1];
			$returnInfo['website_id'] = $this->ctrler->db->getMaxId('websites');
		} else {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = $return[1];
		}
	
		return 	$returnInfo;
	
	}
	
	/**
	 * function to update website
	 * @param Array $info				The input details to process the api
	 * 		$info['id']					The id of the website	- Mandatory
	 * 		$info['name']				The name of the website	- Optional
	 * 		$info['url']				The url of the website	- Optional
	 * 		$info['title']				The title of the website - Optional
	 * 		$info['description']		The description of website - Optional
	 * 		$info['keywords']			The keyword of the website	- Optional
	 * 		$info['user_id']			The user id of website - Optional
	 * 		$info['status']				The status of the website - default[1]	- Optional
	 * @return Array $returnInfo  	Contains details about the operation succes or not
	 */
	function updateWebsite($info) {
	
		$websiteId = intval($info['id']);
	
		// if website exists
		if ($websiteInfo = $this->ctrler->__getWebsiteInfo($websiteId)) {
			
			$websiteInfo['oldName'] = $websiteInfo['name'];
			
			// loop through inputs
			foreach ($info as $key => $val) {
				$websiteInfo[$key] = $val;
			}
			
			// update website call as api call
			$this->ctrler->spTextWeb = $this->ctrler->getLanguageTexts('website', SP_API_LANG_CODE);
			$return = $this->ctrler->updateWebsite($websiteInfo, true);
				
			// if website creation is success
			if ($return[0] == 'success') {
				$returnInfo['response'] = 'success';
				$returnInfo['result'] = $return[1];
			} else {
				$returnInfo['response'] = 'Error';
				$returnInfo['error_msg'] = $return[1];
			}
				
		} else {
	
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "The invalid website id provided";
		}
	
		return 	$returnInfo;
	
	}
	
	/**
	 * function to delete website
	 * @param Array $info				The input details to process the api
	 * 		$info['id']					The id of the website	- Mandatory
	 * @return Array $returnInfo  	Contains details about the operation success or not
	 */
	function deleteWebsite($info) {
	
		$websiteId = intval($info['id']);
	
		// if website exists
		if ($websiteInfo = $this->ctrler->__getWebsiteInfo($websiteId)) {
			
			
			$this->ctrler->__deleteWebsite($websiteId);
			$returnInfo['response'] = 'success';
			$returnInfo['result'] = "Successfully deleted website and related data.";
			
		} else {	
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "The invalid website id provided";
		}
	
		return 	$returnInfo;
	}
	
}
?>