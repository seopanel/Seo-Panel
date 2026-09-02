<?php
/**
 * Backlink Checker modernization tests: DataForSEO backlink summary parsing
 * (fixture-based, no live API calls - this dev DB has a real, billed
 * DataForSEO subscription), the isDFSEnabled('backlink') gate, and
 * BacklinkController::saveRankResults()'s NULL-vs-int broken_backlinks
 * persistence. Uses a throwaway website_id against the real dev DB for the
 * persistence assertions only.
 *
 * Usage: php tests/backlink_dfs_test.php
 */

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
include_once(SP_CTRLPATH . "/settings.ctrl.php");
include_once(SP_CTRLPATH . "/backlink.ctrl.php");

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

echo bold("=== DataForSEOController::parseBacklinkSummaryResponse(): fixtures ===") . "\n";

// (a) a well-formed response, shaped like DataForSEO's documented example
$fixtureA = [
    'status_code' => 20000,
    'tasks' => [
        [
            'status_code' => 20000,
            'result' => [
                [
                    'target' => 'explodingtopics.com',
                    'rank' => 371,
                    'backlinks' => 41245,
                    'backlinks_spam_score' => 12,
                    'referring_domains' => 12372,
                    'referring_main_domains' => 11800,
                    'broken_backlinks' => 340,
                ],
            ],
        ],
    ],
];
$parsedA = DataForSEOController::parseBacklinkSummaryResponse($fixtureA);
assertTrue($parsedA !== false, 'Fixture (a) well-formed response parses successfully');
assertEquals(41245, $parsedA['backlinks'], 'Fixture (a) backlinks extracted correctly');
assertEquals(12372, $parsedA['referring_domains'], 'Fixture (a) referring_domains extracted correctly');
assertEquals(340, $parsedA['broken_backlinks'], 'Fixture (a) broken_backlinks extracted correctly');

// (b) top-level API error (e.g. bad auth, malformed request)
$fixtureB = ['status_code' => 40101, 'status_message' => 'Auth error.', 'tasks' => []];
assertEquals(false, DataForSEOController::parseBacklinkSummaryResponse($fixtureB), 'Fixture (b) top-level error returns false');

// (c) task-level error (task submitted, but failed)
$fixtureC = [
    'status_code' => 20000,
    'tasks' => [
        ['status_code' => 40501, 'status_message' => 'Invalid target.', 'result' => null],
    ],
];
assertEquals(false, DataForSEOController::parseBacklinkSummaryResponse($fixtureC), 'Fixture (c) task-level error returns false');

// (d) empty result set (valid response, no data)
$fixtureD = [
    'status_code' => 20000,
    'tasks' => [
        ['status_code' => 20000, 'result' => []],
    ],
];
assertEquals(false, DataForSEOController::parseBacklinkSummaryResponse($fixtureD), 'Fixture (d) empty result array returns false');

// (e) missing individual fields default to 0, not a fatal error
$fixtureE = [
    'status_code' => 20000,
    'tasks' => [
        ['status_code' => 20000, 'result' => [['target' => 'newsite.com']]],
    ],
];
$parsedE = DataForSEOController::parseBacklinkSummaryResponse($fixtureE);
assertTrue($parsedE !== false, 'Fixture (e) result present but missing count fields still parses');
assertEquals(0, $parsedE['backlinks'], 'Fixture (e) missing backlinks defaults to 0');
assertEquals(0, $parsedE['referring_domains'], 'Fixture (e) missing referring_domains defaults to 0');
assertEquals(0, $parsedE['broken_backlinks'], 'Fixture (e) missing broken_backlinks defaults to 0');

echo "\n" . bold("=== SettingsController::isDFSEnabled('backlink'): gate is independent of 'backsatu' ===") . "\n";

// This dev DB has SP_ENABLE_DFS_BACK_SATU=1 (real credentials, real balance) -
// the new 'backlink' feature key must NOT piggyback on that flag, since doing
// so would silently start billed backlink calls on this exact DB the moment
// this code merges. Confirm they're independently gated.
assertTrue(defined('SP_ENABLE_DFS_BACKLINK'), 'SP_ENABLE_DFS_BACKLINK constant is defined');
assertEquals(0, intval(SP_ENABLE_DFS_BACKLINK), 'SP_ENABLE_DFS_BACKLINK defaults to 0 (off) in this DB');
assertEquals(false, SettingsController::isDFSEnabled('backlink'), "isDFSEnabled('backlink') is false while the flag is off, regardless of SP_ENABLE_DFS_BACK_SATU's value");

echo "\n" . bold("=== BacklinkController::saveRankResults(): broken_backlinks NULL-vs-int persistence ===") . "\n";

$ctrler = new BacklinkController();
$websiteId = 999995; // throwaway, distinct from other test files' IDs this session

$ctrler->db->query("DELETE FROM backlinkresults WHERE website_id=$websiteId");

// Moz-shaped call: no 'broken_backlinks' key at all -> must persist as NULL
$ctrler->saveRankResults([
    'id' => $websiteId,
    'external_pages_to_page' => 120,
    'external_pages_to_root_domain' => 45,
], true);
$mozRow = $ctrler->db->select("SELECT * FROM backlinkresults WHERE website_id=$websiteId", true);
assertEquals(null, $mozRow['broken_backlinks'], 'a Moz-shaped save (no broken_backlinks key) persists NULL, not 0');
assertEquals('120', $mozRow['external_pages_to_page'], 'external_pages_to_page persisted correctly on the Moz-shaped row');

// DFS-shaped call: 'broken_backlinks' key present (even if 0) -> must persist as an int, not NULL
$ctrler->saveRankResults([
    'id' => $websiteId,
    'external_pages_to_page' => 41245,
    'external_pages_to_root_domain' => 12372,
    'broken_backlinks' => 0,
], true);
$dfsRow = $ctrler->db->select("SELECT * FROM backlinkresults WHERE website_id=$websiteId", true);
assertEquals('0', $dfsRow['broken_backlinks'], 'a DFS-shaped save with broken_backlinks=0 persists 0, not NULL (key presence, not truthiness, decides this)');

// cleanup
$ctrler->db->query("DELETE FROM backlinkresults WHERE website_id=$websiteId");

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
