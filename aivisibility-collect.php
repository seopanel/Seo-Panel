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

// PUBLIC, UNAUTHENTICATED endpoint - the AI referral beacon ingest.
// Suppress the session cookie sp-load.php's bootstrap would otherwise set
// (Session::startSession() calls session_start() unconditionally) - the
// snippet promises visitors no cookies, this endpoint must honour that.
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

include_once("includes/sp-load.php");

header_remove('Set-Cookie');
header_remove('Pragma');

// CORS preflight - restricted to the token's registered domain, checked
// inside ingestBeacon() for the actual POST; a preflight has no body to
// look the token up from, so just answer generically and let the real
// POST enforce Origin matching before doing anything.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	if (!empty($_SERVER['HTTP_ORIGIN'])) {
		header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
		header('Vary: Origin');
	}
	header('Access-Control-Allow-Methods: POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type');
	header('Access-Control-Max-Age: 3600');
	http_response_code(204);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	http_response_code(204);
	exit;
}

include_once(SP_CTRLPATH."/aivisibility.ctrl.php");
$controller = New AIVisibilityController();
$controller->ingestBeacon();

?>
