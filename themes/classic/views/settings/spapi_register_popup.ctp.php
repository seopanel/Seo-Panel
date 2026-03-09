<div class="sp-confirm-overlay" id="spapi_popup_overlay" style="display:block;">
	<div class="sp-confirm-box" style="max-width: 920px; width: 95%">
		<div class="sp-confirm-header">
			<i class="fas fa-plug"></i>
			<span>Register for Seo Panel API</span>
		</div>
		<div class="sp-confirm-body">
			<div id="spapi_popup_message"></div>
			<div id="spapi_popup_intro">
				<div class="spapi-upgrade-alert-box" style="background:#e8f5e9; border-color:#66bb6a; border-left-color:#43a047;">
					<div class="spapi-upgrade-alert-icon" style="color:#2e7d32;">
						<i class="fas fa-plug"></i>
					</div>
					<div class="spapi-upgrade-alert-text">
						<div>Connect to the <strong>Seo Panel API</strong> to unlock additional SEO features and services.</div>
						<div class="spapi-upgrade-alert-sub">Registration is completely free — no credit card required.</div>
					</div>
				</div>
			</div>
			<div id="spapi_popup_plans" style="display:none;">
				<div class="spapi-plans-header">
					<i class="fas fa-layer-group"></i> <strong>Choose a Plan</strong>
				</div>
				<div class="spapi-plans-container" id="spapi_plans_list"></div>
				<div class="spapi-upgrade-contact">
					<i class="fas fa-envelope"></i> For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong>contact us</strong></a>.
				</div>
			</div>
			<div id="spapi_popup_form" style="display:none;">
				<div class="form-group mb-2">
					<label><strong><i class="fas fa-user" style="color:#0a66d1; margin-right:5px;"></i>Name <span style="color:red;">*</span></strong></label>
					<input type="text" id="spapi_name" class="form-control" placeholder="Your full name">
				</div>
				<div class="form-group mb-2">
					<label><strong><i class="fas fa-envelope" style="color:#0a66d1; margin-right:5px;"></i>Email <span style="color:red;">*</span></strong></label>
					<input type="email" id="spapi_email" class="form-control" placeholder="Your email address">
				</div>
			</div>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-skip" id="spapi_btn_skip" onclick="window.spapiSkip()">
				<i class="fas fa-times" style="margin-right:5px;"></i>Skip
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-cancel" id="spapi_btn_cancel" onclick="window.spapiCancel()">
				<i class="fas fa-ban" style="margin-right:5px;"></i>Cancel
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_register" onclick="window.spapiLoadPlans()">
				<i class="fas fa-rocket" style="margin-right:5px;"></i>Register
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_continue" style="display:none;" onclick="window.spapiShowRegistrationForm()">
				<i class="fas fa-arrow-right" style="margin-right:5px;"></i>Continue
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_submit" style="display:none;" onclick="window.spapiSubmit()">
				<i class="fas fa-paper-plane" style="margin-right:5px;"></i>Submit
			</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.spapiShowPopup = function(showSkip) {
	if (showSkip === false) {
		$('#spapi_btn_skip').hide();
	} else {
		$('#spapi_btn_skip').show();
	}
	$('#spapi_popup_overlay').fadeIn(200);
};

window.spapiCancel = function() {
	$('#spapi_popup_overlay').fadeOut(200, function() {
		window.spapiResetPopup();
	});
};

window.spapiResetPopup = function() {
	$('#spapi_popup_message').html('');
	$('#spapi_popup_intro').show();
	$('#spapi_popup_plans').hide();
	$('#spapi_popup_form').hide();
	$('#spapi_btn_register').show().prop('disabled', false).html('<i class="fas fa-rocket" style="margin-right:5px;"></i>Register');
	$('#spapi_btn_skip').show();
	$('#spapi_btn_cancel').show().html('<i class="fas fa-ban" style="margin-right:5px;"></i>Cancel');
	$('#spapi_btn_continue').hide();
	$('#spapi_btn_submit').hide().prop('disabled', false).html('<i class="fas fa-paper-plane" style="margin-right:5px;"></i>Submit');
	$('#spapi_name').val('');
	$('#spapi_email').val('');
	window.spapiSelectedPlan = null;
	window.spapiPlansData = [];
};

window.spapiSkip = function() {
	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=spapi_skip',
		type: 'GET',
		dataType: 'json',
		success: function() {
			$('#spapi_popup_overlay').fadeOut(200, function() {
				window.spapiResetPopup();
			});
		},
		error: function() {
			$('#spapi_popup_overlay').fadeOut(200, function() {
				window.spapiResetPopup();
			});
		}
	});
};

window.spapiSelectedPlan = null;
window.spapiPlansData = [];

window.spapiLoadPlans = function() {
	$('#spapi_btn_register').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Loading...');

	var planIcons = ['fa-gift', 'fa-bolt', 'fa-star', 'fa-crown'];

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=spapi_plans',
		type: 'GET',
		dataType: 'json',
		timeout: 15000,
		success: function(response) {
			var plans = response.data || response.plans || response;
			if (Array.isArray(plans) && plans.length > 0) {
				window.spapiPlansData = plans;
				var html = '';
				for (var i = 0; i < plans.length; i++) {
					var plan = plans[i];
					var isFree = !plan.price || parseFloat(plan.price) === 0;
					var activeClass = isFree ? ' active' : '';
					var priceText = isFree ? 'Free' : '$' + plan.price + '<span>/mo</span>';
					var icon = planIcons[i] || 'fa-layer-group';
					var serpLimit = (plan.limits && plan.limits.SERP) ? plan.limits.SERP : (plan.serp_limit || 0);

					if (isFree) {
						window.spapiSelectedPlan = i;
					}

					var paidIndex = 0;
					for (var k = 0; k < i; k++) {
						if (plans[k].price && parseFloat(plans[k].price) > 0) paidIndex++;
					}
					var isPopular = !isFree && paidIndex === 0;

					var cardOnclick = isFree ? 'window.spapiSelectPlan(' + i + ')' : 'window.spapiOpenPlanLink(' + i + ')';
					html += '<div class="spapi-plan-card' + activeClass + '" data-plan-index="' + i + '" onclick="' + cardOnclick + '">';
					if (isPopular) {
						html += '<span class="spapi-plan-badge">Popular</span>';
					}
					html += '<div class="spapi-plan-icon"><i class="fas ' + icon + '"></i></div>';
					html += '<div class="spapi-plan-name">' + (plan.name || plan.plan_name || 'Plan') + '</div>';
					html += '<div class="spapi-plan-price">' + priceText + '</div>';
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
					if (!isFree) {
						html += '<div class="spapi-plan-cta"><span class="spapi-plan-cta-btn"><i class="fas fa-external-link-alt" style="margin-right:5px;"></i>Get Started</span></div>';
					} else {
						html += '<div class="spapi-plan-cta"><span class="spapi-plan-cta-btn cta-free"><i class="fas fa-check-circle" style="margin-right:5px;"></i>Select</span></div>';
					}
					html += '</div>';
				}
				$('#spapi_plans_list').html(html);
				$('#spapi_popup_intro').hide();
				$('#spapi_popup_plans').show();
				$('#spapi_btn_register').hide();
				$('#spapi_btn_continue').show();
			} else {
				window.spapiShowRegistrationForm();
			}
		},
		error: function() {
			window.spapiShowRegistrationForm();
		}
	});
};

window.spapiSelectPlan = function(index) {
	window.spapiSelectedPlan = index;
	$('.spapi-plan-card').removeClass('active');
	$('.spapi-plan-card[data-plan-index="' + index + '"]').addClass('active');
};

window.spapiOpenPlanLink = function(index) {
	var plan = window.spapiPlansData[index];
	if (plan) {
		var url = plan.plan_link || '<?php echo SP_CONTACT_LINK?>';
		window.open(url, '_blank');
	}
};

window.spapiShowRegistrationForm = function() {
	if (window.spapiSelectedPlan !== null && window.spapiPlansData[window.spapiSelectedPlan]) {
		var plan = window.spapiPlansData[window.spapiSelectedPlan];
		var isFree = !plan.price || parseFloat(plan.price) === 0;
		if (!isFree) {
			var url = plan.plan_link || '<?php echo SP_CONTACT_LINK?>';
			window.open(url, '_blank');
			return;
		}
	}
	$('#spapi_popup_intro').hide();
	$('#spapi_popup_plans').hide();
	$('#spapi_popup_form').show();
	$('#spapi_btn_register').hide();
	$('#spapi_btn_continue').hide();
	$('#spapi_btn_submit').show();
};

window.spapiSubmit = function() {
	var name = $('#spapi_name').val();
	var email = $('#spapi_email').val();

	if (!name || !email) {
		$('#spapi_popup_message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>Please fill in Name and Email.</div>');
		return;
	}

	$('#spapi_btn_submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Registering...');
	$('#spapi_popup_message').html('');

	var contactNote = '<div class="spapi-upgrade-contact"><i class="fas fa-envelope"></i> For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong>contact us</strong></a>.</div>';

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php',
		type: 'POST',
		data: {
			sec: 'spapi_register',
			name: name,
			email: email
		},
		dataType: 'json',
		success: function(response) {
			if (response.status == 'success') {
				$('#spapi_popup_message').html('<div class="alert alert-success"><i class="fas fa-check-circle" style="margin-right:5px;"></i>' + response.message + contactNote + '</div>');
				$('#spapi_popup_form').hide();
				$('#spapi_btn_submit').hide();
				$('#spapi_btn_skip').hide();
				$('#spapi_btn_cancel').html('<i class="fas fa-times" style="margin-right:5px;"></i>Close').off('click').on('click', function() {
					$('#spapi_popup_overlay').fadeOut(200, function() { $(this).remove(); });
					scriptDoLoad('settings.php?category=seopanel_api', 'content', 'layout=ajax');
				});
			} else {
				$('#spapi_popup_message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>' + response.message + contactNote + '</div>');
				$('#spapi_btn_submit').prop('disabled', false).html('<i class="fas fa-paper-plane" style="margin-right:5px;"></i>Submit');
			}
		},
		error: function() {
			$('#spapi_popup_message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>An error occurred. Please try again later.' + contactNote + '</div>');
			$('#spapi_btn_submit').prop('disabled', false).html('<i class="fas fa-paper-plane" style="margin-right:5px;"></i>Submit');
		}
	});
};
</script>
