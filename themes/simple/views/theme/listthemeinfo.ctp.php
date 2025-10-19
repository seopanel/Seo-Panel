<?php echo showSectionHead('Theme Details'); ?>
<div class="mb-3">
	<a href="javascript:void(0)" onclick="scriptDoLoad('themes-manager.php?pageno=<?php echo $pageNo?>', 'content')" class="btn btn-secondary">
		<i class="ri-arrow-left-line"></i> &#171&#171 Back
	</a>
</div>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $themeInfo['label']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Theme']?>:</strong></td>
		<td class="td_right_col"><?php echo $themeInfo['name']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Version']?>:</strong></td>
		<td class="td_right_col"><?php echo $themeInfo['version']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Author']?>:</strong></td>
		<td class="td_right_col"><?php echo $themeInfo['author']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Website']?>:</strong></td>
		<td class="td_right_col"><a href="<?php echo $themeInfo['website']?>" target="_blank"><?php echo $themeInfo['website']?></a></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Description']?>:</strong></td>
		<td class="td_right_col"><?php echo $themeInfo['description']?></td>
	</tr>
</table>
