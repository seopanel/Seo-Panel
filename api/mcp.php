<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   		   *
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

// PUBLIC entry point, Bearer-token authenticated - the MCP (Model Context
// Protocol) server. Deliberately kept under api/ alongside api.php for
// discoverability, but NOT the same auth: api.php uses one global
// SP_API_KEY/API_SECRET pair with no per-user scoping (user_id is just a
// client-supplied parameter there). MCP tokens are per-user
// (mcp_tokens table) and every tool call re-verifies website ownership
// from the RESOLVED token's user_id - see MCPController::__assertOwnsWebsite().
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

include_once("../includes/sp-load.php");
include_once(SP_CTRLPATH . "/mcp.ctrl.php");

header_remove('Set-Cookie');
header_remove('Pragma');
header('Content-Type: application/json');

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? (function_exists('apache_request_headers') ? (apache_request_headers()['Authorization'] ?? '') : '');
$token = (stripos($authHeader, 'Bearer ') === 0) ? trim(substr($authHeader, 7)) : '';

$controller = new MCPController();
$tokenRow = $controller->__resolveToken($token);

if (empty($tokenRow)) {
	http_response_code(401);
	echo json_encode(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32001, 'message' => 'Invalid or revoked token']]);
	exit;
}

if (!$controller->__checkMcpRateLimit($tokenRow['id'])) {
	http_response_code(429);
	echo json_encode(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32029, 'message' => 'Rate limit exceeded']]);
	exit;
}

// same defensive body-size cap ingestBeacon()/ingestBotHit() already use
// for every other public ingest point in this feature - a JSON-RPC tool
// call never legitimately needs more than a few KB (method name + a
// handful of scalar params), and this was the one endpoint without a cap
$maxBodyBytes = 8192;
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? intval($_SERVER['CONTENT_LENGTH']) : 0;
if ($contentLength > $maxBodyBytes) {
	http_response_code(413);
	echo json_encode(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Request body too large']]);
	exit;
}

$rawBody = file_get_contents('php://input', false, null, 0, $maxBodyBytes + 1);
if (strlen($rawBody) > $maxBodyBytes) {
	http_response_code(413);
	echo json_encode(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Request body too large']]);
	exit;
}

$request = json_decode($rawBody, true);
if (empty($request) || !is_array($request)) {
	http_response_code(400);
	echo json_encode(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'Parse error']]);
	exit;
}

$result = $controller->dispatch($request['method'] ?? '', $request['params'] ?? [], $tokenRow['user_id']);
echo json_encode(['jsonrpc' => '2.0', 'id' => $request['id'] ?? null] + $result);
?>
