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

# class defines all search engine controller functions
class SearchEngineController extends Controller{
	
	# func to get all search engines
	function __getAllSearchEngines(){
		$sql = "select * from searchengines where status=1";
		$seList = $this->db->select($sql);
		return $seList;
	}
	
	# func to get search engine info
	function __getsearchEngineInfo($seId){
		$sql = "select * from searchengines where id=$seId";
		$seList = $this->db->select($sql, true);
		return $seList;
	}
	
	# func to get all search engines
	function __getAllCrawlFormatedSearchEngines(){
		$sql = "select * from searchengines where status=1";
		$list = $this->db->select($sql);
		$seList = array();
		foreach($list as $seInfo){
			$seId = $seInfo['id'];
			$seInfo['regex'] = "/".$seInfo['regex']."/is";
			$search = array('[--num--]');
			$replace = array($seInfo['no_of_results_page']);
			$seInfo['url'] = str_replace($search, $replace, $seInfo['url']);
			$seList[$seId] = $seInfo;
		}	
		return $seList;
	}
	
	# func to show search engines
	function listSE($info=''){
		$info = sanitizeData($info);
		$info['stscheck'] = isset($info['stscheck']) ? intval($info['stscheck']) : 1;
		$pageScriptPath = 'searchengine.php?stscheck=' . $info['stscheck'];
		$sql = "select * from searchengines where status='{$info['stscheck']}'";
		
		// search for search engine name
		if (!empty($info['se_name'])) {
			$sql .= " and url like '%".addslashes($info['se_name'])."%'";
			$pageScriptPath .= "&se_name=" . $info['se_name'];
		}
		
		$sql .= " order by id"; 
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content', 'layout=ajax');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		$seList = $this->db->select($sql);
		$this->set('seList', $seList);

		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);
		$this->set('pageNo', $info['pageno']);		
		$this->render('searchengine/list', 'ajax');
	}
	
	# func to change status of search engine
	function __changeStatus($seId, $status){		
		$seId = intval($seId);
		$sql = "update searchengines set status=$status where id=$seId";
		$this->db->query($sql);
	}
	
	# func to delete search engine
	function __deleteSearchEngine($seId){
		$seId = intval($seId);
		$sql = "delete from searchengines where id=$seId";
		$this->db->query($sql);
		
		
		$sql = "select id from searchresults where searchengine_id=$seId";
		$recordList = $this->db->select($sql);
		
		if(count($recordList) > 0){
			foreach($recordList as $recordInfo){
				$sql = "delete from searchresultdetails where searchresult_id=".$recordInfo['id'];
				$this->db->query($sql);
			}
			
			$sql = "delete from searchresults where searchengine_id=$seId";
			$this->db->query($sql);
		}		
		
	}
	
	# function to check whether captcha found in search engine results
	static function isCaptchInSearchResults($searchContent) {

		$captchFound = false;
		
		// if captcha input field is found
		if (stristr($searchContent, 'name="captcha"') || stristr($searchContent, 'id="captcha"')) {
			$captchFound = true;
		}
		
		return $captchFound;
	}
	
}
?>