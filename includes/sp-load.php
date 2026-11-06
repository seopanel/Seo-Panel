<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
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

$abspath = getcwd();
$abspath = preg_replace('/\/includes$/', '', $abspath);
if (!file_exists($abspath."/config/sp-config.php")) {
   $abspath = dirname ( realpath ( __FILE__ ) );
   $abspath = preg_replace('/\/includes$/', '', $abspath);
}
define( 'SP_ABSPATH', $abspath );

// global variable for sp db connection id
$SP_DB_CONN_OBJ = false;

// since PHP 8.1, mysqli defaults to throwing mysqli_sql_exception on any SQL
// error instead of returning false - but the whole app (select()/query() in
// mysqlihelper.class.php, and every caller's `if (!$result)` check) is built
// around the pre-8.1 false-return behavior. Without this, a bad query is an
// uncaught fatal error instead of the graceful false + error_log() the app
// expects.
mysqli_report(MYSQLI_REPORT_OFF);

if(file_exists(SP_ABSPATH."/config/sp-config.php")){
	
	# loads seo panel main config file
	include_once(SP_ABSPATH."/config/sp-config.php");
	
	if(!defined('SP_INSTALLED')){
		header('Location: install/');
		exit;
	}
	
	# check for curl with php 
	if (!function_exists('curl_init')) {
		print "
			<div style='margin:50px 250px;font-size:13px;border:1px solid black;padding:5px;line-height:30px;background-color:#f4f7fa;color:#da3838'>
			The <b>CURL is not Installed with PHP</b> in your <b>Server</b>.<br>
			Please <b>INSTALL</b> it by referring <br><a href='http://php.net/manual/en/curl.setup.php'>http://php.net/manual/en/curl.setup.php</a>  
			<br>or <br>Please <b>contact your web hosting provider to INSTALL</b> it.</div>";
		exit;
	}
	
	# load seo panel extra config variables
	if(file_exists(SP_ABSPATH."/config/sp-config-extra.php")){
		include_once(SP_ABSPATH."/config/sp-config-extra.php");
	}

	# debug settings
	if (SP_DEBUG){
		@ini_set("display_errors", "On");
		@ini_set("display_startup_errors", "On");
		error_reporting(E_ALL ^ E_NOTICE);
	} else {
		// "display_erros" (misspelled) previously here meant this call
		// silently no-oped on an unknown ini key, so display_errors was
		// NEVER actually turned off by SP_DEBUG=0 - only error_reporting()
		// was. An uncaught fatal error/exception bypasses error_reporting()
		// entirely and is printed whenever display_errors is On, so on any
		// host whose php.ini ships it On by default (common on shared/
		// budget hosting, which this self-hosted app explicitly targets),
		// a real fatal error could leak a full stack trace and file paths
		// to an unauthenticated visitor even with SP_DEBUG=0.
		@ini_set("display_errors", "Off");
		@ini_set("display_startup_errors", "Off");
		error_reporting(0);
	}

	// security headers - previously never set anywhere (checked: no
	// .htaccess rule, no header() call in any entry point), so every
	// page - including the login page and every authenticated admin
	// page - could be framed by any external site, enabling classic
	// clickjacking/UI-redress attacks (e.g. an invisible iframe over a
	// fake "claim your prize" button, positioned so a real click lands
	// on this app's own "Delete"/"Activate" button instead). Set here so
	// every entry point gets it for free, rather than requiring each of
	// the ~100 top-level PHP files to set it individually. Only two,
	// deliberately conservative headers - not a full CSP: this codebase
	// has inline <script> blocks throughout (checked extensively this
	// session), so a real Content-Security-Policy would need
	// 'unsafe-inline' anyway (minimal real protection) or a large,
	// separate refactor to nonce/hash every inline script - out of scope
	// here. Not Strict-Transport-Security either - this is a self-hosted
	// app not guaranteed to always be served over HTTPS; forcing HSTS on
	// an HTTP-only install would break it, the same reasoning already
	// applied to the Secure cookie flag in Session::startSession().
	if (!headers_sent()) {
		// SAMEORIGIN (not DENY) - this app is never meant to be framed
		// by a THIRD-PARTY site, but same-origin framing is left
		// available in case any current or future feature relies on it
		header('X-Frame-Options: SAMEORIGIN');
		// blocks a browser from MIME-sniffing a response into an
		// executable type (e.g. treating an uploaded/served file as
		// text/html) against the Content-Type it was actually served
		// with - cheap, zero-risk hardening, no legitimate behavior here
		// depends on sniffing being allowed
		header('X-Content-Type-Options: nosniff');
	}

	# system settings
	define('SP_CONFPATH', SP_ABSPATH."/config");
	define('SP_CTRLPATH', SP_ABSPATH."/controllers");
	define('SP_INCPATH', SP_ABSPATH."/includes");
	define('SP_LIBPATH', SP_ABSPATH."/libs");
	define('SP_TMPPATH', SP_ABSPATH."/tmp");	
	define('SP_PLUGINPATH', SP_ABSPATH."/plugins");	
	define('SP_THEMEPATH', SP_ABSPATH."/themes");
	define('SP_DATAPATH', SP_ABSPATH."/install/data");
	define('SP_JSPATH', SP_WEBPATH."/js");
	define('SP_IMGPATH', SP_WEBPATH."/images");

	// include common functions
	include_once(SP_INCPATH.'/sp-common.php');

	// UserTokenController (OAuth access/refresh tokens) and UserController's
	// TOTP secret encryption both call sodium_crypto_secretbox()/the
	// SODIUM_* constants directly. ext-sodium has shipped with PHP core
	// since 7.2, but isn't guaranteed compiled in - confirmed missing on
	// this project's own XAMPP/Linux bundle (no sodium.so in the PHP
	// extension dir at all), which fatals every encrypt/decrypt call
	// (e.g. "Undefined constant SODIUM_CRYPTO_SECRETBOX_KEYBYTES"),
	// breaking Google Analytics/Search Console connect and TOTP
	// enrollment outright. paragonie/sodium_compat is the standard fix
	// for exactly this gap - WordPress core and phpMyAdmin (both already
	// present on this very machine) bundle the same library for the same
	// reason. Its autoloader only defines the sodium_*()/SODIUM_* symbols
	// when they don't already exist, so requiring it unconditionally is
	// safe and a no-op wherever the native extension is present.
	if (!extension_loaded('sodium')) {
	    include_once(SP_LIBPATH.'/sodium_compat/autoload.php');
	}

	# create database object
	include_once(SP_LIBPATH."/database.class.php");
	$dbObj = New Database(DB_ENGINE);
	$dbConn = $dbObj->dbConnect();
	
	// set system settings variables
	$sql = "select * from settings order by id";
	$settingsList = $dbConn->select($sql);
	foreach($settingsList as $settingsInfo){
		if(!defined($settingsInfo['set_name'])){
			define($settingsInfo['set_name'], $settingsInfo['set_val']);
		}
	}

	// Every setting below was introduced by some later upgrade_vX_vY.sql
	// migration and is read unconditionally (no defined() guard) in one
	// or more view/controller files - fine for any database that has
	// actually run that migration, but a pre-upgrade admin logging in on
	// an older database - which is exactly what they need to do to REACH
	// the upgrade wizard in the first place, since it requires an
	// authenticated session - hits "Uncaught Error: Undefined constant
	// ..." on whichever page happens to reference it first. SP_DEMO alone
	// is referenced, unguarded, in well over a hundred view files across
	// both themes, including the shared layout every single page (login
	// included) renders through - a hard lockout identical in shape to
	// the SP_SPAPI_REGISTERED rate-limit lockout fixed earlier, just one
	// level more fundamental. Retrofitting defined() checks into every
	// call site isn't practical; defining each one's real fresh-install
	// default here (see install/data/seopanel.sql) protects all of them
	// at once, for any database old enough to predate any of these -
	// found by diffing seopanel.sql's current setting names against the
	// oldest schema this project has a git tag for (4.1.0) and checking
	// which of the new ones are actually read without a defined() guard
	// anywhere. A real admin's own settings still win: this only fills
	// in a constant the query above found no row for at all.
	if (!defined('QWP_PROXY_BLOCK_URLS')) { define('QWP_PROXY_BLOCK_URLS', ''); }
	if (!defined('SA_EXCLUDE_FILE_EXTENSIONS')) { define('SA_EXCLUDE_FILE_EXTENSIONS', 'zip,gz,tar,png,jpg,jpeg,gif,mp3,flv,pdf,m4a,avi,mov,wmv,mp4,doc,docx,xls,xlsx,ppt,pptx,rar,7z,exe,dmg,iso'); }
	if (!defined('SP_AIO_RETENTION_DAYS')) { define('SP_AIO_RETENTION_DAYS', '90'); }
	if (!defined('SP_AIO_ROLLING_WINDOW')) { define('SP_AIO_ROLLING_WINDOW', '7'); }
	if (!defined('SP_AIO_STALE_DAYS')) { define('SP_AIO_STALE_DAYS', '7'); }
	if (!defined('SP_AIO_SUBDOMAIN_MATCH')) { define('SP_AIO_SUBDOMAIN_MATCH', 'registrable'); }
	if (!defined('SP_CRON_JOB_TIMING_RETENTION_DAYS')) { define('SP_CRON_JOB_TIMING_RETENTION_DAYS', '14'); }
	if (!defined('SP_CRON_PING_ENABLED')) { define('SP_CRON_PING_ENABLED', '0'); }
	if (!defined('SP_CRON_PING_SECRET')) { define('SP_CRON_PING_SECRET', ''); }
	if (!defined('SP_CRON_RUN_LOG_RETENTION_DAYS')) { define('SP_CRON_RUN_LOG_RETENTION_DAYS', '30'); }
	if (!defined('SP_DEFAULT_COUNTRY')) { define('SP_DEFAULT_COUNTRY', 'US'); }
	if (!defined('SP_DEMO')) { define('SP_DEMO', '0'); }
	if (!defined('SP_DFS_API_LOGIN')) { define('SP_DFS_API_LOGIN', ''); }
	if (!defined('SP_DFS_API_PASSWORD')) { define('SP_DFS_API_PASSWORD', ''); }
	if (!defined('SP_DFS_BALANCE')) { define('SP_DFS_BALANCE', '0'); }
	if (!defined('SP_ENABLE_DFS')) { define('SP_ENABLE_DFS', '0'); }
	if (!defined('SP_ENABLE_DFS_BACKLINK')) { define('SP_ENABLE_DFS_BACKLINK', '0'); }
	if (!defined('SP_ENABLE_DFS_BACK_SATU')) { define('SP_ENABLE_DFS_BACK_SATU', '0'); }
	if (!defined('SP_ENABLE_DFS_REVIEW')) { define('SP_ENABLE_DFS_REVIEW', '1'); }
	if (!defined('SP_ENABLE_DFS_SANDBOX')) { define('SP_ENABLE_DFS_SANDBOX', '0'); }
	if (!defined('SP_ENABLE_DFS_SEARCH_VOLUME')) { define('SP_ENABLE_DFS_SEARCH_VOLUME', '1'); }
	if (!defined('SP_ENABLE_DFS_SERP')) { define('SP_ENABLE_DFS_SERP', '1'); }
	if (!defined('SP_ENABLE_LOCAL_AI')) { define('SP_ENABLE_LOCAL_AI', '0'); }
	if (!defined('SP_ENABLE_RECAPTCHA')) { define('SP_ENABLE_RECAPTCHA', '0'); }
	if (!defined('SP_ENABLE_SPAPI_SEARCH_VOLUME')) { define('SP_ENABLE_SPAPI_SEARCH_VOLUME', '1'); }
	if (!defined('SP_ENABLE_SPAPI_SERP')) { define('SP_ENABLE_SPAPI_SERP', '1'); }
	if (!defined('SP_FEATURE_TOUR')) { define('SP_FEATURE_TOUR', '1'); }
	if (!defined('SP_GDPR_COOKIE_BANNER')) { define('SP_GDPR_COOKIE_BANNER', '0'); }
	if (!defined('SP_HOSTED_VERSION')) { define('SP_HOSTED_VERSION', '0'); }
	if (!defined('SP_JOB_QUEUE_BUDGET_SECONDS')) { define('SP_JOB_QUEUE_BUDGET_SECONDS', '20'); }
	if (!defined('SP_JOB_QUEUE_ENABLED')) { define('SP_JOB_QUEUE_ENABLED', '1'); }
	if (!defined('SP_JOB_QUEUE_RETENTION_DAYS')) { define('SP_JOB_QUEUE_RETENTION_DAYS', '7'); }
	if (!defined('SP_LOCAL_AI_MODEL')) { define('SP_LOCAL_AI_MODEL', 'llama3.2:3b'); }
	if (!defined('SP_LOCAL_AI_URL')) { define('SP_LOCAL_AI_URL', 'http://localhost:11434'); }
	if (!defined('SP_RECAPTCHA_SECRET_KEY')) { define('SP_RECAPTCHA_SECRET_KEY', ''); }
	if (!defined('SP_RECAPTCHA_SITE_KEY')) { define('SP_RECAPTCHA_SITE_KEY', ''); }
	if (!defined('SP_SENDGRID_API')) { define('SP_SENDGRID_API', '0'); }
	if (!defined('SP_SPAPI_EMAIL')) { define('SP_SPAPI_EMAIL', ''); }
	if (!defined('SP_SPAPI_KEY')) { define('SP_SPAPI_KEY', ''); }
	if (!defined('SP_SPAPI_NAME')) { define('SP_SPAPI_NAME', ''); }
	if (!defined('SP_SPAPI_REGISTERED')) { define('SP_SPAPI_REGISTERED', '0'); }
	if (!defined('SP_SPAPI_URL')) { define('SP_SPAPI_URL', 'https://api.seopanel.org/api/v1'); }
	if (!defined('SP_USE_SAMPLE_API_DATA')) { define('SP_USE_SAMPLE_API_DATA', '0'); }

	// set system timezone
	if (defined('SP_TIME_ZONE') && (SP_TIME_ZONE != '') ) {
	
		// set timezone for mysql
		@ini_set( 'date.timezone', SP_TIME_ZONE);
		$sql = "select * from timezone where timezone_name='". SP_TIME_ZONE ."'";
		$timezoneInfo = $dbConn->select($sql, true);
	
		// set gmt difference
		if (!empty($timezoneInfo['gmt_diff'])) {
			$sql = "set time_zone = '".$timezoneInfo['gmt_diff']."'";
			$dbConn->query($sql);
		}
		
	}
	
	# web theme settings
	$sql = "select * from themes where status=1 order by id";
	$themeInfo = $dbConn->select($sql, true);
	$themeLocation = empty($themeInfo['folder']) ? "themes/classic" : "themes/".$themeInfo['folder'];
	define('SP_THEME_ABSPATH', SP_ABSPATH."/$themeLocation");
	define('SP_VIEWPATH', SP_ABSPATH."/$themeLocation/views");
	define('SP_CSSPATH', SP_WEBPATH."/$themeLocation/css");
	define('SP_THEME_WEB_PATH', SP_WEBPATH."/$themeLocation");	

	# to prevent sql injection
	if(!empty($_SERVER['REQUEST_METHOD']) && SP_PREVENT_SQL_INJECTION){
	    
	    # merge all post and get elements
        foreach (array_merge($_GET, $_POST) AS $name => $value) {
            
            # if not a numeric parameter
            if (is_string($value) && !empty($value) && !is_numeric($value)) {
            	
            	# exclude conditions for html, javascript save to database
            	if (in_array($name, array('SP_GOOGLE_ANALYTICS_TRACK_CODE'))) {
            		continue;
            	}
               
                # Search for patterns in the value of the parameter that indicate an SQL injection
                $pattern = '/(and|or)[\s\(\)\/\*]+(update|delete|select)\W|(select|update).+\.(password|email)|(select|update|delete).+users|<script>|<\/script>/im';
                
                # replace all matched strings
                while (preg_match($pattern, $value)) {
                    if (isset($_GET[$name])) {
                        $value = $_GET[$name] = $_REQUEST[$name] = preg_replace($pattern, '', $value);
                    } else {
                        $value = $_POST[$name] = $_REQUEST[$name] = preg_replace($pattern, '', $value);
                    }
                }
            }
        }
    }
    
    // format urls
    if (isset($_REQUEST['url'])) {
        $_REQUEST['url'] = html_entity_decode($_REQUEST['url'], ENT_QUOTES);
    }
    
    // default assignments if it is empty
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
        $_GET['sec'] = !empty($_GET['sec']) ? $_GET['sec'] : "";
    }
    
    // default assignments if it is empty
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST['sec'] = !empty($_POST['sec']) ? $_POST['sec'] : "";
    }
    
	// create super class object
	include_once(SP_LIBPATH."/seopanel.class.php");
	$seopanel = New Seopanel();
	$seopanel->loadSeoPanel();

	// special conditional access
	$seopanel->setSpecialConditionalAccessGlobals();
		
}else{
	die("<p>The config file could not be found.</p><p><a href=\"install/index.php\">Click here to install Seo Panel.</a></p>");
}

?>