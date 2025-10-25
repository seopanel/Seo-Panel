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

        // Get user websites
        $websiteCtrler = New WebsiteController();
        $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

        if (empty($websiteList)) {
            showErrorMsg($_SESSION['text']['common']['nowebsites']);
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
                $prevToTime = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'week':
                $fromTime = date('Y-m-d', strtotime('-7 days'));
                $prevFromTime = date('Y-m-d', strtotime('-14 days'));
                $prevToTime = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'year':
                $fromTime = date('Y-m-d', strtotime('-1 year'));
                $prevFromTime = date('Y-m-d', strtotime('-2 years'));
                $prevToTime = date('Y-m-d', strtotime('-1 year'));
                break;
            case 'month':
            default:
                $fromTime = date('Y-m-d', strtotime('-30 days'));
                $prevFromTime = date('Y-m-d', strtotime('-60 days'));
                $prevToTime = date('Y-m-d', strtotime('-30 days'));
                break;
        }

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

        // Get backlinks count (latest - external_pages_to_page)
        $sql = "SELECT external_pages_to_page, external_pages_to_root_domain
                FROM backlinkresults
                WHERE website_id=" . intval($websiteId) . "
                ORDER BY result_date DESC
                LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['backlinks'] = $result['external_pages_to_page'] ?? 0;
        $stats['external_pages_to_page'] = $result['external_pages_to_page'] ?? 0;
        $stats['external_pages_to_root_domain'] = $result['external_pages_to_root_domain'] ?? 0;

        // Get indexed pages (latest - sum of google and msn)
        $sql = "SELECT google, msn
                FROM saturationresults
                WHERE website_id=" . intval($websiteId) . "
                ORDER BY result_date DESC
                LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['indexed_pages'] = ($result['google'] ?? 0) + ($result['msn'] ?? 0);
        $stats['google_indexed'] = $result['google'] ?? 0;
        $stats['msn_indexed'] = $result['msn'] ?? 0;

        // Get Moz rank (latest)
        $sql = "SELECT spam_score, domain_authority, page_authority
                FROM rankresults
                WHERE website_id=" . intval($websiteId) . "
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

}
?>