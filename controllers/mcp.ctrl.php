<?php

/**
 * MCP (Model Context Protocol) server - lets a website owner point their
 * own AI agent (Claude Desktop, etc.) at their own SEO Panel data, fully
 * self-hosted: nothing leaves their server. Per-user token auth (NOT the
 * global SP_API_KEY/API_SECRET pair api/api.php uses, which has no
 * per-user scoping at all - any holder of that one key can query any
 * user's data via a client-supplied user_id parameter). Every MCP tool
 * call here instead resolves user_id from the RESOLVED TOKEN and verifies
 * website ownership before touching any data - see __assertOwnsWebsite(),
 * called as the first line of every tool method so a future new tool
 * can't structurally skip it.
 */
class MCPController extends Controller {

	const RATE_LIMIT_PER_MINUTE = 60;

	// ---- token management (mcp-access.php) ----

	function listTokens($userId) {
		$rows = $this->db->select("SELECT id, label, created_at, expires_at, last_used_at, revoked FROM mcp_tokens WHERE user_id=" . intval($userId) . " ORDER BY created_at DESC");
		$this->set('tokenList', $rows);
		$this->render('myaccount/mcp_tokens');
	}

	// func to translate the mcp_tokens.ctp.php form's expiry dropdown value
	// into a DATETIME string (or null for "never expires")
	function __resolveExpiryOption($expiresIn) {
		switch ($expiresIn) {
			case '30d':  return date('Y-m-d H:i:s', strtotime('+30 days'));
			case '90d':  return date('Y-m-d H:i:s', strtotime('+90 days'));
			case '1y':   return date('Y-m-d H:i:s', strtotime('+1 year'));
			default:     return null; // 'never' or anything unrecognized
		}
	}

	function createToken($userId, $label, $expiresIn = null) {
		$token = bin2hex(random_bytes(32));
		$this->dbHelper->insertRow('mcp_tokens', [
			'user_id|int' => $userId,
			'token' => $token,
			'label' => !empty($label) ? trim($label) : 'Untitled',
			'expires_at' => $this->__resolveExpiryOption($expiresIn) ?? 'NULL',
			'created_at' => 'NOW()',
		]);
		$this->set('newToken', $token);
		$this->listTokens($userId);
	}

	// WHERE clause includes user_id - the only thing preventing one user
	// from revoking another's token
	function revokeToken($userId, $tokenId) {
		$this->dbHelper->updateRow('mcp_tokens', ['revoked|int' => 1], "id=" . intval($tokenId) . " and user_id=" . intval($userId));
		$this->listTokens($userId);
	}

	// ---- MCP protocol (api/mcp.php) ----

	// func to resolve a raw bearer token to its mcp_tokens row - the SOLE
	// trust boundary for every MCP request. Updates last_used_at on hit.
	function __resolveToken($rawToken) {
		if (empty($rawToken)) return null;
		$row = $this->dbHelper->getRow('mcp_tokens', "token='" . addslashes($rawToken) . "' and revoked=0 and (expires_at is null or expires_at > NOW())");
		if (empty($row)) return null;
		$this->dbHelper->updateRow('mcp_tokens', ['last_used_at' => 'NOW()'], "id=" . intval($row['id']));
		return $row;
	}

	// func to rate-limit per token, reusing AIVisibilityController's
	// existing bucket table/logic rather than inventing a new one -
	// mcp: prefix namespaces the bucket key so it shares
	// ai_visibility_rate_limit without colliding with AI Visibility's own buckets
	function __checkMcpRateLimit($tokenId) {
		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$aivCtrler = new AIVisibilityController();
		return $aivCtrler->__checkRateLimit('mcp:' . $tokenId, self::RATE_LIMIT_PER_MINUTE);
	}

	// func to verify $websiteId belongs to $userId - MANDATORY first line
	// of every tool method below, so ownership-checking can't be
	// structurally skipped by a future new tool. This is the one property
	// that must not regress relative to api/api.php's unscoped global key.
	function __assertOwnsWebsite($userId, $websiteId) {
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) return true;
		}
		return false;
	}

	// func to dispatch one JSON-RPC 2.0 method call, called from api/mcp.php
	function dispatch($method, $params, $userId) {
		switch ($method) {
			case 'initialize':
				return ['result' => [
					'protocolVersion' => '2024-11-05',
					'serverInfo' => ['name' => 'seopanel-mcp', 'version' => '1.0.0'],
					'capabilities' => ['tools' => new stdClass()],
				]];

			case 'tools/list':
				return ['result' => ['tools' => $this->__toolCatalog()]];

			case 'tools/call':
				$name = $params['name'] ?? '';
				$args = $params['arguments'] ?? [];
				return $this->__callTool($name, $args, $userId);

			default:
				return ['error' => ['code' => -32601, 'message' => 'Method not found']];
		}
	}

	function __toolCatalog() {
		return [
			[
				'name' => 'list_websites',
				'description' => 'List the websites this account manages in SEO Panel.',
				'inputSchema' => ['type' => 'object', 'properties' => new stdClass()],
			],
			[
				'name' => 'get_keyword_rankings',
				'description' => 'Get current keyword rankings for one of your websites.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer']], 'required' => ['website_id']],
			],
			[
				'name' => 'get_backlink_summary',
				'description' => 'Get the latest backlink summary for one of your websites.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer']], 'required' => ['website_id']],
			],
			[
				'name' => 'get_ai_visibility_summary',
				'description' => 'Get AI referral and AI bot crawl totals (last 30 days) for one of your websites.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer']], 'required' => ['website_id']],
			],
		];
	}

	function __callTool($name, $args, $userId) {
		switch ($name) {
			case 'list_websites':
				$data = $this->toolListWebsites($userId);
				break;
			case 'get_keyword_rankings':
				$data = $this->toolGetKeywordRankings($userId, intval($args['website_id'] ?? 0));
				break;
			case 'get_backlink_summary':
				$data = $this->toolGetBacklinkSummary($userId, intval($args['website_id'] ?? 0));
				break;
			case 'get_ai_visibility_summary':
				$data = $this->toolGetAiVisibilitySummary($userId, intval($args['website_id'] ?? 0));
				break;
			default:
				return ['error' => ['code' => -32602, 'message' => 'Unknown tool: ' . $name]];
		}

		if (isset($data['__error'])) {
			return ['error' => ['code' => -32000, 'message' => $data['__error']]];
		}

		return ['result' => ['content' => [['type' => 'text', 'text' => json_encode($data)]]]];
	}

	// ---- tool implementations - each verifies ownership FIRST ----

	function toolListWebsites($userId) {
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		return array_map(function($w) { return ['id' => intval($w['id']), 'name' => $w['name'], 'url' => $w['url']]; }, $websiteList);
	}

	function toolGetKeywordRankings($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		$sql = "SELECT k.name AS keyword, se.domain AS search_engine, s.rank, s.result_date
				FROM keywords k
				JOIN searchresults s ON s.keyword_id = k.id
				JOIN searchengines se ON se.id = s.searchengine_id
				WHERE k.website_id = $websiteId AND k.status = 1
				AND s.id = (
					SELECT s2.id FROM searchresults s2
					WHERE s2.keyword_id = s.keyword_id AND s2.searchengine_id = s.searchengine_id
					ORDER BY s2.result_date DESC, s2.id DESC LIMIT 1
				)
				ORDER BY k.name";
		return $this->db->select($sql);
	}

	function toolGetBacklinkSummary($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		$row = $this->db->select("SELECT * FROM backlinkresults WHERE website_id=$websiteId ORDER BY result_date DESC LIMIT 1", true);
		return !empty($row) ? $row : ['message' => 'No backlink data yet for this website'];
	}

	function toolGetAiVisibilitySummary($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		$fromDate = date('Y-m-d', strtotime('-30 days'));
		$referrals = $this->db->select("SELECT platform, SUM(hits) AS hits FROM ai_referrals WHERE website_id=$websiteId AND hit_date >= '$fromDate' GROUP BY platform ORDER BY hits DESC");
		$botHits = $this->db->select("SELECT platform, SUM(hits) AS hits FROM ai_bot_hits WHERE website_id=$websiteId AND hit_date >= '$fromDate' GROUP BY platform ORDER BY hits DESC");

		return [
			'period' => 'last_30_days',
			'ai_referrals_by_platform' => $referrals,
			'ai_bot_crawls_by_platform' => $botHits,
		];
	}
}
?>
