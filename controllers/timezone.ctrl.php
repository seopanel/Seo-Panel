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
 * Class defines all details about managing time zones
 */
class TimeZoneController extends Controller {
	
	/**
	 * function to get all timezones used in the system
	 * @param string $where		The condition to filter the timezones
	 * @return Array 			Array contains details about timezone
	 */
	function __getAllTimezones($where=''){
		$sql = "select * from timezone";
		if (!empty($where)) $sql .= $where;
		$sql .= " order by id";
		$timezoneList = $this->db->select($sql);
		return $timezoneList;
	}
	
	
	/**
	 * function to get timezone information
	 * @param int $timezoneVal	The value of the timezone
	 * @param int $timezoneCol	The col name of the timezone
	 * @return Array		Contains all details about the  timezone
	 */
	function __getTimezoneInfo($timezoneVal, $timezoneCol = 'timezone_name') {
		$sql = "select * from timezone where $timezoneCol='".addslashes($timezoneVal)."'";
		$timezoneInfo = $this->db->select($sql, true);
		return $timezoneInfo;
	}
}
?>