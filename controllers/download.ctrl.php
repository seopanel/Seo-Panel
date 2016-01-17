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

# class defines all download controller functions
class DownloadController extends Controller
{
    
    public function downloadFile($fileInfo)
    {
        if ($fileName = $this->isValidFile($fileInfo['file'])) {
            $fileType = $fileInfo['filetype'];
            $fileSec = $fileInfo['filesec'];
            switch ($fileSec) {
                
                case "sitemap":
                    $file = SP_TMPPATH."/".$fileName;
                    break;
            }
            
            header("Content-type: application/$fileType;\n");
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
    public function isValidFile($fileName)
    {
        $fileName = urldecode($fileName);
        $fileName = str_replace(array('../', './', '..'), '', $fileName);
        
        // check its any system file
        if ($fileName[0] == '/') {
            return false;
        }
        
        // allow only these file format
        if (preg_match('/\.xml$|\.html$|\.txt$/i', $fileName)) {
            return $fileName;
        }
                
        return false;
    }
}
