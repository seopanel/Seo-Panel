<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['New Proxy']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#newProxyHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="newProxyHelpModal" tabindex="-1" role="dialog" aria-labelledby="newProxyHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-plus-circle"></i></div>
				<h5 class="modal-title" id="newProxyHelpModalLabel">About New Proxy</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Add a single proxy server to the pool by host and port, with optional username/password if it requires authentication.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>New proxies are added as Inactive by default, so use Check Status to verify and activate them.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<form id="newProxy">
<input type="hidden" name="sec" value="create"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New Proxy']?></td>
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
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('proxy.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('proxy.php', 'newProxy', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
