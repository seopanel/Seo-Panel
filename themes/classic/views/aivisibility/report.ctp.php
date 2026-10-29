<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['AI Referral Report'] ?? 'AI Referral Report'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aivReportHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="aivReportHelpModal" tabindex="-1" role="dialog" aria-labelledby="aivReportHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-external-link-alt"></i></div>
				<h5 class="modal-title" id="aivReportHelpModalLabel"><?php echo $spTextAIV['About AI Referral Report'] ?? 'About AI Referral Report'?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p><?php echo $spTextAIV['Shows real visits that clicked through to your site from a link on an AI platform like ChatGPT or Perplexity - a traffic chart over time, totals by platform, and your top landing pages.'] ?? 'Shows real visits that clicked through to your site from a link on an AI platform like ChatGPT or Perplexity - a traffic chart over time, totals by platform, and your top landing pages.'?></p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span><?php echo $spTextAIV['Needs the tracking snippet from AI Setup installed on your site first. Treat the numbers as a floor, not a full count - some AI clients and most native apps strip or omit the referrer entirely.'] ?? 'Needs the tracking snippet from AI Setup installed on your site first. Treat the numbers as a floor, not a full count - some AI clients and most native apps strip or omit the referrer entirely.'?></span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $spTextAIV['Got it'] ?? 'Got it'?></button>
			</div>
		</div>
	</div>
</div>

<?php $submitAction = "scriptDoLoadPost('aivisibility.php?sec=report', 'search_form', 'content')"; ?>
<form id='search_form'>
<input type="hidden" name="sec" value="report">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="<?php echo $submitAction?>">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo $websiteInfo['name']?></option>
				<?php } ?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
		<td>
			<select name="period" class="custom-select" onchange="<?php echo $submitAction?>">
				<option value="day" <?php echo ($period ?? 'month') == 'day' ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo ($period ?? 'month') == 'week' ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo ($period ?? 'month') == 'month' ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo ($period ?? 'month') == 'year' ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align:center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction?>" class="aiv-btn aiv-btn-primary"><?php echo $spText['button']['Show Records']?></a>
			<a href="aivisibility.php?sec=export-report&website_id=<?php echo intval($websiteId)?>&from_time=<?php echo urlencode($fromTime)?>&to_time=<?php echo urlencode($toTime)?>" class="aiv-btn aiv-btn-outline">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export CSV'] ?? 'Export CSV'?>
			</a>
		</td>
	</tr>
</table>
</form>

<div class="aiv-note">
	<i class="fas fa-info-circle"></i>
	<span><?php echo $spTextAIV['floornotice'] ?? 'Some AI clients strip or omit the referrer, and native mobile apps often send nothing - treat these counts as a floor, not a complete measure.'?></span>
</div>

<div id='subcontent'>
	<?php if (!empty($graphContent)) { ?>
	<div class="aiv-card">
		<?php echo $graphContent; ?>
	</div>
	<?php } ?>

	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-chart-pie"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Platform breakdown'] ?? 'Platform breakdown'?></div>
		</div>
		<div style="overflow-x:auto;">
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Referrals'] ?? 'Referrals'?></th>
			</tr>
			<?php if (!empty($platformTotals)) { ?>
				<?php foreach ($platformTotals as $platform => $hits) { ?>
					<tr>
						<td><?php echo htmlspecialchars($platform)?></td>
						<td class="aiv-num"><?php echo intval($hits)?></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<?php echo showNoRecordsList(0); ?>
			<?php } ?>
		</table>
		</div>
	</div>

	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-file-alt"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Top landing pages'] ?? 'Top landing pages'?></div>
		</div>
		<div style="overflow-x:auto;">
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Page'] ?? 'Page'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Referrals'] ?? 'Referrals'?></th>
			</tr>
			<?php if (!empty($topPages)) { ?>
				<?php foreach ($topPages as $pageInfo) { ?>
					<tr>
						<td><?php echo htmlspecialchars($pageInfo['url_path'])?></td>
						<td class="aiv-num"><?php echo intval($pageInfo['hits'])?></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<?php echo showNoRecordsList(0); ?>
			<?php } ?>
		</table>
		</div>
	</div>
</div>
