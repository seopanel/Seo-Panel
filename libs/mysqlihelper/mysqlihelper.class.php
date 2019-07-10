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
class MysqliHelper extends Database{

	var $connectionId = false;
	var $debugMode;
	var $rowsAffected;
	var $lastInsertId;
	var $noRows = 0;

	// constructor
	function MysqliHelper($dbServer, $dbUser, $dbPassword, $dbName, $debug){
		global $SP_DB_CONN_OBJ;
		$this->setDebugMode($debug);
		
		// check connection id existing and it is a resource
		if (!empty($SP_DB_CONN_OBJ) && is_object($SP_DB_CONN_OBJ) ) {
		    $this->connectionId =  $SP_DB_CONN_OBJ;
		} else {
			
			$dbServer = SP_DB_PERSISTENT_CONNECTION ? "p:$dbServer" : $dbServer;
			$this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);
    		
    		if (!$this->connectionId){
    			$this->showError();			
    			showErrorMsg("<p style='color:red'>Database connection failed!<br>Please check your database settings!</p>");
    		} else {	
    		    $this->query( "SET NAMES utf8");
    		    $SP_DB_CONN_OBJ = $this->connectionId;
    		}
    		
		}		
		
	}

	// func to execute a select query
	function select($query, $fetchFirst = false){

        $dbName = DB_NAME;
        $dbUser = DB_USER;
        $dbPassword = DB_PASSWORD;
        $dbServer = DB_HOST;

        if( $this->connectionId ) {
            if( @mysqli_ping($this->connectionId) ) {} else {
                $this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);
            }
        } else {
            $this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);
        }

		$res = mysqli_query($this->connectionId, $query);
		$this->showError();
		
		if (!$res){
			return false;
		}
		
		$returnArr = array();
		while ($row = mysqli_fetch_assoc($res)){
			$returnArr[] = $row;
		}
		
		mysqli_free_result($res);
		
		if ($fetchFirst){
			return $returnArr[0];
		}
		
		return $returnArr;
	}

	// func to Execute a general mysql query
	function query($query, $noRows=false){

        $dbName = DB_NAME;
        $dbUser = DB_USER;
        $dbPassword = DB_PASSWORD;
        $dbServer = DB_HOST;

        if( $this->connectionId ) {
            if( @mysqli_ping($this->connectionId) ) {} else {
                $this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);
            }
        } else {
            $this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);
        }

		$res = @mysqli_query($this->connectionId, $query);
		
		if ($res){
			$this->rowsAffected = @mysqli_affected_rows($this->connectionId);
			$this->lastInsertId = @mysqli_insert_id($this->connectionId);
		} else {
			$this->showError();
			@mysqli_free_result($res);
		}
		
		if($noRows) $this->noRows = mysqli_num_rows($res);
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
		
		// if debugmode enabled
		if ($this->debugMode) {
			
			// if connection is failed
			if (!$this->connectionId) {
				echo "Error: Unable to connect to Database." . PHP_EOL;
				echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
				echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
				exit;
			} else if (@mysqli_errno($this->connectionId) != 0) {
				echo "Script Halted. \n Mysql Error Number: " . @mysqli_errno($this->connectionId) . "\n" . @mysqli_error($this->connectionId);
				$this->close();
				exit;
				
			}
			
		}
		
	}

	# func to escape mysql string
	function escapeMysqlString($str){
		return mysqli_escape_string($this->connectionId, $str);
	}

	# func to Close Mysql Connection
	function close(){
		$res = @mysqli_close($this->connectionId);
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