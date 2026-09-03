<?php
// Support file, not a runnable test - included by tests/cron_tool_integration_test.php.
// Defensively self-bootstraps so it's a harmless no-op (defines the class,
// does nothing else) if ever invoked directly by a "run every tests/*.php"
// loop, rather than fatal-erroring on a missing CronController/SP_CTRLPATH.
if (!defined('SP_CTRLPATH')) {
    include_once(dirname(__FILE__) . "/../includes/sp-load.php");
}
include_once(SP_CTRLPATH . "/cron.ctrl.php");

/**
 * Fixture-factory subclass of CronController for exercising real
 * *CronQueued() method bodies in tests, safely.
 *
 * This does NOT override any cron logic or mock any tool controller -
 * CronController::$websiteInfo/$timeStamp/$deadline are already plain
 * dynamic properties settable from outside (see how executeCron()/
 * routeCronJob()/runPingTrigger() set them), so a real *CronQueued() method
 * can already be called directly once those are populated. This class only
 * adds fixture setup/teardown convenience on top of that.
 *
 * Safety depends entirely on SP_USE_SAMPLE_API_DATA being forced on BEFORE
 * includes/sp-load.php runs (settings are define()'d once from the DB at
 * bootstrap - a PHP constant can't be redefined mid-process, so toggling the
 * DB row after bootstrap has no effect on the constant a guard clause reads).
 * See the header of tests/cron_tool_integration_test.php for how that's done.
 */
class TestCronController extends CronController {

    var $fixtureWebsiteIds = array();
    var $fixtureUserId = 888880;

    /**
     * A real (but harmless, clearly-fake) users row - not just a bare id
     * referenced by websites.user_id. Several code paths reached by the
     * "no OAuth token linked" no-op case (e.g. AlertController::createAlert())
     * still write a row keyed by user_id via a foreign key, which throws if
     * the id doesn't actually exist in `users`. This has no real account
     * capability - no confirmed login, no email that goes anywhere.
     */
    function __ensureFixtureUser() {
        $existing = $this->db->select("SELECT id FROM users WHERE id={$this->fixtureUserId}", true);
        if (!empty($existing['id'])) {
            return;
        }
        $this->dbHelper->insertRow('users', array(
            'id|int'                     => $this->fixtureUserId,
            'username'                   => 'testcron_fixture_user',
            'password'                   => '',
            'email'                      => 'testcron-fixture@example.com',
            'status|int'                 => 0,
            'lang_code'                  => 'en',
            'created|int'                => time(),
            'confirm_code'               => '',
            'confirm|int'                => 0,
            'spapi_skip|int'             => 0,
            'setup_wizard_step|int'      => 0,
            'setup_wizard_dismissed|int' => 1,
        ));
    }

    /**
     * Insert a throwaway website row, set it as $this->websiteInfo/timeStamp
     * (what every *CronQueued() method reads), and track it for cleanup.
     * Owned by the fixture user above (never a real account, no OAuth token
     * ever linked to it) - which makes any OAuth-dependent tool (analytics,
     * webmaster tools) a safe no-op without needing its own sample-data guard.
     */
    function setupFixtureWebsite($overrides = array()) {
        $this->__ensureFixtureUser();

        $websiteId = !empty($overrides['id']) ? intval($overrides['id']) : (999990 - count($this->fixtureWebsiteIds));

        $this->db->query("DELETE FROM websites WHERE id=$websiteId");
        $data = array_merge(array(
            'id|int'      => $websiteId,
            'name'        => "Test Cron Fixture $websiteId",
            'url'         => 'https://example.com',
            'user_id|int' => $this->fixtureUserId,
            'status|int'  => 1,
        ), $overrides);
        $this->dbHelper->insertRow('websites', $data);

        $this->fixtureWebsiteIds[] = $websiteId;

        $websiteInfo = $this->db->select("SELECT * FROM websites WHERE id=$websiteId", true);
        $this->useFixtureWebsite($websiteInfo);

        return $websiteInfo;
    }

    /**
     * Point $this->websiteInfo/timeStamp at an already-created fixture
     * website, WITHOUT inserting/deleting any row. Every *CronQueued()
     * method reads $this->websiteInfo internally (not whatever $websiteId
     * you happen to pass it), so when a test juggles more than one fixture
     * website on the same TestCronController instance, call this
     * immediately before EACH *CronQueued() call to make the active context
     * unambiguous - setupFixtureWebsite()'s call for website B otherwise
     * silently leaves website A's later calls operating against B's info.
     */
    function useFixtureWebsite($websiteInfo) {
        $this->websiteInfo = $websiteInfo;
        $this->timeStamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return $websiteInfo;
    }

    /**
     * $withSearchEngine=true assigns a real searchengines row (colon-joined
     * id format the keyword-position-checker tiers expect) - required for
     * that tool's cron body to actually reach the network-calling code
     * instead of skipping the keyword as "not assigned to required search
     * engines" (a real, separate guard clause in the app itself, not a
     * substitute for the SP_USE_SAMPLE_API_DATA guards this harness relies on).
     */
    function addFixtureKeyword($websiteId, $name = 'test keyword', $withSearchEngine = false) {
        $this->db->query("DELETE FROM keywords WHERE website_id=$websiteId AND name='" . addslashes($name) . "'");

        $searchEngines = '';
        if ($withSearchEngine) {
            $seRow = $this->db->select("SELECT id FROM searchengines LIMIT 1", true);
            $searchEngines = !empty($seRow['id']) ? $seRow['id'] : '';
        }

        $this->dbHelper->insertRow('keywords', array(
            'name'           => $name,
            'website_id|int' => $websiteId,
            'status|int'     => 1,
            'searchengines'  => $searchEngines,
        ));
        return $this->db->select(
            "SELECT * FROM keywords WHERE website_id=$websiteId AND name='" . addslashes($name) . "' ORDER BY id DESC LIMIT 1",
            true
        );
    }

    /**
     * Remove every row this instance's fixture websites could have touched,
     * across every tool this harness exercises. Safe to call even if a given
     * table never actually received a row for this website.
     */
    function cleanupFixtures() {
        foreach ($this->fixtureWebsiteIds as $websiteId) {
            $websiteId = intval($websiteId);
            $this->db->query("DELETE FROM dfs_tasks WHERE ref_id IN (SELECT id FROM keywords WHERE website_id=$websiteId)");
            $this->db->query("DELETE sr FROM searchresults sr JOIN keywords k ON k.id = sr.keyword_id WHERE k.website_id=$websiteId");
            $this->db->query("DELETE FROM keyword_search_volume WHERE keyword_id IN (SELECT id FROM keywords WHERE website_id=$websiteId)");
            $this->db->query("DELETE FROM keywordcrontracker WHERE keyword_id IN (SELECT id FROM keywords WHERE website_id=$websiteId)");
            $this->db->query("DELETE FROM keywords WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM backlinkresults WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM rankresults WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM saturationresults WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM job_queue WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM cron_job_timing WHERE website_id=$websiteId");
            $this->db->query("DELETE FROM websites WHERE id=$websiteId");
        }
        $this->fixtureWebsiteIds = array();
        $this->db->query("DELETE FROM users WHERE id={$this->fixtureUserId}");
    }
}
