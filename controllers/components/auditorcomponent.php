<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   		   *
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
        $pageInfo = $spider->getPageInfo($reportUrl, $projectInfo['url'], true);

        if ($rInfo = $this->getReportInfo(" and project_id={$projectInfo['id']} and page_url='$reportUrl'") ) {
            
            $reportInfo['id'] = $rInfo['id'];
            $reportInfo['page_title'] = addslashes($pageInfo['page_title']);
            $reportInfo['page_description'] = addslashes($pageInfo['page_description']);
            $reportInfo['page_keywords'] = addslashes($pageInfo['page_keywords']);
            $reportInfo['total_links'] = $pageInfo['total_links'];
            $reportInfo['external_links'] = $pageInfo['external'];
            $reportInfo['crawled'] = 1;
        
            // gooogle pagerank check
            if ($projectInfo['check_pr']) {
                $rankCtrler = $this->createController('Rank');
                $rankInfo = $rankCtrler->__getMozRank(array($reportUrl));
                $reportInfo['pagerank'] = !empty($rankInfo[0]) ? $rankInfo[0] : 0;
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
                $reportInfo['brocken'] = Spider::isLInkBrocken($linkInfo['link_url']);    
            }
            
            $this->saveReportInfo($reportInfo, 'update');
            
            // to store sitelinks in page and links reports
            $i = 0;
            if (count($pageInfo['site_links']) > 0) {
            	
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
                if (count($pageInfo['external_links']) > 0) {
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
        
        // check page title length
        $lengTitle = strlen($reportInfo['page_title']);
        if ( ($lengTitle <= SA_TITLE_MAX_LENGTH) && ($lengTitle >= SA_TITLE_MIN_LENGTH) ) {
            $scoreInfo['page_title'] = 1;        
        } else {
            $scoreInfo['page_title'] = -1;
            $msg = $spTextSA["The page title length is not between"]." ".SA_TITLE_MIN_LENGTH." & ".SA_TITLE_MAX_LENGTH;
            $this->commentInfo['page_title'] = formatErrorMsg($msg, 'error', '');
        }
        
        // check meta description length
        $lengDes = strlen($reportInfo['page_description']);
        if ( ($lengDes <= SA_DES_MAX_LENGTH) && ($lengDes >= SA_DES_MIN_LENGTH) ) {
            $scoreInfo['page_description'] = 1;
        } else {
            $scoreInfo['page_description'] = -1;
            $msg = $spTextSA["The page description length is not between"]." ".SA_DES_MIN_LENGTH." and ".SA_DES_MAX_LENGTH;
            $this->commentInfo['page_description'] = formatErrorMsg($msg, 'error', '');
        }
        
        // check meta keywords length
        $lengKey = strlen($reportInfo['page_keywords']);
        if ( ($lengKey <= SA_KEY_MAX_LENGTH) && ($lengKey >= SA_KEY_MIN_LENGTH) ) {
            $scoreInfo['page_keywords'] = 1;
        } else {
            $scoreInfo['page_keywords'] = -1;
            $msg = $spTextSA["The page keywords length is not between"]." ".SA_KEY_MIN_LENGTH." and ".SA_KEY_MAX_LENGTH;
            $this->commentInfo['page_keywords'] = formatErrorMsg($msg, 'error', '');
        }
        
        // if link brocken
        if ($reportInfo['brocken']) {
            $scoreInfo['brocken'] = -1;
            $msg = $spTextSA["The page is brocken"];
            $this->commentInfo['brocken'] = formatErrorMsg($msg, 'error', '');
        }

        // if total links of a page
        if ($reportInfo['total_links'] >= SA_TOTAL_LINKS_MAX) {
            $scoreInfo['total_links'] = -1;
            $msg = $spTextSA["The total number of links in page is greater than"]." ".SA_TOTAL_LINKS_MAX;
            $this->commentInfo['page_keywords'] = formatErrorMsg($msg, 'error', '');
        }
        
        // check google pagerank
        if ($reportInfo['pagerank'] >= SA_PR_CHECK_LEVEL_SECOND) {
            $scoreInfo['pagerank'] = $reportInfo['pagerank'] * 3;
            $msg = $spTextSA["The page is having exellent pagerank"];
            $this->commentInfo['pagerank'] = formatSuccessMsg($msg);
        } else if ($reportInfo['pagerank'] >= SA_PR_CHECK_LEVEL_FIRST) {
            $scoreInfo['pagerank'] = $reportInfo['pagerank'] * 2;
            $msg = $spTextSA["The page is having very good pagerank"];
            $this->commentInfo['pagerank'] = formatSuccessMsg($msg);
        } else if ($reportInfo['pagerank']) {
            $scoreInfo['pagerank'] = 1;
            $msg = $spTextSA["The page is having good pagerank"];
            $this->commentInfo['pagerank'] = formatSuccessMsg($msg);
        } else {
            $scoreInfo['pagerank'] = 0;
            $msg = $spTextSA["The page is having poor pagerank"];
            $this->commentInfo['pagerank'] = formatErrorMsg($msg, 'error', '');
        }
        
        // check backlinks
        $seArr = array('google', 'bing');
        foreach ($seArr as $se) {
            $label = $se.'_backlinks';
            if ($reportInfo[$label] >= SA_BL_CHECK_LEVEL) {                
                $scoreInfo[$label] = 2;
                $msg = $spTextSA["The page is having exellent number of backlinks in"]." ".$se;
                $this->commentInfo[$label] = formatSuccessMsg($msg);
            } elseif($reportInfo[$label]) {
                $scoreInfo[$label] = 1;
                $msg = $spTextSA["The page is having good number of backlinks in"]." ".$se;
                $this->commentInfo[$label] = formatSuccessMsg($msg);                
            } else {
                $scoreInfo[$label] = 0;
                $msg = $spTextSA["The page is not having backlinks in"]." ".$se;
                $this->commentInfo[$label] = formatErrorMsg($msg, 'error', '');
            }     
        }
        
        // check whether indexed or not    
        foreach ($seArr as $se) {
            $label = $se.'_indexed';
            if($reportInfo[$label]) {
                $scoreInfo[$label] = 1;                
            } else {
                $scoreInfo[$label] = -1;
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