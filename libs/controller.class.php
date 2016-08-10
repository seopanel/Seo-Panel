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

# class defines all controller functions
class Controller extends Seopanel{
	var $view;
	var $session;
	var $validate;
	var $db;
	var $spider;
	var $paging;
	var $layout = 'default';
	var $sessionCats = array('common','login','button','label');

	function Controller(){
		
		# create database object
		$dbObj = New Database(DB_ENGINE);
		$this->db = $dbObj->dbConnect();
		$this->view = New View();
		$this->session = New Session();
		$this->validate = New Validation();
		$this->spider = New Spider();
		$this->paging = New Paging();

		# to define all system variables
		$force = false;
		if (!empty($_GET['lang_code'])) {
		    $_GET['lang_code'] = addslashes($_GET['lang_code']);
			$this->assignLangCode(trim($_GET['lang_code']));
			$_GET['lang_code'] = '';
			$force = true;
		}
		
		# func to assign texts to session
		$_SESSION['lang_code'] = empty($_SESSION['lang_code']) ? SP_DEFAULTLANG : $_SESSION['lang_code'];
		$this->assignTextsToSession($_SESSION['lang_code'], $force);
	}
	
	# func to assign lang code
	function assignLangCode($langCode) {
		
		$sql = "select count(*) count from languages where lang_code='$langCode' and translated=1";
		$info = $this->db->select($sql, true);
		$langCode = empty($info['count']) ? 'en' : $langCode; 
		
		$_SESSION['lang_code'] = $langCode;
		if ($userId = isLoggedIn()) {
			$sql = "update users set lang_code='$langCode' where id=$userId";
			$res = $this->db->query($sql);			
			@Session::setSession('text', '');
		}
	}
	
	# func to get all system settings
	function __getAllSettings($showCheck=false, $showVal=1, $category='system') {
	    $condition = $showCheck ? " where `display`=$showVal and set_category='$category'" : "";
		$sql = "select * from settings $condition order by id";
		$settingsList = $this->db->select($sql);
		return $settingsList;
	}
	
	
	# to define all system settings
	function defineAllSystemSettings() {
		
		$settingsList = $this->__getAllSettings();		
		foreach($settingsList as $settingsInfo){
			if(!defined($settingsInfo['set_name'])){
				define($settingsInfo['set_name'], $settingsInfo['set_val']);
			}
		}				
	}	
	
	# func to restore data
	function restoreData() {
		$dbFile = SP_DATAPATH."/seopanel.sql";
		$this->db->importDatabaseFile($dbFile);
	}

	# func set variable to ctp
	function set($varName, $varValue){
		$this->data[$varName] = $varValue;
	}

	# normal render function
	function render($viewFile='home', $layout='default'){
		if(empty($layout) || ($layout == 'default')){
			if(!empty($this->layout)){
				$layout = $this->layout;
			}			
			if ($layout == 'default') $this->set('translatorInfo', $this->getTranslatorInfo());
		}
		$this->view->data = $this->data;
		$this->view->render($viewFile, $layout);
	}
	
	# function to get translator info
	function getTranslatorInfo() {
		$translatorInfo = '';
		if ($_SESSION['lang_code'] != 'en') {
			$sql = "select t.*,lang_show from translators t,languages l where l.lang_code=t.lang_code and t.lang_code='{$_SESSION['lang_code']}'";
			$list = $this->db->select($sql);		
			if (count($list) > 0) {				
				$trname = $list[0]['lang_show']." ". $_SESSION['text']['label']['translation by']. " ";
				$trlink = "";
				foreach ($list as $i => $info) {
					$trname .=  $i ? " and ".$info['trans_name'] : $info['trans_name'];
					$trlink .= " | <a href='{$info['trans_website']}' target='_blank' style='font-size:12px;'>{$info['trans_company']}</a>";	
				}			
				$translatorInfo .= "<div style='margin-top: 6px;'>$trname $trlink</div>";
			}
		}
		return $translatorInfo;
	}
	
	# plugin render function
	function pluginRender($viewFile='home', $layout='default'){
		if(empty($layout) || ($layout == 'default')){
			if(!empty($this->layout)){
				$layout = $this->layout;
			}
		}
		$this->view->data = $this->data;
		$this->view->pluginRender($viewFile, $layout);
	}
	
	# normal plugingetViewContent function
	function getPluginViewContent($viewFile){
		$this->view->data = $this->data;
		return $this->view->getPluginViewContent($viewFile);
	}
		
	# func to getting language texts
	function getLanguageTexts($category, $langCode='en') {
		$langTexts = array();
		
		$sql = "select label,content from texts where category='$category' and lang_code='$langCode' and content!='' order by label";
		$textList = $this->db->select($sql);
		foreach ($textList as $listInfo) {
			$langTexts[$listInfo['label']] = stripslashes($listInfo['content']);
		}

		# if langauge is not english
		if ($langCode != 'en') {
			$defaultTexts = $this->getLanguageTexts($category, 'en');
			foreach ($defaultTexts as $label => $content) {
				if (empty($langTexts[$label])) {
					$langTexts[$label] = $content;
				}
			} 
		}
		
		return $langTexts;
	}

	# func to assign language to session
	function assignTextsToSession($langCode='en', $force=false) {
		if (SP_LANGTESTING || empty($_SESSION['text']) || $force ) {
			$_SESSION['text'] = array();
			foreach ($this->sessionCats as $category) {
				$_SESSION['text'][$category] = $this->getLanguageTexts($category, $langCode);
			}	
		}
	}	

	# function to check whether user is keyword owner or not
	function checkUserIsObjectOwner($objId, $objName='website') {
		
		if (!isAdmin()) {
			$userId = isLoggedIn();
			switch ($objName) {
				
				case "keyword":
					$sql = "select k.id from keywords k,websites w where k.website_id=w.id and w.user_id='".intval($userId)."' and k.id='".intval($objId)."'";
					break;
					
				case "website":
					$sql = "select id from websites where id='".intval($objId)."' and user_id='".intval($userId)."'";
					break;
					
			}	
			
			$info = $this->db->select($sql, true);
			if (empty($info['id'])) {
				showErrorMsg("You are not allowed to access this page!");
			} 
		}
			
	}

	# to create component object
	function createComponent($compName) {
	    include_once(SP_CTRLPATH."/components/".strtolower($compName).".php");
	    $componentObj = new $compName();
	    return $componentObj;
	}

	# to create cotroller object
	function createController($ctrlName) {
	    include_once(SP_CTRLPATH."/".strtolower($ctrlName).".ctrl.php");
	    $ctrlName .= "Controller"; 
	    $controllerObj = new $ctrlName();
	    return $controllerObj;
	}
	
	# function to create mysql connect again
	function checkDBConn($force=false) {
		if($force || !is_object($this->db)){
			$dbObj = New Database(DB_ENGINE);
			$this->db = $dbObj->dbConnect();
		}
	}
	
	# normal getViewContent function
	function getViewContent($viewFile){
		$this->view->data = $this->data;
		return $this->view->getViewContent($viewFile);
	}
}
?>