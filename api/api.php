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

include_once("../includes/sp-load.php");
include_once(SP_CTRLPATH."/api.ctrl.php");
include_once(SP_CTRLPATH."/settings.ctrl.php");
include(SP_ABSPATH . "/api/api.functions.php");
$controller = New APIController();

$inputInfo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;

// sp demo enabled
if (SP_DEMO) {
	$returnInfo['response'] = 'Error';
	$returnInfo['error_msg'] = "API will not work in demo mode!";
} else if ($controller->verifyAPICredentials($inputInfo)) {

	$category = strtolower($inputInfo['category']);
	$action = $inputInfo['action'];

	// check for category and action values
	if (!empty($category) && !empty($action)) {

		// call api class with the action
		if (include(SP_ABSPATH . "/api/" . $category . ".api.php")) {
			$categortClassName = ucfirst($category) . "API";

			// check for class exists or not
			if (class_exists($categortClassName)) {
				$apiObj = new $categortClassName();

				// check action exists or not
				if (method_exists($apiObj, $action)) {
					$returnInfo = $apiObj->$action($inputInfo);
				} else {
					$returnInfo['response'] = 'Error';
					$returnInfo['error_msg'] = "Action is not supported!";
				}

			} else {
				$returnInfo['response'] = 'Error';
				$returnInfo['error_msg'] = "Category is not supported!";
			}

		} else {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "Invalid category passed!";
		}

	} else {
		$returnInfo['response'] = 'Error';
		$returnInfo['error_msg'] = "Invalid category or action!";
	}

} else {
	$returnInfo['response'] = 'Error';
	$returnInfo['error_msg'] = "API Authentication failed. Please provide valid API key and secret!";
}

// for debugging added below line
// debugVar($returnInfo);exit;

// encode as json and print
$out = json_encode($returnInfo);
print $out;
?>