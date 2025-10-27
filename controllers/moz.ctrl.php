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

# class defines all moz api controller functions
class MozController extends Controller {
    
    var $v1Endpoint = 'http://lsapi.seomoz.com/linkscape';
    var $v2Endpoint = 'https://lsapi.seomoz.com/v2';
    var $v3Endpoint = "https://api.moz.com/jsonrpc";
    
    // function to get moz usage data using JSON-RPC API
    function __getMozUsageData($apiToken, $returnLog=false) {
        $usageData = array();
        $crawlInfo = array();
        if (empty($apiToken)) {
            return $returnLog ? array($usageData, []) : $usageData;
        }

        // Prepare JSON-RPC request for quota lookup
        $uniqueId = generateUuidV4();
        $requestData = array(
            'jsonrpc' => '2.0',
            'id' => $uniqueId,
            'method' => 'quota.lookup',
            'params' => array(
                'data' => array(
                    'path' => 'api.limits.data.rows'
                )
            )
        );

        // call api using spider
        $spider = new Spider();
        $requestUrl = $this->v3Endpoint;
        array_push($spider->_CURL_HTTPHEADER, 'x-moz-token: '. $apiToken);
        array_push($spider->_CURL_HTTPHEADER, 'Content-Type: application/json');
        $spider->_CURLOPT_POSTFIELDS = json_encode($requestData);
        $ret = $spider->getContent($requestUrl, FALSE);

        // parse response from the page
        if (!empty($ret['page'])) {
            $response = json_decode($ret['page'], TRUE);

            // Check for JSON-RPC success response
            if (isset($response['result'])) {
                $usageData = $response['result'];
                $crawlInfo['crawl_status'] = 1;
                $crawlInfo['log_message'] = "moz usage api call success via JSON-RPC.";
            } elseif (isset($response['error'])) {
                // JSON-RPC error response
                $crawlInfo['crawl_status'] = 0;
                $errorMsg = isset($response['error']['message']) ? $response['error']['message'] : 'Unknown error';
                $crawlInfo['log_message'] = "moz usage api call failed: " . $errorMsg;
            } else {
                $crawlInfo['crawl_status'] = 0;
                $crawlInfo['log_message'] = "moz usage api call failed - invalid response format.";
            }
        } else {
            $crawlInfo['crawl_status'] = 0;
            $crawlInfo['log_message'] = $ret['errmsg'];
        }

        // update crawl log
        $crawlLogCtrl = new CrawlLogController();
        $crawlInfo['crawl_type'] = 'usage_data';
        $crawlInfo['ref_id'] = "moz usage_data";
        $crawlInfo['subject'] = "moz usage data";
        $crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
        return $returnLog ? array($usageData, $crawlInfo) : $usageData;
    }
    
    // function to get moz url metrics using JSON-RPC API
    function __getMozRankInfo($urlList, $returnLog=false, $scope='url') {
        $mozUrlMetrics = array();
        $crawlInfo = array();
        if (empty($urlList)) {
            return $returnLog ? array($mozUrlMetrics, []) : $mozUrlMetrics;
        }

        $apiToken = SP_MOZ_API_SECRET;
        if (empty($apiToken)) {
            return $returnLog ? array($mozUrlMetrics, []) : $mozUrlMetrics;
        }

        // Prepare JSON-RPC request for site metrics
        $uniqueId = generateUuidV4();
        $requestData = array(
            'jsonrpc' => '2.0',
            'id' => $uniqueId,
            'method' => 'data.site.metrics.fetch.multiple',
            'params' => array(
                'data' => array(
                    'site_queries' => [],
                )
            )
        );
        
        foreach ($urlList as $url) {
            $requestData['params']['data']['site_queries'][] = [
                'query' => $url,
                'scope' => $scope // 'url', 'domain', or 'subdomain'
            ];
        }
        
        // call api using spider
        $spider = new Spider();
        $requestUrl = $this->v3Endpoint;
        array_push($spider->_CURL_HTTPHEADER, 'x-moz-token: '. $apiToken);
        array_push($spider->_CURL_HTTPHEADER, 'Content-Type: application/json');
        $spider->_CURLOPT_POSTFIELDS = json_encode($requestData);
        $ret = $spider->getContent($requestUrl, FALSE);
        
        // test daa to fill
        /*include(SP_ABSPATH . "/data/test.php");*/
        
        // parse response from the page
        if (!empty($ret['page'])) {
            $response = json_decode($ret['page'], TRUE);

            // Check for JSON-RPC success response
            if (isset($response['result']['results_by_site'])) {
                $rankList = !empty($response['result']['results_by_site']) ? $response['result']['results_by_site'] : [];
                foreach ($rankList as $rankInfo) {
                    $mozUrlMetrics[] = $rankInfo['site_metrics'];
                }
                
                $crawlInfo['crawl_status'] = 1;
                $crawlInfo['log_message'] = "moz url_metrics api call success via JSON-RPC.";
            } elseif (isset($response['error'])) {
                // JSON-RPC error response
                $crawlInfo['crawl_status'] = 0;
                $errorMsg = isset($response['error']['message']) ? $response['error']['message'] : 'Unknown error';
                $errorCode = isset($response['error']['code']) ? ' (Code: ' . $response['error']['code'] . ')' : '';
                $crawlInfo['log_message'] = "moz url_metrics api call failed: " . $errorMsg . $errorCode;
            } else {
                $crawlInfo['crawl_status'] = 0;
                $crawlInfo['log_message'] = "moz url_metrics api call failed - invalid response format.";
            }
        } else {
            $crawlInfo['crawl_status'] = 0;
            $crawlInfo['log_message'] = $ret['errmsg'];
        }        

        // update crawl log
        $crawlLogCtrl = new CrawlLogController();
        $crawlInfo['crawl_type'] = 'rank';
        $crawlInfo['ref_id'] = $url;
        $crawlInfo['subject'] = "moz link metrics";
        $crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
        return $returnLog ? array($mozUrlMetrics, $crawlInfo) : $mozUrlMetrics;
    }
	
	// function to get moz rank
	function __getMozRankInfoOld ($urlList = array(), $accessID = "", $secretKey = "", $returnLog = false) {
		$mozRankList = array();
		
		if (SP_DEMO && !empty($_SERVER['REQUEST_METHOD'])) return $mozRankList;
		
		if (empty($urlList)) return $mozRankList;
		
		// Get your access id and secret key here: https://moz.com/products/api/keys
		$accessID = !empty($accessID) ? $accessID : SP_MOZ_API_ACCESS_ID;
		$secretKey = !empty($secretKey) ? $secretKey : SP_MOZ_API_SECRET;
		
		// if empty no need to crawl
		if (empty($accessID) || empty($secretKey)) {
			$alertCtler = new AlertController();
			$alertInfo = array(
				'alert_subject' => "Click here to enter MOZ API key",
				'alert_message' => "Error: MOZ API key not found",
				'alert_url' => SP_WEBPATH ."/admin-panel.php?sec=moz-settings",
				'alert_type' => "danger",
				'alert_category' => "reports",
			);
			$alertCtler->createAlert($alertInfo, false, true);
			return $mozRankList;
		}
		
		// Set your expires times for several minutes into the future.
		// An expires time excessively far in the future will not be honored by the Mozscape API.
		$expires = time() + 300;
		
		// Put each parameter on a new line.
		$stringToSign = $accessID."\n".$expires;
		
		// Get the "raw" or binary output of the hmac hash.
		$binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
		
		// Base64-encode it and then url-encode that.
		$urlSafeSignature = urlencode(base64_encode($binarySignature));
		
		// Add up all the bit flags you want returned.
		// Learn more here: https://moz.com/help/guides/moz-api/mozscape/api-reference/url-metrics
		$cols = "103079231488";
		
		// Put it all together and you get your request URL.
		$requestUrl = SP_MOZ_API_LINK . "/url-metrics/?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;
		
		// Put your URLS into an array and json_encode them.
		$encodedDomains = json_encode($urlList);
		
		$spider = new Spider();
		$spider->_CURLOPT_POSTFIELDS = $encodedDomains;
		$ret = $spider->getContent($requestUrl);
		
		// parse rank from the page
		if (!empty($ret['page'])) {
			$rankList = json_decode($ret['page']);
			
			// if no errors occured
			if (empty($rankList->error_message)) {
			
				// loop through rank list
				foreach ($rankList as $rankInfo) {
					
					$mozRankInfo = array(
						'moz_rank' => round($rankInfo->umrp, 2),
						'domain_authority' => round($rankInfo->pda, 2),
						'page_authority' => round($rankInfo->upa, 2),
					);
					
					$mozRankList[] = $mozRankInfo;
				}
				
			} else {
				$crawlInfo['crawl_status'] = 0;
				$crawlInfo['log_message'] = $rankList->error_message;
			}
			
		} else {
			$crawlInfo['crawl_status'] = 0;
			$crawlInfo['log_message'] = $ret['errmsg'];
		}
		
		// update crawl log
		$crawlLogCtrl = new CrawlLogController();
		$crawlInfo['crawl_type'] = 'rank';
		$crawlInfo['ref_id'] = $encodedDomains;
		$crawlInfo['subject'] = "moz";
		$crawlLogCtrl->updateCrawlLog($ret['log_id'], $crawlInfo);
	
		return $returnLog ? array($mozRankList, $crawlInfo) : $mozRankList;
	}	
	
}
?>