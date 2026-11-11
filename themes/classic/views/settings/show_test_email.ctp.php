<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Test Email Settings']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#testEmailHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="testEmailHelpModal" tabindex="-1" role="dialog" aria-labelledby="testEmailHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-paper-plane"></i></div>
				<h5 class="modal-title" id="testEmailHelpModalLabel">About Test Email Settings</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Sends a real test email using the SMTP/SendGrid credentials configured under Mail Settings, so you can confirm outgoing email actually works before relying on it for notifications or scheduled reports.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Mail Settings needs to be configured first, or this test will fail the same way real notification emails would.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id="emailForm">
<input type="hidden" value="send_test_email" name="sec">
<table class="list">
	<tr class="listHead">
		<td width='30%'><?php echo $spTextPanel['Test Email Settings']?></td>
		<td>&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['Email']?>:</strong></td>
		<td class="td_right_col"><input type="email" name="test_email" value="" class="form-control"><?php echo $errMsg['name']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong>Debug:</strong></td>
		<td class="td_right_col">
			<select name="debug_mail" class="custom-select" style="width:150px;">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('settings.php?sec=test_email', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('settings.php', 'emailForm', 'subcontent')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextSettings['Send Email']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<br>
<div id="subcontent" class="mt-4 pt-4"></div>
