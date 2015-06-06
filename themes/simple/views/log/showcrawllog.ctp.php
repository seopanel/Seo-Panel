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
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextLog['Crawl Log Details']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Report Type']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_type']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Reference']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['ref_id']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Subject']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['subject']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Url']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_link']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Referer']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_referer']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Cookie']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_cookie']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextLog['Post Fields']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_post_fields']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['User agent']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_useragent']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Proxy']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['proxy_id']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Details']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['log_message']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Status']?>:</td>
		<td class="td_right_col">
			<?php
			if ($logInfo['crawl_status']) {
				echo "<b class='success'>{$spText['label']['Success']}</b>";
			} else {
				echo "<b class='error'>{$spText['label']['Fail']}</b>";
			}
			?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Updated']?>:</td>
		<td class="td_right_col"><?php echo $logInfo['crawl_time']?></td>
	</tr>
	<tr class="white_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>
<br><br>