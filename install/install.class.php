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

class Install {
	
	# func to check requirements
	function checkRequirements($error=false) {		
		
		$phpClass = "red";
		$phpSupport = "No";
		$phpVersion = phpversion();
		if (intval($phpVersion) >= 4 and intval($phpVersion) < 7) {			
			$phpClass = "green";
			$phpSupport = "Yes";
		}
		$phpSupport .= " ( PHP $phpVersion )";
		
		$mysqlClass = "red";
		$mysqlSupport = "No";
		if(function_exists('mysql_query')){
			$mysqlSupport = "Yes";
			$mysqlClass = "green";
		}
		
		$curlClass = "red";
		$curlSupport = "No";
		if(function_exists('curl_version')){
			$version = curl_version();
			$curlSupport = "Yes ( CURL  {$version['version']} )";
			$curlClass = "green";
		}
		
		/*$shorttagClass = "red";
		$shorttagSupport = "Disabled";
		if(ini_get('short_open_tag')){
			$shorttagSupport = "Enabled";
			$shorttagClass = "green";
		}*/
		
		$gdClass = "red";
		$gdSupport = "No";
		if(function_exists('gd_info')){
			$version = gd_info();
			$gdSupport = "Yes ( GD  {$version['GD Version']} )";
			$gdClass = "green";
		}
		
		$configClass = "red";
		$configSupport = "Not found";
		$configFile = SP_INSTALL_CONFIG_FILE;
		if(file_exists($configFile)){
			
			include_once(SP_INSTALL_CONFIG_FILE);
			if(defined('SP_INSTALLED')){
				die("<p style='color:red'>Seo Panel version ".SP_INSTALLED." is already installed in your system!</p>");
			}
			
			$configSupport = "Found, Unwritable<br><p class='note'><b>Command:</b> chmod 666 config/sp-config.php</p>";
			if(is_writable($configFile)){				
				$configSupport = "Found, Writable";				
				$configClass = "green";
			}			
		}
		
	
		$tmpClass = "red";
		$tmpSupport = "Not found";
		$tmpFile = SP_INSTALL_DIR.'/../tmp';
		if(file_exists($tmpFile)){
			$tmpSupport = "Found, Unwritable<br><p class='note'><b>Command:</b> chmod -R 777 tmp/</p>";
			if(is_writable($tmpFile)){				
				$tmpSupport = "Found, Writable";				
				$tmpClass = "green";
			}			
		}
		
		$errMsg = "";
		if($error){
			if( ($phpClass == 'red') || ($mysqlClass == 'red') || ($curlClass == 'red') || ($shorttagClass == 'red') || ($configClass == 'red') ){
				$errMsg = "Please fix the following errors to proceed to next step!";
			}
		}
		
		?>
		<h1 class="BlockHeader">Welcome to Seo panel Installation</h1>
		<form method="post">
		<table width="100%" cellspacing="8px" cellpadding="0px" class="formtab">
			<tr><th colspan="2" class="header">Installation compatibility</th></tr>
			<tr><td colspan="2" class="error"><?php echo $errMsg;?></td></tr>
			<tr>
				<th>PHP version >= 4.0.0 and &lt; 7.0.0</th>
				<td class="<?php echo $phpClass;?>"><?php echo $phpSupport;?></td>
			</tr>
			<tr>
				<th>MySQL Support</th>
				<td class="<?php echo $mysqlClass;?>"><?php echo $mysqlSupport;?></td>
			</tr>
			<tr>
				<th>CURL Support</th>
				<td class="<?php echo $curlClass;?>"><?php echo $curlSupport; ?></td>
			</tr>
			<?php /* ?>
			<tr>
				<th>PHP short_open_tag</th>
				<td class="<?php echo $shorttagClass;?>"><?php echo $shorttagSupport; ?></td>
			</tr>
			<?php */ ?>
			<tr>
				<th>GD graphics support</th>
				<td class="<?php echo $gdClass;?>"><?php echo $gdSupport; ?></td>
			</tr>
			<tr>
				<th>/config/sp-config.php</th>
				<td class="<?php echo $configClass;?>"><?php echo $configSupport; ?></td>
			</tr>
			<tr>
				<th>/tmp</th>
				<td class="<?php echo $tmpClass;?>"><?php echo $tmpSupport; ?></td>
			</tr>
		</table>
		<input type="hidden" value="<?php echo $phpClass;?>" name="php_support">
		<input type="hidden" value="<?php echo $mysqlClass;?>" name="mysql_support">
		<input type="hidden" value="<?php echo $curlClass;?>" name="curl_support">
		<?php /* ?><input type="hidden" value="<?php echo $shorttagClass;?>" name="short_open_tag"><?php */ ?>
		<input type="hidden" value="<?php echo $configClass;?>" name="config">
		<input type="hidden" value="startinstall" name="sec">
		<input type="submit" value="Proceed to next step >>" name="submit" class="button">
		</form>
		<?php
	}
	
	# func to start installation
	function startInstallation($info='', $errMsg='') {
		if( ($info['php_support'] == 'red') || ($info['mysql_support'] == 'red') || ($info['curl_support'] == 'red')
		|| ($info['config'] == 'red') ){
			$this->checkRequirements(true);
			return;
		}
		?>
		<h1 class="BlockHeader">Database Settings</h1>
		<form method="post">
		<table width="100%" cellspacing="8px" cellpadding="0px" class="formtab">
			<tr><th colspan="2" class="header">Database configuration</th></tr>
			<tr><td colspan="2" class="error"><?php echo $errMsg;?></td></tr>
			<tr>
				<th>Database type:</th>
				<td>
					<select name="db_engine">
						<option value="mysql">MySQL</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>Database server hostname:</th>
				<td><input type="text" name="db_host" value="<?php echo empty($info['db_host']) ? "localhost" : $info['db_host'];?>"></td>
			</tr>
			<tr>
				<th>Database name:</th>
				<td><input type="text" name="db_name" value="<?php echo $info['db_name'];?>"></td>
			</tr>
			<tr>
				<th>Database username:</th>
				<td><input type="text" name="db_user" value="<?php echo $info['db_user'];?>"></td>
			</tr>
			<tr>
				<th>Database password:</th>
				<td><input type="text" name="db_pass" value="<?php echo $info['db_pass'];?>"></td>
			</tr>
			<tr>
				<th>Admin email address:</th>
				<td><input type="text" name="email" value="<?php echo $info['email'];?>"></td>
			</tr>
		</table>		
		<input type="hidden" value="proceedinstall" name="sec">		
		<input type="submit" value="Proceed to next step >>" name="submit" class="button">
		</form>
		<?php		
	}
	
	# func to write to config file
	function writeConfigFile($info) {
		
		$handle = fopen(SP_INSTALL_CONFIG_SAMPLE, "r");
		$cfgData = fread($handle, filesize(SP_INSTALL_CONFIG_SAMPLE));
		fclose($handle);
		
		
		$search = array('[SP_WEBPATH]', '[DB_NAME]', '[DB_USER]', '[DB_PASSWORD]', '[DB_HOST]', '[DB_ENGINE]');
		$replace = array($info['web_path'], $info['db_name'], $info['db_user'], $info['db_pass'], $info['db_host'], $info['db_engine'] );
		$cfgData = str_replace($search, $replace, $cfgData);
		
		$handle = fopen(SP_INSTALL_CONFIG_FILE, "w");
		fwrite($handle, $cfgData);
		fclose($handle);
	}
	
	function getWebPath(){
	    
	    // to fix the issue with IIS
	    if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1 );
            if (isset($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
            }
        }	    
	    
		$reqUrl = $_SERVER['REQUEST_URI'];
		$count = 0;
		$reqUrl = preg_replace('/\/install\/$/i', '', $reqUrl, 1, $count);		
		if(empty($count)){
			$reqUrl = preg_replace('/\/install\/index.php$/i', '', $reqUrl, 1, $count);
			if(empty($count)){
				$reqUrl = preg_replace('/\/install$/i', '', $reqUrl, 1, $count);
				if(empty($count)) return false;
			}
		}
		$protocol = empty($_SERVER['HTTPS']) ? "http://" : "https://";
		$port = empty($_SERVER['SERVER_PORT']) ?  "" : (int) $_SERVER['SERVER_PORT'];
		$host =  strtolower($_SERVER['HTTP_HOST']);
		if(!empty($port) && ($port <> 443) && ($port <> 80)){
			if(strpos($host, ':') === false){ $host .= ':' . $port; }
		}
		$webPath = $protocol.$host.$reqUrl;
		return $webPath;
	}
	
	# func to proceed installation
	function proceedInstallation($info) {
		$db = New DB();
		
		# checking db settings
		$errMsg = $db->connectDatabase($info['db_host'], $info['db_user'], $info['db_pass'], $info['db_name']);
		if($db->error ){
			$this->startInstallation($info, $errMsg);
			return;
		}
		
		# checking config file settings
		if(!is_writable(SP_INSTALL_CONFIG_FILE)){
			$this->checkRequirements(true);
			return;
		}	
		
		# checking seo panel web path
		$info['web_path'] = $this->getWebPath();
		if(empty($info['web_path'])){
			$errMsg = "Error occured while parsing installation url. Please <a href='http://www.seopanel.in/contact/' target='_blank'>contact</a> Seo Panel team.<br> or <br> Try manual installation by steps specified in <a href='http://www.seopanel.in/install/manual/' target='_blank'>http://www.seopanel.in/install/manual/</a>";
			$this->startInstallation($info, $errMsg);
			return;
		}

		# importing data to db
		$errMsg = $db->importDatabaseFile(SP_INSTALL_DB_FILE);
		if($db->error ){
			$errMsg = "Error occured while importing data: ". $errMsg;
			$this->startInstallation($info, $errMsg);
			return;
		}
		
		# importing text file
		$errMsg = $db->importDatabaseFile(SP_INSTALL_DB_LANG_FILE);
		if($db->error ){
			$errMsg = "Error occured while importing data: ". $errMsg;
			$this->startInstallation($info, $errMsg);
			return;
		}
		
		# write to config file
		$this->writeConfigFile($info);
		
		# create API Key if not exists
		$this->createSeoPanelAPIKey($db);		
		
		if(gethostbynamel('seopanel.in')){
			include_once SP_INSTALL_DIR.'/../libs/spider.class.php';
			include_once(SP_INSTALL_CONFIG_FILE);
			$installUpdateUrl = "http://www.seopanel.in/installupdate.php?url=".urlencode($info['web_path'])."&ip=".$_SERVER['SERVER_ADDR']."&email=".urlencode($info['email']);
			$installUpdateUrl .= "&version=".SP_INSTALLED;
			$spider = New Spider();
			$spider->getContent($installUpdateUrl, false, false);
		}
		
		$db = New DB();
		$db->connectDatabase($info['db_host'], $info['db_user'], $info['db_pass'], $info['db_name']);
		
		// update email for admin
		$sql = "update users set email='".addslashes($info['email'])."' where id=1";
		$db->query($sql);
		
		// select languages list
		$sql = "select * from languages where translated=1";
		$langList = $db->select($sql);

		// select timezones
		$sql = "select * from timezone order by id";
		$timezoneList = $db->select($sql);
		?>		
		<form method="post" action="<?php echo $info['web_path']."/login.php"; ?>">
		<h1 class="BlockHeader">Seo Panel Installation Success</h1>
		<table width="100%" cellspacing="8px" cellpadding="0px" class="formtab">
			<tr><th colspan="2" class="headersuccess">Seo Panel installed successfully!</th></tr>
			<tr>
				<td class="warning" colspan="2">Warning!</td>
			</tr>
			<tr>
				<td style="border: none;" colspan="2">
					<ul class="list">
						<li> Please change permission of config file <b><?php echo SP_CONFIG_FILE;?></b> to avoid security issues.</li>
						<li>Please remove installation directory <b>install</b> to avoid security issues.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td class="warning" style="color:black;" colspan="2">Admin Login</td>
			</tr>
			<tr>
				<td style="border-left: none;">Default Language:</td>
				<td>
					<select name="lang_code">
            			<?php
            			foreach ($langList as $langInfo) {
            				$selected = ($langInfo['lang_code'] == 'en') ? "selected" : "";
            				?>			
            				<option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
            				<?php
            			}
            			?>
            		</select>
				</td>
			</tr>
			<tr>
				<td style="border-left: none;">Default Time Zone:</td>
				<td>
					<select name="time_zone" style="width: 260px;">
            			<?php
            			$listInfo['set_val'] = ini_get('date.timezone');
            			foreach ($timezoneList as $timezoneInfo) {
            				$selected = ($timezoneInfo['timezone_name'] == $listInfo['set_val']) ? 'selected="selected"' : "";
            				?>
            				<option value="<?php echo $timezoneInfo['timezone_name']?>" <?php echo $selected?> ><?php echo $timezoneInfo['timezone_label']?></option>
            				<?php
            			}
            			?>
            		</select>
				</td>
			</tr>
			<tr>
				<td style="border: none;font-weight: normal;font-size: 13px;" colspan="2">
					<b>Username:</b> <?php echo SP_ADMIN_USER?><br>
					<b>Password:</b> <?php echo SP_ADMIN_PASS?><br><br>
					<b>Note:</b> Please change password of admin after first login.
				</td>
			</tr>
		</table>
		<input type="hidden" name="sec" value="login">
		<input type="hidden" name="userName" value="spadmin">
		<input type="hidden" name="password" value="spadmin">
		<input type="submit" value="Proceed to admin login >>" name="submit" class="button">
		</form>
		<?php		
	}
	
	
	# func to check upgrade requirements
	function checkUpgradeRequirements($error=false, $errorMsg='') {

		$phpClass = "red";
		$phpSupport = "No";
		$phpVersion = phpversion();
		if (intval($phpVersion) >= 4 and intval($phpVersion) < 7) {			
			$phpClass = "green";
			$phpSupport = "Yes";
		}
		$phpSupport .= " ( PHP $phpVersion )";
		
		$mysqlClass = "red";
		$mysqlSupport = "No";
		if(function_exists('mysql_query')){
			$mysqlSupport = "Yes";
			$mysqlClass = "green";
		}
		
		$curlClass = "red";
		$curlSupport = "No";
		if(function_exists('curl_version')){
			$version = curl_version();
			$curlSupport = "Yes ( CURL  {$version['version']} )";
			$curlClass = "green";
		}
		
		/*
		$shorttagClass = "red";
		$shorttagSupport = "Disabled";
		if(ini_get('short_open_tag')){
			$shorttagSupport = "Enabled";
			$shorttagClass = "green";
		}*/
		
		$gdClass = "red";
		$gdSupport = "No";
		if(function_exists('gd_info')){
			$version = gd_info();
			$gdSupport = "Yes ( GD  {$version['GD Version']} )";
			$gdClass = "green";
		}		
			
		$tmpClass = "red";
		$tmpSupport = "Not found";
		$tmpFile = SP_INSTALL_DIR.'/../tmp';
		if(file_exists($tmpFile)){
			$tmpSupport = "Found, Unwritable<br><p class='note'><b>Command:</b> chmod -R 777 tmp/</p>";
			if(is_writable($tmpFile)){				
				$tmpSupport = "Found, Writable";				
				$tmpClass = "green";
			}			
		}
		
		$configClass = "red";
		$configSupport = "Not found";
		$configFile = SP_INSTALL_CONFIG_FILE;
		if(file_exists($configFile)){
			$configSupport = "Found";				
			$configClass = "green";
		}

		$dbClass = "red";
		$dbSupport = "Database config variables not defined";
		include_once(SP_INSTALL_CONFIG_FILE);
		if(defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_ENGINE')){
			$db = New DB();
			
			$errMsg = $db->connectDatabase(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if($db->error ){
				$dbSupport = $errMsg;
			}else{				
				$dbSupport = "Connected to database successfully";				
				$dbClass = "green";
			}
		}		
		
		$errMsg = "";
		if($error){
			if(empty($errorMsg)){
				if( ($phpClass == 'red') || ($mysqlClass == 'red') || ($curlClass == 'red') 
				|| ($configClass == 'red') || ($dbClass == 'red') ){
					$errMsg = "Please fix the following errors to proceed to next step!";
				}
			}else{
				$errMsg = $errorMsg;
			}
		}
		
		?>
		<h1 class="BlockHeader">Welcome to Seo panel Upgrade</h1>
		<form method="post">
		<table width="100%" cellspacing="8px" cellpadding="0px" class="formtab">
			<tr><th colspan="2" class="header">Upgrade compatibility</th></tr>
			<tr><td colspan="2" class="error"><?php echo $errMsg;?></td></tr>
			<tr>
				<th>PHP version >= 4.0.0 and &lt; 7.0.0</th>
				<td class="<?php echo $phpClass;?>"><?php echo $phpSupport;?></td>
			</tr>
			<tr>
				<th>MySQL Support</th>
				<td class="<?php echo $mysqlClass;?>"><?php echo $mysqlSupport;?></td>
			</tr>
			<tr>
				<th>CURL Support</th>
				<td class="<?php echo $curlClass;?>"><?php echo $curlSupport; ?></td>
			</tr>
			<?php /*?>
			<tr>
				<th>PHP short_open_tag</th>
				<td class="<?php echo $shorttagClass;?>"><?php echo $shorttagSupport; ?></td>
			</tr>
			<?php */ ?>
			<tr>
				<th>GD graphics support</th>
				<td class="<?php echo $gdClass;?>"><?php echo $gdSupport; ?></td>
			</tr>
			<tr>
				<th>/config/sp-config.php</th>
				<td class="<?php echo $configClass;?>"><?php echo $configSupport; ?></td>
			</tr>
			<tr>
				<th>Database</th>
				<td class="<?php echo $dbClass;?>"><?php echo $dbSupport; ?></td>
			</tr>
			<tr>
				<th>/tmp</th>
				<td class="<?php echo $tmpClass;?>"><?php echo $tmpSupport; ?></td>
			</tr>
		</table>
		<input type="hidden" value="<?php echo $phpClass;?>" name="php_support">
		<input type="hidden" value="<?php echo $mysqlClass;?>" name="mysql_support">
		<input type="hidden" value="<?php echo $curlClass;?>" name="curl_support">
		<?php /* ?><input type="hidden" value="<?php echo $shorttagClass;?>" name="short_open_tag"><?php */ ?>
		<input type="hidden" value="<?php echo $configClass;?>" name="config">
		<input type="hidden" value="<?php echo $dbClass;?>" name="db_support">
		<input type="hidden" value="proceedupgrade" name="sec">
		<?php $submitLabel = defined('SP_INSTALLED') ? "Upgrade to Seo Panel v.".SP_INSTALLED : "Upgrade Seo Panel"; ?>
		<input type="submit" value="<?php echo $submitLabel?> >>" name="submit" class="button">
		</form>
		<?php
	}
	
	function proceedUpgrade($info=''){ 
		if( ($info['php_support'] == 'red') || ($info['mysql_support'] == 'red') || ($info['curl_support'] == 'red')
		|| ($info['config'] == 'red') || ($info['db_support'] == 'red')){
			$this->checkUpgradeRequirements(true);
			return;
		}		
		
		include_once(SP_INSTALL_CONFIG_FILE);
		$db = New DB();
		
		# check database connection
		$errMsg = $db->connectDatabase(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if($db->error){
			$this->checkUpgradeRequirements(true, $errMsg);
			return;
		}
		
		# importing data to db
		$errMsg = $db->importDatabaseFile(SP_UPGRADE_DB_FILE, false);
		/*if($db->error){
			$errMsg = "Error occured while importing data: ". $errMsg;
			$this->checkUpgradeRequirements(true, $errMsg);
			return;
		}*/

		# importing text file
		$errMsg = $db->importDatabaseFile(SP_UPGRADE_DB_LANG_FILE, false);
		$_SESSION['text'] = "";
		
		# create API Key if not exists
		$this->createSeoPanelAPIKey($db);
		
		?>
		<form method="post" action="<?php echo SP_WEBPATH."/login.php"; ?>">
		<h1 class="BlockHeader">Success Seo Panel v.<?php echo SP_INSTALLED;?> Upgrade</h1>
		<table width="100%" cellspacing="8px" cellpadding="0px" class="formtab">
			<tr><th colspan="2" class="headersuccess">Seo Panel upgraded successfully!</th></tr>
			<tr>
				<td class="warning">Warning!</td>
			</tr>
			<tr>
				<td style="border: none;">
					<ul class="list">
						<li>Please remove installation directory <b>install</b> to avoid security issues.</li>
					</ul>
				</td>
			</tr>
		</table>				
		<input type="submit" value="Proceed to admin login >>" name="submit" class="button">
		</form>
		<?php
	}
	
	
	# func to show default install header
	function showDefaultHeader() {
		?>
		<html>
			<head>
				<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
				<link rel="shortcut icon" href="../images/favicon.ico" />
				<title>Seo Panel installation interface</title>
				<meta name="description" content="Seo Panel installation Steps to install seo control panel for managing seo of your sites.">
				<link rel="stylesheet" type="text/css" href="install.css" media="all" />				
			</head>
			<body>
				<div class="installdiv">
		<?php		
	}
	
	# func to show default install footer
	function showDefaultFooter($content='') {
		?>
				</div>
			</body>
		</html>
		<?php		
	}
	
	# function to create seo panel API Key
	function createSeoPanelAPIKey($db) {
	    $sql = "Select id, set_val from settings where set_name='SP_API_KEY'";
	    $apiInfo = $db->select($sql, true);

	    if (empty($apiInfo['set_val'])) {
	        $apiKey = rand(10000000, 100000000);
	        $apiKey .= rand(10000000, 100000000);
	        $apiKey .= rand(10000000, 100000000);
	        $apiKey = md5($apiKey);
	        
	        if (empty($apiInfo['id'])) {
	            $sql = "Insert into settings(set_label,set_name,set_val,set_type) values('Seo Panel API Key', 'SP_API_KEY', '$apiKey', 'large')";
	        } else {
	            $sql = "update settings set set_val='$apiKey' where set_name='SP_API_KEY'";
	        }
	        $apiInfo = $db->query($sql);
	    }
	}	    
}
?>
