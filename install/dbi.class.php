<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   		   *
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
class DBI{

	var $connectionId = false; 	# db connectio id
	var $error = false;   		# error while databse operations
	var $nonBlockingFailedCount = 0;	# statements that failed with $block=false (see importDatabaseFile())
	var $nonBlockingFailedSamples = array();
	
	function connectDatabase($dbServer, $dbUser, $dbPassword, $dbName){

		try {
			$this->connectionId = @mysqli_connect($dbServer, $dbUser, $dbPassword, $dbName);

			if (!$this->connectionId){
				$this->error = true;
				// bug fix: this always showed the same generic message
				// regardless of the real cause (wrong password, unknown
				// database, unreachable host) - the target user here
				// (shared hosting, no SSH, phpMyAdmin at best) has no
				// other way to self-diagnose which one it is.
				// mysqli_connect_error() is server-generated text, not
				// attacker input, so safe to surface directly.
				$error = "Database connection failed: " . mysqli_connect_error();
				return $error;
			}

			return true;
		} catch (Exception $e) {
			$this->error = true;
			return "Database Error: " . $e->getMessage();
		}
	}

	# func to Execute a general mysql query
	function query($query, $noRows=false){
		
		$res = @mysqli_query($this->connectionId, $query);
		
		if (empty($res)){
			return $this->getError();
		}
		
		return $res;
	}
	
    #func to execute a select query
	function select($query, $fetchFirst = false){
		$res = @mysqli_query($this->connectionId, $query);
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

	# func to Display the Mysql error
	function getError(){
		if (@mysqli_errno($this->connectionId) != 0) {			
			$this->error = true;
			$error =  "Mysql Error: " . @mysqli_error($this->connectionId);
		}
		return $error;
	}
	
	// $onProgress, if given, is called periodically as callable($linesDone, $totalLines) -
	// lets the caller stream a progress bar update to the browser during a large import
	// (see Install::importWithProgress()) without changing anything about how the file
	// itself is parsed/executed.
	function importDatabaseFile($filename, $block=true, $onProgress=null){

		# temporary variable, used to store current query
		$tmpline = '';

		# read in entire file
		$lines = file($filename);
		$totalLines = count($lines);

		// bug fix: with $block=false (the upgrade path - a replayed
		// migration can legitimately fail on a statement that already
		// applied, so the whole chain must keep going), every failed
		// statement was previously discarded completely - no log, no
		// count, nothing - and proceedUpgrade() always rendered
		// "Upgraded successfully" regardless. Accumulated onto $this
		// (not a local var) since this method runs once per migration
		// file, across many files in one upgrade run - the caller reads
		// the running total off $db after the whole chain finishes. Each
		// failure is also error_log()'d immediately (independent of
		// $block), same fix shape as MysqliHelper::showError() in the
		// main app.

		# loop through each line
		foreach ($lines as $lineIndex => $line){

			# skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
				continue;

			# add this line to the current segment
			$tmpline .= $line;

			# if it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';'){

				if(!empty($tmpline)){
					$errMsg = $this->query($tmpline);
					if ($this->error) {
						error_log("SEO Panel installer: statement failed in $filename: $errMsg | statement: " . trim($tmpline));
						if ($block) return $errMsg;
						$this->nonBlockingFailedCount++;
						if (count($this->nonBlockingFailedSamples) < 5) {
							$this->nonBlockingFailedSamples[] = $errMsg;
						}
						// query() leaves $this->error=true for the REST of
						// this object's lifetime (getError() never resets
						// it) - clear it back to false so a later,
						// genuinely successful statement isn't misreported
						// as having failed too
						$this->error = false;
					}
				}
				$tmpline = '';
			}

			// throttled to every 25 lines - frequent enough to feel live,
			// infrequent enough that the flush() overhead never dominates
			if ($onProgress !== null && ($lineIndex % 25 === 0 || $lineIndex === $totalLines - 1)) {
				call_user_func($onProgress, $lineIndex + 1, $totalLines);
			}
		}
	}
}
?>