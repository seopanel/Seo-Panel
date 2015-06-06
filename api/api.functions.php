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

/**
 * function to get from time
 * @param Array $info API input details
 * @return $fromTime	The timestamp of from time
 */
function getFromTime($info) {

	// if from time is not empty
	if (!empty($info['from_time'])) {
		$fromTime = strtotime($info['from_time'] . ' 00:00:00');
	} else {
		$fromTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
	}

	return $fromTime;;
}

/**
 * function to get to time
 * @param Array $info API input details
 * @return $fromTime	The timestamp of to time
 */
function getToTime($info) {

	// if from time is not empty
	if (!empty($info['to_time'])) {
		$toTime = strtotime($info['to_time'] . ' 00:00:00');
	} else {
		$toTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}

	return $toTime;;
}

/**
 * function to remove braces from string
 * @param String $str		The string to be replaced
 * @return mixed $str		The converted string
 */
function removeBraces($str) {
	$str = str_replace(array('(', ')'), '', $str);
	return $str;
}
?>