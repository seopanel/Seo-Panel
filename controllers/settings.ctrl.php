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

# class defines all settings controller functions
class SettingsController extends Controller{
	
	var $layout = 'ajax';
	
	# function to show system settings
	function showSystemSettings($category='system') {
	    $category = htmlentities($category, ENT_QUOTES);
	    $category = addslashes($category);
		$this->set('list', $this->__getAllSettings(true, 1, $category));
		
		if ($category == 'system') {		
    		$langCtrler = New LanguageController();
    		$langList = $langCtrler->__getAllLanguages(" where translated=1");
    		$this->set('langList', $langList);
    		
    		$timezoneCtrler = New TimeZoneController();
    		$timezoneList = $timezoneCtrler->__getAllTimezones();
    		$this->set('timezoneList', $timezoneList);
    		
    		$currencyCtrler = new CurrencyController();
	    	$this->set('currencyList', $currencyCtrler->__getAllCurrency(" and paypal=1 and status=1 and name!=''"));
	    	
	    	$countryCtrl = new CountryController();
	    	$this->set('countryList', $countryCtrl->__getAllCountryAsList());
		}
		
		$this->set('category', $category);
		
		// if report settings page
		if ($category == 'report') {		    
		    
            $spTextReport = $this->getLanguageTexts('report', $_SESSION['lang_code']);
            $this->set('spTextReport', $spTextReport);		    
		    $scheduleList = array(
    			1 => $_SESSION['text']['label']['Daily'],
    			2 => $spTextReport['2 Days'],
    			7 => $_SESSION['text']['label']['Weekly'],
    			30 => $_SESSION['text']['label']['Monthly'],
    		);
		    $this->set('scheduleList', $scheduleList);		    
	        $this->render('settings/showreportsettings');
	        
		} else if ($category == 'proxy') {		    
		    
            $spTextProxy = $this->getLanguageTexts('proxy', $_SESSION['lang_code']);
            $this->set('spTextProxy', $spTextProxy);		    
	        $this->render('settings/showproxysettings');
		} else {
			
			$spTextPanel = $this->getLanguageTexts('panel', $_SESSION['lang_code']);

			// switch through category
			switch ($category) {
				
				case "api":
					$this->set('headLabel', $spTextPanel['API Settings']);					
					break;
				
				case "moz":
					$this->set('headLabel', $spTextPanel['MOZ Settings']);					
					break;
				
				case "google":
					$this->set('headLabel', $spTextPanel['Google Settings']);					
					break;
					
				case "dataforseo":
				    $this->set('headLabel', $spTextPanel['DataForSEO Settings']);
				    break;
					
				case "mail":
				    $this->set('headLabel', $spTextPanel['Mail Settings']);
				    break;

				case "seopanel_api":
				    $this->set('headLabel', $spTextPanel['Seo Panel API Settings']);
				    include_once(SP_CTRLPATH . "/information.ctrl.php");
				    $informationCtrler = new InformationController();
				    $spapiCheckInfo = $informationCtrler->__getTodayInformation('spapi_check');
				    $this->set('spapiCheckResult', !empty($spapiCheckInfo['page']) ? $spapiCheckInfo['page'] : '');
				    break;

				default:
					break;
				
			}
			
		    $this->render('settings/showsettings');
		}
	}
	
	function updateSystemSettings($postInfo) {
		
		$setList = $this->__getAllSettings(true, 1, $postInfo['category']);
		foreach($setList as $setInfo){
		    
		    // exclude from update
		    if ($setInfo['set_name'] == 'SP_DFS_BALANCE') {
		        continue;
		    }

			switch($setInfo['set_name']){
				
				case "SP_PAGINGNO":
					$postInfo[$setInfo['set_name']] = intval($postInfo[$setInfo['set_name']]);
					$postInfo[$setInfo['set_name']] = empty($postInfo[$setInfo['set_name']]) ? SP_PAGINGNO_DEFAULT : $postInfo[$setInfo['set_name']];				
					break;
				
				case "SP_CRAWL_DELAY":
				case "SP_USER_GEN_REPORT":
				case "SA_CRAWL_DELAY_TIME":
				case "SA_MAX_NO_PAGES":
				case "SP_NUMBER_KEYWORDS_CRON":
					$postInfo[$setInfo['set_name']] = intval($postInfo[$setInfo['set_name']]);
					break;

				case "SP_SMTP_HOST":
			    case "SP_SMTP_USERNAME":
		        case "SP_SMTP_PASSWORD":		            
			        // if smtp mail enabled then check all smtp details entered
			        if (empty($postInfo[$setInfo['set_name']]) && !empty($postInfo['SP_SMTP_MAIL'])) {
			            $this->set('errorMsg', $this->spTextSettings['entersmtpdetails']);
	                    $this->showSystemSettings($postInfo['category']);
	                    exit;
			        }
				    break;				    
				    
		        case "SP_SYSTEM_REPORT_INTERVAL":
		            // update users report schedule if system report schedule is greater than them
		            $postInfo[$setInfo['set_name']] = intval($postInfo[$setInfo['set_name']]);
		            $sql = "Update reports_settings set report_interval=".$postInfo[$setInfo['set_name']]." where report_interval<".$postInfo[$setInfo['set_name']];
		            $userList = $this->db->query($sql);
		            break;
			}			
			
			$sql = "update settings set set_val='".addslashes($postInfo[$setInfo['set_name']])."' where set_name='".addslashes($setInfo['set_name'])."'";
			$this->db->query($sql);
		}
		
		$this->set('saved', 1);
		$this->showSystemSettings($postInfo['category']);
	}
	
	# func to show about us of seo panel
	function showAboutUs($info) {
	    
	    $blogContent = getCustomizerPage('aboutus');
	    if (!empty($blogContent['blog_content'])) {
	        $this->set('blogContent', $blogContent);
	    } else {
	    	
	    	if ($info['subsec'] != "sponsors") {
	    		$sql = "select t.*,l.lang_name from translators t,languages l where t.lang_code=l.lang_code";
	    		$transList = $this->db->select($sql); 
	    		$this->set('transList', $transList);
	    	}
    		
    		include_once(SP_CTRLPATH."/information.ctrl.php");
    		$infoCtrler = new InformationController();
    		$this->set('sponsors', $infoCtrler->getSponsors());
    		$this->set('subSec', $info['subsec']);
	    }
	    
		$this->render('settings/aboutus');
	}	
	
	# func to show version of seo panel
	function showVersion() {
		$this->render('settings/version');
	}
	
	# function to check version
	function checkVersion($return = false) {
	    $oldVersion = false;
	    
	    // find latest version of SP
	    $content = $this->spider->getContent(SP_VERSION_PAGE);	    
	    $content['page'] = str_replace('Version:', '', $content['page']);
	    $vList = explode(".", $content['page']);
	    $latestVersion = sprintf("%02d%02d%02d", $vList[0], $vList[1], $vList[2]);
	    
	    // current version of installation
	    $vList = explode(".", SP_VERSION_NUMBER);
	    $installVersion = sprintf("%02d%02d%02d", $vList[0], $vList[1], $vList[2]);
	    
	    // verify installation is upto date or not
	    if ($latestVersion > $installVersion) {
	        $oldVersion = true;
	        $message = $this->spTextSettings['versionnotuptodatemsg']."({$content['page']}) from <a href='".SP_DOWNLOAD_LINK."' target='_blank'>".SP_DOWNLOAD_LINK."</a>";
	    } else {
	        $message = $this->spTextSettings["Your Seo Panel installation is up to date"];
	    }
	    
	    // if message needs to be returned
	    if ($return) {
	        return [$oldVersion, $message];
	    } else {
	        echo $oldVersion ? showErrorMsg($message, false) : showSuccessMsg($message, false);
	    }
	    
	}

	// show google api settings notification
	public static function showCheckCategorySettings($category, $printMsg = false) {
		$ctrler = new SettingsController();
		$spTextSettings = $ctrler->getLanguageTexts('settings', $_SESSION['lang_code']);
		$showMsg = '';
		$notSet = false;
		
		// if category is google
		if ($category == 'google') {
			$settingInfo = $ctrler->__getSettingInfo('SP_GOOGLE_API_KEY');
			
			if (empty($settingInfo['set_val'])) {
				$notSet = true;
				$msgStr = $spTextSettings['Please update google settings to get the results'];
			}
			
		} else if ($category == 'moz') {

			$accessInfo = $ctrler->__getSettingInfo('SP_MOZ_API_ACCESS_ID');
			$secretInfo = $ctrler->__getSettingInfo('SP_MOZ_API_SECRET');
			
			if (empty($accessInfo['set_val']) || empty($secretInfo['set_val'])) {
				$notSet = true;
				$msgStr = $spTextSettings['Please update MOZ settings to get complete results'];
			}
			
		}
				
		// check whether settings is empty
		if ($notSet) {
			$settingUrl = isAdmin() ? SP_WEBPATH . "/admin-panel.php?menu_selected=settings&start_script=settings&category=$category" : "#";
			$showMsg = '
			<div id="topnewsbox">
				<a class="bold_link" href="' . $settingUrl . '">'. $msgStr .' &gt;&gt;
				</a>
			</div>';
			
			// if print message is enabled
			if ($printMsg) {
				echo $showMsg;
				exit;
			}
			
		}
		
		return $showMsg;
		
	}
	
	// function to get settings info
	function __getSettingInfo($setName) {
		$setInfo = $this->dbHelper->getRow('settings', "set_name='".addslashes($setName)."'");
		return $setInfo;
	}
	
	// function to show test email
	function showTestEmailSettings() {
		$this->render('settings/show_test_email');
	}
	
	// fucntion to send test email
	function sendTestEmail($info) {
		$errMsg = formatErrorMsg($this->validate->checkEmail($info['test_email']));
		
		if(!$this->validate->flagErr){
			
			$userController =  New UserController();
			$adminInfo = $userController->__getAdminInfo();
			$adminName = $adminInfo['first_name']."-".$adminInfo['last_name'];
			$this->set('adminName', $adminName);
			$content = $this->getViewContent('email/test_email');
			
			$debugMail = !empty($info['debug_mail']) ? intval($info['debug_mail']) : false;
			if (!sendMail($adminInfo['email'], $adminName, $info['test_email'], "Test email from " . SP_COMPANY_NAME, $content, '', $debugMail)) {
				showErrorMsg('An internal error occured while sending mail!');
			} else {
				showSuccessMsg("Email send successfully to " . $info['test_email']);
			}			
			
		} else {
			showErrorMsg($errMsg);
		}
		
	}
	
	public static function getWebsiteOtherUrl($websiteUrl) {
	    
	    // fix for www. and no www. in search results
	    if (stristr($websiteUrl, "www.")) {
	        $websiteOtherUrl = str_ireplace("www.", "", $websiteUrl);
	    } else {
	        $websiteOtherUrl = "www." . $websiteUrl;
	    }
	    
	    return $websiteOtherUrl;
	}
	
	public static function isSpApiEnabled($feature = 'serp') {
	    if (!defined('SP_SPAPI_REGISTERED') || !SP_SPAPI_REGISTERED) return false;
	    if (!defined('SP_SPAPI_KEY') || empty(SP_SPAPI_KEY)) return false;
	    switch ($feature) {
	        case 'serp': return defined('SP_ENABLE_SPAPI_SERP') && SP_ENABLE_SPAPI_SERP;
	        default:     return true;
	    }
	}

	public static function isDFSEnabled($feature = 'serp') {
	    if (!defined('SP_ENABLE_DFS') || !SP_ENABLE_DFS) return false;
	    if ((SP_DFS_API_LOGIN == "") || (SP_DFS_API_PASSWORD == "")) return false;
	    switch ($feature) {
	        case 'serp':     return defined('SP_ENABLE_DFS_SERP') && SP_ENABLE_DFS_SERP;
	        case 'backsatu': return defined('SP_ENABLE_DFS_BACK_SATU') && SP_ENABLE_DFS_BACK_SATU;
	        case 'review':   return defined('SP_ENABLE_DFS_REVIEW') && SP_ENABLE_DFS_REVIEW;
	        default:         return true;
	    }
	}

	public static function getSearchResults($keywordInfo, $showAll = false, $seId = false, $cron = false) {
	    $status = false;
	    $results =  [];

	    // Tier 1: DataForSEO (highest priority)
	    if (SettingsController::isDFSEnabled('serp')) {
	        include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
	        $dfsCtrler = new DataForSEOController();
	        $status = true;
	        $results = $dfsCtrler->__getSERPResults($keywordInfo, $showAll, $seId, $cron);
	        return [$status, $results];
	    }

	    // Tier 2: SP API
	    if (SettingsController::isSpApiEnabled('serp')) {
	        include_once(SP_CTRLPATH."/spapi.ctrl.php");
	        $spapiCtrler = new SPAPIController();
	        $status = true;
	        $results = $spapiCtrler->__getSERPResults($keywordInfo, $showAll, $seId, $cron);
	        return [$status, $results];
	    }

	    return [$status, $results];
	}
	
	public static function getSearchResultCount($keywordInfo, $cron = false) {
	    $status = false;
	    $results =  [];

	    // check dataforseo is enabled for backlink and saturation checker
	    if (SettingsController::isDFSEnabled('backsatu')) {
	        include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
	        $dfsCtrler = new DataForSEOController();
	        $status = true;
	        $results = $dfsCtrler->__getSERPResultCount($keywordInfo, $cron);
	    }

	    return [$status, $results];
	}

	// check whether spAPI registration popup should be shown
	function showSpApiRegistrationPopup() {
	    $userId = isLoggedIn();
	    if (!isAdmin() || !$userId) {
	        return false;
	    }

	    // check if already registered
	    if (defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED) {
	        return false;
	    }

	    // check if user has skipped
	    $userInfo = $this->dbHelper->getRow('users', "id=" . intval($userId));
	    if (!empty($userInfo['spapi_skip'])) {
	        return false;
	    }

	    return true;
	}

	// register with Seo Panel API
	function registerSpApi($postInfo) {
	    $name = trim($postInfo['name']);
	    $email = trim($postInfo['email']);

	    // validate inputs
	    if (empty($name) || empty($email)) {
	        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
	        return;
	    }

	    $errMsg = $this->validate->checkEmail($email);
	    if ($this->validate->flagErr) {
	        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
	        return;
	    }

	    // call spAPI register endpoint
	    $apiUrl = defined('SP_SPAPI_URL') ? SP_SPAPI_URL : 'https://api.seopanel.org/api/v1';
	    $postData = json_encode([
	        'customer_name' => $name,
	        'email' => $email,
	        'installation_url' => SP_WEBPATH,
	        'version' => SP_VERSION_NUMBER,
	    ]);

	    $ch = curl_init($apiUrl . '/register');
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    $rawResponse = curl_exec($ch);
	    curl_close($ch);

	    header('Content-Type: application/json');

	    if (!empty($rawResponse)) {
	        $response = json_decode($rawResponse, true);

	        if (json_last_error() !== JSON_ERROR_NONE) {
	            echo json_encode(['status' => 'error', 'message' => 'Invalid response from API server. Please try again later.']);
	            return;
	        }

	        if (!empty($response['status']) && $response['status'] == 'success') {
	            $data = $response['data'] ?? [];
	            $apiKey = addslashes($data['api_key'] ?? '');
	            $this->db->query("UPDATE settings SET set_val='1' WHERE set_name='SP_SPAPI_REGISTERED'");
	            $this->db->query("UPDATE settings SET set_val='" . addslashes($email) . "' WHERE set_name='SP_SPAPI_EMAIL'");
	            $this->db->query("UPDATE settings SET set_val='" . addslashes($name) . "' WHERE set_name='SP_SPAPI_NAME'");
	            $this->db->query("UPDATE settings SET set_val='" . $apiKey . "' WHERE set_name='SP_SPAPI_KEY'");
	            echo json_encode(['status' => 'success', 'message' => 'Registration successful! Please check your email inbox to verify your email address.']);
	        } else {
	            $errMsg = !empty($response['message']) ? $response['message'] : 'Registration failed. Please try again later.';
	            echo json_encode(['status' => 'error', 'message' => $errMsg]);
	        }
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Could not connect to the API server. Please try again later.']);
	    }
	}

	// reset SP API token
	function resetSpApiToken() {
	    checkAdminLoggedIn();
	    include_once(SP_CTRLPATH . "/spapi.ctrl.php");
	    $spapiCtrler = new SPAPIController();
	    list($newApiKey, $logInfo) = $spapiCtrler->__resetApiToken();

	    header('Content-Type: application/json');

	    if (isset($logInfo['crawl_status']) && $logInfo['crawl_status'] == 0) {
	        echo json_encode(['status' => 'error', 'message' => $logInfo['log_message']]);
	        return;
	    }

	    $this->db->query("UPDATE settings SET set_val='" . addslashes($newApiKey) . "' WHERE set_name='SP_SPAPI_KEY'");
	    echo json_encode(['status' => 'success', 'message' => 'API token reset successfully.', 'data' => ['api_key' => $newApiKey]]);
	}

	// proxy plans from Seo Panel API (avoids browser CORS)
	function getSpApiPlans() {
	    $apiUrl = defined('SP_SPAPI_URL') ? SP_SPAPI_URL : 'https://api.seopanel.org/api/v1';

	    $ch = curl_init($apiUrl . '/plans');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    $rawResponse = curl_exec($ch);
	    curl_close($ch);

	    header('Content-Type: application/json');

	    if (!empty($rawResponse)) {
	        $response = json_decode($rawResponse, true);
	        if (json_last_error() === JSON_ERROR_NONE) {
	            echo $rawResponse;
	            return;
	        }
	    }

	    echo json_encode(['status' => 'error', 'message' => 'Could not fetch plans.']);
	}

	// skip spAPI registration for current user
	function skipSpApiRegistration() {
	    $userId = isLoggedIn();
	    if ($userId) {
	        $this->db->query("UPDATE users SET spapi_skip=1 WHERE id=" . intval($userId));
	        echo json_encode(['status' => 'success']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
	    }
	}

	// check if the upgrade popup should be shown
	function showSpApiUpgradePopup() {
	    $userId = isLoggedIn();
	    if (!isAdmin() || !$userId) {
	        return false;
	    }

	    // only show if registered
	    if (!defined('SP_SPAPI_REGISTERED') || !SP_SPAPI_REGISTERED) {
	        return false;
	    }

	    // check if user has skipped today
	    $userInfo = $this->dbHelper->getRow('users', "id=" . intval($userId), "spapi_upgrade_skip_date");
	    if (!empty($userInfo['spapi_upgrade_skip_date']) && $userInfo['spapi_upgrade_skip_date'] === date('Y-m-d')) {
	        return false;
	    }

	    // get today's cached check result; if missing (e.g. cleared on login), run a fresh check now
	    include_once(SP_CTRLPATH . "/information.ctrl.php");
	    $informationCtrler = new InformationController();
	    $spapiCheckInfo = $informationCtrler->__getTodayInformation('spapi_check');
	    if (empty($spapiCheckInfo)) {
	        include_once(SP_CTRLPATH . "/alerts.ctrl.php");
	        $alertCtrler = new AlertController();
	        $alertCtrler->updateSpApiAlerts();
	        $spapiCheckInfo = $informationCtrler->__getTodayInformation('spapi_check');
	    }
	    $checkResult = !empty($spapiCheckInfo['page']) ? $spapiCheckInfo['page'] : '';
	    if (!in_array($checkResult, ['expired', 'monthly_limit'])) {
	        return false;
	    }

	    return $checkResult;
	}

	// skip upgrade popup for today
	function skipSpApiUpgrade() {
	    $userId = isLoggedIn();
	    if ($userId) {
	        $this->db->query("UPDATE users SET spapi_upgrade_skip_date='" . date('Y-m-d') . "' WHERE id=" . intval($userId));
	        echo json_encode(['status' => 'success']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
	    }
	}
}
?>