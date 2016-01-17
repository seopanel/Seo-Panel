<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seofreetools.net)  	   *
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

include_once("includes/sp-load.php");
if (empty($_SERVER['REQUEST_METHOD'])) {

    # the section for generate reports using system cron job	
    include_once(SP_CTRLPATH."/cron.ctrl.php");
    $controller = new CronController();
        
    include_once(SP_CTRLPATH."/directory.ctrl.php");
    $dirCtrler = new DirectoryController();
        
    $searchInfo = array(
        //'working' => 1,   # to check active directories
        //'working' => 0,	# to check inactive directories
        'checked' => 0,   # to check unchecked directories
    );
    $dirList = $dirCtrler->getAllDirectories($searchInfo);
    
    $dirCtrler->checkPR = 1; // check pagerank of directory or not
    foreach ($dirList as $dirInfo) {
        $dirCtrler->checkDirectoryStatus($dirInfo['id']);
        echo "sleep for ".SP_CRAWL_DELAY. " seconds";
        sleep(SP_CRAWL_DELAY);
    }
} else {
    showErrorMsg("<p style='color:red'>You don't have permission to access this page!</p>");
}
