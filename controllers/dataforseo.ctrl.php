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

include_once(SP_LIBPATH."/dataforseo/RestClient.php");

/**
 * Class defines all details about managing DataForSEO API
 */
class DataForSEOController extends Controller {
    
    var $restClient;
    var $apiUrl = 'https://api.dataforseo.com/';
    
    function __construct() {
        parent::__construct();
        $this->apiUrl = SP_ENABLE_DFS_SANDBOX ? 'https://sandbox.dataforseo.com/' : $this->apiUrl;
        $this->restClient = new RestClient($this->apiUrl, null, SP_DFS_API_LOGIN, SP_DFS_API_PASSWORD);
    }
    
    function __checkAPIConnection($apiLogin, $apiPassword) {
        $connResult = [
            'status' => false, 
            'message' => $_SESSION['text']['common']['Internal error occured'], 
            'balance' => 0,
        ];
        
        if (!empty($apiLogin) && !empty($apiPassword)) {
            $this->restClient = new RestClient($this->apiUrl, null, $apiLogin, $apiPassword);
            $connResult = $this->getUserAccountDetails();
            
            if ($connResult['status']) {
                $result = $connResult['data'];
                if ($result['status_code'] == 20000) {
                    foreach ($result['tasks'] as $taskInfo) {
                        if ($taskInfo['status_code'] == 20000 && $taskInfo['data']['function'] == 'user_data') {
                            $balance = isset($taskInfo['result'][0]['money']['balance']) ? $taskInfo['result'][0]['money']['balance'] : 0;
                            $connResult['balance'] = $balance;
                            $this->updateAPIBalance($balance);
                            break;
                        }
                    }
                } else {
                    $connResult['status'] = false;
                    $connResult['message'] = $result['status_message'];
                }
            }
        }
        
        return $connResult;
    }
    
    function getUserAccountDetails() {
        $res = ['status' => false, 'message' => $_SESSION['text']['common']['Internal error occured']];
        
        try {
            $result = $this->restClient->get('/v3/appendix/user_data');
            $res['status'] = true;
            $res['data'] = $result;
        } catch (RestClientException $e) {
            $msg = "HTTP code: {$e->getHttpCode()}\n";
            $msg .= "Error code: {$e->getCode()}\n";
            $msg .= "Message: {$e->getMessage()}\n";
            $res['message'] = $msg;
        }
            
        return $res;
    }
    
    function updateAPIBalance($balance) {
        $res = $this->dbHelper->updateRow('settings', ['set_val' => $balance], "set_name='SP_DFS_BALANCE'");
        return $res;
    }
    
    public static function getSERPDomainCategory($seachEngine) {
        $seDomianCat = 'google';
        if (stristr($seachEngine, 'yahoo')) {
            $seDomianCat = 'yahoo';
        } elseif (stristr($seachEngine, 'bing') || stristr($seachEngine, 'msn')) {
            $seDomianCat = 'bing';
        } elseif (stristr($seachEngine, 'yandex')) {
            $seDomianCat = 'yandex';            
        } elseif (stristr($seachEngine, 'baidu')) {
            $seDomianCat = 'baidu';            
        }
        
        return $seDomianCat;
    }    
    
    function doSERPAPICall($keywordInfo, $seachEngine, $cat='organic', $subCat='live', $dataType='regular') {
        $connResult = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'],
            'data' => [],
        ];
        
        $searchInfo = array(
            "keyword" => mb_convert_encoding($keywordInfo['name'], "UTF-8"),
            "location_name" => __assign($keywordInfo, 'location_name', "United States"),
            "language_name" => __assign($keywordInfo, 'language_name', "English"),
            "priority" => __assign($keywordInfo, "priority", 1),
            "depth" => __assign($keywordInfo, "depth", 100),
        );
        
        // exceptions for baidu
        $seDomianCat = DataForSEOController::getSERPDomainCategory($seachEngine);
        if ($seDomianCat != "baidu") {
            if (stristr($seachEngine, ".")) {
                $searchInfo['se_domain'] = $seachEngine;
            }
        }
        
        // if google use dataforseo lite method - currently this api end point disabled
        /*if (($seDomianCat == "google") && ($searchInfo['depth'] > 10)) {
            $cat = "lite";
            $dataType = "advanced";
        }*/

        // option to just search for a target url, if found stop search to save api cost
        if (!empty($keywordInfo['stop_crawl_on_match'])) {
            $searchInfo['stop_crawl_on_match'] = $keywordInfo['stop_crawl_on_match'];
        }
        
        // for debugging purpose
        /*debugVar(["/v3/serp/$seDomianCat/$cat/$subCat/$dataType", $searchInfo]);*/

        try {
            $result = $this->restClient->post("/v3/serp/$seDomianCat/$cat/$subCat/$dataType", [$searchInfo]);
            $connResult['status'] = true;
            $connResult['message'] = "Success";
        } catch (RestClientException $e) {
            $msg = "HTTP code: {$e->getHttpCode()}\n";
            $msg .= "Error code: {$e->getCode()}\n";
            $msg .= "Message: {$e->getMessage()}\n";
            $connResult['message'] = $msg;
        }
        
        if ($connResult['status']) {
            if ($result['status_code'] == 20000) {
                foreach ($result['tasks'] as $taskInfo) {
                    if ($taskInfo['status_code'] == 20000 && isset($taskInfo['result'][0])) {
                        $connResult['data'] = $taskInfo['result'][0];
                    } else {
                        $connResult['status'] = false;
                        $connResult['message'] = $taskInfo['status_message'];
                    }
                    
                    break;
                }
            } else {
                $connResult['status'] = false;
                $connResult['message'] = $result['status_message'];
            }
        }
        
        return $connResult;
    }
    
    function __getLocationName($countryCode, $required=TRUE) {
        $locationName = "";
        $countryCtrl = new CountryController();
        $countryList = $countryCtrl->__getAllCountryAsList();
        if (!empty($countryCode)) {
            $locationName = $countryList[$countryCode];
        }
        
        // if location name is required
        if ($required && empty($locationName)) {
            $locationName = __assign($countryList, SP_DEFAULT_COUNTRY, "United States");
        }
        
        return $locationName;
    }
    
    function __getLanguageName($langCode, $required=TRUE) {
        $langName = "";
        $langCtrl = new LanguageController();
        $langList = $langCtrl->__getAllLanguages();
        $langList = createSelectList($langList, 'lang_name', 'lang_code');
        if (!empty($langCode)) {
            $langName = $langList[$langCode];
        }
        
        // if language name is required
        if ($required && empty($langName)) {
            $langName = __assign($langList, SP_DEFAULTLANG, "English");
        }
        
        return $langName;
    }
    
    function __getSERPResults($keywordInfo, $showAll = false, $seId = false, $cron = false) {
        $crawlResult = array();
        $seFound = false;
        $websiteUrl = formatUrl($keywordInfo['url'], false);
        if(empty($websiteUrl)) {
            return $crawlResult;
        }
        
        if(empty($keywordInfo['name'])) {
            return $crawlResult;
        }
        
        $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $seController = New SearchEngineController();
        $seList = $seController->__getAllCrawlFormatedSearchEngines();        
        $websiteOtherUrl = SettingsController::getWebsiteOtherUrl($websiteUrl);
        
        // set country and language details
        $keywordInfo['location_name'] = $this->__getLocationName($keywordInfo['country_code']);
        $keywordInfo['language_name'] = $this->__getLanguageName($keywordInfo['lang_code']);
        
        $reportCtrler = new ReportController();        
        $keySeList = explode(':', $keywordInfo['searchengines']);
        foreach($keySeList as $seInfoId) {
            
            // function to execute only passed search engine
            if(!empty($seId) && ($seInfoId != $seId)) {
                continue;
            }
            
            // if search engine not found continue
            if (empty($seList[$seInfoId])) {
                continue;
            }            
            
            // set number of search results per page
            $keywordInfo['depth'] = $seList[$seInfoId]['max_results'];

            // if not show all, use option just search for a target url, if found stop search to save api cost
            if (!$showAll) {
                // extract domain and remove www. prefix for matching
                $parsedUrl = parse_url('http://' . $websiteUrl);
                $matchDomain = preg_replace('/^www\./i', '', $parsedUrl['host']);
                $keywordInfo['stop_crawl_on_match'] = [
                    [
                        "match_value" => $matchDomain,
                        "match_type" => "with_subdomains",
                    ]
                ];
            }

            // call serp api to get the results
            $seFound = true;
            $urlInfo = parse_url($seList[$seInfoId]['url']);
            $seachEngine = $urlInfo['host']; 
            $result = $this->doSERPAPICall($keywordInfo, $seachEngine);
            
            // check crawl status
            if(!empty($result['status'])) {                
                // to update cron that report executed for akeyword on a search engine
                if ($cron) {
                    $reportCtrler->saveCronTrackInfo($keywordInfo['id'], $seInfoId, $time);
                }
                
                // verify results array having search results
                if (!empty($result['data']['items'])) {
                    $crawlResult[$seInfoId]['matched'] = array();
                    
                    // loop through the results
                    foreach ($result['data']['items'] as $itemInfo) {
                        $url = $itemInfo['url'];
                        if (
                            $showAll || (
                            stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) ||
                            stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl))
                        ) { 
                            $matchInfo = [];
                            if (
                                $showAll && (
                                stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) ||
                                stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl))
                            ) {
                                $matchInfo['found'] = 1;
                            } else {
                                $matchInfo['found'] = 0;
                            }
                                
                            $matchInfo['url'] = $url;
                            $matchInfo['title'] = $itemInfo['title'];
                            $matchInfo['description'] = $itemInfo['description'];
                            $matchInfo['rank'] = $itemInfo['rank_group'];
                            $crawlResult[$seInfoId]['matched'][] = $matchInfo;
                        }
                    }
                }
            } else {
                if (SP_DEBUG) {
                    echo "<p class='note' style='text-align:left;'>
                            Error occured while crawling  keyword {$keywordInfo['name']} from $seachEngine - ".formatErrorMsg($result['message']."<br>\n")."</p>";
                }
            }
            
            $crawlResult[$seInfoId]['seFound'] = $seFound;
            $crawlResult[$seInfoId]['status'] = $result['status'];
            
            // create crawl log
            $crawlLogCtrl = new CrawlLogController();
            $crawlInfo = [];
            $crawlInfo['crawl_type'] = 'keyword';
            $crawlInfo['crawl_status'] = $result['status'] ? 1 : 0;
            $crawlInfo['ref_id'] = empty($keywordInfo['id']) ? $keywordInfo['name'] : $keywordInfo['id'];
            $crawlInfo['subject'] = $seInfoId;
            $crawlInfo['crawl_referer'] = $this->apiUrl;
            $crawlInfo['log_message'] = addslashes($result['message']);
            $crawlInfo['crawl_link'] = !empty($result['data']['check_url']) ? $result['data']['check_url'] : "";
            $crawlLogCtrl->createCrawlLog($crawlInfo);
        }
        
        return  $crawlResult;        
    }
    
    function __getSERPResultCount($keywordInfo, $cron = false) {
        $crawlResult = array();
        if(empty($keywordInfo['name'])) {
            return $crawlResult;
        }

        if(empty($keywordInfo['engine'])) {
            return $crawlResult;
        }

        $keywordInfo['depth'] = 10;
        $result = $this->doSERPAPICall($keywordInfo, $keywordInfo['engine'], "organic", "live", "regular");

        // check crawl status
        if(!empty($result['status'])) {
            $crawlResult['count'] = isset($result['data']['se_results_count']) ? $result['data']['se_results_count'] : 0;
        } else {
            if (SP_DEBUG) {
                echo "<p class='note' style='text-align:left;'>
                        Error occured while crawling  keyword {$keywordInfo['name']} from {$keywordInfo['engine']} - ".formatErrorMsg($result['message']."<br>\n")."</p>";
            }
        }

        $crawlResult['status'] = $result['status'];

        // create crawl log
        $crawlLogCtrl = new CrawlLogController();
        $crawlInfo = [];
        $crawlInfo['crawl_type'] = stristr($keywordInfo['name'], 'site:') ? 'saturation' : 'backlink';
        $crawlInfo['crawl_status'] = $result['status'] ? 1 : 0;
        $crawlInfo['ref_id'] = $keywordInfo['name'];
        $crawlInfo['subject'] = $keywordInfo['engine'];
        $crawlInfo['crawl_referer'] = $this->apiUrl;
        $crawlInfo['log_message'] = addslashes($result['message']);
        $crawlInfo['crawl_link'] = !empty($result['data']['check_url']) ? $result['data']['check_url'] : "";
        $crawlLogCtrl->createCrawlLog($crawlInfo);

        return  $crawlResult;
    }

    // ==================== DFS Tasks Table Methods ====================

    var $dfsTasksTable = 'dfs_tasks';

    /**
     * Save a new DFS task to database
     *
     * @param array $taskData Task data to save
     * @return int|false Inserted ID or false on failure
     */
    function saveDFSTask($taskData) {
        $data = [
            'task_id' => $taskData['task_id'],
            'category' => $taskData['category'],
            'platform' => $taskData['platform'],
            'ref_id|int' => $taskData['ref_id'],
            'ref_url' => !empty($taskData['ref_url']) ? $taskData['ref_url'] : '',
            'status' => 'pending',
            'report_date' => !empty($taskData['report_date']) ? $taskData['report_date'] : date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->dbHelper->insertRow($this->dfsTasksTable, $data);
        return $this->db->getMaxId($this->dfsTasksTable);
    }

    /**
     * Get pending DFS tasks by category
     *
     * @param string $category Task category (review, serp, backlink, etc.)
     * @param string $reportDate Report date (default: today)
     * @return array List of pending tasks
     */
    function getPendingDFSTasks($category, $reportDate = '') {
        $reportDate = !empty($reportDate) ? addslashes($reportDate) : date('Y-m-d');
        $category = addslashes($category);
        $sql = "SELECT * FROM {$this->dfsTasksTable}
                WHERE category='$category' AND status='pending' AND report_date='$reportDate'
                ORDER BY created_at ASC";
        return $this->db->select($sql);
    }

    /**
     * Get pending DFS task for a specific reference
     *
     * @param string $category Task category
     * @param int $refId Reference ID
     * @param string $reportDate Report date
     * @return array|false Task info or false if not found
     */
    function getPendingDFSTaskByRef($category, $refId, $reportDate = '') {
        $reportDate = !empty($reportDate) ? addslashes($reportDate) : date('Y-m-d');
        $whereCond = "category='".addslashes($category)."' AND ref_id=".intval($refId)." AND report_date='$reportDate' AND status='pending'";
        return $this->dbHelper->getRow($this->dfsTasksTable, $whereCond);
    }

    /**
     * Update DFS task status
     *
     * @param int $id Task ID in database
     * @param string $status New status (pending, completed, failed)
     * @param string $errorMessage Error message if failed
     * @return bool Success
     */
    function updateDFSTaskStatus($id, $status, $errorMessage = '') {
        $data = ['status' => $status];
        if ($status == 'completed' || $status == 'failed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        if (!empty($errorMessage)) {
            $data['error_message'] = $errorMessage;
        }
        return $this->dbHelper->updateRow($this->dfsTasksTable, $data, "id=".intval($id));
    }

    /**
     * Get DFS task by task_id
     *
     * @param string $taskId DataForSEO task ID
     * @return array|false Task info or false
     */
    function getDFSTaskByTaskId($taskId) {
        $whereCond = "task_id='".addslashes($taskId)."'";
        return $this->dbHelper->getRow($this->dfsTasksTable, $whereCond);
    }

    // ==================== Review API Methods ====================

    /**
     * Post a review task to DataForSEO and save to database
     *
     * @param string $platform Platform name (google, trustpilot, tripadvisor)
     * @param int $refId Reference ID (review_link_id)
     * @param string $url Review page URL
     * @param string $reportDate Report date
     * @return array ['status' => bool, 'message' => string, 'task_id' => string, 'pending' => bool]
     */
    function postReviewTask($platform, $refId, $url, $reportDate = '') {
        $result = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'],
            'task_id' => '',
            'pending' => false,
        ];

        $reportDate = !empty($reportDate) ? $reportDate : date('Y-m-d');

        // Check if task already exists for this reference
        $existingTask = $this->getPendingDFSTaskByRef('review', $refId, $reportDate);
        if (!empty($existingTask)) {
            $result['status'] = true;
            $result['message'] = "Task already pending";
            $result['task_id'] = $existingTask['task_id'];
            $result['pending'] = true;
            return $result;
        }

        // Build API parameters based on platform
        $params = $this->__buildReviewTaskParams($platform, $url);
        if (empty($params)) {
            $result['message'] = "Could not extract required parameters from URL for platform: $platform";
            return $result;
        }

        // Post task to DataForSEO
        $apiResult = $this->__postReviewTaskToAPI($platform, $params);
        if (!$apiResult['status']) {
            $result['message'] = $apiResult['message'];
            return $result;
        }

        // Save task to database
        $taskData = [
            'task_id' => $apiResult['task_id'],
            'category' => 'review',
            'platform' => $platform,
            'ref_id' => $refId,
            'ref_url' => $url,
            'report_date' => $reportDate,
        ];
        $this->saveDFSTask($taskData);

        $result['status'] = true;
        $result['message'] = "Task posted successfully";
        $result['task_id'] = $apiResult['task_id'];
        $result['pending'] = true;

        // Create crawl log
        $this->__createReviewCrawlLog($url, $platform, true, "Task posted: " . $apiResult['task_id']);

        return $result;
    }

    /**
     * Fetch results for a pending review task
     *
     * @param array $taskInfo Task info from database
     * @return array ['status' => 0|1, 'reviews' => int, 'rating' => float, 'msg' => string, 'completed' => bool]
     */
    function fetchReviewTaskResult($taskInfo) {
        $result = ['status' => 0, 'reviews' => 0, 'rating' => 0, 'msg' => '', 'completed' => false];

        $apiResult = $this->__getReviewTaskFromAPI($taskInfo['platform'], $taskInfo['task_id']);

        // Task still processing
        if ($apiResult['pending']) {
            $result['msg'] = "Task still processing";
            return $result;
        }

        // Task failed
        if (!$apiResult['status']) {
            $this->updateDFSTaskStatus($taskInfo['id'], 'failed', $apiResult['message']);
            $result['msg'] = $apiResult['message'];
            $result['completed'] = true;
            $this->__createReviewCrawlLog($taskInfo['ref_url'], $taskInfo['platform'], false, $apiResult['message']);
            return $result;
        }

        // Task completed successfully
        $this->updateDFSTaskStatus($taskInfo['id'], 'completed');

        $data = $apiResult['data'];
        $result['status'] = 1;
        $result['reviews'] = isset($data['reviews_count']) ? intval($data['reviews_count']) : 0;
        $result['rating'] = isset($data['rating']['value']) ? round(floatval($data['rating']['value']), 2) : 0;
        $result['msg'] = "Review details fetched successfully via DataForSEO";
        $result['completed'] = true;

        $this->__createReviewCrawlLog($taskInfo['ref_url'], $taskInfo['platform'], true, $result['msg']);

        return $result;
    }

    /**
     * Build review task parameters based on platform
     *
     * @param string $platform Platform name
     * @param string $url Review page URL
     * @return array|false Parameters array or false if extraction failed
     */
    function __buildReviewTaskParams($platform, $url) {
        $params = [];

        switch ($platform) {
            case 'google':
                $keyword = $this->__extractGoogleKeyword($url);
                if (empty($keyword)) return false;
                $params = [
                    'keyword' => $keyword,
                    'location_name' => 'United States',
                    'language_name' => 'English',
                    'depth' => 10,
                    'priority' => 1,
                ];
                break;

            case 'trustpilot':
                $domain = $this->__extractTrustpilotDomain($url);
                if (empty($domain)) return false;
                $params = [
                    'domain' => $domain,
                    'depth' => 20,
                    'priority' => 1,
                ];
                break;

            case 'tripadvisor':
                $urlPath = $this->__extractTripAdvisorPath($url);
                if (empty($urlPath)) return false;
                $params = [
                    'url_path' => $urlPath,
                    'depth' => 10,
                    'priority' => 1,
                ];
                break;

            default:
                return false;
        }

        return $params;
    }

    /**
     * Post review task to DataForSEO API
     *
     * @param string $platform Platform name
     * @param array $params API parameters
     * @return array ['status' => bool, 'message' => string, 'task_id' => string]
     */
    function __postReviewTaskToAPI($platform, $params) {
        $connResult = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'],
            'task_id' => '',
        ];

        $endpoint = "/v3/business_data/$platform/reviews/task_post";

        try {
            $result = $this->restClient->post($endpoint, [$params]);

            if ($result['status_code'] == 20000) {
                foreach ($result['tasks'] as $taskInfo) {
                    if (in_array($taskInfo['status_code'], [20000, 20100]) && !empty($taskInfo['id'])) {
                        $connResult['status'] = true;
                        $connResult['message'] = "Task created successfully";
                        $connResult['task_id'] = $taskInfo['id'];
                    } else {
                        $connResult['message'] = $taskInfo['status_message'];
                    }
                    break;
                }
            } else {
                $connResult['message'] = $result['status_message'];
            }
        } catch (RestClientException $e) {
            $connResult['message'] = "HTTP code: {$e->getHttpCode()}, Error: {$e->getMessage()}";
        }

        return $connResult;
    }

    /**
     * Get review task result from DataForSEO API (single call, no polling)
     *
     * @param string $platform Platform name
     * @param string $taskId Task ID
     * @return array ['status' => bool, 'message' => string, 'data' => array, 'pending' => bool]
     */
    function __getReviewTaskFromAPI($platform, $taskId) {
        $connResult = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'],
            'data' => [],
            'pending' => false,
        ];

        $endpoint = "/v3/business_data/$platform/reviews/task_get/$taskId";

        try {
            $result = $this->restClient->get($endpoint);

            if ($result['status_code'] == 20000) {
                foreach ($result['tasks'] as $taskInfo) {
                    // Task completed successfully
                    if ($taskInfo['status_code'] == 20000 && !empty($taskInfo['result'])) {
                        $connResult['status'] = true;
                        $connResult['message'] = "Success";
                        $connResult['data'] = $taskInfo['result'][0];
                        return $connResult;
                    }
                    // Task still processing
                    elseif ($taskInfo['status_code'] == 20100) {
                        $connResult['pending'] = true;
                        $connResult['message'] = "Task still processing";
                        return $connResult;
                    }
                    // Task failed
                    else {
                        $connResult['message'] = $taskInfo['status_message'];
                        return $connResult;
                    }
                }
            } else {
                $connResult['message'] = $result['status_message'];
            }
        } catch (RestClientException $e) {
            $connResult['message'] = "HTTP code: {$e->getHttpCode()}, Error: {$e->getMessage()}";
        }

        return $connResult;
    }

    /**
     * Create crawl log for review operations
     *
     * @param string $url Review URL
     * @param string $platform Platform name
     * @param bool $status Success status
     * @param string $message Log message
     */
    function __createReviewCrawlLog($url, $platform, $status, $message) {
        $crawlLogCtrl = new CrawlLogController();
        $crawlInfo = [];
        $crawlInfo['crawl_type'] = 'review';
        $crawlInfo['crawl_status'] = $status ? 1 : 0;
        $crawlInfo['ref_id'] = $url;
        $crawlInfo['subject'] = $platform;
        $crawlInfo['crawl_referer'] = $this->apiUrl;
        $crawlInfo['log_message'] = addslashes($message);
        $crawlInfo['crawl_link'] = $url;
        $crawlLogCtrl->createCrawlLog($crawlInfo);
    }

    /**
     * Extract keyword/business name from Google search URL
     *
     * @param string $url Google URL
     * @return string Extracted keyword or empty string
     */
    function __extractGoogleKeyword($url) {
        $keyword = '';

        // Parse URL and get query parameter
        $parsedUrl = parse_url($url);
        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            if (!empty($queryParams['q'])) {
                $keyword = $queryParams['q'];
            }
        }

        return $keyword;
    }

    /**
     * Extract domain from Trustpilot review URL
     *
     * @param string $url Trustpilot URL
     * @return string Extracted domain or empty string
     */
    function __extractTrustpilotDomain($url) {
        $domain = '';

        // Pattern: https://www.trustpilot.com/review/domain.com
        if (preg_match('/trustpilot\.com\/review\/([^\/\?]+)/i', $url, $matches)) {
            $domain = $matches[1];
        }

        return $domain;
    }

    /**
     * Extract URL path from TripAdvisor URL
     *
     * @param string $url TripAdvisor URL
     * @return string Extracted URL path or empty string
     */
    function __extractTripAdvisorPath($url) {
        $urlPath = '';

        // Parse URL and get path
        $parsedUrl = parse_url($url);
        if (!empty($parsedUrl['path'])) {
            $urlPath = ltrim($parsedUrl['path'], '/');
        }

        return $urlPath;
    }

    // ==================== Cron Processing Methods ====================

    /**
     * Get all pending DFS tasks
     *
     * @param string $reportDate Report date (default: today)
     * @return array List of pending tasks
     */
    function getAllPendingDFSTasks($reportDate = '') {
        $reportDate = !empty($reportDate) ? addslashes($reportDate) : date('Y-m-d');
        $sql = "SELECT * FROM {$this->dfsTasksTable}
                WHERE status='pending' AND report_date='$reportDate'
                ORDER BY created_at ASC";
        return $this->db->select($sql);
    }

    /**
     * Process all pending DFS tasks - called from cron
     *
     * @param bool $verbose Print output messages
     * @return array Processing results summary
     */
    function processPendingDFSTasks($verbose = true) {
        $results = [
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'still_pending' => 0,
        ];

        // Get all pending tasks
        $pendingTasks = $this->getAllPendingDFSTasks();
        if (empty($pendingTasks)) {
            if ($verbose) echo "No pending DFS tasks found.\n";
            return $results;
        }

        $results['total'] = count($pendingTasks);
        if ($verbose) echo "\nProcessing " . $results['total'] . " pending DFS tasks...\n";

        // Process each task based on category
        foreach ($pendingTasks as $taskInfo) {
            $taskResult = $this->processDFSTask($taskInfo, $verbose);

            if ($taskResult['completed']) {
                if ($taskResult['success']) {
                    $results['completed']++;
                } else {
                    $results['failed']++;
                }
            } else {
                $results['still_pending']++;
            }
        }

        if ($verbose) {
            echo "DFS Tasks Processing Summary:\n";
            echo "  - Completed: {$results['completed']}\n";
            echo "  - Failed: {$results['failed']}\n";
            echo "  - Still Pending: {$results['still_pending']}\n";
        }

        return $results;
    }

    /**
     * Process a single DFS task
     *
     * @param array $taskInfo Task information from database
     * @param bool $verbose Print output messages
     * @return array ['completed' => bool, 'success' => bool, 'message' => string]
     */
    function processDFSTask($taskInfo, $verbose = true) {
        $result = ['completed' => false, 'success' => false, 'message' => ''];

        switch ($taskInfo['category']) {
            case 'review':
                $result = $this->processReviewTask($taskInfo, $verbose);
                break;

            case 'serp':
                $result = $this->processSerpTask($taskInfo, $verbose);
                break;

            default:
                $result['completed'] = true;
                $result['success'] = false;
                $result['message'] = "Unknown task category: {$taskInfo['category']}";
                $this->updateDFSTaskStatus($taskInfo['id'], 'failed', $result['message']);
                if ($verbose) echo "  - Task {$taskInfo['task_id']}: {$result['message']}\n";
                break;
        }

        return $result;
    }

    /**
     * Process a review task - fetch result and save to database
     *
     * @param array $taskInfo Task information from database
     * @param bool $verbose Print output messages
     * @return array ['completed' => bool, 'success' => bool, 'message' => string]
     */
    function processReviewTask($taskInfo, $verbose = true) {
        $result = ['completed' => false, 'success' => false, 'message' => ''];

        if ($verbose) echo "  - Processing review task {$taskInfo['task_id']} for {$taskInfo['platform']}...";

        // Fetch result from DataForSEO
        $apiResult = $this->fetchReviewTaskResult($taskInfo);

        if (!$apiResult['completed']) {
            // Task still pending on DataForSEO side
            $result['message'] = "Still processing";
            if ($verbose) echo " still processing\n";
            return $result;
        }

        $result['completed'] = true;

        if ($apiResult['status'] == 0) {
            // Task failed
            $result['success'] = false;
            $result['message'] = $apiResult['msg'];
            if ($verbose) echo " failed: {$result['message']}\n";
            return $result;
        }

        // Task completed successfully - save results
        include_once(SP_CTRLPATH . "/review_manager.ctrl.php");
        $reviewCtrler = new ReviewManagerController();

        // Check if result already exists for this date
        $reportDate = $taskInfo['report_date'];
        $existingResult = $this->dbHelper->getRow(
            'review_link_results',
            "review_link_id=" . intval($taskInfo['ref_id']) . " AND report_date='" . addslashes($reportDate) . "'"
        );

        if (empty($existingResult)) {
            // Save new result
            $linkInfo = [
                'reviews' => $apiResult['reviews'],
                'rating' => $apiResult['rating'],
            ];

            $dataList = [
                'review_link_id|int' => $taskInfo['ref_id'],
                'reviews|int' => $linkInfo['reviews'],
                'rating|float' => $linkInfo['rating'],
                'report_date' => $reportDate,
            ];

            $this->dbHelper->insertRow('review_link_results', $dataList);
            $result['success'] = true;
            $result['message'] = "Saved: {$apiResult['reviews']} reviews, {$apiResult['rating']} rating";
            if ($verbose) echo " completed - {$result['message']}\n";
        } else {
            // Result already exists
            $result['success'] = true;
            $result['message'] = "Result already exists for this date";
            if ($verbose) echo " {$result['message']}\n";
        }

        return $result;
    }

    // ==================== SERP Task Methods ====================

    /**
     * Post a SERP task to DataForSEO and save to database
     *
     * @param array $keywordInfo Keyword information (id, name, searchengines, country_code, lang_code, url)
     * @param int $seId Search engine ID
     * @param string $seUrl Search engine URL (e.g., google.com, bing.com)
     * @param string $reportDate Report date
     * @return array ['status' => bool, 'message' => string, 'task_id' => string, 'pending' => bool]
     */
    function postSERPTask($keywordInfo, $seId, $seUrl, $reportDate = '') {
        $result = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'] ?? 'Internal error occurred',
            'task_id' => '',
            'pending' => false,
        ];

        $reportDate = !empty($reportDate) ? $reportDate : date('Y-m-d');

        // Create unique ref_id combining keyword_id and se_id
        $refId = intval($keywordInfo['id']);

        // Check if task already exists for this keyword + search engine + date
        $existingTask = $this->getPendingSERPTaskByRef($refId, $seId, $reportDate);
        if (!empty($existingTask)) {
            $result['status'] = true;
            $result['message'] = "Task already pending";
            $result['task_id'] = $existingTask['task_id'];
            $result['pending'] = true;
            return $result;
        }

        // Determine search engine category (google, bing, yahoo)
        $seDomainCat = DataForSEOController::getSERPDomainCategory($seUrl);

        // Build API parameters
        $params = [
            'keyword' => mb_convert_encoding($keywordInfo['name'], "UTF-8"),
            'location_name' => $this->__getLocationName($keywordInfo['country_code']),
            'language_name' => $this->__getLanguageName($keywordInfo['lang_code']),
            'depth' => isset($keywordInfo['depth']) ? intval($keywordInfo['depth']) : 100,
            'priority' => 1,
        ];

        // Add search engine domain for non-baidu
        if ($seDomainCat != "baidu" && stristr($seUrl, ".")) {
            $params['se_domain'] = $seUrl;
        }

        // Post task to DataForSEO
        $apiResult = $this->__postSERPTaskToAPI($seDomainCat, $params);
        if (!$apiResult['status']) {
            $result['message'] = $apiResult['message'];
            return $result;
        }

        // Save task to database
        $taskData = [
            'task_id' => $apiResult['task_id'],
            'category' => 'serp',
            'platform' => $seDomainCat,
            'ref_id' => $refId,
            'ref_url' => $seId, // Store search engine ID in ref_url for SERP tasks
            'report_date' => $reportDate,
        ];
        $this->saveDFSTask($taskData);

        $result['status'] = true;
        $result['message'] = "Task posted successfully";
        $result['task_id'] = $apiResult['task_id'];
        $result['pending'] = true;

        // Create crawl log
        $this->__createSERPCrawlLog($keywordInfo['name'], $seDomainCat, true, "Task posted: " . $apiResult['task_id']);

        return $result;
    }

    /**
     * Get pending SERP task for a specific keyword and search engine
     *
     * @param int $keywordId Keyword ID
     * @param int $seId Search engine ID
     * @param string $reportDate Report date
     * @return array|false Task info or false if not found
     */
    function getPendingSERPTaskByRef($keywordId, $seId, $reportDate = '') {
        $reportDate = !empty($reportDate) ? addslashes($reportDate) : date('Y-m-d');
        $whereCond = "category='serp' AND ref_id=" . intval($keywordId) . " AND ref_url='" . intval($seId) . "' AND report_date='$reportDate' AND status='pending'";
        return $this->dbHelper->getRow($this->dfsTasksTable, $whereCond);
    }

    /**
     * Post SERP task to DataForSEO API
     *
     * @param string $seDomainCat Search engine category (google, bing, yahoo)
     * @param array $params API parameters
     * @return array ['status' => bool, 'message' => string, 'task_id' => string]
     */
    function __postSERPTaskToAPI($seDomainCat, $params) {
        $connResult = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'] ?? 'Internal error occurred',
            'task_id' => '',
        ];

        $endpoint = "/v3/serp/$seDomainCat/organic/task_post";

        try {
            $result = $this->restClient->post($endpoint, [$params]);

            if ($result['status_code'] == 20000) {
                foreach ($result['tasks'] as $taskInfo) {
                    if (in_array($taskInfo['status_code'], [20000, 20100]) && !empty($taskInfo['id'])) {
                        $connResult['status'] = true;
                        $connResult['message'] = "Task created successfully";
                        $connResult['task_id'] = $taskInfo['id'];
                    } else {
                        $connResult['message'] = $taskInfo['status_message'];
                    }
                    break;
                }
            } else {
                $connResult['message'] = $result['status_message'];
            }
        } catch (RestClientException $e) {
            $connResult['message'] = "HTTP code: {$e->getHttpCode()}, Error: {$e->getMessage()}";
        }

        return $connResult;
    }

    /**
     * Fetch results for a pending SERP task
     *
     * @param array $taskInfo Task info from database
     * @return array ['status' => 0|1, 'data' => array, 'msg' => string, 'completed' => bool]
     */
    function fetchSERPTaskResult($taskInfo) {
        $result = ['status' => 0, 'data' => [], 'msg' => '', 'completed' => false];

        $apiResult = $this->__getSERPTaskFromAPI($taskInfo['platform'], $taskInfo['task_id']);

        // Task still processing
        if ($apiResult['pending']) {
            $result['msg'] = "Task still processing";
            return $result;
        }

        // Task failed
        if (!$apiResult['status']) {
            $this->updateDFSTaskStatus($taskInfo['id'], 'failed', $apiResult['message']);
            $result['msg'] = $apiResult['message'];
            $result['completed'] = true;
            $this->__createSERPCrawlLog($taskInfo['ref_id'], $taskInfo['platform'], false, $apiResult['message']);
            return $result;
        }

        // Task completed successfully
        $this->updateDFSTaskStatus($taskInfo['id'], 'completed');

        $result['status'] = 1;
        $result['data'] = $apiResult['data'];
        $result['msg'] = "SERP results fetched successfully via DataForSEO";
        $result['completed'] = true;

        $this->__createSERPCrawlLog($taskInfo['ref_id'], $taskInfo['platform'], true, $result['msg']);

        return $result;
    }

    /**
     * Get SERP task result from DataForSEO API
     *
     * @param string $seDomainCat Search engine category (google, bing, yahoo)
     * @param string $taskId Task ID
     * @return array ['status' => bool, 'message' => string, 'data' => array, 'pending' => bool]
     */
    function __getSERPTaskFromAPI($seDomainCat, $taskId) {
        $connResult = [
            'status' => false,
            'message' => $_SESSION['text']['common']['Internal error occured'] ?? 'Internal error occurred',
            'data' => [],
            'pending' => false,
        ];

        $endpoint = "/v3/serp/$seDomainCat/organic/task_get/regular/$taskId";

        try {
            $result = $this->restClient->get($endpoint);

            if ($result['status_code'] == 20000) {
                foreach ($result['tasks'] as $taskInfo) {
                    // Task completed successfully
                    if ($taskInfo['status_code'] == 20000 && !empty($taskInfo['result'])) {
                        $connResult['status'] = true;
                        $connResult['message'] = "Success";
                        $connResult['data'] = $taskInfo['result'][0];
                        return $connResult;
                    }
                    // Task still processing
                    elseif ($taskInfo['status_code'] == 20100) {
                        $connResult['pending'] = true;
                        $connResult['message'] = "Task still processing";
                        return $connResult;
                    }
                    // Task failed
                    else {
                        $connResult['message'] = $taskInfo['status_message'];
                        return $connResult;
                    }
                }
            } else {
                $connResult['message'] = $result['status_message'];
            }
        } catch (RestClientException $e) {
            $connResult['message'] = "HTTP code: {$e->getHttpCode()}, Error: {$e->getMessage()}";
        }

        return $connResult;
    }

    /**
     * Process a SERP task - fetch result and save to database
     *
     * @param array $taskInfo Task information from database
     * @param bool $verbose Print output messages
     * @return array ['completed' => bool, 'success' => bool, 'message' => string]
     */
    function processSerpTask($taskInfo, $verbose = true) {
        $result = ['completed' => false, 'success' => false, 'message' => ''];

        if ($verbose) echo "  - Processing SERP task {$taskInfo['task_id']} for {$taskInfo['platform']}...";

        // Fetch result from DataForSEO
        $apiResult = $this->fetchSERPTaskResult($taskInfo);

        if (!$apiResult['completed']) {
            // Task still pending on DataForSEO side
            $result['message'] = "Still processing";
            if ($verbose) echo " still processing\n";
            return $result;
        }

        $result['completed'] = true;

        if ($apiResult['status'] == 0) {
            // Task failed - copy yesterday's result as fallback
            include_once(SP_CTRLPATH . "/report.ctrl.php");
            $fallbackCtrler = new ReportController();
            $fallbackCtrler->copyYesterdayResult($taskInfo['ref_id'], intval($taskInfo['ref_url']), $taskInfo['report_date']);

            $result['success'] = false;
            $result['message'] = $apiResult['msg'];
            if ($verbose) echo " failed: {$result['message']}, copied yesterday's result\n";
            return $result;
        }

        // Task completed successfully - save results
        $keywordId = $taskInfo['ref_id'];
        $seId = intval($taskInfo['ref_url']); // Search engine ID stored in ref_url
        $reportDate = $taskInfo['report_date'];

        // Get keyword info
        include_once(SP_CTRLPATH . "/keyword.ctrl.php");
        include_once(SP_CTRLPATH . "/report.ctrl.php");
        include_once(SP_CTRLPATH . "/website.ctrl.php");

        $keywordCtrler = new KeywordController();
        $keywordInfo = $keywordCtrler->__getKeywordInfo($keywordId);

        if (empty($keywordInfo)) {
            $result['success'] = false;
            $result['message'] = "Keyword not found: $keywordId";
            if ($verbose) echo " failed: {$result['message']}\n";
            return $result;
        }

        // Get website URL
        $websiteCtrler = new WebsiteController();
        $websiteInfo = $websiteCtrler->__getWebsiteInfo($keywordInfo['website_id']);
        $websiteUrl = formatUrl($websiteInfo['url'], false);
        $websiteOtherUrl = SettingsController::getWebsiteOtherUrl($websiteUrl);

        // Process SERP results
        $matchCount = 0;
        if (!empty($apiResult['data']['items'])) {
            $reportCtrler = new ReportController();
            $firstMatch = true;

            foreach ($apiResult['data']['items'] as $itemInfo) {
                $url = $itemInfo['url'];

                // Check if URL matches website
                if (
                    stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) ||
                    stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl)
                ) {
                    $matchInfo = [];
                    $matchInfo['url'] = $url;
                    $matchInfo['title'] = $itemInfo['title'];
                    $matchInfo['description'] = $itemInfo['description'];
                    $matchInfo['rank'] = $itemInfo['rank_group'];
                    $matchInfo['se_id'] = $seId;
                    $matchInfo['keyword_id'] = $keywordId;

                    // Remove previous results only for first match
                    $reportCtrler->saveMatchedKeywordInfo($matchInfo, $firstMatch, $reportDate);
                    $firstMatch = false;
                    $matchCount++;
                }
            }
        }

        // If no matches found, store rank 0
        if ($matchCount == 0) {
            $reportCtrler = new ReportController();
            $matchInfo = [
                'keyword_id' => $keywordId,
                'se_id' => $seId,
                'rank' => 0,
                'url' => '',
                'title' => '',
                'description' => '',
            ];
            $reportCtrler->saveMatchedKeywordInfo($matchInfo, true, $reportDate);
            if ($verbose) echo " no matches, stored rank 0";
        }

        // Update cron track info
        $time = strtotime($reportDate);
        $reportCtrler = new ReportController();
        $reportCtrler->saveCronTrackInfo($keywordId, $seId, $time);

        $result['success'] = true;
        $result['message'] = $matchCount > 0 ? "Found $matchCount matches" : "No matches, stored rank 0";
        if ($verbose) echo " completed - {$result['message']}\n";

        return $result;
    }

    /**
     * Create crawl log for SERP operations
     *
     * @param string $keyword Keyword or keyword ID
     * @param string $platform Platform name (google, bing, yahoo)
     * @param bool $status Success status
     * @param string $message Log message
     */
    function __createSERPCrawlLog($keyword, $platform, $status, $message) {
        $crawlLogCtrl = new CrawlLogController();
        $crawlInfo = [];
        $crawlInfo['crawl_type'] = 'keyword';
        $crawlInfo['crawl_status'] = $status ? 1 : 0;
        $crawlInfo['ref_id'] = $keyword;
        $crawlInfo['subject'] = $platform;
        $crawlInfo['crawl_referer'] = $this->apiUrl;
        $crawlInfo['log_message'] = addslashes($message);
        $crawlInfo['crawl_link'] = '';
        $crawlLogCtrl->createCrawlLog($crawlInfo);
    }

    /**
     * Get keywords needing SERP task posting for a website
     *
     * @param int $websiteId Website ID
     * @param string $reportDate Report date
     * @return array List of keywords with search engines needing task posting
     */
    function getKeywordsNeedingSERPTaskPost($websiteId, $reportDate = '') {
        $reportDate = !empty($reportDate) ? addslashes($reportDate) : date('Y-m-d');
        $websiteId = intval($websiteId);
        $time = strtotime($reportDate);

        // Get keywords that are active and not yet processed
        $sql = "SELECT k.*, w.url as website_url
                FROM keywords k
                JOIN websites w ON k.website_id = w.id
                WHERE k.website_id = $websiteId
                AND k.status = 1
                ORDER BY k.name";

        $keywords = $this->db->select($sql);
        $result = [];

        if (empty($keywords)) {
            return $result;
        }

        // Get search engines list
        include_once(SP_CTRLPATH . "/searchengine.ctrl.php");
        $seController = new SearchEngineController();
        $seList = $seController->__getAllCrawlFormatedSearchEngines();

        foreach ($keywords as $keywordInfo) {
            $seIds = explode(':', $keywordInfo['searchengines']);

            foreach ($seIds as $seId) {
                if (empty($seList[$seId])) {
                    continue;
                }

                // Check if already has a report for today
                $existingReport = $this->dbHelper->getRow(
                    'rankresults',
                    "keyword_id=" . intval($keywordInfo['id']) . " AND searchengine_id=" . intval($seId) . " AND result_time=$time"
                );

                if (!empty($existingReport)) {
                    continue;
                }

                // Check if already has a pending DFS task
                $existingTask = $this->getPendingSERPTaskByRef($keywordInfo['id'], $seId, $reportDate);
                if (!empty($existingTask)) {
                    continue;
                }

                // Get search engine URL
                $urlInfo = parse_url($seList[$seId]['url']);
                $seUrl = $urlInfo['host'];

                // Check if search engine is supported (google, bing, yahoo)
                $seDomainCat = DataForSEOController::getSERPDomainCategory($seUrl);
                if (!in_array($seDomainCat, ['google', 'bing', 'yahoo'])) {
                    continue;
                }

                $result[] = [
                    'keyword_id' => $keywordInfo['id'],
                    'keyword_name' => $keywordInfo['name'],
                    'se_id' => $seId,
                    'se_url' => $seUrl,
                    'se_name' => $seList[$seId]['domain'],
                    'depth' => $seList[$seId]['max_results'],
                    'keyword_info' => $keywordInfo,
                ];
            }
        }

        return $result;
    }
}
?>