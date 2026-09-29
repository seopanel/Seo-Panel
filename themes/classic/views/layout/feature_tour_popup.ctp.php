<div class="sp-wizard-overlay" id="sp_tour_overlay">
    <div class="sp-wizard-box">

        <!-- Header with step progress -->
        <div class="sp-wizard-header">
            <h4><i class="fas fa-compass" style="margin-right:8px;"></i>Quick Tour</h4>
            <div class="sp-wizard-steps" id="sp_tour_steps">
                <?php for ($i = 1; $i <= 6; $i++) { ?>
                <div class="sp-wizard-step-item">
                    <div class="sp-wizard-step-dot" id="sp_tdot_<?php echo $i ?>"><?php echo $i ?></div>
                    <?php if ($i < 6) { ?><div class="sp-wizard-step-line" id="sp_tline_<?php echo $i ?>"></div><?php } ?>
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

            <!-- Step 3: SEO Tools -->
            <div class="sp-wizard-panel" id="sp_tpanel_3">
                <h5><i class="fas fa-tools" style="margin-right:6px;"></i>SEO Tools</h5>
                <p>The <strong>Tools</strong> menu is where the actual work happens - twelve tools in one place. Click any of these to open it:</p>
                <div class="sp-tour-chip-list">
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('aivisibility.php')"><i class="fas fa-robot"></i> AI Visibility</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('rank.php')"><i class="fas fa-key"></i> Keyword Position Checker</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('siteauditor.php')"><i class="fas fa-tasks"></i> Site Auditor</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('backlinks.php')"><i class="fas fa-link"></i> Backlinks Checker</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('webmaster-tools.php')"><i class="fas fa-globe"></i> Webmaster Tools</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('moz.php')"><i class="fas fa-search-location"></i> Rank Checker</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('directories.php')"><i class="fas fa-folder-open"></i> Directory Submission</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('saturationchecker.php')"><i class="fas fa-server"></i> Search Engine Saturation</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('pagespeed.php')"><i class="fas fa-tachometer-alt"></i> PageSpeed Insights</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('social_media.php')"><i class="fas fa-share-alt"></i> Social Media Checker</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('analytics.php')"><i class="fas fa-chart-area"></i> Website Analytics</a>
                    <a class="sp-tour-chip" href="javascript:void(0);" onclick="window.featureTourGoTo('review.php')"><i class="fas fa-star"></i> Review Manager</a>
                </div>
            </div>

            <!-- Step 4: Settings - where your integrations live -->
            <div class="sp-wizard-panel" id="sp_tpanel_4">
                <h5><i class="fas fa-cog" style="margin-right:6px;"></i>Settings: Where Your Integrations Live</h5>
                <p>SEO Panel has a lot of settings because it connects to a lot of services - here's the map so you don't have to hunt for it later. Click any row to go straight there:</p>
                <div class="sp-tour-link-list">
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php')">
                        <span class="sp-tour-link-icon"><i class="fas fa-sliders-h"></i></span>
                        <span class="sp-tour-link-text"><strong>System</strong><small>Language, timezone, pagination, and other app-wide defaults</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=dataforseo')">
                        <span class="sp-tour-link-icon"><i class="fas fa-database"></i></span>
                        <span class="sp-tour-link-text"><strong>DataForSEO</strong><small>The data provider behind rank checking and SERP data</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=moz')">
                        <span class="sp-tour-link-icon"><i class="fas fa-chart-bar"></i></span>
                        <span class="sp-tour-link-text"><strong>MOZ</strong><small>Domain Authority, Page Authority, and Spam Score</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=google')">
                        <span class="sp-tour-link-icon"><i class="fab fa-google"></i></span>
                        <span class="sp-tour-link-text"><strong>Google</strong><small>Connect Analytics and Search Console</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=mail')">
                        <span class="sp-tour-link-icon"><i class="fas fa-envelope"></i></span>
                        <span class="sp-tour-link-text"><strong>Mail</strong><small>SMTP/SendGrid, so scheduled reports actually get delivered</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=local_ai')">
                        <span class="sp-tour-link-icon"><i class="fas fa-brain"></i></span>
                        <span class="sp-tour-link-text"><strong>Local AI</strong><small>Point AI-powered features at your own Ollama server</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?category=seopanel_api')">
                        <span class="sp-tour-link-icon"><i class="fas fa-plug"></i></span>
                        <span class="sp-tour-link-text"><strong>Seo Panel API</strong><small>Unlocks additional rank/SERP data services</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                    <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?sec=proxysettings')">
                        <span class="sp-tour-link-icon"><i class="fas fa-network-wired"></i></span>
                        <span class="sp-tour-link-text"><strong>Proxy</strong><small>Proxies used for crawling and directory submission</small></span>
                        <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                    </a>
                </div>
            </div>

            <!-- Step 5: Plugins -->
            <div class="sp-wizard-panel" id="sp_tpanel_5">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i>Plugins</h5>
                <p>The <strong>Plugins</strong> menu extends SEO Panel beyond the core tools - things like article submission/spinning, a quick web proxy, and an SEO diary for notes.</p>
                <a class="sp-tour-link-row" href="javascript:void(0);" onclick="window.featureTourGoTo('settings.php?sec=aboutus')">
                    <span class="sp-tour-link-icon"><i class="fas fa-th-large"></i></span>
                    <span class="sp-tour-link-text"><strong>Browse Plugins</strong><small>See what's installed, and find more to add</small></span>
                    <i class="fas fa-arrow-right sp-tour-link-arrow"></i>
                </a>
            </div>

            <!-- Step 6: Done -->
            <div class="sp-wizard-panel" id="sp_tpanel_6">
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
    var TOTAL_STEPS = 6;
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

    // Tools/Settings/Plugins steps link to real pages, but every one of
    // them (aivisibility.php, settings.php, rank.php, ...) hardcodes
    // $controller->layout = 'ajax' - they only ever render correctly
    // when loaded via this same scriptDoLoad() AJAX-into-#content
    // mechanism every other menu link in the app already uses. A plain
    // <a href> (even with target="_blank") hits that file directly and
    // gets back a bare content fragment with no navbar/sidebar, since
    // the layout is skipped entirely for that request - confirmed live.
    window.featureTourGoTo = function(url) {
        _tourDismiss();
        $('#sp_tour_overlay').fadeOut(200);
        if (document.getElementById('content') && typeof scriptDoLoad === 'function') {
            scriptDoLoad(url, 'content');
        } else {
            // no #content on the current page to inject into (shouldn't
            // happen in normal use - every page reachable while logged
            // in is itself loaded into that same div) - falls back to a
            // real navigation rather than doing nothing
            window.location.href = '<?php echo SP_WEBPATH ?>/' + url;
        }
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
