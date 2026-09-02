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

// PUBLIC, UNAUTHENTICATED endpoint - embedded on third-party sites.
// Suppress the session cookie and PHP's default no-cache headers that
// sp-load.php's bootstrap would otherwise set (Session::startSession()
// calls session_start() unconditionally) - this file must never set a
// cookie, and its own Cache-Control below must not be overridden.
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

include_once("includes/sp-load.php");

header_remove('Set-Cookie');
header_remove('Pragma');
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=3600');

// active platform hostnames, kept as data (not constants) so the list
// updates for every already-installed snippet within the cache window
$platformCtrler = new Controller();
$platformList = $platformCtrler->db->select("select hostname from ai_platforms where is_active=1");
$hostnames = array_map(function($row) { return $row['hostname']; }, $platformList);

?>
(function () {
	"use strict";
	try {
		var script = document.currentScript;
		if (!script) return;
		var token = script.getAttribute('data-token');
		if (!token) return;

		var endpoint = script.src.replace(/aivisibility\.js\.php.*$/, 'aivisibility-collect.php');
		var platforms = <?php echo json_encode(array_values($hostnames)); ?>;

		function matchedPlatform(referrerHost) {
			for (var i = 0; i < platforms.length; i++) {
				if (referrerHost === platforms[i]) return platforms[i];
			}
			return null;
		}

		function currentPath() {
			return location.pathname || '/';
		}

		function send(platform, path) {
			var payload = JSON.stringify({ t: token, p: platform, u: path, ts: Math.floor(Date.now() / 1000) });
			try {
				if (navigator.sendBeacon) {
					navigator.sendBeacon(endpoint, payload);
					return;
				}
			} catch (e) {}
			try {
				fetch(endpoint, { method: 'POST', body: payload, keepalive: true, mode: 'cors' }).catch(function () {});
			} catch (e) {}
		}

		function checkAndSend() {
			try {
				var ref = document.referrer;
				if (!ref) return;
				var refHost = '';
				try { refHost = new URL(ref).hostname.toLowerCase(); } catch (e) { return; }
				var platform = matchedPlatform(refHost);
				if (!platform) return;
				send(platform, currentPath());
			} catch (e) {}
		}

		// initial page load
		checkAndSend();

		// SPA route changes via History API - guard so pushState right
		// after load doesn't double-fire against the same referrer check,
		// since document.referrer only reflects the ORIGINAL entry referrer
		var lastPath = currentPath();
		function onRouteChange() {
			var path = currentPath();
			if (path === lastPath) return;
			lastPath = path;
			checkAndSend();
		}
		var origPushState = history.pushState;
		if (typeof origPushState === 'function') {
			history.pushState = function () {
				var ret = origPushState.apply(this, arguments);
				onRouteChange();
				return ret;
			};
		}
		window.addEventListener('popstate', onRouteChange);
	} catch (e) {}
})();
