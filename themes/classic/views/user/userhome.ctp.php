<div class="col-sm-12 mt-4">	
    <?php
    $showOverview = true;
    if ($showOverview) {
        $dbTabClass = "";
        $ovTabView = "";
        $mainTabClass = "";
        $smTabView = "";
        $rvTabView = "";

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

                default:
                    $mainTabClass = "active";
                    break;
            }
        }
        ?>
		<ul class="nav nav-tabs" id="main_dashboard_nav">
            <li class="nav-item">
            	<a class="nav-link <?php echo $mainTabClass?>" href="<?php echo SP_WEBPATH?>/" onclick="return navigateDashboardTab(this, '');">
            		<i class="fas fa-tachometer-alt"></i> <?php echo $spText['label']['Overview']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $dbTabClass?>" href="<?php echo SP_WEBPATH?>/?dashboard=reports" onclick="return navigateDashboardTab(this, 'reports');">
            		<i class="fas fa-chart-line"></i> <?php echo $spText['common']['Reports']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $ovTabView?>" href="<?php echo SP_WEBPATH?>/overview.php" onclick="return navigateDashboardTab(this, 'overview');">
            		<i class="fas fa-key"></i> <?php echo $spText['common']['Keywords']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $smTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=social_media" onclick="return navigateDashboardTab(this, 'social_media');">
            		<i class="fas fa-share-alt"></i> <?php echo $spText['label']['Social Media']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $rvTabView?>" href="<?php echo SP_WEBPATH?>/?dashboard=reviews" onclick="return navigateDashboardTab(this, 'reviews');">
            		<i class="fas fa-star"></i> <?php echo $spText['label']['Reviews']?>
            	</a>
            </li>
        </ul>
    	<?php
    }?>
    
    <?php if ($showOverview && !empty($custSubMenu)) {?>
    	<?php include(SP_VIEWPATH."/report/overview.ctp.php");?>
    <?php } else {?> 
        <div id="content">
        	<script type="text/javascript">
        		<?php if ($dbTabClass == "active") {?>
               		scriptDoLoad('archive.php', 'content', '<?php echo getRequestParamStr(); ?>');
           		<?php } elseif ($smTabView == "active") {?>
               		scriptDoLoad('social_media_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php } elseif ($rvTabView == "active") {?>
               		scriptDoLoad('review_dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
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

    // Try to get from main dashboard form
    if ($('#dashboard_form select[name="website_id"]').length) {
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

    // Add website_id parameter to URL if it exists
    if (websiteId) {
        var separator = baseUrl.indexOf('?') !== -1 ? '&' : '?';
        var newUrl = baseUrl + separator + 'website_id=' + websiteId;
        window.location.href = newUrl;
    } else {
        // No website selected, use default navigation
        window.location.href = baseUrl;
    }

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