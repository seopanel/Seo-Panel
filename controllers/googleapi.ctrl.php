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

// include google api module
include_once(SP_LIBPATH . "/google-api-php-client/vendor/autoload.php");
include_once(SP_CTRLPATH . "/user-token.ctrl.php");

// class defines all google api controller functions
class GoogleAPIController extends Controller{

	var $tokenCtrler;
	var $sourceName = 'google';
	
	/*
	 * contructor
	 */
	function __construct() {
		parent::__construct();
		$this->tokenCtrler = new UserTokenController();
	}
	
	/*
	 * function to create auth api client with credentials
	 */
	function createAuthAPIClient() {

		// if credentials defined
		if (defined('SP_GOOGLE_API_CLIENT_ID') && defined('SP_GOOGLE_API_CLIENT_SECRET') && SP_GOOGLE_API_CLIENT_ID != '' && SP_GOOGLE_API_CLIENT_SECRET != '') {
			$client = new Google_Client();
			$client->setApplicationName("SP_CHECKER");
			$client->setClientId(SP_GOOGLE_API_CLIENT_ID);
			$client->setClientSecret(SP_GOOGLE_API_CLIENT_SECRET);
			$client->setAccessType('offline');
			$redirectUrl = SP_WEBPATH . "/admin-panel.php?sec=connections&action=connect_return&category=" . $this->sourceName;
			$client->setRedirectUri($redirectUrl);
			
			// set app scopes
			$client = $this->setAppScopes($client);
			
		} else {
			$alertCtler = new AlertController();
			$alertInfo = array(
				'alert_subject' => "Click here to enter Google Auth Credentials",
				'alert_message' => "Error: Google Auth Credentials not set",
				'alert_url' => SP_WEBPATH ."/admin-panel.php?sec=google-settings",
				'alert_type' => "danger",
				'alert_category' => "reports",
			);
			$alertCtler->createAlert($alertInfo, false, true);
			return "Error: Google Auth Credentials not set.";
		}
		
		return $client;
		
	}
	
	
	/**
	 * function to get auth client
	 */
	function getAuthClient($userId) {
		
		$client = $this->createAuthAPIClient();
		
		// if client created successfully
		if (is_object($client)) {
			
			// get user token
			$tokenInfo = $this->tokenCtrler->getUserToken($userId, $this->sourceName);
			
			// if token not set for the user
			if (empty($tokenInfo['access_token'])) {
			    $spTextWebmaster = $this->getLanguageTexts('webmaster', $_SESSION['lang_code']);
			    $errorText = $spTextWebmaster["Error: Google api connection failed"] . ". ";
			    $errorText .= "<a href='".SP_WEBPATH ."/admin-panel.php?sec=connections' target='_blank'>{$spTextWebmaster['Click here to connect to your google account']}.</a>";
                $alertCtler = new AlertController();
                $alertInfo = array(
					'alert_subject' => $spTextWebmaster['Click here to connect to your google account'],
					'alert_message' => $spTextWebmaster["Error: Google api connection failed"],
					'alert_url' => SP_WEBPATH ."/admin-panel.php?sec=connections",
					'alert_type' => "danger",
					'alert_category' => "reports",
				);
                $alertCtler->createAlert($alertInfo, $userId);
			    return $errorText;
			}
			
			// set token info
			$tokenInfo['created'] = strtotime($tokenInfo['created']);
			$client->setAccessToken($tokenInfo);
			
			// check whether token expired, then refresh existing token
			if ($client->isAccessTokenExpired()) {
			
				try {
					$client->refreshToken($tokenInfo['refresh_token']);
					$newToken = $client->getAccessToken();
					$newTokenInfo = array();
					$newTokenInfo['created'] = date('Y-m-d H:i:s', $newToken['created']);
					$newTokenInfo['access_token'] = $newToken['access_token'];
					$newTokenInfo['token_type'] = $newToken['token_type'];
					$newTokenInfo['expires_in'] = $newToken['expires_in'];
					
					// comment refresh token update to test the perfomnace
					/*$newTokenInfo['refresh_token'] = $newToken['refresh_token'];*/
					
					$this->tokenCtrler->updateUserToken($tokenInfo['id'], $newTokenInfo);
				} catch (Exception $e) {
					$err = $e->getMessage();
					return "Error: Refresh token - $err";
				}
				
			}
			
		}
		
		return $client;
		
	}
	
	/*
	 * function to setup app scopes(read write permissions)
	 */
	function setAppScopes($client) {
	    $client->addScope([
	        Google_Service_Webmasters::WEBMASTERS,
	        Google_Service_AnalyticsReporting::ANALYTICS_READONLY,
	        Google_Service_Analytics::ANALYTICS_READONLY,
	    ]);
	    return $client;
	}
	
	/*
	 * function to get auth url
	 */
	function getAPIAuthUrl($userId) {
		$ret = array('auth_url' => false);
		$client = $this->createAuthAPIClient();
		
		// if client created successfully
		if (is_object($client)) {
			
			try {
				$authUrl = $client->createAuthUrl();
				$ret['auth_url'] = $authUrl;
			} catch (Exception $e) {
				$err = $e->getMessage();
				$ret['msg'] = "Error: Create token - $err";								
			}
				
		} else {
			$ret['msg'] = $client;
		}
		
		return $ret;		
	}
	
	/*
	 * function to create auth token
	 */
	function createUserAuthToken($userId, $authCode) {
		
		$ret = array('status' => false);
		$client = $this->createAuthAPIClient();
		
		// if client created successfully
		if (is_object($client)) {
		
			try {
				$tkInfo = $client->fetchAccessTokenWithAuthCode($authCode);				
				$tokenInfo['created'] = date('Y-m-d H:i:s', $tkInfo['created']);
				$tokenInfo['user_id'] = intval($userId);
				$tokenInfo['access_token'] = $tkInfo['access_token'];
				$tokenInfo['token_type'] = $tkInfo['token_type'];
				$tokenInfo['expires_in'] = $tkInfo['expires_in'];
				$tokenInfo['refresh_token'] = $tkInfo['refresh_token'];
				$this->tokenCtrler->insertUserToken($tokenInfo);
				$ret['status'] = true;
			} catch (Exception $e) {
				$err = $e->getMessage();
				$ret['msg'] = "Error: Create token - $err";
			}
			
		} else {
			$ret['msg'] = $client;
		}
		
		return $ret;
	}
	
	/*
	 * function to remove all user tokens
	 */
	function removeUserAuthToken($userId) {
		$ret = array('status' => false);
		
		try {
			
			$tokenInfo = $this->tokenCtrler->getUserToken($userId, $this->sourceName);
			
			if (!empty($tokenInfo['id'])) {
				$client = $this->createAuthAPIClient();
				$client->revokeToken($tokenInfo['access_token']);
			}
			
		} catch (Exception $e) {
			$err = $e->getMessage();
			$ret['msg'] = "Error: revoke token - $err";
		}
		
		$tokenInfo = $this->tokenCtrler->deleteAllUserTokens($userId, $this->sourceName);
		return $ret;
	}
	
	function getanalyticWebsitesPropertyIds($userId) {
	    $websites = array();
	    $client = $this->getAuthClient($userId);
	    
	    // if error occured
	    if (!is_object($client)) {
	        return [FALSE, $websites, $client];
	    }
	    
	    // Create a Google_Service_Analytics object
	    $analytics = new Google_Service_Analytics($client);	    
	    
	    // Retrieve the list of accounts
	    $accounts = $analytics->management_accounts->listManagementAccounts();
	    
	    // Loop through the accounts
	    foreach ($accounts->getItems() as $account) {
	        // Get the account ID and name
	        $accountId = $account->getId();
	        $accountName = $account->getName();
	        
	        // get analytics admin properties
	        $accessToken = $this->__getAccessToken($client);
	        $apiUrl = "https://analyticsadmin.googleapis.com/v1alpha/properties?filter=parent:accounts/$accountId";
	        $ret = $this->plainAPICall($apiUrl, $accessToken);	        
	        if (!empty($ret['error'])) {
	            continue;
	        }
	        
	        if (!empty($ret['page']['properties'])) {	            
	            foreach ($ret['page']['properties'] as $property) {
	                $propertyId = str_replace("properties/", "", $property['name']);
	                $propertyName = $property['displayName'];
	                $websites[] = array(
	                    'account_name' => $accountName,
	                    'account_id' => $accountId,
	                    'property_name' => $propertyName,
	                    'property_id' => $propertyId,
	                );
	            }
	        }
	    }
	    
	    return [TRUE, $websites, "success"];
	}
	
	// Function to get GA4 properties
	function getGA4Properties($client, $accountId) {
	    $accessToken = $this->__getAccessToken($client);
	    $apiUrl = "https://analyticsadmin.googleapis.com/v1alpha/properties?filter=parent%3Daccounts%2F$accountId";
	    $ret = $this->plainAPICall($apiUrl, $accessToken);
	    if (!empty($ret['error'])) {
	        return [false, $ret['errmsg'], []];
	    }
	}
	
	function __getAccessToken($client) {
	    $tokenInfo = $client->getAccessToken();
	    $accessToken = !empty($tokenInfo['access_token']) ? $tokenInfo['access_token'] : FALSE;
	    return $accessToken;
	}
	
	function plainAPICall($apiUrl, $accessToken, $postArgs=[]) {
	    $spider = new Spider();
	    array_push($spider ->_CURL_HTTPHEADER, 'Authorization: Bearer ' . $accessToken);
	    
	    if (!empty($postArgs)) {
	        $spider->_CURLOPT_POSTFIELDS = http_build_query($postArgs);
	    }
	    
	    $ret = $spider->getContent($apiUrl);	    
	    if (!empty($ret['page'])) {
	        $apiRes = json_decode($ret['page'], TRUE);	        
	        if ($ret['http_code'] == 200) {
	            $ret['page'] = $apiRes;
	        } else {
	            $ret['error'] = $ret['http_code'];
	            $ret['errmsg'] = !empty($apiRes['error']['message']) ? $apiRes['error']['message'] : "API Error";
	        }
	    }
	    
	    return $ret;
	}
}
?>