<?php
/**
 * AI Overview tracking - fixture-based parser tests + domain normalisation
 * + idempotency check, per the AI Overview Tracking spec (section 6).
 *
 * Run against fixtures, not the live API. Exercises AIOverviewController's
 * DataForSEO parser, SEO Panel API mapper, domain-citation matching, and
 * the delete-then-insert idempotency of saved references.
 *
 * Usage: php tests/aioverview_test.php
 */

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/aioverview.ctrl.php");

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

echo bold("=== AI Overview: DataForSEO parser fixtures ===") . "\n";

// (a) no AI Overview present
$fixtureA = [
    ['type' => 'organic', 'rank_group' => 1, 'url' => 'https://example.com/', 'title' => 'Example'],
    ['type' => 'organic', 'rank_group' => 2, 'url' => 'https://other.com/', 'title' => 'Other'],
];
$parsedA = AIOverviewController::parseDataForSEO($fixtureA, '2026-08-20');
assertEquals(false, $parsedA['present'], 'Fixture (a) no ai_overview item -> present=false');
assertEquals([], $parsedA['references'], 'Fixture (a) references empty');
assertEquals(true, $parsedA['supported'], 'Fixture (a) DFS always supported=true');

// (b) synchronous overview, tracked domain cited (top-level references)
$fixtureB = [
    ['type' => 'organic', 'rank_group' => 1, 'url' => 'https://other.com/', 'title' => 'Other'],
    [
        'type' => 'ai_overview',
        'asynchronous_ai_overview' => false,
        'references' => [
            ['domain' => 'tracked-site.com', 'url' => 'https://tracked-site.com/page', 'title' => 'Tracked page', 'source' => 'Tracked Site'],
            ['domain' => 'other.com', 'url' => 'https://other.com/', 'title' => 'Other'],
        ],
    ],
];
$parsedB = AIOverviewController::parseDataForSEO($fixtureB, '2026-08-20');
assertEquals(true, $parsedB['present'], 'Fixture (b) present=true');
assertEquals(false, $parsedB['async'], 'Fixture (b) async=false (synchronous)');
assertEquals(2, count($parsedB['references']), 'Fixture (b) collects 2 top-level references');
assertTrue(AIOverviewController::isDomainCited($parsedB['references'][0]['domain'], 'tracked-site.com'), 'Fixture (b) tracked domain matches by isDomainCited');

// (c) asynchronous overview, tracked domain NOT cited
$fixtureC = [
    [
        'type' => 'ai_overview',
        'asynchronous_ai_overview' => true,
        'references' => [
            ['domain' => 'competitor.com', 'url' => 'https://competitor.com/x', 'title' => 'Competitor'],
        ],
    ],
];
$parsedC = AIOverviewController::parseDataForSEO($fixtureC, '2026-08-20');
assertEquals(true, $parsedC['present'], 'Fixture (c) present=true');
assertEquals(true, $parsedC['async'], 'Fixture (c) async=true');
$citedC = false;
foreach ($parsedC['references'] as $ref) {
    if (AIOverviewController::isDomainCited($ref['domain'], 'tracked-site.com')) $citedC = true;
}
assertEquals(false, $citedC, 'Fixture (c) tracked domain not cited');

// (d) references nested inside ai_overview_element items, not top-level
$fixtureD = [
    [
        'type' => 'ai_overview',
        'asynchronous_ai_overview' => false,
        'items' => [
            [
                'type' => 'ai_overview_element',
                'text' => 'element one',
                'references' => [
                    ['domain' => 'nested-a.com', 'url' => 'https://nested-a.com/1'],
                ],
            ],
            [
                'type' => 'ai_overview_element',
                'text' => 'element two',
                'references' => [
                    ['domain' => 'nested-b.com', 'url' => 'https://nested-b.com/2'],
                    ['domain' => 'nested-a.com', 'url' => 'https://nested-a.com/1'], // duplicate URL, must be deduped
                ],
            ],
        ],
    ],
];
$parsedD = AIOverviewController::parseDataForSEO($fixtureD, '2026-08-20');
assertEquals(true, $parsedD['present'], 'Fixture (d) present=true');
assertEquals(2, count($parsedD['references']), 'Fixture (d) collects references nested in ai_overview_element, deduped by URL');

echo "\n" . bold("=== AI Overview: SEO Panel API mapper ===") . "\n";

// supported, present, with a reference
$spapiPresent = [
    'capabilities' => ['serp', 'search_volume', 'ai_overview'],
    'ai_overview' => [
        'present' => true,
        'asynchronous' => false,
        'reference_count' => 1,
        'references' => [
            ['position' => 1, 'domain' => 'tracked-site.com', 'url' => 'https://tracked-site.com/x', 'title' => 'T'],
        ],
        'collected_at' => '2026-08-18 10:00:00',
        'is_stale' => true,
    ],
];
$mappedPresent = AIOverviewController::mapSpApi($spapiPresent, '2026-08-20');
assertEquals(true, $mappedPresent['supported'], 'spAPI mapper: supported=true when capabilities include ai_overview');
assertEquals(true, $mappedPresent['present'], 'spAPI mapper: present=true');
assertEquals('2026-08-18', $mappedPresent['data_date'], 'spAPI mapper: data_date derived from collected_at, not the check date');

// not supported (archive hasn't shipped it for this account/version)
$spapiUnsupported = ['capabilities' => ['serp', 'search_volume']];
$mappedUnsupported = AIOverviewController::mapSpApi($spapiUnsupported, '2026-08-20');
assertEquals(false, $mappedUnsupported['supported'], 'spAPI mapper: supported=false when ai_overview absent from capabilities (state 3)');

// supported, but not yet crawled (pending) - must return null, not "absent"
$spapiPending = ['capabilities' => ['serp', 'ai_overview']];
$mappedPending = AIOverviewController::mapSpApi($spapiPending, '2026-08-20');
assertEquals(null, $mappedPending, 'spAPI mapper: pending crawl (capability present, ai_overview key missing) returns null, not "absent" (state 2)');

echo "\n" . bold("=== Domain normalisation ===") . "\n";

assertEquals('example.com', AIOverviewController::normalizeDomain('https://www.example.com/path?x=1'), 'strips protocol, www, path');
assertEquals('example.com', AIOverviewController::normalizeDomain('EXAMPLE.com:8443'), 'lowercases and strips port');
assertEquals('example.com', AIOverviewController::registrableDomain('blog.example.com'), 'registrable domain of a subdomain');
assertEquals('example.co.uk', AIOverviewController::registrableDomain('www.example.co.uk'), 'ccTLD (co.uk) registrable domain uses 3 labels');

// subdomain policy: bare registrable tracked domain matches a subdomain reference
assertTrue(AIOverviewController::isDomainCited('blog.example.com', 'example.com', 'registrable'), 'registrable policy: bare tracked domain matches subdomain reference');
assertTrue(!AIOverviewController::isDomainCited('blog.example.com', 'example.com', 'exact'), 'exact policy: bare tracked domain does NOT match subdomain reference');
// exact subdomain entry only matches itself, regardless of policy
assertTrue(!AIOverviewController::isDomainCited('example.com', 'blog.example.com', 'registrable'), 'explicit subdomain entry does not match its parent domain');
assertTrue(AIOverviewController::isDomainCited('blog.example.com', 'blog.example.com', 'registrable'), 'explicit subdomain entry matches itself');
assertTrue(!AIOverviewController::isDomainCited('other.example.com', 'blog.example.com', 'registrable'), 'explicit subdomain entry does not match a sibling subdomain');

echo "\n" . bold("=== Idempotency: saveResult() re-run does not duplicate references ===") . "\n";

$testKeywordId = 999990001;
$testSeId = 999990002;
$testDate = date('Y-m-d');
$aioCtrler = new AIOverviewController();

// clean slate + a synthetic searchresults row for saveResult()'s UPDATE to target
$aioCtrler->dbHelper->deleteRows('aio_references', "keyword_id=$testKeywordId");
$aioCtrler->dbHelper->deleteRows('searchresults', "keyword_id=$testKeywordId AND searchengine_id=$testSeId AND result_date='$testDate'");
$aioCtrler->dbHelper->insertRow('searchresults', [
    'keyword_id|int' => $testKeywordId,
    'searchengine_id|int' => $testSeId,
    'rank|int' => 3,
    'time|int' => time(),
    'result_date' => $testDate,
]);

$normalizedForIdem = AIOverviewController::parseDataForSEO($fixtureB, $testDate);
$aioCtrler->saveResult($testKeywordId, $testSeId, $testDate, 'dataforseo', $normalizedForIdem, 'tracked-site.com', 'registrable');
$countAfterFirst = count($aioCtrler->dbHelper->getAllRows('aio_references', "keyword_id=$testKeywordId"));

$aioCtrler->saveResult($testKeywordId, $testSeId, $testDate, 'dataforseo', $normalizedForIdem, 'tracked-site.com', 'registrable');
$countAfterSecond = count($aioCtrler->dbHelper->getAllRows('aio_references', "keyword_id=$testKeywordId"));

assertEquals(2, $countAfterFirst, 'first saveResult() call inserts 2 deduped reference rows');
assertEquals($countAfterFirst, $countAfterSecond, 're-running saveResult() for same keyword+date does not duplicate rows');

$savedRow = $aioCtrler->dbHelper->getRow('searchresults', "keyword_id=$testKeywordId AND searchengine_id=$testSeId AND result_date='$testDate'");
assertEquals(1, intval($savedRow['aio_present']), 'saveResult() marks aio_present=1 on the searchresults row');
assertEquals(1, intval($savedRow['aio_cited']), 'saveResult() marks aio_cited=1 (tracked domain was in references)');

// cleanup
$aioCtrler->dbHelper->deleteRows('aio_references', "keyword_id=$testKeywordId");
$aioCtrler->dbHelper->deleteRows('searchresults', "keyword_id=$testKeywordId AND searchengine_id=$testSeId AND result_date='$testDate'");

echo "\n" . bold("=== Migration sanity: legacy rows stay distinguishable ===") . "\n";
$legacyCount = $aioCtrler->db->select("SELECT COUNT(*) AS c FROM searchresults WHERE aio_checked_at IS NULL", true);
assertTrue(isset($legacyCount['c']), 'searchresults is queryable with the new aio_* columns after migration');

echo "\n" . bold("=== Rolling window: distinct observation dates, not calendar days ===") . "\n";

// Simulate the archive-path scenario: the SEO Panel API serves the SAME
// underlying observation (aio_data_date) across three consecutive daily
// cron checks. The rolling window must count this as ONE observation, not
// three - otherwise it fabricates confidence the data doesn't have.
$rwKeywordId = 999990003;
$rwSeId = 999990004;
$aioCtrler->dbHelper->deleteRows('searchresults', "keyword_id=$rwKeywordId AND searchengine_id=$rwSeId");

$sharedNormalized = ['supported' => true, 'present' => true, 'async' => false, 'references' => [], 'data_date' => '2026-08-18'];
$checkDates = ['2026-08-18', '2026-08-19', '2026-08-20']; // 3 separate daily checks
foreach ($checkDates as $checkDate) {
    $aioCtrler->dbHelper->insertRow('searchresults', [
        'keyword_id|int' => $rwKeywordId,
        'searchengine_id|int' => $rwSeId,
        'rank|int' => 2,
        'time|int' => strtotime($checkDate),
        'result_date' => $checkDate,
    ]);
    // every check re-serves the same archived observation (aio_data_date fixed)
    $aioCtrler->saveResult($rwKeywordId, $rwSeId, $checkDate, 'spapi', $sharedNormalized, 'tracked-site.com', 'registrable');
}

$window = $aioCtrler->getRollingWindow($rwKeywordId, $rwSeId, 7);
assertEquals(1, $window['measured'], 'rolling window collapses 3 checks sharing one aio_data_date into 1 measured observation');
assertEquals(1, $window['present'], 'the single collapsed observation is counted as present');

// cleanup
$aioCtrler->dbHelper->deleteRows('searchresults', "keyword_id=$rwKeywordId AND searchengine_id=$rwSeId");
$aioCtrler->dbHelper->deleteRows('aio_references', "keyword_id=$rwKeywordId");

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
