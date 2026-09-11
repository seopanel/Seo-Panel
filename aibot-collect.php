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

// PUBLIC, UNAUTHENTICATED endpoint - the AI bot collector ingest. Called
// only by copies of the generated collector script running on customers'
// own servers (a server-to-server POST, never a browser), so unlike
// aivisibility-collect.php there is no Origin/Referer to validate and no
// CORS handling needed.
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

include_once("includes/sp-load.php");

header_remove('Set-Cookie');
header_remove('Pragma');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	http_response_code(204);
	exit;
}

include_once(SP_CTRLPATH."/aivisibility.ctrl.php");
$controller = New AIVisibilityController();
$controller->ingestBotHit();

?>
