<?php echo showSectionHead($spTextPlugin['Seo Plugin Details']); ?>
<div>&nbsp;<a href="javascript:void(0)" onclick="scriptDoLoad('seo-plugins-manager.php?pageno=<?php echo $pageNo?>', 'content')" class="back">&#171&#171 Back</a></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $pluginInfo['label']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col bold"><?php echo $spTextPlugin['Plugin Name']?>:</td>
		<td class="td_right_col"><?php echo $pluginInfo['label']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col bold"><?php echo $spText['label']['Version']?>:</td>
		<td class="td_right_col"><?php echo $pluginInfo['version']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col bold"><?php echo $spText['label']['Author']?>:</td>
		<td class="td_right_col"><?php echo $pluginInfo['author']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col bold"><?php echo $spText['common']['Website']?>:</td>
		<td class="td_right_col"><a href="<?php echo $pluginInfo['website']?>" target="_blank"><?php echo $pluginInfo['website']?></a></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col bold"><?php echo $spText['label']['Description']?>:</td>
		<td class="td_right_col"><?php echo $pluginInfo['description']?></td>
	</tr>
	<tr class="blue_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>