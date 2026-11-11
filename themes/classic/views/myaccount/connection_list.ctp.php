<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel["Connections"]); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#connectionsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="connectionsHelpModal" tabindex="-1" role="dialog" aria-labelledby="connectionsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-link"></i></div>
				<h5 class="modal-title" id="connectionsHelpModalLabel">About Connections</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>This connects your own Google account via OAuth, which is what powers Webmaster Tools, Website Analytics, and any other feature that reads data from your Google account on your behalf.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This is per-user: each account connects its own Google login, separate from the Google API keys configured in Settings.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
if ($successMsg) {
	showSuccessMsg($successMsg, false);
}

if ($errorMsg) {
	showErrorMsg($errorMsg, false);
}
?>
<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Name']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 15%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4;
	if (count($list) > 0) {

		foreach ($list as $i => $listInfo) {
			?>
			<tr>
				<td><?php echo $i + 1;?></td>
				<td><?php echo ucfirst($listInfo['name'])?></td>
				<td class="text-center">
					<?php
					if ($listInfo['status']) {
						echo "<span class='badge badge-success py-2 px-3 text-light'>{$spTextMyAccount['Connected']}</span>";
					} else {
						echo "<span class='badge badge-danger py-2 px-3 text-light'>{$spTextMyAccount['Disconnected']}</span>";
					}
					?>
				</td>
				<td class="text-center">
					<?php
					if ($listInfo['status']) {
						$disconnectFun = SP_DEMO ? "alertDemoMsg()" :  "confirmLoad('connections.php', 'content', 'action=disconnect&category={$listInfo['name']}')";
						?>
						<a onclick="<?php echo $disconnectFun?>" href="javascript:void(0);" class="btn btn-danger"><?php echo $spTextMyAccount['Disconnect']?></a>
						<?php
					} else {

						// check whether auth url set
						if ($listInfo['auth_url_info']['auth_url']) {
							?>
							<a href="<?php echo $listInfo['auth_url_info']['auth_url']?>" class="btn btn-success"><?php echo $spTextMyAccount['Connect']?></a>
							<?php
						} else {
							echo "<span class='text-danger fw-bold'>{$listInfo['auth_url_info']['msg']}</span>";
						}

					}
					?>
				</td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
