<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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

# class defines all index controller functions
class IndexController extends Controller{
	
	# index function
	function index($searchInfo=''){		
		
		$spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
		$this->set('spTextHome', $spTextHome);
		if(isLoggedIn()){
			
			/*checkLoggedIn();
		    isHavingWebsite();
			$userId = isLoggedIn();
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
			
			$sql = "select * from websites w where status=1";
			
			// if admin user
			if (isAdmin()) {
			    $userCtrler = New UserController();
			    $userList = $userCtrler->__getAllUsersHavingWebsite();
			    $this->set('userList', $userList);
			    $webUserId = isset($searchInfo['user_id']) ? intval($searchInfo['user_id']) : $userList[0]['id'];
    			
    			// if user id is passed
    			if (!empty($webUserId)) {
    				$sql .= " and user_id=$webUserId";
    			}
    			
			    // if print method called
			    if ( ($searchInfo['doc_type'] == 'print') && !empty($webUserId)) {
				    $userInfo = $userCtrler->__getUserInfo($webUserId);
				    $this->set('userName', $userInfo['username']);
			    }
			    
			} else {
			    $webUserId = $userId;
			    $sql = "select * from websites w where user_id=$webUserId";
			}

			$pageScriptPath = "index.php?user_id=$webUserId";
			$this->set('webUserId', $webUserId);
			$info['pageno'] = intval($info['pageno']);
			
			// search for user name
			if (!empty($searchInfo['search_name'])) {
				$sql .= " and (w.name like '%".addslashes($searchInfo['search_name'])."%'
				or w.url like '%".addslashes($searchInfo['search_name'])."%')";
				$pageScriptPath .= "&search_name=" . $searchInfo['search_name'];
			}
			
			$sql .= " order by w.name";
			
			// pagination setup
			if (!in_array($searchInfo['doc_type'], array('export', 'pdf'))) {
				$this->db->query($sql, true);
				$this->paging->setDivClass('pagingdiv');
				$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
				$pagingDiv = $this->paging->printPages($pageScriptPath, "", "link");
				$this->set('pagingDiv', $pagingDiv);
				$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
				$this->set('pageNo', $info['pageno']);
			}
			
			$list = $this->db->select($sql);
			
			include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
			include_once(SP_CTRLPATH."/rank.ctrl.php");
			include_once(SP_CTRLPATH."/backlink.ctrl.php");
			$rankCtrler = New RankController();
			$backlinlCtrler = New BacklinkController();
			$saturationCtrler = New SaturationCheckerController();			
			$dirCtrler = New DirectoryController();
			
		    $fromTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		    $toTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			
			$websiteList = array();
			foreach($list as $listInfo){
				
				# rank reports
				$report = $rankCtrler->__getWebsiteRankReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];
				$listInfo['alexarank'] = empty($report['alexa_rank']) ? "-" : $report['alexa_rank']." ".$report['rank_diff_alexa'];
				$listInfo['mozrank'] = empty($report['moz_rank']) ? "-" : $report['moz_rank']." ".$report['rank_diff_moz'];
				
				# back links reports
				$report = $backlinlCtrler->__getWebsitebacklinkReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];
				$listInfo['google']['backlinks'] = empty($report['google']) ? "-" : $report['google']." ".$report['rank_diff_google'];
				$listInfo['alexa']['backlinks'] = empty($report['alexa']) ? "-" : $report['alexa']." ".$report['rank_diff_alexa'];
				$listInfo['msn']['backlinks'] = empty($report['msn']) ? "-" : $report['msn']." ".$report['rank_diff_msn'];
				
				# rank reports
				$report = $saturationCtrler->__getWebsiteSaturationReport($listInfo['id'], $fromTime, $toTime);
				$report = $report[0];				
				$listInfo['google']['indexed'] = empty($report['google']) ? "-" : $report['google']." ".$report['rank_diff_google'];
				$listInfo['msn']['indexed'] = empty($report['msn']) ? "-" : $report['msn']." ".$report['rank_diff_msn'];
				
				$listInfo['dirsub']['total'] = $dirCtrler->__getTotalSubmitInfo($listInfo['id']);
				$listInfo['dirsub']['active'] = $dirCtrler->__getTotalSubmitInfo($listInfo['id'], true);
				$websiteList[] = $listInfo;
			}
			
			// if export function called
			if ($exportVersion) {
				$exportContent .= createExportContent( array());
				$exportContent .= createExportContent( array());
				$exportContent .= createExportContent( array('', $spTextHome['Website Statistics'], ''));
				
				if ((isAdmin() && !empty($webUserId))) {				    
				    $exportContent .= createExportContent( array());				    
				    $exportContent .= createExportContent( array());
				    $userInfo = $userCtrler->__getUserInfo($webUserId);
				    $exportContent .= createExportContent( array($_SESSION['text']['common']['User'], $userInfo['username']));
				}
				
				$exportContent .= createExportContent( array());
				$headList = array(
					$_SESSION['text']['common']['Id'],
					$_SESSION['text']['common']['Website'],
					$_SESSION['text']['common']['MOZ Rank'],
					$_SESSION['text']['common']['Alexa Rank'],
					'Google '.$spTextHome['Backlinks'],
					'alexa '.$spTextHome['Backlinks'],
					'Bing '.$spTextHome['Backlinks'],
					'Google '.$spTextHome['Indexed'],
					'Bing '.$spTextHome['Indexed'],
					$_SESSION['text']['common']['Total'].' Submission',
					$_SESSION['text']['common']['Active'].' Submission',
				);
				$exportContent .= createExportContent( $headList);
				foreach ($websiteList as $websiteInfo) {
					$valueList = array(
						$websiteInfo['id'],
						$websiteInfo['url'],
						strip_tags($websiteInfo['mozrank']),
						strip_tags($websiteInfo['alexarank']),
						strip_tags($websiteInfo['google']['backlinks']),
						strip_tags($websiteInfo['alexa']['backlinks']),
						strip_tags($websiteInfo['msn']['backlinks']),
						strip_tags($websiteInfo['google']['indexed']),					
						strip_tags($websiteInfo['msn']['indexed']),
						$websiteInfo['dirsub']['total'],					
						$websiteInfo['dirsub']['active'],
					);
					$exportContent .= createExportContent( $valueList);
				}
				exportToCsv('website_statistics', $exportContent);
			} else {
							
				$this->set('websiteList', $websiteList);
				
				// if pdf export
				if ($searchInfo['doc_type'] == "pdf") {
					$fromTimeTxt = date('Y-m-d', $fromTime);
					$toTimeTxt = date('Y-m-d', $toTime);
					exportToPdf($this->getViewContent('user/userhome'), "account_summary_$fromTimeTxt-$toTimeTxt.pdf");
				} else {
					$layout = ($searchInfo['doc_type'] == "print") ? "ajax" : "";
					$this->set('searchInfo', $searchInfo);
					$this->render('user/userhome', $layout);
				}
				
			}*/			
			
			$this->render('user/userhome');
		}else{
			$this->render('home');
		}
	}
	
	# show login form
	function showLoginForm(){		
		$this->render('common/login');
	}
	
	# function to show support page
	function showSupport() {
		$this->set('spTextSupport', $this->getLanguageTexts('support', $_SESSION['lang_code']));
		$this->render('support');
	}
	
}
?>