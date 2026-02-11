<div class="sp-confirm-overlay" id="spapi_popup_overlay" style="display:block;">
	<div class="sp-confirm-box" style="max-width: 480px;">
		<div class="sp-confirm-header">
			<i class="fas fa-plug"></i>
			<span>Register for Seo Panel API</span>
		</div>
		<div class="sp-confirm-body">
			<div id="spapi_popup_message"></div>
			<div id="spapi_popup_intro">
				<p>Register to access Seo Panel API features and services. It's free!</p>
			</div>
			<div id="spapi_popup_form" style="display:none;">
				<div class="form-group mb-2">
					<label><strong>First Name <span style="color:red;">*</span></strong></label>
					<input type="text" id="spapi_first_name" class="form-control" placeholder="First Name">
				</div>
				<div class="form-group mb-2">
					<label><strong>Last Name</strong></label>
					<input type="text" id="spapi_last_name" class="form-control" placeholder="Last Name">
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
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_register" onclick="window.spapiShowForm()">Register</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="spapi_btn_submit" style="display:none;" onclick="window.spapiSubmit()">Submit</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.spapiShowPopup = function() {
	$('#spapi_popup_overlay').fadeIn(200);
};

window.spapiCancel = function() {
	$('#spapi_popup_overlay').fadeOut(200, function() {
		$(this).remove();
	});
};

window.spapiSkip = function() {
	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=spapi_skip',
		type: 'GET',
		dataType: 'json',
		success: function() {
			$('#spapi_popup_overlay').fadeOut(200, function() {
				$(this).remove();
			});
		},
		error: function() {
			$('#spapi_popup_overlay').fadeOut(200, function() {
				$(this).remove();
			});
		}
	});
};

window.spapiShowForm = function() {
	$('#spapi_popup_intro').hide();
	$('#spapi_popup_form').show();
	$('#spapi_btn_register').hide();
	$('#spapi_btn_submit').show();
};

window.spapiSubmit = function() {
	var firstName = $('#spapi_first_name').val();
	var lastName = $('#spapi_last_name').val();
	var email = $('#spapi_email').val();

	if (!firstName || !email) {
		$('#spapi_popup_message').html('<div class="alert alert-danger">Please fill in First Name and Email.</div>');
		return;
	}

	$('#spapi_btn_submit').prop('disabled', true).text('Registering...');
	$('#spapi_popup_message').html('');

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php',
		type: 'POST',
		data: {
			sec: 'spapi_register',
			first_name: firstName,
			last_name: lastName,
			email: email
		},
		dataType: 'json',
		success: function(response) {
			if (response.status == 'success') {
				$('#spapi_popup_message').html('<div class="alert alert-success">' + response.message + '</div>');
				$('#spapi_popup_form').hide();
				$('#spapi_btn_submit').hide();
				$('#spapi_btn_skip').hide();
				$('#spapi_btn_cancel').text('Close');
				setTimeout(function() {
					$('#spapi_popup_overlay').fadeOut(200, function() { $(this).remove(); });
					scriptDoLoad('settings.php?category=seopanel_api', 'content', 'layout=ajax');
				}, 1500);
			} else {
				$('#spapi_popup_message').html('<div class="alert alert-danger">' + response.message + '</div>');
				$('#spapi_btn_submit').prop('disabled', false).text('Submit');
			}
		},
		error: function() {
			$('#spapi_popup_message').html('<div class="alert alert-danger">An error occurred. Please try again later.</div>');
			$('#spapi_btn_submit').prop('disabled', false).text('Submit');
		}
	});
};
</script>
