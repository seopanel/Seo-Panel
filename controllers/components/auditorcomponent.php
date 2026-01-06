<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   		   *
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

# class defines all site auditor controller functions
class AuditorComponent extends Controller{
    
    var $commentInfo = array(); // to store the details about the score of each page
    
    // function to save report info
    function saveReportInfo($reportInfo, $action='create') {
        if ($action == 'create') {
			$dateTime = date('Y-m-d H:i:s');
            $reportKeys = array_keys($reportInfo);
            $reportValues = array_values($reportInfo);
            $sql = "insert into auditorreports(".implode(',', $reportKeys).", updated) values('".implode("','", $reportValues)."', '$dateTime')";
        } elseif($action == 'update') {
            $sql = "Update auditorreports set ";
            foreach ($reportInfo as $col => $value) {
                if ($col != 'id') {
                    $sql .= "$col='$value',";    
                }
            }
            $sql = preg_replace('/\,$/', '', $sql);
            $sql .= " where id=".$reportInfo['id'];
        }
        $this->db->query($sql);
    }
    
    // func to run report for a project
    function runReport($reportUrl, $projectInfo, $totalLinks) {        
        $spider = new Spider();
        
        if ($rInfo = $this->getReportInfo(" and project_id={$projectInfo['id']} and page_url='$reportUrl'") ) {        	
        	$pageInfo = $spider->getPageInfo($reportUrl, $projectInfo['url'], true);
        	
            // handle redirects
            if(!empty($spider->effectiveUrl)) {
                $effectiveUrl = rtrim($spider->effectiveUrl, '/'); //remove trailing slash
                $reportId = $rInfo['id'];

                if ($effectiveUrl != $reportUrl){ //redirect occurred. Could be simply www vs. no www
					$parse = parse_url($effectiveUrl);
					$effectiveDomain = str_replace("www.", '', $parse['host']);
                  	$parse = parse_url($projectInfo['url']);
                  	$projectDomain = str_replace("www.", '', $parse['host']);
                  
                  	if ($effectiveDomain == $projectDomain) { //still on same domain
                  	
                      	// check if we already have an entry for the effective URL
						if ($rInfoForEffectiveUrl = $this->getReportInfo(" and project_id={$projectInfo['id']} and page_url='$effectiveUrl'")){

							// If we already have an entry then we can delete this new one and not continue running tests on it as it's a duplicate 
							$this->db->query("delete from auditorreports where id=$reportId");
                        
                        	// if already existing effective url is not crawled, continue with the page crawl details and save
	                        if ($rInfoForEffectiveUrl['crawled'] == 0) {                        	
							$rInfo = $rInfoForEffectiveUrl;
	                        	$reportId = $rInfo['id'];
	                        } else {
	                        	return $effectiveUrl; //Redirected to existing URL
	                        }
                        
						} else { //if we don't already have an entry, update this one
                        	$this->db->query("update auditorreports set page_url='$effectiveUrl' where id=$reportId");
                        	$reportUrl = $effectiveUrl;
                      	}
                      
					} else { //external link -- delete it from report                  	
						$this->db->query("delete from auditorreports where id=$reportId");
                    	return "Error: External Link Found";
					}
                  
				}
            }
            
            $reportInfo['id'] = $rInfo['id'];
            $reportInfo['page_title'] = addslashes($pageInfo['page_title']);
            $reportInfo['page_description'] = addslashes($pageInfo['page_description']);
            $reportInfo['page_keywords'] = addslashes($pageInfo['page_keywords']);
            $reportInfo['total_links'] = intval($pageInfo['total_links']);
            $reportInfo['external_links'] = intval($pageInfo['external']);
            $reportInfo['crawled'] = 1;
        
            // gooogle pagerank check
            if ($projectInfo['check_pr']) {
            	$mozCtrler = $this->createController('Moz');
            	$mozRankList = $mozCtrler->__getMozRankInfo(array($reportUrl));
            	$reportInfo['spam_score'] = !empty($mozRankList[0]['spam_score']) ? $mozRankList[0]['spam_score'] : 0;
            	$reportInfo['page_authority'] = !empty($mozRankList[0]['page_authority']) ? $mozRankList[0]['page_authority'] : 0;
            }
            
            // backlinks page check
            if ($projectInfo['check_backlinks']) {
                $backlinkCtrler = $this->createController('Backlink');
                $backlinkCtrler->url = Spider::addTrailingSlash($reportUrl);
                $reportInfo['bing_backlinks'] = $backlinkCtrler->__getBacklinks('msn');
                $reportInfo['google_backlinks'] = $backlinkCtrler->__getBacklinks('google');
            }
            
            // indexed page check
            if ($projectInfo['check_indexed']) {
                $saturationCtrler = $this->createController('SaturationChecker');
                $saturationCtrler->url = Spider::addTrailingSlash($reportUrl);
                $reportInfo['bing_indexed'] = $saturationCtrler->__getSaturationRank('msn');
                $reportInfo['google_indexed'] = $saturationCtrler->__getSaturationRank('google');
            }
        
            if ($projectInfo['check_brocken']) {
                $reportInfo['brocken'] = Spider::isLInkBrocken($projectInfo['url']);    
            }
            
            $this->saveReportInfo($reportInfo, 'update');
            
            // to store sitelinks in page and links reports
            $i = 0;
            if (!empty($pageInfo['site_links']) && count($pageInfo['site_links']) > 0) {
            	
            	// loo through site links
                foreach ($pageInfo['site_links'] as $linkInfo) {
                    // if store links 
                    if ($projectInfo['store_links_in_page']) {
                        $delete = $i++ ? false : true;
                        $linkInfo['report_id'] = $rInfo['id'];
                        $this->storePagelLinks($linkInfo, $delete);
                    }
                    
                    // if total links saved less than max links allowed for a project
                    if ($totalLinks < $projectInfo['max_links']) { 
                    	
                    	// check whether valid html serving link
                        if(preg_match('/\.zip$|\.gz$|\.tar$|\.png$|\.jpg$|\.jpeg$|\.gif$|\.mp3$|\.flv$|\.pdf$|\.m4a$|#$/i', $linkInfo['link_url'])) continue;
                        
                        // if found any space in the link
                        $linkInfo['link_url'] = Spider::formatUrl($linkInfo['link_url']);
                        if (!preg_match('/\S+/', $linkInfo['link_url'])) continue;                        
                        
                        // check whether url needs to be excluded
                        if ($this->isExcludeLink($linkInfo['link_url'], $projectInfo['exclude_links'])) continue;
                        
                        // save links for the project report
                        if (!$this->getReportInfo(" and project_id={$projectInfo['id']} and page_url='{$linkInfo['link_url']}'")) {
        		            $repInfo['page_url'] = $linkInfo['link_url'];
        		            $repInfo['project_id'] = $projectInfo['id'];
        		            $this->saveReportInfo($repInfo);
        		            $totalLinks++;            
                        }
                    }
                }
            }
            
            // to store external links in page
            if ($projectInfo['store_links_in_page']) {
                if (!empty($pageInfo['site_links']) && count($pageInfo['external_links']) > 0) {
                    foreach ($pageInfo['external_links'] as $linkInfo) {
                        $delete = $i++ ? false : true;
                        $linkInfo['report_id'] = $rInfo['id'];
                        $linkInfo['extrenal'] = 1;
                        $this->storePagelLinks($linkInfo, $delete);
                    }
                }                
            }

            // calculate score of each page and update it
            $this->updateReportPageScore($rInfo['id']);
            
            // calculate score of each page and update it
            $this->updateProjectPageScore($projectInfo['id']);
        }
        
        return $reportUrl;                 
    }
    
    // function to get report info
    function getReportInfo($where) {	    
	    $sql = "SELECT * FROM auditorreports where 1=1 $where";
		$listInfo = $this->db->select($sql, true);
		return empty($listInfo['id']) ? false : $listInfo;
	}
    
    // function to store link of page
    function storePagelLinks($linkInfo, $delete=false) {
        
        foreach ($linkInfo as $col => $val) {
            $linkInfo[$col] = addslashes($val);
        }
        
        if ($delete) {
            $sql = "Delete from auditorpagelinks where report_id=".$linkInfo['report_id'];
            $this->db->query($sql);
        }
        
        $linkKeys = array_keys($linkInfo);
        $linkValues = array_values($linkInfo);
        $sql = "insert into auditorpagelinks(".implode(',', $linkKeys).") values('".implode("','", $linkValues)."')";
        $this->db->query($sql);
    }
    
    // function to check whether link should be excluded or not
    function isExcludeLink($link, $excludeList) {
        $exclude =  false;
        if (!empty($excludeList)) {
            $excludeList = explode(',', $excludeList);
            foreach ($excludeList as $exUrl) {
                if (stristr($link, trim($exUrl))) {
                    $exclude = true;
                    break;    
                }    
            }
        }
        return $exclude;
    }
    
    // function to find the score of a report page
    function updateReportPageScore($reportId) {
        $reportInfo = $this->getReportInfo(" and id=$reportId");
        $scoreInfo = $this->countReportPageScore($reportInfo);
        $score =  array_sum($scoreInfo);
        $sql = "update auditorreports set score=$score where id=$reportId";
        $this->db->query($sql);
    }
    
    // function to count report page score
    function countReportPageScore($reportInfo) {
        $scoreInfo = array();
        $this->commentInfo = array();
        $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);

        // check page title length (Modern SEO: 50-60 characters for optimal display)
        $lengTitle = strlen($reportInfo['page_title']);
        if ( ($lengTitle <= 60) && ($lengTitle >= 50) ) {
            $scoreInfo['page_title'] = 2; // Increased weight for proper title optimization
            $msg = $spTextSA["The page title length is optimal for search engines"];
            $this->commentInfo['page_title'] = formatSuccessMsg($msg);
        } else if ( ($lengTitle <= SA_TITLE_MAX_LENGTH) && ($lengTitle >= 30) ) {
            $scoreInfo['page_title'] = 1; // Acceptable but not optimal
            $msg = $spTextSA["The page title length is acceptable"];
            $this->commentInfo['page_title'] = formatSuccessMsg($msg);
        } else {
            $scoreInfo['page_title'] = -2; // Higher penalty for poor title optimization
            $msg = $spTextSA["The page title length is not optimal (recommended: 50-60 characters)"];
            $this->commentInfo['page_title'] = formatErrorMsg($msg, 'error', '');
        }

        // check meta description length (Modern SEO: 150-160 characters)
        $lengDes = strlen($reportInfo['page_description']);
        if ( ($lengDes <= 160) && ($lengDes >= 150) ) {
            $scoreInfo['page_description'] = 2; // Optimal length
            $msg = $spTextSA["The page description length is optimal for search engines"];
            $this->commentInfo['page_description'] = formatSuccessMsg($msg);
        } else if ( ($lengDes <= SA_DES_MAX_LENGTH) && ($lengDes >= 120) ) {
            $scoreInfo['page_description'] = 1; // Acceptable
            $msg = $spTextSA["The page description length is acceptable"];
            $this->commentInfo['page_description'] = formatSuccessMsg($msg);
        } else {
            $scoreInfo['page_description'] = -1;
            $msg = $spTextSA["The page description length is not optimal (recommended: 150-160 characters)"];
            $this->commentInfo['page_description'] = formatErrorMsg($msg, 'error', '');
        }

        // Meta keywords check removed - Google ignores meta keywords since 2009
        // Keeping minimal check for presence only, not scoring
        if (!empty($reportInfo['page_keywords'])) {
            $scoreInfo['page_keywords'] = 0; // Neutral - no impact on modern SEO
        }

        // if link broken (critical issue in modern SEO)
        if ($reportInfo['brocken']) {
            $scoreInfo['brocken'] = -3; // Higher penalty for broken links
            $msg = $spTextSA["The page is broken - critical SEO issue"];
            $this->commentInfo['brocken'] = formatErrorMsg($msg, 'error', '');
        }

        // if total links of a page (modern recommendation: 100-150 links max)
        if ($reportInfo['total_links'] >= 150) {
            $scoreInfo['total_links'] = -2;
            $msg = $spTextSA["The total number of links in page is too high (recommended: less than 150)"];
            $this->commentInfo['total_links'] = formatErrorMsg($msg, 'error', '');
        } else if ($reportInfo['total_links'] >= 100) {
            $scoreInfo['total_links'] = -1;
            $msg = $spTextSA["The total number of links in page is slightly high"];
            $this->commentInfo['total_links'] = formatErrorMsg($msg, 'warning', '');
        }

        // PageRank removed - deprecated since 2013, no longer used in scoring

        // Page Authority - increased importance in modern SEO
        if ($reportInfo['page_authority'] >= SA_PA_CHECK_LEVEL_SECOND) {
        	$scoreInfo['page_authority'] = 10; // Increased from 6 (most important metric)
        	$msg = $spTextSA["The page is having excellent page authority value"];
        	$this->commentInfo['page_authority'] = formatSuccessMsg($msg);
        } else if ($reportInfo['page_authority'] >= SA_PA_CHECK_LEVEL_FIRST) {
        	$scoreInfo['page_authority'] = 5; // Increased from 3
        	$msg = $spTextSA["The page is having very good page authority value"];
        	$this->commentInfo['page_authority'] = formatSuccessMsg($msg);
        } else if ($reportInfo['page_authority'] >= 20) {
        	$scoreInfo['page_authority'] = 2; // New tier for moderate PA
        	$msg = $spTextSA["The page is having moderate page authority value"];
        	$this->commentInfo['page_authority'] = formatSuccessMsg($msg);
        } else if ($reportInfo['page_authority']) {
        	$scoreInfo['page_authority'] = 1;
        	$msg = $spTextSA["The page is having low page authority value"];
        	$this->commentInfo['page_authority'] = formatErrorMsg($msg, 'warning', '');
        } else {
        	$scoreInfo['page_authority'] = -1;
        	$msg = $spTextSA["The page is having no page authority value"];
        	$this->commentInfo['page_authority'] = formatErrorMsg($msg, 'error', '');
        }

        // check backlinks (important for modern SEO)
        if ($reportInfo['google_backlinks'] >= SA_BL_CHECK_LEVEL) {
            $scoreInfo['google_backlinks'] = 3; // Increased from 2
            $msg = $spTextSA["The page is having excellent number of backlinks"];
            $this->commentInfo['google_backlinks'] = formatSuccessMsg($msg);
        } elseif($reportInfo['google_backlinks'] >= 10) {
            $scoreInfo['google_backlinks'] = 2; // New tier
            $msg = $spTextSA["The page is having good number of backlinks"];
            $this->commentInfo['google_backlinks'] = formatSuccessMsg($msg);
        } elseif($reportInfo['google_backlinks']) {
            $scoreInfo['google_backlinks'] = 1;
            $msg = $spTextSA["The page is having some backlinks"];
            $this->commentInfo['google_backlinks'] = formatSuccessMsg($msg);
        } else {
            $scoreInfo['google_backlinks'] = 0; // Neutral - not a penalty
            $msg = $spTextSA["The page has no backlinks yet"];
            $this->commentInfo['google_backlinks'] = formatErrorMsg($msg, 'warning', '');
        }

        // check whether indexed or not (critical for visibility)
        $seArr = array('google', 'bing');
        foreach ($seArr as $se) {
            $label = $se.'_indexed';
            if($reportInfo[$label]) {
                $scoreInfo[$label] = 2; // Increased from 1 (being indexed is critical)
            } else {
                $scoreInfo[$label] = -2; // Higher penalty for not being indexed
                $msg = $spTextSA["The page is not indexed in"]." ".$se;
                $this->commentInfo[$label] = formatErrorMsg($msg, 'error', '');
            }
        }

        return $scoreInfo;
    }
    
    // function to find the score of a project
    function updateProjectPageScore($projectId) {        
        $sql = "select sum(score)/count(*) as avgscore from auditorreports where crawled=1 and project_id=$projectId";
        $listInfo = $this->db->select($sql, true);
		$score = empty($listInfo['avgscore']) ? 0 : $listInfo['avgscore'];
        
        $sql = "update auditorprojects set score=$score where id=$projectId";
        $this->db->query($sql); 
    }
    
    // function to get all links of a page
    function getAllLinksPage($reportId) {
        $sql = "select * from auditorpagelinks where report_id=$reportId";
        $linkList = $this->db->select($sql);
        return $linkList;        
    }
    
    // function to get duplicate meta contents info
    function getDuplicateMetaInfoCount($projectId, $col='page_title', $statusCheck=false, $statusVal=1) {
        $crawled = $statusCheck ? " and crawled=$statusVal" : "";
        $sql = "select $col,count(*) as count from auditorreports where project_id=$projectId and $col!='' $crawled group by $col having count>1";
        $list = $this->db->select($sql);
        $total = 0;
        foreach ($list as $info) {
            $total++;
        }
        return $total;
    }
    
    // function to get all report pages of a project
    function getAllreportPages($where='', $cols='*') {        	    
	    $sql = "SELECT $cols FROM auditorreports where 1=1 $where";
		$list = $this->db->select($sql);
		return $list;
    }
    
}