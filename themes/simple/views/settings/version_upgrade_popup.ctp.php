<div class="sp-confirm-overlay" id="version_upgrade_overlay" style="display:none;">
	<div class="sp-confirm-box" style="max-width: 600px; width: 95%">
		<div class="sp-confirm-header">
			<i class="fas fa-cloud-download-alt"></i>
			<span>New Seo Panel Version Available</span>
		</div>
		<div class="sp-confirm-body">
			<p>A newer version of Seo Panel is available for your installation.</p>
			<p>Head over to <strong>Settings &gt; Version</strong> to review what's new and upgrade.</p>
		</div>
		<div class="sp-confirm-footer">
			<button class="sp-confirm-btn sp-confirm-btn-skip" id="version_upgrade_btn_skip" onclick="window.versionUpgradeSkip()">
				<i class="fas fa-times" style="margin-right:5px;"></i>Remind Me Tomorrow
			</button>
			<button class="sp-confirm-btn sp-confirm-btn-confirm" id="version_upgrade_btn_view" onclick="window.versionUpgradeGoToSettings()">
				<i class="fas fa-arrow-circle-up" style="margin-right:5px;"></i>View Update
			</button>
		</div>
	</div>
</div>
<script type="text/javascript">
window.versionUpgradeShowPopup = function() {
	$('#version_upgrade_overlay').fadeIn(200);
};

window.versionUpgradeSkip = function() {
	$.ajax({
		url: '<?php echo SP_WEBPATH?>/settings.php?sec=version_upgrade_skip',
		type: 'GET',
		dataType: 'json',
		complete: function() {
			$('#version_upgrade_overlay').fadeOut(200);
		}
	});
};

window.versionUpgradeGoToSettings = function() {
	$('#version_upgrade_overlay').fadeOut(200);
	scriptDoLoad('settings.php?sec=version', 'content');
};
</script>
