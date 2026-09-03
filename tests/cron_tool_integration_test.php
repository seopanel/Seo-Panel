<?php
/**
 * TestCronController harness: exercises the REAL *CronQueued() method
 * bodies in controllers/cron.ctrl.php end-to-end (enqueue -> drain ->
 * complete against the real job_queue table), not just the generic queue
 * primitives already covered by tests/job_queue_test.php.
 *
 * SAFETY: this dev DB has real, working Moz/DataForSEO/SP API credentials.
 * SP_USE_SAMPLE_API_DATA MUST be forced on BEFORE includes/sp-load.php ever
 * runs, because settings are define()'d once from the DB at bootstrap - a
 * PHP constant can't be redefined mid-process, so flipping the DB row after
 * bootstrap has no effect on the constant every sample-data guard clause
 * actually reads. Hence the raw pre-bootstrap mysqli block below, and the
 * register_shutdown_function restoring it afterward regardless of how the
 * script exits. See feedback_live_api_test_caution memory before changing
 * this pattern.
 *
 * Every *CronQueued() method reads $this->websiteInfo internally, not the
 * $websiteId argument you pass it for enqueueChunks()/job_queue purposes -
 * so whenever more than one fixture website is in play on the same
 * TestCronController instance, call useFixtureWebsite($website) immediately
 * before EACH *CronQueued() call to make the active context unambiguous.
 *
 * Usage: php tests/cron_tool_integration_test.php
 */

require_once(dirname(__FILE__) . "/../config/sp-config.php");

$__rawDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($__rawDb->connect_errno) {
    fwrite(STDERR, "Could not open a raw DB connection to force SP_USE_SAMPLE_API_DATA before bootstrap: {$__rawDb->connect_error}\n");
    exit(1);
}
$__sampleFlagRow = $__rawDb->query("SELECT set_val FROM settings WHERE set_name='SP_USE_SAMPLE_API_DATA'")->fetch_assoc();
$__originalSampleFlag = $__sampleFlagRow['set_val'] ?? '0';
$__rawDb->query("UPDATE settings SET set_val='1' WHERE set_name='SP_USE_SAMPLE_API_DATA'");
$__rawDb->close();

register_shutdown_function(function() use ($__originalSampleFlag) {
    $restoreDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$restoreDb->connect_errno) {
        $restoreDb->query("UPDATE settings SET set_val='" . $restoreDb->real_escape_string($__originalSampleFlag) . "' WHERE set_name='SP_USE_SAMPLE_API_DATA'");
        $restoreDb->close();
    }
});

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/settings.ctrl.php");
include_once(dirname(__FILE__) . "/TestCronController.php");

if (!defined('SP_USE_SAMPLE_API_DATA') || !SP_USE_SAMPLE_API_DATA) {
    fwrite(STDERR, "SP_USE_SAMPLE_API_DATA did not come up enabled after bootstrap - refusing to run any *CronQueued() method for safety.\n");
    exit(1);
}

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

function completedChunkCount($db, $urlSection, $websiteId) {
    $row = $db->select("SELECT COUNT(*) AS cnt FROM job_queue WHERE url_section='" . addslashes($urlSection) . "' AND website_id=$websiteId AND status='completed'", true);
    return intval($row['cnt'] ?? 0);
}

$cron = new TestCronController();

echo bold("=== Backlink Checker (Moz sample-data path) ===") . "\n";
$website = $cron->setupFixtureWebsite();
$websiteId = $website['id'];

$cron->useFixtureWebsite($website);
$cron->backlinkCheckerCronQueued($websiteId);
assertTrue(completedChunkCount($cron->db, 'backlink-checker', $websiteId) > 0, 'backlinkCheckerCronQueued() completes its chunk');
$blRow = $cron->db->select("SELECT * FROM backlinkresults WHERE website_id=$websiteId", true);
assertTrue(!empty($blRow), 'backlinkCheckerCronQueued() wrote a backlinkresults row from sample Moz data');
$rankRowViaBacklink = $cron->db->select("SELECT * FROM rankresults WHERE website_id=$websiteId", true);
assertTrue(!empty($rankRowViaBacklink), 'backlinkCheckerCronQueued() also wrote a rankresults row (its documented "also save rank data" side effect)');

echo "\n" . bold("=== Rank Checker (Moz sample-data path) ===") . "\n";
// Own fixture website: SP_MULTIPLE_CRON_EXEC (config/sp-config-extra.php,
// hardcoded =1) makes rankCheckerCronQueued()'s isReportsExists() guard
// correctly skip enqueueing entirely once rankresults already has a row for
// today for a given website - which the backlink-checker run above just
// created as its own side effect. That's correct, intended, once-per-day-
// per-tool behavior, not a bug - a separate website avoids that real
// cross-tool interaction muddying this section's assertions.
$rankWebsite = $cron->setupFixtureWebsite();
$rankWebsiteId = $rankWebsite['id'];

$cron->useFixtureWebsite($rankWebsite);
$cron->rankCheckerCronQueued($rankWebsiteId);
assertTrue(completedChunkCount($cron->db, 'rank-checker', $rankWebsiteId) > 0, 'rankCheckerCronQueued() completes its chunk');
$rankRow = $cron->db->select("SELECT * FROM rankresults WHERE website_id=$rankWebsiteId", true);
assertTrue(!empty($rankRow), 'rankCheckerCronQueued() wrote a rankresults row from sample Moz data');

echo "\n" . bold("=== Saturation Checker (already sample-gated) ===") . "\n";
$satWebsite = $cron->setupFixtureWebsite();
$satWebsiteId = $satWebsite['id'];

$cron->useFixtureWebsite($satWebsite);
$cron->saturationCheckerCronQueued($satWebsiteId);
assertTrue(completedChunkCount($cron->db, 'saturation-checker', $satWebsiteId) > 0, 'saturationCheckerCronQueued() completes its chunk');
$satRow = $cron->db->select("SELECT * FROM saturationresults WHERE website_id=$satWebsiteId", true);
assertTrue(!empty($satRow), 'saturationCheckerCronQueued() wrote a saturationresults row from sample data');

echo "\n" . bold("=== Search Volume Checker (DFS or SP API tier, per this DB's live isDFSEnabled state) ===") . "\n";
$svWebsite = $cron->setupFixtureWebsite();
$svWebsiteId = $svWebsite['id'];
$svKeyword = $cron->addFixtureKeyword($svWebsiteId, 'sample search volume keyword');

$cron->useFixtureWebsite($svWebsite);
$cron->searchVolumeCheckerCronQueued($svWebsiteId);
assertTrue(completedChunkCount($cron->db, 'search-volume', $svWebsiteId) > 0, 'searchVolumeCheckerCronQueued() completes its chunk');
$svRow = $cron->db->select("SELECT * FROM keyword_search_volume WHERE keyword_id={$svKeyword['id']}", true);
assertTrue(!empty($svRow), 'searchVolumeCheckerCronQueued() wrote a keyword_search_volume row');
if (!empty($svRow)) {
    assertEquals('success', $svRow['last_crawl_status'], 'search volume row shows a successful crawl status from sample data');
}

echo "\n" . bold("=== Keyword Position Checker (DFS/SPAPI/crawl tier, per this DB's live isDFSEnabled state) ===") . "\n";
$posWebsite = $cron->setupFixtureWebsite();
$posWebsiteId = $posWebsite['id'];
// withSearchEngine=true is required - without a real searchengines value,
// the app's own "not assigned to required search engines" guard clause
// skips the keyword before ever reaching the network-calling code, which
// would make this test pass without proving anything about the new guards.
$posKeyword = $cron->addFixtureKeyword($posWebsiteId, 'sample position keyword', true);

$cron->useFixtureWebsite($posWebsite);
$cron->keywordPositionCheckerCronQueued($posWebsiteId);
$serpSource = SettingsController::isDFSEnabled('serp') ? 'dataforseo' : (SPAPIController::isConfigured() ? 'spapi' : 'crawl');
echo "  (this DB's live settings route keyword position checking through '$serpSource')\n";

if ($serpSource === 'dataforseo') {
    // DFS is an async task-post model - the *CronQueued() body's job is
    // posting the task (sample-gated, no real HTTP), not fetching the
    // result (a separate cron-tail step, out of scope here).
    assertTrue(completedChunkCount($cron->db, 'keyword-position-checker', $posWebsiteId) > 0, 'keywordPositionCheckerCronQueued() completes its chunk (DFS tier: task posted)');
    $taskRow = $cron->db->select("SELECT * FROM dfs_tasks WHERE category='serp' AND ref_id={$posKeyword['id']}", true);
    assertTrue(!empty($taskRow) && strpos($taskRow['task_id'], 'sample-') === 0, 'a sample (non-real) DFS SERP task_id was recorded, not a real one');
} elseif ($serpSource === 'spapi') {
    assertTrue(completedChunkCount($cron->db, 'keyword-position-checker', $posWebsiteId) > 0, 'keywordPositionCheckerCronQueued() completes its chunk (SP API tier)');
    $rankRow = $cron->db->select("SELECT * FROM searchresults WHERE keyword_id={$posKeyword['id']}", true);
    assertTrue(!empty($rankRow), 'a searchresults row was written from the SP API tier\'s sample data (rank 0 / no-match fallback)');
} else {
    assertTrue(completedChunkCount($cron->db, 'keyword-position-checker', $posWebsiteId) > 0, "keywordPositionCheckerCronQueued() completes its chunk ($serpSource tier)");
    echo "  (the legacy 'crawl' tier makes real, unbilled HTTP requests to Google/Bing - not sample-gated, explicitly out of scope per the plan)\n";
}

echo "\n" . bold("=== Direct guard checks for tiers this DB's live config didn't route through above ===") . "\n";
// This DB's live isDFSEnabled()/isSpApiEnabled() state took the Moz and SP
// API tiers above, not DataForSEO - so the two DFS-specific guards added
// this session (backlink summary, SERP task-post) were never exercised by
// the *CronQueued() orchestration itself. Call them directly instead, to
// confirm they short-circuit before any real HTTP under sample data
// regardless of which tier this particular DB happens to route through.
include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
$dfsCtrler = new DataForSEOController();

$directBacklinkSummary = $dfsCtrler->__getBacklinkSummary('https://example.com');
assertTrue(!empty($directBacklinkSummary), '__getBacklinkSummary() returns sample data directly (no real HTTP) when called under SP_USE_SAMPLE_API_DATA');
assertTrue(isset($directBacklinkSummary['backlinks'], $directBacklinkSummary['referring_domains'], $directBacklinkSummary['broken_backlinks']), '__getBacklinkSummary() sample result has the expected shape');

$directTaskPost = $dfsCtrler->__postSERPTaskToAPI('google', array('keyword' => 'sample', 'location_name' => 'United States', 'language_name' => 'English'));
assertTrue(!empty($directTaskPost['status']) && strpos($directTaskPost['task_id'], 'sample-') === 0, '__postSERPTaskToAPI() returns a sample (non-real) task_id directly (no real HTTP) when called under SP_USE_SAMPLE_API_DATA');

echo "\n" . bold("=== Empty-fixture no-op paths (nothing linked = nothing to enqueue = no real call is even reachable) ===") . "\n";

$emptyWebsite = $cron->setupFixtureWebsite();
$emptyWebsiteId = $emptyWebsite['id'];
$cron->useFixtureWebsite($emptyWebsite);

$cron->socialMediaCheckerCronQueued($emptyWebsiteId);
assertEquals(0, completedChunkCount($cron->db, 'sm-checker', $emptyWebsiteId), 'socialMediaCheckerCronQueued() with no linked accounts enqueues and completes nothing');

$cron->reviewCheckerCronQueued($emptyWebsiteId);
assertEquals(0, completedChunkCount($cron->db, 'review-manager', $emptyWebsiteId), 'reviewCheckerCronQueued() with no linked review links enqueues and completes nothing');

$cron->analyticsCronQueued($emptyWebsiteId);
$analyticsFailed = $cron->db->select("SELECT COUNT(*) AS cnt FROM job_queue WHERE url_section='web-analytics' AND website_id=$emptyWebsiteId AND status='failed'", true);
assertEquals(0, intval($analyticsFailed['cnt'] ?? 0), 'analyticsCronQueued() with no linked Google account fails cleanly, not with an error (0 failed chunks)');

$cron->webmasterToolsCronQueued($emptyWebsiteId);
$wmFailed = $cron->db->select("SELECT COUNT(*) AS cnt FROM job_queue WHERE url_section='webmaster-tools' AND website_id=$emptyWebsiteId AND status='failed'", true);
assertEquals(0, intval($wmFailed['cnt'] ?? 0), 'webmasterToolsCronQueued() with no linked Google account fails cleanly, not with an error (0 failed chunks)');

// cleanup
$cron->cleanupFixtures();

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
