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

# class defines all download controller functions
class DownloadController extends Controller{
	
	function downloadFile($fileInfo){

		if ($fileName = $this->isValidFile($fileInfo['file'])) {

			$fileType = $fileInfo['filetype'];
			$fileSec = $fileInfo['filesec'];
			switch($fileSec) {
				case "sitemap":
				default:
					// IDOR fix: previously read straight from the shared
					// SP_TMPPATH root with no ownership check at all - any
					// logged-in user could download any other user's
					// sitemap just by guessing/knowing its filename.
					// SitemapController now writes every sitemap under a
					// per-user subdirectory (see its constructor); scoping
					// the read to THIS caller's own subdirectory - derived
					// from their own session, never from $fileInfo/client
					// input - means a filename they don't own simply
					// doesn't exist at the path they're allowed to read.
					$userId = intval(isLoggedIn());
					$baseDir = SP_TMPPATH."/sitemap/".$userId;
					$file = $baseDir."/".$fileName;
					break;
			}

			// Defense in depth on top of isValidFile()'s blocklist (a
			// blocklist is inherently fragile - str_replace()-ing out
			// '../'/'./'/'..' once doesn't catch every shape, e.g. a
			// literal null byte or a platform-specific separator quirk),
			// with a real canonicalization + prefix check: resolve both
			// paths with realpath() (following symlinks, collapsing any
			// remaining '..' segments) and verify the target actually
			// lives inside the caller's own directory. The trailing
			// separator on $realBaseDir matters - without it, a sibling
			// directory that merely SHARES the base dir's name as a
			// prefix (e.g. "sitemap/5-evil" against a naive "sitemap/5"
			// check) would wrongly pass.
			$realBaseDir = realpath($baseDir);
			$realFile = realpath($file);
			if ($realBaseDir === false || $realFile === false || strpos($realFile, $realBaseDir . DIRECTORY_SEPARATOR) !== 0) {
				echo "<font style='color:red;'>You are not allowed to access this file!</font>";
				exit;
			}
			$file = $realFile;

			// Set appropriate Content-Type based on file type
			if ($fileType == 'gz' || pathinfo($fileName, PATHINFO_EXTENSION) == 'gz') {
				header("Content-type: application/gzip;\n");
			} else {
				header("Content-type: application/$fileType;\n");
			}

			header("Content-Transfer-Encoding: binary");
			$len = filesize($file);
			header("Content-Length: $len;\n");
			header("Content-Disposition: attachment; filename=\"$fileName\";\n\n");

			ob_clean();
	    	flush();
			readfile($file);
		} else {
			echo "<font style='color:red;'>You are not allowed to access this file!</font>";
			exit;
		}
	}
	
	# function to check whether valid file
	function isValidFile($fileName) {
		$fileName = urldecode($fileName);
		$fileName = str_replace(array('../', './', '..'), '', $fileName);

		// check its any system file
		if ($fileName[0] == '/') {
			return false;
		}

		// allow only these file format (including compressed files)
		if (preg_match('/\.xml$|\.xml\.gz$|\.gz$|\.html$|\.txt$/i', $fileName)) {
			return $fileName;
		}

		return false;
	}
}
?>
