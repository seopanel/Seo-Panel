<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)			   *
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

# class defines all proxy controller functions
class ProxyController extends Controller{

	# func to show proxy list
	function listProxy($info=''){

		$userId = isLoggedIn();
		$sql = "select * from proxylist where 1=1";

		if (isset($info['status'])) {
			if (($info['status']== 'active') || ($info['status']== 'inactive')) {
				$statVal = ($info['status']=='active') ? 1 : 0;
				$conditions .= " and status=$statVal";
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
			$conditions .= " and proxy like '%".addslashes($info['keyword'])."%'";
			$urlParams .= "&keyword=".urlencode($info['keyword']);
		}
		$this->set('keyword', $info['keyword']);

		$sql .= " $conditions order by id";

		# pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('proxy.php', '', 'scriptDoLoad', 'content', $urlParams);
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;

		$proxyList = $this->db->select($sql);
		$this->set('pageNo', $info['pageno']);
		$this->set('list', $proxyList);
		$this->set('urlParams', $urlParams);
		$this->render('proxy/proxylist');
	}

	# func to show import proxy form
	function showImportProxy($info = ''){
		$this->render('proxy/importproxy');
	}

	#funvtion to import proxy
	function importProxy($data = "") {
		$errMsg['proxy_list'] = formatErrorMsg($this->validate->checkBlank($data['proxy_list']));
		if(!$this->validate->flagErr){
			$resInfo['total'] = $resInfo['valid'] = $resInfo['existing'] = 0;
			$proxyMaxId = $this->db->getMaxId('proxylist');
			$proxyList = explode("\n", $data['proxy_list']);
			foreach ($proxyList as $proxy) {
				if(!preg_match('/\w+/', $proxy)) continue;
				$listInfo = explode(",", $proxy);
				$proxyInfo['proxy'] = trim($listInfo[0]);
				if (!empty($proxyInfo['proxy'])) {
					$resInfo['total']++;
					$proxyInfo['port'] = trim($listInfo[1]);
					$proxyInfo['proxy_username'] = trim($listInfo[2]);
					$proxyInfo['proxy_password'] = trim($listInfo[3]);
					if ($this->__checkProxy($proxyInfo['proxy'], $proxyInfo['port'])) {
						$resInfo['existing']++;
					} else {
						$proxyInfo['proxy_auth'] = (!empty($proxyInfo['proxy_username']) || !empty($proxyInfo['proxy_password'])) ? 1 : 0;
						$this->insertProxy($proxyInfo);
						$resInfo['valid']++;
					}

				}
			}

			// if imported proxies needs to be checked
			$proxyList = array();
			if (!empty($data['check_status'])) {
				$this->updateProxyCheckedStatus(0, "");
				$condition = " and id > $proxyMaxId";
				$sql = "select * from proxylist where 1=1 ". $condition;
				$proxyList = $this->db->select($sql);
			}

			$this->set('proxyList', $proxyList);
			$this->set('resInfo', $resInfo);
			$this->set('proxyMaxId', $proxyMaxId);
			$this->render('proxy/importresult');

		} else {
			showErrorMsg("Please enter valid proxy list.");
		}
	}

	# func to check status of all proxy list
	function showcheckAllStatus($info=''){
		$this->render('proxy/showcheckallstatus');
	}

	# function to check all proxy status
	function checkAllProxyStatus($info = '') {
		$isStatusCheck = false;
		$this->updateProxyCheckedStatus(0, "");
		if ($info['status'] != "") {
			$isStatusCheck = true;
			$status = ($info['status'] == 'active') ? 1 : 0;
			$this->set('status', $status);
		}

		$proxyList = $this->__getAllProxys($isStatusCheck, $status);
		$this->set('activeCount', $this->__getProxyCount(" where status=1 and checked=1"));
		$this->set('inActiveCount', $this->__getProxyCount(" where status=0 and checked=1"));
		$this->set('proxyList', $proxyList);
		$this->render('proxy/checkallstatus');

	}

	# func to check status of all proxy list
	function runCheckStatus($info = '') {
		$proxyId = intval($info['id']);
		$this->checkStatus($proxyId);
		$this->updateProxyCheckedStatus(1, " where id=$proxyId");

		$where = "";
		if (isset($info['status'])) {
			$status = $info['status'];
			$where = " and status=$status";
			$this->set('status', $status);
		}

		// if max id is set
		if (isset($info['proxy_max_id'])) {
			$where = " and id > ".intval($info['proxy_max_id']);
			$this->set('proxyMaxId', $info['proxy_max_id']);
		}

		$sql = "select * from proxylist where checked=0 $where order by id";
		$proxyList = $this->db->select($sql);
		$this->set('activeCount', $this->__getProxyCount(" where status=1 and checked=1"));
		$this->set('inActiveCount', $this->__getProxyCount(" where status=0 and checked=1"));
		$this->set('checkedCount', $this->__getProxyCount(" where checked=1"));
		$this->set('proxyList', $proxyList);
		$this->render('proxy/runcheckstatus');
	}

	# function to update checked status of the proxy list
	function updateProxyCheckedStatus($checkedVal = 0, $where = "") {
		$sql = "update proxylist set checked=".intval($checkedVal);
		$sql .= empty($where) ? "" : $where;
		$this->db->query($sql);
	}

	# function to get proxy count
	function __getProxyCount($where = '') {
		$sql = "select count(*) as count from proxylist $where";
		$listInfo = $this->db->select($sql, true);
		return $listInfo['count'];
	}

	# func to get all Proxys
	function __getAllProxys($isStatusCheck=true, $status=1){
		$sql = "select * from proxylist where 1=1";
		if($isStatusCheck){
			$sql .= " and status=".intval($status);
		}
		$sql .= " order by id";
		$proxyList = $this->db->select($sql);
		return $proxyList;
	}

	# func to change status
	function __changeStatus($proxyId, $status){
		$sql = "update proxylist set status=$status where id=".intval($proxyId);
		$this->db->query($sql);
	}

	# func to change status
	function __deleteProxy($proxyId){
		$sql = "delete from proxylist where id=".intval($proxyId);
		$this->db->query($sql);
	}

	function __checkProxy($name, $port=80){
		$sql = "select id from proxylist where proxy='".addslashes($name)."' and port=".intval($port);
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}


	function newProxy($listInfo=''){
		if (!isset($listInfo['port'])) {
			$listInfo['port'] = 80;
		}

		$this->set('post', $listInfo);
		$this->render('proxy/newproxy');
	}

	function createProxy($listInfo){

		$errMsg['proxy'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy']));
		$errMsg['port'] = formatErrorMsg($this->validate->checkNumber($listInfo['port']));
		if (!empty($listInfo['proxy_auth'])) {
			$errMsg['proxy_username'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy_username']));
			$errMsg['proxy_password'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy_password']));
		}
		if(!$this->validate->flagErr){
			if (!$this->__checkProxy($listInfo['proxy'], $listInfo['port'])) {
				$this->insertProxy($listInfo);
				$this->listProxy();
				exit;
			}else{
				$errMsg['proxy'] = formatErrorMsg($this->spTextProxy['Proxyalreadyexist']);
			}
		}
		$this->set('errMsg', $errMsg);
		$this->newProxy($listInfo);
	}

	function insertProxy($listInfo) {
		$proxyAuth = empty($listInfo['proxy_auth']) ? 0 : 1;
		$sql = "insert into proxylist(proxy,port,proxy_auth,proxy_username,proxy_password,status)
		values('".addslashes($listInfo['proxy'])."', '".intval($listInfo['port'])."', $proxyAuth, '".addslashes($listInfo['proxy_username'])."', '".addslashes($listInfo['proxy_password'])."', 0)";
		$this->db->query($sql);
	}

	function __getProxyInfo($proxyId){
		$sql = "select * from proxylist where id=".intval($proxyId);
		$listInfo = $this->db->select($sql, true);
		$listInfo['proxy_username'] = stripslashes($listInfo['proxy_username']);
		$listInfo['proxy_password'] = stripslashes($listInfo['proxy_password']);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	function editProxy($proxyId, $listInfo=''){

		if(!empty($proxyId)){
			if(empty($listInfo)){
				$listInfo = $this->__getProxyInfo($proxyId);
				$listInfo['oldProxy'] = $listInfo['proxy'];
			}
			$this->set('post', $listInfo);

			$this->render('proxy/editproxy');
			exit;
		}
		$this->listProxy();
	}

	function updateProxy($listInfo){

		$this->set('post', $listInfo);
		$errMsg['proxy'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy']));
		$errMsg['port'] = formatErrorMsg($this->validate->checkNumber($listInfo['port']));
		if (!empty($listInfo['proxy_auth'])) {
			$errMsg['proxy_username'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy_username']));
			$errMsg['proxy_password'] = formatErrorMsg($this->validate->checkBlank($listInfo['proxy_password']));
		}
		if(!$this->validate->flagErr){

			if($listInfo['proxy'] != $listInfo['oldProxy']){
				if ($this->__checkProxy($listInfo['proxy'], $listInfo['port'])) {
					$errMsg['proxy'] = formatErrorMsg($this->spTextProxy['Proxyalreadyexist']);
					$this->validate->flagErr = true;
				}
			}

			if (!$this->validate->flagErr) {
				$proxyAuth = empty($listInfo['proxy_auth']) ? 0 : 1;
				$sql = "update proxylist set
						proxy = '".addslashes($listInfo['proxy'])."',
						port = '".intval($listInfo['port'])."',
						status = '".intval($listInfo['status'])."',
						proxy_auth = $proxyAuth,
						proxy_username = '".addslashes($listInfo['proxy_username'])."',
						proxy_password = '".addslashes($listInfo['proxy_password'])."'
						where id={$listInfo['id']}";
				$this->db->query($sql);
				$this->listProxy();
				exit;
			}
		}
		$this->set('errMsg', $errMsg);
		$this->editProxy($listInfo['id'], $listInfo);
	}

	# func to check whether proxy is active or not
	function __isProxyActive($proxyId) {

		$proxyInfo = $this->__getProxyInfo($proxyId);
		$ret = $this->spider->checkProxy($proxyInfo);
		return empty($ret['error']) ? 1 : 0;

	}

	function checkStatus($proxyId) {
		$status = $this->__isProxyActive($proxyId);
		$this->__changeStatus($proxyId, $status);
	}

	function getRandomProxy() {
		$sql = "SELECT * FROM proxylist where status=1 ORDER BY RAND() LIMIT 1";
		$listInfo = $this->db->select($sql, true);
		$listInfo['proxy_username'] = stripslashes($listInfo['proxy_username']);
		$listInfo['proxy_password'] = stripslashes($listInfo['proxy_password']);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	// function to show cron command
	function showCronCommand(){

		$this->render('proxy/croncommand');
	}
	/**
	 * function to show perfomance of a proxy
	 * @param Array $info	Contains all search info details
	 */
	function showProxyPerfomance($info = '') {

		$sql = "select p.id as proxy_id, p.proxy, p.port, count(*) count, sum(crawl_status) success, avg(crawl_status) avg_score,
		count(*) - sum(crawl_status) fail from crawl_log t join proxylist p on p.id=t.proxy_id where 1=1";

		$conditions = "";
		if (empty($info['keyword'])) {
			$info['keyword'] =  '';
		} else {
			$info['keyword'] = urldecode($info['keyword']);
			$searchKeyword = addslashes($info['keyword']);
			$conditions .= " and p.proxy like '%$searchKeyword%'";
			$urlParams .= "&keyword=".urlencode($info['keyword']);
		}

		$this->set('keyword', $info['keyword']);

		if (!empty ($info['from_time'])) {
			$fromTime = strtotime($info['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 90, date('Y'));
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

		// set status
		$urlParams .= "&order_by=".$info['order_by'];
		$this->set('statVal', $info['order_by']);

		// sql created using param
		$sql .= " $conditions and crawl_time >='$fromTimeLabel 00:00:00' and crawl_time<='$toTimeLabel 23:59:59' group by proxy_id order by avg_score";
		$sql .= ($info['order_by'] == 'fail') ? " ASC" : " DESC";

		// pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('proxy.php?sec=perfomance', '', 'scriptDoLoad', 'content', $urlParams);
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;

		$logList = $this->db->select($sql);
		$this->set('pageNo', $info['pageno']);
		$this->set('list', $logList);
		$this->set('urlParams', $urlParams);

		$this->render('proxy/proxyperfomance');
	}
}
?>
