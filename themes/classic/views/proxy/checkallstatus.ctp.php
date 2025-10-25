<div id="run_project">
	<div id="run_info">
		<table class="list">
        	<tr class="listHead">
        		<td colspan="4"><?php echo $spText['button']['Check Status']?></td>
        	</tr>
        	<tr class="white_row">
        		<td class="td_left_col" width="20%"><strong><?php echo $spText['common']['Total']?>:</strong></td>
        		<td class="td_right_col" width="30%"><?php echo count($proxyList)?></td>
        		<td class="td_left_col" width="20%"><strong><?php echo $spText['common']['Checked']?>:</strong></td>
        		<td class="td_right_col" id='checked_count'>0</td>
        	</tr>
        	<tr class="blue_row">
        		<td class="td_left_col"><strong><?php echo $spText['common']["Active"]?>:</strong></td>
        		<td class="td_right_col" id="active_count"><?php echo $activeCount?></td>
        		<td class="td_left_col"><strong><?php echo $spText['common']["Inactive"]?>:</strong></td>
        		<td class="td_right_col" id="inactive_count"><?php echo $inActiveCount?></td>
        	</tr>
        </table>
	</div>
	<?php if (count($proxyList)) {
		$proxyInfo = $proxyList[0];
		$statusVar = isset($status) ? "&status=$status" : "";
		$scriptUrl = "proxy.php?sec=runcheckstatus&id=".$proxyInfo['id'].$statusVar;
		?>
		<p class='alert alert-info mt-3'>
			<?php echo $spTextSA['pressescapetostopexecution']?>.
			<a <?php echo scriptPostAJAXLink('proxy.php', 'listform', 'subcontent')?> href='javascript:void(0);' class="btn btn-link">
				<?php echo $spText['label']['Click Here']?>
			</a>
			<?php echo $spTextSA['to run project again if you stopped execution']?>.
		</p>
		<div id="subcontmed">
			<script>scriptDoLoad('<?php echo $scriptUrl?>', 'subcontmed');</script>
		</div>
	<?php }?>
</div>
