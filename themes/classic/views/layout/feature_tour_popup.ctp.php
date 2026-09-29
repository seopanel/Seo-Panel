<div class="sp-wizard-overlay" id="sp_tour_overlay">
    <div class="sp-wizard-box">

        <!-- Header with step progress -->
        <div class="sp-wizard-header">
            <h4 class="sp-tour-header-row">
                <span><i class="fas fa-compass" style="margin-right:8px;"></i>Quick Tour</span>
                <button type="button" class="sp-tour-refresh-btn" id="sp_tour_refresh_btn" onclick="window.featureTourRefreshConnections()" title="Just saved something in another tab? Refresh to update the connection badges below">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </h4>
            <div class="sp-wizard-steps" id="sp_tour_steps">
                <?php for ($i = 1; $i <= 7; $i++) { ?>
                <div class="sp-wizard-step-item">
                    <div class="sp-wizard-step-dot" id="sp_tdot_<?php echo $i ?>"><?php echo $i ?></div>
                    <?php if ($i < 7) { ?><div class="sp-wizard-step-line" id="sp_tline_<?php echo $i ?>"></div><?php } ?>
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
                    This is a 30-second tour of where everything lives. Skip it anytime, or take it again later from the <strong>Help</strong> menu.
                </div>
            </div>

            <!-- Step 2: Dashboard -->
            <div class="sp-wizard-panel" id="sp_tpanel_2">
                <h5><i class="fas fa-chart-line" style="margin-right:6px;"></i>Your Dashboard</h5>
                <p>This is the screen you're looking at right now. Pick a website from the dropdown to see its ranking trends, top keywords, and recent activity at a glance.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Every tool you connect (Analytics, Social Media, Reviews...) gets its own dashboard tab here too.
                </div>
            </div>

            <?php
            // SettingsController::isLocalAIEnabled() below - not guaranteed
            // loaded yet for a non-admin user (default.ctp.php's own
            // include_once for it is gated behind isAdmin(), further down)
            include_once(SP_CTRLPATH . "/settings.ctrl.php");
            // admin-panel.php is Settings' own shell page (full navbar +
            // left menu, same as seo-tools.php is for the Tools menu
            // below) - it reads menu_selected (which left-menu item to
            // highlight) and start_script (which inner settings.php view
            // to auto-load into it) as plain query params, so any
            // settings.php URL can be reached this way, not just the
            // handful admin-panel.php's own sec= shortcuts cover.
            function tourSettingsLink($startScript) {
                return SP_WEBPATH . '/admin-panel.php?menu_selected=settings&start_script=' . urlencode($startScript);
            }
            // "already configured" per category - the same constants the
            // app itself gates real functionality on (SettingsController::
            // isSpApiEnabled()/isDFSEnabled()/isLocalAIEnabled(), and the
            // matching credential settings for MOZ/Google/Mail/Proxy) -
            // a cheap defined()/non-empty check, no live API calls.
            $tourSpApiConnected = defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED;
            $tourDfsConnected = defined('SP_DFS_API_LOGIN') && SP_DFS_API_LOGIN !== '' && defined('SP_DFS_API_PASSWORD') && SP_DFS_API_PASSWORD !== '';
            $tourMozConnected = defined('SP_MOZ_API_ACCESS_ID') && SP_MOZ_API_ACCESS_ID !== '' && defined('SP_MOZ_API_SECRET') && SP_MOZ_API_SECRET !== '';
            $tourGoogleConnected = defined('SP_GOOGLE_API_CLIENT_ID') && SP_GOOGLE_API_CLIENT_ID !== '' && defined('SP_GOOGLE_API_CLIENT_SECRET') && SP_GOOGLE_API_CLIENT_SECRET !== '';
            $tourMailConnected = defined('SP_SMTP_MAIL') && SP_SMTP_MAIL && defined('SP_SMTP_HOST') && SP_SMTP_HOST !== '';
            $tourLocalAiConnected = SettingsController::isLocalAIEnabled();
            $tourProxyConnected = defined('SP_ENABLE_PROXY') && SP_ENABLE_PROXY;

            function tourConnectionBadgeHtml($isConnected) {
                if ($isConnected) {
                    return '<span class="sp-tour-badge sp-tour-badge-connected"><i class="fas fa-check-circle"></i> Connected</span>';
                }
                return '<span class="sp-tour-badge sp-tour-badge-pending">Not set up</span>';
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
            function tourBadges($service, $isImportant, $isConnected = null) {
                $html = '<span class="sp-tour-badges" data-service="' . $service . '">';
                $html .= tourImportanceBadge($isImportant);
                if ($isConnected !== null) {
                    $html .= '<span class="sp-tour-connection-badge">' . tourConnectionBadgeHtml($isConnected) . '</span>';
                }
                $html .= '</span>';
                return $html;
            }
            ?>

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

            <!-- Step 5: SEO Tools -->
            <div class="sp-wizard-panel" id="sp_tpanel_5">
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

            <!-- Step 6: Plugins -->
            <div class="sp-wizard-panel" id="sp_tpanel_6">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i>Plugins</h5>
                <p>The <strong>Plugins</strong> menu extends SEO Panel beyond the core tools - things like article submission/spinning, a quick web proxy, and an SEO diary for notes.</p>
                <a class="sp-tour-link-row" href="<?php echo SP_WEBPATH . '/admin-panel.php?menu_selected=about-us&start_script=' . urlencode('settings.php?sec=aboutus') ?>" target="_blank" onclick="window.featureTourNotifyDismiss()">
                    <span class="sp-tour-link-icon"><i class="fas fa-th-large"></i></span>
                    <span class="sp-tour-link-text"><strong>Browse Plugins</strong><small>See what's installed, and find more to add</small></span>
                    <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                </a>
            </div>

            <!-- Step 7: Done -->
            <div class="sp-wizard-panel" id="sp_tpanel_7">
                <h5><i class="fas fa-flag-checkered" style="margin-right:6px;"></i>You're All Set</h5>
                <p>That's the layout. Add a website to get started, and everything above will make a lot more sense once real data starts coming in.</p>
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
    var TOTAL_STEPS = 7;
    var currentStep = 1;

    // shown on auto-trigger (new user, tour not yet seen) AND when the
    // "Take a tour" link is clicked manually later - either way this
    // resets to step 1, since it's a short linear tour, not a resumable
    // checklist
    window.featureTourShow = function() {
        currentStep = 1;
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
    };

    window.featureTourBack = function() {
        if (currentStep > 1) {
            currentStep--;
            _tourRender();
        }
    };

    // Skip has the same effect as finishing on the last step - either
    // way the tour is marked seen and won't auto-show again
    window.featureTourSkip = function() {
        _tourDismiss(function() {
            $('#sp_tour_overlay').fadeOut(200);
        });
    };

    // Tools/Settings/Plugins steps link to real, directly-navigable
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

    // the connection badges are computed server-side when the tour first
    // renders - if the user saves DataForSEO/Google/etc. credentials in
    // another tab and comes back here without reloading, those badges
    // would otherwise stay stale until their next login. This re-checks
    // the live settings (a fresh request re-runs sp-load.php, so the
    // constants it reads are never stale) and updates just the
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
                    $slot.html(isConnected
                        ? '<span class="sp-tour-badge sp-tour-badge-connected"><i class="fas fa-check-circle"></i> Connected</span>'
                        : '<span class="sp-tour-badge sp-tour-badge-pending">Not set up</span>');
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
