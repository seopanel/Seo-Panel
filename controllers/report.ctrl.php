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

# class defines all report controller functions
class ReportController extends Controller {
	var $seLIst;
	var $showAll = false;
	var $proxyCheckCount = 1;

	# func to get keyword report summary
	function __getKeywordSearchReport($keywordId, $fromTime, $toTime, $apiCall = false){
		$positionInfo = array();
		
		if(empty($this->seLIst)){
			$seController = New SearchEngineController();
			$this->seLIst = $seController->__getAllSearchEngines();
		}
		
		$fromTimeLabel = date('Y-m-d', $fromTime);
		$toTimeLabel = date('Y-m-d', $toTime);
		foreach($this->seLIst as $seInfo){
			$sql = "select min(`rank`) as `rank`,result_date from searchresults 
			where keyword_id=$keywordId and searchengine_id=".$seInfo['id']."
			and (result_date='$fromTimeLabel' or result_date='$toTimeLabel')
			group by result_date order by result_date DESC limit 0, 2";
			$reportList = $this->db->select($sql);
			$reportList = array_reverse($reportList);
			
			$prevRank = 0;
			$i = 0;
			foreach ($reportList as $key => $repInfo) {
				$rankDiff = '';
				if ($i > 0) {
					$rankDiff = $prevRank - $repInfo['rank'];
					if ($rankDiff > 0) {
						$rankDiff = $apiCall ? $rankDiff : "<font class='green'>($rankDiff)</font>";
					} elseif ($rankDiff < 0) {
						$rankDiff = $apiCall ? $rankDiff : "<font class='red'>($rankDiff)</font>";
					}
				}
				$positionInfo[$seInfo['id']]['rank_diff'] = empty ($rankDiff) ? '' : $rankDiff;
				$positionInfo[$seInfo['id']]['rank'] = $repInfo['rank'];
				$positionInfo[$seInfo['id']][$repInfo['result_date']] = $repInfo['rank'];
				$prevRank = $repInfo['rank'];
				$i++;
			}			
		}
				
		return $positionInfo;
	}
	

	# func to show keyword report summary
	function showKeywordReportSummary($searchInfo = '') {
		
		$userId = isLoggedIn();
		$keywordController = New KeywordController();
		$exportVersion = false;
		switch($searchInfo['doc_type']){
						
			case "export":
				$exportVersion = true;
				$exportContent = "";
				break;
			
			case "pdf":
				$this->set('pdfVersion', true);
				break;
			
			case "print":
				$this->set('printVersion', true);
				break;
		}
		
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else { 
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		}
		
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 00:00:00');
		} else {
			$toTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		}
		
		$fromTimeTxt = date('Y-m-d', $fromTime);		
		$toTimeTxt = date('Y-m-d', $toTime);
		$this->set('fromTime', $fromTimeTxt);
		$this->set('toTime', $toTimeTxt);
		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = isset($searchInfo['website_id']) ? $searchInfo['website_id'] : $websiteList[0]['id'];
		$websiteId = intval($websiteId);
		$this->set('websiteId', $websiteId);
		
		$websiteUrl = "";
		foreach ($websiteList as $websiteInfo) {
		    if ($websiteInfo['id'] == $websiteId) {
		        $websiteUrl = $websiteInfo['url'];
		        break;    
		    }
		}
		$this->set('websiteUrl', $websiteUrl);
		
		$seSearchUserId = isAdmin() ? "" : $userId;
		$this->seLIst = $keywordController->getUserKeywordSearchEngineList($seSearchUserId);
		$this->set('seList', $this->seLIst);
		
		// to find order col
        if (!empty($searchInfo['order_col'])) {
		    $orderCol = $searchInfo['order_col'];
		    $orderVal = getOrderByVal($searchInfo['order_val']);
		} else {
		    $orderCol = $this->seLIst[array_keys($this->seLIst)[0]]['id'];
		    $orderVal = 'ASC';    
		}
		
		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);
		$scriptPath = SP_WEBPATH."/reports.php?sec=reportsum&website_id=$websiteId";
		$scriptPath .= "&from_time=$fromTimeTxt&to_time=$toTimeTxt&search_name=" . $searchInfo['search_name'];
		$scriptPath .= "&order_col=$orderCol&order_val=$orderVal";
			
		$conditions = " and w.status=1 and k.status=1";
		$conditions .= isAdmin() ? "" : $websiteController->getWebsiteUserAccessCondition($userId);
		$conditions .= !empty($websiteId) ? " and w.id=$websiteId" : "";
		$conditions .= !empty($searchInfo['search_name']) ? " and k.name like '%".addslashes($searchInfo['search_name'])."%'" : "";
					
		$subSql = "select [col] from keywords k,searchresults r, websites w 
		where k.id=r.keyword_id and k.website_id=w.id $conditions
		and r.searchengine_id=".intval($orderCol)." and r.result_date='" . addslashes($toTimeTxt) . "'
		group by k.id";

		$unionOrderCol = ($orderCol == "keyword") ? "name" : "`rank`";
		$sql = "(". str_replace("[col]", "k.id,k.name,min(`rank`) `rank`,w.name website,w.url weburl,w.name webname", $subSql) .") 
		UNION 
		(select k.id,k.name,1000,w.name website,w.url weburl,w.name webname 
		from keywords k, websites w  
		where w.id=k.website_id $conditions and k.id not in
		(". str_replace("[col]", "distinct(k.id)", $subSql) ."))
		order by $unionOrderCol $orderVal";
		
		if ($unionOrderCol != 'name') $sql .= ", name";
		
		# pagination setup
		$this->db->query($sql, true);
		$this->paging->setDivClass('pagingdiv');
		$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
		$pagingDiv = $this->paging->printPages($scriptPath, '', 'scriptDoLoad', 'content', "");
		$this->set('pagingDiv', $pagingDiv);
		$this->set('pageNo', $searchInfo['pageno']);
		
		if (!in_array($searchInfo['doc_type'], array("pdf", "export"))) {
			$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		}
			
		# set keywords list
		$list = $this->db->select($sql);
				
		$indexList = array();
		foreach($list as $keywordInfo){
			$positionInfo = $this->__getKeywordSearchReport($keywordInfo['id'], $fromTime, $toTime, true);
			
			// check whether the sorting search engine is there
		    $indexList[$keywordInfo['id']] = empty($positionInfo[$orderCol][$toTimeTxt]) ? 10000 : $positionInfo[$orderCol][$toTimeTxt];
		    
			$keywordInfo['position_info'] = $positionInfo;
			$keywordList[$keywordInfo['id']] = $keywordInfo;
		}		
		
		// sort array according the value
		if ($orderCol != 'keyword') { 
    		if ($orderVal == 'DESC') {
    		    arsort($indexList);
    		} else {
    		    asort($indexList);
    		}
		}
		$this->set('indexList', $indexList);

		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$reportHeading =  $this->spTextTools['Keyword Position Summary']."(".date('Y-m-d', $fromTime)." - ".date('Y-m-d', $toTime).")";
			$exportContent .= createExportContent( array('', $reportHeading, ''));
			$exportContent .= createExportContent( array());
			$headList = array($spText['common']['Website'], $spText['common']['Keyword']);

			$pTxt = str_replace("-", "/", substr($fromTimeTxt, -5));
			$cTxt = str_replace("-", "/", substr($toTimeTxt, -5));
			foreach ($this->seLIst as $seInfo) {
				$domainTxt = str_replace("www.", "", $seInfo['domain']);
				$headList[] = $domainTxt . "($cTxt)";
				$headList[] = $domainTxt . "($pTxt)";
				$headList[] = $domainTxt . "(+/-)";
			}
						
			$exportContent .= createExportContent( $headList);
			foreach($indexList as $keywordId => $rankValue){
			    $listInfo = $keywordList[$keywordId];
				$positionInfo = $listInfo['position_info'];
				
				$valueList = array($listInfo['weburl'], $listInfo['name']);
				foreach ($this->seLIst as $index => $seInfo){
					
					$rankInfo = $positionInfo[$seInfo['id']];
					$prevRank = isset($rankInfo[$fromTimeTxt]) ? $rankInfo[$fromTimeTxt] : "";
					$currRank = isset($rankInfo[$toTimeTxt]) ? $rankInfo[$toTimeTxt] : "";
					$rankDiff = "";
						
					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $prevRank - $currRank;
					}
					
					$valueList[] = $currRank;
					$valueList[] = $prevRank;
					$valueList[] = $rankDiff;
				}
				
				$exportContent .= createExportContent( $valueList);
			}
			exportToCsv('keyword_report_summary', $exportContent);
		} else {
			$this->set('list', $keywordList);
			
			// if pdf export
			if ($searchInfo['doc_type'] == "pdf") {
				exportToPdf($this->getViewContent('report/reportsummary'), "keyword_report_summary_$fromTimeTxt-$toTimeTxt.pdf");
			} else {
				$this->set('searchInfo', $searchInfo);
				$this->render('report/reportsummary');
			}	
		}		
	}
	
	# func to show reports
	function showReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
		}
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 23:59:59');
		} else {
			$toTime = time();
		}
		
		$fromTimeDate = date('Y-m-d', $fromTime);
		$toTimeDate = date('Y-m-d', $toTime);
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
		
		$keywordController = New KeywordController();
		if(!empty($searchInfo['keyword_id']) && !empty($searchInfo['rep'])){
			
			$searchInfo['keyword_id'] = intval($searchInfo['keyword_id']);
			$keywordInfo = $keywordController->__getKeywordInfo($searchInfo['keyword_id']);
			$searchInfo['website_id'] = $keywordInfo['website_id'];
		}

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? $seList[0]['id'] : intval($searchInfo['se_id']);
		$this->set('seId', $seId);
		$this->set('seInfo', $seController->__getsearchEngineInfo($seId));

		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$conditions .= empty ($seId) ? "" : " and s.searchengine_id=$seId";
		$sql = "select s.*,sd.url,sd.title,sd.description from searchresults s,searchresultdetails sd 
		where s.id=sd.searchresult_id and result_date>='$fromTimeDate' and result_date<='$toTimeDate' $conditions  
		order by s.result_date";
		$repList = $this->db->select($sql);

		$reportList = array ();
		foreach ($repList as $repInfo) {
			$var = 'se' . $seId . $repInfo['keyword_id'] . $repInfo['result_date'];
			if (empty ($reportList[$var])) {
				$reportList[$var] = $repInfo;
			} else {
				if ($repInfo['rank'] < $reportList[$var]['rank']) {
					$reportList[$var] = $repInfo;
				}
			}

		}

		$prevRank = 0;
		$i = 0;
		foreach ($reportList as $key => $repInfo) {
			$rankDiff = '';
			if ($i > 0) {
				$rankDiff = $prevRank - $repInfo['rank'];
				if ($rankDiff > 0) {
					$rankDiff = "<font class='green'>($rankDiff)</font>";
				}
				elseif ($rankDiff < 0) {
					$rankDiff = "<font class='red'>($rankDiff)</font>";
				}
			}
			$reportList[$key]['rank_diff'] = empty ($rankDiff) ? '' : $rankDiff;
			$prevRank = $repInfo['rank'];
			$i++;
		}

		$this->set('list', array_reverse($reportList, true));
		$this->render('report/report');
	}

	# func to show reports in a time
	function showTimeReport($searchInfo = '') {
		
		$fromTime = addslashes($searchInfo['time']);
		$toTime = $fromTime + (3600 * 24);
		$keywordId = intval($searchInfo['keyId']);
		$seId = intval($searchInfo['seId']);
		$seController = New SearchEngineController();
		$this->set('seInfo', $seController->__getsearchEngineInfo($seId));

		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$conditions .= empty ($seId) ? "" : " and s.searchengine_id=$seId";
		
		$fromTimeDate = date('Y-m-d', $fromTime);
		$toTimeDate = date('Y-m-d', $toTime);
		$sql = "select s.*,sd.url,sd.title,sd.description from searchresults s,searchresultdetails sd 
		where s.id=sd.searchresult_id and result_date>='$fromTimeDate' and result_date<'$toTimeDate' $conditions  
		order by s.rank";
		$reportList = $this->db->select($sql);
		$this->set('list', $reportList);
		$this->render('report/timereport');
	}

	# func to show graphical reports
	function showGraphicalReports($searchInfo = '') {		
		
		$userId = isLoggedIn();
		$fromTime = !empty($searchInfo['from_time']) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty ($searchInfo['to_time']) ? $searchInfo['to_time'] : date("Y-m-d");
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		$keywordController = New KeywordController();
		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : intval($searchInfo['keyword_id']);
		$this->set('keywordId', $keywordId);

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? '' : intval($searchInfo['se_id']);
		$this->set('seId', $seId);
		$this->set('seNull', true);
		
		// get graph content
		$graphCtrler = new GraphController();
		$graphCtrler->spTextKeyword = $this->spTextKeyword;
		$graphContent = $graphCtrler->showKeywordPostionGraph($keywordId, $fromTime, $toTime, $seId);
		$this->set('graphContent', $graphContent);
		
		$this->render('report/graphicalreport');
	}
	
	# function to show an message in graph when no records exist
	function showMessageAsImage($msg='', $width=700, $height=30, $red=233, $green=14, $blue=91) {	    
		
		$im = imagecreate($width, $height);		
        $bgColor = imagecolorallocate($im, 245, 248, 250);
        $textColor = imagecolorallocate($im, 233, 14, 91);
	    $fontFile = ($_SESSION['lang_code'] == 'ja') ? "fonts/M+1P+IPAG.ttf" : "fonts/tahoma.ttf";
	    imagettftext($im, 10, 0, 260, 20, $textColor, $fontFile, $msg);
        imagepng($im);
        imagedestroy($im);
        exit;
	}

	# function to show graph
	function showGraph($searchInfo = '') {
		
		$fromTimeDate = date('Y-m-d', $searchInfo['fromTime']);
		$toTimeDate = date('Y-m-d', $searchInfo['toTime']);
		$conditions = empty ($searchInfo['keywordId']) ? "" : " and s.keyword_id=".intval($searchInfo['keywordId']);
		$conditions .= empty ($searchInfo['seId']) ? "" : " and s.searchengine_id=".intval($searchInfo['seId']);
		$sql = "select s.*,se.domain from searchresults s,searchengines se  
		where s.searchengine_id=se.id and result_date>='$fromTimeDate' and result_date<='$toTimeDate'
		$conditions order by s.result_date";
		$repList = $this->db->select($sql);		
		$reportList = array ();
		$seList = array();
		foreach ($repList as $repInfo) {
			$var = $repInfo['searchengine_id'] . $repInfo['keyword_id'] . $repInfo['result_date'];
			if (empty ($reportList[$var])) {
				$reportList[$var] = $repInfo;
			} else {
				if ($repInfo['rank'] < $reportList[$var]['rank']) {
					$reportList[$var] = $repInfo;
				}
			}			
			
			if(empty($seList[$repInfo['searchengine_id']])){
				$seList[$repInfo['searchengine_id']] = $repInfo['domain'];
			}
		}
		asort($seList);	
		
		$dataList = array();
		$maxValue = 0;
		foreach($reportList as $repInfo){
			$seId = $repInfo['searchengine_id'];
			$dataList[$repInfo['result_date']][$seId] = $repInfo['rank'];
			$maxValue = ($repInfo['rank'] > $maxValue) ? $repInfo['rank'] : $maxValue;
		}
		
		// check whether the records are available for drawing graph
		if(empty($dataList) || empty($maxValue)) {
		    $kpText = ($_SESSION['lang_code'] == 'ja') ? $_SESSION['text']['common']['No Records Found']."!" : "No Records Found!";
		    $this->showMessageAsImage($kpText);		    
		}
		
		# Dataset definition
		$dataSet = new pData;
		foreach($dataList as $dataInfo){
			$i = 1;	
			foreach($seList as $seId => $seVal){
				$val = empty($dataInfo[$seId]) ? 0 : $dataInfo[$seId];
				$dataSet->AddPoint($val, "Serie".$i++);				  
			}
		}
		
		$i = 1;
		foreach($seList as $seDomain){
			$dataSet->AddSerie("Serie$i");
			$dataSet->SetSerieName($seDomain, "Serie$i");
			$i++;
		}
		
		$serieCount = count($seList) + 1;
		$dataSet->AddPoint(array_keys($dataList), "Serie$serieCount");
		$dataSet->SetAbsciseLabelSerie("Serie$serieCount");
		
		# if language is japanese
		if ($_SESSION['lang_code'] == 'ja') {
		    $fontFile = "fonts/M+1P+IPAG.ttf";		    
		    $dataSet->SetXAxisName($_SESSION['text']['common']["Date"]);		
		    $dataSet->SetYAxisName($_SESSION['text']['common']["Rank"]);
		} else {
	        $fontFile = "fonts/tahoma.ttf";
		    $dataSet->SetXAxisName("Date");		
		    $dataSet->SetYAxisName("Rank");
		}

		/* commented to fix invalid date in graphical reports x axis issue */
		// $dataSet->SetXAxisFormat("date");		
		
		# Initialise the graph
		$chart = new pChart(720, 520);
		$chart->setFixedScale($maxValue, 1);		
		$chart->setFontProperties($fontFile, 8);
		$chart->setGraphArea(85, 30, 670, 425);
		$chart->drawFilledRoundedRectangle(7, 7, 713, 513, 5, 240, 240, 240);
		$chart->drawRoundedRectangle(5, 5, 715, 515, 5, 230, 230, 230);

		$chart->drawGraphArea(255, 255, 255, TRUE);
		$getData = $dataSet->GetData();
		$getDataDes = $dataSet->GetDataDescription();
		$chart->drawScale($getData, $getDataDes, SCALE_NORMAL, 150, 150, 150, TRUE, 90, 2);
		$chart->drawGrid(4, TRUE, 230, 230, 230, 50);

		# Draw the 0 line   
		$chart->setFontProperties($fontFile, 6);
		$chart->drawTreshold(0, 143, 55, 72, TRUE, TRUE);

		# Draw the line graph
		$getData = $dataSet->GetData();
		$getDataDes = $dataSet->GetDataDescription();
		$chart->drawLineGraph($getData, $getDataDes);
		$getData = $dataSet->GetData();
		$getDataDes = $dataSet->GetDataDescription();
		$chart->drawPlotGraph($getData, $getDataDes, 3, 2, 255, 255, 255);
		
		$j = 1;
		$chart->setFontProperties($fontFile, 10);
		foreach($seList as $seDomain){
			$getData = $dataSet->GetData();
			$getDataDes = $dataSet->GetDataDescription();
			$chart->writeValues($getData, $getDataDes, "Serie".$j++);
		}

		# Finish the graph
		$chart->setFontProperties("fonts/tahoma.ttf", 8);
		$getDataDes = $dataSet->GetDataDescription();
		$chart->drawLegend(90, 35, $getDataDes, 255, 255, 255);
		$chart->setFontProperties($fontFile, 10);
		$kpText = ($_SESSION['lang_code'] == 'ja') ? $this->spTextKeyword["Keyword Position Report"] : "Keyword Position Report"; 
		$chart->drawTitle(60, 22, $kpText, 50, 50, 50, 585);
		$chart->stroke();
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		$keywordController = New KeywordController();
		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$this->set('keyNull', false);
		$keywordId = empty ($searchInfo['keyword_id']) ? '' : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);		

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? '' : $searchInfo['se_id'];
		$this->set('seId', $seId);
		$this->set('seNull', true);
		$this->set('seStyle', 170);		
				
		$this->render('report/generatereport');
	}
	
	# func to generate reports
	function generateReports( $searchInfo='' ) {		
		$userId = isLoggedIn();
		$keywordId = empty ($searchInfo['keyword_id']) ? '' : intval($searchInfo['keyword_id']);
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		$seId = empty ($searchInfo['se_id']) ? '' : intval($searchInfo['se_id']);
		
		$seController = New SearchEngineController();
		$this->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and k.status=1";
		if(!empty($userId) && !isAdmin()) $sql .= " and w.user_id=$userId";
		if(!empty($websiteId)) $sql .= " and k.website_id=$websiteId";
		if(!empty($keywordId)) $sql .= " and k.id=$keywordId";
		$sql .= " order by k.name";
		$keywordList = $this->db->select($sql);		
		
		if(count($keywordList) <= 0){
			echo "<p class='note error'>".$_SESSION['text']['common']['No Keywords Found']."</p>";
			exit;
		}
		
		# loop through each keyword			
		foreach ( $keywordList as $keywordInfo ) {
			$this->seFound = 0;
			$crawlResult = $this->crawlKeyword($keywordInfo, $seId);
			foreach($crawlResult as $sengineId => $matchList){
				if($matchList['status']){
					foreach($matchList['matched'] as $i => $matchInfo){
						$remove = ($i == 0) ? true : false;						
						$matchInfo['se_id'] = $sengineId;						
						$matchInfo['keyword_id'] = $keywordInfo['id'];
						$this->saveMatchedKeywordInfo($matchInfo, $remove);
					}
					echo "<p class='note notesuccess'>".$this->spTextKeyword['Successfully crawled keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['results from']." <b>".$this->seList[$sengineId]['domain']."</b>.....</p>";
				}else{
					echo "<p class='note notefailed'>".$this->spTextKeyword['Crawling keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['results from']." <b>".$this->seList[$sengineId]['domain']."</b> ".$_SESSION['text']['common']['failed']."......</p>";
				}
			}
			if(empty($this->seFound)){
				echo "<p class='note notefailed'>".$_SESSION['text']['common']['Keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['not assigned to required search engines']."........</p>";
			}
			sleep(SP_CRAWL_DELAY);
		}	
	}
	
	# function to format pagecontent
	function formatPageContent($seInfoId, $pageContent) {
	    if (!empty($this->seList[$seInfoId]['from_pattern']) && $this->seList[$seInfoId]['to_pattern']) {
	        $pattern = $this->seList[$seInfoId]['from_pattern']."(.*?)".$this->seList[$seInfoId]['to_pattern'];
	        if (preg_match("/$pattern/is", $pageContent, $matches)) {
	            if (!empty($matches[1])) {
	                $pageContent = $matches[1];
	            }
	        }
	    }
	    return $pageContent;	    
	}
	
	# func to crawl keyword
	function crawlKeyword( $keywordInfo, $seId='', $cron=false, $removeDuplicate=true) {
	    
	    // check whether any api source is enabled for crawl keyword
	    list($resDataStatus, $resData) = SettingsController::getSearchResults($keywordInfo, $this->showAll, $seId, $cron);
	    if ($resDataStatus) {
	        $this->seFound = true;
	        if (!empty($seId)) {
	            $this->seFound = isset($resData[$seId]['seFound']) ? $resData[$seId]['seFound'] : 0;
	        }
	        
	        return $resData;
	    }
	    
		$crawlResult = array();
		$websiteUrl = formatUrl($keywordInfo['url'], false);
		if(empty($websiteUrl)) return $crawlResult;
		if(empty($keywordInfo['name'])) return $crawlResult;
		
		$websiteOtherUrl = SettingsController::getWebsiteOtherUrl($websiteUrl);
		
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$seList = explode(':', $keywordInfo['searchengines']);
		foreach($seList as $seInfoId) {
			
			// function to execute only passed search engine
			if(!empty($seId) && ($seInfoId != $seId)) continue;
			
			// if search engine not found continue
			if (empty($this->seList[$seInfoId])) continue;
			
			$this->seFound = 1;
			
			// if execution from cron check whether cron already executed
			/*if ($cron) {
			    if (SP_MULTIPLE_CRON_EXEC && $this->isCronExecuted($keywordInfo['id'], $seInfoId, $time)) continue;
			}*/			
			
			$searchUrl = str_replace('[--keyword--]', urlencode(stripslashes($keywordInfo['name'])), $this->seList[$seInfoId]['url']);
			$searchUrl = str_replace('[--country--]', $keywordInfo['country_code'], $searchUrl);
			if (empty($keywordInfo['country_code']) && stristr($searchUrl, '&cr=country&')) {
			    $searchUrl = str_replace('&cr=country&', '&cr=&', $searchUrl);
			}
			
			$seUrl = str_replace('[--start--]', $this->seList[$seInfoId]['start'], $searchUrl);
			
			// if google add special parameters
			$isGoogle = false;
			if (stristr($this->seList[$seInfoId]['url'], 'google')) {
			    $isGoogle = true;
			    $seUrl .= "&ie=utf-8&pws=0&gl=".$keywordInfo['country_code'];
			    $seUrl = str_replace('[--lang--]', '', $seUrl);
			} else {
				$seUrl = str_replace('[--lang--]', $keywordInfo['lang_code'], $seUrl);
			}
			
			if(!empty($this->seList[$seInfoId]['cookie_send'])){
				$this->seList[$seInfoId]['cookie_send'] = str_replace('[--lang--]', $keywordInfo['lang_code'], $this->seList[$seInfoId]['cookie_send']);
				$this->spider->_CURLOPT_COOKIE = $this->seList[$seInfoId]['cookie_send'];				
			}
			
			$result = $this->spider->getContent($seUrl);
			$pageContent = $this->formatPageContent($seInfoId, $result['page']);
			
			// testing code for regex
            /*$testFileName = SP_TMPPATH . "/google.html";
            $myfile = fopen($testFileName, "w") or die("Unable to open file!");
            fwrite($myfile, $pageContent);
            fclose($myfile);
            exit;
            
            $myfile = fopen($testFileName, "r") or die("Unable to open file!");
            $pageContent = fread($myfile,filesize($testFileName));
            fclose($myfile);*/
			
			$crawlLogCtrl = new CrawlLogController();
			$crawlInfo = [];
			$crawlInfo['crawl_type'] = 'keyword';
			$crawlInfo['ref_id'] = empty($keywordInfo['id']) ? $keywordInfo['name'] : $keywordInfo['id'];
			$crawlInfo['subject'] = $seInfoId;
			
			$seStart = $this->seList[$seInfoId]['start'] + $this->seList[$seInfoId]['start_offset'];
			while(empty($result['error']) && ($seStart < $this->seList[$seInfoId]['max_results']) ){
				$logId = $result['log_id'];
				if(SP_DEBUG){
					$crawlInfo['log_message'] = "Started at: $seStart";
				}
				$crawlLogCtrl->updateCrawlLog($logId, $crawlInfo);
				sleep(SP_CRAWL_DELAY);
				$seUrl = str_replace('[--start--]', $seStart, $searchUrl);
				$result = $this->spider->getContent($seUrl);
				$pageContent .= $this->formatPageContent($seInfoId, $result['page']);
				$seStart += $this->seList[$seInfoId]['start_offset'];
			}

			# to check whether utf8 conversion needed
			if(!empty($this->seList[$seInfoId]['encoding'])){
				$pageContent = mb_convert_encoding($pageContent, "UTF-8", $this->seList[$seInfoId]['encoding']);
			}
			
			$crawlStatus = 0;
			if(empty($result['error'])){
			    
			    // to update cron that report executed for akeyword on a search engine
			    if (SP_MULTIPLE_CRON_EXEC && $cron) $this->saveCronTrackInfo($keywordInfo['id'], $seInfoId, $time);
			    
			    // verify the urls existing in the result
			    preg_match_all($this->seList[$seInfoId]['regex'], $pageContent, $matches);
			    if (!empty($matches[$this->seList[$seInfoId]['url_index']])) {
			    	
					$urlList = $matches[$this->seList[$seInfoId]['url_index']];
					$crawlResult[$seInfoId]['matched'] = array();
					$rank = 1;
					$previousDomain = "";
					foreach($urlList as $i => $url){
						$url = urldecode(strip_tags($url));
						
						// add special condition for baidu
						if (stristr($this->seList[$seInfoId]['domain'], "baidu")) {
							$url =  addHttpToUrl($url);
							$url = str_replace("...", "", $url);
						}

						// if google engine, format the urls
						if ($isGoogle && stristr($url, '/url?q=')) {
							$url = preg_replace(array('/\/url\?q=/i', '/\&amp;sa=U.*$/i'), array("", ""), $url);
						}
						
						if(!preg_match('/^http:\/\/|^https:\/\//i', $url)) continue;
						
						// check for to remove bing ad links in page
						if(stristr($url, 'bat.bing.com')) continue;

						// check to remove duplicates from same domain if google is the search engine
						if ($removeDuplicate && $isGoogle) {
						    $currentDomain = parse_url($url, PHP_URL_HOST);
						    if ($previousDomain == $currentDomain) {
						        continue;        
						    }
						    $previousDomain = $currentDomain;
						}
						
						if($this->showAll || ( 
						      stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) || 
						      stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl)
						    )){

							if ($this->showAll && (
                                    stristr($url, "http://" . $websiteUrl) || stristr($url, "https://" . $websiteUrl) || 
                                    stristr($url, "http://" . $websiteOtherUrl) || stristr($url, "https://" . $websiteOtherUrl)
							    )) {
								$matchInfo['found'] = 1; 
							} else {
								$matchInfo['found'] = 0;
							}
							
							$matchInfo['url'] = $url;
							$matchInfo['title'] = strip_tags($matches[$this->seList[$seInfoId]['title_index']][$i]);
							$matchInfo['description'] = strip_tags($matches[$this->seList[$seInfoId]['description_index']][$i]);
							$matchInfo['rank'] = $rank;
							$crawlResult[$seInfoId]['matched'][] = $matchInfo;
						}
						
						$rank++;							
					}
					
					$crawlStatus = 1;					
					
				} else {
					
					// set crawl log info
					$crawlInfo['crawl_status'] = 0;
					$crawlInfo['log_message'] = SearchEngineController::isCaptchInSearchResults($pageContent) ? "<font class=error>Captcha found</font> in search result page" : "Regex not matched error occured while parsing search results!";
					
					if(SP_DEBUG){
						echo "<p class='note' style='text-align:left;'>Error occured while parsing $seUrl ".formatErrorMsg("Regex not matched <br>\n")."</p>";
					}
				}	
			} else {
				if (SP_DEBUG) {
					echo "<p class='note' style='text-align:left;'>Error occured while crawling $seUrl ".formatErrorMsg($result['errmsg']."<br>\n")."</p>";
				}
			}			
			$crawlResult[$seInfoId]['status'] = $crawlStatus;			
			sleep(SP_CRAWL_DELAY);
			
			// update crawl log
			$logId = $result['log_id'];
			$crawlLogCtrl->updateCrawlLog($logId, $crawlInfo);
		
			// if proxy enabled if crawl failed try to check next item
			if (!$crawlResult[$seInfoId]['status'] && SP_ENABLE_PROXY && CHECK_WITH_ANOTHER_PROXY_IF_FAILED) {
				
				// max proxy checked in one execution is exeeded
				if ($this->proxyCheckCount < CHECK_MAX_PROXY_COUNT_IF_FAILED) {
				
					// if proxy is available for execution
					$proxyCtrler = New ProxyController();
					if ($proxyInfo = $proxyCtrler->getRandomProxy()) {
						$this->proxyCheckCount++;
						sleep(SP_CRAWL_DELAY);
						$crawlResult = array_merge($crawlResult, $this->crawlKeyword($keywordInfo, $seInfoId, $cron, $removeDuplicate));
					}
					
				} else {
					$this->proxyCheckCount = 1;
				}
			}
		
		}
		
		return  $crawlResult;
	}
	
	# func to save the report
	function saveMatchedKeywordInfo($matchInfo, $remove=false) {
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$resultDate = date('Y-m-d');
		$this->checkDBConn();
		if($remove){
			$sql = "select id from searchresults where keyword_id={$matchInfo['keyword_id']}
			and searchengine_id={$matchInfo['se_id']} and result_date='$resultDate'";
			$recordList = $this->db->select($sql);
		
			if(count($recordList) > 0){
				foreach($recordList as $recordInfo){
					$sql = "delete from searchresultdetails where searchresult_id=".$recordInfo['id'];
					$this->db->query($sql);
				}
				
				$sql = "delete from searchresults where keyword_id={$matchInfo['keyword_id']}
				and searchengine_id={$matchInfo['se_id']} and result_date='$resultDate'";
				$this->db->query($sql);
			}
		}
		
		$sql = "insert into searchresults(keyword_id,searchengine_id,`rank`,`time`,result_date)
				values({$matchInfo['keyword_id']},{$matchInfo['se_id']},{$matchInfo['rank']},$time,'$resultDate')";
		$this->db->query($sql);
		
		$recordId = $this->db->getMaxId('searchresults');		
		$sql = "insert into searchresultdetails(searchresult_id,url,title,description)
				values($recordId,'{$matchInfo['url']}','".addslashes($matchInfo['title'])."','".addslashes($matchInfo['description'])."')";
		$this->db->query($sql); 
	}
	
	# func to check keyword rank
	function quickRankChecker() {
		
		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$this->set('seStyle', 230);		
		$seId = empty ($searchInfo['se_id']) ? '' : $searchInfo['se_id'];
		$this->set('seId', $seId);
		
		$langController = New LanguageController();
		$this->set('langNull', true);
		$this->set('langStyle', 230);			
		$this->set('langList', $langController->__getAllLanguages());
		
		$countryController = New CountryController();
		$this->set('countryList', $countryController->__getAllCountries());
		$this->set('countryNull', true);		
		$this->set('countryStyle', 230);
		
		$this->render('report/quickrankchecker');
	}
	
	# func to show quick rank report
	function showQuickRankChecker($keywordInfo='') {
		
		$keywordInfo['searchengines'] = $keywordInfo['se_id'];
		$this->showAll = $keywordInfo['show_all'];
		
		$seController = New SearchEngineController();
		$this->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		$crawlResult = $this->crawlKeyword($keywordInfo);
		
		$resultList = array();
		if(!empty($crawlResult[$keywordInfo['se_id']]['status'])){
			$resultList = $crawlResult[$keywordInfo['se_id']]['matched'];
		}
		$this->set('list', $resultList);
		
		$this->render('report/showquickrankchecker');
	}
	
	#function to save keyword cron trcker for multiple execution of cron same day
	function saveCronTrackInfo($keywordId, $seId, $time) {
	    $sql = "Insert into keywordcrontracker(keyword_id,searchengine_id,`time`) values($keywordId,$seId,$time)";
	    $this->db->query($sql);
	}
	
	# function to check whether cron executed for a particular keyword and search engine
	function isCronExecuted($keywordId, $seId, $time) {
	    $sql = "select keyword_id from keywordcrontracker where keyword_id=$keywordId and searchengine_id=$seId and `time`=$time";
        $info = $this->db->select($sql, true);
	    return empty($info['keyword_id']) ? false : true;
	}
	
    # function to show system reports 
	function showOverallReportSummary($searchInfo='', $cronUserId=false) {
	    $searchInfo['type'] = htmlentities($searchInfo['type'], ENT_QUOTES);
	    $searchInfo['search_name'] = htmlentities($searchInfo['search_name'], ENT_QUOTES);
	    $searchInfo['order_col'] = str_replace([' ', '*'], '', $searchInfo['order_col']);
		$spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
        $this->set('spTextHome', $spTextHome);
        $this->set('cronUserId', $cronUserId);
		
		$exportVersion = false;
		switch($searchInfo['doc_type']){
			
			case "export":
				$exportVersion = true;
				$exportContent = "";
				break;
			
			case "pdf":
				$this->set('pdfVersion', true);
				break;
			
			case "print":
				$this->set('printVersion', true);
				break;
		}
		
		$this->set('sectionHead', $spTextHome['Overall Report Summary']);
		$userId = empty($cronUserId) ? isLoggedIn() : $cronUserId;
		$isAdmin = isAdmin();

		$keywordController = New KeywordController();
		$websiteCtrler = New WebsiteController();
		$websiteList = $websiteCtrler->__getAllWebsites($userId, true);
		$this->set('siteList', $websiteList);
		$websiteId = isset($searchInfo['website_id']) ? $searchInfo['website_id'] : $websiteList[0]['id'];
		$websiteId = intval($websiteId);
		$this->set('websiteId', $websiteId);
		$urlarg = "website_id=$websiteId";
		
		$websiteUrl = "";
		foreach ($websiteList as $websiteInfo) {
		    if ($websiteInfo['id'] == $websiteId) {
		        $websiteUrl = $websiteInfo['url'];
		        break;    
		    }
		}
		$this->set('websiteUrl', $websiteUrl);
				
		$reportTypes = array(
			'keyword-position' => $this->spTextTools["Keyword Position Summary"],
			'website-stats' => $spTextHome["Website Statistics"],
			'website-search-reports' => $this->spTextTools['Website Search Summary'],
			'sitemap-reports' => $this->spTextTools['Sitemap Reports Summary'],
			'keyword-search-reports' => $this->spTextTools['Keyword Search Summary'],
		    'social-media-reports' => $this->spTextTools['Social Media Report Summary'],
		    'analytics-reports' => $this->spTextTools['Website Analytics Summary'],
		    'review-reports' => $this->spTextTools['Review Report Summary'],
		);
		
		$searchInfo['report_type'] = htmlentities($searchInfo['report_type'], ENT_QUOTES);
		$this->set('reportTypes', $reportTypes);
		$urlarg .= "&report_type=".$searchInfo['report_type'];		
				
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		}
		
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 00:00:00');
		} else {
			$toTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		}		
		
		$fromTimeShort = date('Y-m-d', $fromTime);
		$this->set('fromTime', $fromTimeShort);
		$toTimeShort = date('Y-m-d', $toTime);
		$this->set('toTime', $toTimeShort);		
		$urlarg .= "&from_time=$fromTimeShort&to_time=$toTimeShort&search_name=" . $searchInfo['search_name'];	
		
		$seSearchUserId = isAdmin() ? "" : $userId;
		$this->seLIst = $keywordController->getUserKeywordSearchEngineList($seSearchUserId);
		$this->set('seList', $this->seLIst);
		
		$this->set('isAdmin', $isAdmin);
		$this->set('urlarg', $urlarg);
		
		$scriptPath = SP_WEBPATH."/archive.php?website_id=$websiteId";
		$scriptPath .= "&from_time=$fromTimeShort&to_time=$toTimeShort&search_name=" . $searchInfo['search_name'];
		
		# keyword position report section
		if (empty($searchInfo['report_type']) ||  ($searchInfo['report_type'] == 'keyword-position')) {
		    
		    // to find order col
            if (!empty($searchInfo['order_col'])) {
    		    $orderCol = $searchInfo['order_col'];
    		    $orderVal = getOrderByVal($searchInfo['order_val']);
    		} else {
    		    $orderCol = $this->seLIst[array_keys($this->seLIst)[0]]['id'];
    		    $orderVal = 'ASC';    
    		}
    		
    		$this->set('orderCol', $orderCol);
    		$this->set('orderVal', $orderVal);
			$scriptPath .= "&report_type=keyword-position&order_col=$orderCol&order_val=$orderVal";
			
    		$conditions = " and w.status=1 and k.status=1";
    		$conditions .= isAdmin() ? "" : $websiteCtrler->getWebsiteUserAccessCondition($userId);
    		$conditions .= !empty($websiteId) ? " and w.id=$websiteId" : "";
    		$conditions .= !empty($searchInfo['search_name']) ? " and k.name like '%".addslashes($searchInfo['search_name'])."%'" : "";
    		
    		$subSql = "select [col] from keywords k,searchresults r, websites w
    		where k.id=r.keyword_id and k.website_id=w.id $conditions
    		and r.searchengine_id=".intval($orderCol)." and r.result_date='" . addslashes($toTimeShort) . "'
    		group by k.id";
    		
    		$unionOrderCol = ($orderCol == "keyword") ? "name" : "`rank`";
    		$sql = "(". str_replace("[col]", "k.id,k.name,min(`rank`) `rank`,w.name website,w.url weburl", $subSql) .")
    		UNION
    		(select k.id,k.name,1000,w.name website,w.url weburl
    		from keywords k, websites w
    		where w.id=k.website_id $conditions and k.id not in
    		(". str_replace("[col]", "distinct(k.id)", $subSql) ."))
    		order by $unionOrderCol $orderVal";
    		
    		if ($unionOrderCol != 'name') $sql .= ", name";
    		
    		// pagination setup, if not from cron job email send function, pdf and export action
    		if (!in_array($searchInfo['doc_type'], array("pdf", "export")) && !$cronUserId) {
    			
    			$this->db->query($sql, true);
    			$this->paging->setDivClass('pagingdiv');
    			$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
    			$pagingDiv = $this->paging->printPages($scriptPath, '', 'scriptDoLoad', 'content', "");
    			$this->set('keywordPagingDiv', $pagingDiv);
    			$this->set('pageNo', $searchInfo['pageno']);    			
    			
    			$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
    		}
    		
    		# set keywords list
    		$list = $this->db->select($sql);
    		
    		$indexList = array();
    		foreach($list as $keywordInfo){
    			$positionInfo = $this->__getKeywordSearchReport($keywordInfo['id'], $fromTime, $toTime, true);
    			
    			// check whether the sorting search engine is there
    		    $indexList[$keywordInfo['id']] = empty($positionInfo[$orderCol][$toTimeShort]) ? 10000 : $positionInfo[$orderCol][$toTimeShort];
    			
    			$keywordInfo['position_info'] = $positionInfo;
    			$keywordList[$keywordInfo['id']] = $keywordInfo;
    		}
    
    		// sort array according the value
    		if ($orderCol != 'keyword') { 
        		if ($orderVal == 'DESC') {
        		    arsort($indexList);
        		} else {
        		    asort($indexList);
        		}
    		}
    		$this->set('indexList', $indexList);
    
    		if ($exportVersion) {
    			$spText = $_SESSION['text'];
    			$reportHeading =  $this->spTextTools['Keyword Position Summary']."($fromTimeShort - $toTimeShort)";
    			$exportContent .= createExportContent( array('', $reportHeading, ''));
    			$exportContent .= createExportContent( array());
    			$headList = array($spText['common']['Website'], $spText['common']['Keyword']);
    			
    			$pTxt = str_replace("-", "/", substr($fromTimeShort, -5));
    			$cTxt = str_replace("-", "/", substr($toTimeShort, -5));
    			foreach ($this->seLIst as $seInfo) {
    				$domainTxt = str_replace("www.", "", $seInfo['domain']);
    				$headList[] = $domainTxt . "($cTxt)";
    				$headList[] = $domainTxt . "($pTxt)";
    				$headList[] = $domainTxt . "(+/-)";
    			}
    			
    			$exportContent .= createExportContent( $headList);
    			
    			
    			foreach($indexList as $keywordId => $rankValue){
    				$listInfo = $keywordList[$keywordId];
    				$positionInfo = $listInfo['position_info'];
    			
    				$valueList = array($listInfo['weburl'], $listInfo['name']);
    				foreach ($this->seLIst as $index => $seInfo){
    						
    					$rankInfo = $positionInfo[$seInfo['id']];
    					$prevRank = isset($rankInfo[$fromTimeShort]) ? $rankInfo[$fromTimeShort] : "";
    					$currRank = isset($rankInfo[$toTimeShort]) ? $rankInfo[$toTimeShort] : "";
    					$rankDiff = "";
    			
    					// if both ranks are existing
    					if ($prevRank != '' && $currRank != '') {
    						$rankDiff = $prevRank - $currRank;
    					}
    						
    					$valueList[] = $currRank;
    					$valueList[] = $prevRank;
    					$valueList[] = $rankDiff;
    				}
    			
    				$exportContent .= createExportContent( $valueList);
    			}
    			
    		} else {
				$this->set('list', $keywordList);
				$this->set('keywordPos', true);	
    		}
			
		}
		
		# website report section
		if (empty($searchInfo['report_type']) ||  ($searchInfo['report_type'] == 'website-stats')) {
						
			// pagination setup
			if (!in_array($searchInfo['doc_type'], array('export', 'pdf')) && !$cronUserId) {
				$scriptPath .= "&report_type=website-stats";
				$info['pageno'] = intval($info['pageno']);
				$sql = "select * from websites w where w.status=1";
				$sql .= isAdmin() ? "" : $websiteCtrler->getWebsiteUserAccessCondition($userId);
    			$sql .= !empty($websiteId) ? " and w.id=$websiteId" : "";
				
				// search for user name
				if (!empty($searchInfo['search_name'])) {
					$sql .= " and (w.name like '%".addslashes($searchInfo['search_name'])."%'
					or w.url like '%".addslashes($searchInfo['search_name'])."%')";
				}
				
				$sql .= " order by w.name";
				$this->db->query($sql, true);
				$this->paging->setDivClass('pagingdiv');
				$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
				$pagingDiv = $this->paging->printPages($scriptPath, '', 'scriptDoLoad', 'content', "");
				$this->set('websitePagingDiv', $pagingDiv);
				$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
				$this->set('pageNo', $searchInfo['pageno']);
				$websiteList = $this->db->select($sql);
			}
		    
		    include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
			include_once(SP_CTRLPATH."/rank.ctrl.php");
			include_once(SP_CTRLPATH."/backlink.ctrl.php");
			include_once(SP_CTRLPATH."/directory.ctrl.php");
			include_once(SP_CTRLPATH."/pagespeed.ctrl.php");
			$rankCtrler = New RankController();
			$backlinlCtrler = New BacklinkController();
			$saturationCtrler = New SaturationCheckerController();			
			$dirCtrler = New DirectoryController();		
			$pageSpeedCtrler = New PageSpeedController();
			
			$spTextPS = $this->getLanguageTexts('pagespeed', $_SESSION['lang_code']);
			$this->set('spTextPS', $spTextPS);
			
			$websiteRankList = array();
			foreach($websiteList as $listInfo){
				
			    // if only needs to show onewebsite selected
			    if (!empty($websiteId) && ($listInfo['id'] != $websiteId)) continue;
			    
				# rank reports
				$report = $rankCtrler->__getWebsiteRankReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];
				$listInfo['mozrank'] = empty($report['moz_rank']) ? "-" : $report['moz_rank']." ".$report['rank_diff_moz'];
				$listInfo['domain_authority'] = empty($report['domain_authority']) ? "-" : $report['domain_authority']." ".$report['rank_diff_domain_authority'];
				$listInfo['page_authority'] = empty($report['page_authority']) ? "-" : $report['page_authority']." ".$report['rank_diff_page_authority'];
				
				# back links reports
				$report = $backlinlCtrler->__getWebsitebacklinkReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];
				$listInfo['google']['backlinks'] = empty($report['google']) ? "-" : $report['google']." ".$report['rank_diff_google'];
				$listInfo['msn']['backlinks'] = empty($report['msn']) ? "-" : $report['msn']." ".$report['rank_diff_msn'];
				
				# rank reports
				$report = $saturationCtrler->__getWebsiteSaturationReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];				
				$listInfo['google']['indexed'] = empty($report['google']) ? "-" : $report['google']." ".$report['rank_diff_google'];
				$listInfo['msn']['indexed'] = empty($report['msn']) ? "-" : $report['msn']." ".$report['rank_diff_msn'];
				
				# pagespeed reports
				$report = $pageSpeedCtrler->__getWebsitePageSpeedReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];				
				$listInfo['desktop_speed_score'] = empty($report['desktop_speed_score']) ? "-" : $report['desktop_speed_score']." ".$report['rank_diff_desktop_speed_score'];
				$listInfo['mobile_speed_score'] = empty($report['mobile_speed_score']) ? "-" : $report['mobile_speed_score']." ".$report['rank_diff_mobile_speed_score'];
								
				$listInfo['dirsub']['total'] = $dirCtrler->__getTotalSubmitInfo($listInfo['id']);
				$listInfo['dirsub']['active'] = $dirCtrler->__getTotalSubmitInfo($listInfo['id'], true);
				$websiteRankList[] = $listInfo;
			}
			
			// if export function called
			if ($exportVersion) {
				$exportContent .= createExportContent( array());
				$exportContent .= createExportContent( array());
				$exportContent .= createExportContent( array('', $spTextHome['Website Statistics']."($fromTimeShort - $toTimeShort)", ''));
				
				if ((isAdmin() && !empty($webUserId))) {				    
				    $exportContent .= createExportContent( array());				    
				    $exportContent .= createExportContent( array());
				    $userCtrler = new UserController();
				    $userInfo = $userCtrler->__getUserInfo($webUserId);
				    $exportContent .= createExportContent( array($_SESSION['text']['common']['User'], $userInfo['username']));
				}
				
				$exportContent .= createExportContent( array());
				$headList = array(
					$_SESSION['text']['common']['Website'],
					$_SESSION['text']['common']['MOZ Rank'],
					$_SESSION['text']['common']['Domain Authority'],
					$_SESSION['text']['common']['Page Authority'],
					'Google '.$spTextHome['Backlinks'],
					'Bing '.$spTextHome['Backlinks'],
					'Google '.$spTextHome['Indexed'],
					'Bing '.$spTextHome['Indexed'],
					$spTextPS['Desktop Speed'],
					$spTextPS['Mobile Speed'],
					$_SESSION['text']['common']['Total'].' Submission',
					$_SESSION['text']['common']['Active'].' Submission',
				);
				
				$exportContent .= createExportContent( $headList);
				foreach ($websiteRankList as $websiteInfo) {
					$valueList = array(
						$websiteInfo['url'],
						strip_tags($websiteInfo['mozrank']),
						strip_tags($websiteInfo['domain_authority']),
						strip_tags($websiteInfo['page_authority']),
						strip_tags($websiteInfo['google']['backlinks']),
						strip_tags($websiteInfo['msn']['backlinks']),
						strip_tags($websiteInfo['google']['indexed']),					
						strip_tags($websiteInfo['msn']['indexed']),
						strip_tags($websiteInfo['desktop_speed_score']),
						strip_tags($websiteInfo['mobile_speed_score']),
						$websiteInfo['dirsub']['total'],					
						$websiteInfo['dirsub']['active'],
					);
					$exportContent .= createExportContent( $valueList);
				} 
			}else {			
				$this->set('websiteRankList', $websiteRankList);
				$this->set('websiteStats', true);
			}
		}
		
		# website search report section
		if (empty($searchInfo['report_type']) || in_array($searchInfo['report_type'], array('review-reports', 'social-media-reports', 'website-search-reports', 'keyword-search-reports', 'sitemap-reports', 'analytics-reports')) ) {
		    include_once(SP_CTRLPATH."/analytics.ctrl.php");
			$webMasterCtrler = new WebMasterController();
			$socialMediaCtrler = New SocialMediaController();
			$reviewCtrler = New ReviewManagerController();
			$webMasterCtrler->set('spTextTools', $this->spTextTools);
			$webMasterCtrler->spTextTools = $this->spTextTools;
			$filterList = $searchInfo;
			$wmMaxFromTime = strtotime('-3 days');
			$wmMaxEndTime = strtotime('-2 days');
			$filterList['from_time'] = $fromTime > $wmMaxFromTime ? $wmMaxFromTime : $fromTime;
			$filterList['to_time'] = $toTime > $wmMaxEndTime ? $wmMaxEndTime : $toTime;
			$filterList['from_time'] = date('Y-m-d', $filterList['from_time']);
			$filterList['to_time'] = date('Y-m-d', $filterList['to_time']);
			$filterList['website_id'] = $websiteId;
			
			// if website search reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'website-search-reports')) {
				$websiteSearchReport = $webMasterCtrler->viewWebsiteSearchSummary($filterList, true, $cronUserId);
			}
			
			// if keyword search reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'keyword-search-reports')) {
				$keywordSearchReport = $webMasterCtrler->viewKeywordSearchSummary($filterList, true, $cronUserId);
			}
			
			// if sitemap reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'sitemap-reports')) {
				$websiteCtrler->set('spTextPanel', $this->spTextPanel);
				$sitemapReport = $websiteCtrler->listSitemap($filterList, true, $cronUserId);
			}
			
			// if website analytics reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'analytics-reports')) {
			    $analyticsCtrler = new AnalyticsController();
			    $analyticsCtrler->set('spTextTools', $this->spTextTools);
			    $analyticsCtrler->spTextTools = $this->spTextTools;
			    $wmMaxFromTime = strtotime('-2 days');
			    $wmMaxEndTime = strtotime('-1 days');
			    $filterList['from_time'] = $fromTime > $wmMaxFromTime ? $wmMaxFromTime : $fromTime;
			    $filterList['to_time'] = $toTime > $wmMaxEndTime ? $wmMaxEndTime : $toTime;
			    $filterList['from_time'] = date('Y-m-d', $filterList['from_time']);
			    $filterList['to_time'] = date('Y-m-d', $filterList['to_time']);
			    $analyticsReport = $analyticsCtrler->viewAnalyticsSummary($filterList, true, $cronUserId);
			}
			
			// if social media reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'social-media-reports')) {
				$filterList['from_time'] = $fromTimeShort;
				$filterList['to_time'] = $toTimeShort;
				$socialMediaCtrler->set('spTextTools', $this->spTextTools);
				$socialMediaCtrler->set('spTextPanel', $this->spTextPanel);
				$socialMediaCtrler->spTextTools = $this->spTextTools;
				$socialMediaReport = $socialMediaCtrler->viewReportSummary($filterList, true, $cronUserId);
			}

			// if social media reports
			if (empty($searchInfo['report_type']) || ($searchInfo['report_type'] == 'review-reports')) {
				$filterList['from_time'] = $fromTimeShort;
				$filterList['to_time'] = $toTimeShort;
				$reviewCtrler->set('spTextTools', $this->spTextTools);
				$reviewCtrler->set('spTextPanel', $this->spTextPanel);
				$reviewCtrler->spTextTools = $this->spTextTools;
				$reviewReport = $reviewCtrler->viewReportSummary($filterList, true, $cronUserId);
			}
			
			if ($exportVersion) {
				$exportContent .= $websiteSearchReport;
				$exportContent .= $keywordSearchReport;
				$exportContent .= $analyticsReport;
				$exportContent .= $socialMediaReport;
				$exportContent .= $reviewReport;
			} else {
				$this->set('websiteSearchReport', $websiteSearchReport);
				$this->set('keywordSearchReport', $keywordSearchReport);
				$this->set('sitemapReport', $sitemapReport);
				$this->set('socialMediaReport', $socialMediaReport);
				$this->set('reviewReport', $reviewReport);
				$this->set('analyticsReport', $analyticsReport);
			}
			
		}
		
		if ($exportVersion) {
			exportToCsv('archived_report', $exportContent);
		} else {
			$this->set('searchInfo', $searchInfo);
			
			// if execution through cron job then just return the content to send through mail
			if (!empty($cronUserId)) {
				$this->set('cronUserId', $cronUserId);
			    return $this->getViewContent('report/archive');
			} else {
				
				// if pdf export
				if ($searchInfo['doc_type'] == "pdf") {
					exportToPdf($this->getViewContent('report/archive'), "overall_summary_$fromTimeShort-$toTimeShort.pdf");
				} else {
			    	$this->render('report/archive');
				}
			}
		}	
	}
	
    # func to get report settings data
	function getUserReportSettings($userId) {
		$userId = intval($userId);
		$sql = "select * from reports_settings where user_id=$userId";
		$repSetInfo = $this->db->select($sql, true);
		
		// if report settings are empty add default interval
		if (empty($repSetInfo)) {
		    $repSetInfo['user_id'] = $userId;
		    $repSetInfo['report_interval'] = SP_SYSTEM_REPORT_INTERVAL;
		    $repSetInfo['email_notification'] = SP_REPORT_EMAIL_NOTIFICATION;
		    $lastGeneratedDay = (SP_SYSTEM_REPORT_INTERVAL == 30) ? 1 : (date('d') - SP_SYSTEM_REPORT_INTERVAL);
		    $repSetInfo['last_generated'] = mktime(0, 0, 0, date('m'), $lastGeneratedDay, date('Y'));
		    $this->createUserReportSettings($repSetInfo);
		    $repSetInfo['id'] = $this->db->getMaxId('reports_settings');
		}
		
		return $repSetInfo;
	}
	
	# func to insert report settings
	function createUserReportSettings($setInfo) {
		$sql = "Insert into reports_settings(user_id,report_interval,email_notification,last_generated) 
				values({$setInfo['user_id']},{$setInfo['report_interval']},{$setInfo['email_notification']},'{$setInfo['last_generated']}')";
		$this->db->query($sql);
	}
	
	# func to update user report generate interval
	function updateUserReportSetting($userId, $col, $val) {
		$sql = "Update reports_settings set $col='".addslashes($val)."' where user_id=$userId";
		$this->db->query($sql);
	}
	
	# func to update user report generation logs
	function updateUserReportGenerationLogs($userId, $generateDate) {
		$dataList = array(
			'user_id|int' => $userId,
			'report_date' => $generateDate,
		);
		
		$this->dbHelper->insertRow("user_report_logs", $dataList);
	}
	
	# func to schedule reports
	function showReportsScheduler($success=false, $postInfo='') {
		$userId = isLoggedIn();
		
		if (isAdmin()) {
		    $userId = empty($postInfo['user_id']) ? $userId : $postInfo['user_id'];		    
		}
		
		$repSetInfo = $this->getUserReportSettings($userId);
		$this->set('repSetInfo', $repSetInfo);
		
		$reportInterval = !isset($postInfo['report_interval']) ? $repSetInfo['report_interval'] : $postInfo['report_interval'];		
		$this->set('reportInterval', $reportInterval);
		if ($reportInterval == 30) {
		    $nextGenTime = mktime(0, 0, 0, date('m') + 1, 1, date('Y'));
		} else {
		    $nextGenTime = $repSetInfo['last_generated'] + ( $repSetInfo['report_interval'] * 86400);    
		}		
		$this->set('nextReportTime', date('d M Y', $nextGenTime));
		
		$scheduleList = array(
			1 => $_SESSION['text']['label']['Daily'],
			2 => $this->spTextReport['2 Days'],
			7 => $_SESSION['text']['label']['Weekly'],
			30 => $_SESSION['text']['label']['Monthly'],
		);	
		
		$userCtrler = New UserController();
		$userList = $userCtrler->__getAllUsers();
		$this->set('userList', $userList);    
		
		$this->set('success', $success);
		$this->set('scheduleList', $scheduleList);		
		$this->render('report/reportscheduler');
	}
	
	# func to save Report Schedule
	function saveReportSchedule($info) {	    
		$userId = isAdmin() ? $info['user_id'] : isLoggedIn();		
		$repSetInfo = $this->getUserReportSettings($userId);
	    $this->updateUserReportSetting($userId, 'report_interval', $info['report_interval']);
	    $this->updateUserReportSetting($userId, 'email_notification', $info['email_notification']);
	    $this->showReportsScheduler(true, $info);
	}
	
	# func to check whether report can be generated for user
	function isGenerateReportsForUser($userId) {
		$genReport = false;
		$repSetInfo = $this->getUserReportSettings($userId);
		if ($repSetInfo['report_interval'] > 0) {
		    $lastGeneratedTime = $repSetInfo['last_generated'];
		    $currentDateTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		    if ($lastGeneratedTime < $currentDateTime) {
    		    // if monthly interval generate on first of each month
    		    if ($repSetInfo['report_interval'] == 30) {
    		        $genReport = (date('d') == 1) ? true : false;
    		    } else {		    
        			$nextGenTime = $lastGeneratedTime + ( $repSetInfo['report_interval'] * 86400);
        			$genReport = (time() > $nextGenTime) ? true : false;
    		    }    
		    }		    
		}
		$repSetInfo['generate_report'] = $genReport; 
		return $repSetInfo;
	}
		
	# function to sent Email Notification For ReportGeneration
	function sentEmailNotificationForReportGen($userInfo, $fromTime, $toTime) {
		$fromTime = !empty($fromTime) ? $fromTime : date('Y-m-d', strtotime('-1 days'));
		$searchInfo = array(
    	    'website_id' => 0,
            'report_type' => '',
            'from_time' => date('Y-m-d', $fromTime),
            'to_time' => date('Y-m-d', $toTime),
            'doc_type' => 'print',
        );
	    
	    $this->spTextPanel = $this->getLanguageTexts('panel', $_SESSION['lang_code']);
	    
        $reportContent = $this->showOverallReportSummary($searchInfo, $userInfo['id']);        
        $this->set('reportContent', $reportContent);
        
        $reportTexts = $this->getLanguageTexts('reports', $userInfo['lang_code']);
        $this->set('reportTexts', $reportTexts);
        $this->set('commonTexts', $this->getLanguageTexts('common', $userInfo['lang_code']));
        $this->set('loginTexts', $this->getLanguageTexts('login', $userInfo['lang_code']));
	    
		$name = $userInfo['first_name'].' '.$userInfo['last_name'];
		$this->set('name', $name);
		$subject = $reportTexts['report_email_subject'];
		$content = $this->getViewContent('email/emailnotificationreportgen');
		
		$userController =  New UserController();
		$adminInfo = $userController->__getAdminInfo();
		$adminName = $adminInfo['first_name']."-".$adminInfo['last_name'];
		$this->set('adminName', $adminName);
		
		if (!sendMail($adminInfo['email'], $adminName, $userInfo['email'], $subject, $content)) {
			echo 'An internal error occured while sending mail!';
		} else {
		    echo "Reports send successfully to ".$userInfo['email']."\n";
		}
	}
	
	# func to show user report generation logs
	function showReportGenerationLogs($searchInfo = '') {
	
		$userCtrler = New UserController();
		$fromTimeDate = !empty ($searchInfo['from_time']) ? addslashes($searchInfo['from_time']) : date('Y-m-d', strtotime('-1 days'));
		$toTimeDate = !empty ($searchInfo['to_time']) ? addslashes($searchInfo['to_time']) : date('Y-m-d');
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
		
		$userList = $userCtrler->__getAllUsersHavingWebsite();
		$this->set('userList', $userList);
		
		$userTypeCtrler = new UserTypeController();
		$typeList = $userTypeCtrler->__getAllUserTypeList();
		foreach ($typeList as $info) $userTypeList[$info['id']] = $info; 
		$this->set('userTypeList', $userTypeList);
		
		if (!empty($searchInfo['user_id'])) {
			$logUserList = array($userCtrler->__getUserInfo($searchInfo['user_id']));
			$this->set('userId', intval($searchInfo['user_id']));
		} else {
			$logUserList = $userList;
		}
		
		$this->set('logUserList', $logUserList);
		
		$whereCond = "report_date >='$fromTimeDate 00:00:00' and report_date<='$toTimeDate 23:59:59'";
		$whereCond .= !empty($searchInfo['user_id']) ? " and user_id=" . intval($searchInfo['user_id']) : "";
		$whereCond .= " order by report_date desc";
		$list = $this->dbHelper->getAllRows("user_report_logs", $whereCond, "id,user_id,date(report_date) as report_date");
		
		// loop through the list
		$logList = array();
		foreach ($list as $listInfo) {
			$logList[$listInfo['report_date']][$listInfo['user_id']] = 1;
		}
		
		$this->set('logDateList', getDateRange($fromTimeDate, $toTimeDate));
		$this->set('logList', $logList);
		$this->set('spTextUser', $this->getLanguageTexts('user', $_SESSION['lang_code']));
		$this->render('report/report_generation_logs');
	
	}
}
?>