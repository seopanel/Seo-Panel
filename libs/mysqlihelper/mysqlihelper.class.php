<?php

/* * *************************************************************************
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
 * ************************************************************************* */

include_once 'MysqliDb.php';

# class defines all mysql functions

class MysqliHelper extends Database {

    var $connectionId = false;
    var $debugMode;
    var $rowsAffected;
    var $lastInsertId;
    var $noRows = 0;

    # constructor

    function MysqliHelper($dbServer, $dbUser, $dbPassword, $dbName, $debug) {
        global $sp_db;
        $this->setDebugMode($debug);
        if(!is_a($sp_db, 'MysqliDb')){
            $sp_db = new MysqliDb($dbServer, $dbUser, $dbPassword, $dbName);
        }
        
        try {
            $sp_db->mysqli();
        } catch (Exception $exc) {
            if ($this->debugMode) {
                $this->showException($exc);
            }
            showErrorMsg("<p style='color:red'>Database connection failed!<br>Please check your database settings!</p>");
        }
        $sp_db->rawQuery("SET NAMES utf8");

    }

    # func to select database

    function selectDatabase($dbName) {
        $res = @mysql_select_db($dbName, $this->connectionId);
        $this->showError();
        if (mysql_errno() != 0) {
            showErrorMsg("<p style='color:red'>Database connection failed!<br>Please check your database settings!</p>");
        }
        return $res;
    }

    #func to execute a select query

    function select($query, $fetchFirst = false) {
        global $sp_db;
        try{
            $res = $sp_db->rawQuery($query);
            if (!is_array($res)) {
                return false;
            }
            if ($fetchFirst) {
                return $res[0];
            }
            return $res;
        } catch (Exception $ex) {
            $this->showException($ex);
        }
    }

    # func to Execute a general mysql query

    function query($query, $noRows = false) {
        global $sp_db;
        try{
            $res = $sp_db->rawQuery($query);
            if (is_array($res)) {
                $this->rowsAffected = $sp_db->count;
                $this->lastInsertId = $sp_db->getInsertId();
            }else{
                return false;
            }
            if ($noRows){
                $this->noRows = $sp_db->count;
            }
            return $res;
        } catch (Exception $ex) {
            $this->showException($ex);
        }
    }

    # func to get max id of a table

    function getMaxId($table, $col = 'id') {
        $sql = "select max($col) maxid from $table";
        $maxInfo = $this->select($sql);
        return empty($maxInfo[0]['maxid']) ? 1 : $maxInfo[0]['maxid'];
    }

    # func to Sets the dubug mode for mysql access

    function setDebugMode($debug) {
        $this->debugMode = $debug ? true : false;
        return;
    }

    # func to Display the Mysql error

    function showError() {
        global $sp_db;
        $last_error_no = $sp_db->getLastErrno();
        if ($this->debugMode  && isset($last_error_no) && $last_error_no != 0) {
            echo "Script Halted. \n Mysql Error Number: " . $last_error_no . "\n" . $sp_db->getLastError();
            $this->close();
            exit();
        }
        return;
    }

    # func to Display the Mysql error

    function showException($exc) {
        global $sp_db;
        if ($this->debugMode  && $exc->getCode() != 0) {
                echo "Script Halted. \n Mysql Error Number: " . $exc->getCode() . "\n" . $exc->getMessage();
                $this->close();
                exit();
        }
        return;
    }

    # func to escape mysql string

    function escapeMysqlString($str) {
        return mysql_escape_string($str);
    }

    # func to Close Mysql Connection

    function close() {
        global $sp_db;
        $res = $sp_db->__destruct();
        return $res;
    }

    function importDatabaseFile($filename) {

        # temporary variable, used to store current query
        $tmpline = '';

        # read in entire file
        $lines = file($filename);

        # loop through each line
        foreach ($lines as $line) {

            # skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            # add this line to the current segment
            $tmpline .= $line;

            # if it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {

                if (!empty($tmpline)) {
                    $this->query($tmpline);
                }
                $tmpline = '';
            }
        }
    }

}

?>