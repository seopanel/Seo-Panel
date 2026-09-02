<?php
/**
 * AI Insights v2 tests: the four new data-driven recommendation rules added
 * to RecommendationsController (AI Overview citation gap, AI bot
 * blocked/silent, rank drop, Site Auditor quick wins), plus the existing
 * webmaster_tools rule for a regression check. Runs against the real dev DB
 * with a throwaway website_id - no live crawling or external API calls.
 *
 * Usage: php tests/recommendations_test.php
 */

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/recommendations.ctrl.php");

$results = [];

function green($text)  { return "\033[32m$text\033[0m"; }
function red($text)    { return "\033[31m$text\033[0m"; }
function bold($text)   { return "\033[1m$text\033[0m"; }

function assertTrue($condition, $label) {
    global $results;
    $pass = (bool)$condition;
    $results[] = $pass;
    echo ($pass ? green("PASS") : red("FAIL")) . " - $label\n";
    return $pass;
}

function assertEquals($expected, $actual, $label) {
    $pass = ($expected === $actual);
    if (!$pass) {
        $label .= " (expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ")";
    }
    return assertTrue($pass, $label);
}

$ctrler     = new RecommendationsController();
$websiteId  = 999997; // throwaway, distinct from job_queue_test.php (999999) and aibot_test.php (999998)
$userId     = 1;

function cleanupFixtures($ctrler, $websiteId) {
    $ctrler->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId");
    $ctrler->db->query("DELETE FROM searchresults WHERE keyword_id IN (SELECT id FROM keywords WHERE website_id=$websiteId)");
    $ctrler->db->query("DELETE FROM keywords WHERE website_id=$websiteId");
    $ctrler->db->query("DELETE FROM auditorreports WHERE project_id IN (SELECT id FROM auditorprojects WHERE website_id=$websiteId)");
    $ctrler->db->query("DELETE FROM auditorprojects WHERE website_id=$websiteId");
    $ctrler->db->query("DELETE FROM ai_visibility_sites WHERE website_id=$websiteId");
}

// clean slate
cleanupFixtures($ctrler, $websiteId);

$seRow = $ctrler->db->select("SELECT id FROM searchengines LIMIT 1", true);
if (empty($seRow['id'])) {
    echo red("No searchengines row found in this DB - cannot run rank-based fixtures.") . "\n";
    exit(1);
}
$seId = intval($seRow['id']);

$today     = date('Y-m-d');
$monthAgo  = date('Y-m-d', strtotime('-31 days'));

// --- Fixture: AI Overview citation gap keyword (ranks #5, AIO present, not cited) ---
$ctrler->dbHelper->insertRow('keywords', [
    'name' => 'aio gap keyword',
    'website_id|int' => $websiteId,
    'status|int'     => 1,
]);
$aioKeywordId = $ctrler->db->select("SELECT id FROM keywords WHERE website_id=$websiteId AND name='aio gap keyword'", true)['id'];

$ctrler->db->query("INSERT INTO searchresults
    (keyword_id, searchengine_id, `rank`, result_date, aio_present, aio_cited, aio_reference_count, aio_checked_at, aio_data_date)
    VALUES ($aioKeywordId, $seId, 5, '$today', 1, 0, 3, NOW(), '$today')");

// --- Fixture: rank-drop keyword (was #4 a month ago, now #22) ---
$ctrler->dbHelper->insertRow('keywords', [
    'name' => 'rank drop keyword',
    'website_id|int' => $websiteId,
    'status|int'     => 1,
]);
$dropKeywordId = $ctrler->db->select("SELECT id FROM keywords WHERE website_id=$websiteId AND name='rank drop keyword'", true)['id'];

$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($dropKeywordId, $seId, 4, '$monthAgo')");
$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($dropKeywordId, $seId, 22, '$today')");

// --- Fixture: stable keyword (rank 3 both times) - must NOT be flagged ---
$ctrler->dbHelper->insertRow('keywords', [
    'name' => 'stable keyword',
    'website_id|int' => $websiteId,
    'status|int'     => 1,
]);
$stableKeywordId = $ctrler->db->select("SELECT id FROM keywords WHERE website_id=$websiteId AND name='stable keyword'", true)['id'];

$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($stableKeywordId, $seId, 3, '$monthAgo')");
$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($stableKeywordId, $seId, 3, '$today')");

// --- Fixture: Site Auditor project + pages with issues ---
$ctrler->dbHelper->insertRow('auditorprojects', [
    'website_id|int'     => $websiteId,
    'exclude_links'      => '',
]);
$projectId = $ctrler->db->select("SELECT id FROM auditorprojects WHERE website_id=$websiteId", true)['id'];

$ctrler->db->query("INSERT INTO auditorreports (project_id, page_url, ai_robot_allowed, brocken, https_secure, has_og_tags)
    VALUES ($projectId, '/page-a', 0, 1, 0, 0)");
$ctrler->db->query("INSERT INTO auditorreports (project_id, page_url, ai_robot_allowed, brocken, https_secure, has_og_tags)
    VALUES ($projectId, '/page-b', 1, 0, 1, 1)");

// --- Fixture: AI Visibility site installed, bot silent for 45 days ---
$staleSeen = date('Y-m-d H:i:s', strtotime('-45 days'));
$ctrler->dbHelper->insertRow('ai_visibility_sites', [
    'website_id|int'  => $websiteId,
    'token'           => bin2hex(random_bytes(24)),
    'domain'          => 'example.com',
    'created_at'      => 'NOW()',
    'bot_last_seen_at' => $staleSeen,
]);

// refreshRecommendations() reads isLoggedIn() internally (no $userId param) and
// also re-renders the dashboard view, both of which need a real HTTP session -
// neither applies to this CLI test. Call the private generator methods directly
// via reflection instead, replicating just the delete-then-regenerate sequence
// refreshRecommendations() itself performs.
function runAllGenerators($ctrler, $websiteId, $userId) {
    $ctrler->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");
    $methods = [
        '__generateWebmasterRecommendations',
        '__generateAIOverviewCitationRecommendations',
        '__generateAIBotBlockedRecommendation',
        '__generateAIBotSilentRecommendation',
        '__generateRankDropRecommendations',
        '__generateSiteAuditorRecommendations',
    ];
    $reflection = new ReflectionClass($ctrler);
    foreach ($methods as $methodName) {
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $method->invoke($ctrler, $websiteId, $userId);
    }
}

echo bold("=== Run all recommendation generators and inspect stored rows ===") . "\n";

runAllGenerators($ctrler, $websiteId, $userId);

$rows = $ctrler->db->select("SELECT * FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");
$byCategory = [];
foreach ($rows as $r) { $byCategory[$r['category']][] = $r; }

assertTrue(!empty($byCategory['ai_overview']), 'ai_overview category produced at least one row');
if (!empty($byCategory['ai_overview'])) {
    assertEquals('warning', $byCategory['ai_overview'][0]['type'], 'ai_overview row type is warning');
    assertTrue(strpos($byCategory['ai_overview'][0]['title'], 'aio gap keyword') !== false, 'ai_overview title mentions the gap keyword');
}

assertTrue(!empty($byCategory['rank_tracker']), 'rank_tracker category produced at least one row');
if (!empty($byCategory['rank_tracker'])) {
    assertEquals(1, count($byCategory['rank_tracker']), 'rank_tracker produced exactly one row (stable keyword correctly excluded)');
    assertTrue(strpos($byCategory['rank_tracker'][0]['title'], 'rank drop keyword') !== false, 'rank_tracker title mentions the dropped keyword, not the stable one');
    assertEquals('error', $byCategory['rank_tracker'][0]['type'], 'rank_tracker row type is error');
}

assertTrue(!empty($byCategory['ai_visibility']), 'ai_visibility category produced at least one row');
if (!empty($byCategory['ai_visibility'])) {
    $types = array_column($byCategory['ai_visibility'], 'type');
    assertTrue(in_array('error', $types), 'ai_visibility includes the blocked-pages error row');
    assertTrue(in_array('todo', $types), 'ai_visibility includes the silent-crawler todo row');
    assertEquals(2, count($byCategory['ai_visibility']), 'ai_visibility produced exactly the 2 expected rows');
}

assertTrue(!empty($byCategory['site_auditor']), 'site_auditor category produced rows');
if (!empty($byCategory['site_auditor'])) {
    assertEquals(3, count($byCategory['site_auditor']), 'site_auditor produced all 3 expected issue rows (broken link, non-HTTPS, missing OG tags)');
    $sevByNoun = [];
    foreach ($byCategory['site_auditor'] as $r) { $sevByNoun[] = $r['type']; }
    assertTrue(in_array('warning', $sevByNoun) && in_array('todo', $sevByNoun), 'site_auditor rows carry the expected mixed severities');
}

echo "\n" . bold("=== Idempotency: refreshing again does not duplicate rows ===") . "\n";
runAllGenerators($ctrler, $websiteId, $userId);
$rowsAfter = $ctrler->db->select("SELECT COUNT(*) AS cnt FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId", true);
assertEquals(count($rows), intval($rowsAfter['cnt']), 'second refresh produces the same row count (delete-then-regenerate, no duplication)');

echo "\n" . bold("=== No-data website: every new rule silently no-ops ===") . "\n";
$emptyWebsiteId = 999996;
runAllGenerators($ctrler, $emptyWebsiteId, $userId);
$emptyRows = $ctrler->db->select("SELECT COUNT(*) AS cnt FROM sp_recommendations WHERE website_id=$emptyWebsiteId", true);
assertEquals(0, intval($emptyRows['cnt']), 'a website with no keywords/auditor/AI-visibility data produces zero recommendation rows');

echo "\n" . bold("=== refreshRecommendationsForWebsite(): new-item diffing (anti-spam) ===") . "\n";

$ctrler->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");

$firstRun = $ctrler->refreshRecommendationsForWebsite($websiteId, $userId);
assertTrue(count($firstRun) > 0, 'first-ever generation returns the current issues as new');

$secondRun = $ctrler->refreshRecommendationsForWebsite($websiteId, $userId);
assertEquals(0, count($secondRun), 'an immediate second refresh with unchanged data returns zero new items (anti-spam)');

// Change a persisting issue's COUNT (site_auditor broken links: 1 -> 2 pages)
// and confirm it is still not treated as new - same stable "rule" identity.
$ctrler->db->query("INSERT INTO auditorreports (project_id, page_url, ai_robot_allowed, brocken, https_secure, has_og_tags)
    VALUES ($projectId, '/page-c', 1, 1, 1, 1)");
$thirdRun = $ctrler->refreshRecommendationsForWebsite($websiteId, $userId);
assertEquals(0, count($thirdRun), 'a persisting issue whose count changed (1 -> 2 broken links) is still not flagged as new');

// Introduce a genuinely new issue (a second keyword hitting the rank-drop
// rule) and confirm only that one new item comes back, not a re-flag of
// everything else that's already been seen.
$ctrler->dbHelper->insertRow('keywords', [
    'name' => 'second drop keyword',
    'website_id|int' => $websiteId,
    'status|int'     => 1,
]);
$secondDropKeywordId = $ctrler->db->select("SELECT id FROM keywords WHERE website_id=$websiteId AND name='second drop keyword'", true)['id'];
$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($secondDropKeywordId, $seId, 2, '$monthAgo')");
$ctrler->db->query("INSERT INTO searchresults (keyword_id, searchengine_id, `rank`, result_date) VALUES ($secondDropKeywordId, $seId, 30, '$today')");

$fourthRun = $ctrler->refreshRecommendationsForWebsite($websiteId, $userId);
assertEquals(1, count($fourthRun), 'exactly one genuinely new item (the newly dropped keyword) is returned');
if (!empty($fourthRun)) {
    assertTrue(strpos($fourthRun[0]['title'], 'second drop keyword') !== false, 'the new item is the newly dropped keyword, not a re-flagged pre-existing one');
}

echo "\n" . bold("=== ReportController::getUserReportSettings(): new column seeding ===") . "\n";

include_once(SP_CTRLPATH . "/report.ctrl.php");
$reportCtrler = new ReportController();

// user id 2 has no reports_settings row yet in this dev DB (verified before
// writing this test) - use it to exercise the lazy-create path, then clean
// up so this test doesn't leave permanent state behind.
$lazyUserId = 2;
$ctrler->db->query("DELETE FROM reports_settings WHERE user_id=$lazyUserId");
$repSetInfo = $reportCtrler->getUserReportSettings($lazyUserId);
assertEquals(intval(SP_AI_INSIGHTS_EMAIL_NOTIFICATION), intval($repSetInfo['ai_insights_email_notification']), 'a freshly lazy-created reports_settings row seeds ai_insights_email_notification from the system setting');
$persisted = $ctrler->db->select("SELECT ai_insights_email_notification FROM reports_settings WHERE user_id=$lazyUserId", true);
assertEquals('1', $persisted['ai_insights_email_notification'], 'the seeded value was actually persisted to the row, not just held in memory');
$ctrler->db->query("DELETE FROM reports_settings WHERE user_id=$lazyUserId");

echo "\n" . bold("=== CronController::refreshAllAIInsights(): once-per-day gate + iteration ===") . "\n";

// SAFETY: refreshAllAIInsights() reads the real `websites` table directly
// (`WHERE status=1`) and, as of the email digest feature, can send a REAL
// email to a website's real owner if that user has new insights and their
// ai_insights_email_notification preference is on. This dev DB has SMTP
// actually configured (SP_SMTP_MAIL=1) and real user emails - calling this
// method unguarded would risk emailing real addresses. Two independent
// safeguards, both required:
//   1. The fixture website below is owned by a fake, non-existent user id
//      (never a real row in `users`), so __getUserInfo() returns empty and
//      the email-send is skipped via the empty-email guard for OUR fixture.
//   2. Every REAL user's reports_settings.ai_insights_email_notification is
//      temporarily forced to 0 (snapshotted first, restored after) so a
//      real website that happens to have new insights today cannot trigger
//      a real send either. This does NOT touch the SP_AI_INSIGHTS_EMAIL_
//      NOTIFICATION system constant (already loaded in this PHP process
//      and un-changeable mid-run) - only the per-user DB column, which is
//      read fresh from the DB on every call.
include_once(SP_CTRLPATH . "/cron.ctrl.php");
include_once(SP_CTRLPATH . "/information.ctrl.php");

$cronCtrler = new CronController();
$infoCtrler = new InformationController();
$fakeUserId = 888888; // deliberately not a row in `users` - see safeguard 1 above

$repSettingsSnapshot = $ctrler->db->select("SELECT user_id, ai_insights_email_notification FROM reports_settings");
$ctrler->db->query("UPDATE reports_settings SET ai_insights_email_notification=0");

// Snapshot + clear the real global once-per-day flag so this test doesn't
// depend on (or corrupt) whatever state today's real cron run left behind.
$flagSnapshot = $ctrler->db->select("SELECT * FROM information_list WHERE info_type='ai_insights_refresh'");
$ctrler->db->query("DELETE FROM information_list WHERE info_type='ai_insights_refresh'");

// Re-use the same fixture keywords/auditor/AI-visibility rows from above,
// but this method reads the real `websites` table directly, so give this
// throwaway website_id a real (fixture) row there too - owned by the fake
// user id, per safeguard 1.
$ctrler->db->query("DELETE FROM websites WHERE id=$websiteId");
$ctrler->dbHelper->insertRow('websites', [
    'id|int'      => $websiteId,
    'name'        => 'AI Insights cron test fixture',
    'url'         => 'https://example.com',
    'user_id|int' => $fakeUserId,
    'status|int'  => 1,
]);
$ctrler->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId");

$cronCtrler->refreshAllAIInsights();

$rowsAfterCron = $ctrler->db->select("SELECT COUNT(*) AS cnt FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$fakeUserId", true);
assertTrue(intval($rowsAfterCron['cnt']) > 0, 'refreshAllAIInsights() generated recommendations for the fixture website');

$flagRow = $infoCtrler->__getTodayInformation('ai_insights_refresh');
assertTrue(!empty($flagRow), 'refreshAllAIInsights() set the once-per-day flag for today');

// Plant a sentinel row and confirm a second call today is a no-op (the gate
// prevents the delete-then-regenerate from running again).
$ctrler->db->query("INSERT INTO sp_recommendations (website_id, user_id, type, category, title, description, meta, refreshed_at)
    VALUES ($websiteId, $fakeUserId, 'todo', 'sentinel_test', 'sentinel', 'sentinel', NULL, NOW())");
$cronCtrler->refreshAllAIInsights();
$sentinelStillThere = $ctrler->db->select("SELECT * FROM sp_recommendations WHERE website_id=$websiteId AND category='sentinel_test'", true);
assertTrue(!empty($sentinelStillThere), 'a second call on the same day is a no-op (gate prevents re-running)');

// cleanup
$ctrler->db->query("DELETE FROM websites WHERE id=$websiteId");
$ctrler->db->query("DELETE FROM reports_settings WHERE user_id=$fakeUserId"); // lazy-created for the fake user by getUserReportSettings()
$ctrler->db->query("DELETE FROM information_list WHERE info_type='ai_insights_refresh'");
foreach ($flagSnapshot as $row) {
    unset($row['id']);
    $cols = implode(',', array_keys($row));
    $vals = implode(',', array_map(function($v) { return "'" . addslashes($v) . "'"; }, $row));
    $ctrler->db->query("INSERT INTO information_list ($cols) VALUES ($vals)");
}
foreach ($repSettingsSnapshot as $row) {
    $ctrler->db->query("UPDATE reports_settings SET ai_insights_email_notification=" . intval($row['ai_insights_email_notification']) . " WHERE user_id=" . intval($row['user_id']));
}

// cleanup
cleanupFixtures($ctrler, $websiteId);
$ctrler->db->query("DELETE FROM sp_recommendations WHERE website_id=$emptyWebsiteId");

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
