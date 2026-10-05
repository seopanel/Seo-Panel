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
     * Skip and Finish - either way, don't auto-show it again). Also
     * resets feature_tour_step to 0 - a link-click pause deliberately
     * leaves the step in place so a manual reopen resumes there (see
     * topmenu.ctp.php), but an explicit Skip/Finish means the user is
     * genuinely done, and "Setup Tour" reopening later should start
     * fresh from Welcome, not jump straight back to wherever they
     * finished (e.g. the Done screen).
     */
    function dismissTour() {
        $userId = isLoggedIn();
        $this->db->query("UPDATE users SET feature_tour_seen=1, feature_tour_step=0 WHERE id=" . intval($userId));
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
     * whatever was just saved in another tab; no caching to bust here,
     * except seopanel_api below (see __isSpApiConnected()). Read-only
     * (no state changes of its own), so no POST/CSRF requirement, unlike
     * dismissTour() above.
     *
     * Google, Mail, and Proxy below only check whether a credential was
     * SAVED, not whether it actually WORKS: Google's OAuth client
     * id/secret genuinely can't be verified here - unlike every API key
     * above, there is no "call an endpoint with these credentials and
     * see what comes back" for an OAuth client id/secret pair; Google
     * only validates them as part of a completed, user-interactive
     * consent redirect (see GoogleApiController::getAPIAuthUrl()/
     * createUserAuthToken()), which an AJAX call from this modal can't
     * drive. Mail/Proxy have no cheap "are you really there" probe
     * worth adding here either. seopanel_api, dataforseo, moz, and
     * local_ai are genuine live checks - each has its own free,
     * account-info-only (or, for local_ai, just local/LAN) endpoint, so
     * there's no quota/money cost to checking for real instead of just
     * trusting a saved value (see each __is*Connected() method's own
     * comment for specifics).
     */
    function getConnectionStatus() {
        include_once(SP_CTRLPATH . "/settings.ctrl.php");
        $status = array(
            'seopanel_api' => $this->__isSpApiConnected(),
            'dataforseo'   => $this->__isDataForSeoConnected(),
            // SP_MOZ_API_ACCESS_ID is a legacy field, hidden from the
            // settings UI (display=0) - a real user can never fill it in,
            // and MozController itself only ever reads SP_MOZ_API_SECRET
            // ("API Token" in the UI) for real API calls.
            'moz'          => $this->__isMozConnected(),
            'google'       => defined('SP_GOOGLE_API_CLIENT_ID') && SP_GOOGLE_API_CLIENT_ID !== '' && defined('SP_GOOGLE_API_CLIENT_SECRET') && SP_GOOGLE_API_CLIENT_SECRET !== '',
            // Not gated on SP_SMTP_MAIL ("Enable SMTP") - per the app's
            // author, mail is sometimes sent through an API-based
            // provider rather than that toggle, so a filled-in host is
            // enough to call this configured.
            'mail'         => defined('SP_SMTP_HOST') && SP_SMTP_HOST !== '',
            'local_ai'     => $this->__isLocalAiConnected(),
            'proxy'        => defined('SP_ENABLE_PROXY') && SP_ENABLE_PROXY,
            'cron'         => $this->__isCronDetected(),
        );
        header('Content-Type: application/json');
        echo json_encode($status);
        exit;
    }

    /*
     * Seo Panel API step's badge: is the saved key actually valid right
     * now, not just present? Reuses AlertController::updateSpApiAlerts()
     * rather than calling the spAPI directly - that function already
     * calls the real, free, authenticated /account endpoint
     * (SPAPIController::__getSpApiUsageData(), used nowhere else but
     * here and the alerts cron pass) and caches the result in the
     * information table for the rest of the day
     * (InformationController::__getTodayInformation('spapi_check')),
     * same mechanism AdminPanelController::index() already relies on for
     * the "subscription expired" / "usage limit reached" notifications.
     * So repeated Refresh clicks in the tour re-read that cache instead
     * of re-hitting the spAPI server every time, and the very first
     * check of the day (wherever it happens to fire from) populates the
     * cache for everyone else, tour included.
     */
    function __isSpApiConnected() {
        if (!defined('SP_SPAPI_REGISTERED') || !SP_SPAPI_REGISTERED) {
            return false;
        }
        // information.ctrl.php must load before alerts.ctrl.php's own
        // updateSpApiAlerts() runs - that method uses InformationController
        // internally but doesn't include it itself, relying on its only
        // existing caller (AdminPanelController::index()) having already
        // loaded it first. Got this backwards on the first pass here -
        // calling updateSpApiAlerts() before this include fatals with
        // "Class 'InformationController' not found", confirmed live.
        include_once(SP_CTRLPATH . "/information.ctrl.php");
        include_once(SP_CTRLPATH . "/alerts.ctrl.php");
        (new AlertController())->updateSpApiAlerts();
        $todayInfo = (new InformationController())->__getTodayInformation('spapi_check');
        return !empty($todayInfo) && $todayInfo['page'] === 'ok';
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

    /*
     * Shared once-a-day cache for a simple ok/error live check - used by
     * DataForSEO and MOZ below, which both just need "did the free
     * account-info call succeed", unlike seopanel_api's own richer
     * unconfirmed/expired/error states (see __isSpApiConnected(), which
     * has its own caching via updateSpApiAlerts() for that reason and
     * doesn't use this).
     */
    function __cachedLiveCheck($infoType, $liveCheckFn) {
        include_once(SP_CTRLPATH . "/information.ctrl.php");
        $infoCtrler = new InformationController();
        $todayInfo = $infoCtrler->__getTodayInformation($infoType);
        if (!empty($todayInfo)) {
            return $todayInfo['page'] === 'ok';
        }
        $ok = (bool) $liveCheckFn();
        $infoCtrler->updateTodayInformation($ok ? 'ok' : 'error', $infoType);
        return $ok;
    }

    /*
     * DataForSEO step/row's badge: is the saved login/password actually
     * valid, not just present? GET /v3/appendix/user_data (DataForSEO's
     * own account-info endpoint, also used by the "Verify" button on the
     * DataForSEO settings page itself) is account metadata, not a billed
     * search/SERP task - free to call, so there's no quota/money cost to
     * checking for real.
     */
    function __isDataForSeoConnected() {
        if (!defined('SP_DFS_API_LOGIN') || SP_DFS_API_LOGIN === '' || !defined('SP_DFS_API_PASSWORD') || SP_DFS_API_PASSWORD === '') {
            return false;
        }
        include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
        return $this->__cachedLiveCheck('dataforseo_check', function() {
            $connResult = (new DataForSEOController())->__checkAPIConnection(SP_DFS_API_LOGIN, SP_DFS_API_PASSWORD);
            return !empty($connResult['status']);
        });
    }

    /*
     * MOZ step/row's badge: is the saved API token actually valid, not
     * just present? quota.lookup (MOZ's own free quota-check JSON-RPC
     * method, also used by the "Verify" button on the MOZ settings page
     * itself) only reads remaining limits - no billed row/metric
     * consumed, so there's no quota/money cost to checking for real.
     */
    function __isMozConnected() {
        if (!defined('SP_MOZ_API_SECRET') || SP_MOZ_API_SECRET === '') {
            return false;
        }
        include_once(SP_CTRLPATH . "/moz.ctrl.php");
        return $this->__cachedLiveCheck('moz_check', function() {
            list($usageData, $logInfo) = (new MozController())->__getMozUsageData(SP_MOZ_API_SECRET, true);
            return !empty($logInfo['crawl_status']);
        });
    }

    /*
     * Local AI step/row's badge: is Ollama actually reachable at the
     * saved base URL, not just configured? GET /api/tags (Ollama's own
     * lightweight "list installed models" endpoint, also used by the
     * "Verify" button on the Local AI settings page itself) is a plain
     * local/LAN call to the user's own self-hosted server - not a
     * third-party API, so there's no quota/money (or even network
     * egress) cost at all. Unlike DataForSEO/MOZ/seopanel_api above,
     * deliberately NOT run through __cachedLiveCheck()'s once-a-day
     * cache - a self-hosted dev box is far more likely to be started/
     * stopped within the same day than a third-party account's
     * subscription status is, and there's no cost reason to delay
     * noticing that. __checkOllamaConnection() itself keeps a short
     * (5 second) timeout, so an unreachable server doesn't stall the
     * page.
     */
    function __isLocalAiConnected() {
        include_once(SP_CTRLPATH . "/settings.ctrl.php");
        if (!SettingsController::isLocalAIEnabled()) {
            return false;
        }
        include_once(SP_CTRLPATH . "/localai.ctrl.php");
        $connResult = (new LocalAIController())->__checkOllamaConnection(SP_LOCAL_AI_URL);
        return !empty($connResult['status']);
    }

    /*
     * "Test Now" button next to Cron Command - the one thing this tour
     * can genuinely RUN instead of just verifying credentials for.
     * There's no API key or account to check for cron - it's an OS-level
     * crontab entry outside the app's control and never inspectable from
     * inside a PHP request - so the only honest way to answer "does this
     * actually work" is to run cron.php's own logic once, right now, and
     * see what happens. Reuses CronController::runManualTrigger()
     * (shared with cron-beacon.php's opportunistic ping trigger, minus
     * its SP_CRON_PING_ENABLED gate - this is an explicit admin click,
     * not a background poke), so it's the exact same budget-limited,
     * locked execution path already proven safe to run synchronously
     * from an HTTP request. POST-only - this genuinely changes state
     * (runs real website processing), unlike the read-only checks above.
     */
    function testCronNow() {
        checkAdminLoggedIn();
        include_once(SP_CTRLPATH . "/cron.ctrl.php");
        // runManualTrigger() buffers and discards its own CLI-style
        // progress text (ob_start()/ob_end_clean()), same as
        // cron-ping.php/cron-beacon.php rely on - but a couple of spots
        // deep inside executeCron() (the DFS/SERP task-polling loops)
        // also call ob_flush()+flush() to stream live progress during a
        // real CLI run, which pushes content straight to the client
        // instead of leaving it in that buffer to be discarded. An outer
        // buffer here catches exactly that case: flush() only pushes
        // content down to the next open buffer level, not all the way
        // out, so this one still ends up holding (and then discarding)
        // whatever would otherwise have corrupted the JSON response
        // below with stray HTML and triggered a "headers already sent"
        // warning on the header() call that follows.
        ob_start();
        $ran = (new CronController())->runManualTrigger('tour-test');
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(array(
            'status'   => $ran ? 'ok' : 'busy',
            'detected' => $this->__isCronDetected(),
        ));
        exit;
    }
}
