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

# class defines all themes controller functions
class ThemesController extends Controller{
    
    var $tableName = "themes";
    var $layout = "ajax";

	# func to get all  tools
	function __getAllThemes(){
		$sql = "select * from $this->tableName order by id";
		$themeList = $this->db->select($sql);
		return $themeList;
	}

	# func to list  tools
	function listThemes($msg='', $error=false){		
		
		if(empty($msg)) $this->__updateAllThemes();		
		
		$userId = isLoggedIn();
		$this->set('msg', $msg);
		$this->set('error', $error);
		
		$sql = "select * from $this->tableName order by id";
		
		# pagination setup		
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages('themes-manager.php?');		
		$this->set('pagingDiv', $pagingDiv);
		$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		
		$themeList = $this->db->select($sql);
		$this->set('pageNo', $_GET['pageno']);
		$this->set('list', $themeList);
		$this->render('theme/listthemes');
	}

	# function to activate theme
	function activateTheme($themeId){		
		$themeId = intval($themeId);
				
		// inactivate all themes and then activate current theme
		$sql = "update $this->tableName set status=0";
		$this->db->query($sql);
		$sql = "update $this->tableName set status=1 where id=$themeId";
		$this->db->query($sql);
	}
	
	#function to change installed status of themes
	function __changeInstallStatus($themeId, $status){
		
		$themeId = intval($themeId);
		$sql = "update $this->tableName set installed=$status where id=$themeId";
		$this->db->query($sql);
	}
	
	# func to get  theme info
	function __getThemeInfo($val, $col='id') {
		$sql = "select * from $this->tableName where $col='$val'";
		$themeInfo = $this->db->select($sql, true);
		return $themeInfo;
	}
	
	# func to get active theme info
	function __getActiveTheme() {
		$themeInfo = $this->dbHelper->getRow($this->tableName, "status=1");
		return $themeInfo;
	}
	
	# func to list  theme info
	function listThemeInfo($themeId) {
	    $themeId = intval($themeId);
		$this->set('themeInfo', $this->__getThemeInfo($themeId));	
		$this->set('pageNo', $_GET['pageno']);	
		$this->render('theme/listthemeinfo');
	}
	
	function updateThemeInfo($themeId, $themeInfo){		
		$themeId = intval($themeId);
		$sql = "update $this->tableName set
					name='".addslashes($themeInfo['name'])."',
					author='".addslashes($themeInfo['author'])."',
					description='".addslashes($themeInfo['description'])."',
					version='{$themeInfo['version']}',
					website='{$themeInfo['website']}'
					where id=$themeId";
		$this->db->query($sql);
	}
	
	# func to upgrade  theme
	function upgradeTheme($themeId){
		$themeInfo = $this->__getThemeInfo($themeId);
		
		if (file_exists(SP_THEMEPATH."/".$themeInfo['folder'])) {
		    	
			# parse theme info
			$themeInfo = $this->parseThemeInfoFile($themeInfo['folder']);
			$this->updateThemeInfo($themeId, $themeInfo);		
			
			$this->__changeInstallStatus($themeId, 1);
			$this->listThemes("Theme <b>{$themeInfo['name']} {$themeInfo['version']}</b> upgraded successfully!");
		} else {
			$this->__changeInstallStatus($themeId, 0);
			$this->listThemes("Theme <b>{$themeInfo['name']} {$themeInfo['version']}</b> upgrade failed!", true);
		}
	}
	
	# func to re install the  theme
	function reInstallTheme($themeId){
		$themeInfo = $this->__getThemeInfo($themeId);
		
		if(file_exists(SP_THEMEPATH."/".$themeInfo['folder'])){
	
			# parse theme info
			$themeInfo = $this->parseThemeInfoFile($themeInfo['name']);
			$this->updateThemeInfo($themeId, $themeInfo);
			
			$this->__changeInstallStatus($themeId, 1);
			$this->listThemes("Theme <b>{$themeInfo['name']} {$themeInfo['version']}</b> re-installed successfully!");
		}else{
			$this->__changeInstallStatus($themeId, 0);
			$this->listThemes("Theme <b>{$themeInfo['name']} {$themeInfo['version']}</b> re-installation failed!", true);
		}		
	}

	# to check whether the directory is theme
	function isThemeDirectory($file){
		if ( ($file != ".") && ($file != "..") && ($file != ".svn") &&  is_dir(SP_THEMEPATH."/".$file) ) {
			if(!preg_match('/^\./', $file)){
				return true;
			}
		}
		return false;
	}
	
	# func to update themes in db
	function __updateAllThemes(){
		$sql = "update themes set installed=0";
		$this->db->query($sql);
		
		if ($handle = opendir(SP_THEMEPATH)) {
			while (false !== ($file = readdir($handle))) {
				if ( $this->isThemeDirectory($file) ) {
					$themeName = $file;
					// $themeName here is the FOLDER name (from opendir()),
					// but this looked it up against the `name` column
					// (the parsed, human-readable theme.xml <name>, e.g.
					// "Test Upload Theme") instead of `folder` - only
					// "worked" for classic/simple/business because their
					// folder happens to case-insensitively equal their
					// display name (MySQL's default collation). Any
					// other theme whose folder name doesn't match its
					// display name got re-inserted as a brand new row on
					// every single call (including the plain page load
					// that triggers __updateAllThemes() with no action),
					// accumulating duplicates.
					$themeInfo = $this->__getThemeInfo($themeName, 'folder');
					if(empty($themeInfo['id'])){
						
						// parse theme info and save details
						$themeInfo = $this->parseThemeInfoFile($file);
						$sql = "insert into $this->tableName(name,folder,author,description,version,website,status,installed) 
								values('".addslashes($themeInfo['name'])."', '".addslashes($themeName)."','".addslashes($themeInfo['author'])."','".addslashes($themeInfo['description'])."','{$themeInfo['version']}','{$themeInfo['website']}',0,1)";
						$this->db->query($sql);						
						
					}else{
						$this->__changeInstallStatus($themeInfo['id'], 1);
					}
				}
			}
			closedir($handle);
		}
	}
	
	# func to show the upload-theme-zip form
	function showUploadTheme($data=[]){
		checkAdminLoggedIn();
		$this->render('theme/uploadtheme');
	}

	# func to handle an uploaded theme zip - installs it fresh if this is
	# a new theme, or upgrades the existing one in place if a theme of
	# the same folder name is already registered. Reuses the existing
	# upgrade/detect logic below rather than duplicating it - this only
	# handles getting the uploaded zip's files safely onto disk.
	function uploadTheme($data=[]){
		checkAdminLoggedIn();

		if (empty($_FILES['theme_zip']) || $_FILES['theme_zip']['error'] !== UPLOAD_ERR_OK) {
			echo json_encode(['status' => 'error', 'message' => 'Please select a theme zip file to upload.']);
			return;
		}

		include_once(SP_LIBPATH.'/themeupload.class.php');
		$installer = new ThemeUploadInstaller();

		// $installer->uploadedVersion/uploadedLabel are set as soon as
		// the zip's own theme.xml is confirmed to exist, before the
		// possibly-failing install step - lets a FAILURE message still
		// say what version was being attempted, not just success ones.
		list($status, $message, $themeDirName) = $installer->run($_FILES['theme_zip']['tmp_name'], $_FILES['theme_zip']['name']);

		if (!$status) {
			$failMsg = $message;
			if (!empty($themeDirName) && !empty($installer->uploadedVersion)) {
				$existingInfo = $this->__getThemeInfo($themeDirName, 'folder');
				$attemptedLabel = htmlspecialchars($installer->uploadedLabel, ENT_QUOTES);
				$attemptedVersion = htmlspecialchars($installer->uploadedVersion, ENT_QUOTES);
				if (!empty($existingInfo['id']) && $existingInfo['version'] != $installer->uploadedVersion) {
					$fromVersion = htmlspecialchars($existingInfo['version'], ENT_QUOTES);
					$failMsg = "Failed to upgrade theme <b>{$attemptedLabel}</b> from version {$fromVersion} to {$attemptedVersion}: {$message}";
				} else {
					$failMsg = "Failed to install theme <b>{$attemptedLabel}</b> (version {$attemptedVersion}): {$message}";
				}
			}
			echo json_encode(['status' => 'error', 'message' => $failMsg]);
			return;
		}

		// already registered under this folder name? treat it as an
		// upgrade (re-parses theme.xml, updates the existing row) -
		// otherwise let the normal new-theme detection pick it up
		$existingInfo = $this->__getThemeInfo($themeDirName, 'folder');
		if (!empty($existingInfo['id'])) {
			$fromVersion = $existingInfo['version'];
			$themeInfo = $this->parseThemeInfoFile($themeDirName);
			$this->updateThemeInfo($existingInfo['id'], $themeInfo);
			$this->__changeInstallStatus($existingInfo['id'], 1);
			// name/version come from the uploaded zip's own theme.xml -
			// escaped before going into a message rendered as raw HTML
			// client-side (innerHTML)
			$name = htmlspecialchars($themeInfo['name'], ENT_QUOTES);
			$toVersion = htmlspecialchars($themeInfo['version'], ENT_QUOTES);
			$versionText = ($fromVersion != $themeInfo['version'])
				? "from version ".htmlspecialchars($fromVersion, ENT_QUOTES)." to {$toVersion}"
				: "(already at version {$toVersion})";
			echo json_encode(['status' => 'success', 'message' => "Theme <b>{$name}</b> upgraded successfully {$versionText}."]);
		} else {
			$this->__updateAllThemes();
			$freshInfo = $this->__getThemeInfo($themeDirName, 'folder');
			$name = htmlspecialchars($freshInfo['name'], ENT_QUOTES);
			$version = htmlspecialchars($freshInfo['version'], ENT_QUOTES);
			echo json_encode(['status' => 'success', 'message' => "Theme <b>{$name}</b> (version {$version}) installed successfully! Activate it below to start using it."]);
		}
	}

	# func to parse theme info file
	function parseThemeInfoFile($file) {
		$themeInfo = array();
		$themeInfoFile = SP_THEMEPATH."/".$file."/".SP_THEMEINFOFILE;
		if(file_exists($themeInfoFile)){
		    $xml = new XML_Parser;
    		$pInfo = $xml->parse($themeInfoFile);
    		if(!empty($pInfo[0]['child'])){
    			foreach($pInfo[0]['child'] as $info){
    				$infoCol = strtolower($info['name']);
    				$themeInfo[$infoCol] = $info['content'];
    			}
    		}			
		}		
		
		$themeInfo['name'] = empty($themeInfo['name']) ? $file : $themeInfo['name'];
		$themeInfo['version'] = empty($themeInfo['version']) ? '1.0.0' : $themeInfo['version'];
		$themeInfo['author'] = empty($themeInfo['author']) ? 'Seo Panel': $themeInfo['author'];
		$themeInfo['website'] = empty($themeInfo['website']) ? SP_THEMESITE : $themeInfo['website'];		
		return $themeInfo;		 
	}
	
}
?>