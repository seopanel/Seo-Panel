<?php
/**************************************************************************
*   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	          *
*   sendtogeo@gmail.com   											      *
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
 * Class defines all user token controller functions
 */
class UserTokenController extends Controller {
	
	/*
	 * function to get user token for a application
	 */
	function getUserToken($userId, $category = 'google') {
		$category = addslashes($category);
		$userId = intval($userId);
		$whereCond = "user_id=$userId and token_category='$category' order by created DESC";
		$tokenInfo = $this->dbHelper->getRow("user_tokens", $whereCond);
		return $tokenInfo;
	}
	
	/*
	 * function to insert user token
	 */
	function insertUserToken($tokenInfo) {
		$ret = $this->dbHelper->insertRow("user_tokens", $tokenInfo);
		return $ret;
	}
	
	/*
	 * function to update user token
	 */
	function updateUserToken($tokenId, $tokenInfo) {
		$whereCond = "id=" . intval($tokenId);
		$ret = $this->dbHelper->updateRow("user_tokens", $tokenInfo, $whereCond);
		return $ret;
	}
	
	/*
	 * function to delete user token
	 */
	function deleteToken($tokenId) {
		$whereCond = "id=" . intval($tokenId);
		$ret = $this->dbHelper->deleteRows("user_tokens", $whereCond);
		return $ret;
	}
	
	/*
	 * function to delete all user token
	 */
	function deleteAllUserTokens($userId, $category = 'google') {
		$category = addslashes($category);
		$userId = intval($userId);
		$whereCond = "user_id=$userId and token_category='$category'";
		$ret = $this->dbHelper->deleteRows("user_tokens", $whereCond);
		return $ret;
	}
	
}
?>