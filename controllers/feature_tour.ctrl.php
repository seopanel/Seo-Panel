<?php
/**
 * Setup Tour controller - a short, skippable walkthrough shown once to
 * brand-new users. Unlike the old SetupWizardController (now retired -
 * this tour absorbed its one genuinely useful piece, the inline "add a
 * website" action, plus a Cron Job step of its own), it also points out
 * the dashboard, SEO tools, settings tabs, and plugins.
 *
 * Has per-step resume state (feature_tour_step), same idea as the old
 * wizard's setup_wizard_step - a user who closes the tab mid-tour picks
 * back up where they left off instead of replaying Welcome.
 */

class FeatureTourController extends Controller {

    /*
     * Returns whether the tour should auto-show for this user, and
     * which step to resume at.
     * Show conditions: system setting enabled, user hasn't seen/dismissed it.
     */
    function getTourState($userId) {
        if (!defined('SP_FEATURE_TOUR') || !SP_FEATURE_TOUR) {
            return array('show' => false);
        }
        $userId = intval($userId);
        $sql = "SELECT feature_tour_seen, feature_tour_step FROM users WHERE id=$userId";
        $user = $this->db->select($sql, true);
        if (empty($user) || !empty($user['feature_tour_seen'])) {
            return array('show' => false);
        }
        return array('show' => true, 'step' => intval($user['feature_tour_step']));
    }

    /*
     * Save the tour's current step for the user (called via AJAX on
     * every Next/Back), so a reload resumes here instead of restarting.
     */
    function saveTourStep($data) {
        $userId = isLoggedIn();
        $step = isset($data['step']) ? intval($data['step']) : 0;
        if ($step < 0 || $step > 9) $step = 0;
        $this->db->query("UPDATE users SET feature_tour_step=$step WHERE id=" . intval($userId));
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    /*
     * Mark the tour as seen for this user (called via AJAX, on both
     * Skip and Finish - either way, don't auto-show it again).
     */
    function dismissTour() {
        $userId = isLoggedIn();
        $this->db->query("UPDATE users SET feature_tour_seen=1 WHERE id=" . intval($userId));
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    /*
     * Step 2 ("Add Your First Website"): creates a website, its primary
     * keyword, and optionally a social media link and/or review link -
     * all in one request. Website + keyword are mandatory (nothing else
     * in the tour or app has real data without at least one of each);
     * social/review are optional extras and only attempted if both
     * their url and type were actually filled in.
     *
     * Website and keyword failures abort and report the error (name
     * fields shown so the form can highlight it). Social/review
     * failures do NOT abort - the website+keyword the user asked for
     * still exist, so those are reported back as non-fatal warnings
     * instead of losing the whole submission over an optional extra.
     */
    function createWebsiteSetup($data) {
        $userId = isLoggedIn();

        include_once(SP_CTRLPATH . "/website.ctrl.php");
        $websiteCtrl = new WebsiteController();

        // Retry path: if the website was already created on a previous
        // attempt (only the keyword step failed - e.g. a bad search
        // engine selection) and the client sent that id back, skip
        // re-creating the website entirely - resubmitting the same
        // name/url would just fail as a duplicate against the row that
        // already exists.
        if (!empty($data['website_id']) && $websiteCtrl->__verifyWebsiteOwnership($data['website_id'])) {
            $websiteId = intval($data['website_id']);
        } else {
            $websiteResult = $websiteCtrl->createWebsite(array(
                'name' => trim($data['name'] ?? ''),
                'url'  => trim($data['url'] ?? ''),
            ), true);

            if ($websiteResult[0] !== 'success') {
                echo json_encode(array('status' => 'error', 'stage' => 'website', 'errors' => $websiteResult[1]));
                exit;
            }

            // same pattern the real API layer uses to recover the id of
            // a row it just inserted via a createX($data, true) call
            // that only ever returns a status string, not the new id
            // itself - see api/website.api.php's own createWebsite()
            $websiteId = $websiteCtrl->db->getMaxId('websites');
        }

        include_once(SP_CTRLPATH . "/keyword.ctrl.php");
        $keywordCtrl = new KeywordController();
        $keywordResult = $keywordCtrl->createKeyword(array(
            'name'          => trim($data['keyword'] ?? ''),
            'website_id'    => $websiteId,
            'searchengines' => !empty($data['search_engine']) ? array($data['search_engine']) : array(),
            'lang_code'     => $data['lang_code'] ?? '',
            'country_code'  => $data['country_code'] ?? '',
        ), true);

        if ($keywordResult[0] !== 'success') {
            // the website itself was created fine - only the keyword
            // step failed, so say so specifically rather than implying
            // the whole thing failed
            echo json_encode(array('status' => 'error', 'stage' => 'keyword', 'errors' => $keywordResult[1], 'website_id' => $websiteId));
            exit;
        }

        $warnings = array();

        if (!empty($data['social_url']) && !empty($data['social_type'])) {
            include_once(SP_CTRLPATH . "/social_media.ctrl.php");
            $smCtrl = new SocialMediaController();
            $smResult = $smCtrl->createSocialMediaLink(array(
                'name'       => ucfirst($data['social_type']),
                'url'        => trim($data['social_url']),
                'type'       => $data['social_type'],
                'website_id' => $websiteId,
            ), true);
            if ($smResult[0] !== 'success') {
                $warnings['social'] = $smResult[1];
            }
        }

        if (!empty($data['review_url']) && !empty($data['review_type'])) {
            include_once(SP_CTRLPATH . "/review_manager.ctrl.php");
            $rmCtrl = new ReviewManagerController();
            $reviewLabels = array(
                'google'      => 'Google My Business',
                'yelp'        => 'Yelp',
                'trustpilot'  => 'Trustpilot',
                'tripadvisor' => 'TripAdvisor',
            );
            $rmResult = $rmCtrl->createReviewLink(array(
                'name'       => $reviewLabels[$data['review_type']] ?? ucfirst($data['review_type']),
                'url'        => trim($data['review_url']),
                'type'       => $data['review_type'],
                'website_id' => $websiteId,
            ), true);
            if ($rmResult[0] !== 'success') {
                $warnings['review'] = $rmResult[1];
            }
        }

        echo json_encode(array('status' => 'ok', 'website_id' => $websiteId, 'warnings' => $warnings));
        exit;
    }

    /*
     * Live "Connected"/"Not set up" status per integration, for the
     * Refresh button in the tour popup - a fresh GET request re-runs
     * sp-load.php from scratch, so these constants already reflect
     * whatever was just saved in another tab; no caching to bust here.
     * Read-only (no state changes), so no POST/CSRF requirement, unlike
     * dismissTour() above.
     */
    function getConnectionStatus() {
        include_once(SP_CTRLPATH . "/settings.ctrl.php");
        $status = array(
            'seopanel_api' => defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED,
            'dataforseo'   => defined('SP_DFS_API_LOGIN') && SP_DFS_API_LOGIN !== '' && defined('SP_DFS_API_PASSWORD') && SP_DFS_API_PASSWORD !== '',
            // SP_MOZ_API_ACCESS_ID is a legacy field, hidden from the
            // settings UI (display=0) - a real user can never fill it in,
            // and MozController itself only ever reads SP_MOZ_API_SECRET
            // ("API Token" in the UI) for real API calls.
            'moz'          => defined('SP_MOZ_API_SECRET') && SP_MOZ_API_SECRET !== '',
            'google'       => defined('SP_GOOGLE_API_CLIENT_ID') && SP_GOOGLE_API_CLIENT_ID !== '' && defined('SP_GOOGLE_API_CLIENT_SECRET') && SP_GOOGLE_API_CLIENT_SECRET !== '',
            // Not gated on SP_SMTP_MAIL ("Enable SMTP") - per the app's
            // author, mail is sometimes sent through an API-based
            // provider rather than that toggle, so a filled-in host is
            // enough to call this configured.
            'mail'         => defined('SP_SMTP_HOST') && SP_SMTP_HOST !== '',
            'local_ai'     => SettingsController::isLocalAIEnabled(),
            'proxy'        => defined('SP_ENABLE_PROXY') && SP_ENABLE_PROXY,
            'cron'         => $this->__isCronDetected(),
        );
        header('Content-Type: application/json');
        echo json_encode($status);
        exit;
    }

    /*
     * Cron Job step's badge: has cron.php actually run recently? The
     * Cron Command page recommends running it every 15 minutes - 30
     * minutes gives a generous buffer above that before calling it "not
     * detected", so a slightly-late run doesn't flicker the badge.
     */
    function __isCronDetected() {
        $row = $this->db->select("SELECT id FROM cron_run_log WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) LIMIT 1", true);
        return !empty($row);
    }
}
