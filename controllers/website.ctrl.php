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
	function listWebsites($info=[]) {		
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
		
		$propertyList = $this->__getAllAnalyticProperties(TRUE);
		$this->set('propertyList', $propertyList);
				
		$websiteList = $this->db->select($sql);	
		$this->set('pageNo', $info['pageno']);		
		$this->set('list', $websiteList);
		$this->render('website/list');
	}

	# func to get all Websites
	function __getAllWebsites($userId = '', $isAdminCheck = false, $searchName = '') {
		$sql = "select * from websites where status=1";
		if(!$isAdminCheck || !isAdmin() ) {
			if(!empty($userId)) {
				$sql .= $this->getWebsiteUserAccessCondition($userId, "id", "user_id");
			}
		} 
		
		// if search string is not empty
		if (!empty($searchName)) {
			$sql .= " and (name like '%".addslashes($searchName)."%' or url like '%".addslashes($searchName)."%')";
		}
		
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}
	
	function __getUserWebsites($userId, $searchInfo=[]) {
	    $cond = "user_id=".intval($userId);
	    $cond .= isset($searchInfo['status']) ? " and status=".intval($searchInfo['status']) : "";
	    $cond .= isset($searchInfo['search']) ? " and url like '%".addslashes($searchInfo['search'])."%'" : "";
        return $this->dbHelper->getAllRows('websites', $cond);
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
			if(!empty($userId)) {
				$sql .= $this->getWebsiteUserAccessCondition($userId);
			}
		} 
		
		$sql .= " group by w.id order by w.name";
		$websiteList = $this->db->select($sql);
		return $websiteList;
	}
	
	function getWebsiteUserAccessCondition($userId, $col="w.id", $userCol = "w.user_id") {
		if (SP_CUSTOM_DEV) {
			$userCtrl = new UserController();
			$webAccessList = $userCtrl->getUserWebsiteAccessList($userId);
			$webIdList = array_keys($webAccessList);
			$webIdList = !empty($webIdList) ? $webIdList : array(0);
			$cond = " and ($userCol=" . intval($userId) . " or $col in (".implode($webIdList, ',')."))";
		} else {
			$cond = " and $userCol=" . intval($userId);
		}
		
		return $cond;
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

	function newWebsite($info=[]) {
		$userId = isLoggedIn();
		
		// check whether user have only readonly website access 
		if (SP_CUSTOM_DEV) {
		    redirectUrlByScript(SP_WEBPATH . "/admin-panel.php");
		    exit;
		}
		
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
		
		$propertyList = $this->__getAllAnalyticProperties(TRUE);
		$this->set('propertyList', $propertyList);
		$this->set('editAction', 'create');
		$this->render('website/edit_website');
	}

	function __checkName($name, $userId, $websiteId=FALSE) {
	    $userId = intval($userId);
	    $websiteId = intval($websiteId);
		$sql = "select id 
                from websites
                where name='".addslashes($name)."' and user_id=$userId";
		$sql .= !empty($websiteId) ? " and id!=$websiteId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function __checkWebsiteUrl($url, $websiteId=0) {
	    $websiteId = intval($websiteId);
		$sql = "select id from websites where url='".addslashes($url)."'";
		$sql .= $websiteId ? " and id!=$websiteId" : "";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false :  $listInfo['id'];
	}

	function createWebsite($listInfo, $apiCall=false) {
	    $listInfo['url'] = trim($listInfo['url']);
	    $listInfo['name'] = trim($listInfo['name']);
		
		// add user id when using as admin or calling api
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['userid']) ? isLoggedIn() : intval($listInfo['userid']);	
		} else {
			$userId = isLoggedIn();
		}

		$errMsg = [];
		$listInfo['name'] = strip_tags($listInfo['name']);
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkUrl($listInfo['url']));
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
			        $listInfo['title'] = substr($listInfo['title'], 0, 100);
			        $listInfo['description'] = substr($listInfo['description'], 0, 500);
			        $listInfo['keywords'] = substr($listInfo['keywords'], 0, 500);
    				$sql = "insert into websites(name,url,title,description,analytics_view_id,keywords,user_id,status)
    				values('".addslashes($listInfo['name'])."','".addslashes($listInfo['url'])."','".
    				addslashes($listInfo['title'])."','".addslashes($listInfo['description'])."', '".addslashes($listInfo['analytics_view_id'])."', '".
    				addslashes($listInfo['keywords'])."', $userId, $statusVal)";
    				$this->db->query($sql);
    				
    				// if api call
    				if ($apiCall) {
    					return array('success', 'Successfully created website');
    				} else {
	    				$this->listWebsites([]);
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

	function editWebsite($websiteId, $listInfo=[]) {		
		$websiteId = intval($websiteId);
		if(!empty($websiteId)){
			if(empty($listInfo)){
				$listInfo = $this->__getWebsiteInfo($websiteId);
			}
			
			$listInfo['title'] = stripslashes($listInfo['title']);
			$listInfo['description'] = stripslashes($listInfo['description']);
			$listInfo['keywords'] = stripslashes($listInfo['keywords']);
			$this->set('post', $listInfo);
			
			// get all users
			if(isAdmin()) {
				$userCtrler = New UserController();
				$userList = $userCtrler->__getAllUsers();
				$this->set('userList', $userList);  			
				$this->set('isAdmin', 1);
			}
			
			$propertyList = $this->__getAllAnalyticProperties(TRUE);
			$this->set('propertyList', $propertyList);
			
			$this->set('editAction', 'update');
			$this->render('website/edit_website');
			exit;
		}		
		
		$this->listWebsites([]);
	}

	function updateWebsite($listInfo, $apiCall=false) {
	    $listInfo['url'] = trim($listInfo['url']);
	    $listInfo['name'] = trim($listInfo['name']);
		
		// check whether admin or api calll
		if (isAdmin() || $apiCall) {
			$userId = empty($listInfo['user_id']) ? isLoggedIn() : $listInfo['user_id'];	
		} else {
			$userId = isLoggedIn();
		}
		
		$listInfo['id'] = intval($listInfo['id']);
		$listInfo['name'] = strip_tags($listInfo['name']);
		$this->set('post', $listInfo);
		$errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
		$errMsg['url'] = formatErrorMsg($this->validate->checkUrl($listInfo['url']));
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
		if(!$this->validate->flagErr) {

		    if ($this->__checkName($listInfo['name'], $userId, $listInfo['id'])) {
				$errMsg['name'] = formatErrorMsg($this->spTextWeb['Website already exist']);
				$this->validate->flagErr = true;
			}
			
			if ($this->__checkWebsiteUrl($listInfo['url'], $listInfo['id'])) {
			    $errMsg['url'] = formatErrorMsg($this->spTextWeb['Website already exist']);
				$this->validate->flagErr = true;
			}

			if (!$this->validate->flagErr) {
			    $listInfo['title'] = substr($listInfo['title'], 0, 100);
			    $listInfo['description'] = substr($listInfo['description'], 0, 500);
			    $listInfo['keywords'] = substr($listInfo['keywords'], 0, 500);
				$sql = "update websites set
						name = '".addslashes($listInfo['name'])."',
						url = '".addslashes($listInfo['url'])."',
						user_id = $userId,
						title = '".addslashes($listInfo['title'])."',
						description = '".addslashes($listInfo['description'])."',
						analytics_view_id = '".addslashes($listInfo['analytics_view_id'])."',
						$statusVal
						keywords = '".addslashes($listInfo['keywords'])."'
						where id={$listInfo['id']}";
				$this->db->query($sql);
				
				// if api call
				if ($apiCall) {
					return array('success', 'Successfully updated website');
				} else {
					$this->listWebsites([]);
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
		
		// if csv file is not uploaded
		if (mime_content_type($_FILES['website_csv_file']['tmp_name']) != 'text/plain') {
		    print "<script>alert('".$this->spTextWeb['Please enter CSV file']."')</script>";
		    return False;
		}
		
		$userId = isAdmin() ? intval($info['userid']) : isLoggedIn();
		$count = 0;
		$resultInfo = array(
			'total' => 0,
			'valid' => 0,
			'invalid' => 0,
		);
				 
		// process file upload option
		$fileInfo = $_FILES['website_csv_file'];
		if (!empty($fileInfo['name']) && !empty($userId)) {
			if ($fileInfo["type"] == "text/csv" || $fileInfo["type"] == "application/vnd.ms-excel") {
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
		    $listInfo = [];
			$listInfo['name'] = trim($wInfo[0]);
			$listInfo['url'] = trim($wInfo[1]);
			$listInfo['title'] = $wInfo[2] ? trim($wInfo[2]) : "";
			$listInfo['description'] = $wInfo[3] ? trim($wInfo[3]) : "";
			$listInfo['keywords'] = $wInfo[4] ? trim($wInfo[4]) : "";
			$listInfo['status'] = intval($wInfo[5]);
			$listInfo['analytics_view_id'] = $wInfo[6] ? trim($wInfo[6]) : "";
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

		// if limit is set and not -1
		if (isset($userTypeDetails['websitecount']) && $userTypeDetails['websitecount'] >= 0) {
			
			// check whether count greater than limit
			if ($userWebsiteCount <= $userTypeDetails['websitecount']) {
				return true;	
			} else {
				return false;	
			}
			
		} else {
			return true;
		}			
	}
	
	function showimportWebmasterToolsWebsites() {		
		$userId = isLoggedIn();
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$userCtrler = New UserController();
		
		// get all users
		if(isAdmin()){
			$userList = $userCtrler->__getAllUsers();
			$this->set('userList', $userList);
			$this->set('userSelected', empty($info['userid']) ? $userId : $info['userid']);
			$this->set('isAdmin', 1);
		} else {
			$userInfo = $userCtrler->__getUserInfo($userId);
			$this->set('userName', $userInfo['username']);
		}

		// Check the user website count for validation
		if (!isAdmin()) {
			$this->setValidationMessageForLimit($userId);
		}
		
		$this->render('website/import_webmaster_tools_websites');
	}
	
	function importWebmasterToolsWebsites($info) {
		$userId = isAdmin() ? intval($info['userid']) : isLoggedIn();
		$limitReached = false;
		$importList = array();
	
		// verify the limit for the user
		if (!$this->validateWebsiteCount($userId)) {
			showErrorMsg($this->spTextWeb["Your website count already reached the limit"]);
		}
	
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->getAllSites($userId);
		
		// check whether error occured while api call
		if (!$result['status']) {
			showErrorMsg($result['msg']);
		}
	
		// loop through website list
		foreach ($result['resultList'] as $websiteInfo) {
				
			if ($websiteInfo->permissionLevel != 'siteOwner') continue;
				
			// chekc whether website existing or not
			if (!$this->__checkWebsiteUrl($websiteInfo->siteUrl) && !$this->__checkWebsiteUrl(Spider::removeTrailingSlash($websiteInfo->siteUrl))) {
				$websiteName = formatUrl($websiteInfo->siteUrl, false);
				$websiteName = Spider::removeTrailingSlash($websiteName);
				$listInfo['name'] = $websiteName;
				$listInfo['url'] = $websiteInfo->siteUrl;
				$listInfo['title'] = $websiteName;
				$listInfo['description'] = $websiteName;
				$listInfo['keywords'] = $websiteName;
				$listInfo['status'] = 1;
				$listInfo['userid'] = $userId;
				$return = $this->createWebsite($listInfo, true);
	
				// if success, check of number of websites can be added
				if ($return[0] == 'success') {
					$importList[] = $websiteInfo->siteUrl;

					// if reached website add limit
					if (!$this->validateWebsiteCount($userId)) {
						$limitReached = true;
						break;
					}
						
				}
	
			}
				
		}
		
		// show results	
		showSuccessMsg("<b>".$this->spTextWeb["Successfully imported following websites"]."</b>:", false);
		foreach ($importList as $url) showSuccessMsg($url, false);
		
		// if website add limit reached
		if ($limitReached) {
			showErrorMsg($this->spTextWeb["Your website count already reached the limit"]);
		}		
	
	}
	
	function addToWebmasterTools($websiteId) {
		$webisteInfo = $this->__getWebsiteInfo($websiteId);
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->addWebsite($webisteInfo['url'], $webisteInfo['user_id']);
		
		// chekc whether error occured while api call
		if ($result['status']) {
			$activateUrl = "https://www.google.com/webmasters/verification/verification?tid=alternate&siteUrl=" . $webisteInfo['url'];
			$successMsg = $this->spTextWeb["Website successfully added to webmaster tools"] . ": " . $webisteInfo['url'] . "<br><br>";
			$successMsg .= "<a href='$activateUrl' target='_blank'>Click Here</a> to activate the website in webmaster tools.";
			showSuccessMsg($successMsg, false);
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}
	
	// func to list sitemaps
	function listSitemap($info, $summaryPage = false, $cronUserId=false) {
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$this->set('spTextSitemap', $this->getLanguageTexts('sitemap', $_SESSION['lang_code']));
		$this->set('spTextHome', $this->getLanguageTexts('home', $_SESSION['lang_code']));
		$this->set('spTextDirectory', $this->getLanguageTexts('directory', $_SESSION['lang_code']));
		
		$websiteList = $this->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
		$this->set('websiteId', $websiteId);
		
		$whereCond = "status=1";
		
		// check for wbsite id
		if (empty($websiteId)) {
			$wIdList = [0];
			foreach ($websiteList as $websiteInfo) $wIdList[] = $websiteInfo['id'];
			$whereCond .= " and website_id in (".implode(',', $wIdList).")";
		} else {
			$whereCond .= " and website_id=$websiteId";
		}
		
		$sitemapList = $this->dbHelper->getAllRows("webmaster_sitemaps", $whereCond);
		
		$this->set('list', $sitemapList);
		$this->set('summaryPage', $summaryPage);
		
		// if pdf export
		if ($summaryPage) {
			return $this->getViewContent('sitemap/list_webmaster_sitemap_list');
		} else {
			$this->render('sitemap/list_webmaster_sitemap_list');
		}
		
	}
	
	// func to import webmaster tools sitemaps
	function importWebmasterToolsSitemaps($websiteId, $cronJob = false) {
		$websiteId = intval($websiteId);
		$webisteInfo = $this->__getWebsiteInfo($websiteId);

		// call webmaster api
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->getAllSitemap($webisteInfo['url'], $webisteInfo['user_id']);
		
		// check whether error occured while api call
		if ($result['status']) {
			
			// change status of all sitemaps
			$dataList = array('status' => 0);
			$this->dbHelper->updateRow("webmaster_sitemaps", $dataList, " website_id=$websiteId");
			
			// loop through webmaster tools list
			foreach ($result['resultList'] as $sitemapInfo) {
				
				$dataList = array(
					'last_submitted' => date('Y-m-d H:i:s', strtotime($sitemapInfo->lastSubmitted)),
					'last_downloaded' => date('Y-m-d H:i:s', strtotime($sitemapInfo->lastDownloaded)),
					'is_pending|int' => $sitemapInfo->isPending,
					'warnings|int' => $sitemapInfo->warnings,
					'errors|int' => $sitemapInfo->errors,
					'submitted|int' => $sitemapInfo->contents[0]['submitted'],
					'indexed|int' => $sitemapInfo->contents[0]['indexed'],
					'status' => 1,
				);
				
				$rowInfo = $this->dbHelper->getRow("webmaster_sitemaps", " website_id=$websiteId and path='$sitemapInfo->path'");
				if (!empty($rowInfo['id'])) {
					$this->dbHelper->updateRow("webmaster_sitemaps", $dataList, " id=" . $rowInfo['id']);
				} else {
					$dataList['website_id|int'] = $websiteId;
					$dataList['path'] = $sitemapInfo->path;
					$this->dbHelper->insertRow("webmaster_sitemaps", $dataList);
				}
				
			}
			
			if ($cronJob) {
				echo $this->spTextWeb["Successfully sync sitemaps from webmaster tools"] . "<br>\n";
			} else {
				showSuccessMsg($this->spTextWeb["Successfully sync sitemaps from webmaster tools"], false);
			}
			
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}
	
	// func to show submit sitemap form
	function showSubmitSitemap($info) {
		$userId = isLoggedIn();
		$this->set('websiteList', $this->__getAllWebsites($userId, true));
		$this->set('websiteId', intval($info['website_id']));
		$this->set('spTextTools', $this->getLanguageTexts('seotools', $_SESSION['lang_code']));
		$this->render('sitemap/submit_sitemap');
	}
	
	// func to submit sitemap
	function submitSitemap($info) {		
		$webisteInfo = $this->__getWebsiteInfo($info['website_id']);
		$spTextWebproxy = $this->getLanguageTexts('QuickWebProxy', $_SESSION['lang_code']);		
		
		if (empty($info['sitemap_url'])) {
			showErrorMsg($spTextWebproxy["Please enter a valid url"]);
		}
		
		$info['sitemap_url'] = addHttpToUrl($info['sitemap_url']);
		
		// if website url not correct
		if (!preg_match("/". preg_quote($webisteInfo['url'], '/') ."/i", $info['sitemap_url'])) {
			showErrorMsg($spTextWebproxy["Please enter a valid url"]);
		}
		
		// call webmaster api
		$gapiCtrler = new WebMasterController();
		$result = $gapiCtrler->submitSitemap($webisteInfo['url'], $info['sitemap_url'], $webisteInfo['user_id']);
		
		// check whether error occured while api call
		if ($result['status']) {
			showSuccessMsg($this->spTextWeb["Sitemap successfully added to webmaster tools"] . ": " . $info['sitemap_url'], false);
			
			// update seo panel webmaster tool sitemaps
			$this->importWebmasterToolsSitemaps($webisteInfo['id']);
			
		} else {
			showErrorMsg($result['msg'], false);
		}
		
	}

	function deleteWebmasterToolSitemap($sitemapId) {
		$sitemapId = intval($sitemapId);
		$sitemapInfo = $this->dbHelper->getRow("webmaster_sitemaps", "id=$sitemapId");
		
		if (empty($sitemapInfo['id'])) {
			showErrorMsg("Please provide a valid sitemap id");
		}
		
		$webisteInfo = $this->__getWebsiteInfo($sitemapInfo['website_id']);
		$gapiCtrler = new WebMasterController();		
		$result = $gapiCtrler->deleteWebsiteSitemap($webisteInfo['url'], $sitemapInfo['path'], $webisteInfo['user_id']);
	
		// check whether error occured while api call
		if ($result['status']) {
			$this->dbHelper->updateRow("webmaster_sitemaps", array('status' => 0), "id=$sitemapId");
			showSuccessMsg($this->spTextWeb["Successfully deleted sitemap from webmaster tools"], false);
		} else {
			showErrorMsg($result['msg'], false);
		}
		
		$this->listSitemap(array('website_id' => $sitemapInfo['website_id']));	
	}	
	
	function syncGoogleAnalyticProperties($userId) {
	    $userId = intval($userId);
	    $analyticList = $this->dbHelper->getAllRows("analytics_properties", "user_id=$userId");
	    $propertyList = createSelectList($analyticList, "ALL", 'property_id');
	    
	    $GoogleCtrl  = new GoogleAPIController();
	    [$status, $result, $errMsg] = $GoogleCtrl->getanalyticWebsitesPropertyIds($userId);
	    if($status && !empty($result)) {
	        foreach ($result as $data) {
	            $propertyId = $data['property_id'];
	            
	            if (!empty($propertyList[$propertyId])) {
	                $propertyDbId = $propertyList[$propertyId]['id'];
	                $dataList = [
	                    'user_id' => $userId,
	                    'account_name' => $data['account_name'],
	                    'account_id' => $data['account_id'],
	                    'property_name' => $data['property_name'],
	                    'datetime_updated' => date('Y-m-d H:i:s'),
	                ];
	                
	                $this->dbHelper->updateRow('analytics_properties', $dataList, "id=$propertyDbId");
	            } else {
	                $dataList = [
	                    'user_id' => $userId,
	                    'account_name' => $data['account_name'],
	                    'account_id' => $data['account_id'],
	                    'property_name' => $data['property_name'],
	                    'property_id' => $data['property_id'],
	                ];
	                
	                $this->dbHelper->insertRow('analytics_properties', $dataList);
	            }
	        }
	    }
	    
	    return [$status, $errMsg];
	}
	
	function fetchGoogleAnalyticProperties() {
	    $response = ['status' => 0, 'data' => [], 'msg' => "API Error"];
	    $userId = isLoggedIn();
	    
	    // sync google analytics properties using the connectin
	    [$status, $errMsg] = $this->syncGoogleAnalyticProperties($userId);
	    if ($status) {
	        $propertyList = $this->__getAllAnalyticProperties(TRUE);
	        $response['data'] = $propertyList;
	        $response['status'] = TRUE;
	    } else {
	        $response['msg'] = $errMsg;
	    }
	    
	    return $response;
	}
	
	function __getUserAnalyticProperties($userId) {
	    $userId = intval($userId);
	    $sql = "SELECT *
                FROM analytics_properties
                WHERE user_id= $userId ORDER BY account_name, property_name, property_id";
	    $list = $this->db->select($sql);
	    return $list;
	}
	
	function __getAllAnalyticProperties($asSelectList=FALSE) {
	    $sql = "SELECT *
                FROM analytics_properties
                ORDER BY account_name, property_name, property_id";
	    $list = $this->db->select($sql);
	    
	    // if return as property list
	    if ($asSelectList) {
	        $propertyList = [];
	        foreach ($list as $item) {
	            if (empty($item['view_id'])) {
	                $propertyList[$item['property_id']] = $item['account_name'].' - '.$item['property_name'] ."({$item['property_id']})";
	            }
	        }
	        
	        return $propertyList;
	    } else {
	        return $list;
	    }
	}
}
?>