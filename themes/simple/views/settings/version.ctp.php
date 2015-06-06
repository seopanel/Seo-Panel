<?php echo showSectionHead($spText['label']['Version']); ?>
<table width="60%" cellspacing="0" cellpadding="0" class="summary">
	<tr><td class="topheader" colspan="2"><?php echo $spText['label']['Version']?></td></tr>
	<tr>
		<td class="content" style="border-left: none;width: 30%">Installed Version</td>
		<td class="contentmid" style="text-align: left;padding-left: 10px"><?php echo SP_INSTALLED?></td>
	</tr>
	<tr>
		<td colspan="2" id="checkversion" class="content" style="border-left: none;text-align: center;"><br>
			<a class="actionbut" href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'checkversion', 'sec=checkversion')">
			    <?php echo $spTextSettings['Check for Updates']?>
			</a>
			<br><br>
		</td>
	</tr>
</table>