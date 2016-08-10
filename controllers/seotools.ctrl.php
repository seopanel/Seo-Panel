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

# class defines all seo tools controller functions
class SeoToolsController extends Controller{
	var $layout = 'ajax';	
	
	# index function
	function index($info=''){
		$this->layout = "default";
		if(isAdmin()){
			$sql = "select * from seotools where status=1";	
		}else{
			$sql = "select * from seotools where status=1 and user_access=1";			
		}
		$sql .= " order by id";
		
		$menuList = $this->db->select($sql);
		if(count($menuList) <= 0){
			$this->set('msg', $_SESSION['text']['common']['noactivetools']);
			$this->render('common/notfound');
			exit;
		}
		
		$this->set('menuList', $menuList);
		$defaultArgs = empty($info['default_args']) ? "" : urldecode($info['default_args']);
		switch($info['menu_sec']){
			
			case "sitemap-generator":
				$defaultScript = "sitemap.php";
				break;
				
		    case "site-auditor":
				$defaultScript = "siteauditor.php";
				break;
				
			case "rank-checker":
				$defaultScript = "rank.php";
				break;
				
			case "backlink-checker":
				$defaultScript = "backlinks.php";
				break;
				
			case "directory-submission":
				$defaultScript = "directories.php";
				break;
				
			case "saturation-checker":
				$defaultScript = "saturationchecker.php";
				break;

			default:
				$seoToolInfo = $this->__getSeoToolInfo('keyword-position-checker', 'url_section');
				if($seoToolInfo['status'] == 1){					
					$info['menu_sec'] = 'keyword-position-checker';
					$defaultScript = "reports.php";
					$defaultArgs = empty($defaultArgs) ? "sec=reportsum" : $defaultArgs;	
				}
		}	
		
		$this->set('menuSelected', $info['menu_sec']);
		$this->set('defaultScript', $defaultScript);
		$this->set('defaultArgs', $defaultArgs);
		$this->render('seotools/index');
	}

	# func to get all seo tools
	function __getAllSeoTools(){
		$sql = "select * from seotools order by id";
		$seoToolList = $this->db->select($sql);
		return $seoToolList;
	}

	# func to get seo tool info
	function __getSeoToolInfo($val, $col='id'){
		$sql = "select * from seotools where $col='$val'";
		$seoToolInfo = $this->db->select($sql, true);
		return $seoToolInfo;
	}
	
	# func to list seo tools
	function listSeoTools(){
		
		$userId = isLoggedIn();
		$seoToolList = $this->__getAllSeoTools();
		$this->set('list', $seoToolList);
		$this->render('seotools/listseotools');	
	}
	
	#function to change status of seo tools
	function changeStatus($seoToolId, $status, $col='status'){
		
		$seoToolId = intval($seoToolId);
		$sql = "update seotools set $col=$status where id=$seoToolId";
		$this->db->query($sql);
	}
}
?>