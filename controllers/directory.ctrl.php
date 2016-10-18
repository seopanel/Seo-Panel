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

# class defines all directory controller functions
class DirectoryController extends Controller{
	var $noTitles = 5; 			  # no of titles and description for submission
	var $capchaFile = "captcha";  # captcha file name
	var $checkPR = 0;
	
	function showSubmissionPage( ) {
		
		$userId = isLoggedIn();
		$this->session->setSession('dirsub_pr', '');
		
		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		
		$langCtrler = New LanguageController();
		$langList = $langCtrler->__getAllLanguages();
		$this->set('langList', $langList);
		
		$this->render('directory/showsubmission');
	}
	
	function __getDirectoryInfo($dirId){
		$sql = "select * from directories where id=$dirId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo;
	}
	
	function showWebsiteSubmissionPage($submitInfo, $error=false) {
		
		if(empty($submitInfo['website_id'])) {
			showErrorMsg($this->spTextDir['Please select a website to proceed']."!");
		}
		
		# check whether the sitemap directory is writable
 		if(!is_writable(SP_TMPPATH ."/".$this->sitemapDir)){
 			showErrorMsg("Directory '<b>".SP_TMPPATH."</b>' is not <b>writable</b>. Please change its <b>permission</b> !");
 		}
		
		if(empty($error)){
			$websiteController = New WebsiteController();
			$websiteInfo = $websiteController->__getWebsiteInfo($submitInfo['website_id']);
			$websiteInfo['website_id'] = $submitInfo['website_id'];
		}else{
			$websiteInfo = $submitInfo;
		}
		
		$this->set('websiteInfo', $websiteInfo);		
		$this->session->setSession('no_captcha', empty($submitInfo['no_captcha']) ? 0 : 1);
		$this->session->setSession('dirsub_pr', $submitInfo['pagerank']);
		$this->session->setSession('dirsub_lang', $submitInfo['lang_code']);
		$this->set('noTitles', $this->noTitles);		
		$this->render('directory/showsitesubmission');
	}
	
	function saveSubmissiondata( $submitInfo ) {
		
		$submitInfo['website_id']= intval($submitInfo['website_id']);
		if(empty($submitInfo['website_id'])) {
			showErrorMsg("Please select a website to proceed!");
		}
		
		$_SESSION['skipped'][$submitInfo['website_id']] = array();
		
		if(!SP_DEMO){
			$errMsg['url'] = formatErrorMsg($this->validate->checkBlank($submitInfo['url']));
			$errMsg['owner_name'] = formatErrorMsg($this->validate->checkBlank($submitInfo['owner_name']));
			$errMsg['category'] = formatErrorMsg($this->validate->checkBlank($submitInfo['category']));
			$errMsg['title'] = formatErrorMsg($this->validate->checkBlank($submitInfo['title']));
			$errMsg['description'] = formatErrorMsg($this->validate->checkBlank($submitInfo['description']));
			$errMsg['keywords'] = formatErrorMsg($this->validate->checkBlank($submitInfo['keywords']));
			$errMsg['owner_email'] = formatErrorMsg($this->validate->checkEmail($submitInfo['owner_email']));
			
			# error occurs
			if($this->validate->flagErr){
				$this->set('errMsg', $errMsg);
				$submitInfo['sec'] = '';
				$this->showWebsiteSubmissionPage($submitInfo, true);
				return;
			}
		
			$submitInfo['url'] = addHttpToUrl($submitInfo['url']);
			$recUrl = formatUrl($submitInfo['reciprocal_url']);
			$submitInfo['reciprocal_url'] = empty($recUrl) ? "" : addHttpToUrl($submitInfo['reciprocal_url']);
		
			$sql = "update websites set " .
					"url='".addslashes($submitInfo['url'])."'," .
					"owner_name='".addslashes($submitInfo['owner_name'])."'," .
					"owner_email='".addslashes($submitInfo['owner_email'])."'," .
					"category='".addslashes($submitInfo['category'])."'," .
			        "reciprocal_url='".addslashes($submitInfo['reciprocal_url'])."'," .
					"title='".addslashes($submitInfo['title'])."'," .
					"description='".addslashes($submitInfo['description'])."',";			
			for($i=2;$i<=$this->noTitles;$i++){
				$sql .= "title$i='".addslashes($submitInfo['title'.$i])."'," .
					"description$i='".addslashes($submitInfo['description'.$i])."',";
			}			
			$sql .=	"keywords='".addslashes($submitInfo['keywords'])."' " .
					"where id={$submitInfo['website_id']}";
			$this->db->query($sql);
		}
		$this->startSubmission($submitInfo['website_id']);	
					
	}
	
	function isCategoryExists($page,$categoryCol) {
		$matches = array();
		$pattern1 = '/<select name="'.$categoryCol.'".*?<\/select>/is';
		$pattern2 = '/<select.*?'.$categoryCol.'.*?<\/select>/is';
		$pattern3 = '/<select.*?'.$categoryCol.'.*<\/select>/is';
		$matched = 0;
		if($matched = preg_match($pattern1, $page, $matches)){
		}elseif($matched = preg_match($pattern2, $page, $matches)){
		}elseif($matched = preg_match($pattern3, $page, $matches)){
		}
		
		return $matches;
	}

	# func to create captcha image in system
	function __getCreatedCaptchaUrl($captchaUrl, $submitUrl, $phpsessid){
		
		$spider = new Spider();
		$spider->_CURLOPT_REFERER = $submitUrl;
		$spider->_CURLOPT_TIMEOUT = 30;
		
		if(!empty($phpsessid)){
			$spider->_CURLOPT_COOKIE = 'PHPSESSID=' . $phpsessid . '; path=/';	
		}
		
		$ret = $spider->getContent($captchaUrl);
		
		if(!empty($ret['page'])){
			$captchaFile = $this->capchaFile .isLoggedIn() . ".jpg";
			$fp = fopen(SP_TMPPATH."/".$captchaFile, 'w');
			fwrite($fp, $ret['page']);
			fclose($fp);
			$captchaUrl = SP_WEBPATH. "/tmp/" .$captchaFile ."?rand=".mktime();	
		}
		
		return $captchaUrl; 
	}
	
	# func to show submission page
	function startSubmission( $websiteId, $dirId='' ) {
		
		# get list of already submitted directories
		$sql = "select directory_id from dirsubmitinfo where website_id=$websiteId";
		$list = $this->db->select($sql);
		$dirList = array();
		foreach($list as $listInfo){
			$dirList[] = $listInfo['directory_id'];
		}
		
		# to get skipped directories
		$skipDirList = $this->__getAllSkippedDir($websiteId);
		if( count($skipDirList) > 0){
			$dirList = array_merge($dirList, $skipDirList);
		}
		
		$sql = "select * from directories where working=1";
		if(!empty($_SESSION['no_captcha'])) $sql .= " and is_captcha=0";
		
		// check for page rank
		if(!empty($_SESSION['dirsub_pr'])) {
			$prMax = intval($_SESSION['dirsub_pr']) + 0.5;
			$prMin = intval($_SESSION['dirsub_pr']) - 0.5;
			$sql .= " and pagerank<$prMax and pagerank>=$prMin";
		}
		
		if(!empty($_SESSION['dirsub_lang'])) $sql .= " and lang_code='{$_SESSION['dirsub_lang']}'";
		
		// if reciprocal directory needs to be filtered
		if(!empty($_SESSION['no_reciprocal'])) {
			$sql .= " and extra_val not like '%LINK_TYPE=reciprocal%' and is_reciprocal=0";
		}		
		
		if(!empty($dirId)) $sql .= " and id=$dirId";
		if(count($dirList) > 0) $sql .= " and id not in (".implode(',', $dirList).")";
		$sql .= " order by rank DESC, extra_val ASC, id ASC";
		$dirInfo = $this->db->select($sql, true);
		$this->set('dirInfo', $dirInfo);		
		
		# directory list is empty
		if(empty($dirInfo['id'])) {
			showErrorMsg($this->spTextDir['nodirnote'].". Please <a href='".SP_CONTACT_LINK."' target='_blank'>Contact</a> <b>Seo Panel Team</b> to get more <b>directories</b>.");
		}
		
		$websiteController = New WebsiteController();
		$websiteInfo = $websiteController->__getWebsiteInfo($websiteId);
		$this->set('websiteId', $websiteId);
		
		$spider = new Spider();
		$spider->_CURLOPT_HEADER = 1; 
		$ret = $spider->getContent(addHttpToUrl($dirInfo['submit_url']));
				
		if($ret['error']){
			$this->set('error', 1);
			$this->set('msg', $ret['errmsg']);
		}
		
		$page = $ret['page'];
		if(!empty($page)){
			$matches = $this->isCategoryExists($page, $dirInfo['category_col']);		
		}

		# if category exists proceed submission
		if(!empty($matches[0])){
			
			$categorysel = $matches[0];
			$catList = explode(',', $websiteInfo['category']);
			if(count($catList) > 0){
				foreach($catList as $category){
					$category = trim($category);
					$categorysel = preg_replace("/<(option.*?$category.*?)>/si", '<$1 selected>', $categorysel, 1, $count);
					if($count > 0) break;	
				}

				if($count <= 0){
					$categorysel = $matches[0];
				}
			}

			$categorysel = str_replace("ADD_CATEGORY_ID[]", $dirInfo['category_col'], $categorysel);
			$this->set('categorySel', $categorysel);
			
			$captchaUrl = '';
			if(stristr($page, $dirInfo['captcha_script'])){
				$captchaUrl = $dirInfo['captcha_script'];
			}
			
			$imageHash = $addParams = "";
			if(preg_match('/name="'.$dirInfo['imagehash_col'].'".*?value="(.*?)"/is', $page, $hashMatch)){
				$imageHash = $hashMatch[1];
			}
			$this->set('imageHash', $imageHash);
			$phpsessid = $spider->getSessionId($page);
			$this->set('phpsessid', $phpsessid);
			
			if(!empty($captchaUrl)){
				$captchaUrl = preg_replace('/^\//', '', $captchaUrl);				
				$dirInfo['domain'] = addHttpToUrl($dirInfo['domain']);
				if(preg_match('/\/$/', $dirInfo['domain'])){
					$captchaUrl = $dirInfo['domain']. $captchaUrl;
				} else { 
					$captchaUrl =  $dirInfo['domain']."/". $captchaUrl;	
				}			
				
				if(!stristr($captchaUrl, '?')){
					if(!empty($imageHash)) {
						$captchaUrl .= "?".$dirInfo['imagehashurl_col']."=".$imageHash; 
					}else $captchaUrl .= "?rand=".rand(1,1000);	
				}else{					
					if(!empty($imageHash)) {
						$captchaUrl .= "&".$dirInfo['imagehashurl_col']."=".$imageHash; 
					}else $captchaUrl .= "&rand=".rand(1,1000); 
				}

				# to get stored image path if hot linking is prevented
				$captchaUrl = $this->__getCreatedCaptchaUrl($captchaUrl, $dirInfo['submit_url'], $phpsessid);
				
			} else {

				// check for number question existing there
				if (preg_match('/(\d+) \+ (\d+) =/is', $page, $numMatch)) {
					$sumMath = intval($numMatch[1]) + intval($numMatch[2]);
					$addParams = "&DO_MATH=$sumMath&Anti_Spam_Field=$sumMath";
				}
				
			}			
			
			$this->set('captchaUrl', $captchaUrl);
			$this->set('addParams', $addParams);

			// function check whether recriprocal directory
			$scriptInfo = $this->getDirectoryScriptMetaInfo($dirInfo['script_type_id']);
			$checkArg = $scriptInfo['link_type_col']."=".$scriptInfo['reciprocal'];
			$reciprocalUrl = '';
			$reciprocalDir = false;
			if (!empty($dirInfo['is_reciprocal']) || stristr($dirInfo['extra_val'], $checkArg)) {
				$reciprocalDir = true;
                $reciprocalUrl =  $websiteInfo['reciprocal_url'];
                if (empty($reciprocalUrl)) {
                    if (preg_match("/&{$scriptInfo['reciprocal_col']}=(.*)/", $dirInfo['extra_val'], $matches)) { 
                        if (!empty($matches[1])) $reciprocalUrl = $matches[1];     
                    }
                }
			}
			
			$this->set('reciprocalDir', $reciprocalDir);
			$this->set('reciprocalUrl', $reciprocalUrl);
			
		}else{
			$this->set('error', 1);
			$this->set('msg', $this->spTextDir['nocatnote']);
		}
		
		$this->render('directory/showsubmissionform');				
	}
	
	# to get random title and description for submisiion
	function __getSubmitTitleDes($websiteInfo){
		$titleList = array();
		
		$titleList[0]['title'] = $websiteInfo['title'];
		$titleList[0]['description'] = $websiteInfo['description'];
		for($i=2;$i<=$this->noTitles;$i++){
			$titleInfo = array();
			if(!empty($websiteInfo['title'.$i]) && !empty($websiteInfo['description'.$i])){
				$titleInfo['title'] = $websiteInfo['title'.$i];
				$titleInfo['description'] = $websiteInfo['description'.$i];
				$titleList[] = $titleInfo;
			}	
		}
		if($index = array_rand($titleList, 1)){
			$websiteInfo['title'] = $titleList[$index]['title'];
			$websiteInfo['description'] = $titleList[$index]['description'];	
		}
		
		return $websiteInfo;
	}	
	
	# submitting site directory
	function submitSite( $submitInfo ) {

		$submitInfo['dir_id'] = intval($submitInfo['dir_id']);
		$submitInfo['website_id'] = intval($submitInfo['website_id']);
		$dirInfo = $this->__getDirectoryInfo($submitInfo['dir_id']);
		
		$websiteController = New WebsiteController();
		$websiteInfo = $websiteController->__getWebsiteInfo($submitInfo['website_id']);		
		$websiteInfo = $this->__getSubmitTitleDes($websiteInfo);
		
		$postData = $dirInfo['title_col']."=".$websiteInfo['title'];
		$postData .= "&".$dirInfo['url_col']."=".$websiteInfo['url'];
		$postData .= "&".$dirInfo['description_col']."=".$websiteInfo['description'];
		$postData .= "&".$dirInfo['name_col']."=".$websiteInfo['owner_name'];
		$postData .= "&".$dirInfo['email_col']."=".$websiteInfo['owner_email'];
		$postData .= "&".$dirInfo['category_col']."=".$submitInfo[$dirInfo['category_col']];
		$postData .= "&".$dirInfo['cptcha_col']."=".$submitInfo[$dirInfo['cptcha_col']];
		$postData .= $submitInfo['add_params'];
		
		// check image hash there
		if(!empty($submitInfo[$dirInfo['imagehash_col']])){
			$postData .= "&".$dirInfo['imagehash_col']."=".$submitInfo[$dirInfo['imagehash_col']];
		}
		
		// check for reciprocal link
		$scriptInfo = $this->getDirectoryScriptMetaInfo($dirInfo['script_type_id']);
		if (!empty($submitInfo['reciprocal_url'])) {
		    
		    // if extra values contain the reciprocal col name
			$reciprocalUrl = addHttpToUrl($submitInfo['reciprocal_url']);
		    if (stristr($dirInfo['extra_val'], $scriptInfo['reciprocal_col'])) {
		    	$dirInfo['extra_val'] = preg_replace("/&{$scriptInfo['reciprocal_col']}=(.*)/", "&{$scriptInfo['reciprocal_col']}=$reciprocalUrl", $dirInfo['extra_val']);
		    } else {
		    	$postData .= "&".$dirInfo['reciprocal_col']."=$reciprocalUrl";
		    }
		    
		}		
		
		$postData .= "&".$dirInfo['extra_val'];
		
		// check phpLD directory. Then add extra values
		if ($scriptInfo['name'] == 'phpLD') {
			$postData .= "&OWNER_NEWSLETTER_ALLOW=on&META_KEYWORDS={$websiteInfo['keywords']}
			&META_DESCRIPTION=" . substr($websiteInfo['description'], 0, 249) . "&META_DESCRIPTION_limit=250&formSubmitted=1";
		}
		
		$spider = new Spider(); 
		$spider->_CURLOPT_POSTFIELDS = $postData;
		$spider->_CURLOPT_REFERER = $dirInfo['submit_url'];
		if(!empty($submitInfo['phpsessid'])){
			$spider->_CURLOPT_COOKIE = 'PHPSESSID=' . $submitInfo['phpsessid'] . '; path=/';	
		}
		$ret = $spider->getContent($dirInfo['submit_url']);
		
		if($ret['error']){
			$this->set('error', 1);
			$this->set('msg', $ret['errmsg']);
		}else{
			
			$page = $ret['page'];
			
			if(SP_DEBUG) $this->logSubmissionResult($page, $submitInfo['dir_id'], $submitInfo['website_id']);		
			
			// check success messages
			if (preg_match('/<td.*?class="msg".*?>(.*?)<\/td>/is', $page, $matches)) {
			} elseif (preg_match('/<p.*?class="box success".*?>(.*?)<\/p>/is', $page, $matches)) {
			} else{
				$status = 0;
				$this->set('msg', $this->spTextDir['nosuccessnote']);
			}			
			
			// if $matches[1] is not empty
			if (!empty($matches[1])) {
				$this->set('msg', $matches[1]);
				$status = 1;
				
				// to update the rank of directory
				$sql = "update directories set rank=rank+1 where id=".$submitInfo['dir_id'];
				$this->db->query($sql);				
			}
			
			
			$sql = "select id from dirsubmitinfo where website_id={$submitInfo['website_id']} and directory_id={$submitInfo['dir_id']}";
			$subInfo = $this->db->select($sql);
			if(empty($subInfo[0][id])){
				$sql = "insert into dirsubmitinfo(website_id,directory_id,status,submit_time) values({$submitInfo['website_id']}, {$submitInfo['dir_id']}, $status,".mktime().")";				
			}else{
				$sql = "update dirsubmitinfo set status=$status,submit_time=".mktime()." where id={$subInfo[0][id]}";
			}
			$this->db->query($sql);			
		}		
		$this->render('directory/showsubmissionstats');
		
		$this->set('error', 0);
		$this->set('msg', '');
		
		$this->startSubmission($submitInfo['website_id']);		
	}
	
	# to skip submission
	function skipSubmission( $info ) {
		
		$info['website_id'] = intval($info['website_id']);
		$info['dir_id'] = intval($info['dir_id']);
		$sql = "Insert into skipdirectories(website_id,directory_id) values({$info['website_id']}, {$info['dir_id']})";
		$this->db->query($sql);		
		$this->startSubmission($info['website_id']);
	}
	
	# to unskip submission
	function unSkipSubmission( $skipId ) {
		
		$skipId = intval($skipId);
		$sql = "delete from skipdirectories where id=$skipId";
		$this->db->query($sql);
	}
	
	# to get all skipped directories
	function __getAllSkippedDir($websiteId){
		
		$websiteId = intval($websiteId);
		$dirList = array();
		$sql = "select directory_id from skipdirectories where website_id=$websiteId";
		$list = $this->db->select($sql);
		if(count($list) > 0){
			foreach($list as $listInfo){
				$dirList[] = $listInfo['directory_id'];
			}
		}
		
		return $dirList;
	}
	
	# func to show Skipped Directories
	function showSkippedDirectories($searchInfo=''){
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		$this->set('onChange', "scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=skipped')");		
		
		$conditions = empty ($websiteId) ? "" : " and ds.website_id=$websiteId";
		$pageScriptPath = 'directories.php?sec=skipped&website_id='.$websiteId;
		$this->set('searchInfo', $searchInfo); 

		// search for name
		if (!empty($searchInfo['search_name'])) {
			$conditions .= " and d.submit_url like '%".addslashes($searchInfo['search_name'])."%'";
			$pageScriptPath .= "&search_name=" . $searchInfo['search_name'];
		}
				
		$sql = "select ds.* ,d.domain,d.pagerank, d.submit_url
		from skipdirectories ds,directories d where ds.directory_id=d.id $conditions order by id desc,d.domain";
								
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;						
								
		$reportList = $this->db->select($sql);
		
		$this->set('list', $reportList);
		$this->set('pageNo', $_GET['pageno']);
		$this->set('websiteId', $websiteId);
		$this->render('directory/skippeddirs');	
	}
	
	# func to show submision reports
	function showSubmissionReports($searchInfo=''){
		
		$userId = isLoggedIn();		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		$this->set('onChange', "scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=reports')");		
		
		$conditions = empty ($websiteId) ? "" : " and ds.website_id=$websiteId";
		$conditions .= empty ($searchInfo['active']) ? "" : " and ds.active=".($searchInfo['active']=='pending' ? 0 : 1);		

		$pageScriptPath = 'directories.php?sec=reports&website_id='.$websiteId.'&active='.$searchInfo['active'];
		$this->set('searchInfo', $searchInfo);
		
		// search for name
		if (!empty($searchInfo['search_name'])) {
			$conditions .= " and d.submit_url like '%".addslashes($searchInfo['search_name'])."%'";
			$pageScriptPath .= "&search_name=" . $searchInfo['search_name'];
		}
		
		$sql = "select ds.* ,d.domain,d.pagerank, d.submit_url from dirsubmitinfo ds,directories d 
		where ds.directory_id=d.id $conditions order by submit_time desc,d.domain";
								
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
								
		$reportList = $this->db->select($sql);
		$this->set('pageScriptPath', $pageScriptPath);
		$this->set('pageNo', $_GET['pageno']);
		$this->set('activeVal', $searchInfo['active']);
		$this->set('list', $reportList);
		$this->render('directory/directoryreport');	
	}
	
	function changeConfirmStatus($dirInfo){
		
		$dirInfo['id'] = intval($dirInfo['id']);
		$status = ($dirInfo['confirm']=='Yes') ? 0 : 1;
		$sql = "Update dirsubmitinfo set status=$status where id=".$dirInfo['id'];
		$this->db->query($sql);
	}
	
	function showConfirmStatus($id){
		
		$id = intval($id);
		$sql = "select status from dirsubmitinfo where id=".$id;		
		$statusInfo = $this->db->select($sql, true);
		
		$confirm = empty($statusInfo['status']) ? "No" : "Yes";
        $confirmId = "confirm_".$id;
        $confirmLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', '$confirmId', 'sec=changeconfirm&id=$id&confirm=$confirm')\">$confirm</a>";
        
        print $confirmLink;
	}
	
	function checkSubmissionStatus($dirInfo){

		$dirInfo['id'] = intval($dirInfo['id']);
		$sql = "select ds.* ,d.domain,d.search_script,w.url
					from dirsubmitinfo ds,directories d,websites w 
					where ds.directory_id=d.id and ds.website_id=w.id 
					and ds.id=". $dirInfo['id'];
		$statusInfo = $this->db->select($sql, true);
		
		$searchUrl = (preg_match('/\/$/', $statusInfo['domain'])) ? $statusInfo['domain'].$statusInfo['search_script'] : $statusInfo['domain']."/".$statusInfo['search_script'];
		$keyword = formatUrl($statusInfo['url']);
		$searchUrl = str_replace('[--keyword--]', urlencode($keyword), $searchUrl);
		
		$ret = $this->spider->getContent($searchUrl);
		if(empty($ret['error'])){
			if(stristr($ret['page'], 'href="'.$statusInfo['url'].'"')){
				return 1;
			}elseif(stristr($ret['page'], "href='".$statusInfo['url']."'")){
				return 1;
			}elseif(stristr($ret['page'], 'href='.$statusInfo['url'])){
				return 1;
			}
		}
		return 0;
	}
	
	function updateSubmissionStatus($dirId, $status){
		$status = intval($status);
		$dirId = intval($dirId);
		$sql = "Update dirsubmitinfo set active=$status where id=".$dirId;
		$this->db->query($sql);
	}
	
	function showSubmissionStatus($id){
		
		$id = intval($id);
		$sql = "select active from dirsubmitinfo where id=".$id;		
		$statusInfo = $this->db->select($sql, true);
        
        print empty($statusInfo['active']) ? $this->spTextDir["Pending"] : $this->spTextDir["Approved"];
	}
	
	function checkSubmissionReports( $searchInfo ) {
		
		$userId = isLoggedIn();		
		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$this->set('websiteNull', true);
		$this->set('onClick', "scriptDoLoadPost('directories.php', 'search_form', 'subcontent', '&sec=checksub')");
		
		$this->render('directory/checksubmission');
	}
	
	function generateSubmissionReports( $searchInfo ){
		
		$searchInfo['website_id'] = intval($searchInfo['website_id']);
		if(empty($searchInfo['website_id'])) {
			echo "<script>scriptDoLoad('directories.php', 'content', 'sec=checksub');</script>";
			return;
		}		
				
		$sql = "select ds.* ,d.domain
								from dirsubmitinfo ds,directories d 
								where ds.directory_id=d.id  
								and ds.website_id={$searchInfo['website_id']} and ds.active=0   
								order by submit_time";
		$reportList = $this->db->select($sql);		
		$this->set('list', $reportList);
		$this->render('directory/generatesubmission');
	}
	
	function deleteSubmissionReports($dirSubId){
		
		$dirSubId = intval($dirSubId);
		$sql = "delete from dirsubmitinfo where id=$dirSubId";
		$this->db->query($sql);
		
		echo "<script>scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=reports');</script>";
	}
	
	# function to show featured directories
	function showFeaturedSubmission($info="") {
	    $dirList = $this->getAllFeaturedDirectories();
	    $this->set('list', $dirList);    
	    /*if (empty($info['dir_id'])) {
	        $selDirInfo = $dirList[1];
	        $selDirId = $selDirInfo['id'];
	    } else {
	        $selDirId = intval($info['dir_id']); 
	        $selDirInfo = $dirList[$selDirId];   
	    }
	    $this->set('selDirId', $selDirId);
	    $this->set('selDirInfo', $selDirInfo);*/ 	    
		$this->render('directory/featuredsubmission');
	}	
	
	# function to get all features directories
    function getAllFeaturedDirectories() {
		$sql = "SELECT * FROM featured_directories where status=1 order by  google_pagerank DESC";		
		$list = $this->db->select($sql);
		$dirList = array();
		foreach ($list as $listInfo) {
		    $dirList[$listInfo['id']] = $listInfo;
		}
		return $dirList;
	}
	
	# func to get all directories
	function getAllDirectories($searchInfo=array()) {
		$sql = "SELECT * FROM directories ";
		$i = 0;
		foreach($searchInfo as $col => $value){
			$and = ($i++) ? "and" : "where";
			$sql .= " $and $col='$value'";
		}
		$sql .= "order by id";		
		$dirList = $this->db->select($sql);
		
		return $dirList;
	}
	
	# func to get dir info
	function getDirectoryInfo($dirId) {
		
		$dirId = intval($dirId);
		$sql = "SELECT * FROM directories where id=$dirId";		
		$dirInfo = $this->db->select($sql, true);		
		return $dirInfo;
	}
	
	# func to show directory manager
	function showDirectoryManager($info=''){		
		$info = sanitizeData($info);
		$info['stscheck'] = isset($info['stscheck']) ? intval($info['stscheck']) : 1;
		$capcheck = isset($info['capcheck']) ? (($info['capcheck'] == 'yes') ? 1 : 0 ) : "";  
		$sql = "SELECT *,l.lang_name FROM directories d,languages l where d.lang_code=l.lang_code and working='{$info['stscheck']}'";		
		if(!empty($info['dir_name'])) $sql .= " and domain like '%".addslashes($info['dir_name'])."%'";
		if($info['capcheck'] != '') $sql .= " and is_captcha='$capcheck'";
		
		// check for page rank
		if(isset($info['pagerank']) && ($info['pagerank'] != '')) {
			$prMax = intval($info['pagerank']) + 0.5;
			$prMin = intval($info['pagerank']) - 0.5;
			$sql .= " and pagerank<$prMax and pagerank>=$prMin";
		}
		
		if (!empty($info['langcode'])) { $info['lang_code'] = $info['langcode']; }
		if(!empty($info['lang_code'])) $sql .= " and d.lang_code='".addslashes($info['lang_code'])."'";
		$sql .= " order by id";		
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pageScriptPath = 'directories.php?sec=directorymgr&dir_name='.urlencode($info['dir_name'])."&stscheck={$info['stscheck']}&capcheck=".$info['capcheck'];
		$pageScriptPath .= "&pagerank=".$info['pagerank']."&langcode=".$info['lang_code'];
		$pagingDiv = $this->paging->printPages($pageScriptPath);		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		
		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		$captchaList = array(
			$_SESSION['text']['common']['Yes'] => 'yes',
			$_SESSION['text']['common']['No'] => 'no',
		);
		
		$langCtrler = New LanguageController();
		$langList = $langCtrler->__getAllLanguages();
		$this->set('langList', $langList);
		
		$this->set('statusList', $statusList);
		$this->set('captchaList', $captchaList);				
		$dirList = $this->db->select($sql);		
		$this->set('list', $dirList);
		$this->set('info', $info);
		$this->set('ctrler', $this);
		$this->render('directory/list');
	}
	
	# func to change status of directory
	function changeStatusDirectory($dirId, $status, $printLink=false){
		
	    $status = intval($status);
		$dirId = intval($dirId);
		$sql = "update directories set working=$status where id=$dirId";
		$this->db->query($sql);
		
		if($printLink){
			echo $this->getStatusLink($dirId, $status);
		}
	}
	
	# func to show directory check interface
	function showCheckDirectory() {		
		
		$this->render('directory/showcheckdir');
	}
	
	# function to start directory check
	function startDirectoryCheckStatus($info=''){
		
		$searchInfo = array();
		if(isset($info['stscheck']) && ($info['stscheck'] != '')){
			$searchInfo = array(
				'working' => $info['stscheck'],
			);
		}
		
		$dirList = $this->getAllDirectories($searchInfo);
				
		$this->set('dirList', $dirList);
		$this->render('directory/dirstatusgenerator');
	}
	
	# func to check directories active or not 
	function checkDirectoryStatus($dirId, $nodebug=0) {

		$dirId = intval($dirId);
		$dirInfo = $this->getDirectoryInfo($dirId);
		$active = 0;
		$captcha = 0;
		$spider = new Spider(); 
		$ret = $spider->getContent(addHttpToUrl($dirInfo['submit_url']));
		$prUpdate = '';
		$searchUpdate = '';
		$extraValUpdate = '';
		
		if(empty($ret['error']) && !empty($ret['page'])) {								
			$page = $ret['page'];
						
			$matches = $this->isCategoryExists($page, $dirInfo['category_col']);			
			$active = empty($matches[0]) ? 0 : 1;
			
			$captcha = stristr($page, $dirInfo['captcha_script']) ? 1 : 0;
			
			// to check search script
			if (stristr($page, 'name="search"')) {
				$searchUpdate = ",search_script='index.php?search=[--keyword--]'";			    
			}
			
			// to check  the value of the LINK_TYPE if phpld directory
			if (($dirInfo['script_type_id'] == 1) && preg_match('/name="LINK_TYPE" value="(\d)"/s', $page)) {
			    $subject = array('LINK_TYPE=reciprocal', 'LINK_TYPE=normal', 'LINK_TYPE=free');
			    $replace = array('reciprocal=1&LINK_TYPE=1', 'LINK_TYPE=2', 'LINK_TYPE=3');
			    $dirInfo['extra_val'] = str_replace($subject, $replace, $dirInfo['extra_val']);
				$extraValUpdate = ",extra_val='{$dirInfo['extra_val']}'";	    
			}
			
			if ($this->checkPR) {				
				include_once(SP_CTRLPATH."/rank.ctrl.php");
				$rankCtrler = New RankController();
				$rankInfo = $rankCtrler->__getMozRank(array($dirInfo['domain']));
				$pagerank = !empty($rankInfo[0]) ? $rankInfo[0] : 0;
				$prUpdate = ",pagerank=$pagerank";
			}
		}
		
		$sql = "update directories set working=$active,is_captcha=$captcha,checked=1 $prUpdate $searchUpdate $extraValUpdate where id=$dirId";
		$this->db->query($sql);
		
		if($nodebug){			
			$captchaLabel = $captcha ? $_SESSION['text']['common']['Yes'] : $_SESSION['text']['common']['No'];
			?>
			<script type="text/javascript">
				document.getElementById('captcha_<?php echo $dirId?>').innerHTML = '<?php echo $captchaLabel?>';
			</script>
			<?php
			if ($this->checkPR) {
				?>
				<script type="text/javascript">
					document.getElementById('pr_<?php echo $dirId?>').innerHTML = '<?php echo $pagerank?>';
				</script>
				<?php
			}
			echo $this->getStatusLink($dirId, $active);
		}else{
			echo "<p class='note notesuccess'>Saved status of directory <b>{$dirInfo['domain']}</b>.....</p>";
		}
	}
	
	# func to get status link
	function getStatusLink($dirId, $status){
		if($status){
			$statLabel = "Active";
			$statVal = 0;
		}else{
			$statLabel = "Inactive";
			$statVal = 1;
		}
		if (SP_DEMO) {
			$statusLink = scriptAJAXLinkHref('demo', "", "", $_SESSION['text']['common'][$statLabel]);
		} else {			
			$statusLink = scriptAJAXLinkHref('directories.php', 'status_'.$dirId, "sec=dirstatus&dir_id=$dirId&status=$statVal", $_SESSION['text']['common'][$statLabel]);
		}
		
		return $statusLink;
	}

	# to get total directory submission info
	function __getTotalSubmitInfo($websiteId, $activeCheck=false){
		$sql = "select count(*) count from dirsubmitinfo where website_id=$websiteId";
		if($activeCheck) $sql .= " and active=1";
		
		$countInfo = $this->db->select($sql, true);
		return empty($countInfo['count']) ? 0 : $countInfo['count']; 
	}
	
	# function to log submission data
	function logSubmissionResult($content, $dirId, $websiteId) {
		
		$filename = SP_TMPPATH."/subres_web".$websiteId."_dir".$dirId.".html";
		$fp = fopen($filename, 'w');
		fwrite($fp, $content);
		fclose($fp);
		
	}
	
	# function to get directory script type meta info
	function getDirectoryScriptMetaInfo($id) {
	    $id = empty($id) ? 1 : $id;	    
	    $sql = "SELECT * FROM di_directory_meta where id=$id";
	    $scriptInfo = $this->db->select($sql, true); 
	    return $scriptInfo;
	}
}
?>