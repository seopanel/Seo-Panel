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
}
?>
