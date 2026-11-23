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
			[
				'name' => 'get_ai_overview_summary',
				'description' => 'Get Google AI Overview presence/citation counts across this website\'s tracked keywords.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer']], 'required' => ['website_id']],
			],
			[
				'name' => 'toggle_robots_rule',
				'description' => 'Block or allow one AI platform in this website\'s robots.txt (toggles the current state). Requires an admin-configured, writable document root.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer'], 'platform' => ['type' => 'string', 'description' => 'Platform code, e.g. chatgpt, claude, perplexity']], 'required' => ['website_id', 'platform']],
			],
			[
				'name' => 'regenerate_llms_txt',
				'description' => 'Regenerate this website\'s llms.txt from data SEO Panel already has (title/description/crawled pages). Requires an admin-configured, writable document root. Refuses to overwrite a hand-authored llms.txt unless confirm_overwrite is true.',
				'inputSchema' => ['type' => 'object', 'properties' => ['website_id' => ['type' => 'integer'], 'confirm_overwrite' => ['type' => 'boolean', 'description' => 'Set true to overwrite an existing llms.txt not generated by SEO Panel']], 'required' => ['website_id']],
			],
			[
				'name' => 'add_keyword',
				'description' => 'Start tracking a new keyword for one of your websites. If search_engine_ids is omitted, reuses the same search engine(s) this website\'s other tracked keywords already use (or, for a website with none yet, the account\'s first available search engine).',
				'inputSchema' => ['type' => 'object', 'properties' => [
					'website_id' => ['type' => 'integer'],
					'keyword' => ['type' => 'string', 'description' => 'The keyword/phrase to track'],
					'search_engine_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Optional - specific search engine IDs to track this keyword against'],
				], 'required' => ['website_id', 'keyword']],
			],
			[
				'name' => 'update_keyword',
				'description' => 'Update an existing tracked keyword (rename it, change its status, or retarget which search engines it tracks).',
				'inputSchema' => ['type' => 'object', 'properties' => [
					'keyword_id' => ['type' => 'integer'],
					'keyword' => ['type' => 'string', 'description' => 'Optional - new name for the keyword'],
					'status' => ['type' => 'integer', 'description' => 'Optional - 1 to enable, 0 to pause tracking'],
					'search_engine_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Optional - replaces the search engine IDs this keyword tracks against'],
				], 'required' => ['keyword_id']],
			],
			[
				'name' => 'delete_keyword',
				'description' => 'Stop tracking a keyword and remove its stored rank history.',
				'inputSchema' => ['type' => 'object', 'properties' => ['keyword_id' => ['type' => 'integer']], 'required' => ['keyword_id']],
			],
			[
				'name' => 'create_website',
				'description' => 'Add a new website to track in this account.',
				'inputSchema' => ['type' => 'object', 'properties' => [
					'name' => ['type' => 'string', 'description' => 'A label for the website'],
					'url' => ['type' => 'string', 'description' => 'The website\'s URL'],
				], 'required' => ['name', 'url']],
			],
			[
				'name' => 'get_site_auditor_summary',
				'description' => 'Get the Site Auditor crawl summary (overall score, pages crawled, broken links, HTTPS/mobile-friendly/backlink/indexed counts) for one of your websites.',
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
			case 'get_ai_overview_summary':
				$data = $this->toolGetAiOverviewSummary($userId, intval($args['website_id'] ?? 0));
				break;
			case 'toggle_robots_rule':
				$data = $this->toolToggleRobotsRule($userId, intval($args['website_id'] ?? 0), trim($args['platform'] ?? ''));
				break;
			case 'regenerate_llms_txt':
				$data = $this->toolRegenerateLlmsTxt($userId, intval($args['website_id'] ?? 0), !empty($args['confirm_overwrite']));
				break;
			case 'add_keyword':
				$data = $this->toolAddKeyword($userId, intval($args['website_id'] ?? 0), trim($args['keyword'] ?? ''), $args['search_engine_ids'] ?? []);
				break;
			case 'update_keyword':
				$data = $this->toolUpdateKeyword($userId, intval($args['keyword_id'] ?? 0), $args);
				break;
			case 'delete_keyword':
				$data = $this->toolDeleteKeyword($userId, intval($args['keyword_id'] ?? 0));
				break;
			case 'create_website':
				$data = $this->toolCreateWebsite($userId, trim($args['name'] ?? ''), trim($args['url'] ?? ''));
				break;
			case 'get_site_auditor_summary':
				$data = $this->toolGetSiteAuditorSummary($userId, intval($args['website_id'] ?? 0));
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

	// previous_rank/previous_date are the result just before the latest one
	// for that same (keyword, search engine) pair - rank_change is
	// previous_rank - rank (positive = moved up/improved, negative =
	// dropped), left null whenever either side is missing or "not ranked"
	// (rank 0), same not-ranked convention shouldFireRankAlert() already
	// uses for the real-time alert feature
	function toolGetKeywordRankings($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		$sql = "SELECT k.name AS keyword, se.domain AS search_engine, s.rank, s.result_date,
					(SELECT s3.rank FROM searchresults s3
						WHERE s3.keyword_id = s.keyword_id AND s3.searchengine_id = s.searchengine_id AND s3.id <> s.id
						ORDER BY s3.result_date DESC, s3.id DESC LIMIT 1) AS previous_rank,
					(SELECT s3.result_date FROM searchresults s3
						WHERE s3.keyword_id = s.keyword_id AND s3.searchengine_id = s.searchengine_id AND s3.id <> s.id
						ORDER BY s3.result_date DESC, s3.id DESC LIMIT 1) AS previous_date
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
		$rows = $this->db->select($sql);
		foreach ($rows as &$row) {
			$rank = intval($row['rank']);
			$prevRank = isset($row['previous_rank']) ? intval($row['previous_rank']) : null;
			$row['rank_change'] = ($prevRank !== null && $prevRank > 0 && $rank > 0) ? ($prevRank - $rank) : null;
		}
		return $rows;
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

	function toolGetAiOverviewSummary($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		return (new AIVisibilityController())->__getAioSummaryForWebsite($websiteId);
	}

	// the one write-capable tool in this catalog - still ownership-checked
	// FIRST like every read tool above, then delegates the actual write to
	// AIVisibilityController::__toggleRobotsRuleForWebsite(), the same core
	// logic the web Setup page's toggle switch uses (see toggleRobotsRule())
	function toolToggleRobotsRule($userId, $websiteId, $platform) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}
		if ($platform === '') {
			return ['__error' => 'platform is required'];
		}

		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$result = (new AIVisibilityController())->__toggleRobotsRuleForWebsite($websiteId, $platform, $userId);
		if (empty($result['ok'])) {
			return ['__error' => $result['error'] ?? 'Could not toggle robots rule'];
		}

		return [
			'platform' => $platform,
			'is_blocked' => (bool) $result['is_blocked'],
			'robots_write_error' => $result['error'],
		];
	}

	// second write-capable tool - deliberately a strictly safer one than
	// anything .htaccess-touching: worst case on failure is a wrong
	// STATIC file, not a 500'd site, and __regenerateLlmsTxtForWebsite()
	// already refuses to clobber hand-authored content unless told to
	function toolRegenerateLlmsTxt($userId, $websiteId, $confirmOverwrite) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$result = (new AIVisibilityController())->__regenerateLlmsTxtForWebsite($websiteId, $confirmOverwrite);

		if (!empty($result['confirm_needed'])) {
			return ['__error' => 'llms.txt already exists and was not generated by SEO Panel - call again with confirm_overwrite=true to overwrite it.'];
		}
		if (empty($result['ok'])) {
			return ['__error' => $result['error'] ?? 'Could not regenerate llms.txt'];
		}

		return ['ok' => true, 'website_id' => $websiteId];
	}

	// third write-capable tool, and the first one that creates new data
	// rather than editing a static file - still ownership-checked FIRST
	// like every tool above. Delegates to KeywordController::
	// createKeyword($listInfo, $apiCall=true), the exact same path the
	// REST API (api.ctrl.php) already calls for programmatic keyword
	// creation - but that $apiCall=true path deliberately skips its OWN
	// ownership check (it's "admin-equivalent by design", matching the
	// REST API's unscoped global key), so __assertOwnsWebsite() here is
	// what keeps this MCP tool scoped to the caller's own websites,
	// exactly as every other tool in this file already does.
	function toolAddKeyword($userId, $websiteId, $keywordName, $searchEngineIds = []) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}
		if ($keywordName === '') {
			return ['__error' => 'keyword is required'];
		}

		$searchEngineIds = array_filter(array_map('intval', is_array($searchEngineIds) ? $searchEngineIds : []));

		if (empty($searchEngineIds)) {
			// least-surprising default: whatever search engine(s) this
			// website's own most recent keyword already tracks against,
			// so the caller doesn't have to look up search engine ids
			// just to add one more keyword the same way
			$existing = $this->db->select("SELECT searchengines FROM keywords WHERE website_id=" . intval($websiteId) . " AND status=1 ORDER BY id DESC LIMIT 1", true);
			if (!empty($existing['searchengines'])) {
				$searchEngineIds = array_filter(array_map('intval', explode(':', $existing['searchengines'])));
			} else {
				include_once(SP_CTRLPATH . "/searchengine.ctrl.php");
				$firstEngine = (new SearchEngineController())->__getAllSearchEngines();
				if (!empty($firstEngine[0]['id'])) {
					$searchEngineIds = [intval($firstEngine[0]['id'])];
				}
			}
		}
		if (empty($searchEngineIds)) {
			return ['__error' => 'No search engine available to track this keyword against'];
		}

		include_once(SP_CTRLPATH . "/keyword.ctrl.php");
		list($status, $result) = (new KeywordController())->createKeyword([
			'name' => $keywordName,
			'website_id' => $websiteId,
			'searchengines' => $searchEngineIds,
			'lang_code' => 'en',
			'country_code' => 'US',
			'status' => 1,
		], true);

		if ($status !== 'success') {
			return ['__error' => flattenApiErrorMsg($result)];
		}

		return ['ok' => true, 'website_id' => $websiteId, 'keyword' => $keywordName, 'search_engine_ids' => array_values($searchEngineIds)];
	}

	// fourth write-capable tool - ownership resolved via the keyword's OWN
	// website_id (there's no website_id argument to check directly here),
	// same two-step shape toolDeleteKeyword()/toolUpdateKeyword() below use
	function toolDeleteKeyword($userId, $keywordId) {
		if (empty($keywordId)) {
			return ['__error' => 'keyword_id is required'];
		}

		include_once(SP_CTRLPATH . "/keyword.ctrl.php");
		$keywordCtrler = new KeywordController();
		$keywordInfo = $keywordCtrler->__getKeywordInfo($keywordId);
		if (empty($keywordInfo)) {
			return ['__error' => 'Unknown keyword_id'];
		}
		if (!$this->__assertOwnsWebsite($userId, $keywordInfo['website_id'])) {
			return ['__error' => 'Not authorized for this keyword_id'];
		}

		// $apiCall=true - see KeywordController::__deleteKeyword()'s own
		// docblock for why this is required (its default ownership check
		// is session-based and always false here); ownership was already
		// verified above via __assertOwnsWebsite()
		$keywordCtrler->__deleteKeyword($keywordId, true);
		return ['ok' => true, 'keyword_id' => intval($keywordId)];
	}

	function toolUpdateKeyword($userId, $keywordId, $args) {
		if (empty($keywordId)) {
			return ['__error' => 'keyword_id is required'];
		}

		include_once(SP_CTRLPATH . "/keyword.ctrl.php");
		$keywordCtrler = new KeywordController();
		$keywordInfo = $keywordCtrler->__getKeywordInfo($keywordId);
		if (empty($keywordInfo)) {
			return ['__error' => 'Unknown keyword_id'];
		}
		if (!$this->__assertOwnsWebsite($userId, $keywordInfo['website_id'])) {
			return ['__error' => 'Not authorized for this keyword_id'];
		}

		$keywordInfo['oldName'] = $keywordInfo['name'];
		if (isset($args['keyword']) && trim($args['keyword']) !== '') {
			$keywordInfo['name'] = trim($args['keyword']);
		}
		if (isset($args['status'])) {
			$keywordInfo['status'] = intval($args['status']);
		}
		if (!empty($args['search_engine_ids']) && is_array($args['search_engine_ids'])) {
			$keywordInfo['searchengines'] = array_values(array_filter(array_map('intval', $args['search_engine_ids'])));
		} else {
			$keywordInfo['searchengines'] = array_values(array_filter(array_map('intval', explode(':', $keywordInfo['searchengines']))));
		}

		$keywordCtrler->spTextKeyword = $keywordCtrler->getLanguageTexts('keyword', SP_API_LANG_CODE);
		$return = $keywordCtrler->updateKeyword($keywordInfo, true);
		if ($return[0] !== 'success') {
			return ['__error' => flattenApiErrorMsg($return[1])];
		}

		return ['ok' => true, 'keyword_id' => intval($keywordId)];
	}

	// fifth write-capable tool, and the first that creates a brand new
	// website rather than editing an existing one - no __assertOwnsWebsite()
	// check needed (nothing to own yet); 'userid' pins the new row to the
	// CALLER's own account, since WebsiteController::createWebsite()'s
	// $apiCall=true path would otherwise fall back to isLoggedIn() (always
	// false here - no session), which would silently create the website
	// under user_id=0 instead
	function toolCreateWebsite($userId, $name, $url) {
		if ($name === '' || $url === '') {
			return ['__error' => 'name and url are required'];
		}

		include_once(SP_CTRLPATH . "/website.ctrl.php");
		$websiteCtrler = new WebsiteController();
		$websiteCtrler->spTextWeb = $websiteCtrler->getLanguageTexts('website', SP_API_LANG_CODE);
		$return = $websiteCtrler->createWebsite([
			'name' => $name,
			'url' => $url,
			'userid' => $userId,
		], true);

		if ($return[0] !== 'success') {
			return ['__error' => flattenApiErrorMsg($return[1])];
		}

		return ['ok' => true, 'website_id' => intval($websiteCtrler->db->getMaxId('websites')), 'name' => $name, 'url' => $url];
	}

	function toolGetSiteAuditorSummary($userId, $websiteId) {
		if (!$this->__assertOwnsWebsite($userId, $websiteId)) {
			return ['__error' => 'Not authorized for this website_id'];
		}

		include_once(SP_CTRLPATH . "/siteauditor.ctrl.php");
		$summary = (new SiteAuditorController())->__getProjectSummaryForWebsite($websiteId);
		return $summary !== null ? $summary : ['message' => 'No Site Auditor project set up yet for this website'];
	}
}
?>
