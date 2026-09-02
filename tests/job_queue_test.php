<?php
/**
 * Zero-Setup Scheduler, Phase 1 - resumable job queue primitive tests.
 *
 * Exercises CronController's enqueueChunks()/claimNextChunk()/completeChunk()/
 * failChunk()/reapStaleChunks() in isolation, against a throwaway url_section
 * and fabricated website_id so these rows never interact with real tool
 * dispatch. Real dev DB, zero network calls - same pattern as
 * tests/aioverview_test.php.
 *
 * Usage: php tests/job_queue_test.php
 */

include_once(dirname(__FILE__) . "/../includes/sp-load.php");
include_once(SP_CTRLPATH . "/cron.ctrl.php");

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

$cron = new CronController();
$urlSection = 'test-tool';
$websiteId = 999999;

// start clean in case a previous failed run left rows behind
$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");

echo bold("=== job_queue: enqueueChunks() / claimNextChunk() ===") . "\n";

$cron->enqueueChunks($urlSection, $websiteId, ['alpha' => ['n' => 1], 'beta' => null]);
$row = $cron->dbHelper->getRow('job_queue', "website_id=$websiteId AND url_section='$urlSection' AND chunk_key='alpha'");
assertEquals('pending', $row['status'], 'enqueueChunks() inserts a new chunk as pending');
assertEquals('{"n":1}', $row['payload'], 'enqueueChunks() stores payload as JSON');

$claimed = $cron->claimNextChunk($urlSection, $websiteId);
assertEquals('alpha', $claimed['chunk_key'], 'claimNextChunk() claims the oldest pending chunk first (alpha before beta)');
assertEquals(['n' => 1], $claimed['payload'], 'claimNextChunk() json_decodes the payload');
$row = $cron->dbHelper->getRow('job_queue', "id=" . $claimed['id']);
assertEquals('running', $row['status'], 'claimNextChunk() flips status to running');
assertEquals('1', $row['attempts'], 'claimNextChunk() increments attempts');

$nullClaim = $cron->claimNextChunk($urlSection, $websiteId);
assertEquals('beta', $nullClaim['chunk_key'], 'claimNextChunk() claims the second chunk next');

$emptyClaim = $cron->claimNextChunk($urlSection, $websiteId);
assertTrue(is_null($emptyClaim), 'claimNextChunk() returns null when nothing pending is left');

echo "\n" . bold("=== job_queue: completeChunk() / re-enqueue idempotency ===") . "\n";

$cron->completeChunk($claimed['id']);
$row = $cron->dbHelper->getRow('job_queue', "id=" . $claimed['id']);
assertEquals('completed', $row['status'], 'completeChunk() marks a chunk completed');
assertTrue(!empty($row['completed_at']), 'completeChunk() sets completed_at');

// re-enqueue: a still-running chunk (beta) must NOT be disturbed, a completed
// chunk (alpha) must be revived back to pending
$cron->enqueueChunks($urlSection, $websiteId, ['alpha' => null, 'beta' => null]);
$alphaRow = $cron->dbHelper->getRow('job_queue', "id=" . $claimed['id']);
assertEquals('pending', $alphaRow['status'], 'enqueueChunks() revives a completed chunk back to pending');
$betaRow = $cron->dbHelper->getRow('job_queue', "id=" . $nullClaim['id']);
assertEquals('running', $betaRow['status'], 'enqueueChunks() does not disturb a running chunk');

// undo the revive so it doesn't interfere with the reaping test below
$cron->dbHelper->updateRow('job_queue', ['status' => 'completed'], "id=" . $claimed['id']);

echo "\n" . bold("=== job_queue: reapStaleChunks() ===") . "\n";

// beta is still 'running' from the claim above - backdate its claimed_at so
// it looks stuck, then confirm reaping reverts it to pending
$cron->db->query("UPDATE job_queue SET claimed_at = NOW() - INTERVAL 20 MINUTE WHERE id=" . $nullClaim['id']);
$cron->reapStaleChunks(15);
$row = $cron->dbHelper->getRow('job_queue', "id=" . $nullClaim['id']);
assertEquals('pending', $row['status'], 'reapStaleChunks() reverts a stuck running chunk back to pending after the stale threshold');

$freshClaim = $cron->claimNextChunk($urlSection, $websiteId);
assertEquals('beta', $freshClaim['chunk_key'], 'a reaped chunk can be claimed again');

echo "\n" . bold("=== job_queue: failChunk() backoff progression ===") . "\n";

// fresh chunk, claimed exactly once (attempts=1) before the first induced failure
$cron->enqueueChunks($urlSection, $websiteId, ['gamma' => null]);
$gammaClaim = $cron->claimNextChunk($urlSection, $websiteId);
assertEquals('gamma', $gammaClaim['chunk_key'], 'fresh chunk for the backoff sequence claims cleanly');

$expectedMinutes = [5, 25, 125];
foreach ($expectedMinutes as $i => $minutes) {
    $before = $cron->dbHelper->getRow('job_queue', "id=" . $gammaClaim['id']);
    $cron->failChunk($gammaClaim['id'], "induced failure #" . ($i + 1));
    $after = $cron->dbHelper->getRow('job_queue', "id=" . $gammaClaim['id']);

    assertEquals('pending', $after['status'], "failChunk() attempt " . ($i + 1) . " reverts to pending (retry $minutes min)");

    $delta = round((strtotime($after['available_at']) - strtotime($before['claimed_at'] ?: $before['updated_at'])) / 60);
    assertTrue(abs($delta - $minutes) <= 1, "failChunk() attempt " . ($i + 1) . " backs off ~$minutes minutes (got {$delta}m)");

    // re-claim so the next induced failure increments attempts again
    $cron->db->query("UPDATE job_queue SET available_at = NOW() WHERE id=" . $gammaClaim['id']);
    $gammaClaim = $cron->claimNextChunk($urlSection, $websiteId);
}

// 4th failure (attempts now at max_attempts) must be terminal
$cron->failChunk($gammaClaim['id'], "induced failure #4 (terminal)");
$row = $cron->dbHelper->getRow('job_queue', "id=" . $gammaClaim['id']);
assertEquals('failed', $row['status'], 'failChunk() goes terminal once max_attempts is reached');
assertEquals('induced failure #4 (terminal)', $row['last_error'], 'failChunk() records the last error message');

// a terminally-failed chunk must not be claimable
$cron->reapStaleChunks(0); // in case claimed_at looks stale, this must not touch a 'failed' row
$notClaimed = $cron->claimNextChunk($urlSection, $websiteId);
assertTrue(is_null($notClaimed), 'a terminally failed chunk is never claimed again');

echo "\n" . bold("=== drainChunkQueue(): the loop every *CronQueued() method calls ===") . "\n";

$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");

// (a) processes every chunk in order, to exhaustion
$cron->enqueueChunks($urlSection, $websiteId, ['one' => null, 'two' => null, 'three' => null]);
$seen = [];
$cron->drainChunkQueue($urlSection, $websiteId, function($chunk) use (&$seen) {
    $seen[] = $chunk['chunk_key'];
});
assertEquals(['one', 'two', 'three'], $seen, 'drainChunkQueue() processes every pending chunk in order');
$remaining = $cron->db->select("SELECT COUNT(*) as c FROM job_queue WHERE url_section='$urlSection' AND status != 'completed'", true);
assertEquals('0', $remaining['c'], 'drainChunkQueue() leaves nothing but completed chunks behind');

// (b) failure isolation: one throwing chunk doesn't stop the rest
$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");
$cron->enqueueChunks($urlSection, $websiteId, ['ok1' => null, 'bad' => null, 'ok2' => null]);
$seen = [];
$cron->drainChunkQueue($urlSection, $websiteId, function($chunk) use (&$seen) {
    if ($chunk['chunk_key'] == 'bad') throw new Exception('induced chunk failure');
    $seen[] = $chunk['chunk_key'];
});
assertEquals(['ok1', 'ok2'], $seen, 'a thrown exception fails only that chunk - the rest still get processed');
$badRow = $cron->dbHelper->getRow('job_queue', "website_id=$websiteId AND url_section='$urlSection' AND chunk_key='bad'");
assertEquals('pending', $badRow['status'], 'the failed chunk reverts to pending (attempt 1 of its own backoff), not completed');
assertEquals('1', $badRow['attempts'], 'the failed chunk recorded exactly one attempt');

// (c) SP_NUMBER_KEYWORDS_CRON cap short-circuits keyword-position-checker draining cleanly
$cron->db->query("DELETE FROM job_queue WHERE url_section='keyword-position-checker' AND website_id=$websiteId");
$cron->enqueueChunks('keyword-position-checker', $websiteId, ['k1' => null, 'k2' => null, 'k3' => null]);
$cron->checkedKeywords = 0;
$processed = [];
// SP_NUMBER_KEYWORDS_CRON is a real DB-driven setting (defaults to '1' - see
// plan notes); drainChunkQueue() must stop claiming once checkedKeywords
// reaches it, leaving the remaining chunks safely pending for next time.
$cron->drainChunkQueue('keyword-position-checker', $websiteId, function($chunk) use (&$processed) {
    $processed[] = $chunk['chunk_key'];
});
assertEquals(intval(SP_NUMBER_KEYWORDS_CRON), count($processed), 'drainChunkQueue() stops claiming keyword-position-checker chunks once SP_NUMBER_KEYWORDS_CRON is reached');
$stillPending = $cron->db->select("SELECT COUNT(*) as c FROM job_queue WHERE url_section='keyword-position-checker' AND website_id=$websiteId AND status='pending'", true);
assertEquals((string)(3 - intval(SP_NUMBER_KEYWORDS_CRON)), $stillPending['c'], 'remaining keyword-position-checker chunks stay pending (not failed, not lost) for the next invocation');

// cleanup
$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");
$cron->db->query("DELETE FROM job_queue WHERE url_section='keyword-position-checker' AND website_id=$websiteId");

echo "\n" . bold("=== Phase 2: \$deadline budget enforcement in drainChunkQueue() ===") . "\n";

$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");
$cron->enqueueChunks($urlSection, $websiteId, ['d1' => null, 'd2' => null, 'd3' => null]);

// deadline already in the past - drainChunkQueue() must claim nothing at all
$cron->deadline = microtime(true) - 1;
$processed = [];
$cron->drainChunkQueue($urlSection, $websiteId, function($chunk) use (&$processed) {
    $processed[] = $chunk['chunk_key'];
});
assertEquals(0, count($processed), 'drainChunkQueue() claims nothing once $deadline has already passed');
$pendingCount = $cron->db->select("SELECT COUNT(*) as c FROM job_queue WHERE url_section='$urlSection' AND status='pending'", true);
assertEquals('3', $pendingCount['c'], 'all chunks remain pending when the deadline is already up');
$cron->deadline = null; // reset - a null deadline must behave exactly like Phase 1 (unbounded)

$processed = [];
$cron->drainChunkQueue($urlSection, $websiteId, function($chunk) use (&$processed) {
    $processed[] = $chunk['chunk_key'];
});
assertEquals(3, count($processed), 'a null $deadline drains to exhaustion exactly like the CLI path (Phase 1 behavior unchanged)');

// cleanup
$cron->db->query("DELETE FROM job_queue WHERE url_section='$urlSection'");

$total = count($results);
$passed = count(array_filter($results));
echo "\n" . bold("$passed / $total assertions passed") . "\n";
if ($passed !== $total) {
    exit(1);
}
