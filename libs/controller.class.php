<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	           *
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
	var $dbHelper;
	var $db;
	var $spider;
	var $paging;
	var $layout = 'default';
	var $sessionCats = array('common','login','button','label');
	// set()'s own `$this->data[$varName] = $varValue;` already assumed
	// this was always an array (PHP auto-vivifies null into one on first
	// write) - but a controller action that renders a view with zero
	// prior set() calls (e.g. a plugin's static aboutus() page) left it
	// genuinely null, and View::getPluginViewContent()'s count($this->
	// data) is a TypeError on PHP 8+ given null. Declaring the intended
	// default explicitly closes that gap for every such path at once.
	var $data = array();

	function __construct(){
		
		# create database object
		$dbObj = New Database(DB_ENGINE);
		$this->dbHelper = $dbObj;
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
			
			if ($layout == 'default') {
				$this->set('translatorInfo', $this->getTranslatorInfo());
			}
			
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
				$translatorInfo .= " | $trname $trlink";
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

	// records a security-relevant admin action for later review (Settings
	// > Audit Log) - who did what, when, from where. Deliberately
	// denormalizes the actor's username and the target's label (not just
	// their ids) so the log entry stays readable independently of either
	// row still existing later (e.g. "user X deleted user Y" must still
	// read correctly after user Y's own row is gone). $details is
	// free-form text for anything extra worth capturing (e.g. which
	// setting names changed) - callers must never put a secret/password
	// value in here, only in $targetLabel is it borderline-safe (a
	// username, not a credential).
	function logAuditEvent($action, $targetType = null, $targetId = null, $targetLabel = null, $details = null) {
		$actorId = isLoggedIn();
		$actorUsername = null;
		if (!empty($actorId)) {
			$actorInfo = $this->db->select("select username from users where id=" . intval($actorId), true);
			$actorUsername = $actorInfo['username'] ?? null;
		}
		$ip = $_SERVER['REMOTE_ADDR'] ?? null;
		$now = date('Y-m-d H:i:s');

		$sql = "insert into audit_log
			(actor_user_id, actor_username, action, target_type, target_id, target_label, details, ip_address, created_at)
			values (" .
			(empty($actorId) ? "NULL" : intval($actorId)) . ", " .
			($actorUsername === null ? "NULL" : "'" . addslashes($actorUsername) . "'") . ", " .
			"'" . addslashes($action) . "', " .
			($targetType === null ? "NULL" : "'" . addslashes($targetType) . "'") . ", " .
			($targetId === null ? "NULL" : intval($targetId)) . ", " .
			($targetLabel === null ? "NULL" : "'" . addslashes($targetLabel) . "'") . ", " .
			($details === null ? "NULL" : "'" . addslashes($details) . "'") . ", " .
			($ip === null ? "NULL" : "'" . addslashes($ip) . "'") . ", " .
			"'$now')";
		$this->db->query($sql);
	}

	// shared by every "Summary" report page's own __get*DateAvailability()
	// helper (report/webmaster/analytics/social_media/review_manager
	// controllers) - each builds its own min/max(report_date) SQL (table
	// names and join conditions differ per page), this just runs it and
	// normalizes the result row into a plain [min, max] pair.
	function __getMinMaxDate($sql) {
		$row = $this->db->select($sql, true);
		return array($row['min_date'] ?? null, $row['max_date'] ?? null);
	}

	// shared Day/Week/Month/Year period-preset resolution, used by every
	// "Summary" report page that offers that dropdown. Split into two
	// steps so a caller can decide $period (cheap, no DB access) before
	// deciding whether its own availability query is even worth running -
	// each page's own fallback for explicit from_time/to_time (when
	// __resolvePeriodValue() comes back empty/Custom) predates this
	// feature and legitimately differs per page, so that part stays in
	// each controller.

	// reads 'period'/'from_time'/'to_time' from the caller's raw request
	// array and returns the resolved period key ('day'/'week'/'month'/
	// 'year'), or '' when explicit dates were submitted/should be used
	// (an invalid/tampered period value is treated the same as not
	// submitted at all).
	function __resolvePeriodValue($searchInfo) {
		$periodOffsets = array('day' => '-1 day', 'week' => '-7 days', 'month' => '-30 days', 'year' => '-1 year');
		$hasExplicitDates = !empty($searchInfo['from_time']) || !empty($searchInfo['to_time']);
		return (!empty($searchInfo['period']) && isset($periodOffsets[$searchInfo['period']])) ? $searchInfo['period'] : ($hasExplicitDates ? '' : 'month');
	}

	// given an already-resolved (non-empty) $period from
	// __resolvePeriodValue(), anchors "to" to the latest date this page's
	// own availability query actually found data for (or $noDataAnchor,
	// a strtotime()-compatible fallback, e.g. 'today'/'-1 days', when
	// there's no saved data at all yet - differs per page's own
	// reporting-lag tolerance), and "from" to that minus the preset's
	// offset, clamped so it never goes earlier than the earliest
	// available date. Returns array($fromTimeStamp, $toTimeStamp).
	function __resolvePeriodPreset($period, $minAvailDate, $maxAvailDate, $noDataAnchor) {
		$periodOffsets = array('day' => '-1 day', 'week' => '-7 days', 'month' => '-30 days', 'year' => '-1 year');
		$toTimeStamp = !empty($maxAvailDate) ? strtotime($maxAvailDate) : strtotime($noDataAnchor);
		$fromTimeStamp = strtotime($periodOffsets[$period], $toTimeStamp);
		if (!empty($minAvailDate) && $fromTimeStamp < strtotime($minAvailDate)) {
			$fromTimeStamp = strtotime($minAvailDate);
		}
		return array($fromTimeStamp, $toTimeStamp);
	}
}
?>
