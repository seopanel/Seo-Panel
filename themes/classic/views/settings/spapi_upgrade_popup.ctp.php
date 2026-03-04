<div class="sp-confirm-overlay" id="spapi_upgrade_overlay" style="display:none;">
	<div class="sp-confirm-box" style="max-width: 640px;">
		<div class="sp-confirm-header">
			<i class="fas fa-arrow-circle-up"></i>
			<span>Upgrade Seo Panel API Plan</span>
		</div>
		<div class="sp-confirm-body">
			<div id="spapi_upgrade_message"></div>
			<div id="spapi_upgrade_intro">
				<p>Upgrade to a paid plan to continue using the Seo Panel API without limits.</p>
			</div>
			<div id="spapi_upgrade_plans" style="display:none;">
				<p><strong>Available Plans:</strong></p>
				<div class="spapi-plans-container" id="spapi_upgrade_plans_list"></div>
				<div class="mt-2" style="font-size:13px;">For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong><i class="fas fa-envelope"></i> contact us</strong></a>.</div>
			</div>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-cancel" onclick="window.spapiUpgradeCancel()">Cancel</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_upgrade_btn_plans" onclick="window.spapiUpgradeLoadPlans()">See Upgrade Plans</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.spapiShowUpgradePopup = function() {
	$('#spapi_upgrade_message').html('');
	$('#spapi_upgrade_intro').show();
	$('#spapi_upgrade_plans').hide();
	$('#spapi_upgrade_plans_list').html('');
	$('#spapi_upgrade_btn_plans').show().prop('disabled', false).text('See Upgrade Plans');
	window.spapiUpgradePlansData = [];
	$('#spapi_upgrade_overlay').fadeIn(200);
};

window.spapiUpgradeCancel = function() {
	$('#spapi_upgrade_overlay').fadeOut(200);
};

window.spapiUpgradeLoadPlans = function() {
	var apiUrl = '<?php echo defined("SP_SPAPI_URL") ? SP_SPAPI_URL : "http://api.seopanel.org/api/v1"?>';
	var contactNote = '<div class="mt-2" style="font-size:13px;">For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong><i class="fas fa-envelope"></i> contact us</strong></a>.</div>';
	$('#spapi_upgrade_btn_plans').prop('disabled', true).text('Loading...');

	$.ajax({
		url: apiUrl + '/plans',
		type: 'GET',
		dataType: 'json',
		timeout: 10000,
		success: function(response) {
			var allPlans = response.data || response.plans || response;
			var plans = Array.isArray(allPlans) ? allPlans.filter(function(p) {
				return p.price && parseFloat(p.price) > 0;
			}) : [];

			if (plans.length > 0) {
				window.spapiUpgradePlansData = plans;
				var html = '';
				for (var i = 0; i < plans.length; i++) {
					var plan = plans[i];
					var serpLimit = (plan.limits && plan.limits.SERP) ? plan.limits.SERP : 0;
					html += '<div class="spapi-plan-card" style="cursor:pointer;" data-plan-index="' + i + '" onclick="window.spapiUpgradeSelectPlan(' + i + ')">';
					html += '<div class="spapi-plan-name">' + (plan.name || plan.plan_name || 'Plan') + '</div>';
					html += '<div class="spapi-plan-price">$' + plan.price + '/mo</div>';
					if (plan.monthly_limit) {
						html += '<div class="spapi-plan-detail">' + plan.monthly_limit.toLocaleString() + ' requests/mo</div>';
					}
					if (serpLimit) {
						html += '<div class="spapi-plan-detail">' + serpLimit.toLocaleString() + ' SERPs/mo</div>';
					}
					if (plan.features && Array.isArray(plan.features)) {
						for (var j = 0; j < plan.features.length; j++) {
							html += '<div class="spapi-plan-detail">' + plan.features[j] + '</div>';
						}
					}
					html += '</div>';
				}
				$('#spapi_upgrade_plans_list').html(html);
				$('#spapi_upgrade_intro').hide();
				$('#spapi_upgrade_plans').show();
				$('#spapi_upgrade_btn_plans').hide();
			} else {
				$('#spapi_upgrade_message').html('<div class="alert alert-info">No paid plans available at the moment.' + contactNote + '</div>');
				$('#spapi_upgrade_btn_plans').hide();
			}
		},
		error: function() {
			$('#spapi_upgrade_message').html('<div class="alert alert-danger">Could not load plans. Please try again later.' + contactNote + '</div>');
			$('#spapi_upgrade_btn_plans').prop('disabled', false).text('Try Again');
		}
	});
};

window.spapiUpgradePlansData = [];

window.spapiUpgradeSelectPlan = function(index) {
	var plan = window.spapiUpgradePlansData[index];
	if (plan && plan.plan_link) {
		window.open(plan.plan_link, '_blank');
	}
};
</script>
