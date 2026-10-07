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

/* class defines all overview controller functions */
class OverviewController extends Controller {
    var $baseUrl = "overview.php"; 
	
	function showOverView($searchInfo = '') {
	    $userId = isLoggedIn();
	    $websiteCtrler = New WebsiteController();
	    $websiteList = $websiteCtrler->__getAllWebsites($userId, true);

	    if (empty($websiteList)) {
	        $this->set('noWebsites', true);
	        $this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
	        $this->set("custSubMenu", "overview");
	        $this->render('user/userhome');
	        return;
	    }

	    $this->set('siteList', $websiteList);
	    $websiteId = isset($searchInfo['website_id']) ? intval($searchInfo['website_id']) : $websiteList[0]['id'];
	    $this->set('websiteId', $websiteId);

	    $fromTime = !empty($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-14 days'));
	    $toTime = !empty($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d');
	    $this->set('fromTime', $fromTime);
	    $this->set('toTime', $toTime);

		$this->set("custSubMenu", "overview");
		$this->render('user/userhome');
	}
	
	// $websiteId/$fromDate/$toDate come straight from $_GET in overview.php
	// with no sanitization at all - $websiteId was never checked against
	// the caller's own websites (getUserKeywordSearchEngineList() was
	// called with an empty userId, skipping its own access-scoping
	// entirely), and all three were echoed raw into a URL that
	// page_overview.ctp.php/keyword_overview.ctp.php then embed BOTH
	// inside an HTML attribute and inside a single-quoted inline <script>
	// string with no escaping either - a crafted from_time/to_time could
	// break out of either context. Fixed at the source here instead of
	// in the two view files, since both views build that same URL from
	// these three values.
	function showPageOverview($websiteId, $fromDate, $toDate) {
	    $userId = isLoggedIn();
	    $websiteId = $this->__sanitizeOverviewWebsiteId($websiteId);
	    $fromDate = $this->__sanitizeOverviewDate($fromDate, date('Y-m-d', strtotime('-14 days')));
	    $toDate = $this->__sanitizeOverviewDate($toDate, date('Y-m-d'));

	    $keywordController = new KeywordController();
	    $seLIst = $keywordController->getUserKeywordSearchEngineList($userId, $websiteId);

	    if (empty($seLIst)) {
	        showErrorMsg($_SESSION['text']['common']['No Records Found']);
	    }

	    $this->set("seList", $seLIst);
	    $pageOVUrl = SP_WEBPATH . "/$this->baseUrl?sec=page-overview-data&website_id=$websiteId&from_time=$fromDate&to_time=$toDate";
	    $this->set("pageOVUrl", $pageOVUrl);
	    $this->render('report/page_overview');
	}

	// a website_id that doesn't belong to this (non-admin) user, or isn't
	// numeric at all, is treated as "no website selected" rather than
	// silently querying someone else's data - see showPageOverview()'s
	// own comment above for the full IDOR this closes.
	function __sanitizeOverviewWebsiteId($websiteId) {
	    $websiteId = intval($websiteId);
	    if (empty($websiteId)) {
	        return 0;
	    }
	    include_once(SP_CTRLPATH . "/website.ctrl.php");
	    $websiteController = new WebsiteController();
	    return $websiteController->__verifyWebsiteOwnership($websiteId) ? $websiteId : 0;
	}

	// these dates only ever get embedded into a URL (never parameterized
	// SQL here), so anything other than a plain YYYY-MM-DD has no
	// legitimate reason to be in them - reject rather than attempt to
	// escape for whatever context the value eventually lands in.
	function __sanitizeOverviewDate($date, $default) {
	    return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) ? $date : $default;
	}

	function showPageOverviewData($seachInfo) {
	    $websiteId = $this->__sanitizeOverviewWebsiteId($seachInfo['website_id'] ?? '');
	    $seId = intval($seachInfo['se_id']);

	    $conditions = !empty($seachInfo['from_time']) ? " and sr.result_date>='".addslashes($seachInfo['from_time'])."'" : "";
	    $conditions .= !empty($seachInfo['to_time']) ? " and sr.result_date<='".addslashes($seachInfo['to_time'])."'" : "";
	    
	    $sql = "select distinct srd.url, sr.`rank`,sr.result_date, sr.keyword_id, k.name as keyword
	                    from searchresults sr, searchresultdetails srd, keywords k 
	                    where sr.id=srd.searchresult_id and sr.keyword_id=k.id and k.website_id=$websiteId and sr.searchengine_id=$seId $conditions
	                    order by `rank` asc, result_date DESC limit 0, 1000";
		
		$rList = $this->db->select($sql);
		$pageResultList = [];
		foreach ($rList as $rInfo) {
			if (!isset($pageResultList[$rInfo['url']])) {
				$pageResultList[$rInfo['url']] = $rInfo;
				if(count($pageResultList) > SP_PAGINGNO) {
					break;
				}
			}
		}

		$this->set("pageResultList", $pageResultList);
		$this->render('report/page_overview_data');
	}
	
	// same IDOR/reflected-XSS fix as showPageOverview() above
	function showKeywordOverview($websiteId, $fromDate, $toDate) {
	    $userId = isLoggedIn();
	    $websiteId = $this->__sanitizeOverviewWebsiteId($websiteId);
	    $fromDate = $this->__sanitizeOverviewDate($fromDate, date('Y-m-d', strtotime('-14 days')));
	    $toDate = $this->__sanitizeOverviewDate($toDate, date('Y-m-d'));

	    $keywordController = new KeywordController();
	    $seLIst = $keywordController->getUserKeywordSearchEngineList($userId, $websiteId);

	    if (empty($seLIst)) {
	        showErrorMsg($_SESSION['text']['common']['No Records Found']);
	    }

	    $this->set("seList", $seLIst);
	    $keywordOVUrl = SP_WEBPATH . "/$this->baseUrl?sec=keyword-overview-data&website_id=$websiteId&from_time=$fromDate&to_time=$toDate";
	    $this->set("keywordOVUrl", $keywordOVUrl);
	    $this->render('report/keyword_overview');
	}

	function showKeywordOverviewData($seachInfo) {
	    $websiteId = $this->__sanitizeOverviewWebsiteId($seachInfo['website_id'] ?? '');
	    $seId = intval($seachInfo['se_id']);
	    
	    $conditions = !empty($seachInfo['from_time']) ? " and sr.result_date>='".addslashes($seachInfo['from_time'])."'" : "";
	    $conditions .= !empty($seachInfo['to_time']) ? " and sr.result_date<='".addslashes($seachInfo['to_time'])."'" : "";
	    
	    $sql = "select distinct sr.keyword_id, srd.url, sr.`rank`,sr.result_date, k.name as keyword
                    from searchresults sr, searchresultdetails srd, keywords k
                    where sr.id=srd.searchresult_id and sr.keyword_id=k.id and k.website_id=$websiteId and sr.searchengine_id=$seId $conditions
                    order by `rank` asc, result_date DESC limit 0, 1000";
        
        $rList = $this->db->select($sql);
		$keywordResultList = [];
		foreach ($rList as $rInfo) {
			if (!isset($keywordResultList[$rInfo['keyword']])) {
				$keywordResultList[$rInfo['keyword']] = $rInfo;
				if(count($keywordResultList) > SP_PAGINGNO) {
					break;
				}
			}
		}        
	    
	    $this->set("keywordResultList", $keywordResultList);
	    $this->render('report/keyword_overview_data');
	}
	
}
?>