<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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

include_once(SP_CTRLPATH."/proxy.ctrl.php");

class Spider{

	# settings of the spider
	var $_CURL_RESOURCE = null;
	var $_CURLOPT_FAILONERROR = false;
	var $_CURLOPT_FOLLOWLOCATION = true;
	var $_CURLOPT_RETURNTRANSFER = true;
	var $_CURLOPT_TIMEOUT = 15;
	var $_CURLOPT_POST = true;
	var $_CURLOPT_POSTFIELDS = null;
	var $_CURLOPT_USERAGENT = "Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))";
	var $_CURLOPT_USERPWD = null;
	var $_CURLOPT_COOKIEJAR = '';
	var $_CURLOPT_COOKIEFILE = '';
	var $_CURLOPT_REFERER = "";
	var $_CURL_sleep = 1;
	var $_CURLOPT_COOKIE = "";
	var $_CURLOPT_HEADER = 0;
	var $_CURL_HTTPHEADER = array();
	var $userAgentList = array();

	# spider constructor
	function Spider()	{
		$this -> _CURLOPT_COOKIEJAR = SP_TMPPATH.'/'.$this -> _CURLOPT_COOKIEJAR;
		$this -> _CURLOPT_COOKIEFILE = SP_TMPPATH.'/'.$this -> _CURLOPT_COOKIEFILE;
		$this -> _CURL_RESOURCE = curl_init( );
		if(!empty($_SERVER['HTTP_USER_AGENT'])) $this->_CURLOPT_USERAGENT = $_SERVER['HTTP_USER_AGENT'];

		// user agents
		$this->userAgentList[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)";
		$this->userAgentList[] = "Mozilla/5.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)";
		$this->userAgentList[] = "Mozilla/5.0 (Mozilla/5.0; MSIE 7.0; Windows NT 5.1; FDM; SV1; .NET CLR 3.0.04506.30)";
		$this->userAgentList[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; .NET CLR 2.0.50727)";
		$this->userAgentList[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.0; search bar)";
		$this->userAgentList[] = "Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))";
		$this->userAgentList[] = defined('SP_USER_AGENT') ? SP_USER_AGENT : $this->_CURLOPT_USERAGENT;

	}

	# func to format urls
<<<<<<< HEAD
	function formatUrl($url){
=======
	public static function formatUrl($url){	    
>>>>>>> seopanel/master
	    $scheme = "";
		if(stristr($url,'http://')){
			$scheme = "http://";
		}elseif(stristr($url,'https://')){
			$scheme = "https://";
		}
		$url = str_replace(array('http://','https://', '"', '"'), '',$url);
		$url = preg_replace('/\/{2,}/', '/', $url);
		$url = preg_replace('/&{2,}/', '&', $url);
		$url = preg_replace('/#{2,}/', '#', $url);
		$url = Spider::removeTrailingSlash($url);
		return $scheme.$url;
	}

	# func to get relative url to append with relative links found in the page
	function getRelativeUrl($relativeUrl) {

	    $relativeUrl = parse_url($relativeUrl, PHP_URL_PATH);

	    // if link contains script names
        if(preg_match('/.htm$|.html$|.php$|.pl$|.jsp$|.asp$|.aspx$|.do$|.cgi$|.cfm$/i', $relativeUrl)) {
            if (preg_match('/(.*)\//', $relativeUrl, $matches) ) {
                return $matches[1];
            }
        } elseif (preg_match('/\/$/', $relativeUrl)) {
            return $this->removeTrailingSlash($relativeUrl);
	    } else {
            return $relativeUrl;
        }
	}

    # func to get backlink page info
	function getPageInfo($url, $domainUrl, $returnUrls=false){

	    $urlWithTrailingSlash = Spider::addTrailingSlash($url);
		$ret = $this->getContent($urlWithTrailingSlash);
		$pageInfo = array();
		$checkUrl = formatUrl($domainUrl);

		// if relative links of a page needs to be checked
		if (SP_RELATIVE_LINK_CRAWL) {
		    $relativeUrl = $domainUrl . $this->getRelativeUrl($url);
		}

		// find main domain host link
		$domainHostInfo = parse_url($domainUrl);
		$domainHostLink = $domainHostInfo['scheme'] . "://" . $domainHostInfo['host'] . "/";

		if( !empty($ret['page'])){
			$string = str_replace(array("\n",'\n\r','\r\n','\r'), "", $ret['page']);
			$pageInfo = WebsiteController::crawlMetaData($url, '', $string, true);

			// check whether base url tag is there
			$baseTagUrl = "";
			if (preg_match("/<base (.*?)>/is", $string, $match)) {
				$baseTagUrl = $this->__getTagParam("href", $match[1]);
				$baseTagUrl = $this->addTrailingSlash($baseTagUrl);
			}

			$pattern = "/<a(.*?)>(.*?)<\/a>/is";
			preg_match_all($pattern, $string, $matches, PREG_PATTERN_ORDER);

			// loop through matches
			for($i=0; $i < count($matches[1]); $i++){

				// check links foudn valid or not
				$href = $this->__getTagParam("href",$matches[1][$i]);
				if ( !empty($href) || !empty($matches[2][$i])) {

    				if( !preg_match( '/mailto:/', $href ) && !preg_match( '/javascript:|;/', $href ) ){

    					// find external links
    				    $pageInfo['total_links'] += 1;
    				    $external = 0;
    				    if (stristr($href, 'http://') ||  stristr($href, 'https://')) {

    					    if (!preg_match("/^".preg_quote($checkUrl, '/')."/", formatUrl($href))) {
    					        $external = 1;
    					        $pageInfo['external'] += 1;
    					    }

    				    } else {

    				        // if url starts with / then append with base url of site
    				    	if (preg_match('/^\//', $href)) {
    				    		$href = $domainHostLink . $href;
    				    	} elseif (!empty($baseTagUrl)) {
    				        	$href = $baseTagUrl . $href;
    				        } elseif ( $url == $domainUrl) {
    				            $href = $domainUrl ."/". $href;
    				        } elseif ( SP_RELATIVE_LINK_CRAWL) {
    				            $href = $relativeUrl ."/". $href;
    				        } else {
    				            $pageInfo['total_links'] -= 1;
    				            continue;
    				        }

    				        // if contains back directory operator
    				        if (stristr($href, '/../')) {
                            	$hrefParts = explode('/../', $href);
                            	preg_match('/.*\//', $hrefParts[0], $matchpart);
                            	$href = $matchpart[0]. $hrefParts[1];
                            }
    				    }

    				    // if details of urls to be checked
    				    if($returnUrls){
    				        $linkInfo['link_url'] = $href;
    						if(stristr($matches[2][$i], '<img')) {
    							$linkInfo['link_anchor'] = $this->__getTagParam("alt", $matches[2][$i]);
    						} else {
    							$linkInfo['link_anchor'] = strip_tags($matches[2][$i]);
    						}
    						$linkInfo['nofollow'] = stristr($matches[1][$i], 'nofollow') ? 1 : 0;
    						$linkInfo['link_title'] = $this->__getTagParam("title", $matches[1][$i]);
    						if ($external) {
    						    $pageInfo['external_links'][] = $linkInfo;
    						} else {
    						    $pageInfo['site_links'][] = $linkInfo;
    						}
    				    }

    				}
				}
			}
		}

		return $pageInfo;
	}

	# function to remove last trailing slash
<<<<<<< HEAD
	function removeTrailingSlash($url) {
=======
	public static function removeTrailingSlash($url) {		
>>>>>>> seopanel/master
		$url = preg_replace('/\/$/', '', $url);
		return $url;
	}

    # function to remove last trailing slash
	public static function addTrailingSlash($url) {
	    if (!stristr($url, '?') && !stristr($url, '#')) {
	        if (!preg_match("/\.([^\/]+)$/", $url)) {
        		if (!preg_match('/\/$/', $url)) {
        		    $url .= "/";
        		}
	        }
	    }
		return $url;
	}

	# func to get unique urls of a page
	function getUniqueUrls($url){

		$ret = $this->getContent($url);
		$urlList = array();

		if( !empty($ret['page'])){
			$string = strtolower($ret['page']);
			$string = str_replace("\n","",$string);

			$pattern = "/<a (.*)>(.*\n*.*|.*\n*)<\/a>/U";
			preg_match_all($pattern,$string,$matches, PREG_PATTERN_ORDER);
			for($i=0;$i<count($matches[1]);$i++){
				$href = $this->getTagParam("href",$matches[1][$i]);
				$href = preg_replace('/\/{3}/', '/', $href);
				if(!empty($href)){
					if( !preg_match( '/mailto:/', $href ) && ($href!="#") && !preg_match( '/javascript:|;/', $href ) ){
						if($href != "/"){
							$urlList[] = $href;
						}
					}
				}
			}
		}
		return $urlList;
	}

	# function to get value of a parameter in a tag
    function __getTagParam($param, $tag){
		preg_match('/'.$param.'="(.*?)"/is', $tag, $matches);
		if(empty($matches[1])){
			preg_match("/$param='(.*?)'/is", $tag, $matches);
			if(empty($matches[1])){
				preg_match("/$param=(.*?) /is", $tag, $matches);
			}
		}
		if(isset($matches[1])) return trim($matches[1]) ;
	}

	# function to get then useragent
	function getUserAgent() {
	    $userAgentKey = array_rand($this->userAgentList, 1);
	    return $this->userAgentList[$userAgentKey];
	}


	# get contents of a web page
	function getContent( $url, $enableProxy=true, $logCrawl = true)	{

		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_URL , $url );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_FAILONERROR , $this -> _CURLOPT_FAILONERROR );
		@curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_FOLLOWLOCATION , $this -> _CURLOPT_FOLLOWLOCATION );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_RETURNTRANSFER , $this -> _CURLOPT_RETURNTRANSFER );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_TIMEOUT , $this -> _CURLOPT_TIMEOUT );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_COOKIEJAR , $this -> _CURLOPT_COOKIEJAR );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_COOKIEFILE , $this -> _CURLOPT_COOKIEFILE );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_HEADER , $this -> _CURLOPT_HEADER);

		// to fix the ssl related issues
		curl_setopt($this->_CURL_RESOURCE, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->_CURL_RESOURCE, CURLOPT_SSL_VERIFYPEER, 0);

		// to add the curl http headers
		if (!empty($this ->_CURL_HTTPHEADER)) {
			curl_setopt($this->_CURL_RESOURCE, CURLOPT_HTTPHEADER, $this ->_CURL_HTTPHEADER);
		}

		if(!empty($this -> _CURLOPT_COOKIE)) curl_setopt( $this -> _CURL_RESOURCE, CURLOPT_COOKIE , $this -> _CURLOPT_COOKIE );
		if(!empty($this-> _CURLOPT_REFERER)){
			curl_setopt($this -> _CURL_RESOURCE, CURLOPT_REFERER, $this-> _CURLOPT_REFERER);
		}

		if( strlen( $this -> _CURLOPT_POSTFIELDS ) > 1 ) {
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_POST , $this -> _CURLOPT_POST );
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_POSTFIELDS , $this -> _CURLOPT_POSTFIELDS );
		}

		// user agent assignment
		$this->_CURLOPT_USERAGENT = defined('SP_USER_AGENT') ? SP_USER_AGENT : $this->_CURLOPT_USERAGENT;
		if( strlen( $this -> _CURLOPT_USERAGENT ) > 0 ) {
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_USERAGENT, $this -> _CURLOPT_USERAGENT );
		}

		if( strlen( $this -> _CURLOPT_USERPWD ) > 2 ) {
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_USERPWD, $this -> _CURLOPT_USERPWD );
		}

		// to use proxy if proxy enabled
		if (SP_ENABLE_PROXY && $enableProxy) {
			$proxyCtrler = New ProxyController();
			if ($proxyInfo = $proxyCtrler->getRandomProxy()) {
				curl_setopt($this -> _CURL_RESOURCE, CURLOPT_PROXY, $proxyInfo['proxy'].":".$proxyInfo['port']);
				curl_setopt($this -> _CURL_RESOURCE, CURLOPT_HTTPPROXYTUNNEL, CURLOPT_HTTPPROXYTUNNEL_VAL);
				if (!empty($proxyInfo['proxy_auth'])) {
					curl_setopt ($this -> _CURL_RESOURCE, CURLOPT_PROXYUSERPWD, $proxyInfo['proxy_username'].":".$proxyInfo['proxy_password']);
				}
			} else {
			    showErrorMsg("No active proxies found!! Please check your proxy settings from Admin Panel.");
			}
		}

		$ret['page'] = curl_exec( $this -> _CURL_RESOURCE );
		$ret['error'] = curl_errno( $this -> _CURL_RESOURCE );
		$ret['errmsg'] = curl_error( $this -> _CURL_RESOURCE );

		// update crawl log in database for future reference
		if ($logCrawl) {
			$crawlLogCtrl = new CrawlLogController();
			$crawlInfo['crawl_status'] = $ret['error'] ? 0 : 1;
			$crawlInfo['crawl_type'] = 'raw crawl';
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = addslashes($url);
			$crawlInfo['crawl_referer'] = addslashes($this-> _CURLOPT_REFERER);
			$crawlInfo['crawl_cookie'] = addslashes($this -> _CURLOPT_COOKIE);
			$crawlInfo['crawl_post_fields'] = addslashes($this -> _CURLOPT_POSTFIELDS);
			$crawlInfo['crawl_useragent'] = addslashes($this->_CURLOPT_USERAGENT);
			$crawlInfo['proxy_id'] = $proxyInfo['id'];
			$crawlInfo['log_message'] = addslashes($ret['errmsg']);
			$ret['log_id'] = $crawlLogCtrl->createCrawlLog($crawlInfo);
		}

		// disable proxy if not working
		if (SP_ENABLE_PROXY && $enableProxy && !empty($ret['error']) && !empty($proxyInfo['id'])) {

			// deactivate proxy
			if (PROXY_DEACTIVATE_CRAWL) {
				$proxyCtrler->__changeStatus($proxyInfo['id'], 0);
			}

			// chekc with another proxy
			if (CHECK_WITH_ANOTHER_PROXY_IF_FAILED) {
				$ret = $this->getContent($url, $enableProxy);
			}
		}

		// check debug request is enabled
		if (!empty($_GET['debug']) || !empty($_POST['debug'])) {
			?>
			<div style="width: 760px; margin-top: 30px; padding: 14px; height: 900px; overflow: auto; border: 1px solid #B0C2CC;">
				<?php
				if ( ($_GET['debug_format'] == 'html') || ($_POST['debug_format'] == 'html') ) {
					highlight_string($ret['page']);
				} else {
					debugVar($ret, false);
				}
				?>
			</div>
			<?php
		}

		return $ret;
	}

	# func to get session id
	function getSessionId($page){
		if (preg_match('/PHPSESSID=(.*?);/', $page, $result)) {
			return $result[1];
		} else {
			return false;
		}
	}

	# func to check proxy
	function checkProxy($proxyInfo) {

		$this->_CURLOPT_USERAGENT = defined('SP_USER_AGENT') ? SP_USER_AGENT : $this->_CURLOPT_USERAGENT;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['proxy'].":".$proxyInfo['port']);
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, CURLOPT_HTTPPROXYTUNNEL_VAL);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_CURLOPT_USERAGENT);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

		if (!empty($proxyInfo['proxy_auth'])) {
			curl_setopt ($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['proxy_username'].":".$proxyInfo['proxy_password']);
		}

		curl_setopt($ch, CURLOPT_URL, "http://www.google.com/search?q=twitter");
		$ret['page'] = curl_exec( $ch );
		$ret['error'] = curl_errno( $ch );
		$ret['errmsg'] = curl_error( $ch );
		curl_close($ch);

		// if no error check whether the ouput contains twitter keyword
		if (empty($ret['error']) && !stristr($ret['page'], 'twitter') ) {
			$ret['error'] = "Page not contains twitter keyword";
			$ret['errmsg'] = strtok($ret['page'], "\n");
		}

		return $ret;
	}

	// function to get the header of url
    public static function getHeader($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, SP_USER_AGENT);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);

		// Only calling the head
		curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'

		$content = curl_exec ($ch);
		curl_close ($ch);
		return $content;
	}

	// function to check whether link is brocke
	public static function isLInkBrocken($url) {
	    $header = Spider::getHeader($url);
	    if (stristr($header, '404 Not Found')) {
	        return true;
	    } else {
	        return false;
	    }
	}
}
?>
