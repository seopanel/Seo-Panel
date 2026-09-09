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

<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:20px;margin-bottom:24px;">
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;">
		<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo intval($referralTotal)?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['AI Referrals'] ?? 'AI Referrals'?></div>
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
