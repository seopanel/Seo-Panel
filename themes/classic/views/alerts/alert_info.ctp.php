<?php
echo showSectionHead($spTextPanel["Alerts"]);

$alertUrl = "";
if (!empty($listInfo['alert_url'])) {
    $alertUrl = stristr($listInfo['alert_url'], 'http') ? $listInfo['alert_url'] : Spider::addTrailingSlash(SP_WEBPATH) . $listInfo['alert_url'];
}
?>
<table class="list">
	<tr class="listHead">
		<td width='30%'><?php echo $spTextPanel["Alerts"]?></td>
		<td>&nbsp;</td>
	</tr>
	<tr class="table-<?php echo $listInfo['alert_type']?>">
		<td class="td_left_col"><strong><?php echo $spText['label']['Subject']?>:</strong></td>
		<td class="td_right_col"><?php echo $listInfo['alert_subject']?></td>
	</tr>
	<tr class="table-<?php echo $listInfo['alert_type']?>">
		<td class="td_left_col"><strong><?php echo $spText['common']['Details']?>:</strong></td>
		<td class="td_right_col"><?php echo $listInfo['alert_message']?></td>
	</tr>
	<tr>
		<td class="td_left_col"><strong><?php echo $spText['common']['Url']?>:</strong></td>
		<td class="td_right_col"><a target="_blank" href="<?php echo $alertUrl?>"><?php echo $alertUrl?></a></td>
	</tr>
	<tr>
		<td class="td_left_col"><strong><?php echo $spText['common']['Category']?>:</strong></td>
		<td class="td_right_col"><?php echo $alertCategory[$listInfo['alert_category']]?></td>
	</tr>
	<tr>
		<td class="td_left_col"><strong><?php echo $spText['label']['Updated']?>:</strong></td>
		<td class="td_right_col"><?php echo $listInfo['alert_time']?></td>
	</tr>
</table>
