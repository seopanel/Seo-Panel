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

# class defines all dashboard controller functions
class DashboardController extends Controller {    
    var $pageScriptPath = 'dashboard.php';
        
    function __construct() {
        parent::__construct();        
    	$this->set('pageScriptPath', $this->pageScriptPath);
    	$this->set( 'pageNo', $_REQUEST['pageno']);
    }
    
    function showMainDashboard($info=[]) {
        $userId = isLoggedIn();

        // Load dashboard language texts
        $this->set('spTextDashboard', $this->getLanguageTexts('dashboard', $_SESSION['lang_code']));
        $this->set('spTextBack', $this->getLanguageTexts('backlink', $_SESSION['lang_code']));

        // Get user websites
        $websiteCtrler = New WebsiteController();
        $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

        if (empty($websiteList)) {
            $this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
            $this->render('dashboard/no_websites');
            return;
        }

        $this->set('siteList', $websiteList);
        $websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
        $this->set('websiteId', $websiteId);

        // Handle period selection (default: month)
        $period = !empty($info['period']) ? $info['period'] : 'month';
        $this->set('period', $period);

        // Calculate date range based on period
        $toTime = date('Y-m-d');
        switch ($period) {
            case 'day':
                $fromTime = date('Y-m-d', strtotime('-1 day'));
                $prevFromTime = date('Y-m-d', strtotime('-2 days'));
                break;
            case 'week':
                $fromTime = date('Y-m-d', strtotime('-7 days'));
                $prevFromTime = date('Y-m-d', strtotime('-14 days'));
                break;
            case 'year':
                $fromTime = date('Y-m-d', strtotime('-1 year'));
                $prevFromTime = date('Y-m-d', strtotime('-2 years'));
                break;
            case 'month':
            default:
                $fromTime = date('Y-m-d', strtotime('-30 days'));
                $prevFromTime = date('Y-m-d', strtotime('-60 days'));
                break;
        }

        // Calculate previous period end date (one day before current period start to avoid overlap)
        $prevToTime = date('Y-m-d', strtotime($fromTime . ' -1 day'));

        $this->set('fromTime', $fromTime);
        $this->set('toTime', $toTime);
        $this->set('prevFromTime', $prevFromTime);
        $this->set('prevToTime', $prevToTime);
        
        // get keyword last generated report date in between $fromTime and $toTime
        $kwResultLastDate = $this->__getKeywordLastReportGeneratedDate($websiteId, $fromTime, $toTime);
        $kwResultLastDate = !empty($kwResultLastDate) ? $kwResultLastDate : $toTime;
        $kwResultPreLastDate = $this->__getKeywordLastReportGeneratedDate($websiteId, $prevFromTime, $prevToTime);
        $kwResultPreLastDate = !empty($kwResultPreLastDate) ? $kwResultPreLastDate : $prevToTime;
        
        // Get detailed keyword distribution by rank ranges        
        $keywordDistribution = $this->getKeywordDistributionDetails($websiteId, $kwResultLastDate);        
        $this->set('keywordDistribution', $keywordDistribution);

        // Get keyword statistics
        $keywordStats = $this->getKeywordStats($websiteId, $fromTime, $kwResultLastDate);
        $this->set('keywordStats', $keywordStats);

        // Get previous period keyword statistics for comparison
        $prevKeywordStats = $this->getKeywordStats($websiteId, $prevFromTime, $kwResultPreLastDate);
        $this->set('prevKeywordStats', $prevKeywordStats);

        // Calculate keyword stats comparison
        $keywordComparison = $this->calculateComparison($keywordStats, $prevKeywordStats);
        $this->set('keywordComparison', $keywordComparison);

        // Get website overview stats
        $websiteStats = $this->getWebsiteOverviewStats($websiteId, $fromTime, $toTime);
        $this->set('websiteStats', $websiteStats);

        // Get previous period website stats for comparison
        $prevWebsiteStats = $this->getWebsiteOverviewStats($websiteId, $prevFromTime, $prevToTime);
        $this->set('prevWebsiteStats', $prevWebsiteStats);

        // Calculate website stats comparison
        $websiteComparison = $this->calculateComparison($websiteStats, $prevWebsiteStats);
        $this->set('websiteComparison', $websiteComparison);
                
        // Get ranking volatility stats
        $rankingVolatility = $this->getRankingVolatility($websiteId, $fromTime, $toTime);
        $this->set('rankingVolatility', $rankingVolatility);
        
        // Get ranking trends data for graph
        $rankingTrends = $this->getRankingTrends($websiteId, $fromTime, $toTime);
        $this->set('rankingTrends', $rankingTrends);

        // Get top keywords
        $topKeywords = $this->getTopKeywords($websiteId, $kwResultLastDate, 15);
        $this->set('topKeywords', $topKeywords);

        // Get recent activity
        $recentActivity = $this->getRecentActivity($websiteId, 15);
        $this->set('recentActivity', $recentActivity);

        // Get search engine distribution stats
        $searchEngineStats = $this->getSearchEngineStats($websiteId, $fromTime, $toTime);
        $this->set('searchEngineStats', $searchEngineStats);

        $this->render('dashboard/main');
    }

    function showSocialMediaDashboard($info=[]) {
        $userId = isLoggedIn();

        // Load dashboard language texts
        $this->set('spTextDashboard', $this->getLanguageTexts('dashboard', $_SESSION['lang_code']));

        // Get user websites
        $websiteCtrler = New WebsiteController();
        $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

        if (empty($websiteList)) {
            $this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
            $this->render('dashboard/no_websites');
            return;
        }

        $this->set('siteList', $websiteList);
        $websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
        $this->set('websiteId', $websiteId);

        // Handle period selection (default: month)
        $period = !empty($info['period']) ? $info['period'] : 'month';
        $this->set('period', $period);

        // Calculate date range based on period
        $toTime = date('Y-m-d');
        switch ($period) {
            case 'day':
                $fromTime = date('Y-m-d', strtotime('-1 day'));
                $prevFromTime = date('Y-m-d', strtotime('-2 days'));
                break;
            case 'week':
                $fromTime = date('Y-m-d', strtotime('-7 days'));
                $prevFromTime = date('Y-m-d', strtotime('-14 days'));
                break;
            case 'year':
                $fromTime = date('Y-m-d', strtotime('-1 year'));
                $prevFromTime = date('Y-m-d', strtotime('-2 years'));
                break;
            case 'month':
            default:
                $fromTime = date('Y-m-d', strtotime('-30 days'));
                $prevFromTime = date('Y-m-d', strtotime('-60 days'));
                break;
        }

        // Calculate previous period end date
        $prevToTime = date('Y-m-d', strtotime($fromTime . ' -1 day'));

        $this->set('fromTime', $fromTime);
        $this->set('toTime', $toTime);
        $this->set('prevFromTime', $prevFromTime);
        $this->set('prevToTime', $prevToTime);

        // Get social media statistics
        $socialMediaStats = $this->getSocialMediaStats($websiteId, $fromTime, $toTime);
        $socialMediaDistribution = $this->getSocialMediaDistribution($websiteId, $toTime);
        $socialMediaTrends = $this->getSocialMediaTrends($websiteId, $fromTime, $toTime);
        $topSocialMediaLinks = $this->getTopSocialMediaLinks($websiteId, $toTime);

        // Get previous period social media stats for comparison
        $prevSocialMediaStats = $this->getSocialMediaStats($websiteId, $prevFromTime, $prevToTime);

        // Calculate social media stats comparison
        $socialMediaComparison = $this->calculateComparison($socialMediaStats, $prevSocialMediaStats);

        // Pass social media data to view
        $this->set('socialMediaStats', $socialMediaStats);
        $this->set('socialMediaDistribution', $socialMediaDistribution);
        $this->set('socialMediaTrends', $socialMediaTrends);
        $this->set('topSocialMediaLinks', $topSocialMediaLinks);
        $this->set('prevSocialMediaStats', $prevSocialMediaStats);
        $this->set('socialMediaComparison', $socialMediaComparison);

        $this->render('dashboard/social_media_main');
    }

    function showReviewDashboard($info=[]) {
        $userId = isLoggedIn();

        // Load dashboard language texts
        $this->set('spTextDashboard', $this->getLanguageTexts('dashboard', $_SESSION['lang_code']));

        // Get user websites
        $websiteCtrler = New WebsiteController();
        $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

        if (empty($websiteList)) {
            $this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
            $this->render('dashboard/no_websites');
            return;
        }

        $this->set('siteList', $websiteList);
        $websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
        $this->set('websiteId', $websiteId);

        // Handle period selection (default: month)
        $period = !empty($info['period']) ? $info['period'] : 'month';
        $this->set('period', $period);

        // Calculate date range based on period
        $toTime = date('Y-m-d');
        switch ($period) {
            case 'day':
                $fromTime = date('Y-m-d', strtotime('-1 day'));
                $prevFromTime = date('Y-m-d', strtotime('-2 days'));
                break;
            case 'week':
                $fromTime = date('Y-m-d', strtotime('-7 days'));
                $prevFromTime = date('Y-m-d', strtotime('-14 days'));
                break;
            case 'year':
                $fromTime = date('Y-m-d', strtotime('-1 year'));
                $prevFromTime = date('Y-m-d', strtotime('-2 years'));
                break;
            case 'month':
            default:
                $fromTime = date('Y-m-d', strtotime('-30 days'));
                $prevFromTime = date('Y-m-d', strtotime('-60 days'));
                break;
        }

        // Calculate previous period end date
        $prevToTime = date('Y-m-d', strtotime($fromTime . ' -1 day'));

        $this->set('fromTime', $fromTime);
        $this->set('toTime', $toTime);
        $this->set('prevFromTime', $prevFromTime);
        $this->set('prevToTime', $prevToTime);

        // Get review statistics
        $reviewStats = $this->getReviewStats($websiteId, $fromTime, $toTime);
        $reviewDistribution = $this->getReviewDistribution($websiteId, $toTime);
        $reviewTrends = $this->getReviewTrends($websiteId, $fromTime, $toTime);
        $topReviewLinks = $this->getTopReviewLinks($websiteId, $toTime);

        // Get previous period review stats for comparison
        $prevReviewStats = $this->getReviewStats($websiteId, $prevFromTime, $prevToTime);

        // Calculate review stats comparison
        $reviewComparison = $this->calculateComparison($reviewStats, $prevReviewStats);

        // Pass review data to view
        $this->set('reviewStats', $reviewStats);
        $this->set('reviewDistribution', $reviewDistribution);
        $this->set('reviewTrends', $reviewTrends);
        $this->set('topReviewLinks', $topReviewLinks);
        $this->set('prevReviewStats', $prevReviewStats);
        $this->set('reviewComparison', $reviewComparison);

        $this->render('dashboard/review_main');
    }

    function __getKeywordLastReportGeneratedDate($websiteId, $fromTime, $toTime) {
        // Find the latest date within the date range where keywords have results with rank > 0
        $sql = "SELECT MAX(sr.result_date) as last_report_date
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date BETWEEN '$fromTime' AND '$toTime'
                    AND sr.rank > 0";

        $result = $this->db->select($sql, true);
        if ($result && !empty($result['last_report_date'])) {
            return $result['last_report_date'];
        }

        return null;
    }
    
    function __getWebsiteLastReportGeneratedDate($websiteId, $fromTime, $toTime) {
        // Find the latest date within the date range where keywords have results with rank > 0
        $sql = "SELECT MAX(rr.result_date) as last_report_date
                FROM rankresults rr
                WHERE rr.website_id=" . intval($websiteId) . "
                    AND rr.result_date BETWEEN '$fromTime' AND '$toTime'
                    AND rr.domain_authority > 0";
        
        $result = $this->db->select($sql, true);
        if ($result && !empty($result['last_report_date'])) {
            return $result['last_report_date'];
        }
        
        return null;
    }

    // Get keyword statistics
    private function getKeywordStats($websiteId, $fromTime, $toTime) {
        $stats = [];

        // Total keywords
        $sql = "SELECT COUNT(*) as total FROM keywords WHERE website_id=" . intval($websiteId);
        $result = $this->db->select($sql, true);
        $stats['total'] = $result['total'];

        // Keywords with results in period
        $sql = "SELECT COUNT(DISTINCT k.id) as tracked
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date BETWEEN '$fromTime' AND '$toTime'";
        $result = $this->db->select($sql, true);
        $stats['tracked'] = $result['tracked'];

        // Top 10 rankings
        $sql = "SELECT COUNT(DISTINCT k.id) as top10
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$toTime'
                    AND sr.rank <= 10 AND sr.rank > 0";
        $result = $this->db->select($sql, true);
        $stats['top10'] = $result['top10'];

        // Top 3 rankings
        $sql = "SELECT COUNT(DISTINCT k.id) as top3
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$toTime'
                    AND sr.rank <= 3 AND sr.rank > 0";
        $result = $this->db->select($sql, true);
        $stats['top3'] = $result['top3'];

        // Top 11-20 rankings
        $sql = "SELECT COUNT(DISTINCT k.id) as top20
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$toTime'
                    AND sr.rank BETWEEN 11 AND 20";
        $result = $this->db->select($sql, true);
        $stats['top20'] = $result['top20'];

        // Top 21-50 rankings
        $sql = "SELECT COUNT(DISTINCT k.id) as top50
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$toTime'
                    AND sr.rank BETWEEN 21 AND 50";
        $result = $this->db->select($sql, true);
        $stats['top50'] = $result['top50'];

        // Top 51-100 rankings
        $sql = "SELECT COUNT(DISTINCT k.id) as top100
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$toTime'
                    AND sr.rank BETWEEN 51 AND 100";
        $result = $this->db->select($sql, true);
        $stats['top100'] = $result['top100'];

        return $stats;
    }

    // Get ranking trends for graph
    private function getRankingTrends($websiteId, $fromTime, $toTime) {
        $sql = "SELECT DATE(sr.result_date) as date,
                    ROUND(AVG(CASE WHEN sr.rank > 0 THEN sr.rank ELSE NULL END), 2) as avg_rank,
                    COUNT(DISTINCT CASE WHEN sr.rank <= 10 AND sr.rank > 0 THEN k.id END) as top10_count,
                    COUNT(DISTINCT CASE WHEN sr.rank <= 3 AND sr.rank > 0 THEN k.id END) as top3_count
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date BETWEEN '$fromTime' AND '$toTime'
                GROUP BY DATE(sr.result_date)
                ORDER BY date ASC";

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get website overview statistics
    private function getWebsiteOverviewStats($websiteId, $fromTime, $toTime) {
        $stats = [];

        // Get backlinks count (latest within time range - external_pages_to_page)
        $sql = "SELECT external_pages_to_page, external_pages_to_root_domain,result_date
                FROM backlinkresults
                WHERE website_id=" . intval($websiteId) . "
                AND result_date >= '$fromTime' AND result_date <= '$toTime'
                ORDER BY result_date DESC
                LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['backlinks'] = $result['external_pages_to_page'] ?? 0;
        $stats['external_pages_to_page'] = $result['external_pages_to_page'] ?? 0;
        $stats['external_pages_to_root_domain'] = $result['external_pages_to_root_domain'] ?? 0;

        // Get indexed pages (latest within time range - sum of google and msn)
        $sql = "SELECT google, msn, result_date
                FROM saturationresults
                WHERE website_id=" . intval($websiteId) . "
                AND result_date >= '$fromTime' AND result_date <= '$toTime'
                ORDER BY result_date DESC
                LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['indexed_pages'] = ($result['google'] ?? 0) + ($result['msn'] ?? 0);
        $stats['google_indexed'] = $result['google'] ?? 0;
        $stats['msn_indexed'] = $result['msn'] ?? 0;

        // Get Moz rank (latest within time range)
        $sql = "SELECT spam_score, domain_authority, page_authority, result_date
                FROM rankresults
                WHERE website_id=" . intval($websiteId) . "
                AND result_date >= '$fromTime' AND result_date <= '$toTime'
                ORDER BY result_date DESC
                LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['spam_score'] = $result['spam_score'] ?? 0;
        $stats['domain_authority'] = $result['domain_authority'] ?? 0;
        $stats['page_authority'] = $result['page_authority'] ?? 0;

        return $stats;
    }

    // Get top performing keywords
    private function getTopKeywords($websiteId, $date, $limit = 10) {
        $sql = "SELECT k.name, k.id, sr.rank, se.domain as search_engine
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                    INNER JOIN searchengines se ON sr.searchengine_id = se.id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$date'
                    AND sr.rank > 0
                ORDER BY sr.rank ASC
                LIMIT " . intval($limit);

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get recent ranking check activity
    private function getRecentActivity($websiteId, $limit = 10) {
        $sql = "SELECT k.name as keyword, sr.rank, sr.result_date, se.domain as search_engine
                FROM searchresults sr
                    INNER JOIN keywords k ON sr.keyword_id = k.id
                    INNER JOIN searchengines se ON sr.searchengine_id = se.id
                WHERE k.website_id=" . intval($websiteId) . "
                ORDER BY sr.result_date DESC, sr.id DESC
                LIMIT " . intval($limit);

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get search engine distribution statistics
    private function getSearchEngineStats($websiteId, $fromTime, $toTime) {
        $sql = "SELECT se.domain as search_engine, COUNT(DISTINCT k.id) as keyword_count
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                    INNER JOIN searchengines se ON sr.searchengine_id = se.id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date BETWEEN '$fromTime' AND '$toTime'
                GROUP BY se.id, se.domain
                ORDER BY keyword_count DESC";

        $results = $this->db->select($sql);

        $stats = [];
        if ($results) {
            foreach ($results as $row) {
                $stats[$row['search_engine']] = intval($row['keyword_count']);
            }
        }

        return $stats;
    }

    // Calculate comparison between current and previous period
    private function calculateComparison($current, $previous) {
        $comparison = [];

        foreach ($current as $key => $currentValue) {
            $prevValue = isset($previous[$key]) ? $previous[$key] : 0;
            $diff = $currentValue - $prevValue;

            // Calculate percentage change
            if ($prevValue > 0) {
                $percentChange = round(($diff / $prevValue) * 100, 1);
            } else {
                $percentChange = ($currentValue > 0) ? 100 : 0;
            }

            // For spam_score, lower is better, so invert the direction
            if ($key === 'spam_score') {
                $direction = $diff > 0 ? 'down' : ($diff < 0 ? 'up' : 'neutral');
            } else {
                $direction = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral');
            }

            $comparison[$key] = [
                'diff' => $diff,
                'percent' => $percentChange,
                'direction' => $direction
            ];
        }

        return $comparison;
    }

    // Get ranking volatility - keywords with most rank changes
    // Group by both keyword and search engine to track volatility per search engine
    private function getRankingVolatility($websiteId, $fromTime, $toTime) {
        // Get rank changes for each keyword per search engine within the period
        // Also get first and last rank to determine trend direction
        $sql = "SELECT k.id as keyword_id, k.name as keyword_name,
                    se.id as searchengine_id, se.domain as search_engine,
                    MAX(sr.rank) as max_rank,
                    MIN(CASE WHEN sr.rank > 0 THEN sr.rank END) as min_rank,
                    COUNT(DISTINCT sr.result_date) as check_count,
                    AVG(CASE WHEN sr.rank > 0 THEN sr.rank END) as avg_rank,
                    STDDEV(CASE WHEN sr.rank > 0 THEN sr.rank END) as rank_stddev,
                    (SELECT sr2.rank
                     FROM searchresults sr2
                     WHERE sr2.keyword_id = k.id
                        AND sr2.searchengine_id = se.id
                        AND sr2.result_date BETWEEN '$fromTime' AND '$toTime'
                        AND sr2.rank > 0
                     ORDER BY sr2.result_date ASC
                     LIMIT 1) as first_rank,
                    (SELECT sr3.rank
                     FROM searchresults sr3
                     WHERE sr3.keyword_id = k.id
                        AND sr3.searchengine_id = se.id
                        AND sr3.result_date BETWEEN '$fromTime' AND '$toTime'
                        AND sr3.rank > 0
                     ORDER BY sr3.result_date DESC
                     LIMIT 1) as last_rank
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                    INNER JOIN searchengines se ON sr.searchengine_id = se.id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date BETWEEN '$fromTime' AND '$toTime'
                    AND sr.rank > 0
                GROUP BY k.id, k.name, se.id, se.domain
                HAVING check_count >= 2 AND min_rank > 0
                ORDER BY rank_stddev DESC, max_rank DESC
                LIMIT 10";

        $results = $this->db->select($sql);

        $volatilityData = [];
        if ($results) {
            foreach ($results as $row) {
                // Calculate rank change: negative means improvement (rank went down in number)
                // positive means decline (rank went up in number)
                $rankChange = intval($row['last_rank']) - intval($row['first_rank']);

                // Determine if overall trend is improving or declining
                // Improvement = rank number decreased (e.g., 14 -> 4)
                // Decline = rank number increased (e.g., 4 -> 14)
                $trendDirection = 'neutral';
                if ($rankChange < 0) {
                    $trendDirection = 'improving'; // Rank number decreased = better position
                } elseif ($rankChange > 0) {
                    $trendDirection = 'declining'; // Rank number increased = worse position
                }

                $volatilityData[] = [
                    'keyword' => $row['keyword_name'],
                    'search_engine' => formatUrl($row['search_engine']),
                    'rank_change' => $rankChange,
                    'rank_change_abs' => abs($rankChange),
                    'max_rank' => intval($row['max_rank']),
                    'min_rank' => intval($row['min_rank']),
                    'first_rank' => intval($row['first_rank']),
                    'last_rank' => intval($row['last_rank']),
                    'avg_rank' => round($row['avg_rank'], 1),
                    'volatility_score' => round($row['rank_stddev'], 2),
                    'trend_direction' => $trendDirection
                ];
            }
        }

        return $volatilityData;
    }
    
    function __generateKeywordDistributionResult($websiteId, $date, $dstType="top10", $limit=10) {
        $dstResults = [
            "count" => 0,
            "rows" => [],
        ];
        switch ($dstType) {            
            case "top10":
            default:
                $rankCond = "sr.rank BETWEEN 1 AND 10";
                break;
                
            case "top20":
                $rankCond = "sr.rank BETWEEN 11 AND 20";
                break;
                
            case "top50":
                $rankCond = "sr.rank BETWEEN 21 AND 50";
                break;
            
            case "top100":
                $rankCond = "sr.rank BETWEEN 51 AND 100";
                break;
                
            case "not_ranked":
                $rankCond = "(sr.rank IS NULL OR sr.rank <= 0 OR sr.rank > 100)";
                break;
        }
        
        // Get Top 10 keywords
        $countSel = "count(*) as count";
        $rowSel = "k.name, sr.rank, se.domain as search_engine";
        $sql = "SELECT [SELECT_COLS]
                FROM keywords k
                    INNER JOIN searchresults sr ON k.id = sr.keyword_id
                    INNER JOIN searchengines se ON sr.searchengine_id = se.id
                WHERE k.website_id=" . intval($websiteId) . "
                    AND sr.result_date='$date'
                    AND $rankCond
                ORDER BY sr.rank ASC
                LIMIT $limit";
        
        $countRes = $this->db->select(str_replace("[SELECT_COLS]", $countSel, $sql), true);
        if ($countRes['count'] > 0) {
            $dstResults['count'] = $countRes['count'];
            $results = $this->db->select(str_replace("[SELECT_COLS]", $rowSel, $sql));
            $dstResults['rows'] = $results;
        }        
        
        return $dstResults;
    }

    // Get detailed keyword distribution by rank ranges
    private function getKeywordDistributionDetails($websiteId, $date) {
        $distribution = [
            'top10' => [],
            'top20' => [],
            'top50' => [],
            'top100' => [],
            'not_ranked' => []
        ];
        
        foreach (array_keys($distribution) as $distCol) {
            $distribution[$distCol] = $this->__generateKeywordDistributionResult($websiteId, $date, $distCol, 5);
        }

        return $distribution;
    }

    // Get social media statistics with comparison
    private function getSocialMediaStats($websiteId, $fromTime, $toTime) {
        $stats = [];

        // Get total active social media links
        $sql = "SELECT COUNT(*) as total_links
                FROM social_media_links
                WHERE website_id=" . intval($websiteId) . "
                    AND status=1";
        $result = $this->db->select($sql, true);
        $stats['total_links'] = $result['total_links'] ?? 0;

        // Get latest social media results within the time period
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    COALESCE(SUM(smlr.followers), 0) as total_followers,
                    COALESCE(SUM(smlr.likes), 0) as total_likes
                FROM social_media_links sml
                LEFT JOIN (
                    SELECT sm_link_id, MAX(report_date) as max_date
                    FROM social_media_link_results
                    WHERE report_date BETWEEN '$fromTime' AND '$toTime'
                    GROUP BY sm_link_id
                ) latest ON sml.id = latest.sm_link_id
                LEFT JOIN social_media_link_results smlr
                    ON sml.id = smlr.sm_link_id AND smlr.report_date = latest.max_date
                WHERE sml.website_id=" . intval($websiteId) . "
                    AND sml.status=1";
        $result = $this->db->select($sql, true);
        $stats['total_followers'] = $result['total_followers'] ?? 0;
        $stats['total_likes'] = $result['total_likes'] ?? 0;

        return $stats;
    }

    // Get social media distribution by platform
    private function getSocialMediaDistribution($websiteId, $date) {
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    sml.type,
                    COUNT(sml.id) as link_count,
                    COALESCE(SUM(smlr.followers), 0) as total_followers,
                    COALESCE(SUM(smlr.likes), 0) as total_likes
                FROM social_media_links sml
                LEFT JOIN (
                    SELECT sm_link_id, MAX(report_date) as max_date
                    FROM social_media_link_results
                    WHERE report_date <= '$date'
                    GROUP BY sm_link_id
                ) latest ON sml.id = latest.sm_link_id
                LEFT JOIN social_media_link_results smlr
                    ON sml.id = smlr.sm_link_id AND smlr.report_date = latest.max_date
                WHERE sml.website_id=" . intval($websiteId) . "
                    AND sml.status = 1
                GROUP BY sml.type
                ORDER BY total_followers DESC";

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get social media trends over time
    private function getSocialMediaTrends($websiteId, $fromTime, $toTime) {
        $sql = "SELECT
                    smlr.report_date as date,
                    SUM(smlr.followers) as total_followers,
                    SUM(smlr.likes) as total_likes
                FROM social_media_link_results smlr
                INNER JOIN social_media_links sml ON smlr.sm_link_id = sml.id
                WHERE sml.website_id=" . intval($websiteId) . "
                    AND sml.status = 1
                    AND smlr.report_date BETWEEN '$fromTime' AND '$toTime'
                GROUP BY smlr.report_date
                ORDER BY smlr.report_date ASC";

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get top performing social media links
    private function getTopSocialMediaLinks($websiteId, $date, $limit=10) {
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    sml.name,
                    sml.type,
                    sml.url,
                    smlr.followers,
                    smlr.likes,
                    smlr.report_date
                FROM social_media_links sml
                LEFT JOIN (
                    SELECT sm_link_id, MAX(report_date) as max_date
                    FROM social_media_link_results
                    WHERE report_date <= '$date'
                    GROUP BY sm_link_id
                ) latest ON sml.id = latest.sm_link_id
                LEFT JOIN social_media_link_results smlr
                    ON sml.id = smlr.sm_link_id AND smlr.report_date = latest.max_date
                WHERE sml.website_id=" . intval($websiteId) . "
                    AND sml.status = 1
                ORDER BY smlr.followers DESC, smlr.likes DESC
                LIMIT " . intval($limit);

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get review statistics with comparison
    private function getReviewStats($websiteId, $fromTime, $toTime) {
        $stats = [];

        // Get total active review links
        $sql = "SELECT COUNT(*) as total_links
                FROM review_links
                WHERE website_id=" . intval($websiteId) . "
                    AND status=1";
        $result = $this->db->select($sql, true);
        $stats['total_links'] = $result['total_links'] ?? 0;

        // Get latest review results within the time period
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    COALESCE(SUM(rlr.reviews), 0) as total_reviews,
                    COALESCE(AVG(rlr.rating), 0) as avg_rating
                FROM review_links rl
                LEFT JOIN (
                    SELECT review_link_id, MAX(report_date) as max_date
                    FROM review_link_results
                    WHERE report_date BETWEEN '$fromTime' AND '$toTime'
                    GROUP BY review_link_id
                ) latest ON rl.id = latest.review_link_id
                LEFT JOIN review_link_results rlr
                    ON rl.id = rlr.review_link_id AND rlr.report_date = latest.max_date
                WHERE rl.website_id=" . intval($websiteId) . "
                    AND rl.status=1";
        $result = $this->db->select($sql, true);
        $stats['total_reviews'] = $result['total_reviews'] ?? 0;
        $stats['avg_rating'] = round($result['avg_rating'], 2);

        return $stats;
    }

    // Get review distribution by platform
    private function getReviewDistribution($websiteId, $date) {
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    rl.type,
                    COUNT(rl.id) as link_count,
                    COALESCE(SUM(rlr.reviews), 0) as total_reviews,
                    COALESCE(AVG(rlr.rating), 0) as avg_rating
                FROM review_links rl
                LEFT JOIN (
                    SELECT review_link_id, MAX(report_date) as max_date
                    FROM review_link_results
                    WHERE report_date <= '$date'
                    GROUP BY review_link_id
                ) latest ON rl.id = latest.review_link_id
                LEFT JOIN review_link_results rlr
                    ON rl.id = rlr.review_link_id AND rlr.report_date = latest.max_date
                WHERE rl.website_id=" . intval($websiteId) . "
                    AND rl.status = 1
                GROUP BY rl.type
                ORDER BY total_reviews DESC";

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get review trends over time
    private function getReviewTrends($websiteId, $fromTime, $toTime) {
        $sql = "SELECT
                    rlr.report_date as date,
                    SUM(rlr.reviews) as total_reviews,
                    AVG(rlr.rating) as avg_rating
                FROM review_link_results rlr
                INNER JOIN review_links rl ON rlr.review_link_id = rl.id
                WHERE rl.website_id=" . intval($websiteId) . "
                    AND rl.status = 1
                    AND rlr.report_date BETWEEN '$fromTime' AND '$toTime'
                GROUP BY rlr.report_date
                ORDER BY rlr.report_date ASC";

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    // Get top performing review links
    private function getTopReviewLinks($websiteId, $date, $limit=10) {
        // Using derived table for better performance instead of correlated subquery
        $sql = "SELECT
                    rl.name,
                    rl.type,
                    rl.url,
                    rlr.reviews,
                    rlr.rating,
                    rlr.report_date
                FROM review_links rl
                LEFT JOIN (
                    SELECT review_link_id, MAX(report_date) as max_date
                    FROM review_link_results
                    WHERE report_date <= '$date'
                    GROUP BY review_link_id
                ) latest ON rl.id = latest.review_link_id
                LEFT JOIN review_link_results rlr
                    ON rl.id = rlr.review_link_id AND rlr.report_date = latest.max_date
                WHERE rl.website_id=" . intval($websiteId) . "
                    AND rl.status = 1
                ORDER BY rlr.reviews DESC, rlr.rating DESC
                LIMIT " . intval($limit);

        $results = $this->db->select($sql);
        return $results ? $results : [];
    }

    function showSiteAuditorDashboard($info=[]) {
        $userId = isLoggedIn();

        // Load language texts
        $this->set('spTextDashboard', $this->getLanguageTexts('dashboard', $_SESSION['lang_code']));
        $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
        $this->set('spTextSA', $spTextSA);
        $spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
        $this->set('spTextHome', $spTextHome);

        // Get user websites (same as other dashboard tabs)
        $websiteCtrler = New WebsiteController();
        $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

        if (empty($websiteList)) {
            $this->set('noProjects', true);
            $this->render('dashboard/siteauditor_main');
            return;
        }

        $this->set('siteList', $websiteList);
        $websiteId = isset($info['website_id']) ? intval($info['website_id']) : $websiteList[0]['id'];
        $this->set('websiteId', $websiteId);

        // Get Site Auditor project for selected website
        $siteAuditorCtrl = New SiteauditorController();
        $sql = "SELECT ap.*, w.url, w.name FROM auditorprojects ap, websites w
                WHERE w.id = ap.website_id AND ap.website_id = $websiteId AND ap.status = 1 LIMIT 1";
        $projectInfo = $this->db->select($sql, true);

        if (empty($projectInfo)) {
            $this->set('noProjectForWebsite', true);
            $this->set('selectedWebsiteId', $websiteId);
            $this->render('dashboard/siteauditor_main');
            return;
        }

        $projectId = $projectInfo['id'];
        $this->set('projectId', $projectId);

        // Get project statistics
        $projectInfo['total_links'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id']);
        $projectInfo['crawled_links'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], true);
        $projectInfo['last_updated'] = $siteAuditorCtrl->getProjectLastUpdate($projectInfo['id']);

        // Status check for crawled filter - default to crawled=1 on first load
        $statusCheck = false;
        $statusVal = 0;
        $crawledVal = isset($info['crawled']) ? $info['crawled'] : 1;
        // Apply filter if crawled is set OR on first load (default to 1)
        if($crawledVal != -1) {
            $statusCheck = true;
            $statusVal = intval($crawledVal);
        }
        $this->set('crawled', $crawledVal);

        // Broken links
        $conditions = " and brocken=1";
        $projectInfo['brocken'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        // Backlinks
        $seArr = ['google'];
        foreach ($seArr as $se) {
            $conditions = " and $se"."_backlinks>0";
            $projectInfo[$se."_backlinks"] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);
        }

        // No backlinks
        $conditions = " and google_backlinks=0";
        $projectInfo['no_backlinks'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        // Indexed status
        foreach ($seArr as $se) {
            $conditions = " and $se"."_indexed>0";
            $projectInfo[$se."_indexed"] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

            $conditions = " and $se"."_indexed=0";
            $projectInfo[$se."_not_indexed"] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);
        }

        // Bing indexed
        $conditions = " and bing_indexed>0";
        $projectInfo['bing_indexed'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);
        $conditions = " and bing_indexed=0";
        $projectInfo['bing_not_indexed'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        // Duplicate meta info
        $metaArr = array('page_title' => $spTextSA["Duplicate Title"], 'page_description' => $spTextSA['Duplicate Description'], 'page_keywords' => $spTextSA['Duplicate Keywords']);
        $auditorComp = $siteAuditorCtrl->createComponent('AuditorComponent');
        foreach ($metaArr as $meta => $val) {
            $projectInfo["duplicate_".$meta] = $auditorComp->getDuplicateMetaInfoCount($projectInfo['id'], $meta, $statusCheck, $statusVal);
        }

        // Modern SEO features
        $conditions = " and mobile_friendly=1";
        $projectInfo['mobile_friendly'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and https_secure=1";
        $projectInfo['https_secure'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and ai_robot_allowed=1";
        $projectInfo['ai_robot_allowed'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and has_og_tags=1";
        $projectInfo['has_og_tags'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and has_twitter_cards=1";
        $projectInfo['has_twitter_cards'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and blocked_by_robots=0";
        $projectInfo['allowed_by_robots'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        // Page Authority metrics
        $paLevelFirst = defined('SA_PA_CHECK_LEVEL_FIRST') ? SA_PA_CHECK_LEVEL_FIRST : 40;
        $paLevelSecond = defined('SA_PA_CHECK_LEVEL_SECOND') ? SA_PA_CHECK_LEVEL_SECOND : 75;

        $conditions = " and page_authority >= $paLevelSecond";
        $projectInfo['pa_excellent'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and page_authority >= $paLevelFirst and page_authority < $paLevelSecond";
        $projectInfo['pa_good'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and page_authority > 0 and page_authority < $paLevelFirst";
        $projectInfo['pa_low'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $conditions = " and page_authority = 0";
        $projectInfo['pa_none'] = $siteAuditorCtrl->getCountcrawledLinks($projectInfo['id'], $statusCheck, $statusVal, $conditions);

        $this->set('projectInfo', $projectInfo);
        $this->set('metaArr', $metaArr);
        $this->set('seArr', $seArr);

        $this->render('dashboard/siteauditor_main');
    }

}
?>