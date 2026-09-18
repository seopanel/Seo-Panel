<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
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

// Zero-Setup Scheduler, opportunistic trigger: a small fire-and-forget
// beacon fired from the admin layout (see themes/classic/views/layout/
// default.ctp.php) on every admin page load, client-side throttled to
// once every 5 minutes via localStorage. Session-authenticated (normal
// admin login, no secret to leak into page source) - deliberately NOT the
// same code path as cron-ping.php, which stays reserved for an external
// pinger with no session of its own. Same runPingTrigger() underneath
// (same lock, same deadline budget, same no-op-when-disabled guard), just
// a different way in and a distinct 'ping-beacon' trigger_source so the
// two are tellable apart on the Scheduler Health page.
include_once("includes/sp-load.php");
checkAdminLoggedIn();

include_once(SP_CTRLPATH."/cron.ctrl.php");
$controller = New CronController();
$controller->runPingTrigger('ping-beacon');

http_response_code(204);
?>
