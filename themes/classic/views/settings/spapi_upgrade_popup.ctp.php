<?php
$upgradeReason = !empty($spapiCheckResult) ? $spapiCheckResult : '';
if ($upgradeReason === 'expired') {
    $upgradeTitle    = 'API Subscription Expired';
    $upgradeIcon     = 'fa-calendar-times';
    $upgradeAlertMsg = 'Your Seo Panel API subscription has <strong>expired</strong>.';
    $upgradeSubMsg   = 'Upgrade your plan to restore access to the Seo Panel API.';
} else {
    $upgradeTitle    = 'API Monthly Limit Reached';
    $upgradeIcon     = 'fa-tachometer-alt';
    $upgradeAlertMsg = 'You have reached your <strong>monthly API request limit</strong>.';
    $upgradeSubMsg   = 'Upgrade your plan to continue using the Seo Panel API without interruption.';
}
?>
<div class="sp-confirm-overlay" id="spapi_upgrade_overlay" style="display:none;">
	<div class="sp-confirm-box" style="max-width: 920px; width: 95%">
		<div class="sp-confirm-header">
			<i class="fas <?php echo $upgradeIcon?>"></i>
			<span>Seo Panel <?php echo $upgradeTitle?></span>
		</div>
		<div class="sp-confirm-body">
			<div id="spapi_upgrade_message"></div>
			<div id="spapi_upgrade_intro">
				<div class="spapi-upgrade-alert-box">
					<div class="spapi-upgrade-alert-icon">
						<i class="fas <?php echo $upgradeIcon?>"></i>
					</div>
					<div class="spapi-upgrade-alert-text">
						<div><?php echo $upgradeAlertMsg?></div>
						<div class="spapi-upgrade-alert-sub"><?php echo $upgradeSubMsg?></div>
					</div>
				</div>
			</div>
			<div id="spapi_upgrade_plans" style="display:none;">
				<div class="spapi-plans-header">
					<i class="fas fa-layer-group"></i> <strong>Choose a Plan</strong>
				</div>
				<div class="spapi-plans-container" id="spapi_upgrade_plans_list"></div>
				<div class="spapi-upgrade-contact">
					<i class="fas fa-envelope"></i> For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong>contact us</strong></a>.
				</div>
			</div>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-skip" id="spapi_upgrade_btn_skip" onclick="window.spapiUpgradeSkip()">
				<i class="fas fa-times" style="margin-right:5px;"></i>Skip
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-cancel" id="spapi_upgrade_btn_cancel" onclick="$('#spapi_upgrade_overlay').fadeOut(200)">
				<i class="fas fa-ban" style="margin-right:5px;"></i>Cancel
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_upgrade_btn_proceed" onclick="window.spapiUpgradeLoadPlans()">
				<i class="fas fa-rocket" style="margin-right:5px;"></i>See Upgrade Plans
			</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.spapiShowUpgradePopup = function() {
	$('#spapi_upgrade_message').html('');
	$('#spapi_upgrade_intro').show();
	$('#spapi_upgrade_plans').hide();
	$('#spapi_upgrade_plans_list').html('');
	$('#spapi_upgrade_btn_proceed').show().prop('disabled', false).html('<i class="fas fa-rocket" style="margin-right:5px;"></i>See Upgrade Plans');
	$('#spapi_upgrade_btn_skip').show();
	$('#spapi_upgrade_btn_cancel').show();
	window.spapiUpgradePlansData = [];
	$('#spapi_upgrade_overlay').fadeIn(200);
};

window.spapiUpgradeSkip = function() {
	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=spapi_upgrade_skip',
		type: 'GET',
		dataType: 'json',
		complete: function() {
			$('#spapi_upgrade_overlay').fadeOut(200);
		}
	});
};

window.spapiUpgradePlansData = [];

window.spapiUpgradeLoadPlans = function() {
	var contactNote = '<div class="spapi-upgrade-contact"><i class="fas fa-envelope"></i> For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong>contact us</strong></a>.</div>';
	$('#spapi_upgrade_btn_proceed').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Loading...');

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=spapi_plans',
		type: 'GET',
		dataType: 'json',
		timeout: 15000,
		success: function(response) {
			var allPlans = response.data || response.plans || response;
			var plans = Array.isArray(allPlans) ? allPlans.filter(function(p) {
				return p.price && parseFloat(p.price) > 0;
			}) : [];

			var planIcons = ['fa-bolt', 'fa-star', 'fa-crown', 'fa-gem'];

			if (plans.length > 0) {
				window.spapiUpgradePlansData = plans;
				var html = '';
				for (var i = 0; i < plans.length; i++) {
					var plan = plans[i];
					var serpLimit = (plan.limits && plan.limits.SERP) ? plan.limits.SERP : (plan.serp_limit || 0);
					var icon = planIcons[i] || 'fa-layer-group';
					var isPopularUpgrade = i === 0;
					html += '<div class="spapi-plan-card" data-plan-index="' + i + '" onclick="window.spapiUpgradeSelectPlan(' + i + ')">';
					if (isPopularUpgrade) {
						html += '<span class="spapi-plan-badge">Popular</span>';
					}
					html += '<div class="spapi-plan-icon"><i class="fas ' + icon + '"></i></div>';
					html += '<div class="spapi-plan-name">' + (plan.name || plan.plan_name || 'Plan') + '</div>';
					html += '<div class="spapi-plan-price">$' + plan.price + '<span>/mo</span></div>';
					html += '<div class="spapi-plan-divider"></div>';
					if (plan.monthly_limit) {
						html += '<div class="spapi-plan-detail"><i class="fas fa-database"></i>' + plan.monthly_limit.toLocaleString() + ' requests/mo</div>';
					}
					if (serpLimit) {
						html += '<div class="spapi-plan-detail"><i class="fas fa-search"></i>' + serpLimit.toLocaleString() + ' SERPs/mo</div>';
					}
					if (plan.features && Array.isArray(plan.features)) {
						for (var j = 0; j < plan.features.length; j++) {
							html += '<div class="spapi-plan-detail"><i class="fas fa-check"></i>' + plan.features[j] + '</div>';
						}
					}
					html += '<div class="spapi-plan-cta"><span class="spapi-plan-cta-btn"><i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Get Started</span></div>';
					html += '</div>';
				}
				$('#spapi_upgrade_plans_list').html(html);
				$('#spapi_upgrade_intro').hide();
				$('#spapi_upgrade_plans').show();
				$('#spapi_upgrade_btn_proceed').hide();
			} else {
				$('#spapi_upgrade_message').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> No paid plans available at the moment.' + contactNote + '</div>');
				$('#spapi_upgrade_btn_proceed').hide();
			}
		},
		error: function() {
			$('#spapi_upgrade_message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Could not load plans. Please try again later.</div>');
			$('#spapi_upgrade_btn_proceed').prop('disabled', false).html('<i class="fas fa-rocket" style="margin-right:5px;"></i>See Upgrade Plans');
		}
	});
};

window.spapiUpgradeSelectPlan = function(index) {
	var plan = window.spapiUpgradePlansData[index];
	if (plan && plan.plan_link) {
		window.open(plan.plan_link, '_blank');
	}
};
</script>
