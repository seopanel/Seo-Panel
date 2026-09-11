<div class="sp-wizard-overlay" id="sp_wizard_overlay">
    <div class="sp-wizard-box">

        <!-- Header with step progress -->
        <div class="sp-wizard-header">
            <h4><i class="fas fa-magic" style="margin-right:8px;"></i>Initial Setup Wizard</h4>
            <div class="sp-wizard-steps" id="sp_wizard_steps">
                <?php for ($i = 1; $i <= 8; $i++) { ?>
                <div class="sp-wizard-step-item">
                    <div class="sp-wizard-step-dot" id="sp_wdot_<?php echo $i ?>"><?php echo $i ?></div>
                    <?php if ($i < 8) { ?><div class="sp-wizard-step-line" id="sp_wline_<?php echo $i ?>"></div><?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Body — one panel per step -->
        <div class="sp-wizard-body">
            <div id="sp_wizard_message"></div>

            <!-- Step 1: Website Creation -->
            <div class="sp-wizard-panel" id="sp_wpanel_1">
                <h5><i class="fas fa-globe" style="margin-right:6px;"></i>Add Your First Website</h5>
                <div id="wz_site_form">
                    <p>Enter the details of the website you want to manage with SEO Panel. This step is required to continue.</p>
                    <div class="form-group">
                        <label><strong>Website Name <span class="text-danger">*</span></strong></label>
                        <input type="text" id="wz_site_name" class="form-control" placeholder="e.g. My Company Website">
                    </div>
                    <div class="form-group mt-2">
                        <label><strong>Website URL <span class="text-danger">*</span></strong></label>
                        <input type="text" id="wz_site_url" class="form-control" placeholder="https://example.com">
                    </div>
                </div>
                <div id="wz_site_done" style="display:none;">
                    <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                        <i class="fas fa-check-circle" style="color:#34a853; margin-right:6px;"></i>
                        <strong>Website already added.</strong> Click <strong>Next</strong> to continue with the remaining setup steps.
                    </div>
                </div>
            </div>

            <!-- Step 2: SP API -->
            <div class="sp-wizard-panel" id="sp_wpanel_2">
                <h5><i class="fas fa-plug" style="margin-right:6px;"></i>SEO Panel API</h5>
                <p>Connect to the SEO Panel API to unlock additional SEO data services including keyword rank tracking and SERP analysis.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Registration is free. You can configure this later from <strong>Settings &rarr; SEO Panel API</strong>.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/settings.php?category=seopanel_api" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Configure SP API
                </a>
            </div>

            <!-- Step 3: Google Settings -->
            <div class="sp-wizard-panel" id="sp_wpanel_3">
                <h5><i class="fab fa-google" style="margin-right:6px;"></i>Google Integration</h5>
                <p>Connect Google Analytics and Google Search Console to track website traffic and search performance directly in SEO Panel.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    You will need a Google API Client ID and Secret. Configure from <strong>Settings &rarr; Google</strong>.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/settings.php?category=google" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Configure Google
                </a>
            </div>

            <!-- Step 4: Mail Settings -->
            <div class="sp-wizard-panel" id="sp_wpanel_4">
                <h5><i class="fas fa-envelope" style="margin-right:6px;"></i>Mail Settings</h5>
                <p>Set up outgoing email so SEO Panel can send scheduled reports, alerts, and notifications to you and your clients.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Supports SMTP and SendGrid. Configure from <strong>Settings &rarr; Mail</strong>.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/settings.php?category=mail" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Configure Mail
                </a>
            </div>

            <!-- Step 5: Add Keywords -->
            <div class="sp-wizard-panel" id="sp_wpanel_5">
                <h5><i class="fas fa-key" style="margin-right:6px;"></i>Add Keywords</h5>
                <p>Add the search keywords you want to track for your website. SEO Panel will monitor their ranking positions in search engines.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    You can add keywords from the <strong>Keywords</strong> section on your website's dashboard.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/keywords.php?sec=add" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Add Keywords
                </a>
            </div>

            <!-- Step 6: Social Media -->
            <div class="sp-wizard-panel" id="sp_wpanel_6">
                <h5><i class="fas fa-share-alt" style="margin-right:6px;"></i>Social Media Links</h5>
                <p>Link your social media profiles so SEO Panel can monitor your social presence and track shares, followers, and engagement.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Add social profiles from your website's <strong>Social Media</strong> section.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/social_media.php?sec=add" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Add Social Media
                </a>
            </div>

            <!-- Step 7: Review Links -->
            <div class="sp-wizard-panel" id="sp_wpanel_7">
                <h5><i class="fas fa-star" style="margin-right:6px;"></i>Review Links</h5>
                <p>Add review site profiles (Google My Business, Yelp, Trustpilot, etc.) so SEO Panel can track your online reputation and ratings.</p>
                <div class="sp-wizard-info-box">
                    <i class="fas fa-info-circle" style="color:#1a73e8; margin-right:6px;"></i>
                    Manage review links from the <strong>Reviews</strong> section.
                </div>
                <a href="<?php echo SP_WEBPATH ?>/review.php?sec=add" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Add Review Links
                </a>
            </div>

            <!-- Step 8: Success -->
            <div class="sp-wizard-panel" id="sp_wpanel_8">
                <div class="sp-wizard-success-icon"><i class="fas fa-check-circle"></i></div>
                <h5 class="text-center" style="color:#34a853;">You're all set!</h5>
                <p class="text-center">Your SEO Panel is ready to go. Explore the dashboard to start tracking and improving your website's search engine performance.</p>
                <div class="sp-wizard-info-box" style="background:#f0fff4; border-color:#34a853;">
                    <i class="fas fa-lightbulb" style="color:#34a853; margin-right:6px;"></i>
                    <strong>Tip:</strong> Click the <strong>Recommendations</strong> tab on the dashboard to get personalised SEO improvement suggestions after your data is collected.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sp-wizard-footer">
            <div class="sp-wizard-footer-left">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-skip" id="sp_wbtn_dismiss"
                    onclick="window.setupWizardDismiss()" title="Do not show this wizard again">
                    <i class="fas fa-eye-slash" style="margin-right:5px;"></i>Do not show again
                </button>
            </div>
            <div class="sp-wizard-footer-right">
                <button type="button" class="sp-confirm-btn sp-confirm-btn-cancel" id="sp_wbtn_back"
                    onclick="window.setupWizardBack()" style="display:none;">
                    <i class="fas fa-arrow-left" style="margin-right:5px;"></i>Back
                </button>
                <button type="button" class="sp-confirm-btn sp-confirm-btn-skip" id="sp_wbtn_skip"
                    onclick="window.setupWizardSkip()" style="display:none;">
                    <i class="fas fa-forward" style="margin-right:5px;"></i>Skip
                </button>
                <button type="button" class="sp-confirm-btn sp-confirm-btn-confirm" id="sp_wbtn_next"
                    onclick="window.setupWizardNext()">
                    <i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var TOTAL_STEPS = 8;
    var currentStep = 1;
    var websiteCreated = false;

    window.setupWizardShow = function(savedStep) {
        currentStep = (savedStep > 0) ? savedStep : 1;
        // If resuming past step 1, the website was already created in a prior session
        if (currentStep > 1) {
            websiteCreated = true;
        }
        _wizardRender();
        $('#sp_wizard_overlay').fadeIn(200);
    };

    window.setupWizardNext = function() {
        if (currentStep === 1) {
            if (websiteCreated) {
                currentStep = 2;
                _wizardSaveStep(currentStep);
                _wizardRender();
            } else {
                _wizardCreateWebsite();
            }
            return;
        }
        if (currentStep === TOTAL_STEPS) {
            _wizardSaveStep(TOTAL_STEPS, function() {
                $('#sp_wizard_overlay').fadeOut(200);
            });
            return;
        }
        currentStep++;
        _wizardSaveStep(currentStep);
        _wizardRender();
    };

    window.setupWizardBack = function() {
        if (currentStep > 1) {
            currentStep--;
            _wizardSaveStep(currentStep);
            _wizardRender();
        }
    };

    window.setupWizardSkip = function() {
        if (currentStep > 1 && currentStep < TOTAL_STEPS) {
            currentStep++;
            _wizardSaveStep(currentStep);
            _wizardRender();
        }
    };

    window.setupWizardDismiss = function() {
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/setup_wizard.php',
            type: 'POST',
            data: { sec: 'dismiss' },
            complete: function() {
                $('#sp_wizard_overlay').fadeOut(200);
            }
        });
    };

    function _wizardCreateWebsite() {
        var name = $.trim($('#wz_site_name').val());
        var url  = $.trim($('#wz_site_url').val());
        if (!name) {
            _wizardMsg('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>Website name is required.</div>');
            return;
        }
        if (!url) {
            _wizardMsg('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>Website URL is required.</div>');
            return;
        }
        $('#sp_wbtn_next').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Creating...');
        _wizardMsg('');
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/setup_wizard.php',
            type: 'POST',
            data: { sec: 'create_website', name: name, url: url },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'ok') {
                    websiteCreated = true;
                    currentStep = 2;
                    _wizardSaveStep(currentStep);
                    _wizardRender();
                    _wizardMsg('');
                } else {
                    _wizardMsg('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>' + (res.message || 'Failed to create website.') + '</div>');
                    $('#sp_wbtn_next').prop('disabled', false).html('<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next');
                }
            },
            error: function() {
                _wizardMsg('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>An error occurred. Please try again.</div>');
                $('#sp_wbtn_next').prop('disabled', false).html('<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next');
            }
        });
    }

    function _wizardSaveStep(step, callback) {
        $.ajax({
            url: '<?php echo SP_WEBPATH ?>/setup_wizard.php',
            type: 'POST',
            data: { sec: 'save_step', step: step },
            complete: function() {
                if (callback) callback();
            }
        });
    }

    function _wizardRender() {
        // Panels
        $('.sp-wizard-panel').removeClass('active');
        $('#sp_wpanel_' + currentStep).addClass('active');

        // Step 1: toggle form vs already-created message
        if (currentStep === 1) {
            if (websiteCreated) {
                $('#wz_site_form').hide();
                $('#wz_site_done').show();
            } else {
                $('#wz_site_form').show();
                $('#wz_site_done').hide();
            }
        }

        // Step dots
        for (var i = 1; i <= TOTAL_STEPS; i++) {
            var $dot  = $('#sp_wdot_' + i);
            var $line = $('#sp_wline_' + i);
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

        // Back button: hide on step 1
        if (currentStep <= 1) {
            $('#sp_wbtn_back').hide();
        } else {
            $('#sp_wbtn_back').show();
        }

        // Skip button: hidden on step 1 and step 8
        if (currentStep === 1 || currentStep === TOTAL_STEPS) {
            $('#sp_wbtn_skip').hide();
        } else {
            $('#sp_wbtn_skip').show();
        }

        // Next button label
        var $next = $('#sp_wbtn_next');
        $next.prop('disabled', false);
        if (currentStep === 1) {
            $next.html(websiteCreated
                ? '<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next'
                : '<i class="fas fa-plus" style="margin-right:5px;"></i>Create Website');
        } else if (currentStep === TOTAL_STEPS) {
            $next.html('<i class="fas fa-check" style="margin-right:5px;"></i>Get Started');
        } else {
            $next.html('<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Next');
        }

        // Clear message when navigating away from step 1
        if (currentStep !== 1) _wizardMsg('');
    }

    function _wizardMsg(html) {
        $('#sp_wizard_message').html(html);
    }
})();
</script>
