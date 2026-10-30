<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<form name="listform" id="listform">
<input type="hidden" name="sec" value="checkAllstatus">
<div class="aiv-page-head">
	<?php echo showSectionHead($spText['button']["Check Status"]); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#proxyCheckStatusHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="proxyCheckStatusHelpModal" tabindex="-1" role="dialog" aria-labelledby="proxyCheckStatusHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-tasks"></i></div>
				<h5 class="modal-title" id="proxyCheckStatusHelpModalLabel">About Check Status</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>This bulk-tests every proxy in the pool, or just the Active or Inactive ones, by actually connecting through each one, and updates each proxy's Active/Inactive status with the live result.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'subcontent', '')";
?>
<table class="search">
	<tr>
		<th><?php echo $spText['label']["Proxy"]?>: </th>
		<td>
			<select name="status" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$inactCheck = $actCheck = "";
				if ($statVal == 'active') {
				    $actCheck = "selected";
				} elseif($statVal == 'inactive') {
				    $inactCheck = "selected";
				}
				?>
				<option value="active" <?php echo $actCheck?> ><?php echo $spText['common']["Active"]?> <?php echo $spText['label']["Proxy"]?></option>
				<option value="inactive" <?php echo $inactCheck?> ><?php echo $spText['common']["Inactive"]?> <?php echo $spText['label']["Proxy"]?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']["Check Status"]?> &gt;&gt;</a>
		</td>
	</tr>
</table>
</form>
<div id="subcontent"></div>
