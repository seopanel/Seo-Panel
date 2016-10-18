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

# class defines all website controller functions
class WebsiteController extends Controller{

	# func to show websites
	function listWebsites($info=''){		
		
		$userId = isLoggedIn();		
		$info['pageno'] = intval($info['pageno']);
		$pageScriptPath = 'websites.php?stscheck=';
		$pageScriptPath .= isset($info['stscheck']) ? $info['stscheck'] : "select";
		
		// if admin add user filter
		if(isAdmin()){
			$sql = "select w.*,u.username from websites w,users u where u.id=w.user_id";
			$this->set('isAdmin', 1);
			
			// if user id is not empty
			if (!empty($info['userid'])) {
				$sql .= " and w.user_id=".intval($info['userid']);
				$pageScriptPath .= "&userid=" . intval($info['userid']);
			}
			
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			
		}else{
			$sql = "select * from websites w where user_id=$userId";	
		}

		// search for user name
		if (!empty($info['search_name'])) {
			$sql .= " and (w.name like '%".addslashes($info['search_name'])."%'
			or w.url like '%".addslashes($info['search_name'])."%')";
			$pageScriptPath .= "&search_name=" . $info['search_name'];
		}
		
		// if status set
		if (isset($info['stscheck']) && $info['stscheck'] != 'select') {
			$info['stscheck'] = intval($info['stscheck']);
			$sql .= " and w.status='{$info['stscheck']}'";
		}

		$sql .= " order by w.name";
		$this->set('userId', empty($info['userid']) ? 0 : $info['userid']);		
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath);		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;

		$statusList = array(
			$_SESSION['text']['common']['Active'] => 1,
			$_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);
				
		$websiteList = $this->db->select($sql);	
		$this->set('pageNo', $info['pageno']);		
		$this->set('list', $websiteList);
		$this->render('website/list');
	}

	# func to get all Websites
	function __getAllWebsites($userId = '', $isAdminCheck = false, $searchName = '') {
		$sql = "select * from websites where status=1";
		if(!$isAdminCheck || !isAdmin() ){
			if(!empty($userId)) $sql .= " and user_id=" . intval($userId);
		} 
		
		// if search string is not empty
		if (!empty($searchName)) {
			$sql .= " and (name like '%".addslashes($searchName)."%' or url like '%".addslashes($searchName)."%')";
		}
		
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}
	
	# func to get all Websites
	function __getCountAllWebsites($userId='', $statusCheck = true, $statusVal = 1){

		$sql = "select count(*) count from websites where 1=1";
		$sql .= $statusCheck ? " and status=" . $statusVal : "";
		
		if (!empty($userId)) {
			$sql .= " and user_id=" . intval($userId);
		}
		
		$countInfo = $this->db->select($sql, true);
		$count = empty($countInfo['count']) ? 0 : $countInfo['count']; 
		return $count;
	}
	
	# func to get all Websites having active keywords
	function __getAllWebsitesWithActiveKeywords($userId='', $isAdminCheck=false){
		$sql = "select w.* from websites w,keywords k where w.id=k.website_id and w.status=1 and k.status=1";
		if(!$isAdminCheck || !isAdmin() ){
			if(!empty($userId)) $sql .= " and user_id=" . intval($userId);
		} 
		
		$sql .= " group by w.id order by w.name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}

	# func to change status
	function __changeStatus($websiteId, $status){
		
		$websiteId = intval($websiteId);
		$sql = "update websites set status=$status where id=$websiteId";
		$this->db->query($sql);
		
		$sql = "update keywords set status=$status where website_id=$websiteId";
		$this->db->query($sql);
	}

	# func to delete website
	function __deleteWebsite($websiteId){
		
		$websiteId = intval($websiteId);
		$sql = "delete from websites where id=$websiteId";
		$this->db->query($sql);
		
		# delete all keywords under this website
		$sql = "select id from keywords where website_id=$websiteId";
		$keywordList = $this->db->select($sql);
		$keywordCtrler = New KeywordController();
		foreach($keywordList as $keywordInfo){
			$keywordCtrler->__deleteKeyword($keywordInfo['id']);
		}
		
		# remove rank results
		$sql = "delete from rankresults where website_id=$websiteId";
		$this->db->query($sql);
		
		# remove backlink results
		$sql = "delete from backlinkresults where website_id=$websiteId";
		$this->db->query($sql);
		
		# remove saturation results
		$sql = "delete from saturationresults where website_id=$websiteId";
		$this->db->query($sql);
		
		# remove site auditor results		
		$sql = "select id from auditorprojects where website_id=$websiteId";
		$info = $this->db->select($sql, true);
		if (!empty($info['id'])) {
		    $auditorObj = $this->createController('SiteAuditor');
		    $auditorObj->__deleteProject($info['id']);
		}
		
		#remove directory results
		$sql = "delete from dirsubmitinfo where website_id=$websiteId";
		$this->db->query($sql);
		$sql = "delete from skipdirectories where website_id=$websiteId";
		$this->db->query($sql);
		    
	}

	function newWebsite($info=''){
		
		$userId = isLoggedIn();
		if(!empty($info['check']) && !$this->__getCountAllWebsites($userId)){
			$this->set('msg', $this->spTextWeb['plscrtwebsite'].'<br>Please <a href="javascript:void(0);" onclick="scriptDoLoad(\'websites.php\', \'content\')">'.strtolower($_SESSION['text']['common']['Activate']).'</a> '.$this->spTextWeb['yourwebalreday']);
		}
		
		# Validate website count
		if (!$this->validateWebsiteCount($userId)) {
			$this->set('validationMsg', $this->spTextWeb['Your website count already reached the limit']);
		}
		
		# get all users
		if(isAdmin()){
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);  			
			$this->set('isAdmin', 1);
		}
		
		$this->render('website/new');
	}

	function __checkName($name, $userId){
		
		$sql = "select id from websites where name='".addslashes($name)."' and user_id=$userId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function __checkWebsiteUrl($url, $websiteId=0){		
		$sql = "select id from websites where url='".addslashes($url)."'";
		$sql .= $websiteId ? " and id!=$websiteId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function createWebsite($listInfo, $apiCall = false){
		
		// add user id when using as admin or calling api
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['userid']) ? isLoggedIn() : $listInfo['userid'];	
		} else {
			$userId = isLoggedIn();
		}
		
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkBlank($listInfo['url']));
		$listInfo['url'] = addHttpToUrl($listInfo['url']);
		$statusVal = isset($listInfo['status']) ? intval($listInfo['status']) : 1;
		
		// verify the limit for the user
		if (!$this->validateWebsiteCount($userId)) {
			$this->set('validationMsg', $this->spTextWeb["Your website count already reached the limit"]);
			$this->validate->flagErr = true;
			$errMsg['limit_error'] = $this->spTextWeb["Your website count already reached the limit"];
		}
		
		// validate website creation
		if(!$this->validate->flagErr){
			if (!$this->__checkName($listInfo['name'], $userId)) {
			    if (!$this->__checkWebsiteUrl($listInfo['url'])) {
    				$sql = "insert into websites(name,url,title,description,keywords,user_id,status)
    				values('".addslashes($listInfo['name'])."','".addslashes($listInfo['url'])."','".
    				addslashes($listInfo['title'])."','".addslashes($listInfo['description'])."','".
    				addslashes($listInfo['keywords'])."', $userId, $statusVal)";
    				$this->db->query($sql);
    				
    				// if api call
    				if ($apiCall) {
    					return array('success', 'Successfully created website');
    				} else {
	    				$this->listWebsites();
	    				exit;
    				}
    				
			    } else {
			        $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
			    }
			}else{
				$errMsg['name'] = formatErrorMsg($this->spTextWeb['Website already exist']);
			}
		}		
		
		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->newWebsite($listInfo);
		}
	}

	function __getWebsiteInfo($websiteId){
		$websiteId = intval($websiteId);
		$sql = "select * from websites where id=$websiteId";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo;
	}

	function editWebsite($websiteId, $listInfo=''){		
		
		$websiteId = intval($websiteId);
		if(!empty($websiteId)){
			if(empty($listInfo)){
				$listInfo = $this->__getWebsiteInfo($websiteId);
				$listInfo['oldName'] = $listInfo['name'];
			}
			$listInfo['title'] = stripslashes($listInfo['title']);
			$listInfo['description'] = stripslashes($listInfo['description']);
			$listInfo['keywords'] = stripslashes($listInfo['keywords']);
			$this->set('post', $listInfo);
			
			# get all users
			if(isAdmin()){
				$userCtrler = New UserController();
				$userList = $userCtrler->__getAllUsers();
				$this->set('userList', $userList);  			
				$this->set('isAdmin', 1);
			}
			
			$this->render('website/edit');
			exit;
		}
		$this->listWebsites();
	}

	function updateWebsite($listInfo, $apiCall = false){
		
		// check whether admin or api calll
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['user_id']) ? isLoggedIn() : $listInfo['user_id'];	
		} else {
			$userId = isLoggedIn();
		}
		
		$listInfo['id'] = intval($listInfo['id']);
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkBlank($listInfo['url']));
		$listInfo['url'] = addHttpToUrl($listInfo['url']);
		$statusVal = isset($listInfo['status']) ? "status = " . intval($listInfo['status']) ."," : "";		
		
		// check limit
		if(!$this->validate->flagErr && !empty($listInfo['user_id'])){
			$websiteInfo = $this->__getWebsiteInfo($listInfo['id']);
			
			// if user is changed for editing
			if ($websiteInfo['user_id'] != $listInfo['user_id']) {
				
				// verify the limit for the user
				if (!$this->validateWebsiteCount($listInfo['user_id'])) {
					$this->set('validationMsg', $this->spTextWeb["Your website count already reached the limit"]);
					$this->validate->flagErr = true;
					$errMsg['limit_error'] = $this->spTextWeb["Your website count already reached the limit"];
				}	
			}
		}
		
		// verify the form
		if(!$this->validate->flagErr){

			if($listInfo['name'] != $listInfo['oldName']){
				if ($this->__checkName($listInfo['name'], $userId)) {
					$errMsg['name'] = formatErrorMsg($this->spTextWeb['Website already exist']);
					$this->validate->flagErr = true;
				}
			}
			
			if ($this->__checkWebsiteUrl($listInfo['url'], $listInfo['id'])) {
			    $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
				$this->validate->flagErr = true;
			}

			if (!$this->validate->flagErr) {
				$sql = "update websites set
						name = '".addslashes($listInfo['name'])."',
						url = '".addslashes($listInfo['url'])."',
						user_id = $userId,
						title = '".addslashes($listInfo['title'])."',
						description = '".addslashes($listInfo['description'])."',
						$statusVal
						keywords = '".addslashes($listInfo['keywords'])."'
						where id={$listInfo['id']}";
				$this->db->query($sql);
				
				// if api call
				if ($apiCall) {
					return array('success', 'Successfully updated website');
				} else {
					$this->listWebsites();
					exit;
				}
				
			}
		}
		
		// if api call
		if ($apiCall) {
			return array('error', $errMsg);
		} else {
			$this->set('errMsg', $errMsg);
			$this->editWebsite($listInfo['id'], $listInfo);
		}
		
	}
	
	# func to crawl meta data of a website
	public static function crawlMetaData($websiteUrl, $keyInput='', $pageContent='', $returVal=false) {
	    if (empty($pageContent)) {
    		if(!preg_match('/\w+/', $websiteUrl)) return;
    		$websiteUrl = addHttpToUrl($websiteUrl);
    		$spider = New Spider();
    		$ret = $spider->getContent($websiteUrl);
	    } else {
	        $ret['page'] = $pageContent;
	        $metaInfo = array();
	    }
		if(!empty($ret['page'])){
			
		    if (empty($keyInput)) {
		    
    			# meta title
    			preg_match('/<TITLE>(.*?)<\/TITLE>/si', $ret['page'], $matches);
    			if(!empty($matches[1])){
    			    if ($returVal) {
    			        $metaInfo['page_title'] = $matches[1];
    			    } else {
    				    WebsiteController::addInputValue($matches[1], 'webtitle');
    			    }
    			}
    			
    			# meta description
    			preg_match('/<META.*?name="description".*?content="(.*?)"/si', $ret['page'], $matches);		
    			if(empty($matches[1])){
    				preg_match("/<META.*?name='description'.*?content='(.*?)'/si", $ret['page'], $matches);			
    			}
    			if(empty($matches[1])){
    				preg_match('/<META content="(.*?)" name="description"/si', $ret['page'], $matches);					
    			}
    			if(!empty($matches[1])){
    			    if ($returVal) {
    			        $metaInfo['page_description'] = $matches[1];
    			    } else {
    				    WebsiteController::addInputValue($matches[1], 'webdescription');
    			    }
    			}
		    }
			
			# meta keywords
			preg_match('/<META.*?name="keywords".*?content="(.*?)"/si', $ret['page'], $matches);		
			if(empty($matches[1])){
				preg_match("/<META.*?name='keywords'.*?content='(.*?)'/si", $ret['page'], $matches);			
			}
			if(empty($matches[1])){
				preg_match('/<META content="(.*?)" name="keywords"/si', $ret['page'], $matches);			
			}
			
			if(!empty($matches[1])){
    	        if ($returVal) {
    			    $metaInfo['page_keywords'] = $matches[1];
    			} else {
				    WebsiteController::addInputValue($matches[1], 'webkeywords');
    			}
			}			
		}
		return $metaInfo; 
	}
	
	public static function addInputValue($value, $col) {

		$value = removeNewLines($value);
		?>
		<script type="text/javascript">
			document.getElementById('<?php echo $col;?>').value = '<?php echo str_replace("'", "\'", $value);?>';
		</script>
		<?php
	}
	
	function showImportWebsites() {

		$userId = isLoggedIn();
		
		# get all users
		if(isAdmin()){
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);
			$this->set('isAdmin', 1);
		}

		// Check the user website count for validation
		if (!isAdmin()) {
			$this->setValidationMessageForLimit($userId);
		}
		
		$this->set('delimiter', ',');
		$this->set('enclosure', '"');
		$this->set('escape', '\\');
		$this->render('website/importwebsites');
	}

	# function to set validation message for the limit
	function setValidationMessageForLimit($userId) {
	
		// Check the user website count for validation
		$userTypeCtrlr = new UserTypeController();
		$userWebsiteCount = $this->__getCountAllWebsites($userId, false);
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		$validCount = $userTypeDetails['websitecount'] - $userWebsiteCount;
		$validCount = $validCount > 0 ? $validCount : 0;
		$validationMsg = str_replace("[websitecount]", "<b>$validCount</b>", $this->spTextWeb['You can add only websitecount websites more']);
		$this->set('validationMsg', $validationMsg);
		return $validationMsg;
			
	}
	
	function importWebsiteFromCsv($info) {
		
		// if csv file is not uploaded
		if (empty($_FILES['website_csv_file']['name'])) {
			print "<script>alert('".$this->spTextWeb['Please enter CSV file']."')</script>";
			return False;
		}
		
		$userId = isAdmin() ? intval($info['userid']) : isLoggedIn();
		
		$resultInfo = array(
			'total' => 0,
			'valid' => 0,
			'invalid' => 0,
		);
		$count = 0;
				 
		// process file upload option
		$fileInfo = $_FILES['website_csv_file'];
		if (!empty($fileInfo['name']) && !empty($userId)) {
			if ($fileInfo["type"] == "text/csv") {
				$targetFile = SP_TMPPATH . "/".$fileInfo['name'];
				if(move_uploaded_file($fileInfo['tmp_name'], $targetFile)) {

					$delimiterChar = empty($info['delimiter']) ? ',' : $info['delimiter'];
					$enclosureChar = empty($info['enclosure']) ? '"' : $info['enclosure'];
					$escapeChar = empty($info['escape']) ? '\\' : $info['escape'];
					
					// open file read through csv file
					if (($handle = fopen($targetFile, "r")) !== FALSE) {
					
						// loop through the data row
						while (($websiteInfo = fgetcsv($handle, 4096, $delimiterChar, $enclosureChar, $escapeChar)) !== FALSE) {
							if (empty($websiteInfo[0])) continue;
							$count++;
						}
					
						fclose($handle);
					}
					
					// Check the user website count for validation
					if (!$this->validateWebsiteCount($userId, $count)) {
						$validationMag = strip_tags($this->setValidationMessageForLimit($userId));
						print "<script>alert('$validationMag')</script>";
						return False;
					}
					
					// open file read through csv file
					if (($handle = fopen($targetFile, "r")) !== FALSE) {

						// loop through the data row
						while (($websiteInfo = fgetcsv($handle, 4096, $delimiterChar, $enclosureChar, $escapeChar)) !== FALSE) {
							if (empty($websiteInfo[0])) continue;
							$status = $this->importWebsite($websiteInfo, $userId);
							$resultInfo[$status] += 1;
							$resultInfo['total'] += 1;
						}
						
						fclose($handle);
					}					
				}
			}
		}


		$text = "<p class=\'note\' id=\'note\'><b>Website import process started. It will take some time depends on the number of websites needs to be imported!</b></p><div id=\'subcontmed\'></div>";
		print "<script type='text/javascript'>parent.document.getElementById('import_website_div').innerHTML = '$text';</script>";
		print "<script>parent.showLoadingIcon('subcontmed', 0)</script>";
		$spText = $_SESSION['text'];
		$resText = '<table width="40%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab" align="center">'.
		'<tr><td class="topheader" colspan="10">Import Summary</td></tr>'.
				'<tr><th class="leftcell">'.$spText['common']['Total'].':</th><td>'.$resultInfo['total'].'</td><th>Valid:</th><td>'.$resultInfo['valid'].'</td></tr>'.
				'<tr><th>Invalid:</th><td>'.$resultInfo['invalid'].'</td><th>&nbsp;</th><td>&nbsp;</td></tr>'.
		'</table>';
		echo "<script type='text/javascript'>parent.document.getElementById('subcontmed').innerHTML = '$resText'</script>";
        echo "<script type='text/javascript'>parent.document.getElementById('note').style.display='none';</script>";
	}
	
	function importWebsite($wInfo, $userId) {
		$status = 'invalid';
		
		if (!empty($wInfo[0]) && !empty($wInfo[1])) {

			$listInfo['name'] = trim($wInfo[0]);
			$listInfo['url'] = trim($wInfo[1]);
			$listInfo['title'] = $wInfo[2] ? trim($wInfo[2]) : "";
			$listInfo['description'] = $wInfo[3] ? trim($wInfo[3]) : "";
			$listInfo['keywords'] = $wInfo[4] ? trim($wInfo[4]) : "";
			$listInfo['status'] = intval($wInfo[5]);
			$listInfo['userid'] = $userId;
			$return = $this->createWebsite($listInfo, true);
			
			if ($return[0] == 'success') {
				$status = "valid";
			}
		}
		
		return $status;
	}
	
	// Function to check / validate the user type website count
	function validateWebsiteCount($userId, $newCount = 1) {
		$userCtrler = new UserController();

		// if admin user id return true
		if ($userCtrler->isAdminUserId($userId)) {
			return true;
		}
		
		$userTypeCtrlr = new UserTypeController();
		$userWebsiteCount = $this->__getCountAllWebsites($userId, false);
		$userWebsiteCount += $newCount;
		$userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
		
		// check whether count greater than limit
		if ($userWebsiteCount <= $userTypeDetails['websitecount']) {
			return true;	
		} else {
			return false;	
		}
	}
		
}
?>