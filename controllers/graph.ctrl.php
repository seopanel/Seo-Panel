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

# class defines all graph controller functions
class GraphController extends Controller {

	# function to show graph
	public function showKeywordPostionGraph($keywordId, $fromTimeDate,  $toTimeDate, $searchEngineId = '') {
		
		$conditions .= !empty($searchEngineId) ? " and s.searchengine_id=".intval($searchEngineId) : "";
		$sql = "select s.*,se.domain from searchresults s,searchengines se  
		where s.searchengine_id=se.id and result_date>='$fromTimeDate' and result_date<='$toTimeDate' 
		and s.keyword_id=" . intval($keywordId). "	$conditions order by s.result_date";
		$repList = $this->db->select($sql);
		
		// if reports not empty
		if (!empty($repList)) {
			
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
			
			$dataList = array();
			foreach($reportList as $repInfo){
				$seId = $repInfo['searchengine_id'];
				$dataList[$repInfo['result_date']][$seId] = $repInfo['rank'];
			}
			
			ksort($seList);
			$dataArr = "['Date', '" . implode("', '", array_values($seList)) . "']";
	
			// loop through data list
			foreach ($dataList as $dateVal => $dataInfo) {
				 
				$valStr = "";
				foreach ($seList as $seId => $seVal) {
					$valStr .= ", ";
					$valStr .= !empty($dataInfo[$seId])    ? $dataInfo[$seId] : 101;
				}
				 
				$dataArr .= ", ['$dateVal' $valStr]";
			}
			
			$this->set('reverseDir', true);
			$this->set('dataArr', $dataArr);
			$this->set('minValue', 1);
			$this->set('maxValue', 100);
			$this->set('graphTitle', $this->spTextKeyword["Keyword Position Report"]);
			
			return $this->getViewContent('report/graph');
			
		} else {
			return showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
		
	}

}
?>