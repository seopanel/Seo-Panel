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

# class defines all currency controller functions
class CurrencyController extends Controller{
	
	# func to get all currency
	function __getAllCurrency($where = ''){
		$sql = "select * from currency where 1=1";
		if (!empty($where)) $sql .= $where;
		$sql .= " order by name";
		$currencyList = $this->db->select($sql);
		return $currencyList;
	}
	
	# func to get currency info
	function __getCurrencyInfo($currencyCode) {
		$sql = "select * from currency where iso_code='".addslashes($currencyCode)."'";
		$currencyInfo = $this->db->select($sql, true);
		return $currencyInfo;
	}
	
	# function get currency code map list
	function getCurrencyCodeMapList() {
		$allList = $this->__getAllCurrency();
		$currencyList = array();
		
		// loop through currency
		foreach ($allList as $currInfo) {
			$isoCode = $currInfo['iso_code'];
			$currencyList[$isoCode]['symbol'] = $currInfo['symbol'];
			$currencyList[$isoCode]['unicode'] = $currInfo['unicode'];
			$currencyList[$isoCode]['position'] = $currInfo['position'];
		}
		
		return $currencyList;
	}
	
}
?>