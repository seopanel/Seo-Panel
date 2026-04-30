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
     */
    function refreshRecommendations($data) {

        $userId    = isLoggedIn();
        $websiteId = !empty($data['website_id']) ? intval($data['website_id']) : 0;

        if (!empty($websiteId)) {
            // Clear old recommendations for this website / user
            $this->db->query("DELETE FROM sp_recommendations WHERE website_id=$websiteId AND user_id=$userId");

            // Generate and persist each recommendation set
            $this->__generateWebmasterRecommendations($websiteId, $userId);
        }

        $this->showRecommendationsDashboard($data);
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
}
