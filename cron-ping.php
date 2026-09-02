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

// PUBLIC, SECRET-GATED endpoint - Zero-Setup Scheduler Phase 2's external
// trigger. An admin points an external cron/uptime service (or their own
// crontab) at this URL with the secret from the Scheduler Health page. This
// is a server-to-server hit, never a browser page - no session should ever
// be created for it, matching the same cookie/cache suppression used by the
// AI Visibility public endpoints.
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

include_once("includes/sp-load.php");

header_remove('Set-Cookie');
header_remove('Pragma');

// Fail closed: ping must be explicitly enabled, a secret must be set, and
// the provided key must match it exactly (hash_equals - constant time).
// No lock is touched and nothing is logged for a rejected request.
if (empty(SP_CRON_PING_ENABLED) || empty(SP_CRON_PING_SECRET)
	|| empty($_GET['key']) || !hash_equals(SP_CRON_PING_SECRET, (string) $_GET['key'])) {
	http_response_code(403);
	exit;
}

include_once(SP_CTRLPATH."/cron.ctrl.php");
$controller = New CronController();
$controller->runPingTrigger();

http_response_code(204);
?>
