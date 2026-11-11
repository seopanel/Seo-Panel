<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextLog['Mail Log Details']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#mailLogDetailsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="mailLogDetailsHelpModal" tabindex="-1" role="dialog" aria-labelledby="mailLogDetailsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-envelope-open-text"></i></div>
				<h5 class="modal-title" id="mailLogDetailsHelpModalLabel">About Mail Log Details</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>This is the full record for one outgoing email: its subject, category, recipients, Success or Fail status, and the raw send message. It's useful for diagnosing a notification or scheduled report email that didn't arrive.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<table id="cust_tab">
	<tr class="form_head">
		<th width='30%'><?php echo $spTextLog['Mail Log Details']?></th>
		<th>&nbsp;</th>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['label']['Subject']?>:</td>
		<td><?php echo htmlspecialchars($logInfo['subject'])?></td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Category']?>:</td>
		<td><?php echo htmlspecialchars($logInfo['mail_category'])?></td>
	</tr>
	<tr class="form_data">
		<td>TO:</td>
		<td><?php echo htmlspecialchars($logInfo['to_address'])?></td>
	</tr>
	<tr class="form_data">
		<td>CC:</td>
		<td><?php echo htmlspecialchars($logInfo['cc_address'])?></td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['label']['From']?>:</td>
		<td><?php echo htmlspecialchars($logInfo['from_address'])?></td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Status']?>:</td>
		<td>
			<?php 
			if ($logInfo['status']) {
				echo "<b class='success'>{$spText['label']['Success']}</b>";
			} else {
				echo "<b class='error'>{$spText['label']['Fail']}</b>";
			}
			?>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Details']?>:</td>
		<td><?php echo htmlspecialchars($logInfo['log_message'])?></td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['label']['Updated']?>:</td>
		<td><?php echo $logInfo['log_time']?></td>
	</tr>
</table>