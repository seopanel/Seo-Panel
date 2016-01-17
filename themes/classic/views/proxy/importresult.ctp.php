<div id="run_project" style="margin-top: 40px;">
	<div id="run_info">
		<table width="100%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab">
        	<tr>
        		<th class="leftcell" width="20%"><?php echo $spText['common']['Total']?>:</th>
        		<td width="20%"><?php echo $resInfo['total']?></td>
        		<th width="20%"><?php echo $spTextPanel['Valid']?>:</th>
        		<td><?php echo $resInfo['valid']?></td>
        		<th width="20%"><?php echo $spTextPanel['Existing']?>:</th>
        		<td><?php echo $resInfo['existing']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['common']["Checked"]?>:</th>
        		<td id='checked_count'>0</td>
        		<th width="20%"><?php echo $spText['common']['Active']?>:</th>
        		<td id="active_count">0</td>
        		<th><?php echo $spText['common']["Inactive"]?>:</th>
        		<td id="inactive_count" colspan="3">0</td>
        	</tr>
        </table>
	</div>
	<?php if (count($proxyList)) {
    $proxyInfo = $proxyList[0];
    $scriptUrl = "proxy.php?sec=runcheckstatus&id=".$proxyInfo['id']."&proxy_max_id=".$proxyMaxId;
    ?>
		<p class='note'>
			<?php echo $spTextSA['pressescapetostopexecution']?>.
		</p>
		<div id="subcontmed">
			<script>scriptDoLoad('<?php echo $scriptUrl?>', 'subcontmed');</script>
		</div>
	<?php 
}?>
</div>
