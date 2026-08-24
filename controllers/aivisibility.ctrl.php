<?php

/**
 * Class defines all details about the AI Visibility tool - Phase 1
 * (AI referral tracking via a JS snippet). Setup screen, report screen,
 * and the public beacon ingest handler (called only from
 * aivisibility-collect.php, never from the authenticated dispatch tree).
 */
class AIVisibilityController extends Controller {

	# func to show the setup screen (site picker + snippet + install status)
	function showSetup($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);

		if (empty($websiteList)) {
			$this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
			$this->render('dashboard/no_websites');
			return;
		}

		$websiteId = $this->__resolveWebsiteId($info, $websiteList);
		$this->set('websiteId', $websiteId);

		$siteInfo = $this->__getOrCreateSite($websiteId, $websiteList);
		$this->set('siteInfo', $siteInfo);
		$this->set('snippetUrl', SP_WEBPATH . "/aivisibility.js.php");

		$this->render('aivisibility/setup');
	}

	# func to poll install status (AJAX) - waiting for first hit vs receiving data
	function showInstallStatus($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$websiteId = $this->__resolveWebsiteId($info, $websiteList);

		$siteInfo = empty($websiteId) ? null : $this->dbHelper->getRow('ai_visibility_sites', "website_id=$websiteId");

		header('Content-Type: application/json');
		echo json_encode([
			'status' => !empty($siteInfo['last_seen_at']) ? 'receiving' : 'waiting',
			'last_seen_at' => !empty($siteInfo['last_seen_at']) ? $siteInfo['last_seen_at'] : null,
		]);
		exit;
	}

	# func to show the referral report - time series by platform, top landing pages, platform breakdown
	function showReport($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);

		if (empty($websiteList)) {
			$this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
			$this->render('dashboard/no_websites');
			return;
		}

		$websiteId = $this->__resolveWebsiteId($info, $websiteList);
		$this->set('websiteId', $websiteId);

		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		$this->set('fromTime', $fromTime);
		$this->set('toTime', $toTime);

		$fromTimeSql = addslashes($fromTime);
		$toTimeSql = addslashes($toTime);

		$sql = "select platform, hit_date, sum(hits) as hits from ai_referrals
				where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				group by platform, hit_date order by hit_date";
		$timeSeriesList = $this->db->select($sql);

		// platform totals for the breakdown table, also doubles as the chart's series list
		$platformTotals = [];
		foreach ($timeSeriesList as $row) {
			$platformTotals[$row['platform']] = ($platformTotals[$row['platform']] ?? 0) + intval($row['hits']);
		}
		arsort($platformTotals);
		$this->set('platformTotals', $platformTotals);

		$sql2 = "select url_path, sum(hits) as hits from ai_referrals
				 where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				 group by url_path order by hits desc limit 25";
		$this->set('topPages', $this->db->select($sql2));

		$graphContent = '';
		if (!empty($timeSeriesList)) {
			$platforms = array_keys($platformTotals);
			$matrix = []; // hit_date => platform => hits
			foreach ($timeSeriesList as $row) {
				$matrix[$row['hit_date']][$row['platform']] = intval($row['hits']);
			}
			$dates = array_keys($matrix);
			sort($dates);

			$header = "['" . $_SESSION['text']['common']['Date'] . "'";
			foreach ($platforms as $platform) {
				$header .= ", '" . addslashes($platform) . "'";
			}
			$header .= "]";

			$dataArr = $header;
			foreach ($dates as $date) {
				$dataArr .= ", ['$date'";
				foreach ($platforms as $platform) {
					$dataArr .= ", " . intval($matrix[$date][$platform] ?? 0);
				}
				$dataArr .= "]";
			}

			$this->set('dataArr', $dataArr);
			$this->set('graphTitle', $this->spTextAIV['Referrals over time']);
			$graphContent = $this->getViewContent('report/graph');
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
		$this->set('graphContent', $graphContent);

		$this->render('aivisibility/report');
	}

	/**
	 * AI Overview tab: website-wide summary of Google AI Overview presence
	 * and citation, built entirely from data the AI Overview Tracking
	 * feature already collects (searchresults.aio_* + aio_references) via
	 * the scheduled rank-check cron - no new ingest, purely a different
	 * (website-level, not per-keyword-report-row) presentation of it.
	 */
	function showAIOverviewReport($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);

		if (empty($websiteList)) {
			$this->set('spTextWebsite', $this->getLanguageTexts('website', $_SESSION['lang_code']));
			$this->render('dashboard/no_websites');
			return;
		}

		$websiteId = $this->__resolveWebsiteId($info, $websiteList);
		$this->set('websiteId', $websiteId);

		include_once(SP_CTRLPATH."/aioverview.ctrl.php");
		$websiteInfo = $this->dbHelper->getRow('websites', "id=$websiteId");
		$trackedDomain = !empty($websiteInfo['url']) ? AIOverviewController::registrableDomain($websiteInfo['url']) : '';
		$this->set('trackedDomain', $trackedDomain);
		$this->set('subdomainPolicy', defined('SP_AIO_SUBDOMAIN_MATCH') ? SP_AIO_SUBDOMAIN_MATCH : 'registrable');

		// latest measured (keyword, search engine) rows for this website
		$sql = "SELECT k.id AS keyword_id, k.name AS keyword_name, se.domain AS se_domain, s.*
				FROM searchresults s
				JOIN keywords k ON k.id = s.keyword_id
				JOIN searchengines se ON se.id = s.searchengine_id
				WHERE k.website_id = $websiteId AND k.status = 1 AND s.aio_checked_at IS NOT NULL
				AND s.id = (
					SELECT s2.id FROM searchresults s2
					WHERE s2.keyword_id = s.keyword_id AND s2.searchengine_id = s.searchengine_id
					AND s2.aio_checked_at IS NOT NULL
					ORDER BY s2.result_date DESC, s2.id DESC LIMIT 1
				)
				ORDER BY k.name";
		$rowList = $this->db->select($sql);
		$this->set('rowList', $rowList);

		$summary = ['measured' => 0, 'present' => 0, 'cited' => 0, 'unsupported' => 0];
		foreach ($rowList as $row) {
			$summary['measured']++;
			if (intval($row['aio_supported']) === 0) {
				$summary['unsupported']++;
				continue;
			}
			if (!empty($row['aio_present'])) $summary['present']++;
			if (!empty($row['aio_cited'])) $summary['cited']++;
		}
		$this->set('summary', $summary);

		// which domains get cited across this website's keywords instead of
		// (or alongside) the tracked site, from each keyword's latest check
		$sql2 = "SELECT ar.domain, COUNT(DISTINCT ar.keyword_id) AS keyword_count, COUNT(*) AS citation_count
				 FROM aio_references ar
				 JOIN keywords k ON k.id = ar.keyword_id
				 WHERE k.website_id = $websiteId AND k.status = 1
				 AND ar.checked_date = (
					 SELECT MAX(ar2.checked_date) FROM aio_references ar2 WHERE ar2.keyword_id = ar.keyword_id
				 )
				 GROUP BY ar.domain
				 ORDER BY keyword_count DESC, citation_count DESC
				 LIMIT 25";
		$this->set('competitorDomains', $this->db->select($sql2));

		$this->render('aivisibility/aioverview');
	}

	# func to lazily create (or fetch) the ai_visibility_sites row for a website
	function __getOrCreateSite($websiteId, $websiteList=[]) {
		$websiteId = intval($websiteId);
		if (empty($websiteId)) return null;

		$existing = $this->dbHelper->getRow('ai_visibility_sites', "website_id=$websiteId");
		if (!empty($existing)) {
			return $existing;
		}

		$websiteInfo = null;
		foreach ($websiteList as $w) {
			if ($w['id'] == $websiteId) { $websiteInfo = $w; break; }
		}
		if (empty($websiteInfo)) {
			$websiteInfo = $this->dbHelper->getRow('websites', "id=$websiteId");
		}
		if (empty($websiteInfo)) return null;

		include_once(SP_CTRLPATH."/aioverview.ctrl.php");
		$domain = AIOverviewController::normalizeDomain($websiteInfo['url']);
		$token = bin2hex(random_bytes(24));

		$this->dbHelper->insertRow('ai_visibility_sites', [
			'website_id|int' => $websiteId,
			'token' => $token,
			'domain' => $domain,
			'created_at' => 'NOW()',
		]);

		return $this->dbHelper->getRow('ai_visibility_sites', "website_id=$websiteId");
	}

	# func to resolve/validate the requested website_id against the user's own website list
	function __resolveWebsiteId($info, $websiteList) {
		$requested = !empty($info['website_id']) ? intval($info['website_id']) : 0;
		foreach ($websiteList as $w) {
			if ($w['id'] == $requested) return $requested;
		}
		return !empty($websiteList[0]['id']) ? intval($websiteList[0]['id']) : 0;
	}

	# func to prune referral rows past the configured retention window - called from cron.php's tail
	function pruneOldReferrals() {
		$retentionDays = defined('AIV_REFERRAL_RETENTION_DAYS') ? intval(AIV_REFERRAL_RETENTION_DAYS) : 365;
		$cutoff = date('Y-m-d', strtotime("-$retentionDays days"));
		$this->db->query("DELETE FROM ai_referrals WHERE hit_date < '$cutoff'");
	}

	# func to prune stale rate-limit window rows - called from cron.php's tail
	function pruneRateLimitBuckets() {
		$cutoff = intval(floor(time() / 60)) - 10; // keep the last 10 one-minute windows
		$this->db->query("DELETE FROM ai_visibility_rate_limit WHERE window_start < $cutoff");
	}

	/**
	 * Public beacon ingest - called only from aivisibility-collect.php.
	 * The token identifies the site, it does not authenticate the sender
	 * (see spec/plan security model). Every field here is untrusted input
	 * from an unauthenticated third-party request. Always responds 204
	 * with an empty body, whether accepted or silently dropped - no
	 * information disclosure for anything that fails validation.
	 */
	function ingestBeacon() {
		header('Content-Type: text/plain');
		http_response_code(204);

		$maxBytes = 2048;
		$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? intval($_SERVER['CONTENT_LENGTH']) : 0;
		if ($contentLength > $maxBytes) {
			exit;
		}

		$rawBody = file_get_contents('php://input', false, null, 0, $maxBytes + 1);
		if (empty($rawBody) || strlen($rawBody) > $maxBytes) {
			exit;
		}

		$payload = json_decode($rawBody, true);
		if (empty($payload) || !is_array($payload)) {
			exit;
		}

		$token = isset($payload['t']) ? trim((string)$payload['t']) : '';
		$platform = isset($payload['p']) ? trim((string)$payload['p']) : '';
		$urlPath = isset($payload['u']) ? (string)$payload['u'] : '';

		if ($token === '' || strlen($token) > 64 || $platform === '') {
			exit;
		}

		$siteInfo = $this->dbHelper->getRow('ai_visibility_sites', "token='" . addslashes($token) . "'");
		if (empty($siteInfo)) {
			exit;
		}

		// Origin/Referer validation against the registered domain - not
		// bulletproof (spoofable outside a real browser), stops casual noise
		include_once(SP_CTRLPATH."/aioverview.ctrl.php");
		$originHeader = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
		$originDomain = AIOverviewController::registrableDomain($originHeader);
		$registeredDomain = AIOverviewController::registrableDomain($siteInfo['domain']);
		if ($originHeader === '' || $originDomain === '' || $originDomain !== $registeredDomain) {
			exit;
		}

		if (!empty($_SERVER['HTTP_ORIGIN'])) {
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			header('Vary: Origin');
		}

		$tokenCap = defined('AIV_RATE_LIMIT_PER_TOKEN') ? intval(AIV_RATE_LIMIT_PER_TOKEN) : 120;
		if (!$this->__checkRateLimit('token:' . $token, $tokenCap)) {
			exit;
		}
		$remoteIp = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		if ($remoteIp !== '') {
			$ipCap = defined('AIV_RATE_LIMIT_PER_IP') ? intval(AIV_RATE_LIMIT_PER_IP) : 60;
			if (!$this->__checkRateLimit('ip:' . $remoteIp, $ipCap)) {
				exit;
			}
		}

		$platformInfo = $this->dbHelper->getRow('ai_platforms', "hostname='" . addslashes($platform) . "' and is_active=1");
		if (empty($platformInfo)) {
			exit;
		}

		// re-derive the path server-side - defense in depth, every field is untrusted
		$urlPath = strtok($urlPath, '?');
		$urlPath = strtok($urlPath, '#');
		if (empty($urlPath)) $urlPath = '/';
		$urlPath = mb_substr($urlPath, 0, 2048);

		$websiteId = intval($siteInfo['website_id']);
		$hitDate = date('Y-m-d');
		$urlHashHex = bin2hex(md5($urlPath, true));
		$platformCode = addslashes($platformInfo['platform']);
		$urlPathSql = addslashes($urlPath);

		$sql = "INSERT INTO ai_referrals (website_id, hit_date, platform, url_path, url_hash, hits, created_at, updated_at)
				VALUES ($websiteId, '$hitDate', '$platformCode', '$urlPathSql', UNHEX('$urlHashHex'), 1, NOW(), NOW())
				ON DUPLICATE KEY UPDATE hits = hits + 1, updated_at = NOW()";
		$this->db->query($sql);

		$this->db->query("UPDATE ai_visibility_sites SET last_seen_at=NOW() WHERE id=" . intval($siteInfo['id']));

		exit;
	}

	# func to check+increment a fixed 60s-window rate-limit bucket, returns false when over cap
	function __checkRateLimit($bucketKeyBase, $capPerMinute) {
		$windowStart = intval(floor(time() / 60));
		$bucketKey = addslashes(substr($bucketKeyBase, 0, 100));

		$this->db->query("INSERT INTO ai_visibility_rate_limit (bucket_key, window_start, hit_count)
				VALUES ('$bucketKey', $windowStart, 1)
				ON DUPLICATE KEY UPDATE hit_count = hit_count + 1");

		$row = $this->dbHelper->getRow('ai_visibility_rate_limit', "bucket_key='$bucketKey' and window_start=$windowStart");
		return !empty($row) && intval($row['hit_count']) <= $capPerMinute;
	}

}
?>
