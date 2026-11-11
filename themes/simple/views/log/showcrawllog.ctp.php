<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextLog['Crawl Log Details']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#crawlLogDetailsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="crawlLogDetailsHelpModal" tabindex="-1" role="dialog" aria-labelledby="crawlLogDetailsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-search"></i></div>
				<h5 class="modal-title" id="crawlLogDetailsHelpModalLabel">About Crawl Log Details</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>This is the full record for one crawl attempt: the exact URL, referer, cookies, POST fields, user agent, and proxy used, plus the raw success or failure message. It's useful when a report's data looks wrong and you need to see exactly what was fetched.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
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
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_type'])?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Reference']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['ref_id'])?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Subject']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['subject'])?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Url']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_link'])?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Referer']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_referer'])?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Cookie']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_cookie'])?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spTextLog['Post Fields']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_post_fields'])?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['User agent']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['crawl_useragent'])?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Proxy']?>:</strong></td>
		<td class="td_right_col"><?php echo !empty($logInfo['proxy_id']) ? intval($logInfo['proxy_id']) : ""?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Details']?>:</strong></td>
		<td class="td_right_col"><?php echo htmlspecialchars($logInfo['log_message'])?></td>
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
