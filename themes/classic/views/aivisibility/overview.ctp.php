<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['Overview'] ?? 'Overview'); ?>
<?php $submitAction = "scriptDoLoadPost('aivisibility.php?sec=overview', 'search_form', 'content')"; ?>

<form id='search_form'>
<input type="hidden" name="sec" value="overview">
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
			<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display:inline-block;width:45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display:inline-block;width:45%;"/>
			<script type="text/javascript">
			$(function() {
				$("input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
			</script>
		</td>
		<td style="text-align:center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction?>" class="aiv-btn aiv-btn-primary"><?php echo $spText['button']['Show Records']?></a>
			<a href="aivisibility.php?sec=export-overview&website_id=<?php echo intval($websiteId)?>&from_time=<?php echo urlencode($fromTime)?>&to_time=<?php echo urlencode($toTime)?>" class="aiv-btn aiv-btn-outline">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export Overview CSV'] ?? 'Export Overview CSV'?>
			</a>
		</td>
	</tr>
</table>
</form>

<?php $hasAnyData = $referralTotal > 0 || $botTotal > 0 || !empty($aioSummary['present']); ?>

<?php if ($aiVisibilityScore['overall'] !== null) { ?>
	<div class="aiv-card" style="text-align:center; padding:28px; margin-bottom:24px;">
		<div style="font-size:13px; color:#8a8ea3; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
			<?php echo $spTextAIV['AI Visibility Score'] ?? 'AI Visibility Score'?>
		</div>
		<div style="font-size:56px; font-weight:800; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip:text; background-clip:text; color:transparent; line-height:1;">
			<?php echo intval($aiVisibilityScore['overall'])?>
		</div>
		<div style="font-size:12px; color:#b3b6c4; margin-top:6px;">
			<?php echo $spTextAIV['out of 100 - blends every AI-era signal this panel tracks into one number'] ?? 'out of 100 - blends every AI-era signal this panel tracks into one number'?>
		</div>
		<div style="display:flex; justify-content:center; gap:24px; flex-wrap:wrap; margin-top:20px;">
			<?php foreach ($aiVisibilityScore['components'] as $component) { ?>
				<div style="min-width:150px; text-align:center;">
					<div style="font-size:22px; font-weight:700; color:<?php echo $component['measured'] ? '#4a4e69' : '#c5c8d4'; ?>;">
						<?php echo $component['measured'] ? intval($component['score']) . '%' : '&mdash;'; ?>
					</div>
					<div style="font-size:12px; font-weight:600; color:#8a8ea3; margin-top:2px;"><?php echo htmlspecialchars($spTextAIV[$component['label']] ?? $component['label']); ?></div>
					<div style="font-size:11px; color:#b3b6c4; margin-top:2px;"><?php echo htmlspecialchars($component['detail']); ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:20px;margin-bottom:24px;">
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;">
		<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo intval($referralTotal)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Referral Clicks'] ?? 'AI Referral Clicks'?></div>
		<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['visits arriving from an AI platform link'] ?? 'visits arriving from an AI platform link'?></div>
	</div>
	<?php
	// "Impressions" for AI sources: how many keyword checks in the
	// selected range found this site's content appearing in Google's AI
	// Overview at all - present vs. clicked-through (referralTotal above)
	// is the same clicks-vs-impressions distinction Search Console makes
	// for ordinary search results, just for AI Overview specifically.
	// aioSummary itself is NOT date-range-filtered (each keyword's latest
	// measured state, same as the citation-rate card below), so this
	// reflects the current snapshot, not a sum over the period.
	?>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;">
		<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo intval($aioSummary['present'] ?? 0)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Impressions'] ?? 'AI Overview Impressions'?></div>
		<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['keywords where this site appeared in an AI Overview, of'] ?? 'keywords where this site appeared in an AI Overview, of'?> <?php echo intval($aioSummary['measured'] ?? 0)?> <?php echo $spTextAIV['measured'] ?? 'measured'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;">
		<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo intval($botTotal)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Bot Crawls'] ?? 'AI Bot Crawls'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;">
		<?php if (!empty($aioSummary['present'])) { ?>
			<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo round((intval($aioSummary['cited']) / intval($aioSummary['present'])) * 100)?>%</div>
			<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?></div>
			<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['overviewaiocaption'] ?? 'of keywords where Google AI Overview cited this site, among keywords where an AI Overview appeared'?></div>
		<?php } else { ?>
			<div style="font-size:20px;font-weight:700;color:#c5c8d4;">—</div>
			<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?></div>
			<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['No AI Overview data measured yet for this website.'] ?? 'No AI Overview data measured yet for this website.'?></div>
		<?php } ?>
	</div>
</div>
<p style="font-size:12px;color:#8a8ea3;margin:-14px 0 20px;"><?php echo htmlspecialchars($fromTime)?> &ndash; <?php echo htmlspecialchars($toTime)?> &middot; <?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?> <?php echo $spTextAIV['reflects the latest measured state, not this date range'] ?? 'reflects the latest measured state, not this date range'?></p>

<?php if (!$hasAnyData) { ?>
	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['No AI traffic recorded yet - install the snippet and collector script from the Setup tab.'] ?? 'No AI traffic recorded yet - install the snippet and collector script from the Setup tab.'?></span>
	</div>
<?php } else { ?>
	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-layer-group"></i></div>
			<div>
				<div class="aiv-card-title"><?php echo $spTextAIV['Top AI Platforms'] ?? 'Top AI Platforms'?></div>
			</div>
		</div>
		<div style="overflow-x:auto;">
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Referrals'] ?? 'Referrals'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Bot Crawls'] ?? 'Bot Crawls'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Total'] ?? 'Total'?></th>
			</tr>
			<?php foreach ($combinedPlatforms as $platform => $row) { ?>
				<tr>
					<td><?php echo htmlspecialchars($row['display_name'])?></td>
					<td class="aiv-num"><?php echo intval($row['referrals'])?></td>
					<td class="aiv-num"><?php echo intval($row['bot_hits'])?></td>
					<td class="aiv-num"><strong><?php echo intval($row['total'])?></strong></td>
				</tr>
			<?php } ?>
		</table>
		</div>
	</div>
<?php } ?>

<?php if (!$aiReferralRoi['configured']) { ?>
	<div class="aiv-note">
		<i class="fas fa-chart-pie"></i>
		<span>
			<?php echo $spTextAIV['Want to see conversions from AI platforms, not just visits?'] ?? 'Want to see conversions from AI platforms, not just visits?'?>
			<a href="<?php echo SP_WEBPATH?>/admin-panel.php?sec=connections"><?php echo $spTextAIV['Connect Google Analytics'] ?? 'Connect Google Analytics'?></a>
		</span>
	</div>
<?php } else if ($aiReferralRoi['sessions'] > 0) { ?>
	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-chart-pie"></i></div>
			<div>
				<div class="aiv-card-title"><?php echo $spTextAIV['AI Referral ROI'] ?? 'AI Referral ROI'?></div>
			</div>
		</div>
		<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;margin-bottom:16px;">
			<div style="text-align:center;">
				<div style="font-size:26px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo number_format($aiReferralRoi['sessions'])?></div>
				<div style="font-size:12px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Sessions from AI Platforms'] ?? 'Sessions from AI Platforms'?></div>
			</div>
			<div style="text-align:center;">
				<div style="font-size:26px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo number_format($aiReferralRoi['conversions'])?></div>
				<div style="font-size:12px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Conversions from AI Platforms'] ?? 'Conversions from AI Platforms'?></div>
			</div>
			<div style="text-align:center;">
				<div style="font-size:26px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo $aiReferralRoi['conversionRate'] !== null ? $aiReferralRoi['conversionRate'] . '%' : '&mdash;'?></div>
				<div style="font-size:12px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Conversion Rate'] ?? 'Conversion Rate'?></div>
			</div>
		</div>
		<?php if (!empty($aiReferralRoi['byPlatform'])) { ?>
			<div style="overflow-x:auto;">
			<table class="aiv-table">
				<tr>
					<th><?php echo $spTextAIV['Source'] ?? 'Source'?></th>
					<th class="aiv-num"><?php echo $spTextAIV['Sessions'] ?? 'Sessions'?></th>
					<th class="aiv-num"><?php echo $spTextAIV['Conversions'] ?? 'Conversions'?></th>
				</tr>
				<?php foreach ($aiReferralRoi['byPlatform'] as $row) { ?>
					<tr>
						<td><?php echo htmlspecialchars($row['source'])?></td>
						<td class="aiv-num"><?php echo number_format($row['sessions'])?></td>
						<td class="aiv-num"><?php echo number_format($row['conversions'])?></td>
					</tr>
				<?php } ?>
			</table>
			</div>
		<?php } ?>
	</div>
<?php } ?>

<div class="aiv-note">
	<i class="fas fa-chart-line"></i>
	<span>
		<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=report', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextTools['AI Referral Report'] ?? 'AI Referral Report'?></a>
		&nbsp;&middot;&nbsp;
		<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=botreport', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextAIV['AI Bot Crawlers'] ?? 'AI Bot Crawlers'?></a>
		&nbsp;&middot;&nbsp;
		<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=aioverview', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextTools['AI Overview'] ?? 'AI Overview'?></a>
		&nbsp;&mdash; <?php echo $spTextAIV['View full report'] ?? 'View full report'?>
	</span>
</div>

<div class="aiv-note">
	<i class="fas fa-robot"></i>
	<span>
		<?php echo $spTextAIV['Prefer to ask your own AI agent directly?'] ?? 'Prefer to ask your own AI agent directly?'?>
		<a href="javascript:void(0);" onclick="scriptDoLoad('mcp-access.php', 'content')"><?php echo $spTextAIV['Connect Claude Desktop or any MCP client'] ?? 'Connect Claude Desktop or any MCP client'?></a>
		&mdash; <?php echo $spTextAIV['self-hosted, no data leaves this server'] ?? 'self-hosted, no data leaves this server'?>
	</span>
</div>

<div class="aiv-note">
	<i class="fas fa-comment-dots"></i>
	<span>
		<?php echo $spTextAIV['Curious what ChatGPT or Claude actually says about your site?'] ?? 'Curious what ChatGPT or Claude actually says about your site?'?>
		<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=check', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextAIV['Run an AI Perception Check'] ?? 'Run an AI Perception Check'?></a>
		&mdash; <?php echo $spTextAIV['uses your own API key'] ?? 'uses your own API key'?>
	</span>
</div>

<div class="aiv-note">
	<i class="fas fa-code"></i>
	<span>
		<?php echo $spTextAIV['Give AI answer engines a clean fact layer to cite'] ?? 'Give AI answer engines a clean fact layer to cite'?>
		<a href="javascript:void(0);" onclick="scriptDoLoad('schema-generator.php', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextAIV['Generate Schema Markup'] ?? 'Generate Schema Markup'?></a>
		&mdash; <?php echo $spTextAIV['Organization, LocalBusiness, Article, FAQPage'] ?? 'Organization, LocalBusiness, Article, FAQPage'?>
	</span>
</div>
