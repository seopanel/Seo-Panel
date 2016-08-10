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

/**
 * class defines all information controller functions
 * @author Seo Panel
 *
 */
class InformationController extends Controller {

	/**
	 * function to show news boxes
	 */
	function showNews($secInfo='') {
		
		// switch trhough sections
		switch($secInfo['sec_name']){
				
			default:
				
				// get today's information
				$ret = $this->__getTodayInformation();
				
				// if empty fetch directly from website
				if (!isset($ret['page'])) {
					
					// get content directly from website
					$ret = $this->spider->getContent(SP_NEWS_PAGE . "?lang=". $_SESSION['lang_code'], true, false);
					
					// update in db
					$this->updateTodayInformation($ret['page']);
					
				}
	
				// check whether it contains required data
				if (!empty($ret['page']) && stristr($ret['page'], "id='news_info'")) {					
					$this->set('newsContent', stripslashes($ret['page']));
					$this->render('common/topnewsbox', 'ajax');
				}
		}
	
	}

	
	/**
	 * function to get sponsors
	 */
	function getSponsors() {
		
		// get today's information
		$ret = $this->__getTodayInformation('sponsors');
		
		// if empty fetch directly from website
		if (!isset($ret['page'])) {
			
			// get content directly from website
			$ret = $this->spider->getContent(SP_SPONSOR_PAGE . "?lang=". $_SESSION['lang_code'], true, false);
				
			// update in db
			$this->updateTodayInformation($ret['page'], 'sponsors');
				
		}
		
		// check whether it contains required data
		if (!empty($ret['page']) && stristr($ret['page'], 'class="contentmid"')) {
			return 	$ret['page'];
		} else {
			return false;
		}
		
	}
	
	/**
	 * function to update news in database
	 */
	function updateTodayInformation($content, $secName = 'news') {
		
		$todayDate = date('Y-m-d');
		$sql = "delete from information_list where info_type='" . addslashes($secName) . "'";
		$this->db->query($sql);
		
		$sql = "insert into information_list(info_type, content, update_date) 
		values('" . addslashes($secName) . "', '" . addslashes($content) . "', '{$todayDate}')";
		$this->db->query($sql);
		
	}
	
	/**
	 * function to get todays information
	 */
	function __getTodayInformation($secName = 'news') {
		$sql = "select info_type, content as page from information_list where info_type='" . addslashes($secName) . "'
		and update_date='" . date('Y-m-d') . "'";
		$info = $this->db->select($sql, true);
		return $info;
	}
	
}
?>