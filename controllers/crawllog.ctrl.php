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

# class defines all crawl log functions
class CrawlLogController extends Controller {
	
	/**
	 * the name of the crawl logging database tabel
	 */
	var $tablName = "crawl_log";
	
	/**
	 * function to create crawl logs for report generation
	 * @param $crawlInfo   array contains all detaisl of crawl
	 */
	function createCrawlLog($crawlInfo) {
		$dateTime = date('Y-m-d H:i:s');
		$sql = "INSERT INTO ". $this->tablName ."(".implode(",", array_keys($crawlInfo)).", crawl_time)";
		$sql .= " values ('".implode("','", array_values($crawlInfo))."', '$dateTime')";
		$this->db->query($sql);
		$logId = $this->db->getMaxId($this->tablName);
		return $logId;
	}
	
	/**
	 * function to update crawl log
	 * @param int $logId			The id of the crawl log needs to be updated
	 * @param Array $crawlInfo		The array contains crawl log details needs to be updated
	 */
	function updateCrawlLog($logId, $crawlInfo) {
		
		// if log id is not empty
		if (!empty($logId)) {
		
			$sql = "update $this->tablName set ";
			
			// for each through the log values
			foreach($crawlInfo as $key => $value) {
				$sql .= "$key='". addslashes($value) ."',";
			}
			
			$sql = rtrim($sql, ",");
			$sql .= " where id=$logId";
			$this->db->query($sql);
		}
		
	}
	
	/**
	 * Function to display crawl log details 
	 * @param Array $info	Contains all search details
	 */
	function listCrawlLog($info=[]) {
	    
		$sql = "select t.*, k.name keyword from $this->tablName t left join keywords k on t.ref_id=k.id where 1=1";
		$conditions = "";
	
		if (isset($info['status'])) {
			if (($info['status'] == 'success') || ($info['status'] == 'fail')) {
				$statVal = ($info['status']=='success') ? 1 : 0;
				$conditions .= " and crawl_status=$statVal";
				$urlParams .= "&status=".$info['status'];
			}
		} else {
			$info['status'] = '';
		}
		
		$this->set('statVal', $info['status']);
	
		if (empty($info['keyword'])) {
			$info['keyword'] =  '';
		} else {
			$info['keyword'] = urldecode($info['keyword']);
			$searchKeyword = addslashes($info['keyword']);
			$conditions .= " and (ref_id like '%$searchKeyword%' or subject like '%$searchKeyword%' or crawl_referer like '%$searchKeyword%'
			or log_message like '%$searchKeyword%' or k.name like '%$searchKeyword%' or crawl_link like '%$searchKeyword%'
			or crawl_cookie like '%$searchKeyword%' or crawl_post_fields like '%$searchKeyword%' or crawl_useragent like '%$searchKeyword%')";
			$urlParams .= "&keyword=".urlencode($info['keyword']);
		}
		
		$this->set('keyword', $info['keyword']);
		
		$crawlType = "";
		if (!empty($info['crawl_type'])) {
			$crawlType = $info['crawl_type'];
			$conditions .= " and crawl_type='".addslashes($crawlType)."'";
			$urlParams .= "&crawl_type=".$crawlType;
		}
		
		// find different crawl types
		$crawlTypeSql = "select distinct crawl_type from $this->tablName";
		$crawlTypeList = $this->db->select($crawlTypeSql);
		$this->set('crawlTypeList', $crawlTypeList);
		$this->set('crawlType', $crawlType);		
		
		$proxyId = "";
		if (!empty($info['proxy_id'])) {
			$proxyId = $info['proxy_id'];
			$conditions .= " and proxy_id='".intval($proxyId)."'";
			$urlParams .= "&proxy_id=".$proxyId;
		}
		
		// find different proxy used
		$proxySql = "select distinct proxy_id, proxy, port from $this->tablName t, proxylist pl 
		where pl.id=t.proxy_id and t.proxy_id!=0";
		$proxyList = $this->db->select($proxySql);
		$this->set('proxyList', $proxyList);
		$this->set('proxyId', $proxyId);
		
		$seId = "";
		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$seNameList = array();
		foreach ($seList as $seInfo) {
			$seNameList[] = $seInfo['domain'];
		}
		
		if (!empty($info['se_id'])) {
			$seId = intval($info['se_id']);
			$conditions .= " and (subject='$seId' or subject in ('".implode(",", $seNameList)."'))";
			$urlParams .= "&se_id=".$seId;
		}
		
		$this->set('seList', $seList);
		$this->set('seId', $seId);		
		
		if (!empty ($info['from_time'])) {
			$fromTime = strtotime($info['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
		}
		
		if (!empty ($info['to_time'])) {
			$toTime = strtotime($info['to_time'] . ' 00:00:00');
		} else {
			$toTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		}
		
		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		$this->set('fromTime', $fromTimeLabel);
		$this->set('toTime', $toTimeLabel);
		$urlParams .= "&from_time=$fromTimeLabel&to_time=$toTimeLabel";
		
		// sql created using param
		$sql .= " $conditions and crawl_time >='$fromTimeLabel 00:00:00' and crawl_time<='$toTimeLabel 23:59:59' order by id DESC";
		
		// pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('log.php', '', 'scriptDoLoad', 'content', $urlParams);
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
	
		$logList = $this->db->select($sql);
		$this->set('pageNo', $info['pageno']);
		$this->set('list', $logList);
		$this->set('urlParams', $urlParams);
		$this->set('fromPopUp', $info['fromPopUp']);
		$this->render('log/crawlloglist');
	}

	/**
	 * fucntion to delete log id
	 * @param int $logId	The id of the log needs to be deleted
	 */
	function deleteCrawlLog($logId) {
		$sql = "delete from crawl_log where id=".intval($logId);
		$this->db->query($sql);
	}
	
	/**
	 * function to clear all logs saved in teh system
	 */
	function clearAllLog() {
		$sql = "truncate crawl_log";
		$this->db->query($sql);
	}
	
	/**
	 * function to get crawl log information
	 * @param int $logId	The id of the log needs to be dispalyed
	 */
	function getCrawlLogInfo($logId) {
		$sql = "select * from crawl_log where id=".intval($logId);
		$logInfo = $this->db->select($sql, true);
		return $logInfo;
	}
	
	/**
	 * fucntion to show crawl log details
	 * @param int $logId	The id of the log needs to be dispalyed
	 */
	function showCrawlLogDetails($logId) {
		$logInfo = $this->getCrawlLogInfo($logId);
		$this->set('logInfo', $logInfo);
		$this->render('log/showcrawllog');
	}
	
	/**
	 * function to clear logs before an interval 
	 * @param int $daysBefore 	The days before the crawl logs to be deleted
	 */
	function clearCrawlLog($daysBefore) {		
		$dateBefore = mktime(0, 0, 0, date('m'), date('d') - $daysBefore, date('y'));
		$dateBefore = date('Y-m-d H:i:s', $dateBefore);
		$sql = "delete from crawl_log where crawl_time < '$dateBefore'";
		$this->db->query($sql);		
	}
	
	/*
	 * function to show mail logs
	 */
	function listMailLog($info = []) {
	    
	    $sql = "select * from mail_logs where 1=1";
	    $conditions = "";
	    
	    if (isset($info['status'])) {
	        if (($info['status'] == 'success') || ($info['status'] == 'fail')) {
	            $statVal = ($info['status']=='success') ? 1 : 0;
	            $conditions .= " and status=$statVal";
	        }
	    } else {
	        $info['status'] = '';
	    }
	    
	    if (empty($info['keyword'])) {
	        $info['keyword'] =  '';
	    } else {
	        $info['keyword'] = urldecode($info['keyword']);
	        $searchKeyword = addslashes($info['keyword']);
	        $conditions .= " and (from_address like '%$searchKeyword%' or to_address like '%$searchKeyword%' or cc_address like '%$searchKeyword%'
			or log_message like '%$searchKeyword%' or subject like '%$searchKeyword%')";
	    }
	    
	    if (!empty($info['cat_type'])) {
	        $catType = $info['cat_type'];
	        $conditions .= " and mail_category='".addslashes($catType)."'";
	    }
	    
	    // find different cat types
	    $catTypeSql = "select distinct mail_category from mail_logs";
	    $catTypeList = $this->db->select($catTypeSql);
	    $this->set('catTypeList', $catTypeList);
	    
	    $fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
	    $toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
	    $this->set('fromTime', $fromTime);
	    $this->set('toTime', $toTime);
	    
	    // sql created using param
	    $sql .= " $conditions and log_time >='".addslashes($fromTime)." 00:00:00' and log_time<='".addslashes($toTime)." 23:59:59' order by id DESC";
	    
	    // pagination setup
	    $this->db->query($sql, true);
	    $this->paging->setDivClass('pagingdiv');
	    $this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
	    $pagingDiv = $this->paging->printPages('log.php', 'listform', 'scriptDoLoadPost', 'content', '&sec=mail' );
	    $this->set('pagingDiv', $pagingDiv);
	    $sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
	    
	    $logList = $this->db->select($sql);
	    $this->set('list', $logList);
	    $this->set('pageNo', $info['pageno']);
	    $this->set('post', $info);
	    $this->render('log/mail_log_list');
	}
	
	/**
	 * fucntion to delete mail log id
	 * @param int $logId	The id of the log needs to be deleted
	 */
	function deleteMailLog($logId) {
	    $sql = "delete from mail_logs where id=".intval($logId);
	    $this->db->query($sql);
	}
	
	/**
	 * function to clear all mail logs saved in teh system
	 */
	function clearAllMailLog() {
	    $sql = "truncate mail_logs";
	    $this->db->query($sql);
	}
	
	/**
	 * function to clear mail logs before an interval
	 * @param int $daysBefore 	The days before the crawl logs to be deleted
	 */
	function clearMaillLog($daysBefore) {
	    $dateBefore = mktime(0, 0, 0, date('m'), date('d') - $daysBefore, date('y'));
	    $dateBefore = date('Y-m-d H:i:s', $dateBefore);
	    $sql = "delete from mail_logs where log_time < '$dateBefore'";
	    $this->db->query($sql);
	}
	
	/**
	 * function to get mail log information
	 * @param int $logId	The id of the log needs to be dispalyed
	 */
	function getMailLogInfo($logId) {
	    $sql = "select * from mail_logs where id=".intval($logId);
	    $logInfo = $this->db->select($sql, true);
	    return $logInfo;
	}
	
	/**
	 * fucntion to show mail log details
	 * @param int $logId	The id of the log needs to be dispalyed
	 */
	function showMailLogDetails($logId) {
	    $logInfo = $this->getMailLogInfo($logId);
	    $this->set('logInfo', $logInfo);
	    $this->render('log/show_mail_log');
	}
	
	/**
	 * function to create mail logs
	 * @param $mailInfo   array contains all details of mail
	 */
	function createMailLog($mailInfo) {
	    
	    foreach ($mailInfo as $key => $value) {
	        $mailInfo[$key] = addslashes($value);
	    }
	    
	    $sql = "INSERT INTO mail_logs(".implode(",", array_keys($mailInfo)).")";
	    $sql .= " values ('".implode("','", array_values($mailInfo))."')";
	    $this->db->query($sql);
	    $logId = $this->db->getMaxId($this->tablName);
	    return $logId;
	}
}
?>