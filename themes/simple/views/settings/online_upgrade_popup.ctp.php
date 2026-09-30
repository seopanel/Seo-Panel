<div class="sp-confirm-overlay" id="online_upgrade_overlay" style="display:none;">
	<div class="sp-confirm-box" style="max-width: 600px; width: 95%">
		<div class="sp-confirm-header">
			<i class="fas fa-cloud-download-alt"></i>
			<span><?php echo $spTextSettings['Upgrade Now']?></span>
		</div>
		<div class="sp-confirm-body">
			<div id="online_upgrade_message"></div>
			<div id="online_upgrade_intro">
				<p>This will download the latest Seo Panel release and update your application files automatically. Any files being replaced are backed up first.</p>
				<p><strong>This does NOT back up your database.</strong> We strongly recommend taking your own database backup before continuing.</p>
				<p>After the files are updated, you'll be taken to the upgrade wizard to finish applying any database changes with one more click.</p>
			</div>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-cancel" id="online_upgrade_btn_cancel" onclick="$('#online_upgrade_overlay').fadeOut(200)">
				<i class="fas fa-ban" style="margin-right:5px;"></i>Close
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="online_upgrade_btn_confirm" onclick="window.onlineUpgradeProceed()">
				<i class="fas fa-check" style="margin-right:5px;"></i>Confirm Upgrade
			</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.onlineUpgradeStart = function() {
	$('#online_upgrade_message').html('');
	$('#online_upgrade_intro').show();
	$('#online_upgrade_btn_confirm').show().prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Checking...');
	$('#online_upgrade_overlay').fadeIn(200);

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=onlineupgradecheck',
		type: 'GET',
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success' && response.data && response.data.outdated && response.data.preflight) {
				$('#online_upgrade_btn_confirm').prop('disabled', false).html('<i class="fas fa-check" style="margin-right:5px;"></i>Confirm Upgrade');
			} else if (response.status === 'success' && response.data && !response.data.outdated) {
				$('#online_upgrade_intro').hide();
				$('#online_upgrade_btn_confirm').hide();
				$('#online_upgrade_message').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Your Seo Panel installation is already up to date.</div>');
			} else {
				window.onlineUpgradeShowError(response);
			}
		},
		error: function() {
			window.onlineUpgradeShowError({message: 'Could not check for updates. Please try again later.'});
		}
	});
};

window.onlineUpgradeShowError = function(response) {
	$('#online_upgrade_intro').hide();
	$('#online_upgrade_btn_confirm').hide();
	var fallback = (response.data && response.data.fallback_url)
		? ' <a href="' + response.data.fallback_url + '" target="_blank">Download manually <i class="fas fa-external-link-alt"></i></a>'
		: '';
	$('#online_upgrade_message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + response.message + fallback + '</div>');
};

window.onlineUpgradeProceed = function() {
	$('#online_upgrade_message').html('');
	$('#online_upgrade_btn_confirm').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:5px;"></i>Upgrading...');
	$('#online_upgrade_btn_cancel').prop('disabled', true);

	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=onlineupgradeproceed',
		type: 'POST',
		dataType: 'json',
		timeout: 180000,
		success: function(response) {
			if (response.status === 'success') {
				window.location.href = '<?php echo SP_WEBPATH?>/' + response.data.redirect;
			} else {
				window.onlineUpgradeShowError(response);
				$('#online_upgrade_btn_cancel').prop('disabled', false);
			}
		},
		error: function() {
			window.onlineUpgradeShowError({message: 'Upgrade failed. Please try the manual download instead.'});
			$('#online_upgrade_btn_cancel').prop('disabled', false);
		}
	});
};
</script>
