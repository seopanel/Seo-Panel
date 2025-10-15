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
                break;
            case 'week':
                $fromTime = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'year':
                $fromTime = date('Y-m-d', strtotime('-1 year'));
                break;
            case 'month':
            default:
                $fromTime = date('Y-m-d', strtotime('-30 days'));
                break;
        }

        $this->set('fromTime', $fromTime);
        $this->set('toTime', $toTime);

        // Get keyword statistics
        $keywordStats = $this->getKeywordStats($websiteId, $fromTime, $toTime);
        $this->set('keywordStats', $keywordStats);

        // Get ranking trends data for graph
        $rankingTrends = $this->getRankingTrends($websiteId, $fromTime, $toTime);
        $this->set('rankingTrends', $rankingTrends);

        // Get website overview stats
        $websiteStats = $this->getWebsiteOverviewStats($websiteId, $fromTime, $toTime);
        $this->set('websiteStats', $websiteStats);

        // Get top keywords
        $topKeywords = $this->getTopKeywords($websiteId, $toTime, 10);
        $this->set('topKeywords', $topKeywords);

        // Get recent activity
        $recentActivity = $this->getRecentActivity($websiteId, 10);
        $this->set('recentActivity', $recentActivity);

        // Get search engine distribution stats
        $searchEngineStats = $this->getSearchEngineStats($websiteId, $fromTime, $toTime);
        $this->set('searchEngineStats', $searchEngineStats);

        $this->render('dashboard/main');
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

        // Get backlinks count (latest - sum of google and msn)
        $sql = "SELECT google, msn
                FROM backlinkresults
                WHERE website_id=" . intval($websiteId) . "
                ORDER BY result_date DESC, result_time DESC LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['backlinks'] = ($result['google'] ?? 0) + ($result['msn'] ?? 0);
        $stats['google_backlinks'] = $result['google'] ?? 0;
        $stats['msn_backlinks'] = $result['msn'] ?? 0;

        // Get indexed pages (latest - sum of google and msn)
        $sql = "SELECT google, msn
                FROM saturationresults
                WHERE website_id=" . intval($websiteId) . "
                ORDER BY result_date DESC, result_time DESC LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['indexed_pages'] = ($result['google'] ?? 0) + ($result['msn'] ?? 0);
        $stats['google_indexed'] = $result['google'] ?? 0;
        $stats['msn_indexed'] = $result['msn'] ?? 0;

        // Get Moz rank (latest)
        $sql = "SELECT moz_rank, domain_authority, page_authority
                FROM rankresults
                WHERE website_id=" . intval($websiteId) . "
                ORDER BY result_date DESC, result_time DESC LIMIT 1";
        $result = $this->db->select($sql, true);
        $stats['mozrank'] = $result['moz_rank'] ?? 0;
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

}
?>