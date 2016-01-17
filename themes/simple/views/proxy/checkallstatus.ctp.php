<div id="run_project">
	<div id="run_info">
		<table width="100%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab">
        	<tr>
        		<th class="leftcell" width="20%"><?php echo $spText['common']['Total']?>:</th>
        		<td width="40%" style="text-align: left;"><?php echo count($proxyList)?></td>
        		<th width="20%"><?php echo $spText['common']['Checked']?>:</th>
        		<td id='checked_count'>0</td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['common']["Active"]?>:</th>
        		<td style="text-align: left;" id="active_count"><?php echo $activeCount?></td>
        		<th><?php echo $spText['common']["Inactive"]?>:</th>
        		<td id="inactive_count"><?php echo $inActiveCount?></td>
        	</tr>
        </table>
	</div>
	<?php if (count($proxyList)) {
    $proxyInfo = $proxyList[0];
    $statusVar = isset($status) ? "&status=$status" : "";
    $scriptUrl = "proxy.php?sec=runcheckstatus&id=".$proxyInfo['id'].$statusVar;
    ?>
		<p class='note'>
			<?php echo $spTextSA['pressescapetostopexecution']?>.
			<a <?php echo scriptPostAJAXLink('proxy.php', 'listform', 'subcontent')?> href='javascript:void(0);'>
				<?php echo $spText['label']['Click Here']?>
			</a>
			<?php echo $spTextSA['to run project again if you stopped execution']?>.
		</p>
		<div id="subcontmed">
			<script>scriptDoLoad('<?php echo $scriptUrl?>', 'subcontmed');</script>
		</div>
	<?php 
}?>
</div>
