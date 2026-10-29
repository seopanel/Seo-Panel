<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI SERP Overview'] ?? 'AI SERP Overview'); ?>

<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="aioWebsiteId" class="custom-select" onchange="aioReload()">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo $websiteInfo['name']?></option>
				<?php } ?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
		<td>
			<select name="period" id="aioPeriod" class="custom-select" onchange="aioReload()">
				<option value="day" <?php echo ($period ?? 'month') == 'day' ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo ($period ?? 'month') == 'week' ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo ($period ?? 'month') == 'month' ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo ($period ?? 'month') == 'year' ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
	</tr>
</table>
</form>
<script>
function aioReload() {
	scriptDoLoad('aivisibility.php?sec=aioverview', 'content', '&website_id=' + document.getElementById('aioWebsiteId').value + '&period=' + document.getElementById('aioPeriod').value);
}
</script>

<div id='subcontent'>

<div class="aiv-stat-grid aiv-stat-grid-3">
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #3b82f6;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#3b82f6;"><i class="fas fa-key"></i></div>
		<div style="font-size:30px;font-weight:800;color:#3b82f6;"><?php echo intval($summary['measured'])?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Measured Keywords'] ?? 'Measured Keywords'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #764ba2;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#764ba2;"><i class="fas fa-layer-group"></i></div>
		<div style="font-size:30px;font-weight:800;color:#764ba2;"><?php echo intval($summary['present'])?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?> <?php echo $spTextKeyword['Present'] ?? 'Present'?></div>
	</div>
	<div class="aiv-card" style="text-align:center;margin-bottom:0;padding:22px;border-top:3px solid #1f9d55;">
		<div style="width:30px;height:30px;border-radius:9px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:#1f9d55;"><i class="fas fa-check-circle"></i></div>
		<div style="font-size:30px;font-weight:800;color:#1f9d55;"><?php echo intval($summary['cited'])?></div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Cited Keywords'] ?? 'Cited Keywords'?></div>
	</div>
</div>

<?php if (!empty($summary['unsupported'])) { ?>
	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span>
			<?php echo intval($summary['unsupported'])?> <?php echo $spTextKeyword['keyword / search engine combination(s) hidden above'] ?? "keyword / search engine combination(s) hidden above" ?>. <?php echo $spTextKeyword['AI Overview is not available on your current data source'] ?? "AI Overview is only available for Google." ?>
		</span>
	</div>
<?php } ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon" style="background:linear-gradient(135deg, #3b82f6 0%, #6faee0 100%);"><i class="fas fa-key"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Keyword-Level Detail'] ?? 'Keyword-Level Detail'?></div>
	</div>
	<div style="overflow-x:auto;">
	<table class="aiv-table">
		<tr>
			<th><?php echo $spText['common']['Keyword']?></th>
			<th><?php echo $spText['common']['Search Engine']?></th>
			<th><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?></th>
			<th><?php echo $spTextKeyword['Cited'] ?? 'Cited'?></th>
			<th><?php echo $spTextKeyword['Sources'] ?? 'Sources'?></th>
		</tr>
		<?php if (!empty($rowList)) { ?>
			<?php foreach ($rowList as $row) { ?>
				<?php
					$supported = intval($row['aio_supported']) === 1;
					$present = $supported && !empty($row['aio_present']);
					$providerLabel = $row['provider'] === 'dataforseo' ? 'DataForSEO' : ($row['provider'] === 'spapi' ? 'SEO Panel API' : $row['provider']);
				?>
				<tr>
					<td><?php echo htmlspecialchars($row['keyword_name'])?></td>
					<td><?php echo htmlspecialchars($row['se_domain'])?></td>
					<td>
						<?php if (!$supported) { ?>
							<span class="aiv-pill aiv-pill-muted"><?php echo $spTextKeyword['Not available'] ?? 'Not available'?></span>
						<?php } else { ?>
							<span class="aiv-pill <?php echo $present ? 'aiv-pill-success' : 'aiv-pill-muted'?>"><?php echo $present ? ($spTextKeyword['Present'] ?? 'Present') : ($spTextKeyword['Absent'] ?? 'Absent')?></span>
							<br><small title="<?php echo $spTextKeyword['Data source and date this AI Overview result was last checked'] ?? 'Data source and date this AI Overview result was last checked'?>">via <?php echo htmlspecialchars($providerLabel)?> &middot; checked <?php echo htmlspecialchars($row['aio_data_date'])?></small>
						<?php } ?>
					</td>
					<td>
						<?php if ($supported && $present) { ?>
							<?php if (!empty($row['aio_cited'])) { ?>
								<span class="aiv-pill aiv-pill-success"><i class="fas fa-check-circle"></i> <?php echo $spTextKeyword['Yes'] ?? 'Yes'?> (#<?php echo intval($row['aio_cited_position'])?>)</span>
							<?php } else { ?>
								<span class="aiv-pill aiv-pill-muted"><?php echo $spTextKeyword['No'] ?? 'No'?></span>
							<?php } ?>
						<?php } else { ?>
							&mdash;
						<?php } ?>
					</td>
					<td>
						<?php if ($supported && !empty($row['aio_reference_count'])) { ?>
							<a href="javascript:void(0);" onclick="openAjaxModalSP('<?php echo SP_WEBPATH?>/reports.php?sec=aiosources&keyword_id=<?php echo intval($row['keyword_id'])?>', '<i class=&quot;fas fa-robot&quot;></i> AI Overview Cited Sources')"><?php echo intval($row['aio_reference_count'])?></a>
						<?php } else { ?>
							0
						<?php } ?>
					</td>
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
		<div class="aiv-card-icon"><i class="fas fa-users"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Competitor domains cited in your AI Overviews'] ?? 'Competitor domains cited in your AI Overviews'?></div>
	</div>
	<table class="aiv-table">
		<tr>
			<th><?php echo $spTextAIV['Domain'] ?? 'Domain'?></th>
			<th class="aiv-num"><?php echo $spText['common']['Keywords']?></th>
			<th class="aiv-num"><?php echo $spTextAIV['Citations'] ?? 'Citations'?></th>
			<th style="min-width:160px;"><?php echo $spTextAIV['Share of Voice'] ?? 'Share of Voice'?></th>
		</tr>
		<?php if (!empty($competitorDomains)) { ?>
			<?php foreach ($competitorDomains as $domainInfo) { ?>
				<?php $isTracked = !empty($trackedDomain) && AIOverviewController::isDomainCited($domainInfo['domain'], $trackedDomain, $subdomainPolicy); ?>
				<?php $sov = !empty($summary['measured']) ? round((intval($domainInfo['keyword_count']) / intval($summary['measured'])) * 100) : 0; ?>
				<tr <?php echo $isTracked ? "style='background:#fdf8e8;'" : ''?>>
					<td><?php echo htmlspecialchars($domainInfo['domain'])?> <?php echo $isTracked ? '<span class="aiv-pill aiv-pill-warn">' . ($spTextAIV['you'] ?? 'you') . '</span>' : ''?></td>
					<td class="aiv-num"><?php echo intval($domainInfo['keyword_count'])?></td>
					<td class="aiv-num">
						<?php if ($isTracked) { ?>
							<?php echo intval($domainInfo['citation_count'])?>
						<?php } else { ?>
							<a href="javascript:void(0);" onclick="openAjaxModalSP('<?php echo SP_WEBPATH?>/reports.php?sec=aiocompetitorkeywords&website_id=<?php echo intval($websiteId)?>&domain=<?php echo urlencode($domainInfo['domain'])?>', '<i class=&quot;fas fa-users&quot;></i> Competitor Citations')"><?php echo intval($domainInfo['citation_count'])?></a>
						<?php } ?>
					</td>
					<td>
						<div style="display:flex;align-items:center;gap:10px;">
							<div style="flex:1;height:6px;border-radius:4px;background:#eef0f5;overflow:hidden;">
								<div style="height:100%;width:<?php echo $sov?>%;background:<?php echo $isTracked ? '#e0a23b' : '#764ba2'?>;border-radius:4px;"></div>
							</div>
							<strong style="min-width:36px;text-align:right;color:#2a2a3d;"><?php echo $sov?>%</strong>
						</div>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<?php echo showNoRecordsList(0); ?>
		<?php } ?>
	</table>
	<div class="aiv-note" style="margin-top:14px;margin-bottom:0;">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['Share of Voice is the percentage of your measured keywords where this domain is cited in the AI Overview.'] ?? 'Share of Voice is the percentage of your measured keywords where this domain is cited in the AI Overview.'?></span>
	</div>
</div>

</div>
