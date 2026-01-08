<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	           *
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
if (defined('SP_CTRLPATH')) {
    include_once(SP_CTRLPATH."/proxy.ctrl.php");
}

class Spider {

	# settings of the spider
	var $_CURL_RESOURCE = null;	
	var $_CURLOPT_FAILONERROR = false;	
	var $_CURLOPT_FOLLOWLOCATION = true;	
	var $_CURLOPT_RETURNTRANSFER = true;	
	var $_CURLOPT_MAXREDIRS = 4; //Don't get caught in redirect loop
	var $_CURLOPT_TIMEOUT = 15;	
	var $_CURLOPT_POST = true;
	var $_CURLOPT_POSTFIELDS = null;
	var $_CURLOPT_USERAGENT = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0";
	var $_CURLOPT_USERPWD = null;
	var $_CURLOPT_COOKIEJAR = '';
	var $_CURLOPT_COOKIEFILE = '';
	var $_CURLOPT_REFERER = "";
	var $_CURL_sleep = 1;
	var $_CURLOPT_COOKIE = "";
	var $_CURLOPT_HEADER = 0;
	var $_CURL_HTTPHEADER = array();
	var $userAgentList = array();
	var $effectiveUrl = null;
	var $proxyInfo = [];
	
	# spider constructor
	function __construct()	{	    
	    // if _CURLOPT_COOKIEJAR path defined
	    if (!empty($this -> _CURLOPT_COOKIEJAR)) {
	        $this -> _CURLOPT_COOKIEJAR = SP_TMPPATH.'/'.$this -> _CURLOPT_COOKIEJAR;
	    }
	    
	    // if _CURLOPT_COOKIEFILE path defined
	    if (!empty($this -> _CURLOPT_COOKIEFILE)) {
	        $this -> _CURLOPT_COOKIEFILE = SP_TMPPATH.'/'.$this -> _CURLOPT_COOKIEFILE;
	    }
	    
		$this -> _CURL_RESOURCE = curl_init( );
		if(!empty($_SERVER['HTTP_USER_AGENT'])) {
		    $this->_CURLOPT_USERAGENT = $_SERVER['HTTP_USER_AGENT'];
		}

		// user agents
		$this->userAgentList['google'] = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0";
		$this->userAgentList['bing'] = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:69.0) Gecko/20100101 Firefox/69.0";
		$this->userAgentList['pinterest'] = "Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0";
		$this->userAgentList['default'] = defined('SP_USER_AGENT') ? SP_USER_AGENT : $this->_CURLOPT_USERAGENT;
	}	
	
	# func to format urls
	public static function formatUrl($url) {
	    $scheme = "";
		if(stristr($url,'http://')) {
			$scheme = "http://";
		} elseif(stristr($url,'https://')) {
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
	    $matches = [];
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
	function getPageInfo($url, $domainUrl, $returnUrls=false) {	    
	    $urlWithTrailingSlash = Spider::addTrailingSlash($url);
		$ret = $this->getContent($urlWithTrailingSlash);
		$pageInfo = array(
			'external' => 0,
			'total_links' => 0,
		    'site_links' => [],
		);
		
		$checkUrl = formatUrl($domainUrl);
		
		// if relative links of a page needs to be checked
		if (SP_RELATIVE_LINK_CRAWL) {
		    $relativeUrl = $domainUrl . $this->getRelativeUrl($url);
		}
		
		// find main domain host link
		$domainHostInfo = parse_url($domainUrl);
		$domainHostLink = $domainHostInfo['scheme'] . "://" . $domainHostInfo['host'] . "/";
		
		$matches = [];
		$match = [];
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

    				    // Handle protocol-relative URLs (//cdn.example.com)
    				    if (preg_match('#^//#', $href)) {
    				        $href = 'https:' . $href;
    				    }
    				    // Handle query-string-only relative URLs (?page=2)
    				    elseif (preg_match('#^\?#', $href)) {
    				        $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' .
    				                   parse_url($url, PHP_URL_HOST) .
    				                   parse_url($url, PHP_URL_PATH);
    				        $href = $baseUrl . $href;
    				    }
    				    // Handle fragment-only relative URLs (#section)
    				    elseif (preg_match('#^#$', $href)) {
    				        // Skip anchor-only links
    				        $pageInfo['total_links'] -= 1;
    				        continue;
    				    }

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

    				        // Normalize URL to resolve parent directory operators and other issues
    				        $href = Spider::normalizeUrl($href);
    				    }
    				    
    				    // if details of urls to be checked
    				    if($returnUrls) {
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
		
		$pageInfo = __assign($pageInfo, "site_links", []);
		return $pageInfo;
	}
	
	# function to remove last trailing slash
	public static function removeTrailingSlash($url) {		
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

	/**
	 * Normalize URL by resolving relative paths, protocol-relative URLs, etc.
	 * Handles:
	 * - Multiple parent directory references (../../..)
	 * - Protocol-relative URLs (//cdn.example.com)
	 * - Query-string-only URLs (?page=2)
	 * - Fragment-only URLs (#section)
	 *
	 * @param string $url The URL to normalize
	 * @return string Normalized URL
	 */
	public static function normalizeUrl($url) {
	    if (empty($url)) {
	        return $url;
	    }

	    // Parse URL components
	    $parts = parse_url($url);

	    // Handle protocol-relative URLs (//cdn.example.com/path)
	    if (empty($parts['scheme']) && isset($parts['host'])) {
	        // If URL starts with //, add https:
	        if (preg_match('#^//#', $url)) {
	            $url = 'https:' . $url;
	            $parts = parse_url($url);
	        }
	    }

	    // If no path, return as-is
	    if (!isset($parts['path'])) {
	        return $url;
	    }

	    $path = $parts['path'];

	    // Normalize path by resolving .. and .
	    $path = self::normalizePath($path);

	    // Rebuild URL
	    $normalized = '';
	    if (isset($parts['scheme'])) {
	        $normalized .= $parts['scheme'] . '://';
	    }
	    if (isset($parts['host'])) {
	        if (isset($parts['user'])) {
	            $normalized .= $parts['user'];
	            if (isset($parts['pass'])) {
	                $normalized .= ':' . $parts['pass'];
	            }
	            $normalized .= '@';
	        }
	        $normalized .= $parts['host'];
	        if (isset($parts['port'])) {
	            $normalized .= ':' . $parts['port'];
	        }
	    }

	    $normalized .= $path;

	    if (isset($parts['query'])) {
	        $normalized .= '?' . $parts['query'];
	    }
	    if (isset($parts['fragment'])) {
	        $normalized .= '#' . $parts['fragment'];
	    }

	    return $normalized;
	}

	/**
	 * Normalize path by resolving . and .. segments
	 *
	 * @param string $path The path to normalize
	 * @return string Normalized path
	 */
	private static function normalizePath($path) {
	    // Split path into segments
	    $segments = explode('/', $path);
	    $normalized = array();

	    foreach ($segments as $segment) {
	        if ($segment === '' || $segment === '.') {
	            // Skip empty and current directory segments
	            continue;
	        } elseif ($segment === '..') {
	            // Parent directory - pop last segment if possible
	            if (count($normalized) > 0 && end($normalized) !== '..') {
	                array_pop($normalized);
	            }
	        } else {
	            // Regular segment
	            $normalized[] = $segment;
	        }
	    }

	    // Rebuild path
	    $result = '/' . implode('/', $normalized);

	    // Preserve trailing slash if original had one
	    if (substr($path, -1) === '/' && $result !== '/') {
	        $result .= '/';
	    }

	    return $result;
	}

	# func to get unique urls of a page
	function getUniqueUrls($url) {				
		$ret = $this->getContent($url);
		$urlList = array();
		$matches = [];		
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
    function __getTagParam($param, $tag) {
        $matches = [];
		preg_match('/'.$param.'="(.*?)"/is', $tag, $matches);
		if(empty($matches[1])){
			preg_match("/$param='(.*?)'/is", $tag, $matches);
			if(empty($matches[1])){
				preg_match("/$param=(.*?) /is", $tag, $matches);
			}		
		}				
		if(isset($matches[1])) return trim($matches[1]) ;
	}
	
	# function to get the useragent
	function getUserAgent($key = false) {
	    $userAgentKey = !empty($key) ? $key : 'default';
	    return $this->userAgentList[$userAgentKey];    
	}
	
	# function to create custome headers
	function setCustomHeaders() {		
		// if sending custom header with curl is enabled
		if (SP_SEND_CUSTOM_HEADER_IN_CURL) {
			$sessionId = session_id();
			$sessionId = !empty($sessionId) ? $sessionId : session_regenerate_id();
			array_push($this ->_CURL_HTTPHEADER, "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
			array_push($this ->_CURL_HTTPHEADER, "Connection: keep-alive");
			array_push($this ->_CURL_HTTPHEADER, "Cache-Control: max-age=0");
			array_push($this ->_CURL_HTTPHEADER, "Cookie: PHPSESSID=" . $sessionId);
			array_push($this ->_CURL_HTTPHEADER, "User-Agent: " . $this -> _CURLOPT_USERAGENT);
		}		
	}
	
	# get contents of a web page	
	function getContent( $url, $enableProxy=true, $logCrawl = true)	{
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_URL , $url );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_FAILONERROR , $this -> _CURLOPT_FAILONERROR );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_MAXREDIRS , $this -> _CURLOPT_MAXREDIRS );
		@curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_FOLLOWLOCATION , $this -> _CURLOPT_FOLLOWLOCATION );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_RETURNTRANSFER , $this -> _CURLOPT_RETURNTRANSFER );
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_TIMEOUT , $this -> _CURLOPT_TIMEOUT );
		
		if (!empty($this -> _CURLOPT_COOKIEJAR)) {
		    curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_COOKIEJAR, $this -> _CURLOPT_COOKIEJAR );
		}
		
		if (!empty($this -> _CURLOPT_COOKIEFILE)) {
		    curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_COOKIEFILE, $this -> _CURLOPT_COOKIEFILE );
		}
		
		curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_HEADER , $this -> _CURLOPT_HEADER);
		
		// to fix the ssl related issues
		curl_setopt($this->_CURL_RESOURCE, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->_CURL_RESOURCE, CURLOPT_SSL_VERIFYPEER, 0);

		// user agent assignment, if the url is not the main website
		if (stristr($url, SP_MAIN_SITE)) {
		    $this -> _CURLOPT_USERAGENT = "";
		} else {
		    $ugKey = false;
		    if (stristr($url, 'google.')) {
		        $ugKey = 'google';
		    } else if (stristr($url, 'bing.')) {
		        $ugKey = 'bing';
		    } else if (stristr($url, 'pinterest.')) {
		        $ugKey = 'pinterest';
		    }
		    
    		$this->_CURLOPT_USERAGENT = $this->getUserAgent($ugKey);
    		if( strlen( $this -> _CURLOPT_USERAGENT ) > 0 ) {
    			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_USERAGENT, $this -> _CURLOPT_USERAGENT );
    		}
		}
		
		// set custom headers for google domains
		/*if (stristr($url, 'google.')) {
			$this->setCustomHeaders();
		}*/

		// to add the curl http headers
		if (!empty($this ->_CURL_HTTPHEADER)) {
			curl_setopt($this->_CURL_RESOURCE, CURLOPT_HTTPHEADER, $this ->_CURL_HTTPHEADER);
		}
		
		if(!empty($this -> _CURLOPT_COOKIE)) {
		    curl_setopt( $this -> _CURL_RESOURCE, CURLOPT_COOKIE , $this -> _CURLOPT_COOKIE );
		}
		
		if(!empty($this-> _CURLOPT_REFERER)){
			curl_setopt($this -> _CURL_RESOURCE, CURLOPT_REFERER, $this-> _CURLOPT_REFERER); 
		}		
		
		if( strlen( $this -> _CURLOPT_POSTFIELDS ) > 1 ) {
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_POST , $this -> _CURLOPT_POST );
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_POSTFIELDS , $this -> _CURLOPT_POSTFIELDS );
		}

		if( strlen( $this -> _CURLOPT_USERPWD ) > 2 ) {
			curl_setopt( $this -> _CURL_RESOURCE , CURLOPT_USERPWD, $this -> _CURLOPT_USERPWD );
		}
		
		// to use proxy if proxy enabled
		$proxyInfo = [];
		if ($enableProxy && SP_ENABLE_PROXY) {
			$proxyCtrler = New ProxyController();
			if ($proxyInfo = $this->getSpiderProxy()) {
				curl_setopt($this -> _CURL_RESOURCE, CURLOPT_PROXY, $proxyInfo['proxy'].":".$proxyInfo['port']);
				
				if (CURLOPT_HTTPPROXYTUNNEL_VAL) {
					curl_setopt($this -> _CURL_RESOURCE, CURLOPT_HTTPPROXYTUNNEL, CURLOPT_HTTPPROXYTUNNEL_VAL);
				}		
				
				if (!empty($proxyInfo['proxy_auth'])) {
					curl_setopt ($this -> _CURL_RESOURCE, CURLOPT_PROXYUSERPWD, $proxyInfo['proxy_username'].":".$proxyInfo['proxy_password']);
				}				
			} else {
			    showErrorMsg("No active proxies found!! Please check your proxy settings from Admin Panel.");
			}
		}
		
		$ret = [];
		$ret['page'] = curl_exec( $this -> _CURL_RESOURCE );
		$ret['error'] = curl_errno( $this -> _CURL_RESOURCE );
		$ret['errmsg'] = curl_error( $this -> _CURL_RESOURCE );
		$ret['http_code'] = curl_getinfo($this -> _CURL_RESOURCE, CURLINFO_HTTP_CODE);
		
		$this->effectiveUrl = curl_getinfo($this -> _CURL_RESOURCE, CURLINFO_EFFECTIVE_URL);
		
		// update crawl log in database for future reference
		if ($logCrawl) {
			$crawlLogCtrl = new CrawlLogController();
			$crawlInfo = [];
			$crawlInfo['crawl_status'] = $ret['error'] ? 0 : 1;
			$crawlInfo['ref_id'] = $crawlInfo['crawl_link'] = addslashes($this->effectiveUrl);
			$crawlInfo['crawl_referer'] = addslashes($this-> _CURLOPT_REFERER);
			$crawlInfo['crawl_cookie'] = addslashes($this -> _CURLOPT_COOKIE);
			$crawlInfo['crawl_post_fields'] = addslashes($this -> _CURLOPT_POSTFIELDS);
			$crawlInfo['crawl_useragent'] = addslashes($this->_CURLOPT_USERAGENT);
			$crawlInfo['proxy_id'] = isset($proxyInfo['id']) ? intval($proxyInfo['id']) : 0;
			$crawlInfo['log_message'] = addslashes($ret['errmsg']);
			$ret['log_id'] = $crawlLogCtrl->createCrawlLog($crawlInfo);
		}
		
		// disable proxy if not working
		if ($enableProxy && SP_ENABLE_PROXY && !empty($ret['error']) && !empty($proxyInfo['id'])) {
			// deactivate proxy
			if (PROXY_DEACTIVATE_CRAWL) {
				$proxyCtrler->__changeStatus($proxyInfo['id'], 0);
			}
			
			// chekc with another proxy
			if (CHECK_WITH_ANOTHER_PROXY_IF_FAILED) {
				$ret = $this->getContent($url, $enableProxy);
			}
		}
		
		// debug run time if enabled
		$this->debugRunTime($ret);

		return $ret;
	}
	
	# function to debug runtime
	function debugRunTime($ret) {		
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
	}
	
	function resetCurlResourceCookie() {
	    $this->_CURLOPT_COOKIE = "";
	    curl_setopt($this->_CURL_RESOURCE, CURLOPT_COOKIE, $this->_CURLOPT_COOKIE);
	}
	
	# func to get session id
	function getSessionId($page){
	    $result = [];
		if (preg_match('/PHPSESSID=(.*?);/', $page, $result)) {
			return $result[1];
		} else {
			return false;
		}
	}
	
	# func to check proxy 
	function checkProxy($proxyInfo) {
		$this->_CURLOPT_USERAGENT = $this->getUserAgent();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['proxy'].":".$proxyInfo['port']);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_CURLOPT_USERAGENT);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

		if (CURLOPT_HTTPPROXYTUNNEL_VAL) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, CURLOPT_HTTPPROXYTUNNEL_VAL);
		}
		
		if (!empty($proxyInfo['proxy_auth'])) {
			curl_setopt ($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['proxy_username'].":".$proxyInfo['proxy_password']);
		}

		// set custom headers
		$this->setCustomHeaders();

		// to add the curl http headers
		if (!empty($this ->_CURL_HTTPHEADER)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this ->_CURL_HTTPHEADER);
		}
		
		// to fix the ssl related issues
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
		curl_setopt($ch, CURLOPT_URL, "https://www.google.com/search?q=twitter");
		$ret['page'] = curl_exec( $ch );
		$ret['error'] = curl_errno( $ch );
		$ret['errmsg'] = curl_error( $ch );
		curl_close($ch);
		
		// if no error check whether the ouput contains twitter keyword
		if (empty($ret['error'])) {
			
			// is captcha found in search results
			if (SearchEngineController::isCaptchInSearchResults($ret['page'])) {
				$ret['error'] = "Capctha found in the results";
				$ret['errmsg'] = strtok($ret['page'], "\n");
			} elseif(!stristr($ret['page'], 'twitter')) {
				$ret['error'] = "Page not contains twitter keyword";
				$ret['errmsg'] = strtok($ret['page'], "\n");
			}
			
		}
		
		// debug run time if enabled
		$this->debugRunTime($ret);
		
		return $ret;
	}
	
	// function to get the header of url
  public static function getHeader($url, $followRedirects = true){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, SP_USER_AGENT);
		if($followRedirects){
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
		
		// Only calling the head
		curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'
		
		// to fix the ssl related issues
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$content = curl_exec ($ch);
		curl_close ($ch);
		return $content;
	}
	
	function getSpiderProxy() {
	    if (!empty($this->proxyInfo)) {
	        return $this->proxyInfo;
	    } else {
	        $proxyCtrler = New ProxyController();
	        return $proxyCtrler->getRandomProxy();
	    }
	}
	
	// function to check whether link is broken
	public static function isLinkBroken($url) {
	    $header = Spider::getHeader($url);

	    // Check for complete request failure
	    if (empty($header)) {
	        return 1; // Request failed completely
	    }

	    // Check for any 4xx or 5xx error codes (client errors and server errors)
	    if (preg_match('/HTTP\/[\d\.]+\s+([45]\d{2})\s/', $header, $matches)) {
	        return 1; // Broken (4xx or 5xx error)
	    }

	    return 0; // Not broken (2xx or 3xx response)
	}

	// Backward compatibility alias (deprecated)
	public static function isLInkBrocken($url) {
	    return self::isLinkBroken($url);
	}
	
	// function to check whether link is a redirect
	public static function isLinkRedirect($url) {
		$followRedirects = false; // Don't follow redirects so we can detect them
		$header = Spider::getHeader($url, $followRedirects);

		// Check for complete request failure
		if (empty($header)) {
			return 0; // Request failed, not a redirect
		}

		// Check for any 3xx redirect status code
		if (preg_match('/HTTP\/[\d\.]+\s+(3\d{2})\s/', $header, $matches)) {
			$statusCode = intval($matches[1]);

			// 304 Not Modified is not really a redirect, it's a cache validation response
			if ($statusCode == 304) {
				return 0;
			}

			return 1; // Any other 3xx is a redirect (300, 301, 302, 303, 307, 308, etc.)
		}

		return 0; // Not a redirect
	}
	
	public static function getCrawlEngineInfo($engineName, $engineCategory) {
	    $ctrler = new SearchEngineController();
	    $whereCond = "engine_name='" . addslashes($engineName) ."' and engine_category='" . addslashes($engineCategory) . "'";
	    $engineInfo = $ctrler->dbHelper->getRow('crawl_engines', $whereCond);
	    return $engineInfo;
	}
	
	public static function getCrawlEngineCategoryList($engineCategory) {
	    $ctrler = new SearchEngineController();
	    $whereCond = "engine_category='" . addslashes($engineCategory) . "'";
	    $list = $ctrler->dbHelper->getAllRows('crawl_engines', $whereCond);
	    
	    $engineList = [];
	    foreach ($list as $listInfo) {
	        $engineList[$listInfo['engine_name']] = $listInfo;
	    }
	    
	    return $engineList;
	}
	
	/**
	 * Check if a URL is blocked by robots.txt
	 * 
	 * @param string $pageUrl The full page URL to check
	 * @param string $domainUrl The domain base URL
	 * @return int 1 if blocked, 0 if allowed
	 */
	public static function isBlockedByRobotsTxt($pageUrl, $domainUrl) {
		// Parse the domain URL to get the base
		$parsedDomain = parse_url($domainUrl);
		if (!isset($parsedDomain['scheme']) || !isset($parsedDomain['host'])) {
			return 0; // Can't check, assume allowed
		}
		
		$robotsTxtUrl = $parsedDomain['scheme'] . '://' . $parsedDomain['host'] . '/robots.txt';
		
		// Try to fetch robots.txt
		$spider = new Spider();
		$result = $spider->getContent($robotsTxtUrl, false, false);
		
		if (empty($result['page'])) {
			return 0; // No robots.txt found, assume allowed
		}
		
		$robotsTxt = $result['page'];
		
		// Parse the page URL to get the path
		$parsedUrl = parse_url($pageUrl);
		$urlPath = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
		if (isset($parsedUrl['query'])) {
			$urlPath .= '?' . $parsedUrl['query'];
		}
		
		// Parse robots.txt
		$lines = explode("\n", $robotsTxt);
		$currentUserAgent = '';
		$disallowRules = array();
		$allowRules = array();

		foreach ($lines as $line) {
			// Remove comments and trim
			$line = trim(preg_replace('/#.*$/', '', $line));
			if (empty($line)) continue;

			// Check for User-agent directive
			if (preg_match('/^User-agent:\s*(.+)$/i', $line, $matches)) {
				$userAgent = trim($matches[1]);

				// Check if this applies to our bot (treat as generic crawler)
				if ($userAgent === '*' ||
					stripos($userAgent, 'Googlebot') !== false ||
					stripos($userAgent, 'bot') !== false ||
					stripos($userAgent, 'crawler') !== false) {
					$currentUserAgent = $userAgent;
				} else {
					$currentUserAgent = '';
				}
				continue;
			}
			
			// Only process rules if we're in a relevant User-agent block
			if (empty($currentUserAgent)) continue;
			
			// Check for Disallow directive
			if (preg_match('/^Disallow:\s*(.*)$/i', $line, $matches)) {
				$disallowPath = trim($matches[1]);
				if ($disallowPath !== '') {
					$disallowRules[] = $disallowPath;
				}
			}
			
			// Check for Allow directive (takes precedence over Disallow)
			if (preg_match('/^Allow:\s*(.*)$/i', $line, $matches)) {
				$allowPath = trim($matches[1]);
				if ($allowPath !== '') {
					$allowRules[] = $allowPath;
				}
			}
		}
		
		// Check if URL matches any Allow rules first (these take precedence)
		foreach ($allowRules as $allowPath) {
			if (Spider::matchesRobotsPattern($urlPath, $allowPath)) {
				return 0; // Explicitly allowed
			}
		}
		
		// Check if URL matches any Disallow rules
		foreach ($disallowRules as $disallowPath) {
			if (Spider::matchesRobotsPattern($urlPath, $disallowPath)) {
				return 1; // Blocked
			}
		}
		
		return 0; // Not blocked
	}
	
	/**
	 * Check if a URL path matches a robots.txt pattern
	 * 
	 * @param string $urlPath The URL path to check
	 * @param string $pattern The robots.txt pattern
	 * @return bool True if matches
	 */
	private static function matchesRobotsPattern($urlPath, $pattern) {
		// Handle wildcard * (matches any sequence of characters)
		$pattern = preg_quote($pattern, '/');
		$pattern = str_replace('\*', '.*', $pattern);
		
		// Handle $ (end of path marker)
		$pattern = str_replace('\$', '$', $pattern);
		
		// Check if pattern matches the beginning of URL path
		return preg_match('/^' . $pattern . '/i', $urlPath);
	}
}
?>
