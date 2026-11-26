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

# class defines all social media controller functions
class SocialMediaController extends Controller{
    
    var $linkTable = "social_media_links";
    var $linkReportTable = "social_media_link_results";
    var $layout = "ajax";
    var $pageScriptPath = 'social_media.php';
    var $serviceList;
    var $colList;
    // Confirmed dead by live-testing each one with this app's own Spider
    // (not a generic fetch tool) against real public profiles: Facebook
    // and Instagram now redirect unauthenticated requests to a login
    // wall / render zero follower data server-side; Twitter's old
    // follow-button widget endpoint returns HTTP 200 with an empty body
    // (the API was decommissioned); Reddit's modern page no longer has
    // the static subscribers="N" HTML attribute this app's regex looks
    // for. None of these are fixable without each platform's own
    // official, paid/OAuth-gated API - used only to give a clearer
    // failure message (getSocialMediaDetails()), not to disable the
    // platform entirely, in case a future maintainer finds another way.
    var $knownBrokenPlatforms = array('facebook', 'twitter', 'instagram', 'reddit');
    
    function __construct() {
        $engineList = Spider::getCrawlEngineCategoryList("social_media");
    	$this->serviceList = [
    		"facebook" => [
    			"label" => "Facebook",
    			"regex" => [
    			    "like" => $engineList['facebook']['regex1'],
    			    "follower" => $engineList['facebook']['regex2'],
    			],
    		    "url_part" => $engineList['facebook']['url_part'],
    		    "example" => "https://www.facebook.com/seopanel",
    		],
    		"twitter" => [
    		    "label" => "Twitter",
    		    "url" => $engineList['twitter']['url'],
    			"regex" => [
    			    "follower" => $engineList['twitter']['regex1'],
    			],
    		    "example" => "https://twitter.com/seopanel",
    		],
    		"instagram" => [
    			"label" => "Instagram",
    			"regex" => [
    			    "follower" => $engineList['instagram']['regex1'],
    			],
    		    "url_part" => $engineList['instagram']['url_part'],
    		    "example" => "https://www.instagram.com/seopanelorg",
    		],
    		"linkedin" => [
    			"label" => "LinkedIn",
    		    "url" => $engineList['linkedin']['url'],
    		    "regex" => [
    		        "follower" => $engineList['linkedin']['regex1'],
    		    ],
    		    "show_url" => "https://www.linkedin.com/company",
    		    "example" => "14576538",
    		],
    		"pinterest" => [
    			"label" => "Pinterest",
    			"regex" => [
    			    "follower" => $engineList['pinterest']['regex1'],
    			],
    		    "example" => "https://www.pinterest.com/seopanel",
    		],
    		"youtube" => [
    			"label" => "Youtube",
    			"regex" => [
    			    "follower" => $engineList['youtube']['regex1'],
    			],
    		    "url_part" => $engineList['youtube']['url_part'],
    		    "example" => "https://www.youtube.com/c/seopanel",
    		],
    	    "reddit" => [
    	        "label" => "reddit",
    	        "regex" => [
    	            "follower" => $engineList['reddit']['regex1'],
    	        ],
    	        "url_part" => $engineList['reddit']['url_part'],
    	        'example' => 'https://www.reddit.com/r/Twitter',
    	    ],
    	];
    	
    	$this->set('pageScriptPath', $this->pageScriptPath);
    	$this->set( 'serviceList', $this->serviceList );
    	$this->set( 'pageNo', $_REQUEST['pageno']);
		
		$this->colList = array(
			'name' => $_SESSION['text']['common']['Name'],
			'followers' => $_SESSION['text']['label']['Followers'],
			'likes' => $_SESSION['text']['label']['Likes'],
		);
    	
    	parent::__construct();
    }
    
    function showSocialMediaLinks($searchInfo=[]) {
    	$userId = isLoggedIn();

    	$webSiteCtrler = new WebsiteController();
    	$websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
    	if (!isset($searchInfo['website_id']) && !empty($websiteList)) {
    	    $searchInfo['website_id'] = $websiteList[0]['id'];
    	}
    	$this->set('searchInfo', $searchInfo);
    	$sql = "select l.*, w.name as website_name from $this->linkTable l, websites w where l.website_id=w.id";

    	if (!isAdmin()) {
    	    $sql .= " and w.user_id=$userId";
    	}

    	// search conditions
    	$sql .= !empty($searchInfo['name']) ? " and l.name like '%".addslashes($searchInfo['name'])."%'" : "";
    	$sql .= !empty($searchInfo['website_id']) ? " and l.website_id=".intval($searchInfo['website_id']) : "";
    	$sql .= !empty($searchInfo['type']) ? " and `type`='".addslashes($searchInfo['type'])."'" : "";

    	if (!empty($searchInfo['status'])) {
    	    $sql .= ($searchInfo['status'] == 'active') ? " and l.status=1" : " and l.status=0";
    	}

    	$this->set( 'websiteList', $websiteList );
    	 
    	// pagination setup
    	$this->db->query( $sql, true );
    	$this->paging->setDivClass( 'pagingdiv' );
    	$this->paging->loadPaging( $this->db->noRows, SP_PAGINGNO );
    	$pagingDiv = $this->paging->printPages( $this->pageScriptPath, 'searchForm', 'scriptDoLoadPost', 'content', '' );
    	$this->set( 'pagingDiv', $pagingDiv );
    	$sql .= " limit " . $this->paging->start . "," . $this->paging->per_page;
    	
    	$linkList = $this->db->select( $sql );
    	$this->set( 'list', $linkList );
    	$this->render( 'socialmedia/show_social_media_links');
    }
    
    function __checkName($name, $websiteId, $linkId = false){
        $whereCond = "name='".addslashes($name)."'";
        $whereCond .= " and website_id='".intval($websiteId)."'";
        $whereCond .= !empty($linkId) ? " and id!=".intval($linkId) : "";
        $listInfo = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return empty($listInfo['id']) ? false :  $listInfo['id'];
    }
    
    function __checkUrl($url, $websiteId, $linkId = false){
        $whereCond = "url='".addslashes($url)."'";
        $whereCond .= " and website_id=".intval($websiteId);
        $whereCond .= !empty($linkId) ? " and id!=".intval($linkId) : "";
        $listInfo = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return empty($listInfo['id']) ? false :  $listInfo['id'];
    }
    
    // bug fix: SSRF - this used to only check that the platform name
    // appeared SOMEWHERE in the url string (stristr($url, $type)),
    // never the actual host - a url like "http://10.0.0.5/x?y=facebook"
    // passed fine and is fetched server-side (formatMediaLink() only
    // rebuilds a template URL for linkedin/twitter; facebook/instagram/
    // pinterest/youtube/reddit use the raw submitted url as-is). Checks
    // the real host against each platform's known domain(s) instead.
    // (linkedin is excluded - its own field is a bare numeric company
    // id, validated separately above, never a URL at all.)
    function __isUrlForPlatform($url, $type) {
        $allowedDomains = [
            'facebook'  => ['facebook.com', 'fb.com'],
            'twitter'   => ['twitter.com', 'x.com'],
            'instagram' => ['instagram.com'],
            'pinterest' => ['pinterest.com'],
            'youtube'   => ['youtube.com', 'youtu.be'],
            'reddit'    => ['reddit.com'],
        ];
        if (empty($allowedDomains[$type])) return false;

        $host = strtolower((string) parse_url(addHttpToUrl($url), PHP_URL_HOST));
        if (empty($host)) return false;

        foreach ($allowedDomains[$type] as $domain) {
            if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
                return true;
            }
        }
        return false;
    }

    function validateSocialMediaLink($listInfo) {
        $errMsg = [];
        $errMsg['name'] = formatErrorMsg($this->validate->checkBlank($listInfo['name']));
        $errMsg['url'] = formatErrorMsg($this->validate->checkBlank($listInfo['url']));
        $errMsg['website_id'] = formatErrorMsg($this->validate->checkBlank($listInfo['website_id']));
        $errMsg['type'] = formatErrorMsg($this->validate->checkBlank($listInfo['type']));
        
        if(!$this->validate->flagErr){            
            if ($this->__checkName($listInfo['name'], $listInfo['website_id'], $listInfo['id'])) {
                $errMsg['name'] = formatErrorMsg($_SESSION['text']['label']['already exist']);
                $this->validate->flagErr = true;
            }
        }
        
        if(!$this->validate->flagErr){
            if ($this->__checkUrl($listInfo['url'], $listInfo['website_id'], $listInfo['id'])) {
                $errMsg['url'] = formatErrorMsg($_SESSION['text']['label']['already exist']);
                $this->validate->flagErr = true;
            }
        }
        
        if(!$this->validate->flagErr) {
            if ($listInfo['type'] == "linkedin") {
                $errMsg['url'] = formatErrorMsg($this->validate->checkNumber($listInfo['url']));
            } else if (!$this->__isUrlForPlatform($listInfo['url'], $listInfo['type'])) {
                $errMsg['url'] = formatErrorMsg($_SESSION['text']['common']["Invalid value"]);
                $this->validate->flagErr = true;
            }
        }
        
        // Validate link count - only on CREATE. Same fix as
        // ReviewManagerController::validateReviewLink()'s sibling check:
        // an edit (id already set) never increases the count, but
        // validateSocialMediaLinkCount() checks the account's TOTAL
        // current count against its limit regardless - so if the admin
        // had since LOWERED the plan's social_media_link_count below how
        // many links this account already had saved, editing one of
        // those pre-existing links got blocked by the same check meant
        // to gate adding a NEW one.
        if(!$this->validate->flagErr && empty($listInfo['id'])){
            $websiteCtrl = new WebsiteController();
            $websiteInfo = $websiteCtrl->__getWebsiteInfo($listInfo['website_id']);
            if (! $this->validateSocialMediaLinkCount($websiteInfo['user_id'], 1)) {
                $this->set('validationMsg', $this->spTextSMC['Your social media link count already reached the limit']);
                $this->validate->flagErr = true;
            }
        }
        
        return $errMsg;        
    }
    
    // Function to check / validate the user type social media count
    function validateSocialMediaLinkCount($userId, $newCount = 1) {
        $userCtrler = new UserController();
        
        // if admin user id return true
        if ($userCtrler->isAdminUserId($userId)) {
            return true;
        }
        
        $userTypeCtrlr = new UserTypeController();
        $userTypeDetails = $userTypeCtrlr->getUserTypeSpecByUser($userId);
        
        $whereCond = "l.website_id=w.id and w.user_id=".intval($userId);
        $existingInfo = $this->dbHelper->getRow("$this->linkTable l, websites w", $whereCond, "count(*) count");
        $userSMLinkCount = $existingInfo['count'];
        $userSMLinkCount += $newCount;
        
        // if limit is set and not -1
        if (isset($userTypeDetails['social_media_link_count']) && $userTypeDetails['social_media_link_count'] >= 0) {
            
            // check whether count greater than limit
            if ($userSMLinkCount <= $userTypeDetails['social_media_link_count']) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return true;
        }
        
    }
    
    function newSocialMediaLink($info=[]) {
        $userId = isLoggedIn();
        $this->set('post', $info);
        $webSiteCtrler = new WebsiteController();
        $websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
        $this->set( 'websiteList', $websiteList );                
        $this->set('editAction', 'createSocialMediaLink');
        $this->render( 'socialmedia/edit_social_media_link');   
    }
    
    function createSocialMediaLink($listInfo=[], $apiCall=false) {
        $listInfo['name'] = trim($listInfo['name']);
        $listInfo['url'] = trim($listInfo['url']);

        // a non-admin's website_id must be one of their own - previously
        // unchecked, letting any logged-in non-admin attach a bogus link
        // to another user's website (consuming THEIR link-count quota
        // and getting crawled on their behalf by cron). $apiCall=true
        // (e.g. FeatureTourController::createWebsiteSetup()) is trusted
        // to have already verified ownership of the website_id it passes
        // in, same convention WebsiteController::createWebsite()/
        // KeywordController::createKeyword() already use.
        if (!$apiCall && !isAdmin() && !empty($listInfo['website_id']) && !(new WebsiteController())->__verifyWebsiteOwnership($listInfo['website_id'])) {
            showErrorMsg($_SESSION['text']['label']['Access denied']);
            return;
        }

        $errMsg = $this->validateSocialMediaLink($listInfo);

        // if no error occured
        if (!$this->validate->flagErr) {
            $dataList = [
                'name' => $listInfo['name'],
                'url' => ($listInfo['type'] == 'linkedin') ? $listInfo['url'] : addHttpToUrl($listInfo['url']),
                'type' => $listInfo['type'],
                'website_id|int' => $listInfo['website_id'],
            ];
            $this->dbHelper->insertRow($this->linkTable, $dataList);
            if ($apiCall) {
                return array('success', 'Successfully created social media link');
            }
            $this->showSocialMediaLinks(['name' => $listInfo['name']]);
            exit;
        }

        if ($apiCall) {
            return array('error', $errMsg);
        }
        $this->set('errMsg', $errMsg);
        $this->newSocialMediaLink($listInfo);
    }
    
    function editSocialMediaLink($linkId, $listInfo=[]) {        
        if (!empty($linkId)) {
            $userId = isLoggedIn();
            $webSiteCtrler = new WebsiteController();
            $websiteList = $webSiteCtrler->__getAllWebsites($userId, true);
            $this->set( 'websiteList', $websiteList );
            
            if(empty($listInfo)){
                $listInfo = $this->__getSocialMediaLinkInfo($linkId);
            }
            
            $this->set('post', $listInfo);           
            $this->set('editAction', 'updateSocialMediaLink');
            $this->render( 'socialmedia/edit_social_media_link');
        }
        
    }
    
    function updateSocialMediaLink($listInfo) {
        $listInfo['name'] = trim($listInfo['name']);
        $listInfo['url'] = trim($listInfo['url']);

        // the entry point's own verifyActionAllowed($listInfo['id']) only
        // checks the link's CURRENT website_id, not the NEW one being
        // written here - without this, a non-admin could re-point their
        // own existing link onto another user's website_id.
        if (!isAdmin() && !empty($listInfo['website_id']) && !(new WebsiteController())->__verifyWebsiteOwnership($listInfo['website_id'])) {
            showErrorMsg($_SESSION['text']['label']['Access denied']);
            return;
        }

        $this->set('post', $listInfo);
        $errMsg = $this->validateSocialMediaLink($listInfo);
        
        if (!$this->validate->flagErr) {
            $dataList = [
                'name' => $listInfo['name'],
                'url' => ($listInfo['type'] == 'linkedin') ? $listInfo['url'] : addHttpToUrl($listInfo['url']),
                'type' => $listInfo['type'],
                'website_id|int' => $listInfo['website_id'],
            ];
            $this->dbHelper->updateRow($this->linkTable, $dataList, "id=".intval($listInfo['id']));
            $this->showSocialMediaLinks(['name' => $listInfo['name']]);
            exit;
        }
        
        $this->set('errMsg', $errMsg);
        $this->editSocialMediaLink($listInfo['id'], $listInfo);
    }
    
    function deleteSocialMediaLink($linkId) {
        $this->dbHelper->deleteRows($this->linkTable, "id=" . intval($linkId));
        $this->showSocialMediaLinks();
    }
    
    function __changeStatus($linkId, $status){
        $linkId = intval($linkId);
        $this->dbHelper->updateRow($this->linkTable, ['status|int' => $status], "id=$linkId");
    }
    
    function __getSocialMediaLinkInfo($linkId) {
        $whereCond = "id=".intval($linkId);
        $info = $this->dbHelper->getRow($this->linkTable, $whereCond);
        return $info;
    }
    
    function verifyActionAllowed($linkId) {
        $allowed = true;
        
        // if not admin, check the permissions
        if (!isAdmin()) {
            $userId = isLoggedIn();
            $linkInfo = $this->__getSocialMediaLinkInfo($linkId);
            $webSiteCtrler = new WebsiteController();
            $webSiteInfo = $webSiteCtrler->__getWebsiteInfo($linkInfo['website_id']);
            $allowed = ($userId == $webSiteInfo['user_id']) ? true : false;
        }
        
        if (!$allowed) {
            showErrorMsg($_SESSION['text']['label']['Access denied']); 
        }
                
    }

	function viewQuickChecker($info=[]) {
		$this->render('socialmedia/quick_checker');
	}

	function doQuickChecker($listInfo=[]) {
	    // bug fix: this had zero rate limiting of any kind, unlike the
	    // saved-link workflow (quota-checked via validateSocialMediaLinkCount()).
	    // A user blocked from adding a 4th saved link could still Quick-
	    // Check unlimited arbitrary URLs - this is the more exploitable
	    // SSRF surface in this controller (see __isUrlForPlatform()'s own
	    // comment below), since it's a synchronous live fetch with no
	    // stored-link ownership gate at all. Reuses the same generic,
	    // fail-open rate limiter UserController::__checkLoginRateLimit()
	    // already uses for login attempts (AIVisibilityController::
	    // __checkRateLimit() - not AI-Visibility-specific, just where the
	    // shared ai_visibility_rate_limit table happens to live).
	    $userId = isLoggedIn();
	    include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
	    $aivCtrler = new AIVisibilityController();
	    if (!$aivCtrler->__checkRateLimit('social-quickcheck-user:' . intval($userId), 10)) {
	        showErrorMsg($this->spTextSMC['Too many checks - please wait a moment and try again']);
	        return;
	    }

	    if ($listInfo['type'] == 'linkedin') {
	        $errorMsg = formatErrorMsg($this->validate->checkNumber($listInfo['url']));
	        $smLink = $listInfo['url'];
	    } else {
	        $smLink = addHttpToUrl($listInfo['url']);
	        // bug fix: SSRF - this still used the old stristr($url, $type)
	        // check (just "does the platform name appear somewhere in the
	        // url string"), the exact gap __isUrlForPlatform() was added
	        // to close elsewhere - a url like
	        // "http://10.0.0.5/x?y=facebook" passed this check fine. This
	        // path is actually the MORE exploitable of the two: it's an
	        // immediate, synchronous getSocialMediaDetails() fetch for any
	        // logged-in user, with no stored-link row/ownership gate at
	        // all (unlike the saved-link workflow this check was
	        // originally fixed for).
	        if (!$this->__isUrlForPlatform($listInfo['url'], $listInfo['type'])) {
    			$errorMsg = formatErrorMsg($_SESSION['text']['common']["Invalid value"]);
    			$this->validate->flagErr = true;
            }
	   }

		// if no error occured find social media details
		if (!$this->validate->flagErr) {			
			$result = $this->getSocialMediaDetails($listInfo['type'], $smLink);
			
			// if call is success
			if ($result['status']) {
				$this->set('smType', $listInfo['type']);
				$this->set('smLink', $smLink);
				$this->set('statInfo', $result);
				$this->render('socialmedia/quick_checker_results');
				exit;
			} else {
				$errorMsg = $result['msg'];
			}
			
		}
		
		$errorMsg = !empty($errorMsg) ? $errorMsg : $_SESSION['text']['common']['Internal error occured'];
		showErrorMsg($errorMsg);
		
	}
	
	function formatMediaLink($smType, $smLink) {
	    $smInfo = $this->serviceList[$smType];
	    $smLink = str_ireplace("http://", "https://", $smLink);
	    
	    // switch through the social media types
	    switch ($smType) {
	        case "facebook":
	            $smLink = strtok($smLink, '?');	            
	            $smLink = str_ireplace(["//facebook.com", "//www.facebook.com"], "//m.facebook.com", $smLink);
	            $smLink = preg_replace('/\/$/', '', $smLink);
	            $smLink .= "/community/";
	            break;
	            
	        case "linkedin":
	            $smLink = str_replace("{CID}", $smLink, $smInfo['url']);
	            break;
	            
	        case "twitter":
	            $smLink = strtok($smLink, '?');
	            $smLink = preg_replace('/\/$/', '', $smLink);
	            $linkList = explode('/', $smLink);
	            $acountName = array_pop($linkList);
	            if (!empty($acountName)) {
	               $smLink = str_replace("{ACC_NAME}", $acountName, $smInfo['url']);
	            } else {
	                $smLink = "";
	            }
	            break;
	            
	        case "youtube":
	            $smLink = strtok($smLink, '?');
	            $smLink = preg_replace('/\/$/', '', $smLink);
	            break;
	            
	        case "instagram":
	            $smLink = strtok($smLink, '?');
	            $smLink = preg_replace('/\/$/', '', $smLink);
	            break;

	        // bug fix: pinterest/reddit fell through this switch with no
	        // case at all, unlike every other plain profile-url type
	        // (youtube/instagram) - currently harmless only because both
	        // platforms' DB-configured url_part happens to be empty right
	        // now, but a saved URL with a trailing slash or query string
	        // (e.g. copied from a browser with UTM params) would silently
	        // skip the same cleanup every other type gets. Same treatment
	        // as youtube/instagram above.
	        case "pinterest":
	        case "reddit":
	            $smLink = strtok($smLink, '?');
	            $smLink = preg_replace('/\/$/', '', $smLink);
	            break;
	    }
	    
	    // if params needs to be added with url
	    if (!empty($smInfo['url_part'])) {
	        $smLink .= stristr($smLink, '?') ? str_replace("?", "&", $smInfo['url_part']) : $smInfo['url_part'];
	    }
	    
	    return $smLink;
	}
	
	function getSocialMediaDetails($smType, $smLink) {
		$result = ['status' => 0, 'likes' => 0, 'followers' => 0, 'msg' => $_SESSION['text']['common']['Internal error occured']];
		
		$smInfo = $this->serviceList[$smType];
		if (!empty($smInfo) && !empty($smLink)) {
			$smLink = $this->formatMediaLink($smType, $smLink);
			$smContentInfo = $this->spider->getContent($smLink);
			
			// testing val
            /*$myfile = fopen(SP_TMPPATH . "/smcpage.html", "w") or die("Unable to open file!");
            fwrite($myfile, $smContentInfo['page']);
            fclose($myfile);
            exit;
            
            $smContentInfo = [];
            $myfile = fopen(SP_TMPPATH . "/smcpage.html", "r") or die("Unable to open file!");
            $smContentInfo['page'] = fread($myfile,filesize(SP_TMPPATH . "/smcpage.html"));
            fclose($myfile);*/			
			
			if (!empty($smContentInfo['page'])) {
			    $matches = [];

				// find likes
				if (!empty($smInfo['regex']['like'])) {
					preg_match($smInfo['regex']['like'], $smContentInfo['page'], $matches);
					if (!empty($matches[1])) {
						$result['status'] = 1;
						$result['likes'] = formatNumber($matches[1]);
					}
				}
				
				// find followers
				if (!empty($smInfo['regex']['follower'])) {
					preg_match($smInfo['regex']['follower'], $smContentInfo['page'], $matches);
					if (!empty($matches[1])) {
						$result['status'] = 1;
						$result['followers'] = formatNumber($matches[1]);
					}	
				}
				
				// if not found any details
				if (!$result['status']) {
				    $result['msg'] = "Social media channel details not found.";
				} else {
				    $result['msg'] = "Social media channel details fetched successfully.";
				}
			} else {
				$result['msg'] = $smContentInfo['errmsg'];
			}
		}

		// bug fix: on a failed check, this always showed the same generic
		// "details not found" (or, for a platform whose request itself
		// comes back empty - e.g. Twitter's decommissioned widget API -
		// an outright BLANK message), indistinguishable from the user
		// having simply typed the wrong link. For the platforms confirmed
		// dead (live-tested with this app's own Spider, not just assumed -
		// see $knownBrokenPlatforms' own comment), replace whatever
		// message resulted with one naming the real cause.
		if (!$result['status'] && in_array($smType, $this->knownBrokenPlatforms, true)) {
			$label = !empty($this->serviceList[$smType]['label']) ? $this->serviceList[$smType]['label'] : ucfirst($smType);
			$result['msg'] = "Automated checks for this platform currently don't work due to login/API restrictions on {$label}'s side - this is a known platform limitation, not an issue with your link.";
		}

		return $result;
	}
	
	/*
	 * function to get all links with out reports for a day
	 */
	function getAllLinksWithOutReports($websiteId, $date) {
		$websiteId = intval($websiteId);
		$date = addslashes($date);
		$sql = "select link.*, lr.id result_id from social_media_links link left join 
			social_media_link_results lr on (link.id=lr.sm_link_id and lr.report_date='$date') 
			where link.status=1 and link.website_id=$websiteId and lr.id is NULL";
		
		$linkList = $this->db->select($sql);
		return $linkList;
		
	}
	
	function saveSocialMediaLinkResults($linkId, $linkInfo) {
		$dataList = [
			'sm_link_id|int' => $linkId,
			'likes|int' => $linkInfo['likes'],
			'followers|int' => $linkInfo['followers'],
			'report_date' => date('Y-m-d'),
		];

		$this->dbHelper->insertRow($this->linkReportTable, $dataList);
	}

	# func to show generate reports interface - Social Media was the only
	# SEO tool with no manual "Generate Reports Now" at all (every sibling
	# tool - Rank, Backlink, PageSpeed, Saturation - has one), leaving an
	# admin 100% dependent on the next cron run with no way to refresh a
	# link on demand. Same website-select + Proceed pattern as those.
	function showGenerateReports($searchInfo = []) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		// bug fix: see __setGenerateReportsEstimate()'s own comment -
		// computed here too (not just on the AJAX refresh below) so the
		// very first render already shows the real estimate, instead of
		// the generic "click to generate" text until the website_id
		// dropdown is touched (the same "wrong until you interact" bug
		// class this research pass already fixed elsewhere).
		$this->__setGenerateReportsEstimate($websiteId);

		$this->render('socialmedia/generatereport');
	}

	/*
	 * bug fix: generateReports() below gives the admin zero upfront
	 * indication of how long a run will take before they click Proceed -
	 * each link costs a real HTTP fetch plus sleep(SP_CRAWL_DELAY + 5)
	 * (there's no way to batch N different platforms' URLs into one
	 * call, unlike Rank/Backlink Checker's single Moz API call for an
	 * entire site list), so a website - or "all websites" - with many
	 * pending links can take minutes with no warning at all. Pure count,
	 * no crawling - mirrors generateReports()'s own website-scoping and
	 * per-website getAllLinksWithOutReports() call exactly, so the
	 * estimate always matches what a real run would actually do.
	 */
	function __setGenerateReportsEstimate($websiteId) {
		$userId = isLoggedIn();
		$websiteId = empty($websiteId) ? '' : intval($websiteId);

		$sql = "select id from websites where status=1";
		if (!empty($userId) && !isAdmin()) $sql .= " and user_id=$userId";
		if (!empty($websiteId)) $sql .= " and id=$websiteId";
		$websiteList = $this->db->select($sql);

		$today = date('Y-m-d');
		$linkCount = 0;
		foreach ($websiteList as $websiteInfo) {
			$linkCount += count($this->getAllLinksWithOutReports($websiteInfo['id'], $today));
		}

		$this->set('linkCount', $linkCount);
		$this->set('websiteCount', count($websiteList));
		$this->set('estimatedSeconds', $linkCount * (intval(SP_CRAWL_DELAY) + 5));
	}

	# AJAX refresh of the estimate above, fired when the website_id
	# dropdown changes
	function estimateGenerateReports($searchInfo = []) {
		$websiteId = empty($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		$this->__setGenerateReportsEstimate($websiteId);
		$this->render('socialmedia/generatereport_estimate');
	}

	# func to generate reports - reuses the exact same crawl/save calls
	# (and the already-fixed "only save on a successful crawl" guard) as
	# CronController::socialMediaCheckerCron()
	function generateReports($searchInfo = []) {
		$userId = isLoggedIn();
		$websiteId = empty($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);

		$sql = "select id,name,url from websites where status=1";
		if (!empty($userId) && !isAdmin()) $sql .= " and user_id=$userId";
		if (!empty($websiteId)) $sql .= " and id=$websiteId";
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);

		if (count($websiteList) <= 0) {
			echo "<p class='note'>" . $_SESSION['text']['common']['nowebsites'] . "!</p>";
			exit;
		}

		$today = date('Y-m-d');
		foreach ($websiteList as $websiteInfo) {
			$linkList = $this->getAllLinksWithOutReports($websiteInfo['id'], $today);

			if (empty($linkList)) {
				echo "<p class='note'>" . $this->spTextSMC['No social media links left to generate report for'] . " <b>{$websiteInfo['name']}</b>.....</p>";
				continue;
			}

			foreach ($linkList as $linkInfo) {
				$result = $this->getSocialMediaDetails($linkInfo['type'], $linkInfo['url']);

				// only save on a successful crawl - same false-0
				// protection as the cron path
				if ($result['status']) {
					$this->saveSocialMediaLinkResults($linkInfo['id'], $result);
					echo "<p class='note notesuccess'>" . $this->spTextSMC['Saved social media results of'] . " <b>{$linkInfo['name']}</b>.....</p>";
				} else {
					echo "<p class='note error'>" . $this->spTextSMC['Failed crawling social media results of'] . " <b>{$linkInfo['name']}</b>.....</p>";
					error_log("SocialMediaController::generateReports(): crawl failed for link {$linkInfo['id']} ({$linkInfo['name']}, website {$websiteInfo['id']}) - " . ($result['msg'] ?? 'unknown error'));
				}
				// bug fix: this slept SP_CRAWL_DELAY alone, while
				// CronController::socialMediaCheckerCron() sleeps
				// SP_CRAWL_DELAY + 5 for the exact same per-link crawl -
				// an unexplained inconsistency between the two paths
				// doing identical work. Matched to the cron's spacing.
				sleep(SP_CRAWL_DELAY + 5);
			}
		}
	}
	
	/*
	 * func to show report summary
	 */ 
	function __getSocialMediaDateAvailability($conditions) {
		$sql = "select min(r.report_date) as min_date, max(r.report_date) as max_date
				from $this->linkTable sml, $this->linkReportTable r
				where sml.id=r.sm_link_id and sml.status=1 $conditions";
		return $this->__getMinMaxDate($sql);
	}

	function viewReportSummary($searchInfo=[], $summaryPage = false, $cronUserId=false) {
		$userId = !empty($cronUserId) ? $cronUserId : isLoggedIn();
		$this->set('summaryPage', $summaryPage);
		$this->set('searchInfo', $searchInfo);
		$this->set('cronUserId', $cronUserId);

		$exportVersion = false;
		switch($searchInfo['doc_type']){

			case "export":
				$exportVersion = true;
				$exportContent = "";
				break;

			case "pdf":
				$this->set('pdfVersion', true);
				break;

			case "print":
				$this->set('printVersion', true);
				break;
		}

		$websiteController = New WebsiteController();
		$wList = $websiteController->__getAllWebsites($userId, true);
		$websiteList = [];
		foreach ($wList as $wInfo) $websiteList[$wInfo['id']] = $wInfo;
		$websiteList = count($websiteList) ? $websiteList : array(0);
		$this->set('websiteList', $websiteList);
		$websiteId = isset($searchInfo['website_id']) ? intval($searchInfo['website_id']) : null;
		// a non-admin's website_id must be one of their own (already-scoped)
		// websites - previously unchecked, letting any non-admin view ANY
		// other user's social media report summary for an arbitrary
		// website_id.
		if (!empty($websiteId) && !isAdmin() && !isset($websiteList[$websiteId])) {
			$websiteId = 0;
		}
		// resolve to a real website (first one, same default the select
		// box itself now always pre-selects) only when nothing was
		// submitted at all - the search form's own select never submits
		// an empty value any more. An explicit 0 is left alone:
		// ReportController::sentEmailNotificationForReportGen()'s "Overall
		// Report Summary" email deliberately passes website_id=0 to mean
		// every one of this user's websites combined, which still needs
		// the WEBSITE column below to tell rows apart.
		if ($websiteId === null) {
			$websiteId = !empty($websiteList) ? array_key_first($websiteList) : 0;
		}
		$this->set('websiteId', $websiteId);

		$websiteIds = array_keys($websiteList);
		if (!empty($websiteId)) {
			$conditions = " and sml.website_id=" . intval($websiteId);
		} elseif (!empty($websiteIds)) {
			$conditions = " and sml.website_id in (" . implode(',', $websiteIds) . ")";
		} else {
			// no websites at all - "in ()" with no values is a SQL syntax
			// error, so match nothing instead
			$conditions = " and 1=0";
		}
		$conditions .= !empty($searchInfo['search_name']) ? " and (sml.url like '%".addslashes($searchInfo['search_name'])."%' or sml.name like '%".addslashes($searchInfo['search_name'])."%')" : "";
		$conditions .= !empty($searchInfo['type']) ? " and sml.type='".addslashes($searchInfo['type'])."'" : "";

		$period = $this->__resolvePeriodValue($searchInfo);

		if (!empty($period)) {
			list($minAvailDate, $maxAvailDate) = $this->__getSocialMediaDateAvailability($conditions);
			list($fromTimeStamp, $toTimeStamp) = $this->__resolvePeriodPreset($period, $minAvailDate, $maxAvailDate, 'today');
			$fromTime = date('Y-m-d', $fromTimeStamp);
			$toTime = date('Y-m-d', $toTimeStamp);
		} else {
			// bug fix: from_time/to_time were only ever addslashes()'d
			// (SQL-safe) before being echoed raw into HTML attributes in
			// social_media_report_summary.ctp.php - reflected XSS. Only
			// ever meant to be plain dates, so whitelist the format instead.
			$fromTime = (!empty($searchInfo['from_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['from_time'])) ? $searchInfo['from_time'] : date('Y-m-d', strtotime('-1 days'));
			$toTime = (!empty($searchInfo['to_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['to_time'])) ? $searchInfo['to_time'] : date('Y-m-d');
		}
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);
		$this->set('period', $period);

		// to find order col - order_col is caller-supplied and lands
		// directly in an ORDER BY clause with no way to parameterize an
		// identifier position, so it must be checked against a fixed
		// whitelist (not just addslashes()'d, which does nothing for an
		// unquoted SQL identifier) - previously unchecked, letting any
		// logged-in non-admin run a blind SQL injection via ORDER BY
		// (e.g. a CASE/SLEEP() expression), same fix shape Site Auditor
		// already uses for its own order_col.
		if (!empty($searchInfo['order_col']) && array_key_exists($searchInfo['order_col'], $this->colList)) {
			$orderCol = $searchInfo['order_col'];
			$orderVal = getOrderByVal($searchInfo['order_val']);
		} else {
			$orderCol = "followers";
			$orderVal = 'DESC';
		}

		$this->set('orderCol', $orderCol);
		$this->set('orderVal', $orderVal);
		$scriptName = $summaryPage ? "archive.php" : $this->pageScriptPath;
		$scriptPath = SP_WEBPATH . "/$scriptName?sec=reportSummary&website_id=$websiteId";
		// bug fix: reflected XSS - search_name/type were concatenated raw
		// into this query string, which the view embeds inside an
		// onclick="scriptDoLoad('...')" attribute (report_summary.ctp.php) -
		// a quote in either value broke out and ran on page load, no click
		// needed. urlencode() is both the correct way to build a query
		// string and closes the hole.
		$scriptPath .= "&from_time=$fromTime&to_time=$toTime&search_name=" . urlencode($searchInfo['search_name'] ?? '') . "&type=" . urlencode($searchInfo['type'] ?? '');
		$scriptPath .= "&order_col=$orderCol&order_val=$orderVal&report_type=social-media-reports";
	
		$subSql = "select [cols] from $this->linkTable sml, $this->linkReportTable r where sml.id=r.sm_link_id
		and sml.status=1 $conditions and r.report_date='$toTime'";
	
		$sql = "
		(" . str_replace("[cols]", "sml.id,sml.url,sml.name,sml.website_id,sml.type,r.likes,r.followers", $subSql) . ")
			UNION
			(select sml.id,sml.url,sml.name,sml.website_id,sml.type,0,0 from $this->linkTable sml where sml.status=1 $conditions
			and sml.id not in (". str_replace("[cols]", "distinct(sml.id)", $subSql) ."))
		order by " . addslashes($orderCol) . " " . addslashes($orderVal);
	
		if ($orderCol != 'name') $sql .= ", name";
	
		// pagination setup, if not from cron job email send function, pdf and export action
		if (!in_array($searchInfo['doc_type'], array("pdf", "export"))) {
			$this->db->query($sql, true);
			$this->paging->setDivClass('pagingdiv');
			$this->paging->loadPaging($this->db->noRows, SP_PAGINGNO);
			$pagingDiv = $this->paging->printPages($scriptPath, '', 'scriptDoLoad', 'content', "");
			$this->set('pagingDiv', $pagingDiv);
			$this->set('pageNo', $searchInfo['pageno']);
			$sql .= " limit ".$this->paging->start .",". $this->paging->per_page;
		}
	
		# set report list
		$baseReportList = $this->db->select($sql);
		$this->set('baseReportList', $baseReportList);
		$this->set('colList', $this->colList);
	
		// if keywords existing
		if (!empty($baseReportList)) {
				
			$keywordIdList = array();
			foreach ($baseReportList as $info) {
				$keywordIdList[] = $info['id'];
			}
	
			$sql = "select sml.id,sml.url,sml.website_id,sml.type,r.followers,r.likes
			from $this->linkTable sml, $this->linkReportTable r where sml.id=r.sm_link_id
			and sml.status=1 $conditions and r.report_date='$fromTime'";
			$sql .= " and sml.id in(" . implode(",", $keywordIdList) . ")";
			$reportList = $this->db->select($sql);
			$compareReportList = array();
				
			foreach ($reportList as $info) {
				$compareReportList[$info['id']] = $info;
			}
				
			$this->set('compareReportList', $compareReportList);
				
		}
	
		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$reportHeading =  $this->spTextTools['Social Media Report Summary']."($fromTime - $toTime)";
			$exportContent .= createExportContent( array('', $reportHeading, ''));
			$exportContent .= createExportContent( array());
			$headList = empty($websiteId) ? array($spText['common']['Website'], $spText['common']['Url']) : array($spText['common']['Url']);
	
			$pTxt = str_replace("-", "/", substr($fromTime, -5));
			$cTxt = str_replace("-", "/", substr($toTime, -5));
			foreach ($this->colList as $colKey => $colLabel) {
				if ($colKey == 'name') continue;
				$headList[] = $colLabel . "($pTxt)";
				$headList[] = $colLabel . "($cTxt)";
				$headList[] = $colLabel . "(+/-)";
			}
	
			$exportContent .= createExportContent($headList);
			foreach($baseReportList as $listInfo){
			    // listUrl is user-supplied (link url, or linkedin's company
			    // id stored in name) - neutralize before it reaches a
			    // spreadsheet export (CSV formula/DDE injection)
			    $listUrl = neutralizeCsvFormula(($listInfo['type'] == 'linkedin') ? $listInfo['name'] : $listInfo['url']);
				$valueList = empty($websiteId) ? array($websiteList[$listInfo['website_id']]['url'] ?? '', $listUrl) : array($listUrl);
				foreach ($this->colList as $colName => $colVal) {
					if ($colName == 'name') continue;
						
					$currRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
					$prevRank = isset($compareReportList[$listInfo['id']][$colName]) ? $compareReportList[$listInfo['id']][$colName] : 0;
					$rankDiff = "";
	
					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $currRank - $prevRank;
					}
	
					$valueList[] = $prevRank;
					$valueList[] = $currRank;
					$valueList[] = $rankDiff;
				}
	
				$exportContent .= createExportContent( $valueList);
			}
				
			if ($summaryPage) {
				return $exportContent;
			} else {
				exportToCsv('social_media_report_summary', $exportContent);
			}
				
		} else {
				
			// if pdf export
			if ($summaryPage) {
				return $this->getViewContent('socialmedia/social_media_report_summary');
			} else {
				// if pdf export
				if ($searchInfo['doc_type'] == "pdf") {
					exportToPdf($this->getViewContent('socialmedia/social_media_report_summary'), "social_media_report_summary_$fromTime-$toTime.pdf");
				} else {
					$this->set('searchInfo', $searchInfo);
					$this->render('socialmedia/social_media_report_summary');
				}
			}
				
		}
	}
	
	function __getSocialMediaLinks($whereCond = false) {
	    $linkList = $this->dbHelper->getAllRows($this->linkTable, $whereCond);
	    return !empty($linkList) ? $linkList : false;
	}
	
	// func to show detailed reports
	function viewDetailedReports($searchInfo=[]) {
		$userId = isLoggedIn();

		// bug fix: from_time/to_time were only ever addslashes()'d
		// (SQL-safe) before being echoed raw into an HTML attribute in
		// social_media_reports.ctp.php - reflected XSS. Only ever meant to
		// be plain dates, so whitelist the format instead.
		if (!empty($searchInfo['from_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['from_time'])) {
			$fromTimeDate = $searchInfo['from_time'];
		} else {
			$fromTimeDate = date('Y-m-d', strtotime('-15 days'));
		}

		if (!empty($searchInfo['to_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['to_time'])) {
			$toTimeDate = $searchInfo['to_time'];
		} else {
			$toTimeDate = date('Y-m-d');
		}
		
		$this->set('fromTime', $fromTimeDate);
		$this->set('toTime', $toTimeDate);
	
	    if(!empty($searchInfo['link_id']) && !empty($searchInfo['rep'])){				
			$searchInfo['link_id'] = intval($searchInfo['link_id']);
			$linkInfo = $this->__getSocialMediaLinkInfo($searchInfo['link_id']);
			$searchInfo['website_id'] = $linkInfo['website_id'];
		}
	
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		// a non-admin's website_id must be one of their own (already-scoped)
		// websites - previously unchecked. Same fallback as the other
		// dashboard/tool fixes this session: their own first website.
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = '';
		}
		if (empty($websiteId)) $websiteId = $websiteList[0]['id'] ?? '';
		$this->set('websiteId', $websiteId);

		$linkList = $this->__getSocialMediaLinks("website_id=$websiteId and status=1 order by name");
		$this->set('linkList', $linkList);
		// bug fix: social_media_link_select_box.ctp.php references
		// $linkNull but it was never set anywhere, so a website with 0
		// social media links rendered a completely empty, unusable
		// <select> (zero <option>s) instead of a "-- Select --"
		// placeholder. Only needed for the empty-list case - when real
		// links exist, the dropdown always auto-selects one (below) and
		// there's no "select nothing" state to offer. Same bug,
		// independently present in ReviewManagerController's sibling
		// select box too.
		$this->set('linkNull', empty($linkList));
		$linkId = empty($searchInfo['link_id']) ? $linkList[0]['id'] : intval($searchInfo['link_id']);
		// same check one level down: a non-admin's link_id must belong to
		// the just-resolved (already-scoped) linkList - previously
		// unchecked, so a foreign link_id bypassed the website_id scoping
		// entirely (this table has no direct website ownership column of
		// its own to filter on in the query below).
		if (!empty($linkId) && !isAdmin() && !in_array($linkId, array_column($linkList, 'id'))) {
			$linkId = $linkList[0]['id'] ?? '';
		}
		$this->set('linkId', $linkId);

		$list = [];
		if (!empty($linkId)) {

    		$sql = "select s.* from $this->linkReportTable s
    		where report_date>='$fromTimeDate' and report_date<='$toTimeDate' and s.sm_link_id=$linkId
    		order by s.report_date";
    		$reportList = $this->db->select($sql);
    		
    		$colList = array_keys($this->colList);
    		array_shift($colList);
    		foreach ($colList as $col) $prevRank[$col] = 0;
    		
    		# loop through rank
    		foreach ($reportList as $key => $repInfo) {
    			
    			// if not the first row, find differences in rank
    			if ($key)  {

    				foreach ($colList as $col) $rankDiff[$col] = '';

    				foreach ($colList as $col) {
    					// bug fix: $reportList[$key]['rank_diff_'.$col] was
    					// only ever set INSIDE this loop body, which a zero
    					// diff (no change since the previous day) skipped
    					// entirely via the continue below - leaving the key
    					// completely unset instead of ''. Both
    					// social_media_reports.ctp.php's rank_diff_followers/
    					// rank_diff_likes echo it directly, no isset check,
    					// so every zero-change day threw an undefined array
    					// key warning. Set once, up front, for every column,
    					// regardless of whether the diff turns out to be zero.
    					$reportList[$key]['rank_diff_'.$col] = '';
    					$rankDiff[$col] = round($repInfo[$col] - $prevRank[$col], 2);
    					if (empty($rankDiff[$col])) continue;

    					// bug fix: dead branch, copy-pasted from Rank
    					// Checker's own report-diff code - $colList here is
    					// always name/followers/likes (see $this->colList
    					// above), average_position is a Rank Checker
    					// column that can never appear in this loop.
    					$rankClass = ($rankDiff[$col] > 0) ? 'green' : 'red';

    					$rankDiff[$col] = "<font class='$rankClass'>($rankDiff[$col])</font>";
    					$reportList[$key]['rank_diff_'.$col] = $rankDiff[$col];
    				}

    			}
    				
    			foreach ($colList as $col) $prevRank[$col] = $repInfo[$col];
    		
    		}
    		
    		$list = array_reverse($reportList, true);
		}
		
		$this->set('list', $list);
		$this->set('localAiAvailable', SettingsController::isLocalAIEnabled());
		$this->render('socialmedia/social_media_reports');

	}

	/*
	 * AJAX action: plain-language AI summary of this social media link's
	 * follower/like trend over the selected date range - see
	 * LocalAIController::summarizeSocialMediaTrend(). Never auto-fired;
	 * returns JSON for the "Summarize with AI" button in
	 * social_media_reports.ctp.php. Ownership is enforced by
	 * summarizeSocialMediaTrend() itself, not re-checked here.
	 */
	function summarizeTrend($info) {
		$userId = isLoggedIn();
		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		include_once(SP_CTRLPATH . '/localai.ctrl.php');
		$result = (new LocalAIController())->summarizeSocialMediaTrend($info['link_id'], $userId, $fromTime, $toTime);
		header('Content-Type: application/json');
		print json_encode($result);
	}

	// func to show social media link select box
	function showSocialMediaLinkSelectBox($websiteId, $linkId = ""){
	    $websiteId = intval($websiteId);
	    // previously unchecked - any logged-in non-admin could enumerate
	    // another user's social media link names/ids via an arbitrary
	    // website_id, unlike every other single-target action in
	    // social_media.php
	    if (!isAdmin() && !(new WebsiteController())->__verifyWebsiteOwnership($websiteId)) {
	        return;
	    }
	    $linkList = $this->__getSocialMediaLinks("website_id=$websiteId and status=1 order by name");
	    $this->set('linkList', $linkList);
	    // bug fix: same $linkNull fix as viewDetailedReports()/viewGraphReports()
	    $this->set('linkNull', empty($linkList));
	    $this->set('linkId', $linkId);
	    $this->render('socialmedia/social_media_link_select_box');
	}
	
	// func to show link search reports in graph
	function viewGraphReports($searchInfo=[]) {
	    $userId = isLoggedIn();

	    // bug fix: same from_time/to_time XSS fix as viewDetailedReports() above
	    if (!empty($searchInfo['from_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['from_time'])) {
	        $fromTimeDate = $searchInfo['from_time'];
	    } else {
	        $fromTimeDate = date('Y-m-d', strtotime('-15 days'));
	    }

	    if (!empty($searchInfo['to_time']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $searchInfo['to_time'])) {
	        $toTimeDate = $searchInfo['to_time'];
	    } else {
	        $toTimeDate = date('Y-m-d');
	    }
	    
	    $this->set('fromTime', $fromTimeDate);
	    $this->set('toTime', $toTimeDate);
	    
	    if(!empty($searchInfo['link_id']) && !empty($searchInfo['rep'])){
	        $searchInfo['link_id'] = intval($searchInfo['link_id']);
	        $linkInfo = $this->__getSocialMediaLinkInfo($searchInfo['link_id']);
	        $searchInfo['website_id'] = $linkInfo['website_id'];
	    }
	    
	    $websiteController = New WebsiteController();
	    $websiteList = $websiteController->__getAllWebsites($userId, true);
	    $this->set('websiteList', $websiteList);
	    $websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
	    // same fix as viewDetailedReports() - see that method's comment
	    if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
	        $websiteId = '';
	    }
	    if (empty($websiteId)) $websiteId = $websiteList[0]['id'] ?? '';
	    $this->set('websiteId', $websiteId);

	    $linkList = $this->__getSocialMediaLinks("website_id=$websiteId and status=1 order by name");
	    $this->set('linkList', $linkList);
	    // bug fix: same $linkNull fix as viewDetailedReports() above
	    $this->set('linkNull', empty($linkList));
	    $linkId = empty($searchInfo['link_id']) ? $linkList[0]['id'] : intval($searchInfo['link_id']);
	    if (!empty($linkId) && !isAdmin() && !in_array($linkId, array_column($linkList, 'id'))) {
	        $linkId = $linkList[0]['id'] ?? '';
	    }
	    $this->set('linkId', $linkId);
	    
	    // if reports not empty
	    $colList = $this->colList;
	    array_shift($colList);
	    $this->set('colList', $colList);
	    $this->set('searchInfo', $searchInfo);
	
	    $graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
	    if (!empty($linkId)) {
	        
	        $sql = "select s.* from $this->linkReportTable s
    		where report_date>='$fromTimeDate' and report_date<='$toTimeDate'  and s.sm_link_id=$linkId
    		order by s.report_date";
	        $reportList = $this->db->select($sql);
    		
    		$graphColList = array();
    		if (!empty($searchInfo['attr_type'])) {
    			$graphColList[$searchInfo['attr_type']] = $colList[$searchInfo['attr_type']];
    		} else {
    			//array_pop($colList);
    			$graphColList = $colList;
    		}
    		
    		if (!empty($reportList)) {
    				
    			$dataArr = "['Date', '" . implode("', '", array_values($graphColList)) . "']";
    			 
    			// loop through data list
    			foreach ($reportList as $dataInfo) {
    	
    				$valStr = "";
    				foreach ($graphColList as $seId => $seVal) {
    					$valStr .= ", ";
    					$valStr .= !empty($dataInfo[$seId]) ? $dataInfo[$seId] : 0;
    				}
    	
    				$dataArr .= ", ['{$dataInfo['report_date']}' $valStr]";
    			}
    			 
    			$this->set('dataArr', $dataArr);
    			$this->set('graphTitle', $this->spTextTools['Graphical Reports']);
    			$graphContent = $this->getViewContent('report/graph');
    		} else {
    			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
    		}
    		
	    }
		
		// get graph content
		$this->set('graphContent', $graphContent);
		$this->render('socialmedia/graphicalreport');		
	}	
}
?>