<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
    $custSiteInfo = getCustomizerDetails();
    $spTitle = empty($spTitle) ? SP_TITLE : $spTitle;
    $spDescription = empty($spDescription) ? SP_DESCRIPTION : $spDescription;
    $spKeywords = empty($spKeywords) ? SP_KEYWORDS : $spKeywords;
    $spKey = "v" . substr(SP_INSTALLED, 2);
    $userInfo = @Session::readSession('userInfo');
    $userType = empty($userInfo['userType']) ? "guest" : $userInfo['userType'];
    
    $menuName = ($userType != "guest" && $userType != "admin") ? "user" : $userType;
    $menuInfo = getCustomizerMenu($menuName);
    
    if (!empty($menuInfo['bg_color'])) {
    	$siteBgClass = $menuInfo['bg_color'];
    	$siteFooterBgClass = $menuInfo['bg_color'];
    } else {
	    // theme wise changes
	    if (stristr(SP_VIEWPATH, '/simple/')) {
	    	$siteBgClass = "navbar-expand-md-bg";
	    	$siteFooterBgClass = "footer-sp-bg";
	    } else {
	    	$siteBgClass = "bg-dark";
	    	$siteFooterBgClass = "bg-dark text-muted";
	    }
    }
    
    $siteNavFontClass = !empty($menuInfo['font_color']) ? $menuInfo['font_color'] : "navbar-dark"; 
    ?>
    <title><?php echo stripslashes($spTitle)?></title>
    <meta name="description" content="<?php echo $spDescription?>" />
    <meta name="keywords" content="<?php echo $spKeywords?>" />
    <link rel="shortcut icon" href="<?php echo !empty($custSiteInfo['site_favicon']) ? $custSiteInfo['site_favicon'] : SP_IMGPATH . "/favicon.ico"?>" />

    <!-- PWA: installable app manifest + icons. sw.js is deliberately a
         no-cache passthrough (see sw.js) - this is an authenticated,
         session-based dashboard, not an offline-content app. -->
    <link rel="manifest" href="<?php echo SP_WEBPATH?>/manifest.json" />
    <meta name="theme-color" content="#9a0000" />
    <link rel="apple-touch-icon" href="<?php echo SP_IMGPATH?>/pwa-icon-192.png" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="SEO Panel" />

    <!-- Css files -->
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/bootstrap.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/jquery-ui/jquery-ui.min.css?<?php echo $spKey?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/datepicker.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/fontawesome/css/all.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/simplemde.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen.css?<?php echo $spKey?>" media="all" />
    
    <?php if (in_array($_SESSION['lang_code'], array('ar', 'he', 'fa'))) {?>
    	<link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen_rtl.css?<?php echo $spKey?>" media="all" />
    <?php }?>
    
    <!-- JS Files -->
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/jquery-3.3.1.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/popper.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/bootstrap.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/datepicker.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_WEBPATH?>/jquery-ui/jquery-ui.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH; ?>/loader.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH; ?>/jquery.tablesorter.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/simplemde.min.js?<?php echo $spKey?>"></script>
    
     <!-- tinymce editor -->
	<script type="text/javascript" src="<?php echo SP_JSPATH?>/tinymce/tinymce.min.js?<?php echo $spKey?>"></script>
	
    <!-- sp specific files -->    
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/common.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/popup.js?<?php echo $spKey?>"></script>
    
    <?php if (isPluginActivated("customizer")) {?>
    	<link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/custom_style.php?<?php echo $spKey?>" media="all" />
    	<script type="text/javascript" src="<?php echo SP_WEBPATH?>/custom_js.php?<?php echo $spKey?>"></script>
    <?php }?>

    <script type="text/javascript">
    if ('serviceWorker' in navigator) {
        // silently no-op on plain HTTP (non-localhost) - service workers
        // require a secure context and the browser rejects registration there
        navigator.serviceWorker.register('<?php echo SP_WEBPATH?>/sw.js').catch(function() {});
    }
    </script>

</head>
<body class="bg-light">
    <script type="text/javascript">
    var spdemo = <?php echo SP_DEMO; ?>;
    var wantproceed = '<?php  echo $spText['label']['wantproceed']; ?>';
    </script>
    
    <nav class="navbar navbar-expand-md <?php echo $siteNavFontClass?> <?php echo $siteBgClass;?>">
    	<a class="navbar-brand" href="<?php echo SP_WEBPATH?>">
    		<img src="<?php echo !empty($custSiteInfo['site_logo']) ? $custSiteInfo['site_logo'] : SP_IMGPATH . "/logo_red_sm.png";?>" width="131" height="31" alt="Seo Panel">
    	</a>
      	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
      		aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        	<span class="navbar-toggler-icon"></span>
      	</button>
      	
    	<div class="collapse navbar-collapse" id="navbarSupportedContent">
    		<ul class="navbar-nav mr-auto">
    			<?php include(SP_VIEWPATH.'/menu/main_menu.ctp.php');?>
    		</ul>		
    		<form class="form-inline mt-2 mt-md-0">
    			<?php include_once(SP_VIEWPATH."/menu/topmenu.ctp.php");?>
    		</form>

            <?php
            if (isLoggedIn()) {
                include_once(SP_VIEWPATH."/menu/notifications_menu.ctp.php");
            }
            ?>
    	</div>
    </nav>

    <!-- PWA install prompt. Chrome/Edge/Android show this via
         beforeinstallprompt, which has no Safari/iOS equivalent - iOS
         Safari has no programmatic install API at all, so for that
         platform the JS below instead swaps in manual "tap Share, then
         Add to Home Screen" instructions and hides the Install button
         (there's nothing to trigger). Hidden by default either way; JS
         reveals it only when installable/iOS-Safari, not dismissed
         before, and not already running installed (standalone). -->
    <div id="sp-pwa-install-banner">
        <div class="sp-pwa-install-content">
            <img src="<?php echo SP_IMGPATH?>/pwa-icon-192.png" alt="" class="sp-pwa-install-icon">
            <div class="sp-pwa-install-text">
                <strong>Install SEO Panel</strong>
                <p id="sp-pwa-install-text-default">Add it to your home screen for quick, full-screen access.</p>
                <p id="sp-pwa-install-text-ios" style="display:none;">Tap <i class="fas fa-share-square"></i> <strong>Share</strong>, then <strong>Add to Home Screen</strong>.</p>
            </div>
            <div class="sp-pwa-install-buttons">
                <button type="button" class="btn btn-sm btn-light" id="sp-pwa-install-btn">Install</button>
                <button type="button" class="sp-pwa-install-dismiss" id="sp-pwa-install-dismiss-btn" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var deferredInstallPrompt = null;
        var banner = document.getElementById('sp-pwa-install-banner');
        var installBtn = document.getElementById('sp-pwa-install-btn');
        var dismissBtn = document.getElementById('sp-pwa-install-dismiss-btn');

        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredInstallPrompt = e;
            if (!banner) return;

            var dismissed = false;
            try { dismissed = localStorage.getItem('sp_pwa_install_dismissed') === '1'; } catch (err) {}
            var standalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
            if (dismissed || standalone) return;

            banner.style.display = 'block';
        });

        if (installBtn) {
            installBtn.addEventListener('click', function() {
                if (banner) banner.style.display = 'none';
                if (!deferredInstallPrompt) return;
                deferredInstallPrompt.prompt();
                deferredInstallPrompt.userChoice.then(function() { deferredInstallPrompt = null; });
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                if (banner) banner.style.display = 'none';
                try { localStorage.setItem('sp_pwa_install_dismissed', '1'); } catch (err) {}
            });
        }

        window.addEventListener('appinstalled', function() {
            if (banner) banner.style.display = 'none';
        });

        // iOS Safari has no beforeinstallprompt (or any programmatic
        // install API) - the only way to install is the user manually
        // tapping Share > Add to Home Screen, so show instructions for
        // that instead of an Install button. Excludes Chrome/Firefox/Edge
        // for iOS (CriOS/FxiOS/EdgiOS) since their share sheets differ
        // from Safari's and don't offer this in the same way.
        var ua = window.navigator.userAgent;
        var isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        var isIosSafari = isIos && !/CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);
        if (isIosSafari && banner) {
            var dismissed = false;
            try { dismissed = localStorage.getItem('sp_pwa_install_dismissed') === '1'; } catch (err) {}
            var standalone = window.navigator.standalone === true ||
                (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches);
            if (!dismissed && !standalone) {
                var defaultText = document.getElementById('sp-pwa-install-text-default');
                var iosText = document.getElementById('sp-pwa-install-text-ios');
                if (defaultText) defaultText.style.display = 'none';
                if (iosText) iosText.style.display = 'block';
                if (installBtn) installBtn.style.display = 'none';
                banner.style.display = 'block';
            }
        }
    })();
    </script>

    <?php include_once(SP_VIEWPATH."/common/top_notification.ctp.php");?>
    <?php
    // show initial setup wizard for logged-in users who haven't completed or dismissed it
    if (isLoggedIn() && defined('SP_SETUP_WIZARD') && SP_SETUP_WIZARD) {
        include_once(SP_CTRLPATH . "/setup_wizard.ctrl.php");
        $spWizardCtrl = new SetupWizardController();
        $spWizardState = $spWizardCtrl->getWizardState(isLoggedIn());
        if (!empty($spWizardState['show'])) {
            $wizardStep = intval($spWizardState['step']);
            include_once(SP_VIEWPATH . "/layout/setup_wizard_popup.ctp.php");
            echo '<script>$(document).ready(function(){ window.setupWizardShow(' . $wizardStep . '); });</script>';
        }
    }
    ?>
    <?php
    // show spAPI registration popup for admin users who haven't registered or skipped
    if (isLoggedIn() && isAdmin()) {
        include_once(SP_CTRLPATH."/settings.ctrl.php");
        $spApiCtrl = new SettingsController();
        if (defined('SP_SPAPI_REGISTERED') && !SP_SPAPI_REGISTERED) {
            if ($spApiCtrl->showSpApiRegistrationPopup()) {
                include_once(SP_VIEWPATH."/settings/spapi_register_popup.ctp.php");
            }
        } else {
            $spapiCheckResult = $spApiCtrl->showSpApiUpgradePopup();
            include_once(SP_VIEWPATH."/settings/spapi_upgrade_popup.ctp.php");
            if ($spapiCheckResult) {
                echo '<script>$(document).ready(function(){ window.spapiShowUpgradePopup(); });</script>';
            }
        }
    }
    ?>
    <?php
    // show the daily "new version available" notice popup for admin users -
    // once per day on login, notice-only (its CTA just navigates to
    // Settings > Version, it never triggers an upgrade from here)
    if (isLoggedIn() && isAdmin()) {
        include_once(SP_CTRLPATH."/settings.ctrl.php");
        $versionUpgradeCtrl = new SettingsController();
        if ($versionUpgradeCtrl->showVersionUpgradePopup()) {
            include_once(SP_VIEWPATH."/settings/version_upgrade_popup.ctp.php");
            echo '<script>$(document).ready(function(){ window.versionUpgradeShowPopup(); });</script>';
        }
    }
    ?>
    <?php
    // Zero-Setup Scheduler opportunistic trigger: fire a non-blocking
    // beacon at cron-beacon.php on admin page loads, so an install with no
    // external pinger/crontab configured still gets scheduled work done
    // just from the admin using the panel. Client-side throttled to once
    // per 5 minutes via localStorage - the server-side lock in
    // runPingTrigger() already makes overlapping fires harmless, this just
    // avoids firing on every single page load. Only rendered when the
    // ping trigger is actually enabled (see Scheduler Health) - off by
    // default, so this is silent unless an admin has opted in.
    if (isLoggedIn() && isAdmin() && defined('SP_CRON_PING_ENABLED') && SP_CRON_PING_ENABLED) {
    ?>
    <script>
    (function() {
        var THROTTLE_MS = 5 * 60 * 1000;
        var STORAGE_KEY = 'sp_cron_beacon_last_fired';
        try {
            var last = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            if (Date.now() - last < THROTTLE_MS) return;
            localStorage.setItem(STORAGE_KEY, String(Date.now()));
        } catch (e) {
            // localStorage unavailable (private browsing, blocked, etc.) -
            // fire anyway rather than never triggering at all; the
            // server-side lock still makes this safe
        }
        if (navigator.sendBeacon) {
            navigator.sendBeacon('<?php echo SP_WEBPATH?>/cron-beacon.php');
        } else {
            fetch('<?php echo SP_WEBPATH?>/cron-beacon.php', { credentials: 'same-origin', keepalive: true }).catch(function() {});
        }
    })();
    </script>
    <?php
    }
    ?>

    <div class="container-fluid" style="margin-bottom: 50px;">
    	<div class="row">
    		<?php echo $viewContent?>
    	</div>
    </div>
    
    <div class="container-fluid fixed-bottom <?php echo $siteNavFontClass?> <?php echo $siteFooterBgClass;?> center footer-sp d-none d-md-block">
    	<?php include_once(SP_VIEWPATH."/common/footer.ctp.php"); ?>
    </div>

    <?php
    // mobile/PWA bottom tab bar - replaces the plain-text footer above on
    // small viewports (see pwa_bottom_nav.ctp.php) - same $this->menu
    // active-state switch main_menu.ctp.php above already relies on
    // unconditionally, so no extra guard needed here either
    include_once(SP_VIEWPATH."/menu/pwa_bottom_nav.ctp.php");
    ?>

    <div id="tmp"><form name="tmp" id="tmp"></form></div>
    <div id="dialogContent" style="display:none;"></div>
    <?php
    $spGdprEnabled = defined('SP_GDPR_COOKIE_BANNER') && SP_GDPR_COOKIE_BANNER;
    $spGaCode      = defined('SP_GOOGLE_ANALYTICS_TRACK_CODE') ? SP_GOOGLE_ANALYTICS_TRACK_CODE : '';
    $spGaSimple    = !empty($spGaCode) && !stristr($spGaCode, '<script');
    ?>

    <?php if (!empty($spGaCode)): ?>
    <?php if ($spGaSimple): ?>
    <!-- Google Analytics (GDPR-aware) -->
    <script>
    function spLoadAnalytics() {
        if (window._spGaLoaded) return;
        window._spGaLoaded = true;
        (function() {
            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($spGaCode, ENT_QUOTES)?>';
            document.head.appendChild(s);
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', '<?php echo htmlspecialchars($spGaCode, ENT_QUOTES)?>');
        })();
    }
    <?php if (!$spGdprEnabled): ?>
    spLoadAnalytics();
    <?php else: ?>
    if (localStorage.getItem('sp_gdpr_consent') === 'accepted') { spLoadAnalytics(); }
    <?php endif; ?>
    </script>
    <?php else: ?>
    <?php if (!$spGdprEnabled): ?>
    <?php echo $spGaCode; ?>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($spGdprEnabled):
        $spGdprText = !empty($spText['common']['SP_GDPR_COOKIE_BANNER_TEXT'])
            ? htmlspecialchars($spText['common']['SP_GDPR_COOKIE_BANNER_TEXT'], ENT_QUOTES)
            : 'This website uses cookies to improve your experience and analyse traffic. You may accept or decline the use of non-essential cookies in accordance with the GDPR/RGPD regulation.';
        $spGdprAccept  = !empty($spText['button']['Accept'])  ? htmlspecialchars($spText['button']['Accept'],  ENT_QUOTES) : 'Accept';
        $spGdprDecline = !empty($spText['button']['Decline']) ? htmlspecialchars($spText['button']['Decline'], ENT_QUOTES) : 'Decline';
    ?>
    <!-- GDPR / RGPD Cookie Consent Banner -->
    <div id="sp-gdpr-banner" role="dialog" aria-live="polite" aria-label="Cookie consent">
        <div class="sp-gdpr-content">
            <div class="sp-gdpr-text">
                <strong>Cookie Notice (GDPR / RGPD)</strong>
                <p><?php echo $spGdprText?></p>
            </div>
            <div class="sp-gdpr-buttons">
                <button id="sp-gdpr-accept"  class="btn btn-sm btn-success"      type="button"><?php echo $spGdprAccept?></button>
                <button id="sp-gdpr-decline" class="btn btn-sm btn-secondary" type="button"><?php echo $spGdprDecline?></button>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var banner  = document.getElementById('sp-gdpr-banner');
        var consent = localStorage.getItem('sp_gdpr_consent');
        if (!consent) {
            banner.style.display = 'block';
        }
        document.getElementById('sp-gdpr-accept').addEventListener('click', function() {
            localStorage.setItem('sp_gdpr_consent', 'accepted');
            banner.style.display = 'none';
            <?php if ($spGaSimple): ?>if (typeof spLoadAnalytics === 'function') { spLoadAnalytics(); }<?php endif; ?>
        });
        document.getElementById('sp-gdpr-decline').addEventListener('click', function() {
            localStorage.setItem('sp_gdpr_consent', 'declined');
            banner.style.display = 'none';
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>