<?php 
echo showSectionHead($spTextLog['Mail Log Details']);
?>
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