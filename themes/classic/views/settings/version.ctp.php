<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
include(SP_VIEWPATH.'/settings/_settings_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spText['label']['Version']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#versionHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="versionHelpModal" tabindex="-1" role="dialog" aria-labelledby="versionHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-code-branch"></i></div>
				<h5 class="modal-title" id="versionHelpModalLabel">About Version</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Shows the SEO Panel version currently installed, and whether a newer version is available.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<div class="aiv-card">
	<div class="sp-settings-row">
		<div class="sp-settings-row-label">Installed Version</div>
		<div class="sp-settings-row-control"><?php echo SP_VERSION_NUMBER?></div>
	</div>
	<div class="sp-settings-row" id="checkversion" style="align-items:center;">
		<div class="sp-settings-row-label">Check for the latest release</div>
		<div class="sp-settings-row-control">
			<a class="btn btn-primary" href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'checkversion', 'sec=checkversion')">
			    <?php echo $spTextSettings['Check for Updates']?>
			</a>
		</div>
	</div>
	<div class="sp-settings-row" style="align-items:center;">
		<div class="sp-settings-row-label">
			Already uploaded newer files?
			<small>Use this to finish migrating the database without downloading anything.</small>
		</div>
		<div class="sp-settings-row-control">
			<!-- Direct link to the manual upgrade wizard (install/upgrade.php)
				 - previously the only way an admin could reach it was typing
				 the URL themselves; this "Upgrade Now" / "View Update" page
				 only ever linked to the automated online-download flow
				 (window.onlineUpgradeStart()), which downloads and replaces
				 files first. An admin who already uploaded newer files
				 themselves (the standard self-hosted upgrade method) just
				 needs to run the DB migration, with nothing to download. -->
			<a class="btn btn-outline-primary" href="<?php echo SP_WEBPATH; ?>/install/upgrade.php" target="_blank">
				<i class="fas fa-database"></i> Run Database Migration
			</a>
		</div>
	</div>
</div>
<?php include(SP_VIEWPATH."/settings/online_upgrade_popup.ctp.php"); ?>
