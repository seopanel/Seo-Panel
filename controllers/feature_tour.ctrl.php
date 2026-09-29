<?php
/**
 * Feature Tour controller - a short, skippable educational walkthrough
 * shown once to brand-new users, pointing out the dashboard, SEO tools,
 * settings tabs, and plugins. Separate from SetupWizardController, which
 * drives specific setup ACTIONS (add a website, connect APIs) rather
 * than orienting a new user to the app's layout - see
 * setup_wizard.ctrl.php for that one.
 *
 * Unlike the wizard, this has no per-step resume state: it's a short,
 * linear, skip-anytime tour rather than a multi-session checklist, so
 * "seen" is a single boolean.
 */

class FeatureTourController extends Controller {

    /*
     * Returns whether the tour should auto-show for this user.
     * Show conditions: system setting enabled, user hasn't seen/dismissed it.
     */
    function getTourState($userId) {
        if (!defined('SP_FEATURE_TOUR') || !SP_FEATURE_TOUR) {
            return array('show' => false);
        }
        $userId = intval($userId);
        $sql = "SELECT feature_tour_seen FROM users WHERE id=$userId";
        $user = $this->db->select($sql, true);
        if (empty($user) || !empty($user['feature_tour_seen'])) {
            return array('show' => false);
        }
        return array('show' => true);
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
        );
        header('Content-Type: application/json');
        echo json_encode($status);
        exit;
    }
}
