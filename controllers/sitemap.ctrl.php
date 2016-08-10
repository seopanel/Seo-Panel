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

# class defines all sitemap controller functions
class SitemapController extends Controller{

	var $smLimit = 50000;			# number of pages in a sitemap
 	var $baseUrl;					# base url of page
 	var $smType = 'xml';			# the type of sitemap file should be created
 	var $urlList;					# the list of urls crawled from a site
 	var $hostName;					# hostname of the site
 	var $spider;					# spider object
 	var $sleep = 0;					# sleep b/w the page crawl in seconds
 	var $excludeUrl = "";			# url to be excluded
 	var $changefreq = "always";		# page modification frequency
 	var $priority = 0.5;			# priority of a page
 	var $lastmod;				 	# page last modification date
 	var $smheader;					# sitemap header
 	var $smfooter;					# sitemap footer
 	var $smfile = "";				# sitemap file
 	var $section = "";              # sitemap website
 	var $sitemapDir = "";			# sitemap directory where sitemap is created
	
	# func to show sitemap generator interface
	function showSitemapGenerator() {
		
		$userId = isLoggedIn();		
		$saCtrler = $this->createController('SiteAuditor');
	    $where = isAdmin() ? "" : " and w.user_id=$userId";
	    $pList = $saCtrler->getAllProjects($where);
	    $projectList = array();
	    foreach($pList as $pInfo) {
		    $pInfo['total_links'] = $saCtrler->getCountcrawledLinks($pInfo['id']);
		    if ($pInfo['total_links'] > 0) {
	            $projectList[] = $pInfo;
		    }
	    }
	    
	    if(empty($projectList)) {
            $spTextSA = $this->getLanguageTexts('siteauditor', $_SESSION['lang_code']);
	        showErrorMsg($spTextSA['No active projects found'].'!');
        }
	    
	    $this->set('projectList', $projectList);
		$this->render('sitemap/showsitemap');
	}	
	
	# func to generate sitemap
 	function generateSitemapFile($sitemapInfo){
 		
 		$sitemapInfo['project_id'] = intval($sitemapInfo['project_id']);
 		if(!empty($sitemapInfo['project_id'])){	

 			# check whether the sitemap directory is writable
 			if(!is_writable(SP_TMPPATH ."/".$this->sitemapDir)){
 				hideDiv('message');
 				showErrorMsg("Directory '<b>".SP_TMPPATH ."/".$this->sitemapDir."</b>' is not <b>writable</b>. Please change its <b>permission</b> !");
 			}
 			
	        $saCtrler = $this->createController('SiteAuditor');
 			$projectInfo = $saCtrler->__getProjectInfo($sitemapInfo['project_id']);
 			$this->section = formatFileName($projectInfo['name']);
 			
			$this->smType = $sitemapInfo['sm_type'];
			$this->excludeUrl = $sitemapInfo['exclude_url'];
			if(!empty($sitemapInfo['freq'])) $this->changefreq = $sitemapInfo['freq'];
			if(!empty($sitemapInfo['priority'])) $this->priority = $sitemapInfo['priority'];
			$auditorComp = $this->createComponent('AuditorComponent');
			$pageList = $auditorComp->getAllreportPages(" and project_id=".$sitemapInfo['project_id']);
			$urlList = array();
			foreach ($pageList as $pageInfo) {
			    $pageInfo['page_url'] = Spider::addTrailingSlash($pageInfo['page_url']); 
			    if ($auditorComp->isExcludeLink($pageInfo['page_url'], trim($sitemapInfo['exclude_url']))) continue;
			    $urlList[] = $pageInfo['page_url'];
			}
			$this->createSitemap($this->smType, $urlList);
 		}else{
 			hideDiv('message');
 			showErrorMsg("No Website Found!");
 		} 		
 	}
 	
 	# Create new sitemaps and index file 	
 	function createSitemap($smType="", $urlList="") {
 		
 		if(!empty($smType)){
 			$this->smType = $smType;
 		}			
 		
 		print("<p class=\"note noteleft\">".$_SESSION['text']['common']['Found']." <a>".count($urlList)."</a> Sitemap Urls</p>"); 		
 		$function = $this->smType ."SitemapFile";
 		$this->deleteSitemapFiles();
 		$this->$function($urlList);
 		$this->showSitemapFiles();
 		
	}
	
	# func to get a sitemap urls of a site
	function getSitemapUrls(){		
		$this->urlList = array();
		$this->crawlSitemapUrls($this->baseUrl, true);						
	}
	
	# func to crawl sitemap urls
	function crawlSitemapUrls($baseUrl, $recursive=false){
		
		if($this->urlList[$baseUrl]['visit'] == 1) return;				
		$this->urlList[$baseUrl]['visit'] = 1;
		
		$urlList = $this->spider->getUniqueUrls($baseUrl);
		$hostName = $this->hostName;
		
		foreach($urlList as $href){
			if(preg_match('/\.zip$|\.gz$|\.tar$|\.png$|\.jpg$|\.jpeg$|\.gif$|\.mp3$/i', $href)) continue;
			$urlInfo = @parse_url($href);
			
			$urlHostName = str_replace('www.', '', $urlInfo['host']);
			if(empty($urlHostName)){
				$href = $this->baseUrl.$href;
			}else{
				if($urlHostName != $hostName){
					continue;
				}
			}
			
			$href = $this->spider->formatUrl($href);
			$href = preg_replace('/http:\/\/.*?\//i', $this->baseUrl, $href);
			if(!empty( $this->excludeUrl) && stristr($href, $this->excludeUrl)) continue;
			if(!isset($this->urlList[$href]['visit']) && !isset($this->urlList[$href.'/']['visit'])){
				$this->urlList[$href]['visit'] = 0;
				if($recursive){
					sleep($this->sleep);
					$this->crawlSitemapUrls($href,true);
				}
			}
		}			
	}

	# create text sitemap file
	function txtSitemapFile($urlList) {
		$this->smheader = '';	
		$this->smfooter = '';
		$smxml = "";
		foreach($urlList as $this->loc){
			$smxml .= $this->loc ."\n";
		}
		$this->smfile = $this->section ."_sitemap1.".$this->smType;
		$this->createSitemapFile($smxml);
	}
	
	# create Html sitemap file
	function htmlSitemapFile($urlList) {
		$this->smheader = '';	
		$this->smfooter = '';
		$smxml = "";
		foreach($urlList as $this->loc){
			$smxml .= "<a href='$this->loc'>$this->loc</a><br>";
		}
		$this->smfile = $this->section ."_sitemap1.".$this->smType;
		$this->createSitemapFile($smxml);
	}
	
	
	# create xml sitemap file
	function xmlSitemapFile($urlList) {
		$this->lastmod = Date("Y-m-d");
		$this->smheader = '<?xml version="1.0" encoding="UTF-8"?>
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"><!-- created with Seo Panel:www.seopanel.in -->';	
		$this->smfooter = '</urlset>';
		$index = 1;
		$rowcount = 0;
		$smxml = "";
			
		foreach($urlList as $this->loc){
			$smxml .= $this->createUrlXmlText();
			if(($this->smLimit -1) == $rowcount++){
				
				# create sitemap file when tot url count equal max count
				$this->smfile = $this->section ."_sitemap". $index . ".".$this->smType;
				$this->createSitemapFile($smxml);
				$rowcount = 0;
				$smxml = "";
				$index++;				
			}
		}
			
		# to create sitemap file with rest of urls 
		if(!empty($smxml)){
			$this->smfile = $this->section ."_sitemap". $index . ".xml";
			$this->createSitemapFile($smxml);
		}		
	}	
	
	function showSitemapFiles(){
		if ($handle = opendir(SP_TMPPATH ."/".$this->sitemapDir)) {
		    while (false !== ($file = readdir($handle))) {
		        if ( ($file != ".") && ($file != "..") ) {
		        	if(preg_match("/".$this->section."_sitemap\d+\.".$this->smType."/", $file, $matches)){
		        		echo "<p class=\"note noteleft\">
		        				".$this->spTextSitemap['Download sitemap file from'].": 
		        				<a href='".SP_WEBPATH."/download.php?filesec=sitemap&filetype=$this->smType&file=".urlencode($matches[0])."' target='_blank'>$file</a>
		        			</p>";
		        	}
		        }
		    }
		    closedir($handle);
		}
	}
	
	function deleteSitemapFiles(){
		if ($handle = opendir(SP_TMPPATH ."/".$this->sitemapDir)) {
		    while (false !== ($file = readdir($handle))) {
		        if ( ($file != ".") && ($file != "..") ) {
		        	if(preg_match("/".preg_quote($this->section, '/')."_sitemap\d+\.".$this->smType."/", $file, $matches)){
		        		unlink(SP_TMPPATH ."/".$this->sitemapDir."/$file");
		        	}
		        }
		    }
		    closedir($handle);
		}
	}
	
	# create url xml text 
	function createUrlXmlText() {		
		$xmltext = 
		'
		<url>
			<loc>'.$this->loc.'</loc>
		   	<lastmod>'.$this->lastmod.'</lastmod>
		    <changefreq>'.$this->changefreq.'</changefreq>
		    <priority>'.$this->priority.'</priority>
	 	</url>
	 	';
	 	return $xmltext;
	}
	
	# create sitemap file
	function createSitemapFile($smxml) {
		$fp = fopen(SP_TMPPATH ."/".$this->sitemapDir."/" .$this->smfile, 'w');
		$smxml = $this->smheader . $smxml . $this->smfooter; 
		fwrite($fp, $smxml);
		fclose($fp);
	}
	
	
	# function to create encoded url for sitemap
	function getEncodedUrl($url){
		
		# convert url to entity encoded
		$url = str_replace(array('&',"'",'"','>','<'," "), array('&amp;','&apos;','&quot;','&gt;','&lt;','_'), $url);
		return $url;
	}
		
}
?>