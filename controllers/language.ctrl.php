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

# class defines all language controller functions
class LanguageController extends Controller{
	
	# func to get all Languages
	function __getAllLanguages($where=''){
		$sql = "select * from languages";
		if (!empty($where)) $sql .= $where;
		$sql .= " order by lang_name";
		$langList = $this->db->select($sql);
		return $langList;
	}

	# fun to create resdirect url
	function getRedirectUrl() {
		$currUrl = getCurrentUrl();
		if (!stristr($currUrl, '?')) {
			$currUrl .= "?";
		}
		
		$currUrl = preg_replace('/&lang_code=\w{2}$|&lang_code=\w{2}&/i', '', $currUrl, 1, $count);
		return $currUrl;
	}
	
	# func to get language info
	function __getLanguageInfo($langCode) {
		$sql = "select * from languages where lang_code='$langCode'";
		$langInfo = $this->db->select($sql, true);
		return $langInfo;
	}
}
?>