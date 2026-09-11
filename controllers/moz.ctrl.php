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

        // Use sample API data if enabled (saves API credits)
        if (defined('SP_USE_SAMPLE_API_DATA') && SP_USE_SAMPLE_API_DATA) {
            foreach ($urlList as $url) {
                $mozUrlMetrics[] = array(
                    'domain_authority' => rand(30, 70),
                    'page_authority' => rand(20, 60),
                    'spam_score' => rand(1, 15),
                    'external_pages_to_page' => rand(100, 5000),
                    'external_pages_to_root_domain' => rand(10, 500),
                );
            }

            $crawlInfo['crawl_status'] = 1;
            $crawlInfo['log_message'] = "Sample Moz data returned (SP_USE_SAMPLE_API_DATA enabled)";
            return $returnLog ? array($mozUrlMetrics, $crawlInfo) : $mozUrlMetrics;
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

}
?>