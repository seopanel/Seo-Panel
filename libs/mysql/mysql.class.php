<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   *
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

# class defines all mysql functions
class Mysql extends Database{

	var $connectionId = false;
	var $debugMode;
	var $rowsAffected;
	var $lastInsertId;
	var $noRows = 0;

	# constructor
	function Mysql($dbServer, $dbUser, $dbPassword, $dbName, $debug){
		$this->setDebugMode($debug);
		
		// check connection id existing and it is a resource
		if (defined('SP_DB_CONN_ID') && is_resource(SP_DB_CONN_ID) ) {
		    $this->connectionId =  SP_DB_CONN_ID;
		} else {
		    
    		// if mysql persistent connection enabled
    		if (SP_DB_PERSISTENT_CONNECTION) {
    		    $this->connectionId = @mysql_pconnect($dbServer, $dbUser, $dbPassword, true);
    		} else {
    		    $this->connectionId = @mysql_connect($dbServer, $dbUser, $dbPassword, true);
    		}
    		
    		if (!$this->connectionId){
    			$this->showError();			
    			showErrorMsg("<p style='color:red'>Database connection failed!<br>Please check your database settings!</p>");
    		} else {
    		    $this->selectDatabase($dbName);				
    		    $this->query( "SET NAMES utf8");
    		    define('SP_DB_CONN_ID', $this->connectionId);
    		}
		}		
		
	}

	# func to select database
	function selectDatabase($dbName){
		$res = @mysql_select_db($dbName, $this->connectionId);
		$this->showError();
		if(mysql_errno() != 0){
			showErrorMsg("<p style='color:red'>Database connection failed!<br>Please check your database settings!</p>");
		}
		return $res;
	}

	#func to execute a select query
	function select($query, $fetchFirst = false){
		$res = mysql_query($query, $this->connectionId);
		$this->showError();
		if (!$res){
			return false;
		}
		$returnArr = array();
		while ($row = mysql_fetch_assoc($res)){
			$returnArr[] = $row;
		}
		mysql_free_result($res);
		if ($fetchFirst){
			return $returnArr[0];
		}
		return $returnArr;
	}

	# func to Execute a general mysql query
	function query($query, $noRows=false){
		$res = @mysql_query($query, $this->connectionId);
		if ($res){
			$this->rowsAffected = @mysql_affected_rows($this->connectionId);
			$this->lastInsertId = @mysql_insert_id($this->connectionId);
		}else{
			$this->showError();
			@mysql_free_result($res);
		}
		if($noRows) $this->noRows = mysql_num_rows($res);
		return $res;
	}

	# func to get max id of a table
	function getMaxId($table, $col='id') {
		$sql = "select max($col) maxid from $table";
		$maxInfo = $this->select($sql);
		return empty($maxInfo[0]['maxid']) ? 1 : $maxInfo[0]['maxid'];
	}

	# func to Sets the dubug mode for mysql access
	function setDebugMode($debug){
		$this->debugMode = $debug ? true: false;
		return;
	}

	# func to Display the Mysql error
	function showError(){
		if ($this->debugMode && @mysql_errno() != 0) {
			echo "Script Halted. \n Mysql Error Number: " . @mysql_errno() . "\n" . @mysql_error();
			$this->close();
			exit();
		}
		return;
	}

	# func to escape mysql string
	function escapeMysqlString($str){
		return mysql_escape_string($str);
	}

	# func to Close Mysql Connection
	function close(){
		$res = @mysql_close($this->connectionId);
		return $res;
	}
	
	function importDatabaseFile($filename){
		
		# temporary variable, used to store current query
		$tmpline = '';
		
		# read in entire file
		$lines = file($filename);
		
		# loop through each line
		foreach ($lines as $line){
			
			# skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
				continue;
		 
			# add this line to the current segment
			$tmpline .= $line;
			
			# if it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';'){
				
				if(!empty($tmpline)){
					$this->query($tmpline);
				}
				$tmpline = '';
			}
		}
		
	}
}
?>