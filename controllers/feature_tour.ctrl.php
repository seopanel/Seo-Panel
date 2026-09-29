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
}
