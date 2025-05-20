<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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

# class defines all seo plugins controller functions
class SeoPluginsController extends Controller{
	
	var $layout = 'ajax';
	var $info = array();
	var $pluginText = "";
	var $pluginCtrler = FALSE;
	var $pluginPath;
	var $pluginId;
	var $pluginViewPath;
	var $pluginWebPath;
	var $pluginImagePath;
	var $pluginCssPath;
	var $pluginJsPath;
	var $pluginScriptUrl;
	var $pluginDirName;
	
	# function to manage seo plugins
	function manageSeoPlugins($info, $method='get', $cronJob = false) {
	    $info['pid'] = intval($info['pid']);
		
		// check for plugin access level for user, if not admin
		if (!isAdmin() && !$cronJob) {
			$userTypeCtrler = new UserTypeController();
			$userSessInfo = Session::readSession('userInfo');
			$pluginAccessList = $userTypeCtrler->getPluginAccessSettings($userSessInfo['userTypeId']);
			if (isset($pluginAccessList[$info['pid']]) && empty($pluginAccessList[$info['pid']]['value'])) {
				showErrorMsg($_SESSION['text']['label']['Access denied']);
			}
		}
		
		$pluginInfo = $this->__getSeoPluginInfo($info['pid']);
		$pluginDirName = $pluginInfo['name'];
		$pluginPath = SP_PLUGINPATH."/".$pluginDirName;
		
		if(file_exists($pluginPath."/".SP_PLUGINCONF)){
			include_once($pluginPath."/".SP_PLUGINCONF);
		}
		
		include_once($pluginPath."/".$pluginDirName.".ctrl.php");
		$pluginControler = New $pluginDirName();
		
		// set plugin specific variabled
		$pluginControler->pluginDirName = $pluginDirName;
		$pluginControler->pluginPath = $pluginPath;
		$pluginControler->pluginId = $info['pid'];
		$pluginControler->pluginViewPath = $this->getPluginViewPath($pluginControler->pluginPath);
		$pluginControler->pluginWebPath = SP_WEBPATH . "/" . SP_PLUGINDIR . "/" . $pluginDirName;
		$pluginControler->pluginImagePath = $pluginControler->pluginWebPath . "/images";
		$pluginControler->pluginCssPath = $pluginControler->pluginWebPath . "/css";
		$pluginControler->pluginJsPath = $pluginControler->pluginWebPath . "/js";
		$pluginControler->pluginScriptUrl = SP_WEBPATH . "/seo-plugins.php?pid=" . $pluginControler->pluginId;
		
		// if not_set_global_vars is not assigned
		if (empty($info['not_set_global_vars']) || !$info['not_set_global_vars']) {
			define('PLUGIN_PATH', $pluginControler->pluginPath);
			define('PLUGIN_ID', $info['pid']);
			define('PLUGIN_VIEWPATH', $pluginControler->pluginViewPath);
			define('PLUGIN_WEBPATH', $pluginControler->pluginWebPath);
			define('PLUGIN_IMGPATH', $pluginControler->pluginImagePath);
			define('PLUGIN_CSSPATH', $pluginControler->pluginCssPath);
			define('PLUGIN_JSPATH', $pluginControler->pluginJsPath);
			define("PLUGIN_SCRIPT_URL", $pluginControler->pluginScriptUrl);
		}
		
		// if no action specified just initialize plugin
		if ($info['action'] == 'get_plugin_object') {
			$pluginControler->initPlugin($data);
			return $pluginControler;
		} else {
			$this->pluginCtrler = $pluginControler;
			$action = empty($info['action']) ? "index" : $info['action'];
			$data = $_REQUEST;
			$pluginControler->initPlugin($data);
			$pluginControler->$action($data);
		}
	}
	
	# function to get plugin view path, chekc for theme exists or not
	function getPluginViewPath($pluginPath) {
		$pluginViewPath = $pluginPath."/views";
		
		
		# if theme compatible design for plugin
		if (file_exists($pluginPath."/themes")) {
			$themeInfo = $this->dbHelper->getRow("themes", "status=1 order by id");
			
			// if activated theme folder is existing
			if (file_exists($pluginPath."/themes/". $themeInfo['folder'])) {
				$pluginViewPath = $pluginPath."/themes/". $themeInfo['folder'] ."/views";
			} else {
				$pluginViewPath = $pluginPath."/themes/classic/views";
			}
			
		}
		
		return $pluginViewPath;
		
	}
	
	
	# function to init plugin before do action
	function initPlugin($data) {
		return;
	}

	# func to load plugin css files
	function loadAllPluginCss($pluginPathDir = "", $pluginCssWebPath = "") {
		$styleCont = "";
		$pluginPathDir = !empty($pluginPathDir) ? $pluginPathDir : PLUGIN_PATH."/css";
		$pluginCssWebPath = !empty($pluginCssWebPath) ? $pluginCssWebPath : PLUGIN_CSSPATH;
		if(file_exists($pluginPathDir)){
			if ($handle = opendir($pluginPathDir)) {
				while (false !== ($file = readdir($handle))) {
					if ( ($file != ".") && ($file != "..") &&  preg_match('/\.css$/i', $file) ) {
						$styleCont .= 'loadJsCssFile("'.$pluginCssWebPath."/".$file.'", "css");';
					}
				}
			}
		}
		
		if (!empty($styleCont)) {
			$styleCont = "<script>$styleCont</script>";
		}
		
		return $styleCont;
	}
	
	# func to load plugin js files
	function loadAllPluginJs($pluginPathDir = "", $pluginJsWebPath = "") {
		$styleCont = "";
		$pluginPathDir = !empty($pluginPathDir) ? $pluginPathDir : PLUGIN_PATH."/js";
		$pluginJsWebPath = !empty($pluginJsWebPath) ? $pluginJsWebPath : PLUGIN_JSPATH;
		if(file_exists($pluginPathDir)){
			if ($handle = opendir($pluginPathDir)) {
				while (false !== ($file = readdir($handle))) {
					if ( ($file != ".") && ($file != "..") &&  preg_match('/\.js$/i', $file) ) {
						$styleCont .= 'loadJsCssFile("'.$pluginJsWebPath."/".$file.'", "js");';
					}
				}
			}
		}
		
		if (!empty($styleCont)) {
			$styleCont = "<script>$styleCont</script>";
		}
		
		return $styleCont;
	}
	
	// index function
	function showSeoPlugins($info=[]){
		$this->layout = "default";
		
		$menuList = array();
		$pluginList = $this->__getAllSeoPlugins("status=1 and installed=1 ");
		
		// if not admin, check plugin access set for user, 
		if (!isAdmin()) {
			$userTypeCtrler = new UserTypeController();
			$userSessInfo = Session::readSession('userInfo');
			$pluginAccessList = $userTypeCtrler->getPluginAccessSettings($userSessInfo['userTypeId']);
			
			// loop through plugin list
			foreach ($pluginList as $pluginInfo) {
				
				// if access is set for plugin
				if (isset($pluginAccessList[$pluginInfo['id']]['value'])) {

					// access is on
					if (!empty($pluginAccessList[$pluginInfo['id']]['value'])) {
						$menuList[] = $pluginInfo;
					}
					
				} else {
					$menuList[] = $pluginInfo;	
				}
				
			}
			
		} else {
			$menuList = $pluginList;
		}
		
		if(count($menuList) <= 0){
		    $msg = $_SESSION['text']['label']['noactiveplugins'];
		    $msgButton = '<a class="actionbut" href="'.SP_PLUGINSITE.'" target="_blank">'.$this->spTextPlugin['Download Seo Panel Plugins'].' &gt;&gt;</a>';
			$this->set('msg', $msg);
			$this->set('msgButton', $msgButton);
			$this->render('common/notfound');
			exit;
		}
		
		# to get sub menus under a plugin main menu
		foreach($menuList as $i => $menuInfo){
			@Session::setSession('plugin_id', $menuInfo['id']);
			$pluginDirName = $menuInfo['name'];
			
			// for older versions with out themes specific files
			$pluginDirName = $menuInfo['name'];
			$menuFile = SP_PLUGINPATH."/".$pluginDirName."/views/".SP_PLUGINMENUFILE;
			
			// check for menu file exists or not
			if(file_exists($menuFile)){
				$menuList[$i]['menu'] = @View::fetchFile($menuFile);
			}else{
				
				// create plugin object and access the menu file - theme specific files
				$pluginObj = $this->createPluginObject($pluginDirName, array('not_set_global_vars' => true));
				$menuFile = $pluginObj->pluginViewPath . "/". SP_PLUGINMENUFILE;
				
				if(file_exists($menuFile)){
					$menuList[$i]['menu'] = @View::fetchFile($menuFile);
				}else{
					$menuList[$i]['menu'] = "<ul id='subui'>
											<li><a href='javascript:void(0);' onclick=\"".pluginMenu('action=index')."\">{$menuInfo['name']}</a></li>
										</ul>";
				}
			}

			// load plugin js and css files
			$pluginPathDir = SP_PLUGINPATH."/".$pluginDirName;
			$pluginWebPath = SP_WEBPATH . "/plugins/" . $pluginDirName;
			$pluginCssJsCont = $this->loadAllPluginCss($pluginPathDir . "/css", $pluginWebPath . "/css");
			$pluginCssJsCont .= $this->loadAllPluginJs($pluginPathDir . "/js", $pluginWebPath . "/js");
			$menuList[$i]['menu'] .= $pluginCssJsCont;
		}
		
		$this->set('menuList', $menuList);
		$menuSelected = empty($info['menu_selected']) ? $menuList[0]['id'] : $info['menu_selected'];
		$this->set('menuSelected', $menuSelected);
		
		$this->render('seoplugins/showseoplugins');
	}

	# func to get all seo tools
	function __getAllSeoPlugins($whereCond = ""){
		$whereCond = !empty($whereCond) ? $whereCond : "1=1";
		$sql = "select * from seoplugins where $whereCond order by priority,id";
		$seoPluginList = $this->db->select($sql);
		return $seoPluginList;
	}

	# func to list seo tools
	function listSeoPlugins($msg='', $error=false, $info=[]) {
		if(empty($msg)) $this->__updateAllSeoPlugins();
		
		$info['pageno'] = intval($info['pageno']);
		$userId = isLoggedIn();
		$this->set('msg', $msg);
		$this->set('error', $error);
		
		$sql = "select * from seoplugins where 1=1";
		
		// if status set
		if (isset($info['stscheck']) && $info['stscheck'] != 'select') {
		    $info['stscheck'] = intval($info['stscheck']);
		    $sql .= " and status='{$info['stscheck']}'";
		}
		
		$pageScriptPath = 'seo-plugins-manager.php?stscheck=';
		$pageScriptPath .= isset($info['stscheck']) ? $info['stscheck'] : "select";
		
		// search for keyword
		if (!empty($info['keyword'])) {
		    $sql .= " and (label like '%".addslashes($info['keyword'])."%'
			or name like '%".addslashes($info['keyword'])."%'
			or description like '%".addslashes($info['keyword'])."%')";
		    $pageScriptPath .= "&keyword=" . $info['keyword'];
		}
		
		$sql .= " order by id";
		
		// pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($pageScriptPath, '', 'scriptDoLoad', 'content', 'layout=ajax');
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		$seoPluginList = $this->db->select($sql);
		$this->set('list', $seoPluginList);
		
		$statusList = array(
		    $_SESSION['text']['common']['Active'] => 1,
		    $_SESSION['text']['common']['Inactive'] => 0,
		);
		
		$this->set('statusList', $statusList);
		$this->set('info', $info);
		$this->set('pageNo', $info['pageno']);
		$this->render('seoplugins/listseoplugins');
	}

	#function to change status of seo plugins
	function changeStatus($seoPluginId, $status){
		$status = intval($status);		
		$seoPluginId = intval($seoPluginId);
		$sql = "update seoplugins set status=$status where id=$seoPluginId";
		$this->db->query($sql);
	}
	
	#function to change installed status of seo plugins
	function __changeInstallStatus($seoPluginId, $status){
		$status = intval($status);		
		$seoPluginId = intval($seoPluginId);
		$sql = "update seoplugins set installed=$status where id=$seoPluginId";
		$this->db->query($sql);
	}
	
	# func to get seo plugin info
	function __getSeoPluginInfo($val, $col='id') {
		$val = ($col == 'id') ? intval($val) : addslashes($val);
		$sql = "select * from seoplugins where $col='$val'";
		$seoPluginInfo = $this->db->select($sql, true);
		return $seoPluginInfo;
	}
	
	# func to edit seo plugin
	function editSeoPlugin($info, $error=false){		
		
		if($error){
			$this->set('post', $info);
		}else{
			$info['pid'] = intval($info['pid']);
			$this->set('post', $this->__getSeoPluginInfo($info['pid']));
		}
		
		$this->render('seoplugins/editseoplugin');
	}
	
	# func to list seo plugin info
	function listPluginInfo($pluginId){
	    $pluginId = intval($pluginId);		
		$this->set('pluginInfo', $this->__getSeoPluginInfo($pluginId));	
		$this->set('pageNo', intval($_GET['pageno']));	
		$this->render('seoplugins/listplugininfo');
	}
	
	function updateSeoPlugin($listInfo){
		
		$listInfo['id'] = intval($listInfo['id']);
		$this->set('post', $listInfo);
		$errMsg['plugin_name'] = formatErrorMsg($this->validate->checkBlank($listInfo['plugin_name']));
		$errMsg['priority'] = formatErrorMsg($this->validate->checkNumber($listInfo['priority']));
		if(!$this->validate->flagErr){
			$sql = "update seoplugins set
						label='".addslashes($listInfo['plugin_name'])."',
						priority='".intval($listInfo['priority'])."'
						where id={$listInfo['id']}";
			$this->db->query($sql);
			$this->listSeoPlugins();
		}else{
			$this->set('errMsg', $errMsg);
			$listInfo['label'] = $listInfo['plugin_name'];
			$this->editSeoPlugin($listInfo, true);
		}
	}
	
	function updatePluginInfo($pluginId, $pluginInfo){
		
		$pluginId = intval($pluginId);
		$sql = "update seoplugins set
					label='".addslashes($pluginInfo['label'])."',
					author='".addslashes($pluginInfo['author'])."',
					description='".addslashes($pluginInfo['description'])."',
					version='{$pluginInfo['version']}',
					website='{$pluginInfo['website']}'
					where id=$pluginId";
		$this->db->query($sql);
	}
	
	# func to upgrade seo plugin
	function upgradeSeoPlugin($pluginId){
		$pluginInfo = $this->__getSeoPluginInfo($pluginId);
		
		if(file_exists(SP_PLUGINPATH."/".$pluginInfo['name'])){
			$pluginDBFile = SP_PLUGINPATH."/".$pluginInfo['name']."/".SP_PLUGINUPGRADEFILE;  
			if(file_exists($pluginDBFile)){
				$this->db->debugMode = false;
				$this->db->importDatabaseFile($pluginDBFile, false);
			}
	
			# parse plugin info
			$pluginInfo = $this->parsePluginInfoFile($pluginInfo['name']);
			$this->updatePluginInfo($pluginId, $pluginInfo);		
			
			$this->__changeInstallStatus($pluginId, 1);
			$this->listSeoPlugins("Plugin <b>{$pluginInfo['label']}</b> upgraded successfully!");
		}else{
			$this->__changeInstallStatus($pluginId, 0);
			$this->listSeoPlugins("Plugin <b>{$pluginInfo['label']}</b> upgrade failed!", true);
		}
	}
	
	# func to re install the seo plugin
	function reInstallSeoPlugin($pluginId){
		$pluginInfo = $this->__getSeoPluginInfo($pluginId);
		
		if(file_exists(SP_PLUGINPATH."/".$pluginInfo['name'])){
			$pluginDBFile = SP_PLUGINPATH."/".$pluginInfo['name']."/".SP_PLUGINDBFILE;  
			if(file_exists($pluginDBFile)){
				$this->db->debugMode = false;
				$this->db->importDatabaseFile($pluginDBFile, false);
			}
	
			# parse plugin info
			$pluginInfo = $this->parsePluginInfoFile($pluginInfo['name']);
			$this->updatePluginInfo($pluginId, $pluginInfo);
			
			$this->__changeInstallStatus($pluginId, 1);
			$this->listSeoPlugins("Plugin <b>{$pluginInfo['label']}</b> re-installed successfully!");
		}else{
			$this->__changeInstallStatus($pluginId, 0);
			$this->listSeoPlugins("Plugin <b>{$pluginInfo['label']}</b> re-installation failed!", true);
		}		
	}

	# to check whether the directory is plugin
	function isPluginDirectory($file){
		if ( ($file != ".") && ($file != "..") && ($file != ".svn") &&  is_dir(SP_PLUGINPATH."/".$file) ) {
			if(!preg_match('/^\./', $file)){
				return true;
			}
		}
		return false;
	}
	
	# func to update seo plugins in db
	function __updateAllSeoPlugins(){
		$sql = "update seoplugins set installed=0";
		$this->db->query($sql);
		
		if ($handle = opendir(SP_PLUGINPATH)) {
			while (false !== ($file = readdir($handle))) {
				if ( $this->isPluginDirectory($file) ) {
					$pluginName = $file;
					$seoPluginInfo = $this->__getSeoPluginInfo($pluginName, 'name');
					if(empty($seoPluginInfo['id'])){
						
						# parse plugin info
						$pluginInfo = $this->parsePluginInfoFile($file);						
						
						$sql = "insert into seoplugins(label,name,author,description,version,website,status,installed) 
								values('".addslashes($pluginInfo['label'])."','$pluginName','".addslashes($pluginInfo['author'])."','".addslashes($pluginInfo['description'])."','{$pluginInfo['version']}','{$pluginInfo['website']}',0,1)";
						$this->db->query($sql);
						
						$pluginDBFile = SP_PLUGINPATH."/".$file."/".SP_PLUGINDBFILE;  
						if(file_exists($pluginDBFile)){
							
							$this->db->debugMode = false;
							$this->db->importDatabaseFile($pluginDBFile, false);
						}						
						
					}else{
						$this->__changeInstallStatus($seoPluginInfo['id'], 1);
					}
				}
			}
			closedir($handle);
		}
	}
	
	# func to parse plugin info file
	function parsePluginInfoFile($file) {
		$pluginInfo = array();
		$pluginInfoFile = SP_PLUGINPATH."/".$file."/".SP_PLUGININFOFILE;
		if(file_exists($pluginInfoFile)){
		    $xml = new XML_Parser;
    		$pInfo = $xml->parse($pluginInfoFile);
    		if(!empty($pInfo[0]['child'])){
    			foreach($pInfo[0]['child'] as $info){
    				$infoCol = strtolower($info['name']);
    				$pluginInfo[$infoCol] = $info['content'];
    			}
    		}			
		}		
		
		$pluginInfo['label'] = empty($pluginInfo['label']) ? $file : $pluginInfo['label'];
		$pluginInfo['version'] = empty($pluginInfo['version']) ? '1.0.0' : $pluginInfo['version'];
		$pluginInfo['author'] = empty($pluginInfo['author']) ? 'Seo Panel': $pluginInfo['author'];
		$pluginInfo['website'] = empty($pluginInfo['website']) ? SP_PLUGINSITE : $pluginInfo['website'];		
		return $pluginInfo;		 
	}
	
	# function to create helpers for main controlller
	function createHelper($helperName, $pluginDirName='') {
	    $pluginPath = !empty($this->pluginPath)	? $this->pluginPath : SP_PLUGINPATH."/$pluginDirName";
	    $pluginHelperFile = $pluginPath . "/".strtolower($helperName).".ctrl.php";
	    if (!file_exists($pluginHelperFile)) {
	        return false;
	    }
	    
	    include_once($pluginHelperFile);
	    $helperObj = New $helperName();
	    $helperObj->pluginPath = $this->pluginPath;
	    $helperObj->pluginId = $this->pluginId;
	    $helperObj->pluginViewPath = $this->pluginViewPath;
	    $helperObj->pluginWebPath = $this->pluginWebPath;
	    $helperObj->pluginImagePath = $this->pluginImagePath;
	    $helperObj->pluginCssPath = $this->pluginCssPath;
	    $helperObj->pluginJsPath = $this->pluginJsPath;
	    $helperObj->pluginScriptUrl = $this->pluginScriptUrl;
	    $helperObj->data = $this->data;
	    $helperObj->pluginText = $this->pluginText;
	    return $helperObj;
	}
	
	# func to get plugin language texts
	function getPluginLanguageTexts($category, $langCode='en', $table='') {
		$langTexts = array();
		
		$sql = "select label,content from $table where category='$category' and lang_code='$langCode' and content!='' order by label";
		$textList = $this->db->select($sql);
		foreach ($textList as $listInfo) {
			$langTexts[$listInfo['label']] = stripslashes($listInfo['content']);
		}

		# if langauge is not english
		if ($langCode != 'en') {
			$defaultTexts = $this->getPluginLanguageTexts($category, 'en', $table);
			foreach ($defaultTexts as $label => $content) {
				if (empty($langTexts[$label])) {
					$langTexts[$label] = $content;
				}
			} 
		}
		
		return $langTexts;
	}
	
	# func to set language texts
	function setPluginTextsForRender($category='', $table='') {
				
		if (empty($this->pluginText)) {
			$this->pluginText = $this->getPluginLanguageTexts($category, $_SESSION['lang_code'], $table);
			$this->set('pluginText', $this->pluginText);	
		}		
	}
	
	# function to check whether a plugin is installed and active
	function isPluginActive($value, $col = 'name') {
		$sql = "select * from seoplugins where $col='".addslashes($value)."' and installed=1 and status=1";
		$pluginInfo = $this->db->select($sql, true);
		return empty($pluginInfo['id']) ? false : $pluginInfo;
	}
	
	# function to create plugin object
	function createPluginObject($pluginName, $info = array()) {
		$pluginInfo = $this->__getSeoPluginInfo($pluginName, 'name');
		$info['pid'] = $pluginInfo['id'];
		$info['action'] = "get_plugin_object";
		$pluginCtrler = $this->manageSeoPlugins($info, 'get', true);
		return $pluginCtrler;
	}
	
}
?>
