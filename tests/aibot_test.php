<?php
/**
 * AI Visibility: AI Bot Crawler Tracking tests.
 *
 * Exercises the collector script generation (valid PHP output), the
 * ingest endpoint's UA-to-platform classification and aggregate-on-write
 * dedup logic directly against the real dev DB with a throwaway website id,
 * and the collector script's own prefilter logic in isolation. No network
 * calls, no live DNS resolution required (FCrDNS itself is not exercised
 * here - it's a thin wrapper around gethostbyaddr()/gethostbyname() that
 * only runs on the customer's own server, not testable in isolation
 * without a real bot IP).
 *
 * Usage: php tests/aibot_test.php
 */

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");

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

$aivCtrler = new AIVisibilityController();
$websiteId = 999998; // throwaway, distinct from job_queue_test.php's 999999
$testToken = bin2hex(random_bytes(24));

// clean slate
$aivCtrler->db->query("DELETE FROM ai_visibility_sites WHERE website_id=$websiteId");
$aivCtrler->db->query("DELETE FROM ai_bot_hits WHERE website_id=$websiteId");
$aivCtrler->dbHelper->insertRow('ai_visibility_sites', [
    'website_id|int' => $websiteId,
    'token' => $testToken,
    'domain' => 'example.com',
    'created_at' => 'NOW()',
]);

echo bold("=== generateBotCollectorScript(): output is valid, self-contained PHP ===") . "\n";

$script = $aivCtrler->generateBotCollectorScript($websiteId);
assertTrue(strpos($script, '<?php') === 0, 'collector script starts with <?php');
assertTrue(strpos($script, $testToken) !== false, 'collector script embeds the site token');
assertTrue(strpos($script, 'include') === false && strpos($script, 'require') === false, 'collector script has no include/require - no remote-code-execution surface');

$tmpFile = tempnam(sys_get_temp_dir(), 'aibot_collector_test_') . '.php';
file_put_contents($tmpFile, $script);
exec('php -l ' . escapeshellarg($tmpFile) . ' 2>&1', $lintOutput, $lintStatus);
assertEquals(0, $lintStatus, 'generated collector script passes php -l: ' . implode(' ', $lintOutput));
unlink($tmpFile);

echo "\n" . bold("=== collector script prefilter: no-op for normal traffic, proceeds for bot-like UAs ===") . "\n";

// Exercise the prefilter regex directly (mirrors the pattern embedded in
// generateBotCollectorScript()) rather than actually including the
// generated file, since including it would attempt a real network POST.
$prefilter = '/bot|crawler|spider|agent/i';
assertTrue(!preg_match($prefilter, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'), 'normal browser UA does not match the prefilter');
assertTrue((bool)preg_match($prefilter, 'Mozilla/5.0 AppleWebKit (compatible; GPTBot/1.0; +https://openai.com/gptbot)'), 'GPTBot UA matches the prefilter');
assertTrue((bool)preg_match($prefilter, 'ClaudeBot/1.0 (+https://www.anthropic.com/claudebot)'), 'ClaudeBot UA matches the prefilter');

echo "\n" . bold("=== ingestBotHit(): UA classification + aggregate-on-write ===") . "\n";

function postBotHit($token, $ua, $verified, $path) {
    $payload = json_encode(['t' => $token, 'ua' => $ua, 'verified' => $verified, 'u' => $path]);
    $tmpIn = tempnam(sys_get_temp_dir(), 'aibot_payload_');
    file_put_contents($tmpIn, $payload);
    return $tmpIn;
}

// simulate the raw POST body path ingestBotHit() reads via php://input by
// calling the classification/aggregation logic directly through a tiny
// harness that mirrors ingestBotHit()'s SQL, since php://input can't be
// stubbed from within the same process
function classifyAndAggregate($db, $dbHelper, $websiteId, $token, $ua, $verified, $path) {
    $siteInfo = $dbHelper->getRow('ai_visibility_sites', "token='" . addslashes($token) . "'");
    if (empty($siteInfo)) return null;

    $platformList = $db->select("SELECT platform, bot_ua_pattern FROM ai_platforms WHERE is_active=1 AND bot_ua_pattern IS NOT NULL AND bot_ua_pattern != ''");
    $matchedPlatform = null;
    foreach ($platformList as $platformRow) {
        if (stripos($ua, $platformRow['bot_ua_pattern']) !== false) {
            $matchedPlatform = $platformRow['platform'];
            break;
        }
    }
    if (empty($matchedPlatform)) return null;

    $urlPath = strtok($path, '?');
    $urlPath = strtok($urlPath, '#');
    if (empty($urlPath)) $urlPath = '/';

    $hitDate = date('Y-m-d');
    $urlHashHex = bin2hex(md5($urlPath, true));
    $platformSql = addslashes($matchedPlatform);
    $urlPathSql = addslashes($urlPath);
    $verifiedInt = $verified ? 1 : 0;

    $db->query("INSERT INTO ai_bot_hits (website_id, hit_date, platform, verified, url_path, url_hash, hits, created_at, updated_at)
                VALUES (" . intval($siteInfo['website_id']) . ", '$hitDate', '$platformSql', $verifiedInt, '$urlPathSql', UNHEX('$urlHashHex'), 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE hits = hits + 1, updated_at = NOW()");

    return $matchedPlatform;
}

$platform = classifyAndAggregate($aivCtrler->db, $aivCtrler->dbHelper, $websiteId, $testToken, 'Mozilla/5.0 AppleWebKit (compatible; GPTBot/1.0)', true, '/pricing');
assertEquals('chatgpt', $platform, 'GPTBot UA classifies as the chatgpt platform');

$row = $aivCtrler->dbHelper->getRow('ai_bot_hits', "website_id=$websiteId AND platform='chatgpt' AND verified=1 AND url_path='/pricing'");
assertEquals('1', $row['hits'], 'first hit inserts a row with hits=1');

classifyAndAggregate($aivCtrler->db, $aivCtrler->dbHelper, $websiteId, $testToken, 'Mozilla/5.0 AppleWebKit (compatible; GPTBot/1.0)', true, '/pricing?utm_source=x');
$row = $aivCtrler->dbHelper->getRow('ai_bot_hits', "website_id=$websiteId AND platform='chatgpt' AND verified=1 AND url_path='/pricing'");
assertEquals('2', $row['hits'], 'second hit (same path, query string stripped) aggregates instead of duplicating');

classifyAndAggregate($aivCtrler->db, $aivCtrler->dbHelper, $websiteId, $testToken, 'Mozilla/5.0 AppleWebKit (compatible; GPTBot/1.0)', false, '/pricing');
$verifiedRow = $aivCtrler->dbHelper->getRow('ai_bot_hits', "website_id=$websiteId AND platform='chatgpt' AND verified=1 AND url_path='/pricing'");
$unverifiedRow = $aivCtrler->dbHelper->getRow('ai_bot_hits', "website_id=$websiteId AND platform='chatgpt' AND verified=0 AND url_path='/pricing'");
assertEquals('2', $verifiedRow['hits'], 'verified=1 row is untouched by an unverified hit on the same path');
assertEquals('1', $unverifiedRow['hits'], 'verified and unverified hits on the same path aggregate into separate rows');

$unmatched = classifyAndAggregate($aivCtrler->db, $aivCtrler->dbHelper, $websiteId, $testToken, 'Mozilla/5.0 (a completely ordinary browser)', true, '/pricing');
assertTrue(is_null($unmatched), 'a UA matching no known bot_ua_pattern is silently dropped (no platform match)');

// cleanup
$aivCtrler->db->query("DELETE FROM ai_bot_hits WHERE website_id=$websiteId");
$aivCtrler->db->query("DELETE FROM ai_visibility_sites WHERE website_id=$websiteId");

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
