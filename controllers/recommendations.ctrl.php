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

        $this->render('dashboard/recommendations_main');
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

        if (!empty($websiteId)) {
            $this->refreshRecommendationsForWebsite($websiteId, $userId);
        }

        $this->showRecommendationsDashboard($data);
    }

    /*
     * Core generation logic, independent of any HTTP session - callable from
     * the AJAX-facing refreshRecommendations() above, or from a cron context
     * (CronController::refreshAllAIInsights()) that already knows the
     * website's owning user_id.
     */
    function refreshRecommendationsForWebsite($websiteId, $userId) {

        // Clear old recommendations for this website / user
        $this->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");

        // Generate and persist each recommendation set
        $this->__generateWebmasterRecommendations($websiteId, $userId);
        $this->__generateAIOverviewCitationRecommendations($websiteId, $userId);
        $this->__generateAIBotBlockedRecommendation($websiteId, $userId);
        $this->__generateAIBotSilentRecommendation($websiteId, $userId);
        $this->__generateRankDropRecommendations($websiteId, $userId);
        $this->__generateSiteAuditorRecommendations($websiteId, $userId);
    }

    /*
     * Return recommendations stored in DB for a website.
     */
    private function __getStoredRecommendations($websiteId, $userId) {
        $sql = "SELECT * FROM sp_recommendations
                WHERE website_id=$websiteId AND user_id=$userId
                ORDER BY FIELD(type,'error','warning','todo'), id ASC";
        return $this->db->select($sql);
    }

    /*
     * Return the timestamp of the last refresh for display.
     */
    private function __getLastRefreshedAt($websiteId, $userId) {
        $sql = "SELECT MAX(refreshed_at) AS ts FROM sp_recommendations
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

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'error', 'ai_visibility', '$title', '$desc', NULL, '$now')"
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

        $this->db->query(
            "INSERT INTO sp_recommendations
                (website_id, user_id, type, category, title, description, meta, refreshed_at)
             VALUES
                ($websiteId, $userId, 'todo', 'ai_visibility', '$title', '$desc', NULL, '$now')"
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
                'title'     => function($n) { return $n == 1 ? "1 broken link found" : "{$n} broken links found"; },
                'desc'      => function($n) { return "Site Auditor found {$n} broken " . ($n == 1 ? 'link' : 'links') . " on this website's most recently crawled pages."; },
            ),
            array(
                'condition' => 'https_secure=0',
                'type'      => 'warning',
                'title'     => function($n) { return $n == 1 ? "1 page is not served over HTTPS" : "{$n} pages are not served over HTTPS"; },
                'desc'      => function($n) { return $n == 1 ? "Site Auditor found 1 page that is not served over HTTPS." : "Site Auditor found {$n} pages that are not served over HTTPS."; },
            ),
            array(
                'condition' => 'has_og_tags=0',
                'type'      => 'todo',
                'title'     => function($n) { return $n == 1 ? "1 page is missing Open Graph tags" : "{$n} pages are missing Open Graph tags"; },
                'desc'      => function($n) { return $n == 1 ? "Site Auditor found 1 page missing Open Graph (og:) meta tags, which affects how it appears when shared on social media." : "Site Auditor found {$n} pages missing Open Graph (og:) meta tags, which affects how they appear when shared on social media."; },
            ),
        );

        foreach ($checks as $check) {
            $row = $this->db->select("SELECT COUNT(*) AS cnt FROM auditorreports WHERE project_id=$projectId AND {$check['condition']}", true);
            $count = intval($row['cnt'] ?? 0);
            if ($count <= 0) continue;

            $title = addslashes($check['title']($count));
            $desc  = addslashes($check['desc']($count));

            $this->db->query(
                "INSERT INTO sp_recommendations
                    (website_id, user_id, type, category, title, description, meta, refreshed_at)
                 VALUES
                    ($websiteId, $userId, '{$check['type']}', 'site_auditor', '$title', '$desc', NULL, '$now')"
            );
        }
    }
}
