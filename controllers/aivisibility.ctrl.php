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
		$this->set('botCollectorUrl', SP_WEBPATH . "/aibot-collector.php?website_id=" . intval($websiteId));

		$this->render('aivisibility/setup');
	}

	/**
	 * Generates the downloadable AI bot collector script for a website.
	 * Called only from aibot-collector.php (logged-in gated - this produces
	 * a file the site owner takes away and hosts on their OWN server, it is
	 * not itself served to the public). Dependency-free, no remote includes
	 * (a remote-includable snippet would be a code-execution risk for
	 * whoever installs it) - just UA prefilter, FCrDNS, and a short-timeout
	 * POST that fails silently so it can never break the host site.
	 *
	 * Classification into a named platform happens server-side at ingest
	 * (see ingestBotHit()), not in this script, so the UA prefilter here is
	 * deliberately broad/generic and never needs to be re-downloaded when
	 * new bots emerge - only the "should I bother with a DNS lookup at all"
	 * decision is static.
	 */
	function generateBotCollectorScript($websiteId) {
		$siteInfo = $this->dbHelper->getRow('ai_visibility_sites', "website_id=" . intval($websiteId));
		$token = !empty($siteInfo['token']) ? $siteInfo['token'] : '';
		$collectUrl = SP_WEBPATH . "/aibot-collect.php";

		$token = addslashes($token);
		$collectUrl = addslashes($collectUrl);

		return <<<PHP
<?php
/**
 * SEO Panel - AI Bot Crawler Collector
 *
 * Detects AI crawler visits (GPTBot, ClaudeBot, PerplexityBot, and others)
 * server-side, since these bots never execute JavaScript. Include this file
 * at the very top of your site's bootstrap (e.g. the first line of
 * index.php or wp-config.php; for WordPress, dropping it into
 * wp-content/mu-plugins/ loads it automatically on every request).
 *
 * This file never breaks your site: it does nothing at all for normal
 * traffic, and any failure (DNS, network, whatever) is silently swallowed.
 */

\$__aiv_ua = isset(\$_SERVER['HTTP_USER_AGENT']) ? \$_SERVER['HTTP_USER_AGENT'] : '';

// Broad, generic prefilter - deliberately not tied to a specific vendor
// list so this file never goes stale. Precise classification happens
// server-side at SEO Panel, this just decides whether to bother at all.
if (\$__aiv_ua !== '' && preg_match('/bot|crawler|spider|agent/i', \$__aiv_ua)) {
	try {
		\$__aiv_ip = isset(\$_SERVER['REMOTE_ADDR']) ? \$_SERVER['REMOTE_ADDR'] : '';
		\$__aiv_verified = false;

		if (\$__aiv_ip !== '') {
			// FCrDNS: reverse-DNS the IP, then forward-confirm it resolves
			// back to the same IP - the same method used to verify
			// Googlebot. Done here, not at SEO Panel, because this is the
			// only point where the real crawler IP is known with certainty.
			//
			// gethostbyaddr()/gethostbyname() have no timeout parameter of
			// their own and go through the OS resolver, so a slow/dead
			// nameserver could otherwise stall every request whose UA loosely
			// matches the prefilter above. default_socket_timeout is the
			// closest lever userland PHP has for this - not honored by every
			// resolver backend, but a real bound on most - so this caps it
			// best-effort and always restores the host's own setting after.
			\$__aiv_prevTimeout = ini_get('default_socket_timeout');
			ini_set('default_socket_timeout', 2);
			try {
				\$__aiv_host = @gethostbyaddr(\$__aiv_ip);
				if (\$__aiv_host && \$__aiv_host !== \$__aiv_ip) {
					\$__aiv_verified = (@gethostbyname(\$__aiv_host) === \$__aiv_ip);
				}
			} finally {
				ini_set('default_socket_timeout', \$__aiv_prevTimeout);
			}
		}

		\$__aiv_path = isset(\$_SERVER['REQUEST_URI']) ? strtok(\$_SERVER['REQUEST_URI'], '?') : '/';

		\$__aiv_payload = json_encode(array(
			't' => '$token',
			'ua' => \$__aiv_ua,
			'verified' => \$__aiv_verified,
			'u' => \$__aiv_path,
		));

		\$__aiv_ctx = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => "Content-Type: application/json\\r\\n",
			'content' => \$__aiv_payload,
			'timeout' => 2,
			'ignore_errors' => true,
		)));
		@file_get_contents('$collectUrl', false, \$__aiv_ctx);
	} catch (Throwable \$__aiv_e) {
		// never let a collector failure affect the host site
	}
}
PHP;
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
			'bot_status' => !empty($siteInfo['bot_last_seen_at']) ? 'receiving' : 'waiting',
			'bot_last_seen_at' => !empty($siteInfo['bot_last_seen_at']) ? $siteInfo['bot_last_seen_at'] : null,
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

	# func to export the AI Referral report (daily hits by platform, same range as showReport()) as CSV
	function exportReportCsv($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$websiteId = $this->__resolveWebsiteId($info, $websiteList);
		if (empty($websiteId)) {
			exit;
		}

		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		$fromTimeSql = addslashes($fromTime);
		$toTimeSql = addslashes($toTime);

		$sql = "select hit_date, platform, sum(hits) as hits from ai_referrals
				where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				group by hit_date, platform order by hit_date, platform";
		$rowList = $this->db->select($sql);

		$this->__streamCsv("ai-referrals-$websiteId-$fromTime-to-$toTime.csv", ['Date', 'Platform', 'Referrals'], $rowList, ['hit_date', 'platform', 'hits']);
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

	/**
	 * AI Bot Crawler Tracking report: platform breakdown (verified vs
	 * unverified), top crawled pages, hits-over-time chart. Data comes from
	 * ai_bot_hits, populated by the downloadable collector script's POSTs
	 * to aibot-collect.php - see generateBotCollectorScript()/ingestBotHit().
	 */
	function showBotReport($info=[]) {
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

		$sql = "select platform, verified, hit_date, sum(hits) as hits from ai_bot_hits
				where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				group by platform, verified, hit_date order by hit_date";
		$timeSeriesList = $this->db->select($sql);

		// platform totals split by verified/unverified for the breakdown table
		$platformTotals = [];
		foreach ($timeSeriesList as $row) {
			$key = $row['platform'];
			if (!isset($platformTotals[$key])) $platformTotals[$key] = ['verified' => 0, 'unverified' => 0];
			$platformTotals[$key][!empty($row['verified']) ? 'verified' : 'unverified'] += intval($row['hits']);
		}
		uasort($platformTotals, function($a, $b) {
			return ($b['verified'] + $b['unverified']) - ($a['verified'] + $a['unverified']);
		});
		$this->set('platformTotals', $platformTotals);

		$sql2 = "select url_path, sum(hits) as hits from ai_bot_hits
				 where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				 group by url_path order by hits desc limit 25";
		$this->set('topPages', $this->db->select($sql2));

		$graphContent = '';
		if (!empty($timeSeriesList)) {
			$platforms = array_keys($platformTotals);
			$matrix = []; // hit_date => platform => hits (verified+unverified combined for the chart)
			foreach ($timeSeriesList as $row) {
				$matrix[$row['hit_date']][$row['platform']] = ($matrix[$row['hit_date']][$row['platform']] ?? 0) + intval($row['hits']);
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
			$this->set('graphTitle', $this->spTextAIV['Bot crawls over time'] ?? 'Bot crawls over time');
			$graphContent = $this->getViewContent('report/graph');
		} else {
			$graphContent = showErrorMsg($_SESSION['text']['common']['No Records Found'], false, true);
		}
		$this->set('graphContent', $graphContent);

		$this->render('aivisibility/botreport');
	}

	# func to export the AI Bot Crawler report (daily hits by platform+verified, same range as showBotReport()) as CSV
	function exportBotReportCsv($info=[]) {
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$websiteId = $this->__resolveWebsiteId($info, $websiteList);
		if (empty($websiteId)) {
			exit;
		}

		$fromTime = !empty($info['from_time']) ? $info['from_time'] : date('Y-m-d', strtotime('-30 days'));
		$toTime = !empty($info['to_time']) ? $info['to_time'] : date('Y-m-d');
		$fromTimeSql = addslashes($fromTime);
		$toTimeSql = addslashes($toTime);

		$sql = "select hit_date, platform, verified, sum(hits) as hits from ai_bot_hits
				where website_id=$websiteId and hit_date >= '$fromTimeSql' and hit_date <= '$toTimeSql'
				group by hit_date, platform, verified order by hit_date, platform";
		$rowList = $this->db->select($sql);
		foreach ($rowList as &$row) {
			$row['verified'] = !empty($row['verified']) ? 'Yes' : 'No';
		}
		unset($row);

		$this->__streamCsv("ai-bot-hits-$websiteId-$fromTime-to-$toTime.csv", ['Date', 'Platform', 'Verified', 'Crawls'], $rowList, ['hit_date', 'platform', 'verified', 'hits']);
	}

	/**
	 * Admin: AI platform catalog manager - add/edit/delete/toggle the
	 * ai_platforms rows that drive both the referral snippet (client-side,
	 * is_referral_source) and bot UA classification (server-side, always
	 * gated on is_active) - see aivisibility.js.php and ingestBotHit().
	 * Lets new answer engines/crawlers be added without a code deploy.
	 * Every entry point here calls checkAdminLoggedIn() itself (redirects
	 * non-admins) since aivisibility.php as a whole is not admin-only.
	 */
	function listPlatforms($info=[]) {
		checkAdminLoggedIn();
		$this->set('platformList', $this->db->select("select * from ai_platforms order by platform, hostname"));
		$this->render('aivisibility/platforms');
	}

	function savePlatform($info) {
		checkAdminLoggedIn();
		$id = intval($info['id'] ?? 0);

		$errMsg = [];
		$errMsg['platform'] = formatErrorMsg($this->validate->checkBlank($info['platform']));
		$errMsg['hostname'] = formatErrorMsg($this->validate->checkBlank($info['hostname']));
		$errMsg['display_name'] = formatErrorMsg($this->validate->checkBlank($info['display_name']));

		if (!$this->validate->flagErr) {
			$hostname = strtolower(trim($info['hostname']));
			$dupCond = "hostname='" . addslashes($hostname) . "'" . ($id ? " and id!=$id" : "");
			$dup = $this->dbHelper->getRow('ai_platforms', $dupCond);
			if (!empty($dup)) {
				$this->validate->flagErr = TRUE;
				$errMsg['hostname'] = formatErrorMsg($this->spTextAIV['platformexistsnotice'] ?? 'A platform with this hostname already exists.');
			}
		}

		if (!$this->validate->flagErr) {
			$dataList = [
				'platform' => strtolower(trim($info['platform'])),
				'hostname' => strtolower(trim($info['hostname'])),
				'display_name' => trim($info['display_name']),
				'is_active|int' => !empty($info['is_active']) ? 1 : 0,
				'is_referral_source|int' => !empty($info['is_referral_source']) ? 1 : 0,
				'bot_ua_pattern' => !empty($info['bot_ua_pattern']) ? trim($info['bot_ua_pattern']) : 'NULL',
				'verify_suffix' => !empty($info['verify_suffix']) ? trim($info['verify_suffix']) : 'NULL',
			];

			if ($id) {
				$this->dbHelper->updateRow('ai_platforms', $dataList, "id=$id");
			} else {
				$this->dbHelper->insertRow('ai_platforms', $dataList);
			}
		} else {
			$this->set('formErrMsg', $errMsg);
			$this->set('formPost', $info);
		}

		$this->listPlatforms();
	}

	function togglePlatformField($info) {
		checkAdminLoggedIn();
		$id = intval($info['id'] ?? 0);
		$field = in_array($info['field'] ?? '', ['is_active', 'is_referral_source']) ? $info['field'] : null;

		if ($id && $field) {
			$current = $this->dbHelper->getRow('ai_platforms', "id=$id");
			if (!empty($current)) {
				$this->dbHelper->updateRow('ai_platforms', [$field . '|int' => empty($current[$field]) ? 1 : 0], "id=$id");
			}
		}

		$this->listPlatforms();
	}

	function deletePlatform($info) {
		checkAdminLoggedIn();
		$id = intval($info['id'] ?? 0);
		if ($id) {
			$this->db->query("delete from ai_platforms where id=$id");
		}
		$this->listPlatforms();
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

	# func to stream a query result set as a downloaded CSV and exit - $cols maps the header row to row keys, in order
	function __streamCsv($fileName, $header, $rowList, $cols) {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) . '"');

		$out = fopen('php://output', 'w');
		fputcsv($out, $header);
		foreach ($rowList as $row) {
			$line = [];
			foreach ($cols as $col) {
				$line[] = $row[$col] ?? '';
			}
			fputcsv($out, $line);
		}
		fclose($out);
		exit;
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

	# func to prune bot hit rows past the configured retention window - called from cron.php's tail
	function pruneOldBotHits() {
		$retentionDays = defined('AIB_BOT_RETENTION_DAYS') ? intval(AIB_BOT_RETENTION_DAYS) : 365;
		$cutoff = date('Y-m-d', strtotime("-$retentionDays days"));
		$this->db->query("DELETE FROM ai_bot_hits WHERE hit_date < '$cutoff'");
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

		// is_referral_source, not just is_active - every field in this payload
		// is untrusted, so the server enforces the same "is this hostname
		// actually a click source" rule the snippet applies client-side,
		// rather than trusting the caller not to pass a bot-only vendor
		// domain (e.g. google.com) directly.
		$platformInfo = $this->dbHelper->getRow('ai_platforms', "hostname='" . addslashes($platform) . "' and is_active=1 and is_referral_source=1");
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

		// checked before the aggregate-on-write insert below, so "first
		// hit" reflects state prior to this request
		$this->__alertOnFirstPlatformHit($websiteId, $platformInfo['platform'], 'ai_referrals', $platformInfo['display_name']);

		$sql = "INSERT INTO ai_referrals (website_id, hit_date, platform, url_path, url_hash, hits, created_at, updated_at)
				VALUES ($websiteId, '$hitDate', '$platformCode', '$urlPathSql', UNHEX('$urlHashHex'), 1, NOW(), NOW())
				ON DUPLICATE KEY UPDATE hits = hits + 1, updated_at = NOW()";
		$this->db->query($sql);

		$this->db->query("UPDATE ai_visibility_sites SET last_seen_at=NOW() WHERE id=" . intval($siteInfo['id']));

		exit;
	}

	/**
	 * Public bot-hit ingest - called only from aibot-collect.php. This is a
	 * server-to-server POST from the customer's own hosting server (sent by
	 * the collector script), never a browser, so there is no Origin/Referer
	 * to validate and no CORS handling applies. The 'verified' flag is
	 * trusted as reported by the collector - FCrDNS runs there, at the only
	 * point where the real crawler IP is known with certainty (see
	 * generateBotCollectorScript()). Always responds fast with no body.
	 */
	function ingestBotHit() {
		header('Content-Type: text/plain');
		http_response_code(204);

		$maxBytes = 4096;
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
		$userAgent = isset($payload['ua']) ? (string)$payload['ua'] : '';
		$verified = !empty($payload['verified']) ? 1 : 0;
		$urlPath = isset($payload['u']) ? (string)$payload['u'] : '';

		if ($token === '' || strlen($token) > 64 || $userAgent === '') {
			exit;
		}

		$siteInfo = $this->dbHelper->getRow('ai_visibility_sites', "token='" . addslashes($token) . "'");
		if (empty($siteInfo)) {
			exit;
		}

		$tokenCap = defined('AIV_RATE_LIMIT_PER_TOKEN') ? intval(AIV_RATE_LIMIT_PER_TOKEN) : 120;
		if (!$this->__checkRateLimit('bot-token:' . $token, $tokenCap)) {
			exit;
		}

		// classify by matching the reported UA against known crawler
		// patterns - unmatched UAs (the prefilter is deliberately broad,
		// so plenty won't match anything here) are silently dropped
		$platformList = $this->db->select("SELECT platform, display_name, bot_ua_pattern FROM ai_platforms WHERE is_active=1 AND bot_ua_pattern IS NOT NULL AND bot_ua_pattern != ''");
		$matchedPlatform = null;
		$matchedDisplayName = null;
		foreach ($platformList as $platformRow) {
			if (stripos($userAgent, $platformRow['bot_ua_pattern']) !== false) {
				$matchedPlatform = $platformRow['platform'];
				$matchedDisplayName = $platformRow['display_name'];
				break;
			}
		}
		if (empty($matchedPlatform)) {
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
		$platformSql = addslashes($matchedPlatform);
		$urlPathSql = addslashes($urlPath);

		// checked before the aggregate-on-write insert below, so "first
		// hit" reflects state prior to this request
		$this->__alertOnFirstPlatformHit($websiteId, $matchedPlatform, 'ai_bot_hits', $matchedDisplayName);

		$sql = "INSERT INTO ai_bot_hits (website_id, hit_date, platform, verified, url_path, url_hash, hits, created_at, updated_at)
				VALUES ($websiteId, '$hitDate', '$platformSql', $verified, '$urlPathSql', UNHEX('$urlHashHex'), 1, NOW(), NOW())
				ON DUPLICATE KEY UPDATE hits = hits + 1, updated_at = NOW()";
		$this->db->query($sql);

		$this->db->query("UPDATE ai_visibility_sites SET bot_last_seen_at=NOW() WHERE id=" . intval($siteInfo['id']));

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

	/**
	 * Alerts the website owner the first time a website is ever seen
	 * receiving a hit from a given platform, in either ai_referrals
	 * (click referrals) or ai_bot_hits (crawler visits). Must be called
	 * BEFORE the aggregate-on-write INSERT ... ON DUPLICATE KEY UPDATE for
	 * that hit, since the existence check below is what distinguishes
	 * "first ever" from "one more of many" - once the row exists this
	 * always no-ops. createAlert() itself dedupes same subject+message
	 * per user per day, so this is safe to call on every ingest.
	 */
	function __alertOnFirstPlatformHit($websiteId, $platform, $table, $displayName) {
		$exists = $this->dbHelper->getRow($table, "website_id=" . intval($websiteId) . " and platform='" . addslashes($platform) . "'");
		if (!empty($exists)) {
			return;
		}

		$websiteInfo = $this->dbHelper->getRow('websites', "id=" . intval($websiteId));
		if (empty($websiteInfo['user_id'])) {
			return;
		}

		include_once(SP_CTRLPATH . "/alerts.ctrl.php");
		$alertCtrl = new AlertController();
		$alertCtrl->createAlert([
			'alert_subject' => 'New AI Visibility Platform Detected',
			'alert_message' => ($websiteInfo['url'] ?? 'Your website') . " just received its first tracked " . ($table == 'ai_bot_hits' ? 'crawl' : 'referral') . " from " . $displayName . ".",
			'alert_category' => 'reports',
			'alert_url' => SP_WEBPATH . "/aivisibility.php?website_id=" . intval($websiteId) . "&sec=" . ($table == 'ai_bot_hits' ? 'botreport' : 'report'),
		], $websiteInfo['user_id']);
	}

}
?>
