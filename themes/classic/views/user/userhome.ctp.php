<div class="col-sm-12 mt-4">	
    <?php
    $showOverview = true;
    if ($showOverview) {
        $dbTabClass = "";
        $ovTabView = "";
        $mainTabClass = "";
        $smTabView = "";
        $rvTabView = "";
        $saTabView = "";
        $waTabView = "";
        $anTabView = "";
        $scTabView = "";
        $dsTabView = "";
        $rcTabView = "";

        if (!empty($custSubMenu)) {
            $ovTabView = "active";
        } else {
            switch ($post['dashboard']) {
                case "reports":
                    $dbTabClass = "active";
                    break;

                case "social_media":
                    $smTabView = "active";
                    break;

                case "reviews":
                    $rvTabView = "active";
                    break;

                case "site_auditor":
                    $saTabView = "active";
                    break;

                case "website_analytics":
                    $waTabView = "active";
                    break;

                case "analytics":
                    $anTabView = "active";
                    break;

                case "search_console":
                    $scTabView = "active";
                    break;

                case "directory_submission":
                    $dsTabView = "active";
                    break;

                case "recommendations":
                    $rcTabView = "active";
                    break;

                default:
                    $mainTabClass = "active";
                    break;
            }
        }

        // shared Website/Period selector bar (below the tab nav) - drives
        // whichever tab is currently active instead of each tab keeping
        // its own duplicate "Website:"/"Period:" controls. Reload target
        // and period support both vary per tab, so both are resolved here
        // once rather than re-deriving them in every view.
        // navMode: "ajax" (scriptDoLoad into #content, every tab but one) or
        // "page" (a real window.location.href navigation) - Keywords
        // (overview.php) always renders the full page layout server-side
        // (never an ajax fragment), so it needs a real navigation even
        // though it now uses the same day/week/month/year preset as
        // everything else.
        $spSharedNavMode = "ajax";
        if ($ovTabView == "active") {
            $spSharedScript = "overview.php";
            $spSharedPeriodMode = "preset"; // was raw from_time/to_time; now the same preset as every other tab
            $spSharedNavMode = "page";
        } else if ($saTabView == "active" || $rcTabView == "active") {
            $spSharedScript = $saTabView == "active" ? "siteauditor_dashboard.php" : "recommendations_dashboard.php";
            $spSharedPeriodMode = "none"; // no date-range concept on this tab
        } else if ($dbTabClass == "active") {
            $spSharedScript = "archive.php";
            $spSharedPeriodMode = "none"; // archive.php has its own separate date-range UI
        } else {
            $spSharedPeriodMode = "preset"; // the common day/week/month/year select
            if ($smTabView == "active") { $spSharedScript = "social_media_dashboard.php"; }
            else if ($rvTabView == "active") { $spSharedScript = "review_dashboard.php"; }
            else if ($waTabView == "active") { $spSharedScript = "website_analytics_dashboard.php"; }
            else if ($anTabView == "active") { $spSharedScript = "analytics_dashboard.php"; }
            else if ($scTabView == "active") { $spSharedScript = "search_console_dashboard.php"; }
            else if ($dsTabView == "active") { $spSharedScript = "directory_submission_dashboard.php"; }
            else { $spSharedScript = "dashboard.php"; } // Overview (default/$mainTabClass)
        }
        $spSharedWebsiteList = $siteList ?? [];
        // pre-select whatever website/period actually carried over, so
        // spInitSharedFilterBar()'s own restore-from-sessionStorage check
        // doesn't see a false mismatch and fire a pointless (or, worse,
        // infinite - see below) extra reload when the content already
        // rendered correctly.
        //
        // Two different render paths set this differently:
        // - Keywords (overview.php) renders this whole page synchronously
        //   via OverviewController::showOverView(), which resolves
        //   website_id/period itself and exposes them as $websiteId/
        //   $period directly - the authoritative values actually used.
        //   $post is never set on this path at all (only index.ctrl.php's
        //   index() sets it), so checking $post here unconditionally
        //   silently fell back to the wrong defaults (first website,
        //   "month") EVERY time regardless of what was requested - a
        //   permanent mismatch that triggered doReload() -> same URL
        //   again -> same wrong defaults -> infinite reload loop.
        // - Every other tab renders this shell via index.ctrl.php's own
        //   index() (which sets $post from the request) and loads its
        //   actual content afterward via a separate AJAX call - $websiteId
        //   isn't resolved at shell-render time at all here, so $post's
        //   website_id/period (forwarded by navigateDashboardTab() on a
        //   tab switch) is the best available signal.
        $spSharedCurrentPeriod = isset($period) ? $period : ((isset($post) && !empty($post['period'])) ? $post['period'] : 'month');
        $spSharedCurrentWebsiteId = isset($websiteId) ? intval($websiteId) : ((isset($post) && !empty($post['website_id'])) ? intval($post['website_id']) : (!empty($spSharedWebsiteList[0]['id']) ? intval($spSharedWebsiteList[0]['id']) : 0));
        ?>

        <div id="sp_shared_filter_bar" style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:14px 18px;margin-bottom:14px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        	<label style="font-weight:600;color:#444;font-size:13px;margin:0;"><?php echo $spText['common']['Website']?>:</label>
        	<select id="sp_shared_website_id" class="custom-select" style="width:auto;max-width:280px;">
        		<?php foreach ($spSharedWebsiteList as $spSharedWs) { ?>
        			<option value="<?php echo intval($spSharedWs['id'])?>" <?php echo (intval($spSharedWs['id']) == $spSharedCurrentWebsiteId) ? 'selected' : ''?>><?php echo htmlspecialchars($spSharedWs['name'])?></option>
        		<?php } ?>
        	</select>
        	<?php if ($spSharedPeriodMode == "preset") { ?>
        	<label style="font-weight:600;color:#444;font-size:13px;margin:0;"><?php echo $spText['common']['Period']?>:</label>
        	<select id="sp_shared_period" class="custom-select" style="width:auto;max-width:180px;">
        		<option value="day" <?php echo $spSharedCurrentPeriod == 'day' ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
        		<option value="week" <?php echo $spSharedCurrentPeriod == 'week' ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
        		<option value="month" <?php echo $spSharedCurrentPeriod == 'month' ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
        		<option value="year" <?php echo $spSharedCurrentPeriod == 'year' ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
        	</select>
        	<?php } ?>
        </div>
        <script>
        $(function() {
        	spInitSharedFilterBar(<?php echo json_encode($spSharedScript)?>, <?php echo json_encode($spSharedPeriodMode)?>, <?php echo json_encode($spSharedNavMode)?>);
        });
        </script>
		<ul class="nav nav-tabs" id="main_dashboard_nav">
            <li class="nav-item">
            	<a class="nav-link <?php echo $mainTabClass?>" href="<?php echo SP_WEBPATH?>/" onclick="return navigateDashboardTab(this, '');">
            		<i class="fas fa-tachometer-alt"></i> <span class="nav-label"><?php echo $spText['label']['Overview']?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $rcTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=recommendations" onclick="return navigateDashboardTab(this, 'recommendations');">
            		<i class="fas fa-eye"></i> <span class="nav-label">AI Visibility</span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $waTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=website_analytics" onclick="return navigateDashboardTab(this, 'website_analytics');">
            		<i class="fas fa-globe"></i> <span class="nav-label"><?php echo $spText['label']['Website'] ?? 'Website'?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $ovTabView?>" href="<?php echo SP_WEBPATH?>/overview.php" onclick="return navigateDashboardTab(this, 'overview');">
            		<i class="fas fa-key"></i> <span class="nav-label"><?php echo $spText['common']['Keywords']?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $anTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=analytics" onclick="return navigateDashboardTab(this, 'analytics');">
            		<i class="fas fa-chart-area"></i> <span class="nav-label"><?php echo $spText['label']['Analytics'] ?? 'Analytics'?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $scTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=search_console" onclick="return navigateDashboardTab(this, 'search_console');">
            		<i class="fas fa-search-plus"></i> <span class="nav-label"><?php echo $spText['label']['Search Console'] ?? 'Search Console'?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $saTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=site_auditor" onclick="return navigateDashboardTab(this, 'site_auditor');">
            		<i class="fas fa-search"></i> <span class="nav-label"><?php echo $spTextTools['Site Auditor'] ?? 'Site Auditor'?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $smTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=social_media" onclick="return navigateDashboardTab(this, 'social_media');">
            		<i class="fas fa-share-alt"></i> <span class="nav-label"><?php echo $spText['label']['Social Media']?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $rvTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=reviews" onclick="return navigateDashboardTab(this, 'reviews');">
            		<i class="fas fa-star"></i> <span class="nav-label"><?php echo $spText['label']['Reviews']?></span>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $dsTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=directory_submission" onclick="return navigateDashboardTab(this, 'directory_submission');">
            		<i class="fas fa-folder-open"></i> <span class="nav-label"><?php echo $spTextTools['Directory Submission'] ?? 'Directory Submission'?></span>
            	</a>
            </li>
        </ul>
    	<?php
    }?>

    <?php if ($showOverview && !empty($custSubMenu)) {?>
    	<?php if (!empty($noWebsites)) {
    		include(SP_VIEWPATH."/dashboard/no_websites.ctp.php");
    	} else {
    		include(SP_VIEWPATH."/report/overview.ctp.php");
    	}?>
    <?php } else {?> 
        <div id="content">
        	<script type="text/javascript">
        		<?php if ($dbTabClass == "active") {?>
               		scriptDoLoad('archive.php', 'content', '<?php echo getRequestParamStr(); ?>');
           		<?php } elseif ($smTabView == "active") {?>
               		scriptDoLoad('social_media_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($rvTabView == "active") {?>
               		scriptDoLoad('review_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($saTabView == "active") {?>
               		scriptDoLoad('siteauditor_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($waTabView == "active") {?>
               		scriptDoLoad('website_analytics_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($anTabView == "active") {?>
               		scriptDoLoad('analytics_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($scTabView == "active") {?>
               		scriptDoLoad('search_console_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($dsTabView == "active") {?>
               		scriptDoLoad('directory_submission_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($rcTabView == "active") {?>
               		scriptDoLoad('recommendations_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } else {?>
               		scriptDoLoad('dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php }?>
        	</script>
        </div>
    <?php }?>
</div>
<?php if (!empty($_GET['source']) && $_GET['source'] == 'install') {?>
    <script type="text/javascript">
        $(document).ready(function() {
        	scriptDoLoad("<?php echo SP_WEBPATH?>/?sec=sync_all_se", "tmp");
        });
    </script>
<?php }?>

<script type="text/javascript">
// Function to get currently selected website ID from any dashboard form
function getCurrentWebsiteId() {
    var websiteId = null;

    // The shared filter bar (always present, above #main_dashboard_nav)
    // is now the single source of truth - covers every tab uniformly,
    // including Keywords (#ov_search_form), which this chain's per-tab
    // fallbacks below never included even before the shared bar existed.
    if ($('#sp_shared_website_id').length) {
        websiteId = $('#sp_shared_website_id').val();
    }
    // Try to get from main dashboard form
    else if ($('#dashboard_form select[name="website_id"]').length) {
        websiteId = $('#dashboard_form select[name="website_id"]').val();
    }
    // Try to get from social media dashboard form
    else if ($('#social_media_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#social_media_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from review dashboard form
    else if ($('#review_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#review_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from site auditor dashboard form
    else if ($('#siteauditor_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#siteauditor_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from website analytics dashboard form
    else if ($('#website_analytics_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#website_analytics_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from analytics dashboard form
    else if ($('#analytics_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#analytics_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from search console dashboard form
    else if ($('#search_console_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#search_console_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from directory submission dashboard form
    else if ($('#directory_submission_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#directory_submission_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from recommendations dashboard form
    else if ($('#recommendations_dashboard_form select[name="website_id"]').length) {
        websiteId = $('#recommendations_dashboard_form select[name="website_id"]').val();
    }
    // Try to get from URL parameter
    else {
        var urlParams = new URLSearchParams(window.location.search);
        websiteId = urlParams.get('website_id');
    }

    // Store in sessionStorage for persistence
    if (websiteId) {
        sessionStorage.setItem('sp_selected_website_id', websiteId);
    } else {
        // Try to get from sessionStorage
        websiteId = sessionStorage.getItem('sp_selected_website_id');
    }

    return websiteId;
}

// Function to navigate dashboard tabs with website persistence
function navigateDashboardTab(linkElement, tabType) {
    var websiteId = getCurrentWebsiteId();
    var baseUrl = linkElement.href;
    var separator = baseUrl.indexOf('?') !== -1 ? '&' : '?';
    var newUrl = baseUrl;
    var addedParam = false;

    // Add website_id parameter to URL if it exists
    if (websiteId) {
        newUrl += separator + 'website_id=' + websiteId;
        separator = '&';
        addedParam = true;
    }

    // Carry the period preset forward too, same as website_id - read from
    // sessionStorage (kept in sync by spInitSharedFilterBar() on every
    // change) rather than the live #sp_shared_period select, since that
    // select only exists in the DOM on tabs that actually show a period
    // control; sessionStorage still has the last-chosen value regardless
    // of which tab you're navigating away from. Harmless to pass along
    // even to a tab that ignores it (Site Auditor, AI Visibility,
    // Keywords) - those controllers simply never read it.
    var period = sessionStorage.getItem('sp_selected_period');
    if (period) {
        newUrl += separator + 'period=' + period;
        addedParam = true;
    }

    window.location.href = addedParam ? newUrl : baseUrl;

    return false; // Prevent default link behavior
}

// Store website ID whenever it's changed in any form
$(document).ready(function() {
    // Listen for changes on all website_id dropdowns
    $(document).on('change', 'select[name="website_id"]', function() {
        var websiteId = $(this).val();
        if (websiteId) {
            sessionStorage.setItem('sp_selected_website_id', websiteId);
        }
    });
});
</script>