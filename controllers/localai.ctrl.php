<?php

/**
 * Local AI (Ollama) integration - optional on-server LLM for a plain-
 * language summary of AI Insights findings and on-demand meta-description
 * suggestions, with zero content ever sent to a third party (unlike a
 * cloud AI feature). Every call is gated on SettingsController::
 * isLocalAIEnabled() and degrades cleanly (never throws) when Local AI
 * isn't enabled/configured/reachable - callers always get back
 * ['ok'=>bool, ...] rather than an exception.
 */
class LocalAIController extends Controller {

	const OLLAMA_TAGS_PATH = '/api/tags';
	const OLLAMA_GENERATE_PATH = '/api/generate';
	const RATE_LIMIT_PER_MINUTE = 10;

	// func to test whether the configured (or a raw, not-yet-saved)
	// Ollama base URL is reachable - mirrors DataForSEOController::
	// __checkAPIConnection()'s shape, used by the settings.php "Verify
	// connection" button
	function __checkOllamaConnection($baseUrl) {
		$result = ['status' => false, 'message' => 'Could not connect to Ollama', 'models' => []];
		if (empty($baseUrl)) {
			$result['message'] = 'Ollama Base URL is required';
			return $result;
		}

		ob_start();
		$spider = new Spider();
		$spider->_CURLOPT_TIMEOUT = 5; // same-host/LAN traffic, unlike remote APIs' longer timeouts
		$response = $spider->getContent(rtrim($baseUrl, '/') . self::OLLAMA_TAGS_PATH, false, false);
		ob_end_clean();

		if (empty($response['page'])) {
			$result['message'] = 'Could not connect to Ollama at ' . $baseUrl;
			return $result;
		}

		$responseData = json_decode($response['page'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$result['message'] = 'Invalid response from Ollama';
			return $result;
		}

		$result['status'] = true;
		$result['message'] = 'Success';
		$result['models'] = !empty($responseData['models']) ? $responseData['models'] : [];
		return $result;
	}

	// func to check+increment this user's Local AI call bucket, reusing
	// AIVisibilityController's existing rate-limit table/logic (same idiom
	// as MCPController::__checkMcpRateLimit()) rather than inventing a new
	// one - nothing previously stopped a user repeatedly clicking "Generate
	// AI summary"/"Suggest with AI" and hammering their own local Ollama instance
	function __checkLocalAiRateLimit($userId) {
		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$aivCtrler = new AIVisibilityController();
		return $aivCtrler->__checkRateLimit('localai:' . $userId, self::RATE_LIMIT_PER_MINUTE);
	}

	// func to send one prompt to the configured Ollama model - never
	// throws, every failure path returns ['ok'=>false,'error'=>...] so
	// callers can degrade cleanly. $userId is required for rate limiting -
	// omit only for a context with no logged-in user to scope the bucket to.
	function __callOllama($prompt, $systemPrompt = '', $timeout = 25, $userId = null) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'text' => '', 'error' => 'Local AI is not enabled'];
		}

		if (!empty($userId) && !$this->__checkLocalAiRateLimit($userId)) {
			return ['ok' => false, 'text' => '', 'error' => 'Too many Local AI requests - please wait a moment and try again.'];
		}

		$model = defined('SP_LOCAL_AI_MODEL') ? SP_LOCAL_AI_MODEL : '';
		if (empty($model)) {
			return ['ok' => false, 'text' => '', 'error' => 'No Ollama model configured'];
		}

		$payload = [
			'model' => $model,
			'prompt' => $prompt,
			'stream' => false,
		];
		if (!empty($systemPrompt)) {
			$payload['system'] = $systemPrompt;
		}

		ob_start();
		$spider = new Spider();
		$spider->_CURL_HTTPHEADER = ['Content-Type: application/json'];
		$spider->_CURLOPT_TIMEOUT = $timeout;
		$spider->_CURLOPT_POSTFIELDS = json_encode($payload);
		$response = $spider->getContent(rtrim(SP_LOCAL_AI_URL, '/') . self::OLLAMA_GENERATE_PATH, false, false);
		ob_end_clean();

		if (empty($response['page'])) {
			return ['ok' => false, 'text' => '', 'error' => 'Could not connect to Ollama'];
		}

		$responseData = json_decode($response['page'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['ok' => false, 'text' => '', 'error' => 'Invalid response from Ollama'];
		}

		$httpCode = !empty($response['http_code']) ? intval($response['http_code']) : 0;
		if ($httpCode < 200 || $httpCode >= 300 || empty($responseData['response'])) {
			$errMsg = !empty($responseData['error']) ? $responseData['error'] : 'Ollama request failed';
			return ['ok' => false, 'text' => '', 'error' => $errMsg];
		}

		return ['ok' => true, 'text' => trim($responseData['response']), 'error' => null];
	}

	/**
	 * Generates a plain-language summary paragraph over this website's
	 * ALREADY-GENERATED, deterministic AI Insights findings (sp_recommendations
	 * rows) - the rule-based engine stays the source of truth, the LLM is
	 * only ever asked to restate what's already there, explicitly
	 * instructed never to invent new findings. Empty findings short-circuit
	 * with a canned string, no LLM call made (avoids pointless local compute).
	 */
	function generateInsightsSummary($websiteId, $userId) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		include_once(SP_CTRLPATH . "/recommendations.ctrl.php");
		$recCtrler = new RecommendationsController();
		$findings = $recCtrler->__getStoredRecommendations($websiteId, $userId);

		if (empty($findings)) {
			return ['ok' => true, 'summary' => 'No AI Insights findings for this website right now.', 'error' => null];
		}

		$lines = [];
		foreach ($findings as $row) {
			$lines[] = '- [' . $row['type'] . '] ' . $row['title'] . ': ' . $row['description'];
		}
		$findingsText = implode("\n", $lines);

		$systemPrompt = 'You summarize a list of SEO findings for a website owner in plain language. '
			. 'You must ONLY restate and group the findings given to you - never invent, assume, or add any '
			. 'finding, statistic, or recommendation that is not explicitly present in the list. Keep it to one short paragraph.';
		$prompt = "Findings:\n$findingsText\n\nWrite a one-paragraph plain-language summary of exactly these findings.";

		$result = $this->__callOllama($prompt, $systemPrompt, 25, $userId);
		return ['ok' => $result['ok'], 'summary' => $result['text'], 'error' => $result['error']];
	}

	/**
	 * Suggests a meta description (<=160 chars) for one Site Auditor page
	 * row. Verifies report -> project -> website -> user ownership BEFORE
	 * touching any page content - the one spot besides MCP where an
	 * unscoped report_id could otherwise leak another user's crawled page data.
	 */
	function suggestMetaDescription($reportId, $userId) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Local AI is not enabled'];
		}

		$reportId = intval($reportId);
		$reportInfo = $this->dbHelper->getRow('auditorreports', "id=$reportId");
		if (empty($reportInfo)) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Page not found'];
		}

		$projectInfo = $this->dbHelper->getRow('auditorprojects', "id=" . intval($reportInfo['project_id']));
		if (empty($projectInfo)) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Page not found'];
		}

		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$owns = false;
		foreach ($websiteList as $w) {
			if ($w['id'] == $projectInfo['website_id']) { $owns = true; break; }
		}
		if (!$owns) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Not authorized'];
		}

		$systemPrompt = 'You write concise SEO meta descriptions. Respond with ONLY the meta description text, '
			. 'no quotes, no preamble, at most 160 characters.';
		$prompt = 'Page title: ' . ($reportInfo['page_title'] ?? '') . "\n"
			. 'Page URL: ' . ($reportInfo['page_url'] ?? '') . "\n"
			. 'Keywords: ' . ($reportInfo['page_keywords'] ?? '') . "\n\n"
			. 'Suggest a meta description for this page.';

		$result = $this->__callOllama($prompt, $systemPrompt, 15, $userId);
		$suggestion = !empty($result['text']) ? mb_substr($result['text'], 0, 160) : '';
		return ['ok' => $result['ok'], 'suggestion' => $suggestion, 'error' => $result['error']];
	}

	/**
	 * Drafts a one-paragraph website description (<=300 chars) for llms.txt
	 * from Site Auditor's already-crawled page titles/descriptions -
	 * AIVisibilityController::__buildLlmsTxtContent() reads
	 * websites.description verbatim, so this only ever suggests text for
	 * the user to copy in manually, same restate-only-what's-there pattern
	 * as suggestMetaDescription(). Verifies website ownership BEFORE
	 * touching any crawled page content.
	 */
	function suggestLlmsTxtDescription($websiteId, $userId) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Not authorized'];
		}

		$projectInfo = $this->dbHelper->getRow('auditorprojects', "website_id=$websiteId");
		$pages = !empty($projectInfo['id'])
			? $this->db->select("SELECT page_title, page_description FROM auditorreports WHERE project_id=" . intval($projectInfo['id']) . " ORDER BY pagerank DESC LIMIT 15")
			: [];

		$lines = [];
		foreach ($pages as $page) {
			if (empty($page['page_title']) && empty($page['page_description'])) continue;
			$desc = !empty($page['page_description']) ? ': ' . trim(preg_replace('/\s+/', ' ', $page['page_description'])) : '';
			$lines[] = '- ' . ($page['page_title'] ?? '') . $desc;
		}
		if (empty($lines)) {
			return ['ok' => false, 'suggestion' => '', 'error' => 'Run Site Auditor for this website first - no crawled page data to summarize yet.'];
		}

		$systemPrompt = 'You write a concise one-paragraph website description for an llms.txt file (a summary AI agents read to understand what a site is about). '
			. 'You must ONLY restate what is present in the page list given to you - never invent facts, products, or claims not present there. '
			. 'Respond with ONLY the description text, no quotes, no preamble, at most 300 characters.';
		$prompt = 'Website name: ' . ($websiteInfo['name'] ?? '') . "\n"
			. 'Crawled pages:' . "\n" . implode("\n", $lines) . "\n\n"
			. 'Write a one-paragraph description of this website.';

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		$suggestion = !empty($result['text']) ? mb_substr($result['text'], 0, 300) : '';
		return ['ok' => $result['ok'], 'suggestion' => $suggestion, 'error' => $result['error']];
	}

	/**
	 * Suggests a meta <title> (<=60 chars) and meta description (<=160
	 * chars) for a website's whole-site meta tags (MetaTagGenerator
	 * plugin), from the website's own name/url plus - when Site Auditor
	 * has already crawled it - its top pages' titles/descriptions as
	 * extra context. Unlike generateInsightsSummary()/
	 * suggestLlmsTxtDescription() above, this one's whole point is to
	 * draft new marketing copy rather than only restate existing facts -
	 * the user still reviews and can edit/discard it before it's ever
	 * used, same as every other Local AI suggestion in this app. Verifies
	 * website ownership before touching any crawled page content.
	 */
	function suggestMetaTags($websiteId, $userId) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Not authorized'];
		}

		$context = 'Website name: ' . ($websiteInfo['name'] ?? '') . "\n" . 'Website URL: ' . ($websiteInfo['url'] ?? '');

		$projectInfo = $this->dbHelper->getRow('auditorprojects', "website_id=$websiteId");
		if (!empty($projectInfo['id'])) {
			$pages = $this->db->select("SELECT page_title, page_description FROM auditorreports WHERE project_id=" . intval($projectInfo['id']) . " ORDER BY pagerank DESC LIMIT 10");
			$lines = [];
			foreach ($pages as $page) {
				if (empty($page['page_title']) && empty($page['page_description'])) continue;
				$desc = !empty($page['page_description']) ? ': ' . trim(preg_replace('/\s+/', ' ', $page['page_description'])) : '';
				$lines[] = '- ' . ($page['page_title'] ?? '') . $desc;
			}
			if (!empty($lines)) {
				$context .= "\n\nCrawled pages:\n" . implode("\n", $lines);
			}
		}

		$systemPrompt = 'You write concise, compelling SEO title tags and meta descriptions for websites. '
			. 'Respond with EXACTLY two lines in this format and nothing else:'
			. "\nTITLE: <title, at most 60 characters>"
			. "\nDESCRIPTION: <description, at most 160 characters>";
		$prompt = "$context\n\nSuggest an SEO title tag and meta description for this website's homepage.";

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => $result['error']];
		}

		$title = '';
		$description = '';
		if (preg_match('/TITLE:\s*(.+)/i', $result['text'], $m)) $title = trim($m[1]);
		if (preg_match('/DESCRIPTION:\s*(.+)/i', $result['text'], $m)) $description = trim($m[1]);

		if (empty($title) && empty($description)) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Could not parse a suggestion from the AI response'];
		}

		return [
			'ok' => true,
			'title' => mb_substr($title, 0, 60),
			'description' => mb_substr($description, 0, 160),
			'error' => null,
		];
	}

	/*
	 * Drafts a business directory listing title/description for the
	 * Directory Submission tool. Directory listings traditionally use
	 * several hand-reworded title/description variants (see
	 * DirectoryController::$noTitles) so the same wording isn't submitted
	 * verbatim to dozens of directories - this drafts a genuinely
	 * different phrasing on demand instead of relying on manual
	 * synonym-swapping. Pass $avoid (an existing title+description
	 * elsewhere on the form) to steer the model toward a distinct angle;
	 * omitted for the first/primary listing slot.
	 */
	function suggestDirectoryListing($websiteId, $userId, $avoid = '') {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Not authorized'];
		}

		$context = 'Business/website name: ' . ($websiteInfo['name'] ?? '') . "\n" . 'URL: ' . ($websiteInfo['url'] ?? '');
		if (!empty($websiteInfo['description'])) {
			$context .= "\nExisting site description: " . stripslashes($websiteInfo['description']);
		}

		$systemPrompt = 'You write short business directory listing titles and descriptions. '
			. 'Respond with EXACTLY two lines in this format and nothing else:'
			. "\nTITLE: <title, at most 60 characters>"
			. "\nDESCRIPTION: <description, at most 200 characters>";

		$avoid = trim((string) $avoid);
		if (!empty($avoid)) {
			$prompt = "$context\n\nWrite a directory listing title and description for the SAME business, "
				. "genuinely different in wording and angle from this existing one (not just synonym-swapped):\n$avoid";
		} else {
			$prompt = "$context\n\nWrite a business directory listing title and description for this website.";
		}

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => $result['error']];
		}

		$title = '';
		$description = '';
		if (preg_match('/TITLE:\s*(.+)/i', $result['text'], $m)) $title = trim($m[1]);
		if (preg_match('/DESCRIPTION:\s*(.+)/i', $result['text'], $m)) $description = trim($m[1]);

		if (empty($title) && empty($description)) {
			return ['ok' => false, 'title' => '', 'description' => '', 'error' => 'Could not parse a suggestion from the AI response'];
		}

		return [
			'ok' => true,
			'title' => mb_substr($title, 0, 60),
			'description' => mb_substr($description, 0, 200),
			'error' => null,
		];
	}

	/*
	 * Plain-language summary of a website's domain/page authority + spam
	 * score trend over a date range, for the Rank Checker's "Rank
	 * Reports" screen - same restate-only-the-facts discipline as
	 * generateInsightsSummary(): the system prompt explicitly forbids
	 * inventing a cause the numbers themselves don't show.
	 */
	function summarizeAuthorityTrend($websiteId, $userId, $fromTime, $toTime) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);
		$rows = $this->db->select("SELECT result_date, spam_score, domain_authority, page_authority FROM rankresults WHERE website_id=$websiteId AND result_date >= '$fromTime' AND result_date <= '$toTime' ORDER BY result_date");

		if (count($rows) < 2) {
			return ['ok' => true, 'summary' => 'Not enough history in this date range yet to summarize a trend - check back after a few more Generate Rank Reports runs.', 'error' => null];
		}

		$lines = [];
		foreach ($rows as $row) {
			$lines[] = $row['result_date'] . ': Spam Score ' . round(floatval($row['spam_score']), 2) . '%, Domain Authority ' . round(floatval($row['domain_authority']), 2) . ', Page Authority ' . round(floatval($row['page_authority']), 2);
		}

		$systemPrompt = 'You summarize a website authority metric trend in plain language for a non-technical SEO client. '
			. 'ONLY restate what the numbers show (direction, magnitude, any notable jump) - never invent a cause the data itself does not show. '
			. 'Keep it to 2-3 sentences.';
		$prompt = 'Website: ' . ($websiteInfo['name'] ?? '') . "\n\nDomain Authority/Page Authority (higher is better) and Spam Score (lower is better) history:\n" . implode("\n", $lines);

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'summary' => '', 'error' => $result['error']];
		}

		return ['ok' => true, 'summary' => trim($result['text']), 'error' => null];
	}

	/*
	 * Plain-language summary of a website's backlink/referring-domain (+
	 * broken backlinks, where measured by DataForSEO) trend over a date
	 * range, for the Backlink Checker's "Backlinks Reports" screen - same
	 * restate-only-the-facts discipline as summarizeAuthorityTrend()/
	 * generateInsightsSummary().
	 */
	function summarizeBacklinkTrend($websiteId, $userId, $fromTime, $toTime) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);
		$rows = $this->db->select("SELECT result_date, external_pages_to_page, external_pages_to_root_domain, broken_backlinks FROM backlinkresults WHERE website_id=$websiteId AND result_date >= '$fromTime' AND result_date <= '$toTime' ORDER BY result_date");

		if (count($rows) < 2) {
			return ['ok' => true, 'summary' => 'Not enough history in this date range yet to summarize a trend - check back after a few more Generate Backlinks Reports runs.', 'error' => null];
		}

		$lines = [];
		foreach ($rows as $row) {
			$line = $row['result_date'] . ': ' . intval($row['external_pages_to_page']) . ' backlinks to this page, ' . intval($row['external_pages_to_root_domain']) . ' referring domains to the root domain';
			if ($row['broken_backlinks'] !== null) $line .= ', ' . intval($row['broken_backlinks']) . ' broken backlinks';
			$lines[] = $line;
		}

		$systemPrompt = 'You summarize a website backlink trend in plain language for a non-technical SEO client. '
			. 'ONLY restate what the numbers show (direction, magnitude, any notable jump) - never invent a cause the data itself does not show. '
			. 'Keep it to 2-3 sentences.';
		$prompt = 'Website: ' . ($websiteInfo['name'] ?? '') . "\n\nBacklink/referring-domain history:\n" . implode("\n", $lines);

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'summary' => '', 'error' => $result['error']];
		}

		return ['ok' => true, 'summary' => trim($result['text']), 'error' => null];
	}

	/*
	 * Plain-language summary of a website's Google/Bing indexed-page-count
	 * ("search engine saturation") trend over a date range, for the
	 * Saturation Checker's "Search Engine Saturation Reports" screen -
	 * same restate-only-the-facts discipline as summarizeAuthorityTrend()/
	 * summarizeBacklinkTrend()/generateInsightsSummary().
	 */
	function summarizeSaturationTrend($websiteId, $userId, $fromTime, $toTime) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);
		$rows = $this->db->select("SELECT result_date, google, msn FROM saturationresults WHERE website_id=$websiteId AND result_date >= '$fromTime' AND result_date <= '$toTime' ORDER BY result_date");

		if (count($rows) < 2) {
			return ['ok' => true, 'summary' => 'Not enough history in this date range yet to summarize a trend - check back after a few more Generate Reports runs.', 'error' => null];
		}

		$lines = [];
		foreach ($rows as $row) {
			$lines[] = $row['result_date'] . ': ' . intval($row['google']) . ' pages indexed by Google, ' . intval($row['msn']) . ' pages indexed by Bing';
		}

		$systemPrompt = 'You summarize a website\'s search engine indexation ("saturation") trend in plain language for a non-technical SEO client. '
			. 'ONLY restate what the numbers show (direction, magnitude, any notable jump) - never invent a cause the data itself does not show. '
			. 'Keep it to 2-3 sentences.';
		$prompt = 'Website: ' . ($websiteInfo['name'] ?? '') . "\n\nIndexed-page-count history:\n" . implode("\n", $lines);

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'summary' => '', 'error' => $result['error']];
		}

		return ['ok' => true, 'summary' => trim($result['text']), 'error' => null];
	}

	/*
	 * Cross-tool summary combining Google Analytics (website_analytics -
	 * users/sessions/bounce rate) and Search Console (website_search_analytics
	 * - clicks/impressions/position) for the same website+date range, for
	 * the Website Analytics and Search Console dashboard tabs. Same
	 * restate-only-the-facts discipline as the other trend summaries: the
	 * two datasets are genuinely independent measurements (GA's own
	 * traffic tracking vs Search Console's server-side search data), so a
	 * correlation between them (e.g. organic sessions and Search Console
	 * clicks moving together) is a real, factual co-occurrence worth
	 * surfacing - the prompt allows noting that, but never inventing a
	 * cause neither dataset actually shows.
	 */
	function summarizeTrafficSearchTrend($websiteId, $userId, $fromTime, $toTime) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		$websiteId = intval($websiteId);
		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);

		$gaRows = $this->db->select("SELECT report_date, SUM(users) AS users, SUM(sessions) AS sessions, AVG(bounceRate) AS bounce_rate FROM website_analytics WHERE website_id=$websiteId AND report_date >= '$fromTime' AND report_date <= '$toTime' GROUP BY report_date ORDER BY report_date");
		$scRows = $this->db->select("SELECT report_date, SUM(clicks) AS clicks, SUM(impressions) AS impressions, AVG(average_position) AS avg_position FROM website_search_analytics WHERE website_id=$websiteId AND report_date >= '$fromTime' AND report_date <= '$toTime' GROUP BY report_date ORDER BY report_date");

		if (count($gaRows) < 2 && count($scRows) < 2) {
			return ['ok' => true, 'summary' => 'Not enough history in this date range yet to summarize a trend - check back once more Analytics/Search Console data has been collected.', 'error' => null];
		}

		$lines = [];
		if (!empty($gaRows)) {
			$first = reset($gaRows);
			$last = end($gaRows);
			$lines[] = "Google Analytics: sessions went from {$first['sessions']} on {$first['report_date']} to {$last['sessions']} on {$last['report_date']}, users from {$first['users']} to {$last['users']}, bounce rate from " . round($first['bounce_rate'], 1) . "% to " . round($last['bounce_rate'], 1) . "%.";
		} else {
			$lines[] = "Google Analytics: no data in this date range.";
		}
		if (!empty($scRows)) {
			$first = reset($scRows);
			$last = end($scRows);
			$lines[] = "Search Console: clicks went from {$first['clicks']} on {$first['report_date']} to {$last['clicks']} on {$last['report_date']}, impressions from {$first['impressions']} to {$last['impressions']}, average position from " . round($first['avg_position'], 1) . " to " . round($last['avg_position'], 1) . ".";
		} else {
			$lines[] = "Search Console: no data in this date range.";
		}

		$systemPrompt = 'You summarize a website\'s traffic and search visibility trend in plain language for a non-technical SEO client, '
			. 'using BOTH a Google Analytics figure and a Search Console figure supplied to you. '
			. 'ONLY restate what the numbers show (direction, magnitude) - you may note when the two datasets moved together (a factual co-occurrence), '
			. 'but never invent a cause neither dataset actually shows. Keep it to 2-3 sentences.';
		$prompt = 'Website: ' . ($websiteInfo['name'] ?? '') . "\n\n" . implode("\n", $lines);

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'summary' => '', 'error' => $result['error']];
		}

		return ['ok' => true, 'summary' => trim($result['text']), 'error' => null];
	}

	/*
	 * Plain-language summary of one social media link's follower/like
	 * trend over a date range, for the Social Media tool's Detailed
	 * Reports screen - same restate-only-the-facts discipline as the
	 * other trend summaries. Ownership is via social_media_links.website_id
	 * (the link itself carries no user_id of its own) - verified against
	 * the caller's own website list, same as every other tool this round.
	 */
	function summarizeSocialMediaTrend($linkId, $userId, $fromTime, $toTime) {
		if (!SettingsController::isLocalAIEnabled()) {
			return ['ok' => false, 'summary' => '', 'error' => 'Local AI is not enabled'];
		}

		$linkId = intval($linkId);
		$linkInfo = $this->dbHelper->getRow('social_media_links', "id=$linkId");
		if (empty($linkInfo)) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$websiteList = (new WebsiteController())->__getAllWebsites($userId, true);
		$owned = false;
		foreach ($websiteList as $w) {
			if ($w['id'] == $linkInfo['website_id']) { $owned = true; break; }
		}
		if (!$owned) {
			return ['ok' => false, 'summary' => '', 'error' => 'Not authorized'];
		}

		$fromTime = addslashes($fromTime);
		$toTime = addslashes($toTime);
		$rows = $this->db->select("SELECT report_date, followers, likes FROM social_media_link_results WHERE sm_link_id=$linkId AND report_date >= '$fromTime' AND report_date <= '$toTime' ORDER BY report_date");

		if (count($rows) < 2) {
			return ['ok' => true, 'summary' => 'Not enough history in this date range yet to summarize a trend - check back after a few more report runs.', 'error' => null];
		}

		$first = reset($rows);
		$last = end($rows);
		$prompt = 'Social media link: ' . ($linkInfo['name'] ?? '') . ' (' . ($linkInfo['type'] ?? '') . ")\n\n"
			. "Followers went from {$first['followers']} on {$first['report_date']} to {$last['followers']} on {$last['report_date']}.\n"
			. "Likes went from {$first['likes']} on {$first['report_date']} to {$last['likes']} on {$last['report_date']}.";

		$systemPrompt = 'You summarize a social media account\'s follower/like trend in plain language for a non-technical client. '
			. 'ONLY restate what the numbers show (direction, magnitude) - never invent a cause the data itself does not show. '
			. 'Keep it to 1-2 sentences.';

		$result = $this->__callOllama($prompt, $systemPrompt, 20, $userId);
		if (!$result['ok']) {
			return ['ok' => false, 'summary' => '', 'error' => $result['error']];
		}

		return ['ok' => true, 'summary' => trim($result['text']), 'error' => null];
	}
}
?>
