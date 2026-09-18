<?php

/**
 * AI Perception Check - a customer-initiated, one-off diagnostic that asks
 * a real hosted AI model (ChatGPT, Claude, or Gemini) what it knows about
 * the customer's own website, using the customer's OWN API key.
 *
 * Unlike every other AI Visibility feature (which is self-hosted / never
 * sends data anywhere) and Local AI (self-hosted Ollama), this feature
 * DOES send the website's name/url to a third-party API - it is opt-in,
 * per-provider, and only ever runs with a key the customer themselves
 * entered. Kept in its own controller/table, deliberately separate from
 * AIVisibilityController and LocalAIController, so that boundary stays
 * unambiguous in the code, not just in the UI copy.
 */
class AiPerceptionController extends Controller {

	const RATE_LIMIT_PER_MINUTE = 5;
	const OPENAI_MODEL = 'gpt-4o-mini';
	const ANTHROPIC_MODEL = 'claude-3-5-haiku-20241022';
	const GOOGLE_MODEL = 'gemini-1.5-flash';
	const ALLOWED_PROVIDERS = ['openai', 'anthropic', 'google'];

	// Scheduled tracking: unlike the one-off "Ask" above, this runs
	// UNATTENDED on a cron schedule - it is the customer's own money being
	// spent automatically, not on a click, so both caps exist specifically
	// to bound worst-case unattended spend to something small and
	// predictable: at most MAX_PROMPTS_PER_WEBSITE prompts, each checked at
	// most once per TRACKING_INTERVAL_DAYS, per configured provider.
	const MAX_PROMPTS_PER_WEBSITE = 10;
	const TRACKING_INTERVAL_DAYS = 7;

	// func to list this user's configured providers (never returns the raw key)
	function __getUserProviders($userId) {
		$userId = intval($userId);
		return $this->db->select("SELECT provider, created_at, RIGHT(api_key, 4) AS key_tail FROM llm_api_keys WHERE user_id=$userId ORDER BY provider");
	}

	// func to fetch a raw key for internal use only - never set()/echoed to a view
	private function __getApiKey($userId, $provider) {
		$userId = intval($userId);
		$provider = addslashes($provider);
		$row = $this->db->select("SELECT api_key FROM llm_api_keys WHERE user_id=$userId AND provider='$provider'", true);
		return !empty($row['api_key']) ? $row['api_key'] : '';
	}

	function showSettings() {
		$userId = isLoggedIn();
		$this->set('providers', $this->__getUserProviders($userId));
		$this->set('spTextAIV', $this->getLanguageTexts('aivisibility', $_SESSION['lang_code']));
		$this->render('aiperception/settings');
	}

	function saveApiKey($info) {
		$userId = isLoggedIn();
		$provider = trim($info['provider'] ?? '');
		$apiKey = trim($info['api_key'] ?? '');

		if (!in_array($provider, self::ALLOWED_PROVIDERS, true) || empty($apiKey)) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$providerSql = addslashes($provider);
		$apiKeySql = addslashes($apiKey);
		$this->db->query(
			"INSERT INTO llm_api_keys (user_id, provider, api_key, created_at)
			 VALUES ($userId, '$providerSql', '$apiKeySql', NOW())
			 ON DUPLICATE KEY UPDATE api_key='$apiKeySql', created_at=NOW()"
		);

		$this->showSettings();
	}

	function removeApiKey($info) {
		$userId = isLoggedIn();
		$provider = trim($info['provider'] ?? '');
		if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}
		$providerSql = addslashes($provider);
		$this->db->query("DELETE FROM llm_api_keys WHERE user_id=$userId AND provider='$providerSql'");
		$this->showSettings();
	}

	function showCheck($info) {
		$userId = isLoggedIn();
		$websiteController = new WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('noWebsites', empty($websiteList));

		$websiteId = !empty($info['website_id']) ? intval($info['website_id']) : 0;
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = 0;
		}
		if (empty($websiteId) && !empty($websiteList)) {
			$websiteId = intval($websiteList[0]['id']);
		}
		$this->set('websiteId', $websiteId);

		$this->set('providers', $this->__getUserProviders($userId));
		$this->set('spTextAIV', $this->getLanguageTexts('aivisibility', $_SESSION['lang_code']));
		$this->render('aiperception/check');
	}

	/*
	 * Scheduled tracking dashboard: this website's tracked prompts, each
	 * one's latest per-provider mentioned/not-mentioned status, and an
	 * overall share-of-voice percentage across every (prompt, provider,
	 * latest check) combination.
	 */
	function showTracking($info) {
		$userId = isLoggedIn();
		$websiteController = new WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('noWebsites', empty($websiteList));

		$websiteId = !empty($info['website_id']) ? intval($info['website_id']) : 0;
		if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
			$websiteId = 0;
		}
		if (empty($websiteId) && !empty($websiteList)) {
			$websiteId = intval($websiteList[0]['id']);
		}
		$this->set('websiteId', $websiteId);

		$prompts = !empty($websiteId) ? $this->__getPromptsWithLatestResults($websiteId) : [];
		$this->set('prompts', $prompts);
		$this->set('shareOfVoice', $this->__getShareOfVoice($websiteId));
		$this->set('promptCap', self::MAX_PROMPTS_PER_WEBSITE);
		$this->set('trackingIntervalDays', self::TRACKING_INTERVAL_DAYS);
		$this->set('providers', $this->__getUserProviders($userId));
		$this->set('spTextAIV', $this->getLanguageTexts('aivisibility', $_SESSION['lang_code']));
		$this->render('aiperception/tracking');
	}

	// func to add a tracked prompt for a website - verifies ownership and
	// the MAX_PROMPTS_PER_WEBSITE cap before inserting
	function addPrompt($info) {
		$userId = isLoggedIn();
		$websiteId = intval($info['website_id'] ?? 0);
		$promptText = trim($info['prompt_text'] ?? '');

		$websiteController = new WebsiteController();
		$ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
		if (empty($websiteId) || empty($promptText) || (!isAdmin() && !in_array($websiteId, $ownedIds))) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$activeCount = intval($this->db->select("SELECT COUNT(*) AS c FROM llm_perception_prompts WHERE website_id=$websiteId AND status=1", true)['c'] ?? 0);
		if ($activeCount >= self::MAX_PROMPTS_PER_WEBSITE) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$this->dbHelper->insertRow('llm_perception_prompts', [
			'website_id|int' => $websiteId,
			'user_id|int'    => $userId,
			'prompt_text'    => mb_substr($promptText, 0, 500),
			'status|int'     => 1,
			'created_at'     => 'NOW()',
		]);

		$this->showTracking(['website_id' => $websiteId]);
	}

	// func to remove a tracked prompt - ownership verified via a join back
	// to websites rather than trusting the prompt's own stored user_id, same
	// shape as every other ownership-gated action this session
	function removePrompt($info) {
		$userId = isLoggedIn();
		$promptId = intval($info['prompt_id'] ?? 0);

		$prompt = $this->db->select("SELECT p.id, p.website_id FROM llm_perception_prompts p
			JOIN websites w ON w.id = p.website_id
			WHERE p.id=$promptId AND (w.user_id=$userId OR " . (isAdmin() ? '1=1' : '1=0') . ")", true);
		if (empty($prompt['id'])) {
			showErrorMsg($_SESSION['text']['label']['Access denied']);
			return;
		}

		$websiteId = intval($prompt['website_id']);
		$this->db->query("DELETE FROM llm_perception_results WHERE prompt_id=$promptId");
		$this->db->query("DELETE FROM llm_perception_prompts WHERE id=$promptId");

		$this->showTracking(['website_id' => $websiteId]);
	}

	// func to list a website's active prompts with each provider's latest result
	function __getPromptsWithLatestResults($websiteId) {
		$websiteId = intval($websiteId);
		$prompts = $this->db->select("SELECT id, prompt_text FROM llm_perception_prompts WHERE website_id=$websiteId AND status=1 ORDER BY id");
		foreach ($prompts as &$prompt) {
			$prompt['results'] = $this->db->select(
				"SELECT r.provider, r.checked_date, r.mentioned FROM llm_perception_results r
				 WHERE r.prompt_id={$prompt['id']} AND r.checked_date = (
					 SELECT MAX(r2.checked_date) FROM llm_perception_results r2
					 WHERE r2.prompt_id = r.prompt_id AND r2.provider = r.provider
				 )
				 ORDER BY r.provider"
			);
		}
		return $prompts;
	}

	// func to compute overall share of voice: of every (prompt, provider)
	// combination's latest check for this website, what fraction mentioned it
	function __getShareOfVoice($websiteId) {
		$websiteId = intval($websiteId);
		if (empty($websiteId)) return null;
		$sql = "SELECT r.mentioned FROM llm_perception_results r
				JOIN llm_perception_prompts p ON p.id = r.prompt_id
				WHERE p.website_id=$websiteId AND p.status=1
				AND r.checked_date = (
					SELECT MAX(r2.checked_date) FROM llm_perception_results r2
					WHERE r2.prompt_id = r.prompt_id AND r2.provider = r.provider
				)";
		$rows = $this->db->select($sql);
		if (empty($rows)) return null;
		$mentioned = count(array_filter($rows, function($r) { return !empty($r['mentioned']); }));
		return round(($mentioned / count($rows)) * 100);
	}

	// func to decide whether an LLM's free-text response counts as
	// "mentioning" the tracked website - a simple, explainable heuristic
	// (case-insensitive substring match on the bare domain or the site
	// name), not an attempt at NLP - good enough for a directional signal,
	// same spirit as the citation substring matching AIOverviewController
	// does for structured search results
	function __isMentioned($responseText, $website) {
		if (empty($responseText)) return false;
		$haystack = mb_strtolower($responseText);

		$domain = !empty($website['url']) ? preg_replace('#^https?://(www\.)?#i', '', rtrim($website['url'], '/')) : '';
		$domain = mb_strtolower(preg_replace('#/.*$#', '', $domain));
		if (!empty($domain) && mb_strpos($haystack, $domain) !== false) {
			return true;
		}

		$name = mb_strtolower(trim($website['name'] ?? ''));
		if (!empty($name) && mb_strlen($name) >= 3 && mb_strpos($haystack, $name) !== false) {
			return true;
		}

		return false;
	}

	/*
	 * Cron-callable: run every DUE (prompt, provider) combination for one
	 * website - due meaning no result row checked within the last
	 * TRACKING_INTERVAL_DAYS days. The "is it due" query runs first and
	 * costs nothing; only a genuinely due combination with a configured key
	 * ever reaches a real network call, so this function safely no-ops for
	 * any website with nothing due and/or no keys configured. Returns the
	 * number of checks actually performed (network calls made), for
	 * logging/testing.
	 */
	function refreshTrackingForWebsite($websiteId) {
		$websiteId = intval($websiteId);
		$website = $this->dbHelper->getRow('websites', "id=$websiteId");
		if (empty($website)) return 0;

		$prompts = $this->db->select("SELECT id, prompt_text, user_id FROM llm_perception_prompts WHERE website_id=$websiteId AND status=1");
		if (empty($prompts)) return 0;

		$cutoff = date('Y-m-d', strtotime('-' . self::TRACKING_INTERVAL_DAYS . ' days'));
		$checksRun = 0;

		foreach ($prompts as $prompt) {
			$configuredProviders = array_column($this->__getUserProviders($prompt['user_id']), 'provider');
			foreach ($configuredProviders as $provider) {
				$lastChecked = $this->db->select(
					"SELECT MAX(checked_date) AS d FROM llm_perception_results WHERE prompt_id={$prompt['id']} AND provider='" . addslashes($provider) . "'",
					true
				);
				$due = empty($lastChecked['d']) || $lastChecked['d'] < $cutoff;
				if (!$due) continue;

				$apiKey = $this->__getApiKey($prompt['user_id'], $provider);
				if (empty($apiKey)) continue; // key removed since the prompt was added - skip silently, don't error

				switch ($provider) {
					case 'openai':
						$result = $this->__callOpenAI($apiKey, $prompt['prompt_text']);
						break;
					case 'anthropic':
						$result = $this->__callAnthropic($apiKey, $prompt['prompt_text']);
						break;
					case 'google':
						$result = $this->__callGoogleGemini($apiKey, $prompt['prompt_text']);
						break;
					default:
						continue 2;
				}
				$checksRun++;

				$mentioned = !empty($result['ok']) ? $this->__isMentioned($result['text'], $website) : false;
				$this->dbHelper->insertRow('llm_perception_results', [
					'prompt_id|int'   => intval($prompt['id']),
					'provider'        => $provider,
					'checked_date'    => date('Y-m-d'),
					'response_text'   => !empty($result['ok']) ? $result['text'] : ('ERROR: ' . ($result['error'] ?? 'unknown')),
					'mentioned|int'   => $mentioned ? 1 : 0,
					'created_at'      => 'NOW()',
				]);
			}
		}

		return $checksRun;
	}

	// func to check+increment this user's AI Perception call bucket, reusing
	// AIVisibilityController's existing rate-limit table/logic (same idiom
	// as MCPController::__checkMcpRateLimit()/LocalAIController::
	// __checkLocalAiRateLimit()) - each call spends the customer's own
	// money against a real third-party API, so this is deliberately the
	// tightest limit of the three
	function __checkPerceptionRateLimit($userId) {
		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$aivCtrler = new AIVisibilityController();
		return $aivCtrler->__checkRateLimit('aiperception:' . $userId, self::RATE_LIMIT_PER_MINUTE);
	}

	/*
	 * AJAX action: ask the caller's configured provider what it knows about
	 * one of the caller's own websites. website_id is verified against the
	 * caller's own website list before anything else runs - the prompt is
	 * fixed/templated (never accepts free-form user text) so this can't
	 * become a general-purpose LLM proxy.
	 */
	function askAboutWebsite($info) {
		header('Content-Type: application/json');
		$userId = isLoggedIn();
		$websiteId = intval($info['website_id'] ?? 0);
		$provider = trim($info['provider'] ?? '');

		if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
			echo json_encode(['ok' => false, 'error' => 'Unknown provider']);
			return;
		}

		$websiteController = new WebsiteController();
		$ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
		if (empty($websiteId) || (!isAdmin() && !in_array($websiteId, $ownedIds))) {
			echo json_encode(['ok' => false, 'error' => 'Access denied']);
			return;
		}

		if (!$this->__checkPerceptionRateLimit($userId)) {
			echo json_encode(['ok' => false, 'error' => 'Too many AI Perception checks - please wait a moment and try again.']);
			return;
		}

		$apiKey = $this->__getApiKey($userId, $provider);
		if (empty($apiKey)) {
			echo json_encode(['ok' => false, 'error' => 'No API key configured for this provider']);
			return;
		}

		$website = $this->dbHelper->getRow('websites', "id=$websiteId");
		$domain = !empty($website['url']) ? preg_replace('#^https?://(www\.)?#i', '', rtrim($website['url'], '/')) : '';
		$name = !empty($website['name']) ? $website['name'] : $domain;

		$prompt = "What do you know about the website \"$name\" ($domain)? "
			. "If someone asked you to recommend a website like this, would you mention it, and why or why not? "
			. "Be honest and concise (3-5 sentences) - if you don't have any information about it, say so plainly.";

		switch ($provider) {
			case 'openai':
				$result = $this->__callOpenAI($apiKey, $prompt);
				break;
			case 'anthropic':
				$result = $this->__callAnthropic($apiKey, $prompt);
				break;
			case 'google':
				$result = $this->__callGoogleGemini($apiKey, $prompt);
				break;
		}

		$result['provider'] = $provider;
		echo json_encode($result);
	}

	// func to call OpenAI's Chat Completions API - never throws, every
	// failure path returns ['ok'=>false,...] so the caller degrades cleanly
	function __callOpenAI($apiKey, $prompt) {
		$payload = [
			'model' => self::OPENAI_MODEL,
			'messages' => [['role' => 'user', 'content' => $prompt]],
			'max_tokens' => 300,
		];

		ob_start();
		$spider = new Spider();
		$spider->_CURL_HTTPHEADER = ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey];
		$spider->_CURLOPT_TIMEOUT = 25;
		$spider->_CURLOPT_POSTFIELDS = json_encode($payload);
		$response = $spider->getContent('https://api.openai.com/v1/chat/completions', false, false);
		ob_end_clean();

		if (empty($response['page'])) {
			return ['ok' => false, 'text' => '', 'error' => 'Could not reach OpenAI'];
		}
		$data = json_decode($response['page'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['ok' => false, 'text' => '', 'error' => 'Invalid response from OpenAI'];
		}
		if (!empty($data['error'])) {
			return ['ok' => false, 'text' => '', 'error' => !empty($data['error']['message']) ? $data['error']['message'] : 'OpenAI request failed'];
		}
		$text = $data['choices'][0]['message']['content'] ?? '';
		if (empty($text)) {
			return ['ok' => false, 'text' => '', 'error' => 'Empty response from OpenAI'];
		}
		return ['ok' => true, 'text' => trim($text), 'error' => null];
	}

	// func to call Anthropic's Messages API
	function __callAnthropic($apiKey, $prompt) {
		$payload = [
			'model' => self::ANTHROPIC_MODEL,
			'max_tokens' => 300,
			'messages' => [['role' => 'user', 'content' => $prompt]],
		];

		ob_start();
		$spider = new Spider();
		$spider->_CURL_HTTPHEADER = ['Content-Type: application/json', 'x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01'];
		$spider->_CURLOPT_TIMEOUT = 25;
		$spider->_CURLOPT_POSTFIELDS = json_encode($payload);
		$response = $spider->getContent('https://api.anthropic.com/v1/messages', false, false);
		ob_end_clean();

		if (empty($response['page'])) {
			return ['ok' => false, 'text' => '', 'error' => 'Could not reach Anthropic'];
		}
		$data = json_decode($response['page'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['ok' => false, 'text' => '', 'error' => 'Invalid response from Anthropic'];
		}
		if (!empty($data['error'])) {
			return ['ok' => false, 'text' => '', 'error' => !empty($data['error']['message']) ? $data['error']['message'] : 'Anthropic request failed'];
		}
		$text = $data['content'][0]['text'] ?? '';
		if (empty($text)) {
			return ['ok' => false, 'text' => '', 'error' => 'Empty response from Anthropic'];
		}
		return ['ok' => true, 'text' => trim($text), 'error' => null];
	}

	// func to call Google's Gemini generateContent API (API key passed as
	// a query param, per Google's own API shape - not a header)
	function __callGoogleGemini($apiKey, $prompt) {
		$payload = [
			'contents' => [['parts' => [['text' => $prompt]]]],
		];

		ob_start();
		$spider = new Spider();
		$spider->_CURL_HTTPHEADER = ['Content-Type: application/json'];
		$spider->_CURLOPT_TIMEOUT = 25;
		$spider->_CURLOPT_POSTFIELDS = json_encode($payload);
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::GOOGLE_MODEL . ':generateContent?key=' . urlencode($apiKey);
		$response = $spider->getContent($url, false, false);
		ob_end_clean();

		if (empty($response['page'])) {
			return ['ok' => false, 'text' => '', 'error' => 'Could not reach Google'];
		}
		$data = json_decode($response['page'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['ok' => false, 'text' => '', 'error' => 'Invalid response from Google'];
		}
		if (!empty($data['error'])) {
			return ['ok' => false, 'text' => '', 'error' => !empty($data['error']['message']) ? $data['error']['message'] : 'Google request failed'];
		}
		$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
		if (empty($text)) {
			return ['ok' => false, 'text' => '', 'error' => 'Empty response from Google'];
		}
		return ['ok' => true, 'text' => trim($text), 'error' => null];
	}
}
