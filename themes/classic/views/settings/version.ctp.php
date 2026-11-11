<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
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
<table class="list">
	<tr class="listHead">
		<td colspan="2"><?php echo $spText['label']['Version']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col" style="width: 30%"><strong>Installed Version</strong></td>
		<td class="td_right_col"><?php echo SP_VERSION_NUMBER?></td>
	</tr>
	<tr class="blue_row">
		<td colspan="2" id="checkversion" class="text-center" style="padding: 20px;">
			<a class="btn btn-primary" href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'checkversion', 'sec=checkversion')">
			    <?php echo $spTextSettings['Check for Updates']?>
			</a>
		</td>
	</tr>
	<tr class="white_row">
		<td colspan="2" class="text-center" style="padding: 15px;">
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
			<div style="font-size:12px; color:#6c757d; margin-top:8px;">Already uploaded newer files yourself? Use this to finish migrating the database.</div>
		</td>
	</tr>
</table>
<?php include(SP_VIEWPATH."/settings/online_upgrade_popup.ctp.php"); ?>
