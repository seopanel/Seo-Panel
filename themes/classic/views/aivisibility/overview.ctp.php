<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['Overview'] ?? 'AI Visibility Summary'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aivOverviewHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="aivOverviewHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('aivOverviewHelpModal', this)" aria-label="<?php echo $spText['button']['Close'] ?? 'Close'?>"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-tachometer-alt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title"><?php echo $spTextAIV['Overview'] ?? 'AI Visibility Summary'?></div>
		<div class="aiv-intro-desc"><?php echo $spTextAIV['Rolls up everything AI Visibility tracks for this website into one score: bot crawling, AI Overview citations, AI Perception, and AI-referred traffic.'] ?? 'Rolls up everything AI Visibility tracks for this website into one score: bot crawling, AI Overview citations, AI Perception, and AI-referred traffic.'?> <a href="javascript:void(0);" data-toggle="modal" data-target="#aivOverviewHelpModal" class="aiv-intro-cta"><?php echo $spTextAIV['Learn more'] ?? 'Learn more'?> <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="aivOverviewHelpModal" tabindex="-1" role="dialog" aria-labelledby="aivOverviewHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-tachometer-alt"></i></div>
				<h5 class="modal-title" id="aivOverviewHelpModalLabel"><?php echo $spTextAIV['About AI Visibility Summary'] ?? 'About AI Visibility Summary'?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p><?php echo $spTextAIV['This rolls up everything AI Visibility tracks for the selected website into one score: real AI bot crawling, Google AI Overview citations, how AI assistants describe you (AI Perception Check), and real AI-referred traffic - plus an ROI card tying that traffic to conversions, if Google Analytics is connected.'] ?? 'This rolls up everything AI Visibility tracks for the selected website into one score: real AI bot crawling, Google AI Overview citations, how AI assistants describe you (AI Perception Check), and real AI-referred traffic - plus an ROI card tying that traffic to conversions, if Google Analytics is connected.'?></p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span><?php echo $spTextAIV['It only aggregates data the other AI Visibility pages already collect - nothing new is measured here. A component shows "not measured yet" until its own page (AI Setup, AI Perception Check, Google Analytics) is configured.'] ?? 'It only aggregates data the other AI Visibility pages already collect - nothing new is measured here. A component shows "not measured yet" until its own page (AI Setup, AI Perception Check, Google Analytics) is configured.'?></span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $spTextAIV['Got it'] ?? 'Got it'?></button>
			</div>
		</div>
	</div>
</div>

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
			<select name="period" class="custom-select" onchange="<?php echo $submitAction?>">
				<option value="day" <?php echo ($period ?? 'month') == 'day' ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo ($period ?? 'month') == 'week' ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo ($period ?? 'month') == 'month' ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo ($period ?? 'month') == 'year' ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align:center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction?>" class="aiv-btn aiv-btn-primary"><?php echo $spText['button']['Show Records']?></a>
			<a href="aivisibility.php?sec=export-overview&website_id=<?php echo intval($websiteId)?>&from_time=<?php echo urlencode($fromTime)?>&to_time=<?php echo urlencode($toTime)?>" class="aiv-btn aiv-btn-outline" style="float:right;">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export Overview CSV'] ?? 'Export Overview CSV'?>
			</a>
		</td>
	</tr>
</table>
</form>

<?php $hasAnyData = $referralTotal > 0 || $botTotal > 0 || !empty($aioSummary['present']); ?>

<?php
	// presentation only, no effect on the actual score - a quick visual
	// read of the same number already shown below (red/amber/green)
	$scoreVal = intval($aiVisibilityScore['overall'] ?? 0);
	$scoreGradient = $scoreVal >= 67 ? '#1f9d55 0%, #34d399 100%' : ($scoreVal >= 34 ? '#e0a23b 0%, #f5c161 100%' : '#e05353 0%, #f0847f 100%');

	// per-component accent (icon + measured color) - purely visual, keyed
	// off the same $component['label'] strings __getAiVisibilityScore()
	// already returns, with a safe neutral fallback for any future component
	$componentAccents = [
		'AI Bot Crawling' => ['color' => '#0d9488', 'icon' => 'fa-robot'],
		'AI Overview Citation' => ['color' => '#764ba2', 'icon' => 'fa-layer-group'],
		'LLM Mention Rate' => ['color' => '#d6336c', 'icon' => 'fa-comment-dots'],
		'AI Referral Traffic' => ['color' => '#c9820f', 'icon' => 'fa-mouse-pointer'],
	];
	$defaultAccent = ['color' => '#4a4e69', 'icon' => 'fa-chart-bar'];
?>
<?php if ($aiVisibilityScore['overall'] !== null) { ?>
	<div class="aiv-card" style="text-align:center; padding:28px; margin-bottom:24px;">
		<div style="font-size:13px; color:#8a8ea3; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
			<?php echo $spTextAIV['AI Visibility Score'] ?? 'AI Visibility Score'?>
		</div>
		<div style="font-size:56px; font-weight:800; background:linear-gradient(135deg, <?php echo $scoreGradient?>); -webkit-background-clip:text; background-clip:text; color:transparent; line-height:1;">
			<?php echo $scoreVal?>
		</div>
		<div style="font-size:14px; color:#6b7084; margin-top:6px;">
			<?php echo $spTextAIV['out of 100 - blends every AI-era signal this panel tracks into one number'] ?? 'out of 100 - blends every AI-era signal this panel tracks into one number'?>
		</div>
		<div style="display:flex; justify-content:center; gap:24px; flex-wrap:wrap; margin-top:20px;">
			<?php foreach ($aiVisibilityScore['components'] as $component) { ?>
				<?php $accent = $componentAccents[$component['label']] ?? $defaultAccent; ?>
				<div style="min-width:150px; text-align:center;">
					<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:<?php echo $component['measured'] ? htmlspecialchars($accent['color']) : '#c5c8d4'?>;">
						<i class="fas <?php echo htmlspecialchars($accent['icon'])?>"></i>
					</div>
					<div style="font-size:22px; font-weight:700; color:<?php echo $component['measured'] ? htmlspecialchars($accent['color']) : '#c5c8d4'; ?>;">
						<?php echo $component['measured'] ? intval($component['score']) . '%' : '&mdash;'; ?>
					</div>
					<div style="font-size:12px; font-weight:600; color:#8a8ea3; margin-top:2px;"><?php echo htmlspecialchars($spTextAIV[$component['label']] ?? $component['label']); ?></div>
					<div style="font-size:13px; color:#6b7084; margin-top:4px;"><?php echo htmlspecialchars($component['detail']); ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<div class="aiv-stat-grid aiv-stat-grid-4">
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #3b82f6;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#3b82f6;"><i class="fas fa-mouse-pointer"></i></div>
		<div style="font-size:30px;font-weight:800;color:#3b82f6;"><?php echo intval($referralTotal)?></div>
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
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #764ba2;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#764ba2;"><i class="fas fa-eye"></i></div>
		<div style="font-size:30px;font-weight:800;color:#764ba2;"><?php echo intval($aioSummary['present'] ?? 0)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Impressions'] ?? 'AI Overview Impressions'?></div>
		<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['keywords where this site appeared in an AI Overview, of'] ?? 'keywords where this site appeared in an AI Overview, of'?> <?php echo intval($aioSummary['measured'] ?? 0)?> <?php echo $spTextAIV['measured'] ?? 'measured'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #0d9488;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#0d9488;"><i class="fas fa-robot"></i></div>
		<div style="font-size:30px;font-weight:800;color:#0d9488;"><?php echo intval($botTotal)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Bot Crawls'] ?? 'AI Bot Crawls'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid <?php echo !empty($aioSummary['present']) ? '#1f9d55' : '#c5c8d4'?>;">
		<?php if (!empty($aioSummary['present'])) { ?>
			<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#1f9d55;"><i class="fas fa-check-circle"></i></div>
			<div style="font-size:30px;font-weight:800;color:#1f9d55;"><?php echo round((intval($aioSummary['cited']) / intval($aioSummary['present'])) * 100)?>%</div>
			<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?></div>
			<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['overviewaiocaption'] ?? 'of keywords where Google AI Overview cited this site, among keywords where an AI Overview appeared'?></div>
		<?php } else { ?>
			<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#c5c8d4;"><i class="fas fa-check-circle"></i></div>
			<div style="font-size:20px;font-weight:700;color:#c5c8d4;">—</div>
			<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?></div>
			<div style="font-size:11px;color:#b3b6c4;margin-top:6px;"><?php echo $spTextAIV['No AI Overview data measured yet for this website.'] ?? 'No AI Overview data measured yet for this website.'?></div>
		<?php } ?>
	</div>
</div>
<div class="aiv-note" style="margin-top:0;">
	<i class="fas fa-calendar-alt"></i>
	<span><strong><?php echo htmlspecialchars($fromTime)?> &ndash; <?php echo htmlspecialchars($toTime)?></strong> &middot; <?php echo $spTextAIV['AI Overview Citation Rate'] ?? 'AI Overview Citation Rate'?> <?php echo $spTextAIV['reflects the latest measured state, not this date range'] ?? 'reflects the latest measured state, not this date range'?></span>
</div>

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
		<?php
		$aivPlatformColors = [
			'chatgpt' => '#10a37f', 'claude' => '#cc785c', 'gemini' => '#4285f4',
			'perplexity' => '#20808d', 'copilot' => '#0078d4', 'you' => '#7c3aed',
			'poe' => '#6c5ce7', 'grok' => '#1a1a2e', 'mistral' => '#fa520f',
			'google-extended' => '#4285f4', 'bytespider' => '#fe2c55', 'ccbot' => '#718096',
			'applebot-extended' => '#555555', 'meta-externalagent' => '#0866ff',
			'amazon' => '#ff9900', 'duckassist' => '#de5833',
		];
		$aivMaxTotal = !empty($combinedPlatforms) ? max(array_column($combinedPlatforms, 'total')) : 0;
		?>
		<div style="overflow-x:auto;">
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Referrals'] ?? 'Referrals'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Bot Crawls'] ?? 'Bot Crawls'?></th>
				<th style="min-width:160px;"><?php echo $spTextAIV['Total'] ?? 'Total'?></th>
			</tr>
			<?php foreach ($combinedPlatforms as $platform => $row) {
				$platColor = $aivPlatformColors[$platform] ?? '#667eea';
				$platShare = $aivMaxTotal > 0 ? round((intval($row['total']) / $aivMaxTotal) * 100) : 0;
			?>
				<tr>
					<td>
						<div style="display:flex;align-items:center;gap:10px;">
							<div style="width:26px;height:26px;border-radius:50%;background:<?php echo htmlspecialchars($platColor)?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;"><?php echo htmlspecialchars(mb_substr($row['display_name'], 0, 1))?></div>
							<span style="font-weight:600;color:#2a2a3d;"><?php echo htmlspecialchars($row['display_name'])?></span>
						</div>
					</td>
					<td class="aiv-num"><?php echo intval($row['referrals'])?></td>
					<td class="aiv-num"><?php echo intval($row['bot_hits'])?></td>
					<td>
						<div style="display:flex;align-items:center;gap:10px;">
							<div style="flex:1;height:6px;border-radius:4px;background:#eef0f5;overflow:hidden;">
								<div style="height:100%;width:<?php echo $platShare?>%;background:<?php echo htmlspecialchars($platColor)?>;border-radius:4px;"></div>
							</div>
							<strong style="min-width:32px;text-align:right;color:#2a2a3d;"><?php echo intval($row['total'])?></strong>
						</div>
					</td>
				</tr>
			<?php } ?>
		</table>
		</div>
	</div>
<?php } ?>

<?php if ($aiReferralRoi['sessions'] > 0) { ?>
	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-chart-pie"></i></div>
			<div>
				<div class="aiv-card-title"><?php echo $spTextAIV['AI Referral ROI'] ?? 'AI Referral ROI'?></div>
			</div>
		</div>
		<div class="aiv-stat-grid aiv-stat-grid-3" style="gap:16px;margin-bottom:16px;">
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

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon" style="background:linear-gradient(135deg, #4a8fc7 0%, #6faee0 100%);"><i class="fas fa-compass"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Discover More'] ?? 'Discover More'?></div>
	</div>

	<?php if (!$aiReferralRoi['configured']) { ?>
	<div class="aiv-discover-item">
		<div class="aiv-discover-icon" style="background:#0d9488;"><i class="fas fa-chart-pie"></i></div>
		<div class="aiv-discover-text"><?php echo $spTextAIV['Want to see conversions from AI platforms, not just visits?'] ?? 'Want to see conversions from AI platforms, not just visits?'?></div>
		<a href="<?php echo SP_WEBPATH?>/admin-panel.php?sec=connections" class="aiv-discover-cta"><?php echo $spTextAIV['Connect Google Analytics'] ?? 'Connect Google Analytics'?> <i class="fas fa-arrow-right"></i></a>
	</div>
	<?php } ?>

	<div class="aiv-discover-item">
		<div class="aiv-discover-icon" style="background:#4a8fc7;"><i class="fas fa-chart-line"></i></div>
		<div class="aiv-discover-text"><?php echo $spTextAIV['View full report'] ?? 'View full report'?></div>
		<div style="display:flex;gap:8px;flex-wrap:wrap;">
			<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=report', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextTools['AI Referral Report'] ?? 'AI Referral Report'?></a>
			<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=botreport', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextAIV['AI Bot Crawlers'] ?? 'AI Bot Crawlers'?></a>
			<a href="javascript:void(0);" onclick="scriptDoLoad('aivisibility.php?sec=aioverview', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextTools['AI SERP Overview'] ?? 'AI SERP Overview'?></a>
		</div>
	</div>

	<div class="aiv-discover-item">
		<div class="aiv-discover-icon" style="background:#764ba2;"><i class="fas fa-robot"></i></div>
		<div class="aiv-discover-text">
			<?php echo $spTextAIV['Prefer to ask your own AI agent directly?'] ?? 'Prefer to ask your own AI agent directly?'?>
			<small><?php echo $spTextAIV['self-hosted, no data leaves this server'] ?? 'self-hosted, no data leaves this server'?></small>
		</div>
		<a href="javascript:void(0);" onclick="scriptDoLoad('mcp-access.php', 'content')" class="aiv-discover-cta"><?php echo $spTextAIV['Connect Claude Desktop or any MCP client'] ?? 'Connect Claude Desktop or any MCP client'?> <i class="fas fa-arrow-right"></i></a>
	</div>

	<div class="aiv-discover-item">
		<div class="aiv-discover-icon" style="background:#d6336c;"><i class="fas fa-comment-dots"></i></div>
		<div class="aiv-discover-text">
			<?php echo $spTextAIV['Curious what ChatGPT or Claude actually says about your site?'] ?? 'Curious what ChatGPT or Claude actually says about your site?'?>
			<small><?php echo $spTextAIV['uses your own API key'] ?? 'uses your own API key'?></small>
		</div>
		<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=check', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextAIV['Run an AI Perception Check'] ?? 'Run an AI Perception Check'?> <i class="fas fa-arrow-right"></i></a>
	</div>

	<div class="aiv-discover-item">
		<div class="aiv-discover-icon" style="background:#c9820f;"><i class="fas fa-code"></i></div>
		<div class="aiv-discover-text">
			<?php echo $spTextAIV['Give AI answer engines a clean fact layer to cite'] ?? 'Give AI answer engines a clean fact layer to cite'?>
			<small><?php echo $spTextAIV['Organization, LocalBusiness, Article, FAQPage'] ?? 'Organization, LocalBusiness, Article, FAQPage'?></small>
		</div>
		<a href="javascript:void(0);" onclick="scriptDoLoad('schema-generator.php', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextAIV['Generate Schema Markup'] ?? 'Generate Schema Markup'?> <i class="fas fa-arrow-right"></i></a>
	</div>
</div>
