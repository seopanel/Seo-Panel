<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
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
 * Class defines all functions for managing search engine API
 * 
 * @author Seo panel
 *
 */
class SearchengineAPI extends Seopanel{
	
	/**
	 * the main controller to get details for api
	 * @var Object
	 */
	var $ctrler;
	
	/**
	 * The list contains search engine details
	 * @var Array
	 */
	var $seList;
	
	/**
	 * The constructor of API
	 */
	function __construct() {
		$this->ctrler = new SearchEngineController();
	}	
	
	/**
	 * function to get all search engines
	 * @param Array $info			The input details to process the api
	 * 		$info['status']  		The status of the search engine
	 * 		$info['search']  		The search keyword
	 * @return Array $returnInfo  	Contains informations about keyword
	 */
	function getSearchengines($info) {
	    $returnInfo = array();
	    $resultList = $this->ctrler->__searchSerachEngines($info);
        $returnInfo['response'] = 'success';
        $returnInfo['result'] = $resultList;
        return $returnInfo;
	}
	
}
?>