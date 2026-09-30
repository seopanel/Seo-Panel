<?php
// Translatable via the standard texts-table mechanism (category
// 'featuretour', same getLanguageTexts()/fallback-to-English pattern
// every other category in the app uses) - tourText() falls back to
// the given English default whenever a key isn't seeded/translated
// yet, so the tour always renders correctly even before any
// translator has touched this category.
//
// Reuses existing categories wherever the exact same English string is
// already translated elsewhere in the app, rather than seeding a
// duplicate under 'featuretour' - checked directly against the live
// texts table before writing anything: all 12 SEO Tools card labels
// already exist verbatim under 'seotools' (keyed by the very url_section
// slugs this file already links with), several Settings row labels
// exist under 'panel' as "X Settings", and Dashboard/Plugins/Country/
// Search Engine/Language/Seo Tools already exist under 'common'
// (already loaded as $spText - see libs/controller.class.php's
// $sessionCats). Only genuinely new tour-specific copy gets a new key.
// Written directly to $GLOBALS, not a plain assignment - this file is
// include_once'd from inside default.ctp.php, which is itself rendered
// via View::render()'s own include() call. A top-level "$spTextTour ="
// here would only be local to THAT render() invocation's function
// scope, not the true global scope - tourText()'s "global $spTextTour"
// would then see nothing and silently fall back to the English default
// every time, regardless of the active language. Confirmed live: a
// seeded German translation never rendered until this was fixed.
$GLOBALS['spTextTour'] = (new Controller())->getLanguageTexts('featuretour', $_SESSION['lang_code']);
$spTextPanel = (new Controller())->getLanguageTexts('panel', $_SESSION['lang_code']);
$spTextSeoTools = (new Controller())->getLanguageTexts('seotools', $_SESSION['lang_code']);
$spTextDirectory = (new Controller())->getLanguageTexts('directory', $_SESSION['lang_code']);
function tourText($key, $default) {
    global $spTextTour;
    return $spTextTour[$key] ?? $default;
}
?>
<div class="sp-wizard-overlay" id="sp_tour_overlay">
    <div class="sp-wizard-box">

        <!-- Header with step progress -->
        <div class="sp-wizard-header">
            <h4 class="sp-tour-header-row">
                <span><i class="fas fa-compass" style="margin-right:8px;"></i><?php echo $spText['common']['Setup Tour'] ?? 'Setup Tour' ?></span>
                <button type="button" class="sp-tour-refresh-btn" id="sp_tour_refresh_btn" onclick="window.featureTourRefreshConnections()" title="<?php echo htmlspecialchars(tourText('tour_refresh_tooltip', 'Just saved something in another tab? Refresh to update the connection badges below')) ?>">
                    <i class="fas fa-sync-alt"></i> <?php echo tourText('tour_refresh', 'Refresh') ?>
                </button>
            </h4>
            <div class="sp-wizard-steps" id="sp_tour_steps">
                <?php for ($i = 1; $i <= 9; $i++) { ?>
                <div class="sp-wizard-step-item">
                    <div class="sp-wizard-step-dot" id="sp_tdot_<?php echo $i ?>"><?php echo $i ?></div>
                    <?php if ($i < 9) { ?><div class="sp-wizard-step-line" id="sp_tline_<?php echo $i ?>"></div><?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Body — one panel per step -->
        <div class="sp-wizard-body">

            <!-- Step 1: Welcome -->
            <div class="sp-wizard-panel" id="sp_tpanel_1">
                <h5><i class="fas fa-hand-sparkles" style="margin-right:6px;"></i><?php echo tourText('tour_step1_heading', 'Welcome to SEO Panel') ?></h5>
                <p><?php echo tourText('tour_step1_body', 'SEO Panel tracks rankings, audits your sites, checks backlinks, and monitors how you show up in AI answer engines - all from one self-hosted control room.') ?></p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    <?php echo tourText('tour_step1_info', 'This is a short tour of where everything lives, and gets your first website set up along the way. Skip it anytime, or take it again later from the <strong>Setup Tour</strong> link in the top menu.') ?>
                </div>
            </div>

            <?php
            // SettingsController::isLocalAIEnabled() below - not guaranteed
            // loaded yet for a non-admin user (default.ctp.php's own
            // include_once for it is gated behind isAdmin(), further down)
            include_once(SP_CTRLPATH . "/settings.ctrl.php");
            include_once(SP_CTRLPATH . "/website.ctrl.php");
            include_once(SP_CTRLPATH . "/searchengine.ctrl.php");
            include_once(SP_CTRLPATH . "/language.ctrl.php");
            include_once(SP_CTRLPATH . "/country.ctrl.php");
            include_once(SP_CTRLPATH . "/seoplugins.ctrl.php");

            // admin-panel.php is Settings' own shell page (full navbar +
            // left menu, same as seo-tools.php is for the Tools menu
            // below) - it reads menu_selected (which left-menu item to
            // highlight) and start_script (which inner settings.php view
            // to auto-load into it) as plain query params, so any
            // settings.php URL can be reached this way, not just the
            // handful admin-panel.php's own sec= shortcuts cover. Most
            // callers are Settings rows ($menuSelected defaults to that),
            // but Cron Command actually lives under the left menu's
            // Report Manager section (adminleftmenu.ctp.php), not Settings.
            function tourSettingsLink($startScript, $menuSelected = 'settings') {
                return SP_WEBPATH . '/admin-panel.php?menu_selected=' . urlencode($menuSelected) . '&start_script=' . urlencode($startScript);
            }
            // "already configured" per category - the same constants the
            // app itself gates real functionality on (SettingsController::
            // isSpApiEnabled()/isDFSEnabled()/isLocalAIEnabled(), and the
            // matching credential settings for MOZ/Google/Mail/Proxy) -
            // a cheap defined()/non-empty check, no live API calls.
            $tourSpApiConnected = defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED;
            $tourDfsConnected = defined('SP_DFS_API_LOGIN') && SP_DFS_API_LOGIN !== '' && defined('SP_DFS_API_PASSWORD') && SP_DFS_API_PASSWORD !== '';
            // SP_MOZ_API_ACCESS_ID is a legacy field, hidden from the
            // settings UI (display=0) - a real user can never fill it in,
            // and MozController itself only ever reads SP_MOZ_API_SECRET
            // ("API Token" in the UI) for real API calls. Requiring both
            // meant this badge could never show Connected even with a
            // correctly saved token - confirmed live via a screenshot.
            $tourMozConnected = defined('SP_MOZ_API_SECRET') && SP_MOZ_API_SECRET !== '';
            $tourGoogleConnected = defined('SP_GOOGLE_API_CLIENT_ID') && SP_GOOGLE_API_CLIENT_ID !== '' && defined('SP_GOOGLE_API_CLIENT_SECRET') && SP_GOOGLE_API_CLIENT_SECRET !== '';
            // Not gated on SP_SMTP_MAIL ("Enable SMTP") - per the app's
            // author, mail is sometimes sent through an API-based
            // provider rather than that toggle, so a filled-in host is
            // enough to call this configured.
            $tourMailConnected = defined('SP_SMTP_HOST') && SP_SMTP_HOST !== '';
            $tourLocalAiConnected = SettingsController::isLocalAIEnabled();
            $tourProxyConnected = defined('SP_ENABLE_PROXY') && SP_ENABLE_PROXY;
            $tourCronConnected = (new FeatureTourController())->__isCronDetected();

            function tourConnectionBadgeHtml($isConnected, $connectedLabel, $pendingLabel) {
                if ($isConnected) {
                    return '<span class="sp-tour-badge sp-tour-badge-connected"><i class="fas fa-check-circle"></i> ' . $connectedLabel . '</span>';
                }
                return '<span class="sp-tour-badge sp-tour-badge-pending">' . $pendingLabel . '</span>';
            }
            // Important = the tools you'll see next have no real data
            // without this connected; Optional = nice-to-have, nothing is
            // blocked if it's skipped for now.
            function tourImportanceBadge($isImportant) {
                if ($isImportant) {
                    return '<span class="sp-tour-badge sp-tour-badge-important">' . tourText('tour_important', 'Important') . '</span>';
                }
                return '<span class="sp-tour-badge sp-tour-badge-optional">' . tourText('tour_optional_badge', 'Optional') . '</span>';
            }
            // wraps both badges together, tagged with data-service so the
            // Refresh button (see the <script> below) can find and update
            // just the connection half in place, without touching the
            // importance badge or re-rendering the row
            function tourBadges($service, $isImportant, $isConnected = null, $connectedLabel = null, $pendingLabel = null) {
                $connectedLabel = $connectedLabel ?? tourText('tour_connected', 'Connected');
                $pendingLabel = $pendingLabel ?? tourText('tour_not_set_up', 'Not set up');
                $html = '<span class="sp-tour-badges" data-service="' . $service . '">';
                $html .= tourImportanceBadge($isImportant);
                if ($isConnected !== null) {
                    $html .= '<span class="sp-tour-connection-badge">' . tourConnectionBadgeHtml($isConnected, $connectedLabel, $pendingLabel) . '</span>';
                }
                $html .= '</span>';
                return $html;
            }

            $tourUserId = isLoggedIn();
            $tourWebsiteCount = 0;
            $tourFirstWebsite = null;
            $tourFirstWebsiteKeywordCount = 0;
            if ($tourUserId) {
                $tourWebsiteCountRow = (new WebsiteController())->db->select("SELECT COUNT(*) as c FROM websites WHERE user_id=" . intval($tourUserId), true);
                $tourWebsiteCount = intval($tourWebsiteCountRow['c'] ?? 0);
                if ($tourWebsiteCount > 0) {
                    $tourFirstWebsite = (new WebsiteController())->db->select("SELECT id, name, url FROM websites WHERE user_id=" . intval($tourUserId) . " ORDER BY id ASC LIMIT 1", true);
                    if ($tourFirstWebsite) {
                        $tourFirstWebsiteKeywordCount = intval((new WebsiteController())->db->select("SELECT COUNT(*) as c FROM keywords WHERE website_id=" . intval($tourFirstWebsite['id']), true)['c'] ?? 0);
                    }
                }
            }

            // Plugins step: the real, active+installed plugin list (same
            // "status=1 and installed=1" query SeoPluginsController::
            // showSeoPlugins() itself uses), filtered by this user's own
            // plugin access permissions for non-admins - the exact same
            // check showSeoPlugins() applies, so a card is never shown
            // for a plugin this user would actually be denied.
            $tourPluginList = (new SeoPluginsController())->__getAllSeoPlugins("status=1 and installed=1");
            if (!isAdmin() && !empty($tourPluginList)) {
                include_once(SP_CTRLPATH . "/user-type.ctrl.php");
                $tourUserSessInfo = Session::readSession('userInfo');
                $tourPluginAccessList = (new UserTypeController())->getPluginAccessSettings($tourUserSessInfo['userTypeId']);
                $tourPluginList = array_values(array_filter($tourPluginList, function($pluginInfo) use ($tourPluginAccessList) {
                    if (!isset($tourPluginAccessList[$pluginInfo['id']]['value'])) {
                        return true;
                    }
                    return !empty($tourPluginAccessList[$pluginInfo['id']]['value']);
                }));
            }
            // $GLOBALS, not a plain assignment - same reasoning as
            // $GLOBALS['spTextTour'] above: a bare "global" inside
            // tourPluginIcon() below can't see a variable that was only
            // ever local to View::render()'s own include() scope.
            $GLOBALS['tourPluginIcons'] = array(
                'MetaTagGenerator' => 'fa-tags',
                'QuickWebProxy' => 'fa-exchange-alt',
                'SeoDiary' => 'fa-book',
                'ArticleSubmitter' => 'fa-file-alt',
            );
            function tourPluginIcon($pluginName) {
                global $tourPluginIcons;
                return $tourPluginIcons[$pluginName] ?? 'fa-puzzle-piece';
            }
            $tourSearchEngines = (new SearchEngineController())->__getAllSearchEngines();
            $tourLanguages = (new LanguageController())->__getAllLanguages();
            $tourCountries = (new CountryController())->__getAllCountryAsList();
            ?>

            <!-- Step 2: Add Your First Website - the one genuinely useful
                 piece of the old Setup Wizard (now retired), absorbed here
                 as this tour's own action step. Website + primary keyword
                 are mandatory (nothing else the tour points at next has
                 real data without at least one of each); social/review
                 links are optional extras. -->
            <div class="sp-wizard-panel" id="sp_tpanel_2">
                <h5><i class="fas fa-globe" style="margin-right:6px;"></i><?php echo tourText('tour_step2_heading', 'Add Your First Website') ?></h5>
                <?php if ($tourWebsiteCount > 0) { ?>
                    <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                        <i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>
                        <?php echo tourText('tour_step2_already_set', "You're all set - nothing to do here.") ?>
                    </div>
                    <?php if ($tourFirstWebsite) { ?>
                        <a class="sp-tour-link-row" href="<?php echo SP_WEBPATH ?>/websites.php" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-globe"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo htmlspecialchars($tourFirstWebsite['name']) ?></strong><small><?php echo htmlspecialchars($tourFirstWebsite['url']) ?></small></span>
                            <?php if ($tourFirstWebsiteKeywordCount > 0) { ?>
                                <span class="sp-tour-badge sp-tour-badge-connected"><?php echo $tourFirstWebsiteKeywordCount ?> <?php echo tourText($tourFirstWebsiteKeywordCount == 1 ? 'tour_keyword_singular' : 'tour_keyword_plural', $tourFirstWebsiteKeywordCount == 1 ? 'keyword' : 'keywords') ?></span>
                            <?php } ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <?php if ($tourWebsiteCount > 1) { ?>
                            <div class="sp-tour-form-hint"><?php echo sprintf(tourText('tour_more_websites', '+ %d more %s - click above to manage all of them'), $tourWebsiteCount - 1, tourText(($tourWebsiteCount - 1) == 1 ? 'tour_website_singular' : 'tour_website_plural', ($tourWebsiteCount - 1) == 1 ? 'website' : 'websites')) ?></div>
                        <?php } ?>
                    <?php } ?>
                <?php } else { ?>
                    <p><?php echo tourText('tour_step2_intro', 'Everything below - the dashboard, rank tracking, audits - needs at least one website to work with. Takes a few seconds:') ?></p>
                    <div id="tour_web_general_err"></div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_name"><?php echo tourText('tour_field_website_name', 'Website Name') ?> <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_name" class="form-control" placeholder="<?php echo htmlspecialchars(tourText('tour_placeholder_website_name', 'e.g. My Company Website')) ?>">
                        <div class="sp-tour-field-err" id="tour_web_err_name"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_url"><?php echo $spTextDirectory['Website Url'] ?? 'Website Url' ?> <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_url" class="form-control" placeholder="<?php echo htmlspecialchars(tourText('tour_placeholder_website_url', 'https://example.com')) ?>">
                        <div class="sp-tour-field-err" id="tour_web_err_url"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_keyword"><?php echo tourText('tour_field_keyword', 'Primary Keyword') ?> <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_keyword" class="form-control" placeholder="<?php echo htmlspecialchars(tourText('tour_placeholder_keyword', 'e.g. seo software')) ?>">
                        <div class="sp-tour-field-err" id="tour_web_err_keyword"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_se"><?php echo $spText['common']['Search Engine'] ?? 'Search Engine' ?> <span class="sp-tour-required">*</span></label>
                        <select id="tour_web_se" class="form-control custom-select">
                            <?php foreach ($tourSearchEngines as $seInfo) { ?>
                                <option value="<?php echo $seInfo['id'] ?>"><?php echo $seInfo['domain'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="sp-tour-field-err" id="tour_web_err_searchengines"></div>
                    </div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_lang"><?php echo $spText['common']['lang'] ?? 'Language' ?></label>
                            <select id="tour_web_lang" class="form-control custom-select">
                                <option value=""><?php echo tourText('tour_optional_option', '-- optional --') ?></option>
                                <?php foreach ($tourLanguages as $langInfo) { ?>
                                    <option value="<?php echo $langInfo['lang_code'] ?>"><?php echo $langInfo['lang_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="sp-tour-form-row">
                            <label for="tour_web_country"><?php echo $spText['common']['Country'] ?? 'Country' ?></label>
                            <select id="tour_web_country" class="form-control custom-select">
                                <option value=""><?php echo tourText('tour_optional_option', '-- optional --') ?></option>
                                <?php foreach ($tourCountries as $countryCode => $countryName) { ?>
                                    <option value="<?php echo $countryCode ?>"><?php echo $countryName ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="sp-tour-form-divider"><?php echo tourText('tour_optional_extras', 'Optional extras') ?></div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_social_type"><?php echo tourText('tour_field_social', 'Social Media') ?></label>
                            <select id="tour_web_social_type" class="form-control custom-select">
                                <option value=""><?php echo tourText('tour_none_option', '-- none --') ?></option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="instagram">Instagram</option>
                                <option value="pinterest">Pinterest</option>
                                <option value="youtube">YouTube</option>
                                <option value="reddit">Reddit</option>
                            </select>
                        </div>
                        <div class="sp-tour-form-row">
                            <label for="tour_web_social_url">&nbsp;</label>
                            <input type="text" id="tour_web_social_url" class="form-control" placeholder="<?php echo htmlspecialchars(tourText('tour_placeholder_profile_url', 'Profile URL')) ?>">
                        </div>
                    </div>
                    <div class="sp-tour-field-err" id="tour_web_err_social"></div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_review_type"><?php echo tourText('tour_field_review', 'Review Link') ?></label>
                            <select id="tour_web_review_type" class="form-control custom-select" onchange="window._tourUpdateReviewHint()">
                                <option value=""><?php echo tourText('tour_none_option', '-- none --') ?></option>
                                <option value="google">Google My Business</option>
                                <option value="yelp">Yelp</option>
                                <option value="trustpilot">Trustpilot</option>
                                <option value="tripadvisor">TripAdvisor</option>
                            </select>
                        </div>
                        <div class="sp-tour-form-row">
                            <label for="tour_web_review_url">&nbsp;</label>
                            <input type="text" id="tour_web_review_url" class="form-control" placeholder="<?php echo htmlspecialchars(tourText('tour_placeholder_review_url', 'Review page URL')) ?>">
                        </div>
                    </div>
                    <div class="sp-tour-form-hint" id="tour_web_review_hint"><?php echo tourText('tour_review_hint', 'The URL must contain the platform\'s name, e.g. a Yelp link should include "yelp".') ?></div>
                    <div class="sp-tour-field-err" id="tour_web_err_review"></div>

                    <button type="button" class="sp-confirm-btn sp-confirm-btn-confirm" id="tour_web_submit_btn" onclick="window._tourSubmitWebsite()" style="margin-top:10px;">
                        <i class="fas fa-plus" style="margin-right:5px;"></i><?php echo tourText('tour_add_website_btn', 'Add Website') ?>
                    </button>
                <?php } ?>
            </div>

            <!-- Step 3: Seo Panel API - its own step, called out separately
                 from the general Settings list below since it's the main
                 source of rank/SERP data for accounts without their own
                 DataForSEO or MOZ keys. settings.php requires
                 checkAdminLoggedIn() for every category (only sec=aboutus
                 is exempt) - a non-admin clicking this would get bounced
                 to login.php, so they get the live badge without a
                 broken link, same treatment as the Cron step below. -->
            <div class="sp-wizard-panel" id="sp_tpanel_3">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i><?php echo tourText('tour_step3_heading', 'Seo Panel API') ?></h5>
                <p><?php echo tourText('tour_step3_body', 'The fastest way to get real rank and SERP data flowing without hunting down your own DataForSEO or MOZ keys - free to register, no credit card.') ?></p>
                <?php if (isAdmin()) { ?>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=seopanel_api') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                        <span class="sp-tour-link-icon"><i class="fas fa-plug"></i></span>
                        <span class="sp-tour-link-text"><strong><?php echo tourText('tour_seopanel_api_label', 'Seo Panel API') ?></strong><small><?php echo tourText('tour_seopanel_api_desc', 'Rank tracking and SERP data, ready in a couple of minutes') ?></small></span>
                        <?php echo tourBadges('seopanel_api', true, $tourSpApiConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                <?php } else { ?>
                    <div class="sp-wizard-info-box">
                        <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                        <?php echo tourText('tour_admin_only_spapi', 'This is an account-wide setting, so only an admin on your account can register it. Current status:') ?>
                        <?php echo tourBadges('seopanel_api', true, $tourSpApiConnected) ?>
                    </div>
                <?php } ?>
            </div>

            <!-- Step 4: Settings - where your integrations live.
                 settings.php requires checkAdminLoggedIn() for every
                 category, so this whole step is admin-only for the same
                 reason step 3 is - a non-admin sees the live badges
                 without the broken links. -->
            <div class="sp-wizard-panel" id="sp_tpanel_4">
                <h5><i class="fas fa-cog" style="margin-right:6px;"></i><?php echo tourText('tour_step4_heading', 'Settings: Where Your Integrations Live') ?></h5>
                <?php if (isAdmin()) { ?>
                    <p><?php echo tourText('tour_step4_body', "Worth doing before the Tools menu next: without these connected, several tools won't have any real data to show yet. Click any row to go straight there in a new tab:") ?></p>
                    <div class="sp-tour-link-list">
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-sliders-h"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['System Settings'] ?? 'System Settings' ?></strong><small><?php echo tourText('tour_system_desc', 'Language, timezone, pagination, and other app-wide defaults') ?></small></span>
                            <?php echo tourBadges('system', false) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=dataforseo') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-database"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['DataForSEO Settings'] ?? 'DataForSEO Settings' ?></strong><small><?php echo tourText('tour_dfs_desc', 'The data provider behind rank checking and SERP data') ?></small></span>
                            <?php echo tourBadges('dataforseo', true, $tourDfsConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=moz') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-chart-bar"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['MOZ Settings'] ?? 'MOZ Settings' ?></strong><small><?php echo tourText('tour_moz_desc', 'Domain Authority, Page Authority, and Spam Score') ?></small></span>
                            <?php echo tourBadges('moz', true, $tourMozConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=google') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fab fa-google"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['Google Settings'] ?? 'Google Settings' ?></strong><small><?php echo tourText('tour_google_desc', 'Connect Analytics and Search Console') ?></small></span>
                            <?php echo tourBadges('google', true, $tourGoogleConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=mail') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-envelope"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['Mail Settings'] ?? 'Mail Settings' ?></strong><small><?php echo tourText('tour_mail_desc', 'Scheduled reports, password resets, and registration emails all go through here') ?></small></span>
                            <?php echo tourBadges('mail', true, $tourMailConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=local_ai') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-brain"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['Local AI Settings'] ?? 'Local AI Settings' ?></strong><small><?php echo tourText('tour_localai_desc', 'Point AI-powered features at your own Ollama server') ?></small></span>
                            <?php echo tourBadges('local_ai', false, $tourLocalAiConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?sec=proxysettings') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-network-wired"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['Proxy Settings'] ?? 'Proxy Settings' ?></strong><small><?php echo tourText('tour_proxy_desc', 'Proxies used for crawling and directory submission') ?></small></span>
                            <?php echo tourBadges('proxy', false, $tourProxyConnected) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="sp-wizard-info-box">
                        <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                        <?php echo tourText('tour_admin_only_settings', 'These are account-wide settings, so only an admin on your account can change them - nothing to do here.') ?>
                    </div>
                <?php } ?>
            </div>

            <!-- Step 5: Cron Job - nothing scheduled (rank checks, audits,
                 reports) runs at all without this. Badge is a genuine live
                 check (cron_run_log), not just a setting's presence.
                 cron.php requires checkAdminLoggedIn() - a non-admin user
                 can't reach that page at all, so they get an explanatory
                 note instead of a link they'd just be denied on. -->
            <div class="sp-wizard-panel" id="sp_tpanel_5">
                <h5><i class="fas fa-clock" style="margin-right:6px;"></i><?php echo tourText('tour_step5_heading', 'Set Up the Cron Job') ?></h5>
                <p><?php echo tourText('tour_step5_body', "SEO Panel checks rankings, runs audits, and generates reports on a schedule - but only once your server is actually calling <code>cron.php</code>. Nothing above matters if this isn't running.") ?></p>
                <?php if (isAdmin()) { ?>
                    <?php
                    // Site Auditor runs on its own separate cron script
                    // (siteauditorcron.php), not cron.php above - easy to
                    // miss since it lives under the Seo Tools shell
                    // (seo-tools.php?menu_sec=site-auditor), not Reports
                    // Manager. default_args (this shell's equivalent of
                    // admin-panel.php's inner-script arguments, read by
                    // SeoToolsController::index()) lands directly on that
                    // tool's own Cron Command sub-page instead of just its
                    // default view.
                    // Both rows wrapped in sp-tour-link-list (same wrapper
                    // Step 4's Settings rows use) for the gap/margin
                    // between them - without it the two rows sit flush
                    // against each other, and the ping-trigger hint just
                    // below (sp-tour-form-hint's negative margin-top,
                    // meant to hug a form field above it) looked stuck
                    // directly onto the Site Auditor row instead of being
                    // its own separate line - confirmed live via screenshot.
                    $tourSaCronLink = SP_WEBPATH . '/seo-tools.php?menu_sec=site-auditor&default_args=' . urlencode('sec=croncommand');
                    ?>
                    <div class="sp-tour-link-list">
                        <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('cron.php?sec=croncommand', 'report-manager') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-terminal"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo $spTextPanel['Cron Command'] ?? 'Cron Command' ?></strong><small><?php echo tourText('tour_cron_desc', "The exact command to add to your server's crontab") ?></small></span>
                            <?php echo tourBadges('cron', true, $tourCronConnected, tourText('tour_detected', 'Detected'), tourText('tour_not_detected', 'Not detected yet')) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                        <a class="sp-tour-link-row" href="<?php echo $tourSaCronLink ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <span class="sp-tour-link-icon"><i class="fas fa-tasks"></i></span>
                            <span class="sp-tour-link-text"><strong><?php echo sprintf(tourText('tour_sa_cron_title', '%s Cron Command'), $spTextSeoTools['site-auditor'] ?? 'Site Auditor') ?></strong><small><?php echo tourText('tour_sa_cron_desc', 'A separate command for scheduled site audits') ?></small></span>
                            <?php echo tourBadges('siteauditor_cron', true) ?>
                            <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                        </a>
                    </div>
                    <?php
                    // Resumable job queue needs no mention here - it's on by
                    // default for every fresh install (SP_JOB_QUEUE_ENABLED
                    // seeds '1' in both seopanel.sql and upgrade.sql) and
                    // there's nothing for a new user to set up. The ping
                    // trigger is the opposite: off by default (opt-in - it
                    // hands out a bearer-token-style secret URL, so it can't
                    // just be silently turned on) and only actually useful
                    // to someone whose host has no real crontab access - a
                    // small secondary link rather than a second full row,
                    // reached via the same Reports Manager tab (Scheduler
                    // Health, cron.php?sec=health) as Cron Command above.
                    ?>
                    <div class="sp-tour-form-hint">
                        <?php echo tourText('tour_cron_ping_hint', "Can't set up a real crontab on your host?") ?>
                        <a href="<?php echo tourSettingsLink('cron.php?sec=health', 'report-manager') ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                            <?php echo sprintf(tourText('tour_cron_ping_hint_link', 'Use the %s instead.'), $spTextPanel['External ping trigger'] ?? 'External Ping Trigger') ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="sp-wizard-info-box">
                        <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                        <?php echo tourText('tour_admin_only_cron', 'This is a server setting, so only an admin on your account can set it up. Current status:') ?>
                        <?php echo tourBadges('cron', true, $tourCronConnected, tourText('tour_detected', 'Detected'), tourText('tour_not_detected', 'Not detected yet')) ?>
                    </div>
                <?php } ?>
            </div>

            <!-- Step 6: Dashboard -->
            <div class="sp-wizard-panel" id="sp_tpanel_6">
                <h5><i class="fas fa-chart-line" style="margin-right:6px;"></i><?php echo tourText('tour_step6_heading', 'Your Dashboard') ?></h5>
                <p><?php echo tourText('tour_step6_body', 'Pick a website from the dropdown to see its ranking trends, top keywords, and recent activity at a glance.') ?></p>
                <a class="sp-tour-link-row" href="<?php echo SP_WEBPATH ?>/" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                    <span class="sp-tour-link-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="sp-tour-link-text"><strong><?php echo $spText['common']['Dashboard'] ?? 'Dashboard' ?></strong><small><?php echo tourText('tour_dashboard_desc', 'The screen you land on after logging in') ?></small></span>
                    <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                </a>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    <?php echo tourText('tour_step6_info', 'Every tool you connect (Analytics, Social Media, Reviews...) gets its own dashboard tab here too.') ?>
                </div>
            </div>

            <!-- Step 7: SEO Tools -->
            <div class="sp-wizard-panel" id="sp_tpanel_7">
                <h5><i class="fas fa-tools" style="margin-right:6px;"></i><?php echo $spText['common']['Seo Tools'] ?? 'Seo Tools' ?></h5>
                <p><?php echo tourText('tour_step7_body', 'The <strong>Tools</strong> menu is where the actual work happens - twelve tools in one place. Click any card to open it in a new tab:') ?></p>
                <div class="sp-tour-tool-grid">
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=ai-visibility" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-robot"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['ai-visibility'] ?? 'AI Visibility' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=keyword-position-checker" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-key"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['keyword-position-checker'] ?? 'Keyword Position Checker' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=site-auditor" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-tasks"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['site-auditor'] ?? 'Site Auditor' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=backlink-checker" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-link"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['backlink-checker'] ?? 'Backlinks Checker' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=webmaster-tools" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-globe"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['webmaster-tools'] ?? 'Webmaster Tools' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=rank-checker" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-search-location"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['rank-checker'] ?? 'Rank Checker' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=directory-submission" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-folder-open"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['directory-submission'] ?? 'Directory Submission' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=saturation-checker" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-server"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['saturation-checker'] ?? 'Search Engine Saturation' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=pagespeed" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-tachometer-alt"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['pagespeed'] ?? 'PageSpeed Insights' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=sm-checker" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-share-alt"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['sm-checker'] ?? 'Social Media Checker' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=web-analytics" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-chart-area"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['web-analytics'] ?? 'Website Analytics' ?></span></a>
                    <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=review-manager" target="_blank" onclick="window.featureTourPauseOnLinkClick()"><span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span><span class="sp-tour-tool-icon"><i class="fas fa-star"></i></span><span class="sp-tour-tool-label"><?php echo $spTextSeoTools['review-manager'] ?? 'Review Manager' ?></span></a>
                </div>
            </div>

            <!-- Step 8: Plugins - lists the real, active plugins (same
                 "status=1 and installed=1" set showSeoPlugins() itself
                 uses), not just a generic link - the old link actually
                 pointed at the About Us/sponsors page, not a plugin
                 browser at all. -->
            <div class="sp-wizard-panel" id="sp_tpanel_8">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i><?php echo $spText['common']['Plugins'] ?? 'Plugins' ?></h5>
                <?php if (!empty($tourPluginList)) { ?>
                    <p><?php echo tourText('tour_step8_body', 'The <strong>Plugins</strong> menu extends SEO Panel beyond the core tools. Click any card to open it in a new tab:') ?></p>
                    <div class="sp-tour-tool-grid">
                        <?php foreach ($tourPluginList as $pluginInfo) { ?>
                            <a class="sp-tour-tool-card" href="<?php echo SP_WEBPATH ?>/seo-plugins.php?sec=show&menu_selected=<?php echo intval($pluginInfo['id']) ?>" target="_blank" onclick="window.featureTourPauseOnLinkClick()">
                                <span class="sp-tour-tool-open"><i class="fas fa-external-link-alt"></i></span>
                                <span class="sp-tour-tool-icon"><i class="fas <?php echo tourPluginIcon($pluginInfo['name']) ?>"></i></span>
                                <span class="sp-tour-tool-label"><?php echo htmlspecialchars($pluginInfo['label']) ?></span>
                            </a>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p><?php echo tourText('tour_no_plugins', 'No plugins are available to use on your account right now.') ?></p>
                <?php } ?>
            </div>

            <!-- Step 9: Done -->
            <div class="sp-wizard-panel" id="sp_tpanel_9">
                <h5><i class="fas fa-flag-checkered" style="margin-right:6px;"></i><?php echo tourText('tour_step9_heading', "You're All Set") ?></h5>
                <p><?php echo tourText('tour_step9_body', "That's the layout. Everything above will make a lot more sense once real data starts coming in.") ?></p>
                <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                    <i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>
                    <?php echo tourText('tour_step9_info', 'Want to see this again? Look for <strong>Setup Tour</strong> in the top menu bar.') ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sp-wizard-footer">
            <div class="sp-wizard-footer-left">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-skip" id="sp_tbtn_skip"
                    onclick="window.featureTourSkip()" title="<?php echo htmlspecialchars(tourText('tour_skip_tooltip', 'Skip this tour')) ?>">
                    <i class="fas fa-forward" style="margin-right:5px;"></i><?php echo $spText['button']['Skip'] ?? tourText('tour_skip', 'Skip') ?>
                </button>
            </div>
            <div class="sp-wizard-footer-right">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-cancel" id="sp_tbtn_back"
                    onclick="window.featureTourBack()" style="display:none;">
                    <i class="fas fa-arrow-left" style="margin-right:5px;"></i><?php echo tourText('tour_back', 'Back') ?>
                </button>
                <button type="button" class="sp-confirm-btn sp-confirm-btn-confirm" id="sp_tbtn_next"
                    onclick="window.featureTourNext()">
                    <i class="fas fa-arrow-right" style="margin-right:5px;"></i><?php echo tourText('tour_next', 'Next') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var TOTAL_STEPS = 9;
    var currentStep = 1;
    // set once the website step succeeds (or was already satisfied on
    // load) - lets Next skip re-validating a step that's already done,
    // and lets a keyword-only retry pass the existing website_id back
    // instead of re-submitting the website fields
    var tourCreatedWebsiteId = null;

    // strings the JS needs at runtime (button labels swapped in place,
    // badge text, dynamic hints/messages) - translated server-side same
    // as everything else in this view, injected here rather than
    // hardcoded so the whole tour (not just the initial HTML) follows
    // the active language
    var TOUR_I18N = {
        next: <?php echo json_encode(tourText('tour_next', 'Next')) ?>,
        getStarted: <?php echo json_encode(tourText('tour_get_started', 'Get Started')) ?>,
        connected: <?php echo json_encode(tourText('tour_connected', 'Connected')) ?>,
        notSetUp: <?php echo json_encode(tourText('tour_not_set_up', 'Not set up')) ?>,
        detected: <?php echo json_encode(tourText('tour_detected', 'Detected')) ?>,
        notDetected: <?php echo json_encode(tourText('tour_not_detected', 'Not detected yet')) ?>,
        websiteAdded: <?php echo json_encode(tourText('tour_website_added', 'Website and primary keyword added.')) ?>,
        socialNotAdded: <?php echo json_encode(tourText('tour_social_not_added', 'Social media link')) ?>,
        reviewNotAdded: <?php echo json_encode(tourText('tour_review_not_added', 'Review link')) ?>,
        notAddedSuffix: <?php echo json_encode(tourText('tour_not_added_suffix', 'not added: %s')) ?>,
        genericError: <?php echo json_encode(tourText('tour_generic_error', 'Something went wrong. Please try again.')) ?>,
        reviewHintDynamic: <?php echo json_encode(tourText('tour_review_hint_dynamic', 'The URL must contain "%s" (e.g. a %s link should include that word).')) ?>,
        addWebsiteHeading: <?php echo json_encode(tourText('tour_step2_heading', 'Add Your First Website')) ?>
    };

    // shown on auto-trigger (new user, tour not yet seen) AND when the
    // "Setup Tour" link is clicked manually later. startStep resumes a
    // user where they left off (feature_tour_step) - see topmenu.ctp.php,
    // which now computes and passes the same resume step for the manual
    // reopen link too, not just the auto-show script.
    window.featureTourShow = function(startStep) {
        currentStep = (startStep && startStep >= 1 && startStep <= TOTAL_STEPS) ? startStep : 1;
        _tourRender();
        $('#sp_tour_overlay').fadeIn(200);
    };

    window.featureTourNext = function() {
        if (currentStep === TOTAL_STEPS) {
            _tourDismiss(function() {
                $('#sp_tour_overlay').fadeOut(200);
            });
            return;
        }
        currentStep++;
        _tourRender();
        _tourSaveStep();
    };

    window.featureTourBack = function() {
        if (currentStep > 1) {
            currentStep--;
            _tourRender();
            _tourSaveStep();
        }
    };

    // Skip has the same effect as finishing on the last step - either
    // way the tour is marked seen and won't auto-show again
    window.featureTourSkip = function() {
        _tourDismiss(function() {
            $('#sp_tour_overlay').fadeOut(200);
        });
    };

    // Tools/Settings/Plugins/Cron/Seo Panel API/Dashboard steps link to
    // real, directly-navigable pages that open in a new tab
    // (seo-tools.php?menu_sec=... and admin-panel.php?menu_selected=...
    // &start_script=... - each is a full page with its own navbar/
    // sidebar that auto-loads the right tool/settings view, unlike
    // linking straight to e.g. aivisibility.php or settings.php on
    // their own, which render as a bare fragment with no chrome at all
    // since those controllers hardcode layout='ajax' - confirmed live).
    // The href does the actual navigation; this just saves the CURRENT
    // step and closes the overlay in the background, without blocking
    // the new tab from opening. Saves rather than dismisses - clicking
    // a link to go look at something is the single most common way a
    // user leaves the tour mid-flow, and if that also marked it
    // permanently "seen" (the original behavior here), the whole point
    // of step persistence (resume where you left off) would never
    // actually apply to the case it matters most for. Only Skip or
    // finishing the last step dismiss it for good.
    window.featureTourPauseOnLinkClick = function() {
        _tourSaveStep();
        $('#sp_tour_overlay').fadeOut(200);
    };

    function _tourDismiss(callback) {
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/feature_tour.php',
            type: 'POST',
            data: { sec: 'dismiss' },
            complete: function() {
                if (callback) callback();
            }
        });
    }

    function _tourSaveStep() {
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/feature_tour.php',
            type: 'POST',
            data: { sec: 'save_step', step: currentStep }
        });
    }

    // Step 2's "Add Your First Website" form - creates the website,
    // keyword, and optional social/review links in one request. On a
    // partial failure (website created but keyword rejected), the
    // returned website_id is remembered so a retry only re-attempts the
    // keyword, not the whole website again.
    window._tourSubmitWebsite = function() {
        var $btn = $('#tour_web_submit_btn');
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);
        $('.sp-tour-field-err').text('');
        $('#tour_web_general_err').empty();

        var payload = {
            sec: 'create_website_setup',
            name: $('#tour_web_name').val(),
            url: $('#tour_web_url').val(),
            keyword: $('#tour_web_keyword').val(),
            search_engine: $('#tour_web_se').val(),
            lang_code: $('#tour_web_lang').val(),
            country_code: $('#tour_web_country').val(),
            social_type: $('#tour_web_social_type').val(),
            social_url: $('#tour_web_social_url').val(),
            review_type: $('#tour_web_review_type').val(),
            review_url: $('#tour_web_review_url').val()
        };
        if (tourCreatedWebsiteId) {
            payload.website_id = tourCreatedWebsiteId;
        }

        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/feature_tour.php',
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'ok') {
                    tourCreatedWebsiteId = res.website_id;
                    var warningHtml = '';
                    if (res.warnings) {
                        $.each(res.warnings, function(kind, errs) {
                            $.each(errs, function(field, html) {
                                if (html) {
                                    var label = (kind === 'social') ? TOUR_I18N.socialNotAdded : TOUR_I18N.reviewNotAdded;
                                    warningHtml += '<div class="sp-tour-form-hint">' + label + ' ' + TOUR_I18N.notAddedSuffix.replace('%s', $('<div>').html(html).text()) + '</div>';
                                }
                            });
                        });
                    }
                    $('#sp_tpanel_2').html(
                        '<h5><i class="fas fa-globe" style="margin-right:6px;"></i>' + TOUR_I18N.addWebsiteHeading + '</h5>' +
                        '<div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">' +
                        '<i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>' +
                        TOUR_I18N.websiteAdded + '</div>' + warningHtml
                    );
                } else if (res.stage === 'website') {
                    _tourShowFieldErrors({ name: 'tour_web_err_name', url: 'tour_web_err_url' }, res.errors);
                    $btn.prop('disabled', false);
                } else if (res.stage === 'keyword') {
                    tourCreatedWebsiteId = res.website_id;
                    _tourShowFieldErrors({ name: 'tour_web_err_keyword', searchengines: 'tour_web_err_searchengines' }, res.errors);
                    $btn.prop('disabled', false);
                } else {
                    $('#tour_web_general_err').text(TOUR_I18N.genericError);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                $('#tour_web_general_err').text(TOUR_I18N.genericError);
                $btn.prop('disabled', false);
            }
        });
    };

    function _tourShowFieldErrors(fieldMap, errors) {
        if (!errors) return;
        $.each(errors, function(field, html) {
            if (!html) return;
            var slotId = fieldMap[field];
            if (slotId && $('#' + slotId).length) {
                $('#' + slotId).html(html);
            } else {
                $('#tour_web_general_err').html(html);
            }
        });
    }

    window._tourUpdateReviewHint = function() {
        var type = $('#tour_web_review_type').val();
        var $hint = $('#tour_web_review_hint');
        if (!type) {
            $hint.hide();
        } else {
            $hint.show().text(TOUR_I18N.reviewHintDynamic.replace('%s', type).replace('%s', type));
        }
    };

    // the connection badges are computed server-side when the tour first
    // renders - if the user saves DataForSEO/Google/etc. credentials (or
    // runs cron) in another tab and comes back here without reloading,
    // those badges would otherwise stay stale until their next login.
    // This re-checks the live state (a fresh request re-runs
    // sp-load.php, so nothing here is cached) and updates just the
    // connection half of each badge in place, leaving the Important/
    // Optional badge and the rest of the row untouched.
    window.featureTourRefreshConnections = function() {
        var $btn = $('#sp_tour_refresh_btn');
        if ($btn.hasClass('sp-tour-refreshing')) return;
        $btn.addClass('sp-tour-refreshing');
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/feature_tour.php',
            type: 'GET',
            data: { sec: 'connection_status' },
            dataType: 'json',
            success: function(status) {
                $.each(status, function(service, isConnected) {
                    var $slot = $('.sp-tour-badges[data-service="' + service + '"] .sp-tour-connection-badge');
                    if (!$slot.length) return;
                    var connectedLabel = (service === 'cron') ? TOUR_I18N.detected : TOUR_I18N.connected;
                    var pendingLabel = (service === 'cron') ? TOUR_I18N.notDetected : TOUR_I18N.notSetUp;
                    $slot.html(isConnected
                        ? '<span class="sp-tour-badge sp-tour-badge-connected"><i class="fas fa-check-circle"></i> ' + connectedLabel + '</span>'
                        : '<span class="sp-tour-badge sp-tour-badge-pending">' + pendingLabel + '</span>');
                });
            },
            complete: function() {
                $btn.removeClass('sp-tour-refreshing');
            }
        });
    };

    function _tourRender() {
        $('.sp-wizard-panel').removeClass('active');
        $('#sp_tpanel_' + currentStep).addClass('active');

        for (var i = 1; i <= TOTAL_STEPS; i++) {
            var $dot  = $('#sp_tdot_' + i);
            var $line = $('#sp_tline_' + i);
            $dot.removeClass('active done');
            if ($line.length) $line.removeClass('done');
            if (i < currentStep) {
                $dot.addClass('done').html('<i class="fas fa-check" style="font-size:10px;"></i>');
                if ($line.length) $line.addClass('done');
            } else if (i === currentStep) {
                $dot.addClass('active').html(i);
            } else {
                $dot.html(i);
            }
        }

        $('#sp_tbtn_back').toggle(currentStep > 1);
        $('#sp_tbtn_skip').toggle(currentStep < TOTAL_STEPS);

        var $next = $('#sp_tbtn_next');
        if (currentStep === TOTAL_STEPS) {
            $next.html('<i class="fas fa-check" style="margin-right:5px;"></i>' + TOUR_I18N.getStarted);
        } else {
            $next.html('<i class="fas fa-arrow-right" style="margin-right:5px;"></i>' + TOUR_I18N.next);
        }
    }
})();
</script>
