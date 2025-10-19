<?php echo showSectionHead($spText['label']['Version']); ?>
<table class="list">
	<tr class="listHead">
		<td colspan="2"><?php echo $spText['label']['Version']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col" style="width: 30%"><strong>Installed Version</strong></td>
		<td class="td_right_col"><?php echo SP_VERSION_NUMBER?></td>
	</tr>
	<tr class="blue_row">
		<td colspan="2" id="checkversion" class="text-center" style="padding: 20px;">
			<a class="btn btn-primary" href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'checkversion', 'sec=checkversion')">
			    <?php echo $spTextSettings['Check for Updates']?>
			</a>
		</td>
	</tr>
</table>
