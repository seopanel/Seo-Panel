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

# class defines all database functions
class Database{
	
	var $dbEngine;
	var $dbConObj;
	
    # constructor
    function __construct($dbEngine='mysql') {
    	
    	// if db engine is mysql
    	if ($dbEngine == 'mysql') {
    		$this->dbEngine = function_exists('mysqli_query') ? "mysqlihelper" : $dbEngine;
    	} else {
    		$this->dbEngine = $dbEngine;
    	}
    	
    }
    
	# func to connect db enine
    function dbConnect(){
    	include_once(SP_LIBPATH."/".$this->dbEngine."/".$this->dbEngine.".class.php");
    	$this->dbConObj = New $this->dbEngine(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, SP_DEBUG);
    	return $this->dbConObj;
    }
    
    /**
     * function get a db table row information
     * @param String $table			The name of the table
     * @param String $whereCond		The where condition string to get the results
     * @param String $cols 			The db columns needs to be retrieved
     */
    public function getRow($table, $whereCond = '1=1', $cols = '*') {
    	$whereCond = !empty($whereCond) ? $whereCond : '1=1';
    	$sql = "SELECT $cols FROM $table WHERE $whereCond";
    	$rowInfo = $this->dbConObj->select($sql, true);
    	return $rowInfo;
    }
    
    /**
     * function get all db table rows according to where condition
     * @param String $table			The name of the table
     * @param String $whereCond		The where condition string to get the results
     * @param String $cols 			The db columns needs to be retrieved
     */
    public function getAllRows($table, $whereCond = '1=1', $cols = '*') {
    	$whereCond = !empty($whereCond) ? $whereCond : '1=1';
    	$sql = "SELECT $cols FROM $table WHERE $whereCond";
    	$rowList = $this->dbConObj->select($sql);
    	return $rowList;
    }
    
    /**
     * Function to delete db table row according to the where condition
     * @param String $table			The name of the table
     * @param String $whereCond		The where condition string to get the results
     */
    public function deleteRows($table, $whereCond = '1=1') {
    	
    	$whereCond = !empty($whereCond) ? $whereCond : '1=1';
    	$sql = "Delete FROM $table WHERE $whereCond";
    		
    	// if no error occured
    	if ($this->dbConObj->query($sql)) {
    		return TRUE;
    	}
    
    	return FALSE;
    }
    
    /**
     * function to insert row to a db table
     * @param string $table			The name of the table
     * @param Array $dataList		The data list array with key as db column and value as db column insert value
     * 								Eg: array('name' => 'Tom', 'status' => 1)
     */
    public function insertRow($table, $dataList) {

    	$dataList = self::escapeValue($dataList);
    	$colList = array_keys($dataList);
    	$valueList = array_values($dataList);
    	$sql = "INSERT into $table(" . implode(',', $colList) . ") values('" . implode("', '", $valueList) . "')";
    	
    	// if no error occured
    	if ($this->dbConObj->query($sql)) {
    		return TRUE;
    	}
    
    	return FALSE;
    }
    
    /**
     * function to update row of a db table
     * @param string $table			The name of the table
     * @param Array $dataList		The data list array with key as db column and value as db column insert value
     * 								Eg: array('name' => 'Tom', 'status' => 1)
     * @param String $whereCond		The where condition string to get the results
     */
    public function updateRow($table, $dataList, $whereCond = '1=1') {

    	$dataList = self::escapeValue($dataList);
    	$whereCond = !empty($whereCond) ? $whereCond : '1=1';
    	$sql = "Update $table SET ";
    		
    	// for loop through values
    	foreach ($dataList as $key => $value) {
    		$sql .= "$key='$value',";
    	}
    		
    	$sql = rtrim($sql, ",");
		$sql .= " WHERE $whereCond";
    		
    	// if no error occured
    	if ($this->dbConObj->query($sql)) {
    		return TRUE;
    	}
    
    	return FALSE;
    }

    /**
     * function to escape db values to be inserted
     * @param Mixed $dataList		the array contains valus as key => $value
     */
    public static function escapeValue($dataList) {
    	$escDataList = array();
    	
    	// loop through the data list
    	foreach ($dataList as $key => $value) {
    	
	    	// check whether type passed
	    	if (stristr($key, '|')) {
	    		list($key, $type) = explode("|", $key);
	    	} else {
	    		$type = "string";
	    	}
	    	 
	    	switch ($type) {
	    
	    		case "float":
	    			$value = floatval($value);
	    			break;
	
	    		case "number":
	    		case "integer":
	    		case "int":
	    			$value = intval($value);
	    			break;
	    
	    		case "text":
	    		case "string":
	    		default:
	    			$value = addslashes($value);
	    			break;
	    			 
	    	}
	    	
	    	$escDataList[$key] = $value;
    	}
    	
    	return $escDataList;
    	 
    }
    
    /**
     * function to escape data
     * @param mixed $value		To be escaped
     * @param string $type		The data type[float|number|integer|int|text|string]
     */
    public static function escapeData($value, $type) {
    	$dataList = array("data|$type" => $value);
    	$dataList = self::escapeValue($dataList);
    	return $dataList['data'];
    }

}
?>