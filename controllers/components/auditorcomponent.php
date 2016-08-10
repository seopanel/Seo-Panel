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
        global $sp_db;
        if(!is_array($reportInfo)){
            return FALSE;
        }
        $data = array();
        $insert_fields = array('project_id','page_url','page_title','page_description','page_keywords','pagerank','google_backlinks','bing_backlinks',
            'google_indexed','bing_indexed','total_links','external_links','crawled','brocken','score');
        foreach($insert_fields as $field){
            if(key_exists($field, $reportInfo)){
                $data[$field] = $reportInfo[$field];
            }
        }
        $data['updated'] = date('Y-m-d H:i:s');
        $data = apply_filters('save_report_data', $data, $reportInfo, $action);
        if ($action == 'create') {
//            $reportKeys = array_keys($reportInfo);
//            $reportValues = array_values($reportInfo);
//            $sql = "insert into auditorreports(".implode(',', $reportKeys).", updated) values('".implode("','", $reportValues)."', '$dateTime')";
            $id = $sp_db->insert('auditorreports',$data);
        } elseif($action == 'update') {
            $sp_db->where('id',$reportInfo['id']);
            $id = $sp_db->update('auditorreports',$data);
//            $sql = "Update auditorreports set ";
//            foreach ($reportInfo as $col => $value) {
//                if ($col != 'id') {
//                    $sql .= "$col='$value',";    
//                }
//            }
//            $sql = preg_replace('/\,$/', '', $sql);
//            $sql .= " where id=".$reportInfo['id'];
        }
        if($id !== FALSE){
            do_action('save_report',$reportInfo,$action);
            return $id;
        }else{
            return FALSE;
        }
        //$this->db->query($sql);
    }
    
    // func to run report for a project
    function runReport($reportUrl, $projectInfo, $totalLinks) {        
        
        $reportInfo = array();
        $spider = new Spider();
        if ($projectInfo['check_brocken']) {
            $reportInfo['brocken'] = $spider->isLInkBrocken($reportUrl);    
        }
            
        if($reportInfo['brocken']){
            $pageInfo = array('page_title' => '','page_description' => '','page_keywords' => '',
            'total_links' => 0,'external' => 0,'brocken'=>TRUE);
            $pageInfo = apply_filters('page_broken', $pageInfo);
        }else{
            $pageInfo = $spider->getPageInfo($reportUrl, $projectInfo['url'], true);
        }
        
        $where = array('project_id'=>$projectInfo['id'],'page_url'=>$reportUrl);
        $rInfo = $this->getReportInfo($where);
        if ($rInfo) {
            
            $reportInfo['id'] = $rInfo['id'];
            $reportInfo['page_title'] = $pageInfo['page_title'];
            $reportInfo['page_description'] = $pageInfo['page_description'];
            $reportInfo['page_keywords'] = $pageInfo['page_keywords'];
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
        
            $reportInfo = apply_filters('run_report_info', $reportInfo, $pageInfo);

            $this->saveReportInfo($reportInfo, 'update');
            
            // to store sitelinks in page and links reports
            $i = 0;
            if (count($pageInfo['site_links']) > 0) {
            	
            	// loo through site links
                foreach ($pageInfo['site_links'] as $linkInfo) {
                    if ($projectInfo['check_brocken']) {
                        $linkInfo['brocken'] = Spider::isLInkBrocken($linkInfo['link_url']);
                    }
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
                        //$linkInfo['link_url'] = Spider::formatUrl($linkInfo['link_url']);
                        if (!preg_match('/\S+/', $linkInfo['link_url'])) continue;                        
                       
                        // check whether url needs to be excluded
                        if ($this->isExcludeLink($linkInfo['link_url'], $projectInfo['exclude_links'])) continue;
                        
                        // save links for the project report
                        $where = array('project_id'=>$projectInfo['id'],'page_url'=>$linkInfo['link_url']);
                        $r_exists = $this->getReportInfo($where);
                        //if (!$this->getReportInfo(" and project_id={$projectInfo['id']} and page_url='{$linkInfo['link_url']}'")) {
                        if (!$r_exists) {
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
        global $sp_db;
        if(is_array($where)){
            foreach($where as $k => $v){
                $sp_db->where($k,$v);
            }
            $ret = $sp_db->getOne('auditorreports');
            if(!empty($ret['id'])){
                return $ret;
            }else{
                return FALSE;
            }
        //For legacy accept a string as input
        }else if (is_string($where)){
            $sql = "SELECT * FROM auditorreports where 1=1 $where";
            $listInfo = $this->db->select($sql, true);
            return empty($listInfo['id']) ? false : $listInfo;
        //For legacy if $where is empty return all reports
        }else if(empty ($where)){
            $ret = $sp_db->get('auditorreports');
            if(count($ret) > 0){
                return $ret;
            }else{
                return FALSE;
            }
        }else{
            return false;
        }
    }

    // function to store link of page
    function storePagelLinks($linkInfo, $delete=false) {
        global $sp_db;
//        foreach ($linkInfo as $col => $val) {
//            $linkInfo[$col] = addslashes($val);
//        }
        
        if ($delete) {
            $sp_db->where('report_id',$linkInfo['report_id']);
            $sp_db->delete('auditorpagelinks');
            //$sql = "Delete from auditorpagelinks where report_id=".$linkInfo['report_id'];
            //$this->db->query($sql);
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
        global $sp_db;
        //$reportInfo = $this->getReportInfo(" and id=$reportId");
        $reportInfo = $this->getReportInfo(array('id' => $reportId));
        $scoreInfo = $this->countReportPageScore($reportInfo);
        $score = $this->weightedScore($scoreInfo);
        //$score =  array_sum($scoreInfo);
        //$sql = "update auditorreports set score=$score where id=$reportId";
        //$this->db->query($sql);
        $sp_db->where('id', $reportId);
        $data = array('score'=>$score);
        $sp_db->update('auditorreports',$data);
        do_action('report_score_updated',$score,$reportInfo);
    }
    
    function weightedScore($scoreInfo,$type = 'reports'){
        global $scores;
        $total_points = 0;
        $total_weight = 0;
        foreach($scoreInfo as $k => $v){
            if(isset($scores[$type][$k]) && is_subclass_of($scores[$type][$k],'Score',FALSE)){
                $total_weight += $scores[$type][$k]->get_weight();
                $score += $scores[$type][$k]->get_weight() * $v;
            }
        }
        $score = $total_points/$total_weight*10;
        return $score;
    }
    
    function countScore($targetInfo,$type = 'reports'){
        global $scores;
        $scoreData = array('scoreInfo' => array(),'commentInfo' => array());
        foreach($scores[$type] as $k => $v){
            if($v->is_active()){
                $temp = $v->calc_score($scoreData,$targetInfo);
                if(isset($temp) && is_array($temp)){
                    if(isset($temp['scoreInfo'])){
                        if(is_numeric($temp['scoreInfo'])){
                            if($temp['scoreInfo'] > 1){
                                $temp['scoreInfo'] = 1;
                            }
                            if($temp['scoreInfo'] < -1){
                                $temp['scoreInfo'] = -1;
                            }
                            $scoreData['scoreInfo'][$k] = $temp['scoreInfo'];
                            if(isset($temp['commentInfo'])){
                                 $scoreData['commentInfo'][$k] = $temp['commentInfo'];
                            }
                        }
                    }
                }
            }
        }
        
        $filter_info = apply_filters($type.'_page_score', $scoreData,$targetInfo);
        return $filter_info;
    }


    // function to count report page score
    function countReportPageScore($reportInfo) {
        $calcScore = $this->countScore($reportInfo);
        $this->commentInfo = $calcScore['commentInfo'];
        return $calcScore['scoreInfo'];
    }
    
    // function to find the score of a project
    function updateProjectPageScore($projectId) {
        global $sp_db;
        $projectInfo = getProjectInfo($projectId);
        $calcScore = $this->countScore($projectInfo,"website");
        $weighted = $this->weightedScore($calcScore['scoreInfo'],"website");
        if(empty($weighted)){
            $data = array('score'=>0);
        }else{
            $data = array('score'=>$weighted);
        }
        $sp_db->where('id',$projectId);
        $sp_db->update('auditorprojects',$data);
//        $sql = "update auditorprojects set score=$score where id=$projectId";
//        $this->db->query($sql); 
    }
    
    // function to get all links of a page
    function getAllLinksPage($reportId) {
        global $sp_db;
        $sp_db->where('report_id',$reportId);
        $linkList = $sp_db->get('auditorpagelinks');
//        $sql = "select * from auditorpagelinks where report_id=$reportId";
//        $linkList = $this->db->select($sql);
        return $linkList;        
    }
    
    // function to get duplicate meta contents info
    function getDuplicateMetaInfoCount($projectId, $col='page_title', $statusCheck=false, $statusVal=1) {
        global $sp_db;
        $sp_db->where('project_id',$projectId);
        $sp_db->where($col." !=''");
        if($statusCheck){
            $sp_db->where('crawled',$statusVal);
        }
        $sp_db->groupBy ($col);
        $sp_db->having('count > 1');
        $cols = array($col,'count(*) as count');
        $list = $sp_db->get('auditorreports',null, $cols);
//        $crawled = $statusCheck ? " and crawled=$statusVal" : "";
//        $sql = "select $col,count(*) as count from auditorreports where project_id=$projectId and $col!='' $crawled group by $col having count>1";
//        $list = $this->db->select($sql);
        $total = 0;
        foreach ($list as $info) {
            $total++;
        }
        return $total;
    }
    
    // function to get all report pages of a project
    function getAllreportPages($where='', $cols='*') {     
        global $sp_db;    
        if(is_array($where)){
                foreach ($where as $k => $v){
                    $sp_db->where($k,$v);
                }
            }
            $list = $sp_db->get('auditorreports',NULL,$cols);
//	    $sql = "SELECT $cols FROM auditorreports where 1=1 $where";
//		$list = $this->db->select($sql);
		return $list;
    }
    
}