<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['AI Bot Crawlers'] ?? 'AI Bot Crawlers'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aivBotHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="aivBotHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('aivBotHelpModal', this)" aria-label="<?php echo $spText['button']['Close'] ?? 'Close'?>"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-robot"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title"><?php echo $spTextAIV['AI Bot Crawlers'] ?? 'AI Bot Crawlers'?></div>
		<div class="aiv-intro-desc">
			<?php echo $spTextAIV['Tracks actual AI crawler bots - GPTBot, ClaudeBot, PerplexityBot and others - visiting your site to read and index it.'] ?? 'Tracks actual AI crawler bots - GPTBot, ClaudeBot, PerplexityBot and others - visiting your site to read and index it.'?><br>
			<span style="color:#8a8ea3;"><?php echo $spTextAIV['botverifiednotice'] ?? '"Verified" means the crawler\'s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'?></span>
			<a href="javascript:void(0);" data-toggle="modal" data-target="#aivBotHelpModal" class="aiv-intro-cta"><?php echo $spTextAIV['Learn more'] ?? 'Learn more'?> <i class="fas fa-arrow-right"></i></a>
		</div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="aivBotHelpModal" tabindex="-1" role="dialog" aria-labelledby="aivBotHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-robot"></i></div>
				<h5 class="modal-title" id="aivBotHelpModalLabel"><?php echo $spTextAIV['About AI Bot Crawlers'] ?? 'About AI Bot Crawlers'?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p><?php echo $spTextAIV['Tracks actual AI crawler bots - GPTBot, ClaudeBot, PerplexityBot and others - visiting your site to read and index it. Since bots don\'t run JavaScript, this is measured separately from the AI Referral Report\'s click-through traffic.'] ?? 'Tracks actual AI crawler bots - GPTBot, ClaudeBot, PerplexityBot and others - visiting your site to read and index it. Since bots don\'t run JavaScript, this is measured separately from the AI Referral Report\'s click-through traffic.'?></p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span><?php echo $spTextAIV['Needs the bot collector script from AI Setup installed first. "Verified" means the visitor\'s IP passed a reverse-DNS check at the time - the same technique used to confirm Googlebot - which is advisory analytics, not forensic proof.'] ?? 'Needs the bot collector script from AI Setup installed first. "Verified" means the visitor\'s IP passed a reverse-DNS check at the time - the same technique used to confirm Googlebot - which is advisory analytics, not forensic proof.'?></span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $spTextAIV['Got it'] ?? 'Got it'?></button>
			</div>
		</div>
	</div>
</div>

<?php $submitAction = "scriptDoLoadPost('aivisibility.php?sec=botreport', 'search_form', 'content')"; ?>
<form id='search_form'>
<input type="hidden" name="sec" value="botreport">
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
			<a href="aivisibility.php?sec=export-botreport&website_id=<?php echo intval($websiteId)?>&from_time=<?php echo urlencode($fromTime)?>&to_time=<?php echo urlencode($toTime)?>" class="aiv-btn aiv-btn-outline">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export CSV'] ?? 'Export CSV'?>
			</a>
		</td>
	</tr>
</table>
</form>

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
				<th class="aiv-num"><?php echo $spTextAIV['Verified'] ?? 'Verified'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Unverified'] ?? 'Unverified'?></th>
			</tr>
			<?php if (!empty($platformTotals)) { ?>
				<?php foreach ($platformTotals as $platform => $counts) { ?>
					<tr>
						<td><?php echo htmlspecialchars($platform)?></td>
						<td class="aiv-num"><span class="aiv-badge-soft success"><?php echo intval($counts['verified'])?></span></td>
						<td class="aiv-num"><span class="aiv-badge-soft neutral"><?php echo intval($counts['unverified'])?></span></td>
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
			<div class="aiv-card-title"><?php echo $spTextAIV['Top crawled pages'] ?? 'Top crawled pages'?></div>
		</div>
		<div style="overflow-x:auto;">
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Page'] ?? 'Page'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Crawls'] ?? 'Crawls'?></th>
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
