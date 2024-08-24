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

# class defines all review base service configurations
class ReviewBase extends Controller {
    
    var $serviceList;
    
    function __construct() {
        $engineList = Spider::getCrawlEngineCategoryList("review");
    	$this->serviceList = [
    		"google" => [
    			"label" => "Google My Business",
    			"regex" => [
    			    "reviews" => $engineList['google']['regex1'],
    			    "rating" => $engineList['google']['regex2'],
    			],
    		    "url_part" => '?hl=en',
    		    'example' => ['https://www.google.com/search?q=kfc+Damrak'],
    		    'icon' => 'fab fa-google',
    		],
    	    /*"glassdoor" => [
    	        "label" => "Glassdoor",
    	        "regex" => [
    	            "reviews" => $engineList['glassdoor']['regex1'],
    	            "rating" => $engineList['glassdoor']['regex2'],
    	        ],
    		    'example' => ['https://www.glassdoor.com/Overview/Working-at-Google-EI_IE9079.11,17.htm']
    	    ],*/
    	    "yelp" => [
    	        "label" => "Yelp",
    	        "regex" => [
    	            "reviews" => $engineList['yelp']['regex1'],
    	            "rating" => $engineList['yelp']['regex2'],
    	        ],
    	        'example' => ['https://www.yelp.com/biz/intercontinental-singapore-singapore-2'],
    	        'icon' => 'fab fa-yelp',
    	    ],
    	    "trustpilot" => [
    	        "label" => "Trustpilot",
    	        "regex" => [
    	            "reviews" => $engineList['trustpilot']['regex1'],
    	            "rating" => $engineList['trustpilot']['regex2'],
    	        ],
    	        'example' => ['https://www.trustpilot.com/review/xohotels.com'],
    	        'icon' => 'fab fa-buffer',
    	    ],
    	    "tripadvisor" => [
    	        "label" => "Tripadvisor",
    	        "regex" => [
    	            "reviews" => $engineList['tripadvisor']['regex1'],
    	            "rating" => $engineList['tripadvisor']['regex2'],
    	        ],
    	        'example' => ['https://www.tripadvisor.com/Hotel_Review-g188098-d236186-Reviews-Hotel_Julen-Zermatt_Canton_of_Valais_Swiss_Alps.html'],
    	        'icon' => 'fab fa-tripadvisor',
    	    ],
    	];
    	
    	parent::__construct();
    }    
}
?>