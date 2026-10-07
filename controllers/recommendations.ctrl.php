<?php
/**
 * Recommendations controller — generates and stores SEO recommendations
 * derived from data already collected across SEO Panel modules.
 */

class RecommendationsController extends Controller {

    /*
     * Show the recommendations dashboard for a website.
     */
    function showRecommendationsDashboard($data) {

        $userId    = isLoggedIn();
        $websiteController = new WebsiteController();
        $websiteList = $websiteController->__getAllWebsites($userId, true);
        $this->set('websiteList', $websiteList);
        $this->set('noWebsites', empty($websiteList));

        $websiteId = !empty($data['website_id']) ? intval($data['website_id']) : 0;
        // a non-admin's website_id must be one of their own (already-scoped)
        // websites - previously unchecked. This matters more than the usual
        // read-only IDOR shape: refreshRecommendations() below actually
        // WRITES sp_recommendations rows keyed by (website_id, THIS
        // session's own user_id), so an unchecked foreign website_id there
        // would let a non-admin launder another user's real keyword/
        // webmaster/site-auditor/AI-visibility data into a row their own
        // user_id can then read back right here. Falling back to their own
        // first website reuses this method's own existing "no website_id
        // given" semantics.
        if (!empty($websiteId) && !isAdmin() && !in_array($websiteId, array_column($websiteList, 'id'))) {
            $websiteId = 0;
        }
        if (empty($websiteId) && !empty($websiteList)) {
            $websiteId = intval($websiteList[0]['id']);
        }
        $this->set('websiteId', $websiteId);

        if (!empty($websiteId)) {
            $refreshedAt = $this->__getLastRefreshedAt($websiteId, $userId);
            $this->set('refreshedAt', $refreshedAt);
            $recommendations = $this->__getStoredRecommendations($websiteId, $userId);
            $this->set('recommendations', $recommendations);
        } else {
            $this->set('refreshedAt', null);
            $this->set('recommendations', array());
        }

        include_once(SP_CTRLPATH . "/settings.ctrl.php");
        $this->set('localAiAvailable', SettingsController::isLocalAIEnabled());
        $this->set('spTextRec', $this->getLanguageTexts('recommendations', $_SESSION['lang_code']));

        // "Add to SEO Diary" per-finding action - only offered when the
        // plugin is installed+active AND the CURRENT user's account type
        // actually has access to it. isPluginActive() alone isn't enough -
        // seo-plugins.php's own manageSeoPlugins() separately enforces a
        // per-usertype plugin access setting for any non-admin, and would
        // reject the very link this renders with "Access denied" if that
        // second check is skipped here (this is exactly the bug a user
        // hit testing this feature - the button rendered for their
        // non-admin account, but clicking it 403'd). Mirrors
        // showSeoPlugins()'s own access-list logic exactly
        // (controllers/seoplugins.ctrl.php): getPluginAccessSettings()
        // always returns a 'value' key per plugin, defaulting to 0 when
        // no explicit user_specs row exists - so access is DENY-BY-DEFAULT
        // for a non-admin until an admin explicitly grants this plugin to
        // their user type via the User Type manager.
        include_once(SP_CTRLPATH . "/seoplugins.ctrl.php");
        $seoDiaryInfo = (new SeoPluginsController())->isPluginActive("SeoDiary");
        $seoDiaryPluginId = 0;
        if (!empty($seoDiaryInfo['id'])) {
            if (isAdmin()) {
                $seoDiaryPluginId = $seoDiaryInfo['id'];
            } else {
                include_once(SP_CTRLPATH . "/user-type.ctrl.php");
                $userSessInfo = Session::readSession('userInfo');
                $pluginAccessList = (new UserTypeController())->getPluginAccessSettings($userSessInfo['userTypeId']);
                $hasAccess = !isset($pluginAccessList[$seoDiaryInfo['id']]['value']) || !empty($pluginAccessList[$seoDiaryInfo['id']]['value']);
                if ($hasAccess) {
                    $seoDiaryPluginId = $seoDiaryInfo['id'];
                }
            }
        }
        $this->set('seoDiaryPluginId', $seoDiaryPluginId);

        $this->render('dashboard/recommendations_main');
    }

    /*
     * AJAX action: on-demand Local AI summary of this website's current
     * findings - never auto-fired on page load (unnecessary local compute).
     * Ownership is implicit since generateInsightsSummary() reads from
     * __getStoredRecommendations($websiteId, $userId), which already scopes
     * by this session's own user_id.
     */
    function generateAISummaryAction($info) {
        $userId = isLoggedIn();
        $websiteId = !empty($info['website_id']) ? intval($info['website_id']) : 0;

        header('Content-Type: application/json');
        if (empty($websiteId)) {
            echo json_encode(['ok' => false, 'error' => 'Missing website_id']);
            exit;
        }

        // same ownership check as refreshRecommendations() - without it a
        // non-admin who already laundered another user's data via that
        // write path (or simply guesses a website_id with pre-existing
        // rows) could ask the AI to summarize it.
        if (!isAdmin()) {
            $websiteController = new WebsiteController();
            $ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
            if (!in_array($websiteId, $ownedIds)) {
                echo json_encode(['ok' => false, 'error' => 'Not authorized']);
                exit;
            }
        }

        include_once(SP_CTRLPATH . "/settings.ctrl.php");
        include_once(SP_CTRLPATH . "/localai.ctrl.php");
        $localAiCtrler = new LocalAIController();
        echo json_encode($localAiCtrler->generateInsightsSummary($websiteId, $userId));
        exit;
    }

    /*
     * Recalculate all recommendations for a website, persist to DB, then re-render.
     * Session-facing wrapper (reads the logged-in user, re-renders the dashboard) -
     * the actual generation logic lives in refreshRecommendationsForWebsite() so
     * it can also be called from a non-session context (the daily cron pass).
     */
    function refreshRecommendations($data) {

        $userId    = isLoggedIn();
        $websiteId = !empty($data['website_id']) ? intval($data['website_id']) : 0;

        // this is the actual write path the IDOR note in
        // showRecommendationsDashboard() above is about - refuse to
        // (re)generate recommendations for a website the caller doesn't own.
        if (!empty($websiteId) && !isAdmin()) {
            $websiteController = new WebsiteController();
            $ownedIds = array_column($websiteController->__getAllWebsites($userId, true), 'id');
            if (!in_array($websiteId, $ownedIds)) {
                $websiteId = 0;
            }
        }

        $refreshError = null;
        if (!empty($websiteId)) {
            // was uncaught - unlike the cron caller (CronController::
            // refreshAllAIInsights()), which already wraps this same call
            // in try/catch. A Throwable from any one of the 18 generators
            // used to surface as a raw uncaught-exception response with no
            // indication to the user that anything failed - now rolled
            // back cleanly by refreshRecommendationsForWebsite() itself
            // (so no data is lost either way), and reported here so the
            // re-rendered dashboard can show a real error instead of
            // silently looking like an "all clear" refresh.
            try {
                $this->refreshRecommendationsForWebsite($websiteId, $userId);
            } catch (Throwable $e) {
                error_log("SEO Panel: refreshRecommendations() failed for website $websiteId: " . $e->getMessage());
                $refreshError = 'Refresh failed - please try again. If this keeps happening, contact support.';
            }
        }

        // must be set BEFORE showRecommendationsDashboard() below, which
        // calls $this->render() as its very last step - a set() after
        // render() already ran has no effect on the output.
        $this->set('refreshError', $refreshError);
        $this->showRecommendationsDashboard($data);
    }

    /*
     * Core generation logic, independent of any HTTP session - callable from
     * the AJAX-facing refreshRecommendations() above, or from a cron context
     * (CronController::refreshAllAIInsights()) that already knows the
     * website's owning user_id.
     *
     * Returns the rows that are genuinely new since the last refresh (by
     * stable identity, not by row id - every refresh deletes and re-inserts
     * everything). The AJAX caller above ignores this; the cron caller uses
     * it to decide what's worth emailing, so a persisting issue whose count
     * merely changed (e.g. 42 -> 45 broken links) does NOT get re-flagged
     * as new every day.
     */
    function refreshRecommendationsForWebsite($websiteId, $userId) {

        $previousKeys = array();
        foreach ($this->__getStoredRecommendations($websiteId, $userId) as $rec) {
            $previousKeys[$this->__recommendationIdentity($rec)] = true;
        }

        // Wrapped in a real DB transaction (sp_recommendations is InnoDB) -
        // this used to DELETE then re-INSERT with no transaction and no
        // concurrency guard at all. Two overlapping refreshes for the same
        // website (a double-click, or two browser tabs) could interleave
        // their DELETE/INSERT and leave duplicate rows, or leave the table
        // empty if one request's DELETE landed after the other's INSERTs
        // had already run. And if any ONE of the 18 generators below threw
        // (a real Throwable, not just a failed query), the DELETE had
        // already committed with nothing re-inserted to replace it - the
        // AJAX caller had no try/catch either (unlike the cron caller,
        // which already wraps this same call), so the user was left with
        // zero recommendations and no indication anything went wrong. Any
        // failure now rolls back to the exact state before this call ran.
        $this->db->query("START TRANSACTION");
        try {
            // Clear old recommendations for this website / user
            $this->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");

            // Generate and persist each recommendation set
            $this->__generateWebmasterRecommendations($websiteId, $userId);
            $this->__generateAIOverviewCitationRecommendations($websiteId, $userId);
            $this->__generateAIBotBlockedRecommendation($websiteId, $userId);
            $this->__generateAIBotSilentRecommendation($websiteId, $userId);
            $this->__generateRankDropRecommendations($websiteId, $userId);
            $this->__generateSiteAuditorRecommendations($websiteId, $userId);
            // Added in priority order from a deep-research pass across every
            // feature/report not yet covered above - see each generator's own
            // comment for its data source and why it's shaped the way it is.
            $this->__generateAiPerceptionDropRecommendations($websiteId, $userId);
            $this->__generateAiPerceptionCompetitorRecommendations($websiteId, $userId);
            $this->__generateBacklinkDropRecommendations($websiteId, $userId);
            $this->__generateReviewDropRecommendations($websiteId, $userId);
            $this->__generateAnalyticsDropRecommendations($websiteId, $userId);
            $this->__generateSearchConsoleDropRecommendations($websiteId, $userId);
            $this->__generatePageSpeedRegressionRecommendations($websiteId, $userId);
            $this->__generateSocialFollowerRecommendations($websiteId, $userId);
            $this->__generateCronReliabilityRecommendations($websiteId, $userId);
            $this->__generateJobQueueFailureRecommendations($websiteId, $userId);
            $this->__generateDirectorySubmissionDecayRecommendations($websiteId, $userId);
            $this->__generateSearchVolumeMismatchRecommendations($websiteId, $userId);
            // Must run LAST - reads this same run's own rank_tracker/
            // site_auditor rows, already inserted above.
            $this->__generateRankDropAuditorCorrelationRecommendations($websiteId, $userId);

            // Recorded independently of sp_recommendations' own row count -
            // see __getLastRefreshedAt()'s own comment for why - and inside
            // this same transaction, so a rolled-back refresh correctly
            // leaves the PREVIOUS refreshed_at in place rather than
            // claiming credit for an attempt that didn't actually commit.
            $this->db->query("INSERT INTO sp_recommendations_refresh (website_id, user_id, refreshed_at)
                    VALUES ($websiteId, $userId, NOW())
                    ON DUPLICATE KEY UPDATE refreshed_at = NOW()");

            $this->db->query("COMMIT");
        } catch (Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }

        $newlyAdded = array();
        foreach ($this->__getStoredRecommendations($websiteId, $userId) as $rec) {
            if (empty($previousKeys[$this->__recommendationIdentity($rec)])) {
                $newlyAdded[] = $rec;
            }
        }
        return $newlyAdded;
    }

    /*
     * Stable identity for a recommendation row, independent of any dynamic
     * count/value baked into its title - keyed on the underlying thing the
     * rule is about (a keyword, or a fixed rule name for site-wide checks),
     * not the row's wording. Falls back to title only for a row whose meta
     * doesn't carry either (shouldn't happen for any current rule).
     */
    private function __recommendationIdentity($rec) {
        $meta = !empty($rec['meta']) ? json_decode($rec['meta'], true) : array();
        $key  = !empty($meta['keyword']) ? $meta['keyword'] : (!empty($meta['rule']) ? $meta['rule'] : $rec['title']);
        return $rec['category'] . '|' . $key;
    }

    /*
     * Send one aggregated "what's new" email for a user, covering every
     * website of theirs that has genuinely new insights today. Mirrors
     * ReportController::sentEmailNotificationForReportGen()'s shape.
     * $newInsightsByWebsite: [websiteId => ['name' => ..., 'rows' => [...]]].
     * Uses $userInfo['lang_code'] directly (the recipient's own language) -
     * unlike the report email, this must not read $_SESSION['lang_code'],
     * since this runs from a session-less cron context.
     */
    function sendAIInsightsDigestEmail($userInfo, $newInsightsByWebsite) {
        if (empty($newInsightsByWebsite)) return false;

        $aiTexts = $this->getLanguageTexts('aiinsights', $userInfo['lang_code']);
        $this->set('aiTexts', $aiTexts);
        $this->set('commonTexts', $this->getLanguageTexts('common', $userInfo['lang_code']));
        $this->set('loginTexts', $this->getLanguageTexts('login', $userInfo['lang_code']));

        $name = trim($userInfo['first_name'] . ' ' . $userInfo['last_name']);
        $this->set('name', $name);
        $this->set('newInsightsByWebsite', $newInsightsByWebsite);

        $subject = !empty($aiTexts['ai_insights_email_subject']) ? $aiTexts['ai_insights_email_subject'] : 'New AI Insights for your website';
        $content = $this->getViewContent('email/aiinsightsdigest');

        $userController = new UserController();
        $adminInfo = $userController->__getAdminInfo();
        $adminName = $adminInfo['first_name'] . "-" . $adminInfo['last_name'];
        $this->set('adminName', $adminName);

        return sendMail($adminInfo['email'], $adminName, $userInfo['email'], $subject, $content);
    }

    /*
     * Return recommendations stored in DB for a website. Not private -
     * called directly by LocalAIController::generateInsightsSummary() to
     * build its summary from these same deterministic findings, rather
     * than duplicating this query (double-underscore convention already
     * used across controllers for cross-controller calls, e.g.
     * AIVisibilityController::__getOrCreateSite()).
     */
    function __getStoredRecommendations($websiteId, $userId) {
        $sql = "SELECT * FROM sp_recommendations
                WHERE website_id=$websiteId AND user_id=$userId
                ORDER BY FIELD(type,'error','warning','todo'), id ASC";
        return $this->db->select($sql);
    }

    /*
     * Return the timestamp of the last refresh for display.
     *
     * Was: MAX(refreshed_at) over sp_recommendations itself - that
     * returns NULL whenever the last refresh genuinely found zero
     * issues (a healthy site with no rank drops, no site-auditor
     * issues, etc. - a common, EXPECTED outcome, not an edge case),
     * which the view then couldn't tell apart from "never refreshed",
     * and rendered the literally false "No AI insights yet - click
     * Refresh" message immediately after a successful refresh that
     * found nothing wrong. sp_recommendations_refresh tracks the
     * refresh event itself, independent of how many rows (if any)
     * resulted from it.
     */
    private function __getLastRefreshedAt($websiteId, $userId) {
        $sql = "SELECT refreshed_at AS ts FROM sp_recommendations_refresh
                WHERE website_id=$websiteId AND user_id=$userId";
        $row = $this->db->select($sql, true);
        return !empty($row['ts']) ? $row['ts'] : null;
    }

    /*
     * Webmaster Tools recommendation: keywords on positions 11–29 with
     * meaningful impressions are just off page 1 — a high-value SEO opportunity.
     */
    private function __generateWebmasterRecommendations($websiteId, $userId) {

        $cutoff = date('Y-m-d', strtotime('-30 days'));

        // Aggregate last 30 days: sum impressions/clicks, average position and CTR
        // Only keep keywords whose 30-day avg position is 11–29 (page 2/3 opportunity)
        $sql = "SELECT k.name,
                       SUM(r.impressions)                        AS impressions,
                       SUM(r.clicks)                             AS clicks,
                       AVG(r.ctr)                                AS ctr,
                       AVG(r.average_position)                   AS average_position,
                       MIN(r.report_date)                        AS date_from,
                       MAX(r.report_date)                        AS date_to
                FROM webmaster_keywords k
                JOIN keyword_analytics r ON k.id = r.keyword_id
                WHERE k.website_id=$websiteId
                  AND k.status=1
                  AND r.source='google'
                  AND r.report_date >= '$cutoff'
                  AND r.impressions > 0
                GROUP BY k.id, k.name
                HAVING average_position > 10
                   AND average_position < 30
                ORDER BY impressions DESC
                LIMIT 20";

        $keywords = $this->db->select($sql);
        if (empty($keywords)) return;

        $now = date('Y-m-d H:i:s');

        foreach ($keywords as $kw) {
            $pos       = round($kw['average_position'], 1);
            $imp       = intval($kw['impressions']);
            $clicks    = intval($kw['clicks']);
            $ctr       = round($kw['ctr'] * 100, 2);
            $dateFrom  = $kw['date_from'];
            $dateTo    = $kw['date_to'];

            $title = addslashes("Keyword \"{$kw['name']}\" is ranking at avg position {$pos} — boost it to page 1");
            $desc  = addslashes(
                "Over the last 30 days ({$dateFrom} to {$dateTo}) this keyword accumulated {$imp} impressions " .
                "at an average position of {$pos} (page 2/3). " .
                "It received {$clicks} clicks (avg CTR: {$ctr}%). Focused on-page optimisation, " .
                "internal linking, and quality backlinks could push it onto page 1 and significantly increase traffic."
            );
            $meta = addslashes(json_encode(array(
                'keyword'          => $kw['name'],
                'average_position' => $pos,
                'impressions'      => $imp,
                'clicks'           => $clicks,
                'ctr'              => $ctr,
                'date_from'        => $dateFrom,
                'date_to'          => $dateTo,
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'webmaster_tools', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Resolve a website's Site Auditor project id, if one exists. Site
     * Auditor enforces one project per website (see
     * SiteAuditorController::isProjectExists()), so a plain LIMIT 1 is safe.
     */
    private function __getAuditorProjectId($websiteId) {
        $row = $this->db->select("SELECT id FROM auditorprojects WHERE website_id=$websiteId LIMIT 1", true);
        return !empty($row['id']) ? intval($row['id']) : 0;
    }

    /*
     * AI Overview recommendation: the site ranks reasonably well (top 20) for
     * a keyword, Google's AI Overview appears for that keyword's most
     * recently AI-Overview-checked date, but the site isn't cited in it.
     * aio_checked_at IS NOT NULL is the "was this row actually AIO-checked"
     * gate used throughout AIOverviewController.
     */
    private function __generateAIOverviewCitationRecommendations($websiteId, $userId) {

        $sql = "SELECT k.name AS keyword_name, se.domain AS se_domain, sr.rank, sr.aio_reference_count
                FROM searchresults sr
                JOIN (
                    SELECT keyword_id, searchengine_id, MAX(result_date) AS max_date
                    FROM searchresults
                    WHERE aio_checked_at IS NOT NULL
                    GROUP BY keyword_id, searchengine_id
                ) latest ON latest.keyword_id = sr.keyword_id
                        AND latest.searchengine_id = sr.searchengine_id
                        AND sr.result_date = latest.max_date
                JOIN keywords k ON k.id = sr.keyword_id
                JOIN searchengines se ON se.id = sr.searchengine_id
                WHERE k.website_id=$websiteId AND k.status=1
                  AND sr.aio_present=1 AND sr.aio_cited=0
                  AND sr.rank > 0 AND sr.rank <= 20
                ORDER BY sr.rank ASC
                LIMIT 20";

        $rows = $this->db->select($sql);
        if (empty($rows)) return;

        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            $keyword = $row['keyword_name'];
            $rank    = intval($row['rank']);
            $refs    = intval($row['aio_reference_count']);

            $title = addslashes("AI Overview appears for \"{$keyword}\" but you're not cited");
            $desc  = addslashes(
                "You rank #{$rank} on {$row['se_domain']} for \"{$keyword}\" and Google's AI Overview is showing " .
                "for this search (citing {$refs} other source" . ($refs == 1 ? '' : 's') . "), but your page isn't " .
                "one of them. Strengthening this page's authority and directly answering the query may help it " .
                "get cited."
            );
            $meta = addslashes(json_encode(array(
                'keyword'    => $keyword,
                'searchengine' => $row['se_domain'],
                'rank'       => $rank,
                'aio_reference_count' => $refs,
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'ai_overview', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * AI crawler recommendation: Site Auditor found pages whose own meta
     * tags block known AI bots (ai_robot_allowed=0, set in
     * WebsiteController::crawlMetaData() - a meta-tag check, distinct from
     * blocked_by_robots which reflects robots.txt).
     */
    private function __generateAIBotBlockedRecommendation($websiteId, $userId) {

        $projectId = $this->__getAuditorProjectId($websiteId);
        if (empty($projectId)) return;

        $row = $this->db->select("SELECT COUNT(*) AS cnt FROM auditorreports WHERE project_id=$projectId AND ai_robot_allowed=0", true);
        $count = intval($row['cnt'] ?? 0);
        if ($count <= 0) return;

        $now   = date('Y-m-d H:i:s');
        $title = addslashes("{$count} page" . ($count == 1 ? '' : 's') . " block AI crawlers via meta tags");
        $desc  = addslashes(
            "Site Auditor found {$count} page" . ($count == 1 ? '' : 's') . " with meta tags that block AI bots " .
            "(GPTBot, ClaudeBot, Google-Extended, PerplexityBot, and similar) from indexing them. If you want these " .
            "pages to be eligible for citation in AI-generated answers, remove those directives."
        );

        $meta = addslashes(json_encode(array('rule' => 'ai_bot_blocked')));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'error', 'ai_visibility', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * AI crawler recommendation: the site has installed the AI bot collector
     * script (a row exists in ai_visibility_sites) but no AI crawler has
     * been seen in 30+ days. Sites that never installed the collector have
     * no row at all and are silently skipped - that's a Setup-page nudge,
     * not a recommendation.
     */
    private function __generateAIBotSilentRecommendation($websiteId, $userId) {

        $site = $this->db->select("SELECT bot_last_seen_at FROM ai_visibility_sites WHERE website_id=$websiteId", true);
        if (empty($site)) return;

        $cutoff = date('Y-m-d H:i:s', strtotime('-30 days'));
        if (!empty($site['bot_last_seen_at']) && $site['bot_last_seen_at'] >= $cutoff) return;

        $now   = date('Y-m-d H:i:s');
        $title = "No AI crawler activity in the last 30 days";
        $desc  = !empty($site['bot_last_seen_at'])
            ? addslashes("The last AI crawler visit (GPTBot, ClaudeBot, PerplexityBot, etc.) recorded for this site was on {$site['bot_last_seen_at']}. This may mean AI platforms are indexing your content less often.")
            : "No AI crawler visit has been recorded for this site yet since the bot collector script was installed.";

        $meta = addslashes(json_encode(array('rule' => 'ai_bot_silent')));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'todo', 'ai_visibility', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * Rank drop recommendation: uses the panel's own rank tracker (works for
     * every user, unlike the Search-Console-only rule above). Compares each
     * tracked keyword's latest known rank against its rank from ~30 days
     * ago and flags meaningful regressions, ignoring keywords with less
     * than ~14 days of history between the two snapshots (avoids noise from
     * newly added keywords or a single fresh crawl).
     */
    private function __generateRankDropRecommendations($websiteId, $userId) {

        $keywords = $this->db->select("SELECT id, name FROM keywords WHERE website_id=$websiteId AND status=1");
        if (empty($keywords)) return;

        $keywordIds = implode(',', array_map(function($k) { return intval($k['id']); }, $keywords));
        $keywordNames = array();
        foreach ($keywords as $k) { $keywordNames[$k['id']] = $k['name']; }

        $cutoffDate = date('Y-m-d', strtotime('-30 days'));

        $latestSql = "SELECT sr.keyword_id, sr.searchengine_id, MIN(sr.rank) AS rank, sr.result_date
                      FROM searchresults sr
                      JOIN (
                          SELECT keyword_id, searchengine_id, MAX(result_date) AS max_date
                          FROM searchresults
                          WHERE keyword_id IN ($keywordIds)
                          GROUP BY keyword_id, searchengine_id
                      ) l ON l.keyword_id=sr.keyword_id AND l.searchengine_id=sr.searchengine_id AND sr.result_date=l.max_date
                      WHERE sr.keyword_id IN ($keywordIds)
                      GROUP BY sr.keyword_id, sr.searchengine_id, sr.result_date";
        $latestRows = $this->db->select($latestSql);
        if (empty($latestRows)) return;

        $baselineSql = "SELECT sr.keyword_id, sr.searchengine_id, MIN(sr.rank) AS rank, sr.result_date
                        FROM searchresults sr
                        JOIN (
                            SELECT keyword_id, searchengine_id, MAX(result_date) AS max_date
                            FROM searchresults
                            WHERE keyword_id IN ($keywordIds) AND result_date <= '$cutoffDate'
                            GROUP BY keyword_id, searchengine_id
                        ) l ON l.keyword_id=sr.keyword_id AND l.searchengine_id=sr.searchengine_id AND sr.result_date=l.max_date
                        WHERE sr.keyword_id IN ($keywordIds)
                        GROUP BY sr.keyword_id, sr.searchengine_id, sr.result_date";
        $baselineRows = $this->db->select($baselineSql);
        if (empty($baselineRows)) return;

        $baselineMap = array();
        foreach ($baselineRows as $b) {
            $baselineMap[$b['keyword_id'] . '-' . $b['searchengine_id']] = $b;
        }

        $seRows = $this->db->select("SELECT id, domain FROM searchengines");
        $seMap = array();
        foreach ($seRows as $se) { $seMap[$se['id']] = $se['domain']; }

        $flagged = array();
        foreach ($latestRows as $latest) {
            $key = $latest['keyword_id'] . '-' . $latest['searchengine_id'];
            if (empty($baselineMap[$key])) continue;
            $baseline = $baselineMap[$key];

            $gapDays = (strtotime($latest['result_date']) - strtotime($baseline['result_date'])) / 86400;
            if ($gapDays < 14) continue; // not enough real history between the two snapshots

            $baselineRank = intval($baseline['rank']);
            $latestRank   = intval($latest['rank']);

            $fellOffPageOne = ($baselineRank <= 10 && $latestRank > 10);
            $droppedALot    = (($latestRank - $baselineRank) >= 10);

            if ($fellOffPageOne || $droppedALot) {
                $flagged[] = array(
                    'keyword_id'    => $latest['keyword_id'],
                    'searchengine'  => !empty($seMap[$latest['searchengine_id']]) ? $seMap[$latest['searchengine_id']] : 'search engine',
                    'baseline_rank' => $baselineRank,
                    'baseline_date' => $baseline['result_date'],
                    'latest_rank'   => $latestRank,
                    'latest_date'   => $latest['result_date'],
                );
            }
        }

        if (empty($flagged)) return;

        usort($flagged, function($a, $b) {
            return ($b['latest_rank'] - $b['baseline_rank']) - ($a['latest_rank'] - $a['baseline_rank']);
        });
        $flagged = array_slice($flagged, 0, 20);

        $now = date('Y-m-d H:i:s');

        foreach ($flagged as $f) {
            $keyword = !empty($keywordNames[$f['keyword_id']]) ? $keywordNames[$f['keyword_id']] : 'keyword';

            $title = addslashes("Keyword \"{$keyword}\" dropped from position {$f['baseline_rank']} to {$f['latest_rank']}");
            $desc  = addslashes(
                "On {$f['searchengine']}, this keyword was at position {$f['baseline_rank']} on {$f['baseline_date']} " .
                "and has since dropped to position {$f['latest_rank']} (as of {$f['latest_date']}). Check for recent " .
                "content, technical, or backlink changes that might explain the drop."
            );
            $meta = addslashes(json_encode($f));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'error', 'rank_tracker', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Site Auditor quick wins: surfaces issue counts Site Auditor already
     * computes (see WebsiteController::crawlMetaData()) that aren't
     * otherwise front-and-center on the dashboard.
     */
    private function __generateSiteAuditorRecommendations($websiteId, $userId) {

        $projectId = $this->__getAuditorProjectId($websiteId);
        if (empty($projectId)) return;

        $now = date('Y-m-d H:i:s');

        $checks = array(
            array(
                'condition' => 'brocken=1',
                'type'      => 'warning',
                'rule'      => 'broken_links',
                'title'     => function($n) { return $n == 1 ? "1 broken link found" : "{$n} broken links found"; },
                'desc'      => function($n) { return "Site Auditor found {$n} broken " . ($n == 1 ? 'link' : 'links') . " on this website's most recently crawled pages."; },
            ),
            array(
                'condition' => 'https_secure=0',
                'type'      => 'warning',
                'rule'      => 'https_secure',
                'title'     => function($n) { return $n == 1 ? "1 page is not served over HTTPS" : "{$n} pages are not served over HTTPS"; },
                'desc'      => function($n) { return $n == 1 ? "Site Auditor found 1 page that is not served over HTTPS." : "Site Auditor found {$n} pages that are not served over HTTPS."; },
            ),
            array(
                'condition' => 'has_og_tags=0',
                'type'      => 'todo',
                'rule'      => 'og_tags',
                'title'     => function($n) { return $n == 1 ? "1 page is missing Open Graph tags" : "{$n} pages are missing Open Graph tags"; },
                'desc'      => function($n) { return $n == 1 ? "Site Auditor found 1 page missing Open Graph (og:) meta tags, which affects how it appears when shared on social media." : "Site Auditor found {$n} pages missing Open Graph (og:) meta tags, which affects how they appear when shared on social media."; },
            ),
            array(
                'condition' => 'has_structured_data=0',
                'type'      => 'todo',
                'rule'      => 'structured_data',
                'title'     => function($n) { return $n == 1 ? "1 page is missing structured data" : "{$n} pages are missing structured data"; },
                'desc'      => function($n) { return $n == 1 ? "Site Auditor found 1 page with no structured data (JSON-LD), which is what AI models like ChatGPT and Google's AI Overview read to understand what the page is actually about." : "Site Auditor found {$n} pages with no structured data (JSON-LD), which is what AI models like ChatGPT and Google's AI Overview read to understand what those pages are actually about."; },
            ),
        );

        foreach ($checks as $check) {
            $row = $this->db->select("SELECT COUNT(*) AS cnt FROM auditorreports WHERE project_id=$projectId AND {$check['condition']}", true);
            $count = intval($row['cnt'] ?? 0);
            if ($count <= 0) continue;

            $title = addslashes($check['title']($count));
            $desc  = addslashes($check['desc']($count));
            $meta  = addslashes(json_encode(array('rule' => $check['rule'])));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, '{$check['type']}', 'site_auditor', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    // Display names for AiPerceptionController's provider enum
    // ('openai'/'anthropic'/'google') - same mapping already used in
    // every AI Perception view (aiperception/settings.ctp.php,
    // tracking.ctp.php, check.ctp.php), repeated here rather than
    // shared since those are view-layer constants, not a controller one.
    private function __aiPerceptionProviderLabels() {
        return array(
            'openai'    => 'OpenAI (ChatGPT)',
            'anthropic' => 'Anthropic (Claude)',
            'google'    => 'Google (Gemini)',
        );
    }

    /*
     * AI Perception: did a provider stop mentioning this site, or did its
     * sentiment turn negative, between the two most recent checks for a
     * prompt? AiPerceptionController::TRACKING_INTERVAL_DAYS gates each
     * (prompt, provider) pair to at most one check per 7 days, so "latest
     * vs a fixed N-days-ago cutoff" (the rank-drop generator's approach)
     * doesn't fit well here - a slow week could mean zero checks in a
     * 7-day window. Comparing the two most recent checks instead, so this
     * always fires on a genuine change regardless of check cadence.
     * MySQL 5.7 here has no window functions, and grouping+keeping the
     * top 2 rows per (prompt_id, provider) in PHP is simpler than the
     * nested-derived-table SQL that would otherwise take to express
     * "second most recent row per group".
     */
    private function __generateAiPerceptionDropRecommendations($websiteId, $userId) {
        $prompts = $this->db->select("SELECT id, prompt_text FROM llm_perception_prompts WHERE website_id=$websiteId AND status=1");
        if (empty($prompts)) return;

        $promptIds = implode(',', array_map(function($p) { return intval($p['id']); }, $prompts));
        $promptTextMap = array();
        foreach ($prompts as $p) { $promptTextMap[$p['id']] = $p['prompt_text']; }

        $results = $this->db->select(
            "SELECT prompt_id, provider, checked_date, mentioned, sentiment
             FROM llm_perception_results
             WHERE prompt_id IN ($promptIds)
             ORDER BY prompt_id, provider, checked_date DESC"
        );
        if (empty($results)) return;

        // keep only the 2 most recent rows per (prompt_id, provider) -
        // already DESC-ordered by the query above
        $byGroup = array();
        foreach ($results as $r) {
            $key = $r['prompt_id'] . '|' . $r['provider'];
            if (!isset($byGroup[$key])) $byGroup[$key] = array();
            if (count($byGroup[$key]) < 2) $byGroup[$key][] = $r;
        }

        $providerLabels = $this->__aiPerceptionProviderLabels();
        $now = date('Y-m-d H:i:s');
        $flagged = array();

        foreach ($byGroup as $rows) {
            if (count($rows) < 2) continue; // need two checks to compare against
            list($latest, $prev) = $rows;

            $mentionLost = (!empty($prev['mentioned']) && empty($latest['mentioned']));
            $sentimentWorsened = (!$mentionLost
                && $prev['sentiment'] !== 'negative' && $latest['sentiment'] === 'negative');
            if (!$mentionLost && !$sentimentWorsened) continue;

            $flagged[] = array(
                'prompt_id'    => $latest['prompt_id'],
                'provider'     => $latest['provider'],
                'prev_date'    => $prev['checked_date'],
                'latest_date'  => $latest['checked_date'],
                'mention_lost' => $mentionLost,
            );
        }
        if (empty($flagged)) return;
        $flagged = array_slice($flagged, 0, 20);

        foreach ($flagged as $f) {
            $promptText    = !empty($promptTextMap[$f['prompt_id']]) ? $promptTextMap[$f['prompt_id']] : 'a tracked prompt';
            $providerLabel = $providerLabels[$f['provider']] ?? ucfirst($f['provider']);

            if ($f['mention_lost']) {
                $type  = 'error';
                $title = addslashes("{$providerLabel} stopped mentioning you for \"{$promptText}\"");
                $desc  = addslashes(
                    "On {$f['prev_date']} your site was mentioned when {$providerLabel} was asked this question. " .
                    "As of {$f['latest_date']}, it's no longer mentioned."
                );
                $rule = "ai_perception:{$f['prompt_id']}:{$f['provider']}:mention_lost";
            } else {
                $type  = 'warning';
                $title = addslashes("{$providerLabel}'s sentiment turned negative for \"{$promptText}\"");
                $desc  = addslashes(
                    "{$providerLabel}'s answer to this question turned negative between {$f['prev_date']} and " .
                    "{$f['latest_date']}. Open the AI Perception check to review the response."
                );
                $rule = "ai_perception:{$f['prompt_id']}:{$f['provider']}:sentiment_negative";
            }
            // 'rule' keyed by (prompt_id, provider, which condition fired) -
            // stable across refreshes even as prev_date/latest_date/title
            // shift day to day, unlike __recommendationIdentity()'s other
            // fallback (title) would be. Without this, every daily refresh
            // mints a "new" row for the same ongoing issue (confirmed via
            // a dedicated review pass) and re-triggers the daily digest
            // email every day instead of just once.
            $f['rule'] = $rule;
            $meta = addslashes(json_encode($f));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, '$type', 'ai_perception', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * AI Perception: a tracked competitor is mentioned for a prompt this
     * site is NOT mentioned for, on the same check (same prompt,
     * provider, checked_date) - a direct, named competitive loss, the
     * single most actionable signal the whole LLM Perception feature can
     * produce. Single-date, no history needed.
     */
    private function __generateAiPerceptionCompetitorRecommendations($websiteId, $userId) {
        $competitors = $this->db->select("SELECT id, name FROM llm_perception_competitors WHERE website_id=$websiteId AND status=1");
        if (empty($competitors)) return;
        $competitorNameMap = array();
        foreach ($competitors as $c) { $competitorNameMap[$c['id']] = $c['name']; }

        $prompts = $this->db->select("SELECT id, prompt_text FROM llm_perception_prompts WHERE website_id=$websiteId AND status=1");
        if (empty($prompts)) return;
        $promptIds = implode(',', array_map(function($p) { return intval($p['id']); }, $prompts));
        $promptTextMap = array();
        foreach ($prompts as $p) { $promptTextMap[$p['id']] = $p['prompt_text']; }

        $sql = "SELECT r.prompt_id, r.provider, r.checked_date, cr.competitor_id
                FROM llm_perception_results r
                JOIN (
                    SELECT prompt_id, provider, MAX(checked_date) AS max_date
                    FROM llm_perception_results
                    WHERE prompt_id IN ($promptIds)
                    GROUP BY prompt_id, provider
                ) l ON l.prompt_id=r.prompt_id AND l.provider=r.provider AND l.max_date=r.checked_date
                JOIN llm_perception_competitor_results cr
                    ON cr.prompt_id=r.prompt_id AND cr.provider=r.provider
                   AND cr.checked_date=r.checked_date AND cr.mentioned=1
                WHERE r.mentioned=0";
        $rows = $this->db->select($sql);
        if (empty($rows)) return;
        $rows = array_slice($rows, 0, 20);

        $providerLabels = $this->__aiPerceptionProviderLabels();
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $r) {
            $promptText     = !empty($promptTextMap[$r['prompt_id']]) ? $promptTextMap[$r['prompt_id']] : 'a tracked prompt';
            $competitorName = !empty($competitorNameMap[$r['competitor_id']]) ? $competitorNameMap[$r['competitor_id']] : 'A tracked competitor';
            $providerLabel  = $providerLabels[$r['provider']] ?? ucfirst($r['provider']);

            $title = addslashes("{$competitorName} is being recommended by {$providerLabel} instead of you");
            $desc  = addslashes(
                "For the prompt \"{$promptText}\", {$providerLabel} mentioned {$competitorName} but not you, " .
                "as of {$r['checked_date']}."
            );
            // stable identity independent of checked_date, which changes
            // every refresh - see the matching comment in
            // __generateAiPerceptionDropRecommendations() for why this
            // matters (anti-spam diffing, not just dedup on this one run).
            $r['rule'] = "ai_perception_competitor:{$r['prompt_id']}:{$r['provider']}:{$r['competitor_id']}";
            $meta = addslashes(json_encode($r));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'error', 'ai_perception', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Backlinks Checker: external_pages_to_root_domain fell between the
     * latest check and a ~30-day-old baseline. Same latest-vs-baseline
     * shape as __generateRankDropRecommendations(), one row per website
     * instead of per keyword. Percentage alone is noisy for small
     * backlink counts, so a site with under 20 baseline backlinks flags
     * on ANY drop rather than needing to clear the 15% bar.
     */
    private function __generateBacklinkDropRecommendations($websiteId, $userId) {
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));

        $latest = $this->db->select(
            "SELECT external_pages_to_root_domain, result_date FROM backlinkresults
             WHERE website_id=$websiteId ORDER BY result_date DESC LIMIT 1", true
        );
        if (empty($latest)) return;

        $baseline = $this->db->select(
            "SELECT external_pages_to_root_domain, result_date FROM backlinkresults
             WHERE website_id=$websiteId AND result_date <= '$cutoffDate'
             ORDER BY result_date DESC LIMIT 1", true
        );
        if (empty($baseline)) return;

        $gapDays = (strtotime($latest['result_date']) - strtotime($baseline['result_date'])) / 86400;
        if ($gapDays < 14) return;

        $baselineCount = intval($baseline['external_pages_to_root_domain']);
        $latestCount   = intval($latest['external_pages_to_root_domain']);
        // A previously-active site dropping to LITERALLY 0 backlinks is
        // far more likely a failed/incomplete crawl than genuine total
        // loss - confirmed live in this dev environment's own data
        // (external checks going silently to 0 after a certain date,
        // same pattern seen in reviews/social/PageSpeed below). Treat as
        // no reliable data rather than a real drop to avoid false alarms.
        if ($baselineCount <= 0 || $latestCount <= 0 || $latestCount >= $baselineCount) return;

        $dropPct = round((($baselineCount - $latestCount) / $baselineCount) * 100, 1);
        if ($baselineCount >= 20 && $dropPct < 15) return;

        $now = date('Y-m-d H:i:s');
        $title = addslashes("Backlinks dropped {$dropPct}% in the last 30 days");
        $desc  = addslashes(
            "This website had {$baselineCount} referring pages to its root domain on {$baseline['result_date']}; " .
            "now at {$latestCount} as of {$latest['result_date']}. Review recently lost or removed backlinks."
        );
        $meta = addslashes(json_encode(array(
            'rule' => 'backlink_drop',
            'baseline_count' => $baselineCount, 'latest_count' => $latestCount,
            'baseline_date'  => $baseline['result_date'], 'latest_date' => $latest['result_date'],
            'drop_pct'       => $dropPct,
        )));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'warning', 'backlink_checker', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * Review Manager: average rating dropped at least 0.3 stars between
     * the latest check and a ~30-day-old baseline, per review link (one
     * website can track Google/Yelp/TripAdvisor/etc. separately). Per-
     * link, not aggregated, since a drop on one platform shouldn't be
     * diluted by other platforms being stable.
     */
    private function __generateReviewDropRecommendations($websiteId, $userId) {
        $links = $this->db->select("SELECT id, name, type FROM review_links WHERE website_id=$websiteId AND status=1");
        if (empty($links)) return;

        $cutoffDate = date('Y-m-d', strtotime('-30 days'));
        $now = date('Y-m-d H:i:s');

        foreach ($links as $link) {
            $latest = $this->db->select(
                "SELECT rating, reviews, report_date FROM review_link_results
                 WHERE review_link_id={$link['id']} ORDER BY report_date DESC LIMIT 1", true
            );
            if (empty($latest)) continue;

            $baseline = $this->db->select(
                "SELECT rating, reviews, report_date FROM review_link_results
                 WHERE review_link_id={$link['id']} AND report_date <= '$cutoffDate'
                 ORDER BY report_date DESC LIMIT 1", true
            );
            if (empty($baseline)) continue;

            $gapDays = (strtotime($latest['report_date']) - strtotime($baseline['report_date'])) / 86400;
            if ($gapDays < 14) continue;

            // reviews=0 on the latest check almost always means the crawl
            // failed to fetch the page, not that every review vanished -
            // confirmed live (this dev environment's own data goes
            // reviews=0/rating=0 after a certain date, consistent with
            // backlinks/social/PageSpeed hitting the same failure mode).
            if (intval($latest['reviews']) <= 0) continue;

            $baselineRating = floatval($baseline['rating']);
            $latestRating   = floatval($latest['rating']);
            $ratingDrop     = round($baselineRating - $latestRating, 2);
            if ($ratingDrop < 0.3) continue;

            $title = addslashes("{$link['name']} rating dropped from {$baselineRating} to {$latestRating}");
            $desc  = addslashes(
                "Your average rating on {$link['name']} fell over the last 30 days " .
                "({$baseline['report_date']} to {$latest['report_date']}). New negative reviews may need a response."
            );
            $meta = addslashes(json_encode(array(
                'rule' => "review_drop:{$link['id']}",
                'link_id' => $link['id'], 'baseline_rating' => $baselineRating, 'latest_rating' => $latestRating,
                'baseline_date' => $baseline['report_date'], 'latest_date' => $latest['report_date'],
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'review_manager', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Google Analytics: sitewide sessions down 20%+ week-over-week. Sits
     * alongside webmaster_tools' per-keyword opportunity rows but reads a
     * different table (website_analytics, GA's own sitewide numbers, not
     * keyword_analytics' per-keyword GSC data) - catches a broad traffic
     * problem that per-keyword tracking might miss entirely if the drop
     * is concentrated in untracked keywords/pages.
     */
    private function __generateAnalyticsDropRecommendations($websiteId, $userId) {
        $recentCutoff = date('Y-m-d', strtotime('-7 days'));
        $priorCutoff  = date('Y-m-d', strtotime('-14 days'));

        $recent = $this->db->select(
            "SELECT SUM(sessions) AS sessions, SUM(goalCompletionsAll) AS goals FROM website_analytics
             WHERE website_id=$websiteId AND report_date >= '$recentCutoff'", true
        );
        $prior = $this->db->select(
            "SELECT SUM(sessions) AS sessions, SUM(goalCompletionsAll) AS goals FROM website_analytics
             WHERE website_id=$websiteId AND report_date >= '$priorCutoff' AND report_date < '$recentCutoff'", true
        );
        if (empty($recent) || empty($prior)) return;

        $priorSessions = intval($prior['sessions']);
        // week-to-week traffic naturally swings 20%+ on a low-traffic
        // site from weekday/weekend mix or one lost referral source
        // alone - a floor of 10 sessions/week was too low to filter that
        // noise out (confirmed via a dedicated review pass), so this is
        // meaningfully higher than the other generators' sample floors.
        if ($priorSessions < 50) return;

        $recentSessions = intval($recent['sessions']);
        $dropPct = round((($priorSessions - $recentSessions) / $priorSessions) * 100, 1);
        if ($dropPct < 20) return;

        $recentGoals = intval($recent['goals']);
        $priorGoals  = intval($prior['goals']);

        $now = date('Y-m-d H:i:s');
        $title = addslashes("Sessions down {$dropPct}% week-over-week");
        $desc  = addslashes(
            "This website had {$recentSessions} sessions in the last 7 days vs {$priorSessions} the week before" .
            ($priorGoals > 0 ? ", with goal completions down from {$priorGoals} to {$recentGoals}" : "") . "."
        );
        $meta = addslashes(json_encode(array(
            'rule' => 'ga_sessions_drop',
            'recent_sessions' => $recentSessions, 'prior_sessions' => $priorSessions,
            'recent_goals' => $recentGoals, 'prior_goals' => $priorGoals, 'drop_pct' => $dropPct,
        )));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'warning', 'web_analytics', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * Search Console: sitewide impressions down 20%+ week-over-week, per
     * source (google/bing/yandex/etc. - website_search_analytics.source).
     * Distinct from webmaster_tools' per-keyword opportunity rows the
     * same way __generateAnalyticsDropRecommendations() is - catches a
     * broad de-indexing/algorithm-update scenario that individual
     * keyword tracking alone might not surface quickly.
     */
    private function __generateSearchConsoleDropRecommendations($websiteId, $userId) {
        $sources = $this->db->select("SELECT DISTINCT source FROM website_search_analytics WHERE website_id=$websiteId");
        if (empty($sources)) return;

        $recentCutoff = date('Y-m-d', strtotime('-7 days'));
        $priorCutoff  = date('Y-m-d', strtotime('-14 days'));
        $now = date('Y-m-d H:i:s');

        foreach ($sources as $s) {
            // source is an ENUM('google','yahoo','bing','baidu','yandex')
            // at the schema level, so this can never actually carry a
            // quote today - addslashes() anyway, matching how the one
            // existing writer of this same column
            // (WebmasterController::insertWebsiteAnalytics()) already
            // treats it, rather than relying on the enum constraint
            // holding forever (confirmed via a dedicated review pass).
            $source = addslashes($s['source']);
            $recent = $this->db->select(
                "SELECT SUM(clicks) AS clicks, SUM(impressions) AS impressions FROM website_search_analytics
                 WHERE website_id=$websiteId AND source='$source' AND report_date >= '$recentCutoff'", true
            );
            $prior = $this->db->select(
                "SELECT SUM(clicks) AS clicks, SUM(impressions) AS impressions FROM website_search_analytics
                 WHERE website_id=$websiteId AND source='$source' AND report_date >= '$priorCutoff' AND report_date < '$recentCutoff'", true
            );
            if (empty($recent) || empty($prior)) continue;

            $priorImpressions = intval($prior['impressions']);
            // same noise concern as __generateAnalyticsDropRecommendations()'s
            // sessions floor - 50 impressions/week is still thin, bumped
            // to 200 so a 20% swing means something.
            if ($priorImpressions < 200) continue;

            $recentImpressions = intval($recent['impressions']);
            $dropPct = round((($priorImpressions - $recentImpressions) / $priorImpressions) * 100, 1);
            if ($dropPct < 20) continue;

            $sourceLabel = ucfirst($source);
            $title = addslashes("Search Console impressions down {$dropPct}% on {$sourceLabel}");
            $desc  = addslashes(
                "Impressions on {$sourceLabel} fell from {$priorImpressions} to {$recentImpressions} over the last " .
                "7 days compared to the week before. This can signal a ranking drop, a de-indexing issue, or reduced search interest."
            );
            $meta = addslashes(json_encode(array(
                'rule' => "search_console_drop:{$source}",
                'source' => $source, 'recent_impressions' => $recentImpressions,
                'prior_impressions' => $priorImpressions, 'drop_pct' => $dropPct,
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'search_console', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * PageSpeed Insights: mobile or desktop score dropped 15+ points
     * between the two most recent checks. Two-point comparison (no fixed
     * baseline window needed) since PageSpeed checks don't run on a
     * predictable daily cadence.
     */
    private function __generatePageSpeedRegressionRecommendations($websiteId, $userId) {
        $rows = $this->db->select(
            "SELECT desktop_speed_score, mobile_speed_score, result_date FROM pagespeedresults
             WHERE website_id=$websiteId ORDER BY result_date DESC LIMIT 2"
        );
        if (count($rows) < 2) return;
        list($latest, $prev) = $rows;

        $now = date('Y-m-d H:i:s');
        $checks = array(
            array('field' => 'mobile_speed_score',  'label' => 'Mobile'),
            array('field' => 'desktop_speed_score', 'label' => 'Desktop'),
        );

        foreach ($checks as $c) {
            $prevScore   = intval($prev[$c['field']]);
            $latestScore = intval($latest[$c['field']]);
            // a score of exactly 0 is not a realistic organic PageSpeed
            // result for a real page - almost always a failed API call,
            // same failure mode confirmed live for backlinks/reviews/
            // social above, not a genuine total performance collapse.
            if ($latestScore <= 0) continue;
            $drop = $prevScore - $latestScore;
            if ($drop < 15) continue;

            $title = addslashes("{$c['label']} PageSpeed score dropped from {$prevScore} to {$latestScore}");
            $desc  = addslashes(
                "A recent deploy or added script may be slowing the site down (checked {$prev['result_date']} vs " .
                "{$latest['result_date']}). Check Core Web Vitals in the PageSpeed Insights tool for details."
            );
            $meta = addslashes(json_encode(array(
                'rule' => "pagespeed_regression:{$c['field']}",
                'metric' => $c['field'], 'prev_score' => $prevScore, 'latest_score' => $latestScore,
                'prev_date' => $prev['result_date'], 'latest_date' => $latest['result_date'],
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'pagespeed', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Social Media Checker: follower count dropped, per tracked link.
     * Follower counts rarely fall on their own, so even a modest drop
     * (10+) is worth a look - usually tied to a specific event (platform
     * purge, content controversy) rather than gradual churn.
     */
    private function __generateSocialFollowerRecommendations($websiteId, $userId) {
        $links = $this->db->select("SELECT id, name, type FROM social_media_links WHERE website_id=$websiteId AND status=1");
        if (empty($links)) return;

        $cutoffDate = date('Y-m-d', strtotime('-30 days'));
        $now = date('Y-m-d H:i:s');

        foreach ($links as $link) {
            $latest = $this->db->select(
                "SELECT followers, report_date FROM social_media_link_results
                 WHERE sm_link_id={$link['id']} ORDER BY report_date DESC LIMIT 1", true
            );
            if (empty($latest)) continue;

            $baseline = $this->db->select(
                "SELECT followers, report_date FROM social_media_link_results
                 WHERE sm_link_id={$link['id']} AND report_date <= '$cutoffDate'
                 ORDER BY report_date DESC LIMIT 1", true
            );
            if (empty($baseline)) continue;

            $gapDays = (strtotime($latest['report_date']) - strtotime($baseline['report_date'])) / 86400;
            if ($gapDays < 14) continue;

            $baselineFollowers = intval($baseline['followers']);
            $latestFollowers   = intval($latest['followers']);
            // followers=0 on the latest check almost always means the
            // crawl failed, not that every follower vanished - same
            // failure mode confirmed live for backlinks/reviews/PageSpeed.
            if ($latestFollowers <= 0) continue;
            $lost = $baselineFollowers - $latestFollowers;
            if ($lost < 10) continue;

            $platform = ucfirst($link['type']);
            $title = addslashes("{$platform} followers dropped by {$lost}");
            $desc  = addslashes(
                "{$platform} ({$link['name']}) had {$baselineFollowers} followers on {$baseline['report_date']}, " .
                "now at {$latestFollowers} as of {$latest['report_date']}."
            );
            $meta = addslashes(json_encode(array(
                'rule' => "social_follower_drop:{$link['id']}",
                'link_id' => $link['id'], 'platform' => $link['type'],
                'baseline_followers' => $baselineFollowers, 'latest_followers' => $latestFollowers, 'lost' => $lost,
            )));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'warning', 'social_media', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Scheduler reliability: a specific cron tool (url_section) failed 3
     * or more of its last 5 runs for this website - the data it feeds
     * may be silently stale. Reuses cron_job_timing, built for the
     * Scheduler Health page earlier this session; requires at least 5
     * recorded runs for that section before judging it (a brand-new
     * install won't have enough history yet).
     */
    private function __generateCronReliabilityRecommendations($websiteId, $userId) {
        $sections = $this->db->select("SELECT DISTINCT url_section FROM cron_job_timing WHERE website_id=$websiteId");
        if (empty($sections)) return;

        $now = date('Y-m-d H:i:s');
        foreach ($sections as $s) {
            $section = addslashes($s['url_section']);
            // one query per section, not a single global LIMIT 200 then
            // group-in-PHP as this started out - a high-frequency section
            // (e.g. keyword-position-checker, run many times per cron
            // pass via the chunked job queue) could fill that whole
            // window and crowd a low-frequency section's own last-5-runs
            // out of it entirely, silently hiding a genuinely failing
            // rare tool (confirmed via a dedicated review pass). Realistic
            // section counts are small (~10), so this stays cheap.
            $runs = $this->db->select(
                "SELECT status, error_message FROM cron_job_timing
                 WHERE website_id=$websiteId AND url_section='$section'
                 ORDER BY started_at DESC LIMIT 5"
            );
            if (count($runs) < 5) continue;

            $failures = 0;
            $lastError = '';
            foreach ($runs as $r) {
                if ($r['status'] === 'failed') {
                    $failures++;
                    if (empty($lastError) && !empty($r['error_message'])) $lastError = $r['error_message'];
                }
            }
            if ($failures < 3) continue;

            $sectionLabel = ucwords(str_replace(array('-', '_'), ' ', $section));
            $title = addslashes("{$sectionLabel} has failed {$failures} of the last 5 cron runs");
            $desc  = addslashes(
                "This tool's scheduled runs are failing repeatedly" . (!empty($lastError) ? ". Last error: {$lastError}" : "") .
                ". Its data may be stale until this is fixed."
            );
            $meta = addslashes(json_encode(array('rule' => "cron_reliability:{$section}", 'url_section' => $section, 'failures' => $failures, 'last_error' => $lastError)));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'error', 'scheduler_health', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Scheduler reliability: job_queue chunks that have exhausted their
     * retry attempts and permanently failed (status='failed' AND
     * attempts >= max_attempts, not just "retrying") - catches silent
     * partial failure in the resumable chunked scheduler that otherwise
     * has no user-facing surfacing anywhere today.
     */
    private function __generateJobQueueFailureRecommendations($websiteId, $userId) {
        $rows = $this->db->select(
            "SELECT url_section, COUNT(*) AS cnt, MAX(last_error) AS last_error FROM job_queue
             WHERE website_id=$websiteId AND status='failed' AND attempts >= max_attempts
             GROUP BY url_section"
        );
        if (empty($rows)) return;

        $now = date('Y-m-d H:i:s');
        foreach ($rows as $r) {
            $sectionLabel = ucwords(str_replace(array('-', '_'), ' ', $r['url_section']));
            $count = intval($r['cnt']);
            $title = addslashes(
                $count == 1 ? "A {$sectionLabel} job chunk has permanently failed" : "{$count} {$sectionLabel} job chunks have permanently failed"
            );
            $desc = addslashes(
                "These chunks have exhausted their retry attempts and will not complete" .
                (!empty($r['last_error']) ? ". Last error: {$r['last_error']}" : "") .
                ". This part of the data will remain incomplete until resolved."
            );
            $meta = addslashes(json_encode(array('rule' => "job_queue_failure:{$r['url_section']}", 'url_section' => $r['url_section'], 'count' => $count, 'last_error' => $r['last_error'])));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, 'error', 'scheduler_health', '$title', '$desc', '$meta', '$now')"
            );
        }
    }

    /*
     * Directory Submission: a meaningful share of this website's
     * submitted directory listings have since gone inactive (directories
     * table's own 'working' flag flips dirsubmitinfo.active off when a
     * recheck finds the listing gone). This feature is "set and forget"
     * for most users, so decay is otherwise invisible. dirsubmitinfo has
     * no history column (submit_time is the only timestamp, set once at
     * submission) - this is a current-state ratio check, not a true
     * before/after trend like every other generator above.
     */
    private function __generateDirectorySubmissionDecayRecommendations($websiteId, $userId) {
        $row = $this->db->select(
            "SELECT COUNT(*) AS total, SUM(CASE WHEN active=0 THEN 1 ELSE 0 END) AS inactive
             FROM dirsubmitinfo WHERE website_id=$websiteId AND status=1", true
        );
        if (empty($row)) return;

        $total = intval($row['total']);
        if ($total < 5) return; // too small a sample for a ratio to mean anything

        $inactive = intval($row['inactive']);
        $pct = round(($inactive / $total) * 100, 1);
        if ($pct < 25) return;

        $now = date('Y-m-d H:i:s');
        $title = addslashes("{$inactive} of your {$total} directory submissions are no longer active");
        $desc  = addslashes("These backlinks may have been lost. Consider re-submitting or replacing them with active directories.");
        $meta  = addslashes(json_encode(array('rule' => 'directory_decay', 'total' => $total, 'inactive' => $inactive, 'pct' => $pct)));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'todo', 'directory_submission', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * Keyword opportunity: a tracked keyword with high search volume and
     * low difficulty (per DataForSEO's keyword_search_volume) that isn't
     * ranking well, paired with proof this site CAN compete (another
     * tracked keyword it already ranks top 10 for). Weakest generator
     * here by design - keyword_search_volume has a UNIQUE KEY
     * (keyword_id, source), so it's overwritten on every crawl with no
     * history, and there's no "related keyword" concept in the schema,
     * so this is necessarily a current-state snapshot comparison across
     * this website's own tracked keywords, not a true opportunity-mining
     * feature. Still useful as occasional, honest signal - just not a
     * trend like the generators above it.
     */
    private function __generateSearchVolumeMismatchRecommendations($websiteId, $userId) {
        $keywords = $this->db->select("SELECT id, name FROM keywords WHERE website_id=$websiteId AND status=1");
        if (count($keywords) < 2) return;

        $keywordIds = implode(',', array_map(function($k) { return intval($k['id']); }, $keywords));
        $nameMap = array();
        foreach ($keywords as $k) { $nameMap[$k['id']] = $k['name']; }

        $rankSql = "SELECT sr.keyword_id, MIN(sr.rank) AS best_rank
                    FROM searchresults sr
                    JOIN (
                        SELECT keyword_id, MAX(result_date) AS max_date
                        FROM searchresults WHERE keyword_id IN ($keywordIds)
                        GROUP BY keyword_id
                    ) l ON l.keyword_id=sr.keyword_id AND l.max_date=sr.result_date
                    GROUP BY sr.keyword_id";
        $rankMap = array();
        foreach ($this->db->select($rankSql) as $r) { $rankMap[$r['keyword_id']] = intval($r['best_rank']); }

        $svRows = $this->db->select(
            "SELECT keyword_id, search_volume, keyword_difficulty FROM keyword_search_volume
             WHERE keyword_id IN ($keywordIds) AND source='google'"
        );
        if (empty($svRows)) return;

        $bestCandidate = null;
        foreach ($svRows as $sv) {
            $volume = intval($sv['search_volume']);
            $difficulty = floatval($sv['keyword_difficulty']);
            if ($volume < 500 || $difficulty > 30) continue; // only high-volume, easy terms

            $currentRank = isset($rankMap[$sv['keyword_id']]) ? $rankMap[$sv['keyword_id']] : null;
            if ($currentRank !== null && $currentRank <= 20) continue; // already doing fine

            if ($bestCandidate === null || $volume > $bestCandidate['volume']) {
                $bestCandidate = array(
                    'keyword_id' => $sv['keyword_id'], 'volume' => $volume,
                    'difficulty' => $difficulty, 'rank' => $currentRank,
                );
            }
        }
        if ($bestCandidate === null) return;

        $provenKeywordId = null;
        foreach ($rankMap as $kwId => $rank) {
            if ($rank <= 10 && $kwId != $bestCandidate['keyword_id']) { $provenKeywordId = $kwId; break; }
        }

        $candidateName = !empty($nameMap[$bestCandidate['keyword_id']]) ? $nameMap[$bestCandidate['keyword_id']] : 'a tracked keyword';
        $volume = $bestCandidate['volume'];
        $difficulty = round($bestCandidate['difficulty']);
        $rankClause = ($bestCandidate['rank'] !== null)
            ? "is only ranking at position {$bestCandidate['rank']}"
            : "isn't being tracked for rank yet";

        if ($provenKeywordId !== null) {
            $provenName = $nameMap[$provenKeywordId];
            $provenRank = $rankMap[$provenKeywordId];
            $title = addslashes("Untapped opportunity: \"{$candidateName}\" ({$volume}/mo searches, easy to rank)");
            $desc  = addslashes(
                "You already rank #{$provenRank} for \"{$provenName}\", proving you can compete in this space. " .
                "\"{$candidateName}\" gets an estimated {$volume} searches/month with low difficulty ({$difficulty}/100) but {$rankClause}. Worth targeting."
            );
        } else {
            $title = addslashes("Untapped keyword opportunity: \"{$candidateName}\"");
            $desc  = addslashes(
                "\"{$candidateName}\" gets an estimated {$volume} searches/month with low difficulty ({$difficulty}/100), " .
                "but {$rankClause}. Worth targeting with dedicated content."
            );
        }
        $bestCandidate['rule'] = "keyword_opportunity:{$bestCandidate['keyword_id']}";
        $meta = addslashes(json_encode($bestCandidate));

        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'todo', 'keyword_opportunity', '$title', '$desc', '$meta', '$now')"
        );
    }

    /*
     * Cross-tool correlation: this refresh pass also flagged keyword rank
     * drops AND Site Auditor currently shows issues on this website - the
     * one insight no single existing report can produce on its own, since
     * it ties two generators' output together. Reads sp_recommendations
     * rows THIS SAME refreshRecommendationsForWebsite() run already
     * inserted (rank_tracker, site_auditor), so this must run after both
     * in that method's call order. auditorreports has no per-issue
     * history (see __generateSiteAuditorRecommendations()'s own table),
     * so this deliberately says "may be related" / "worth checking",
     * not "caused by" - it's correlating two things that are both true
     * right now, not proving a new issue appeared at the same time as
     * the drop.
     */
    private function __generateRankDropAuditorCorrelationRecommendations($websiteId, $userId) {
        $rankDropRow = $this->db->select(
            "SELECT COUNT(*) AS cnt FROM sp_recommendations
             WHERE website_id=$websiteId AND user_id=$userId AND category='rank_tracker'", true
        );
        $rankDropN = !empty($rankDropRow) ? intval($rankDropRow['cnt']) : 0;
        if ($rankDropN == 0) return;

        $auditorIssues = $this->db->select(
            "SELECT title FROM sp_recommendations
             WHERE website_id=$websiteId AND user_id=$userId AND category='site_auditor'"
        );
        if (empty($auditorIssues)) return;

        $issueCount = count($auditorIssues);
        $issueTitles = implode('; ', array_map(function($i) { return $i['title']; }, array_slice($auditorIssues, 0, 3)));

        $now = date('Y-m-d H:i:s');
        $title = addslashes("Rank drops and site auditor issues may be related");
        $desc  = addslashes(
            ($rankDropN == 1 ? "1 keyword dropped" : "{$rankDropN} keywords dropped") . " recently, and Site Auditor " .
            "currently flags " . ($issueCount == 1 ? "1 issue" : "{$issueCount} issues") . " on this website " .
            "({$issueTitles}" . ($issueCount > 3 ? ', ...' : '') . "). Technical issues can suppress rankings even when " .
            "content hasn't changed - worth checking whether these are connected."
        );
        $meta = addslashes(json_encode(array('rule' => 'cross_tool', 'rank_drop_count' => $rankDropN, 'auditor_issue_count' => $issueCount)));

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'warning', 'cross_tool', '$title', '$desc', '$meta', '$now')"
        );
    }
}
