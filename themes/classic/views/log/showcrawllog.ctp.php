<?php
echo showSectionHead($spTextLog['Crawl Log Details']);

// crawl log is for keyword
if ($logInfo['crawl_type'] == 'keyword') {

	// if ref is is integer get keyword name
	if (!empty($logInfo['keyword'])) {
		$listInfo['ref_id'] = $listInfo['keyword'];
	}

	// find search engine info
	if (preg_match("/^\d+$/", $logInfo['subject'])) {
		$seCtrler = new SearchEngineController();
		$seInfo = $seCtrler->__getsearchEngineInfo($logInfo['subject']);
		$logInfo['subject'] = $seInfo['domain'];
	}

}
?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextLog['Crawl Log Details']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Report Type']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_type']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Reference']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['ref_id']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Subject']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['subject']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Url']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_link']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Referer']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_referer']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Cookie']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_cookie']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spTextLog['Post Fields']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_post_fields']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['User agent']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_useragent']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Proxy']?>:</strong></td>
		<td class="td_right_col"><?php echo !empty($logInfo['proxy_id']) ? $logInfo['proxy_id'] : ""?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Details']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['log_message']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Status']?>:</strong></td>
		<td class="td_right_col">
			<?php
			if ($logInfo['crawl_status']) {
				echo "<span class='badge badge-success py-2 px-3 text-light'>{$spText['label']['Success']}</span>";
			} else {
				echo "<span class='badge badge-danger py-2 px-3 text-light'>{$spText['label']['Fail']}</span>";
			}
			?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Updated']?>:</strong></td>
		<td class="td_right_col"><?php echo $logInfo['crawl_time']?></td>
	</tr>
</table>
