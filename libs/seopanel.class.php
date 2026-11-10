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

# super class defines all seo panel functions
class Seopanel{
	
	var $data;
	
	// function load seo panel
	function loadSeoPanel() {
		
		# include main classes
		include_once(SP_LIBPATH.'/session.class.php');
		include_once(SP_LIBPATH.'/controller.class.php');
		include_once(SP_LIBPATH.'/view.class.php');
		include_once(SP_LIBPATH.'/validation.class.php');
		include_once(SP_LIBPATH.'/spider.class.php');
		include_once(SP_LIBPATH.'/paging.class.php');
		include_once(SP_LIBPATH.'/pchart.class.php');
		include_once(SP_LIBPATH.'/pdata.class.php');
		include_once(SP_LIBPATH.'/xmlparser.class.php');
		include_once(SP_LIBPATH.'/PHPMailerException.php');
		include_once(SP_LIBPATH.'/PHPMailer.php');
		include_once(SP_LIBPATH.'/SMTP.php');
		// must run before captcha.class.php, which starts a plain,
		// unhardened session itself at file-scope on include (`if
		// (session_status() === PHP_SESSION_NONE) { session_start(); }`)
		// if no session is active yet - once THAT session_start() runs,
		// this call's session_set_cookie_params() would silently no-op
		// (PHP refuses to change cookie params on an already-active
		// session) and every cookie hardening flag below would be lost
		@Session::startSession();
		include_once(SP_LIBPATH.'/captcha.class.php');
		
		// include common functions		
		include_once(SP_INCPATH.'/sp-common.php');
		
		// include coomon controllers classes
		include_once(SP_CTRLPATH.'/country.ctrl.php');
		include_once(SP_CTRLPATH.'/language.ctrl.php');
		include_once(SP_CTRLPATH.'/website.ctrl.php');
		include_once(SP_CTRLPATH.'/keyword.ctrl.php');
		include_once(SP_CTRLPATH.'/user.ctrl.php');
		include_once(SP_CTRLPATH.'/user-type.ctrl.php');
		include_once(SP_CTRLPATH.'/settings.ctrl.php');
		include_once(SP_CTRLPATH."/crawllog.ctrl.php");
		include_once(SP_CTRLPATH.'/timezone.ctrl.php');
		include_once(SP_CTRLPATH.'/searchengine.ctrl.php');	
		include_once(SP_CTRLPATH.'/currency.ctrl.php');
		include_once(SP_CTRLPATH.'/seoplugins.ctrl.php');
		include_once(SP_CTRLPATH.'/alerts.ctrl.php');

		// Global default website - picking one anywhere (every tool page
		// already has its own "Website:" selector submitting website_id)
		// now also becomes the fallback for every OTHER tool's next load,
		// without touching any of those individual pages: whatever
		// website_id actually arrives on THIS request is stashed in the
		// session, and on a LATER request that doesn't submit one at all,
		// it's transparently filled into $_GET/$_POST/$_REQUEST before
		// that page's own controller ever runs - each page's existing
		// "empty($info['website_id']) ? fall back to first website : use
		// it" logic just sees a real value more often, unchanged
		// otherwise. Same mechanism already implemented in spAPI's own
		// loadSeoPanel() (libs/seopanel.class.php there), just keyed off
		// the one website_id param every page already sends instead of a
		// second global_website_id - this codebase has no single shared
		// top-nav selector to justify a separate one. No ownership
		// re-check here on either side: a forged/stale id is no worse
		// than one hand-typed into the URL today, since every page's own
		// real data access already verifies ownership before acting on
		// it regardless of where website_id came from.
		if (isLoggedIn()) {
			if (!empty($_REQUEST['website_id'])) {
				Session::setSession('sp_default_website_id', intval($_REQUEST['website_id']));
			} elseif (empty($_GET['website_id']) && empty($_POST['website_id'])) {
				$defaultWebsiteId = @Session::readSession('sp_default_website_id');
				if (!empty($defaultWebsiteId)) {
					$_REQUEST['website_id'] = $_GET['website_id'] = $_POST['website_id'] = $defaultWebsiteId;
				}
			}
		}

	}
	
	// to set variable to render
	function set($varName, $varValue){
		$this->controller->set($varName, $varValue);
	}

	function setSpecialConditionalAccessGlobals() {

	    // section to set SP_CUSTOM_DEV to provid eread access to users
	    $customAccessUser = false;
	    $userId = isLoggedIn();
	    if ($userId && !isAdmin() && isPluginActivated("Subscription")) {
	    	$userTypeCtler = new UserTypeController();
	    	$userAccessType = $userTypeCtler->getUserAccessType($userId);
			$customAccessUser = ($userAccessType == 'read') ? true : false;
	    }

		define('SP_CUSTOM_DEV', $customAccessUser);
	}
	
}
?>
