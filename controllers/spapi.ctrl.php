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

/**
 * Class defines all details about managing Seo Panel API SERP operations
 */
class SPAPIController extends Controller {

    var $apiUrl;
    var $apiKey;

    function __construct() {
        parent::__construct();
        $this->apiUrl = defined('SP_SPAPI_URL') ? SP_SPAPI_URL : 'http://api.seopanel.org/api/v1';
        $this->apiKey = defined('SP_SPAPI_KEY') ? SP_SPAPI_KEY : '';
    }

    /**
     * Check if SP API is configured for SERP checking
     */
    static function isConfigured() {
        return defined('SP_ENABLE_SPAPI_SERP') && SP_ENABLE_SPAPI_SERP
            && defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED
            && defined('SP_SPAPI_KEY') && !empty(SP_SPAPI_KEY);
    }

    /**
     * Get usage data for the SP API key
     *
     * @param string $apiKey Optional API key override
     * @return array [usageData, logInfo]
     */
    function __getSpApiUsageData($apiKey = '') {
        $apiKey = !empty($apiKey) ? $apiKey : $this->apiKey;
        $logInfo = ['crawl_status' => 1];
        $usageData = [];

        if (empty($apiKey)) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'API key is required';
            return [$usageData, $logInfo];
        }

        ob_start();
        $spider = new Spider();
        $spider->_CURL_HTTPHEADER = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];
        $spider->_CURLOPT_TIMEOUT = 30;
        $response = $spider->getContent($this->apiUrl . '/account', false, false);
        ob_end_clean();

        if (empty($response['page'])) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'Could not connect to SP API server';
            return [$usageData, $logInfo];
        }

        $responseData = json_decode($response['page'], true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($responseData)) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'Invalid response from SP API server';
            return [$usageData, $logInfo];
        }

        $httpCode = !empty($response['http_code']) ? intval($response['http_code']) : 0;

        if (!empty($responseData['status']) && $responseData['status'] == 'success') {
            $usageData = $responseData['data'] ?? [];
            $monthlyUsed  = intval($usageData['monthly_used'] ?? 0);
            $monthlyLimit = intval($usageData['monthly_limit'] ?? 0);
            $serpUsed     = intval($usageData['serp_used'] ?? 0);
            $serpLimit    = intval($usageData['serp_limit'] ?? 0);
            if (($monthlyLimit > 0 && $monthlyUsed >= $monthlyLimit) ||
                ($serpLimit > 0 && $serpUsed >= $serpLimit)) {
                $logInfo['needs_upgrade'] = true;
                $logInfo['upgrade_reason'] = 'monthly_limit';
            }
        } else {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = !empty($responseData['message']) ? $responseData['message'] : 'SP API request failed';
            if ($httpCode == 403) {
                $logInfo['needs_upgrade'] = true;
                $logInfo['upgrade_reason'] = 'expired';
            }
        }

        return [$usageData, $logInfo];
    }

    /**
     * Reset (regenerate) the SP API key
     *
     * @return array [newApiKey, logInfo]
     */
    function __resetApiToken() {
        $logInfo = ['crawl_status' => 1];
        $newApiKey = '';

        if (empty($this->apiKey)) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'API key is required';
            return [$newApiKey, $logInfo];
        }

        ob_start();
        $spider = new Spider();
        $spider->_CURLOPT_POSTFIELDS = '{}';
        $spider->_CURL_HTTPHEADER = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
        $spider->_CURLOPT_TIMEOUT = 30;
        $response = $spider->getContent($this->apiUrl . '/reset-token', false, false);
        ob_end_clean();

        if (empty($response['page'])) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'Could not connect to SP API server';
            return [$newApiKey, $logInfo];
        }

        $responseData = json_decode($response['page'], true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($responseData)) {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = 'Invalid response from SP API server';
            return [$newApiKey, $logInfo];
        }

        if (!empty($responseData['status']) && $responseData['status'] == 'success') {
            $newApiKey = $responseData['data']['api_key'] ?? '';
        } else {
            $logInfo['crawl_status'] = 0;
            $logInfo['log_message'] = !empty($responseData['message']) ? $responseData['message'] : 'SP API token reset failed';
        }

        return [$newApiKey, $logInfo];
    }

    /**
     * Post a SERP keyword request to the SP API
     *
     * @param array $keywordInfo Keyword info with name, lang_code, country_code
     * @param array $seIds Array of local search engine IDs
     * @return array Response with status and data
     */
    function postSERPKeyword($keywordInfo, $seIds) {
        $result = [
            'status' => false,
            'message' => 'Internal error occurred',
            'data' => [],
        ];

        $postData = json_encode([
            'keyword' => mb_convert_encoding($keywordInfo['name'], "UTF-8"),
            'searchengine_ids' => $seIds,
            'lang_code' => !empty($keywordInfo['lang_code']) ? $keywordInfo['lang_code'] : '',
            'country_code' => !empty($keywordInfo['country_code']) ? $keywordInfo['country_code'] : '',
        ]);

        ob_start();
        $spider = new Spider();
        $spider->_CURLOPT_POSTFIELDS = $postData;
        $spider->_CURL_HTTPHEADER = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
        $spider->_CURLOPT_TIMEOUT = 120;
        $response = $spider->getContent($this->apiUrl . '/serp', false, false);
        ob_end_clean();

        if (empty($response['page'])) {
            $result['message'] = 'Could not connect to SP API server';
            return $result;
        }

        $responseData = json_decode($response['page'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['message'] = 'Invalid response from SP API server';
            return $result;
        }

        if (!empty($responseData['status']) && $responseData['status'] == 'success') {
            $result['status'] = true;
            $result['message'] = 'Success';
            $result['data'] = $responseData['data'] ?? [];
        } else {
            $result['message'] = !empty($responseData['message']) ? $responseData['message'] : 'SP API request failed';
        }

        return $result;
    }

    /**
     * Process SERP response from SP API and save matched keyword results
     *
     * @param array $responseData Response data from SP API containing searchengine_results
     * @param array $keywordInfo Keyword info
     * @param string $websiteUrl Website URL (without http/https)
     * @param string $reportDate Report date
     * @return array Results per search engine [seId => matchCount]
     */
    function processSERPResponse($responseData, $keywordInfo, $websiteUrl, $reportDate = '') {
        $reportDate = !empty($reportDate) ? $reportDate : date('Y-m-d');
        $results = [];

        include_once(SP_CTRLPATH . "/report.ctrl.php");
        include_once(SP_CTRLPATH . "/settings.ctrl.php");

        $websiteUrl = formatUrl($websiteUrl, false);
        $websiteOtherUrl = SettingsController::getWebsiteOtherUrl($websiteUrl);

        $seResults = !empty($responseData['searchengine_results']) ? $responseData['searchengine_results'] : [];

        foreach ($seResults as $seResult) {
            $seId = intval($seResult['searchengine_id']);
            $matchCount = 0;

            if (!empty($seResult['crawled_result']) && is_array($seResult['crawled_result'])) {
                $reportCtrler = new ReportController();
                $firstMatch = true;

                foreach ($seResult['crawled_result'] as $itemInfo) {
                    $url = $itemInfo['url'] ?? '';

                    // Check if URL matches website
                    if (
                        stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) ||
                        stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl)
                    ) {
                        $matchInfo = [];
                        $matchInfo['url'] = $url;
                        $matchInfo['title'] = $itemInfo['title'] ?? '';
                        $matchInfo['description'] = $itemInfo['description'] ?? '';
                        $matchInfo['rank'] = $itemInfo['rank'] ?? 0;
                        $matchInfo['se_id'] = $seId;
                        $matchInfo['keyword_id'] = $keywordInfo['id'];

                        $reportCtrler->saveMatchedKeywordInfo($matchInfo, $firstMatch, $reportDate);
                        $firstMatch = false;
                        $matchCount++;
                    }
                }
            }

            $results[$seId] = $matchCount;
        }

        return $results;
    }
}
