<div class="sp-confirm-overlay" id="spapi_popup_overlay" style="display:block;">
	<div class="sp-confirm-box" style="max-width: 640px;">
		<div class="sp-confirm-header">
			<i class="fas fa-plug"></i>
			<span>Register for Seo Panel API</span>
		</div>
		<div class="sp-confirm-body">
			<div id="spapi_popup_message"></div>
			<div id="spapi_popup_intro">
				<p>Register to access Seo Panel API features and services. It's free!</p>
			</div>
			<div id="spapi_popup_plans" style="display:none;">
				<p><strong>Available Plans:</strong></p>
				<div class="spapi-plans-container" id="spapi_plans_list"></div>
				<div class="mt-2" style="font-size:13px;">For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong><i class="fas fa-envelope"></i> contact us</strong></a>.</div>
			</div>
			<div id="spapi_popup_form" style="display:none;">
				<div class="form-group mb-2">
					<label><strong>Name <span style="color:red;">*</span></strong></label>
					<input type="text" id="spapi_name" class="form-control" placeholder="Name">
				</div>
				<div class="form-group mb-2">
					<label><strong>Email <span style="color:red;">*</span></strong></label>
					<input type="email" id="spapi_email" class="form-control" placeholder="Email Address">
				</div>
			</div>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-cancel" id="spapi_btn_skip" onclick="window.spapiSkip()">Skip</button>
			<button class="sp-confirm-btn sp-confirm-btn-cancel" id="spapi_btn_cancel" onclick="window.spapiCancel()">Cancel</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_register" onclick="window.spapiLoadPlans()">Register</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_continue" style="display:none;" onclick="window.spapiShowRegistrationForm()">Continue</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_submit" style="display:none;" onclick="window.spapiSubmit()">Submit</button>
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
	$('#spapi_btn_register').show().prop('disabled', false).text('Register');
	$('#spapi_btn_skip').show();
	$('#spapi_btn_cancel').show().text('Cancel');
	$('#spapi_btn_continue').hide();
	$('#spapi_btn_submit').hide().prop('disabled', false).text('Submit');
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
	var apiUrl = '<?php echo defined("SP_SPAPI_URL") ? SP_SPAPI_URL : "http://api.seopanel.org/api/v1"?>';
	$('#spapi_btn_register').prop('disabled', true).text('Loading...');

	$.ajax({
		url: apiUrl + '/plans',
		type: 'GET',
		dataType: 'json',
		timeout: 10000,
		success: function(response) {
			var plans = response.data || response.plans || response;
			if (Array.isArray(plans) && plans.length > 0) {
				window.spapiPlansData = plans;
				var html = '';
				for (var i = 0; i < plans.length; i++) {
					var plan = plans[i];
					var isFree = !plan.price || parseFloat(plan.price) === 0;
					var activeClass = isFree ? ' active' : '';
					var priceText = isFree ? 'Free' : '$' + plan.price + '/mo';

					if (isFree) {
						window.spapiSelectedPlan = i;
					}

					html += '<div class="spapi-plan-card' + activeClass + '" data-plan-index="' + i + '" onclick="window.spapiSelectPlan(' + i + ')">';
					html += '<div class="spapi-plan-name">' + (plan.name || plan.plan_name || 'Plan') + '</div>';
					html += '<div class="spapi-plan-price">' + priceText + '</div>';
					if (plan.monthly_limit) {
						html += '<div class="spapi-plan-detail">' + plan.monthly_limit.toLocaleString() + ' requests/mo</div>';
					}
					if (plan.serp_limit) {
						html += '<div class="spapi-plan-detail">' + plan.serp_limit.toLocaleString() + ' SERPs/mo</div>';
					}
					if (plan.features && Array.isArray(plan.features)) {
						for (var j = 0; j < plan.features.length; j++) {
							html += '<div class="spapi-plan-detail">' + plan.features[j] + '</div>';
						}
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

window.spapiShowRegistrationForm = function() {
	if (window.spapiSelectedPlan !== null && window.spapiPlansData[window.spapiSelectedPlan]) {
		var plan = window.spapiPlansData[window.spapiSelectedPlan];
		var isFree = !plan.price || parseFloat(plan.price) === 0;
		if (!isFree && plan.plan_link) {
			window.open(plan.plan_link, '_blank');
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
		$('#spapi_popup_message').html('<div class="alert alert-danger">Please fill in Name and Email.</div>');
		return;
	}

	$('#spapi_btn_submit').prop('disabled', true).text('Registering...');
	$('#spapi_popup_message').html('');

	var contactNote = '<div class="mt-2" style="font-size:13px;">For any questions, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong><i class="fas fa-envelope"></i> contact us</strong></a>.</div>';

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
				$('#spapi_popup_message').html('<div class="alert alert-success">' + response.message + contactNote + '</div>');
				$('#spapi_popup_form').hide();
				$('#spapi_btn_submit').hide();
				$('#spapi_btn_skip').hide();
				$('#spapi_btn_cancel').text('Close');
				setTimeout(function() {
					$('#spapi_popup_overlay').fadeOut(200, function() { $(this).remove(); });
					scriptDoLoad('settings.php?category=seopanel_api', 'content', 'layout=ajax');
				}, 1500);
			} else {
				$('#spapi_popup_message').html('<div class="alert alert-danger">' + response.message + contactNote + '</div>');
				$('#spapi_btn_submit').prop('disabled', false).text('Submit');
			}
		},
		error: function() {
			$('#spapi_popup_message').html('<div class="alert alert-danger">An error occurred. Please try again later.' + contactNote + '</div>');
			$('#spapi_btn_submit').prop('disabled', false).text('Submit');
		}
	});
};
</script>
