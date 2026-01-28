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

class Install {
	
	# func to check requirements
	function checkRequirements($error=false) {		
		
		$phpClass = "red";
		$phpSupport = "No";
		$phpVersion = phpversion();
		if (intval($phpVersion) >= 5) {			
			$phpClass = "green";
			$phpSupport = "Yes";
		}
		$phpSupport .= " ( PHP $phpVersion )";
		
		$mysqlClass = "red";
		$mysqlSupport = "No";
		if(function_exists('mysql_query') || function_exists('mysqli_query')){
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
		<div class="steps">
			<div class="step active"><span class="step-number">1</span> Requirements</div>
			<div class="step-divider"></div>
			<div class="step"><span class="step-number">2</span> Database</div>
			<div class="step-divider"></div>
			<div class="step"><span class="step-number">3</span> Complete</div>
		</div>
		<h1 class="BlockHeader">System Requirements Check</h1>
		<form method="post">
		<div class="content-section">
			<?php if($errMsg){ ?>
			<div class="alert alert-warning"><?php echo $errMsg;?></div>
			<?php } ?>
			<table width="100%" class="formtab">
				<tr><th colspan="2" class="header">Server Compatibility</th></tr>
				<tr>
					<th>PHP Version >= 5.4.0</th>
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
				<tr>
					<th>GD Graphics Support</th>
					<td class="<?php echo $gdClass;?>"><?php echo $gdSupport; ?></td>
				</tr>
				<tr>
					<th>Config File</th>
					<td class="<?php echo $configClass;?>"><?php echo $configSupport; ?></td>
				</tr>
				<tr>
					<th>Temp Directory</th>
					<td class="<?php echo $tmpClass;?>"><?php echo $tmpSupport; ?></td>
				</tr>
			</table>
		</div>
		<input type="hidden" value="<?php echo $phpClass;?>" name="php_support">
		<input type="hidden" value="<?php echo $mysqlClass;?>" name="mysql_support">
		<input type="hidden" value="<?php echo $curlClass;?>" name="curl_support">
		<input type="hidden" value="<?php echo $configClass;?>" name="config">
		<input type="hidden" value="startinstall" name="sec">
		<div class="button-section">
			<input type="submit" value="Continue &rarr;" name="submit" class="button">
		</div>
		</form>
		<?php
	}
	
	# func to start installation
	function startInstallation($info=[], $errMsg='') {
		if( ($info['php_support'] == 'red') || ($info['mysql_support'] == 'red') || ($info['curl_support'] == 'red')
		|| ($info['config'] == 'red') ){
			$this->checkRequirements(true);
			return;
		}
		
		// check whether installation from the docker, then use env variables
		$info['db_host'] = !empty($info['db_host']) ? $info['db_host'] : getenv('MYSQL_DB_HOST');
		$info['db_name'] = !empty($info['db_name']) ? $info['db_name'] : getenv('MYSQL_DATABASE');
		$info['db_user'] = !empty($info['db_user']) ? $info['db_user'] : getenv('MYSQL_USER');
		$info['db_pass'] = !empty($info['db_pass']) ? $info['db_pass'] : getenv('MYSQL_PASSWORD');
		?>
		<div class="steps">
			<div class="step completed"><span class="step-number"></span> Requirements</div>
			<div class="step-divider"></div>
			<div class="step active"><span class="step-number">2</span> Database</div>
			<div class="step-divider"></div>
			<div class="step"><span class="step-number">3</span> Complete</div>
		</div>
		<h1 class="BlockHeader">Database Configuration</h1>
		<form method="post" id="installForm" onsubmit="return validateInstallForm()">
		<div class="content-section">
			<?php if($errMsg){ ?>
			<div class="alert alert-warning"><?php echo $errMsg;?></div>
			<?php } ?>
			<div id="validationErrors" class="alert alert-warning" style="display:none;"></div>
			<table width="100%" class="formtab">
				<tr><th colspan="2" class="header">Database Settings</th></tr>
				<tr>
					<th>Database Type</th>
					<td>
						<select name="db_engine" required>
							<option value="mysql">MySQL / MariaDB</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Database Host <span class="required">*</span></th>
					<td><input type="text" name="db_host" id="db_host" value="<?php echo empty($info['db_host']) ? "localhost" : $info['db_host'];?>" placeholder="localhost" required></td>
				</tr>
				<tr>
					<th>Database Name <span class="required">*</span></th>
					<td><input type="text" name="db_name" id="db_name" value="<?php echo isset($info['db_name']) ? $info['db_name'] : '';?>" placeholder="seopanel" required></td>
				</tr>
				<tr>
					<th>Database Username <span class="required">*</span></th>
					<td><input type="text" name="db_user" id="db_user" value="<?php echo isset($info['db_user']) ? $info['db_user'] : '';?>" placeholder="root" required></td>
				</tr>
				<tr>
					<th>Database Password</th>
					<td><input type="password" name="db_pass" id="db_pass" value="<?php echo isset($info['db_pass']) ? $info['db_pass'] : '';?>" placeholder="Enter password"></td>
				</tr>
				<tr><th colspan="2" class="header" style="margin-top: 15px;">Administrator Settings</th></tr>
				<tr>
					<th>Admin Email <span class="required">*</span></th>
					<td><input type="email" name="email" id="email" value="<?php echo isset($info['email']) ? $info['email'] : '';?>" placeholder="admin@example.com" required></td>
				</tr>
			</table>
		</div>
		<input type="hidden" value="proceedinstall" name="sec">
		<div class="button-section">
			<input type="submit" value="Install Seo Panel &rarr;" name="submit" class="button">
		</div>
		</form>
		<script>
		function validateInstallForm() {
			var errors = [];
			var errDiv = document.getElementById('validationErrors');

			var dbHost = document.getElementById('db_host').value.trim();
			var dbName = document.getElementById('db_name').value.trim();
			var dbUser = document.getElementById('db_user').value.trim();
			var email = document.getElementById('email').value.trim();

			if (!dbHost) errors.push('Database Host is required');
			if (!dbName) errors.push('Database Name is required');
			if (!dbUser) errors.push('Database Username is required');
			if (!email) {
				errors.push('Admin Email is required');
			} else if (!isValidEmail(email)) {
				errors.push('Please enter a valid email address');
			}

			if (errors.length > 0) {
				errDiv.innerHTML = '<strong>Please fix the following errors:</strong><ul><li>' + errors.join('</li><li>') + '</li></ul>';
				errDiv.style.display = 'block';
				window.scrollTo(0, 0);
				return false;
			}

			errDiv.style.display = 'none';
			return true;
		}

		function isValidEmail(email) {
			var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return re.test(email);
		}
		</script>
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
		
		// find protocol of the server to get seo panel installation url
		if (isset($_SERVER['HTTPS']) &&	($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
		isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		
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
		
		// if mysqli function exists
		$db = function_exists('mysqli_query') ? New DBI() : New DB();
		
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
			$errMsg = "Error occured while parsing installation url. Please <a href='https://www.seopanel.org/contact/' target='_blank'>contact</a> Seo Panel team.<br> or <br> Try manual installation by steps specified in <a href='http://www.seopanel.org/install/manual/' target='_blank'>http://www.seopanel.org/install/manual/</a>";
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
		
		if(gethostbynamel('seopanel.org')){
		    include_once(SP_INSTALL_DIR.'/../libs/spider.class.php');
		    include_once(SP_INSTALL_CONFIG_FILE);
		    include_once(SP_INSTALL_CONFIG_FILE_EXTRA);
			$installUpdateUrl = "https://www.seopanel.org/installupdate.php?url=".urlencode($info['web_path'])."&ip=".$_SERVER['SERVER_ADDR']."&email=".urlencode($info['email']);
			$installUpdateUrl .= "&version=".SP_INSTALLED;
			$spider = New Spider();
			$spider->getContent($installUpdateUrl, false, false);
		}
		
		$db = function_exists('mysqli_query') ? New DBI() : New DB();
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
		<div class="steps">
			<div class="step completed"><span class="step-number"></span> Requirements</div>
			<div class="step-divider"></div>
			<div class="step completed"><span class="step-number"></span> Database</div>
			<div class="step-divider"></div>
			<div class="step active"><span class="step-number">3</span> Complete</div>
		</div>
		<h1 class="BlockHeader">Installation Complete</h1>
		<form method="post" action="<?php echo $info['web_path']."/login.php"; ?>">
		<div class="content-section">
			<div class="success-box">
				<h3>Seo Panel Installed Successfully!</h3>
				<p>Your SEO control panel is ready to use.</p>
			</div>

			<div class="alert alert-warning">
				<strong>Important Security Steps:</strong>
				<ul>
					<li>Change permission of <b><?php echo SP_CONFIG_FILE;?></b> (chmod 644)</li>
					<li>Remove the <b>install</b> directory completely</li>
				</ul>
			</div>

			<div class="credentials-box">
				<div class="row">
					<div class="col">
						<div class="label">Username</div>
						<div class="value"><?php echo SP_ADMIN_USER?></div>
					</div>
					<div class="col">
						<div class="label">Password</div>
						<div class="value"><?php echo SP_ADMIN_PASS?></div>
					</div>
				</div>
			</div>

			<div class="alert alert-info">
				<strong>Note:</strong> Please change the admin password after your first login.
			</div>

			<table width="100%" class="formtab">
				<tr><th colspan="2" class="header">Initial Settings</th></tr>
				<tr>
					<th>Default Language</th>
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
					<th>Time Zone</th>
					<td>
						<select name="time_zone">
							<?php
							$listInfo['set_val'] = ini_get('date.timezone');
							foreach ($timezoneList as $timezoneInfo) {
								$selected = (trim($timezoneInfo['timezone_name']) == $listInfo['set_val']) ? 'selected="selected"' : "";
								?>
								<option value="<?php echo $timezoneInfo['timezone_name']?>" <?php echo $selected?>><?php echo $timezoneInfo['timezone_label']?></option>
								<?php
							}
							?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="sec" value="login">
		<input type="hidden" name="userName" value="spadmin">
		<input type="hidden" name="password" value="spadmin">
		<input type="hidden" name="source" value="install">
		<div class="button-section">
			<input type="submit" value="Login to Admin Panel &rarr;" name="submit" class="button">
		</div>
		</form>
		<?php
	}
	
	
	# func to check upgrade requirements
	function checkUpgradeRequirements($error=false, $errorMsg='') {

		$phpClass = "red";
		$phpSupport = "No";
		$phpVersion = phpversion();
		if (intval($phpVersion) >= 5) {			
			$phpClass = "green";
			$phpSupport = "Yes";
		}
		$phpSupport .= " ( PHP $phpVersion )";
		
		$mysqlClass = "red";
		$mysqlSupport = "No";
		if(function_exists('mysql_query')|| function_exists('mysqli_query')){
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
			$db = function_exists('mysqli_query') ? New DBI() : New DB();
			
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
		<div class="steps">
			<div class="step active"><span class="step-number">1</span> Check</div>
			<div class="step-divider"></div>
			<div class="step"><span class="step-number">2</span> Upgrade</div>
		</div>
		<h1 class="BlockHeader">Upgrade Requirements Check</h1>
		<form method="post">
		<div class="content-section">
			<?php if($errMsg){ ?>
			<div class="alert alert-warning"><?php echo $errMsg;?></div>
			<?php } ?>
			<table width="100%" class="formtab">
				<tr><th colspan="2" class="header">System Compatibility</th></tr>
				<tr>
					<th>PHP Version >= 5.4.0</th>
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
				<tr>
					<th>GD Graphics Support</th>
					<td class="<?php echo $gdClass;?>"><?php echo $gdSupport; ?></td>
				</tr>
				<tr>
					<th>Config File</th>
					<td class="<?php echo $configClass;?>"><?php echo $configSupport; ?></td>
				</tr>
				<tr>
					<th>Database Connection</th>
					<td class="<?php echo $dbClass;?>"><?php echo $dbSupport; ?></td>
				</tr>
				<tr>
					<th>Temp Directory</th>
					<td class="<?php echo $tmpClass;?>"><?php echo $tmpSupport; ?></td>
				</tr>
			</table>
		</div>
		<input type="hidden" value="<?php echo $phpClass;?>" name="php_support">
		<input type="hidden" value="<?php echo $mysqlClass;?>" name="mysql_support">
		<input type="hidden" value="<?php echo $curlClass;?>" name="curl_support">
		<input type="hidden" value="<?php echo $configClass;?>" name="config">
		<input type="hidden" value="<?php echo $dbClass;?>" name="db_support">
		<input type="hidden" value="proceedupgrade" name="sec">
		<?php $submitLabel = defined('SP_INSTALLED') ? "Upgrade to v".SP_INSTALLED : "Upgrade Seo Panel"; ?>
		<div class="button-section">
			<input type="submit" value="<?php echo $submitLabel?> &rarr;" name="submit" class="button">
		</div>
		</form>
		<?php
	}
	
	function getUpgradeDBFiles($db) {
		$upgradeFileList = array();
		$spVersionList = array(
			'3.8.0',
			'3.9.0',
			'3.10.0',
			'3.11.0',
			'3.12.0',
			'3.13.0',
			'3.14.0',
			'3.15.0',
			'3.16.0',
			'3.17.0',
			'3.18.0',
			'4.0.0',
			'4.1.0',
			'4.2.0',
			'4.3.0',
			'4.4.0',
		    '4.5.0',
		    '4.6.0',
		    '4.7.0',
		    '4.8.0',
		    '4.9.0',
		    '4.10.0',
		    '4.11.0',
		    '5.0.0',
		    '5.1.0',
		);
		
		// get current version number
		$sql = "Select set_val from settings where set_name='SP_VERSION_NUMBER'";
		$versionInfo = $db->select($sql, true);
		$currentVersion = !empty($versionInfo['set_val']) ? $versionInfo['set_val'] : '3.8.0';
		
		// if current version is set
		if ($currentVersion) {
			
			$index = array_search($currentVersion, $spVersionList);
			$lastIndex = count($spVersionList) - 1;
		
			// if it is not last index value
			if ($index != $lastIndex) {
				$prevIndex = $index;
			
				// loop through the versions
				for ($i = $index + 1; $i <= $lastIndex; $i++) {
					$upgradeFileList[] = SP_INSTALL_DIR . "/data/upgrade_v$spVersionList[$prevIndex]_v$spVersionList[$i].sql";
					$prevIndex = $i;
				}
				
			}
			
		}
		
		$upgradeFileList[] = SP_UPGRADE_DB_FILE;
		return $upgradeFileList;
		
	}
	
	function proceedUpgrade($info=[]){ 
		if( ($info['php_support'] == 'red') || ($info['mysql_support'] == 'red') || ($info['curl_support'] == 'red')
		|| ($info['config'] == 'red') || ($info['db_support'] == 'red')){
			$this->checkUpgradeRequirements(true);
			return;
		}		
		
		include_once(SP_INSTALL_CONFIG_FILE);
		$db = function_exists('mysqli_query') ? New DBI() : New DB();
		
		// check database connection
		$errMsg = $db->connectDatabase(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if($db->error){
			$this->checkUpgradeRequirements(true, $errMsg);
			return;
		}
		
		// loop through upgrade files and import data to db
		$upgradeFileList = $this->getUpgradeDBFiles($db);
		foreach ($upgradeFileList as $dbFile) {
			$errMsg = $db->importDatabaseFile($dbFile, false);
		}

		// importing text file
		$errMsg = $db->importDatabaseFile(SP_UPGRADE_DB_LANG_FILE, false);
		$_SESSION['text'] = "";
		
		# create API Key if not exists
		$this->createSeoPanelAPIKey($db);
		
		?>
		<div class="steps">
			<div class="step completed"><span class="step-number"></span> Check</div>
			<div class="step-divider"></div>
			<div class="step active"><span class="step-number">2</span> Complete</div>
		</div>
		<h1 class="BlockHeader">Upgrade Complete</h1>
		<form method="post" action="<?php echo SP_WEBPATH."/login.php"; ?>">
		<div class="content-section">
			<div class="success-box">
				<h3>Upgraded to Seo Panel v<?php echo SP_INSTALLED;?></h3>
				<p>Your SEO Panel has been upgraded successfully.</p>
			</div>

			<div class="alert alert-warning">
				<strong>Important Security Step:</strong>
				<ul>
					<li>Remove the <b>install</b> directory to avoid security issues</li>
				</ul>
			</div>
		</div>
		<input type="hidden" name="source" value="install">
		<div class="button-section">
			<input type="submit" value="Login to Admin Panel &rarr;" name="submit" class="button">
		</div>
		</form>
		<?php
	}
	
	
	# func to show default install header
	function showDefaultHeader() {
		?>
		<!DOCTYPE html>
		<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<link rel="shortcut icon" href="../images/favicon.ico" />
				<title>Seo Panel Installation</title>
				<meta name="description" content="Seo Panel installation - Install SEO control panel for managing SEO of your sites.">
				<link rel="stylesheet" type="text/css" href="install.css" media="all" />
			</head>
			<body>
				<div class="installdiv">
					<div class="logo-section">
						<img src="../images/logo_red_sm.png" alt="Seo Panel">
						<h2>Installation Wizard</h2>
					</div>
		<?php
	}
	
	# func to show default install footer
	function showDefaultFooter($content='') {
		?>
				<div class="install-footer">
					<a href="https://www.seopanel.org" target="_blank">www.seopanel.org</a>
					<span class="separator">|</span>
					<a href="https://www.seopanel.org/contact/" target="_blank">Contact Us</a>
					<span class="separator">|</span>
					<a href="https://www.seopanel.org/docs/" target="_blank">Documentation</a>
				</div>
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
