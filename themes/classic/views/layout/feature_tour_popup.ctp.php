<div class="sp-wizard-overlay" id="sp_tour_overlay">
    <div class="sp-wizard-box">

        <!-- Header with step progress -->
        <div class="sp-wizard-header">
            <h4 class="sp-tour-header-row">
                <span><i class="fas fa-compass" style="margin-right:8px;"></i>Setup Tour</span>
                <button type="button" class="sp-tour-refresh-btn" id="sp_tour_refresh_btn" onclick="window.featureTourRefreshConnections()" title="Just saved something in another tab? Refresh to update the connection badges below">
                    <i class="fas fa-sync-alt"></i> Refresh
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
                <h5><i class="fas fa-hand-sparkles" style="margin-right:6px;"></i>Welcome to SEO Panel</h5>
                <p>SEO Panel tracks rankings, audits your sites, checks backlinks, and monitors how you show up in AI answer engines - all from one self-hosted control room.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    This is a short tour of where everything lives, and gets your first website set up along the way. Skip it anytime, or take it again later from the <strong>Help</strong> menu.
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

            function tourConnectionBadgeHtml($isConnected, $connectedLabel = 'Connected', $pendingLabel = 'Not set up') {
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
                    return '<span class="sp-tour-badge sp-tour-badge-important">Important</span>';
                }
                return '<span class="sp-tour-badge sp-tour-badge-optional">Optional</span>';
            }
            // wraps both badges together, tagged with data-service so the
            // Refresh button (see the <script> below) can find and update
            // just the connection half in place, without touching the
            // importance badge or re-rendering the row
            function tourBadges($service, $isImportant, $isConnected = null, $connectedLabel = 'Connected', $pendingLabel = 'Not set up') {
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
            if ($tourUserId) {
                $tourWebsiteCountRow = (new WebsiteController())->db->select("SELECT COUNT(*) as c FROM websites WHERE user_id=" . intval($tourUserId), true);
                $tourWebsiteCount = intval($tourWebsiteCountRow['c'] ?? 0);
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
                <h5><i class="fas fa-globe" style="margin-right:6px;"></i>Add Your First Website</h5>
                <?php if ($tourWebsiteCount > 0) { ?>
                    <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                        <i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>
                        You already have <?php echo $tourWebsiteCount ?> website<?php echo $tourWebsiteCount == 1 ? '' : 's' ?> set up - nothing to do here.
                    </div>
                <?php } else { ?>
                    <p>Everything below - the dashboard, rank tracking, audits - needs at least one website to work with. Takes a few seconds:</p>
                    <div id="tour_web_general_err"></div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_name">Website Name <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_name" class="form-control" placeholder="e.g. My Company Website">
                        <div class="sp-tour-field-err" id="tour_web_err_name"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_url">Website URL <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_url" class="form-control" placeholder="https://example.com">
                        <div class="sp-tour-field-err" id="tour_web_err_url"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_keyword">Primary Keyword <span class="sp-tour-required">*</span></label>
                        <input type="text" id="tour_web_keyword" class="form-control" placeholder="e.g. seo software">
                        <div class="sp-tour-field-err" id="tour_web_err_keyword"></div>
                    </div>
                    <div class="sp-tour-form-row">
                        <label for="tour_web_se">Search Engine <span class="sp-tour-required">*</span></label>
                        <select id="tour_web_se" class="form-control custom-select">
                            <?php foreach ($tourSearchEngines as $seInfo) { ?>
                                <option value="<?php echo $seInfo['id'] ?>"><?php echo $seInfo['domain'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="sp-tour-field-err" id="tour_web_err_searchengines"></div>
                    </div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_lang">Language</label>
                            <select id="tour_web_lang" class="form-control custom-select">
                                <option value="">-- optional --</option>
                                <?php foreach ($tourLanguages as $langInfo) { ?>
                                    <option value="<?php echo $langInfo['lang_code'] ?>"><?php echo $langInfo['lang_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="sp-tour-form-row">
                            <label for="tour_web_country">Country</label>
                            <select id="tour_web_country" class="form-control custom-select">
                                <option value="">-- optional --</option>
                                <?php foreach ($tourCountries as $countryCode => $countryName) { ?>
                                    <option value="<?php echo $countryCode ?>"><?php echo $countryName ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="sp-tour-form-divider">Optional extras</div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_social_type">Social Media</label>
                            <select id="tour_web_social_type" class="form-control custom-select">
                                <option value="">-- none --</option>
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
                            <input type="text" id="tour_web_social_url" class="form-control" placeholder="Profile URL">
                        </div>
                    </div>
                    <div class="sp-tour-field-err" id="tour_web_err_social"></div>
                    <div class="sp-tour-form-row-pair">
                        <div class="sp-tour-form-row">
                            <label for="tour_web_review_type">Review Link</label>
                            <select id="tour_web_review_type" class="form-control custom-select" onchange="window._tourUpdateReviewHint()">
                                <option value="">-- none --</option>
                                <option value="google">Google My Business</option>
                                <option value="yelp">Yelp</option>
                                <option value="trustpilot">Trustpilot</option>
                                <option value="tripadvisor">TripAdvisor</option>
                            </select>
                        </div>
                        <div class="sp-tour-form-row">
                            <label for="tour_web_review_url">&nbsp;</label>
                            <input type="text" id="tour_web_review_url" class="form-control" placeholder="Review page URL">
                        </div>
                    </div>
                    <div class="sp-tour-form-hint" id="tour_web_review_hint">The URL must contain the platform's name, e.g. a Yelp link should include "yelp".</div>
                    <div class="sp-tour-field-err" id="tour_web_err_review"></div>

                    <button type="button" class="sp-confirm-btn sp-confirm-btn-confirm" id="tour_web_submit_btn" onclick="window._tourSubmitWebsite()" style="margin-top:10px;">
                        <i class="fas fa-plus" style="margin-right:5px;"></i>Add Website
                    </button>
                <?php } ?>
            </div>

            <!-- Step 3: Seo Panel API - its own step, called out separately
                 from the general Settings list below since it's the main
                 source of rank/SERP data for accounts without their own
                 DataForSEO or MOZ keys -->
            <div class="sp-wizard-panel" id="sp_tpanel_3">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i>Seo Panel API</h5>
                <p>The fastest way to get real rank and SERP data flowing without hunting down your own DataForSEO or MOZ keys - free to register, no credit card.</p>
                <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=seopanel_api') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                    <span class="sp-tour-link-icon"><i class="fas fa-plug"></i></span>
                    <span class="sp-tour-link-text"><strong>Seo Panel API</strong><small>Rank tracking and SERP data, ready in a couple of minutes</small></span>
                    <?php echo tourBadges('seopanel_api', true, $tourSpApiConnected) ?>
                    <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                </a>
            </div>

            <!-- Step 4: Settings - where your integrations live -->
            <div class="sp-wizard-panel" id="sp_tpanel_4">
                <h5><i class="fas fa-cog" style="margin-right:6px;"></i>Settings: Where Your Integrations Live</h5>
                <p>Worth doing before the Tools menu next: without these connected, several tools won't have any real data to show yet. Click any row to go straight there in a new tab:</p>
                <div class="sp-tour-link-list">
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-sliders-h"></i></span>
                        <span class="sp-tour-link-text"><strong>System</strong><small>Language, timezone, pagination, and other app-wide defaults</small></span>
                        <?php echo tourBadges('system', false) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=dataforseo') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-database"></i></span>
                        <span class="sp-tour-link-text"><strong>DataForSEO</strong><small>The data provider behind rank checking and SERP data</small></span>
                        <?php echo tourBadges('dataforseo', true, $tourDfsConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=moz') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-chart-bar"></i></span>
                        <span class="sp-tour-link-text"><strong>MOZ</strong><small>Domain Authority, Page Authority, and Spam Score</small></span>
                        <?php echo tourBadges('moz', true, $tourMozConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=google') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fab fa-google"></i></span>
                        <span class="sp-tour-link-text"><strong>Google</strong><small>Connect Analytics and Search Console</small></span>
                        <?php echo tourBadges('google', true, $tourGoogleConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=mail') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-envelope"></i></span>
                        <span class="sp-tour-link-text"><strong>Mail</strong><small>Scheduled reports, password resets, and registration emails all go through here</small></span>
                        <?php echo tourBadges('mail', true, $tourMailConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?category=local_ai') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-brain"></i></span>
                        <span class="sp-tour-link-text"><strong>Local AI</strong><small>Point AI-powered features at your own Ollama server</small></span>
                        <?php echo tourBadges('local_ai', false, $tourLocalAiConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('settings.php?sec=proxysettings') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-network-wired"></i></span>
                        <span class="sp-tour-link-text"><strong>Proxy</strong><small>Proxies used for crawling and directory submission</small></span>
                        <?php echo tourBadges('proxy', false, $tourProxyConnected) ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                </div>
            </div>

            <!-- Step 5: Cron Job - nothing scheduled (rank checks, audits,
                 reports) runs at all without this. Badge is a genuine live
                 check (cron_run_log), not just a setting's presence.
                 cron.php requires checkAdminLoggedIn() - a non-admin user
                 can't reach that page at all, so they get an explanatory
                 note instead of a link they'd just be denied on. -->
            <div class="sp-wizard-panel" id="sp_tpanel_5">
                <h5><i class="fas fa-clock" style="margin-right:6px;"></i>Set Up the Cron Job</h5>
                <p>SEO Panel checks rankings, runs audits, and generates reports on a schedule - but only once your server is actually calling <code>cron.php</code>. Nothing above matters if this isn't running.</p>
                <?php if (isAdmin()) { ?>
                    <a class="sp-tour-link-row" href="<?php echo tourSettingsLink('cron.php?sec=croncommand', 'report-manager') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                        <span class="sp-tour-link-icon"><i class="fas fa-terminal"></i></span>
                        <span class="sp-tour-link-text"><strong>Cron Command</strong><small>The exact command to add to your server's crontab</small></span>
                        <?php echo tourBadges('cron', true, $tourCronConnected, 'Detected', 'Not detected yet') ?>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                <?php } else { ?>
                    <div class="sp-wizard-info-box">
                        <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                        This is a server setting, so only an admin on your account can set it up. Current status:
                        <?php echo tourBadges('cron', true, $tourCronConnected, 'Detected', 'Not detected yet') ?>
                    </div>
                <?php } ?>
            </div>

            <!-- Step 6: Dashboard -->
            <div class="sp-wizard-panel" id="sp_tpanel_6">
                <h5><i class="fas fa-chart-line" style="margin-right:6px;"></i>Your Dashboard</h5>
                <p>This is the screen you land on after logging in. Pick a website from the dropdown to see its ranking trends, top keywords, and recent activity at a glance.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Every tool you connect (Analytics, Social Media, Reviews...) gets its own dashboard tab here too.
                </div>
            </div>

            <!-- Step 7: SEO Tools -->
            <div class="sp-wizard-panel" id="sp_tpanel_7">
                <h5><i class="fas fa-tools" style="margin-right:6px;"></i>SEO Tools</h5>
                <p>The <strong>Tools</strong> menu is where the actual work happens - twelve tools in one place. Click any of these to open it in a new tab:</p>
                <div class="sp-tour-chip-list">
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=ai-visibility" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-robot"></i> AI Visibility</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=keyword-position-checker" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-key"></i> Keyword Position Checker</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=site-auditor" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-tasks"></i> Site Auditor</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=backlink-checker" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-link"></i> Backlinks Checker</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=webmaster-tools" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-globe"></i> Webmaster Tools</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=rank-checker" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-search-location"></i> Rank Checker</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=directory-submission" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-folder-open"></i> Directory Submission</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=saturation-checker" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-server"></i> Search Engine Saturation</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=pagespeed" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-tachometer-alt"></i> PageSpeed Insights</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=sm-checker" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-share-alt"></i> Social Media Checker</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=web-analytics" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-chart-area"></i> Website Analytics</a>
                    <a class="sp-tour-chip" href="<?php echo SP_WEBPATH ?>/seo-tools.php?menu_sec=review-manager" target="_blank" onclick="window.featureTourNotifyDismiss()"><i class="fas fa-star"></i> Review Manager</a>
                </div>
            </div>

            <!-- Step 8: Plugins -->
            <div class="sp-wizard-panel" id="sp_tpanel_8">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i>Plugins</h5>
                <p>The <strong>Plugins</strong> menu extends SEO Panel beyond the core tools - things like article submission/spinning, a quick web proxy, and an SEO diary for notes.</p>
                <a class="sp-tour-link-row" href="<?php echo SP_WEBPATH . '/admin-panel.php?menu_selected=about-us&start_script=' . urlencode('settings.php?sec=aboutus') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                    <span class="sp-tour-link-icon"><i class="fas fa-th-large"></i></span>
                    <span class="sp-tour-link-text"><strong>Browse Plugins</strong><small>See what's installed, and find more to add</small></span>
                    <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                </a>
            </div>

            <!-- Step 9: Done -->
            <div class="sp-wizard-panel" id="sp_tpanel_9">
                <h5><i class="fas fa-flag-checkered" style="margin-right:6px;"></i>You're All Set</h5>
                <p>That's the layout. Everything above will make a lot more sense once real data starts coming in.</p>
                <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                    <i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>
                    Want to see this again? Look for <strong>Take a tour</strong> in the Help menu.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sp-wizard-footer">
            <div class="sp-wizard-footer-left">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-skip" id="sp_tbtn_skip"
                    onclick="window.featureTourSkip()" title="Skip this tour">
                    <i class="fas fa-forward" style="margin-right:5px;"></i>Skip
                </button>
            </div>
            <div class="sp-wizard-footer-right">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-cancel" id="sp_tbtn_back"
                    onclick="window.featureTourBack()" style="display:none;">
                    <i class="fas fa-arrow-left" style="margin-right:5px;"></i>Back
                </button>
                <button type="button" class="sp-confirm-btn sp-confirm-btn-confirm" id="sp_tbtn_next"
                    onclick="window.featureTourNext()">
                    <i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next
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

    // shown on auto-trigger (new user, tour not yet seen) AND when the
    // "Take a tour" link is clicked manually later. startStep resumes a
    // new user where they left off (feature_tour_step); the manual
    // reopen always passes 1 (see featureTourShow && featureTourShow()
    // below with no argument, which defaults to step 1).
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

    // Tools/Settings/Plugins/Cron steps link to real, directly-navigable
    // pages that open in a new tab (seo-tools.php?menu_sec=... and
    // admin-panel.php?menu_selected=...&start_script=... - each is a
    // full page with its own navbar/sidebar that auto-loads the right
    // tool/settings view, unlike linking straight to e.g. aivisibility.php
    // or settings.php on their own, which render as a bare fragment with
    // no chrome at all since those controllers hardcode layout='ajax' -
    // confirmed live). The href does the actual navigation; this just
    // marks the tour seen in the background so it won't auto-show again,
    // without blocking the new tab from opening.
    window.featureTourNotifyDismiss = function() {
        _tourDismiss();
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
                                if (html) warningHtml += '<div class="sp-tour-form-hint">' + (kind === 'social' ? 'Social media link' : 'Review link') + ' not added: ' + $('<div>').html(html).text() + '</div>';
                            });
                        });
                    }
                    $('#sp_tpanel_2').html(
                        '<h5><i class="fas fa-globe" style="margin-right:6px;"></i>Add Your First Website</h5>' +
                        '<div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">' +
                        '<i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>' +
                        'Website and primary keyword added.</div>' + warningHtml
                    );
                } else if (res.stage === 'website') {
                    _tourShowFieldErrors({ name: 'tour_web_err_name', url: 'tour_web_err_url' }, res.errors);
                    $btn.prop('disabled', false);
                } else if (res.stage === 'keyword') {
                    tourCreatedWebsiteId = res.website_id;
                    _tourShowFieldErrors({ name: 'tour_web_err_keyword', searchengines: 'tour_web_err_searchengines' }, res.errors);
                    $btn.prop('disabled', false);
                } else {
                    $('#tour_web_general_err').text('Something went wrong. Please try again.');
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                $('#tour_web_general_err').text('Something went wrong. Please try again.');
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
            $hint.show().text('The URL must contain "' + type + '" (e.g. a ' + type + ' link should include that word).');
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
                    var connectedLabel = (service === 'cron') ? 'Detected' : 'Connected';
                    var pendingLabel = (service === 'cron') ? 'Not detected yet' : 'Not set up';
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
            $next.html('<i class="fas fa-check" style="margin-right:5px;"></i>Get Started');
        } else {
            $next.html('<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next');
        }
    }
})();
</script>
