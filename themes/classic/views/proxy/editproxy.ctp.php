<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextProxy['Edit Proxy']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#editProxyHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="editProxyHelpModal" tabindex="-1" role="dialog" aria-labelledby="editProxyHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-plus-circle"></i></div>
				<h5 class="modal-title" id="editProxyHelpModalLabel">About Edit Proxy</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Change this proxy's host, port, authentication credentials, or Active/Inactive status.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<form id="editProxy">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="oldProxy" value="<?php echo htmlspecialchars($post['oldProxy'] ?? '')?>"/>
<input type="hidden" name="id" value="<?php echo intval($post['id'])?>"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextProxy['Edit Proxy']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Proxy']?>:</strong></td>
		<td class="td_right_col"><input type="text" name="proxy" value="<?php echo htmlspecialchars($post['proxy'] ?? '')?>" class="form-control"><?php echo $errMsg['proxy']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Port']?>:</strong></td>
		<td class="td_right_col">
			<input type="text" name="port" value="<?php echo htmlspecialchars($post['port'] ?? '')?>" class="form-control" style="width:60px;"><?php echo $errMsg['port']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Authentication']?>:</strong></td>
		<td class="td_right_col"><input type="checkbox" id="proxy_auth" name="proxy_auth" <?php echo empty($post['proxy_auth']) ? "" : "checked"; ?> > Yes</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spTextProxy['Proxy Username']?>:</strong></td>
		<td class="td_right_col"><input type="text" name="proxy_username" value="<?php echo htmlspecialchars($post['proxy_username'] ?? '')?>" class="form-control"><?php echo $errMsg['proxy_username']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spTextProxy['Proxy Password']?>:</strong></td>
		<td class="td_right_col">
			<input type="password" name="proxy_password" value="<?php echo htmlspecialchars($post['proxy_password'] ?? '')?>" class="form-control"><?php echo $errMsg['proxy_password']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Status']?>:</strong></td>
		<td class="td_right_col">
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
				<?php
				$inactCheck = $actCheck = "";
				if ($post['status']) {
				    $actCheck = "selected";
				} else {
				    $inactCheck = "selected";
				}
				?>
				<option value="1" <?php echo $actCheck?> ><?php echo $spText['common']["Active"]?></option>
				<option value="0" <?php echo $inactCheck?> ><?php echo $spText['common']["Inactive"]?></option>
			</select>
		</td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('proxy.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('proxy.php', 'editProxy', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
