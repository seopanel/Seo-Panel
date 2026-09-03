<?php echo showSectionHead($spTextAIV['AI Overview'] ?? 'AI Overview'); ?>

<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="scriptDoLoad('aivisibility.php?sec=aioverview', 'content', '&website_id='+this.value)">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo $websiteInfo['name']?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>

<div class="row" style="margin-bottom:20px;">
	<div class="col" style="text-align:center;padding:15px;">
		<div style="font-size:28px;font-weight:600;"><?php echo intval($summary['measured'])?></div>
		<div><?php echo $spTextAIV['Measured Keywords'] ?? 'Measured Keywords'?></div>
	</div>
	<div class="col" style="text-align:center;padding:15px;">
		<div style="font-size:28px;font-weight:600;"><?php echo intval($summary['present'])?></div>
		<div><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?> <?php echo $spTextKeyword['Present'] ?? 'Present'?></div>
	</div>
	<div class="col" style="text-align:center;padding:15px;">
		<div style="font-size:28px;font-weight:600;"><?php echo intval($summary['cited'])?></div>
		<div><?php echo $spTextAIV['Cited Keywords'] ?? 'Cited Keywords'?></div>
	</div>
</div>

<?php if (!empty($summary['unsupported'])) { ?>
	<div class="alert alert-info">
		<?php echo intval($summary['unsupported'])?> <?php echo $spTextKeyword['AI Overview is not available on your current data source'] ?? 'keyword(s) are on a data source that does not support AI Overview.'?>
		<?php echo $spTextKeyword['Configure DataForSEO credentials to enable this feature immediately'] ?? ''?>
	</div>
<?php } ?>

<table class="list">
	<tr class="listHead">
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
						<span class="text-muted"><?php echo $spTextKeyword['Not available'] ?? 'Not available'?></span>
					<?php } else { ?>
						<span class="<?php echo $present ? 'text-success' : 'text-muted'?>"><?php echo $present ? ($spTextKeyword['Present'] ?? 'Present') : ($spTextKeyword['Absent'] ?? 'Absent')?></span>
						<br><small><?php echo htmlspecialchars($providerLabel)?>, <?php echo htmlspecialchars($row['aio_data_date'])?></small>
					<?php } ?>
				</td>
				<td>
					<?php if ($supported && $present) { ?>
						<?php if (!empty($row['aio_cited'])) { ?>
							<?php echo $spTextKeyword['Yes'] ?? 'Yes'?> (#<?php echo intval($row['aio_cited_position'])?>)
						<?php } else { ?>
							<?php echo $spTextKeyword['No'] ?? 'No'?>
						<?php } ?>
					<?php } else { ?>
						&mdash;
					<?php } ?>
				</td>
				<td>
					<?php if ($supported && !empty($row['aio_reference_count'])) { ?>
						<?php echo scriptAJAXLinkHref('reports.php', 'subcontent', "sec=aiosources&keyword_id={$row['keyword_id']}", intval($row['aio_reference_count'])); ?>
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

<h4 style="margin-top:20px;"><?php echo $spTextAIV['Competitor domains cited in your AI Overviews'] ?? 'Competitor domains cited in your AI Overviews'?></h4>
<table class="list">
	<tr class="listHead">
		<th><?php echo $spTextAIV['Domain'] ?? 'Domain'?></th>
		<th><?php echo $spText['common']['Keywords']?></th>
		<th><?php echo $spTextAIV['Citations'] ?? 'Citations'?></th>
	</tr>
	<?php if (!empty($competitorDomains)) { ?>
		<?php foreach ($competitorDomains as $domainInfo) { ?>
			<?php $isTracked = !empty($trackedDomain) && AIOverviewController::isDomainCited($domainInfo['domain'], $trackedDomain, $subdomainPolicy); ?>
			<tr <?php echo $isTracked ? "style='background:#fffbe6;'" : ''?>>
				<td><?php echo htmlspecialchars($domainInfo['domain'])?> <?php echo $isTracked ? '<strong>(' . ($spTextAIV['you'] ?? 'you') . ')</strong>' : ''?></td>
				<td><?php echo intval($domainInfo['keyword_count'])?></td>
				<td><?php echo intval($domainInfo['citation_count'])?></td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<?php echo showNoRecordsList(0); ?>
	<?php } ?>
</table>

</div>
