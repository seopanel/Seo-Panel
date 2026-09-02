<?php
/**
 * Setup Wizard controller — handles initial setup wizard state and actions.
 */

class SetupWizardController extends Controller {

    /*
     * Returns whether the wizard should show, and the current step.
     * Show conditions: system setting enabled, user not dismissed, step < 8.
     */
    function getWizardState($userId) {
        if (!defined('SP_SETUP_WIZARD') || !SP_SETUP_WIZARD) {
            return array('show' => false);
        }
        $userId = intval($userId);
        $sql = "SELECT setup_wizard_step, setup_wizard_dismissed FROM users WHERE id=$userId";
        $user = $this->db->select($sql, true);
        if (empty($user) || !empty($user['setup_wizard_dismissed'])) {
            return array('show' => false);
        }
        $step = intval($user['setup_wizard_step']);
        if ($step >= 8) {
            return array('show' => false);
        }
        return array('show' => true, 'step' => $step);
    }

    /*
     * Save the wizard's current step for the user (called via AJAX).
     */
    function saveWizardStep($data) {
        $userId = isLoggedIn();
        $step   = isset($data['step']) ? intval($data['step']) : 0;
        if ($step < 0 || $step > 8) $step = 0;
        $this->db->query("UPDATE users SET setup_wizard_step=$step WHERE id=$userId");
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    /*
     * Mark wizard as dismissed permanently for this user.
     */
    function dismissWizard() {
        $userId = isLoggedIn();
        $this->db->query("UPDATE users SET setup_wizard_dismissed=1 WHERE id=$userId");
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    /*
     * Create a website from the wizard step 1 form.
     */
    function wizardCreateWebsite($data) {
        include_once(SP_CTRLPATH . "/website.ctrl.php");
        $wsCtrl = new WebsiteController();
        $result = $wsCtrl->createWebsite($data, true);
        if ($result[0] === 'success') {
            echo json_encode(array('status' => 'ok', 'message' => 'Website created successfully.'));
        } else {
            $errMsgs = array();
            if (is_array($result[1])) {
                foreach ($result[1] as $msg) {
                    if (!empty($msg)) $errMsgs[] = strip_tags($msg);
                }
            }
            echo json_encode(array('status' => 'error', 'message' => implode(' ', $errMsgs) ?: 'Failed to create website.'));
        }
        exit;
    }
}
