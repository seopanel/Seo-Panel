<?php echo showSectionHead($spTextKeyword['Detailed Keyword Position Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area">
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			  $( function() {
			    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
			  } );
		  	</script>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php echo $this->render('searchengine/seselectbox', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('reports.php', 'search_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php
	if(empty($keywordId)){
		?>
		<div class="alert alert-danger">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>!
		</div>
		<?php
		exit;
	}
?>

<?php if (!empty($showAioUpsellHint)): ?>
	<div class="alert alert-info py-2 mb-2" style="font-size:0.85rem;">
		<i class="fas fa-info-circle"></i>
		<?php echo $spTextKeyword['AI Overview is not available on your current data source'] ?? 'AI Overview is not available on your current data source.' ?>
		<?php echo $spTextKeyword['Configure DataForSEO credentials to enable this feature immediately'] ?? 'Configure DataForSEO credentials to enable this feature immediately.' ?>
	</div>
<?php endif; ?>

<?php if (!empty($aioWindow) && $aioWindow['measured'] > 0): ?>
	<div class="mb-2" style="font-size:0.85rem;">
		<strong><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview' ?>:</strong>
		<?php echo $spTextKeyword['present in'] ?? 'present in' ?> <?php echo $aioWindow['present']?>/<?php echo $aioWindow['measured']?>
		<?php echo $spTextKeyword['of last observations'] ?? 'of last observations' ?>
		<span style="letter-spacing:2px;">
			<?php foreach ($aioWindow['observations'] as $obs): ?>
				<i class="fas <?php echo $obs['present'] ? 'fa-square' : 'fa-square-o'?>"
				   style="color:<?php echo $obs['present'] ? '#2e7d32' : '#bdbdbd'?>;"
				   title="<?php echo htmlspecialchars($obs['date'] . ' - ' . ($obs['present'] ? 'present' : 'absent') . ' (' . $obs['provider'] . ')')?>"></i>
			<?php endforeach; ?>
		</span>
	</div>
<?php endif; ?>

<div id='subcontent'>
<table width="100%" class="list">
	<tr class="listHead">
		<td width="10%"><?php echo $spText['common']['Date']?></td>
		<td><?php echo $seInfo['domain']?> <?php echo $spText['common']['Results']?></td>
		<td><?php echo $spText['common']['Rank']?></td>
		<td><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview' ?></td>
		<td><?php echo $spTextKeyword['Cited'] ?? 'Cited' ?></td>
		<td><?php echo $spTextKeyword['Sources'] ?? 'Sources' ?></td>
	</tr>
	<?php
	$colCount = 6;
	$today = date('Y-m-d');
	if(count($list) > 0) {
		foreach($list as $listInfo) {
            $scriptLink = "sec=show-info&keyId={$listInfo['keyword_id']}&time={$listInfo['time']}&seId=$seId";
            $dateLink = scriptAJAXLinkHref('reports.php', 'subcontent', $scriptLink, date('Y-m-d', $listInfo['time']) );

			$aioMeasured = !empty($listInfo['aio_checked_at']);
			$aioSupported = $aioMeasured && intval($listInfo['aio_supported']) === 1;
			$aioPresent = $aioSupported && !empty($listInfo['aio_present']);
			$aioStale = $aioSupported && !empty($listInfo['aio_data_date'])
				&& (strtotime($today) - strtotime($listInfo['aio_data_date'])) > ($aioStaleDays * 86400);
			$aioProviderLabel = $listInfo['provider'] === 'dataforseo' ? 'DataForSEO' : ($listInfo['provider'] === 'spapi' ? 'SEO Panel API' : $listInfo['provider']);
			?>
			<tr class="<?php echo $class?>">
				<td><?php echo $dateLink; ?></td>
				<td id='seresult'>
					<a href='<?php echo $listInfo['url']?>' target='_blank'><?php echo stripslashes($listInfo['title']);?></a>
					<p><?php echo stripslashes($listInfo['description']);?><p>
					<label><?php echo $listInfo['url']?></label>
				</td>
				<td class="fw-bold"><?php echo $listInfo['rank'].'</b> '. $listInfo['rank_diff']?></td>
				<td style="font-size:0.85rem;">
					<?php if (!$aioMeasured): ?>
						<span class="text-muted">&mdash;</span>
					<?php elseif (!$aioSupported): ?>
						<span class="text-muted" title="<?php echo $spTextKeyword['AI Overview is not available on your current data source'] ?? 'AI Overview is not available on your current data source'?>">
							<?php echo $spTextKeyword['Not available'] ?? 'Not available' ?>
						</span>
					<?php else: ?>
						<span class="<?php echo $aioPresent ? 'text-success' : 'text-muted'?>">
							<?php echo $aioPresent ? ($spTextKeyword['Present'] ?? 'Present') : ($spTextKeyword['Absent'] ?? 'Absent') ?>
						</span>
						<br>
						<small class="text-muted">
							<?php echo htmlspecialchars($aioProviderLabel)?>, <?php echo htmlspecialchars($listInfo['aio_data_date'])?>
							<?php if ($aioStale): ?>
								<span class="text-warning" title="<?php echo $spTextKeyword['Data older than the configured freshness threshold'] ?? 'Data older than the configured freshness threshold'?>">
									(<?php echo $spTextKeyword['stale'] ?? 'stale' ?>)
								</span>
							<?php endif; ?>
						</small>
					<?php endif; ?>
				</td>
				<td style="font-size:0.85rem;">
					<?php if (!$aioSupported): ?>
						<span class="text-muted">&mdash;</span>
					<?php elseif (!empty($listInfo['aio_cited'])): ?>
						<span class="text-success"><?php echo $spTextKeyword['Yes'] ?? 'Yes'?><?php echo !empty($listInfo['aio_cited_position']) ? ' (#' . intval($listInfo['aio_cited_position']) . ')' : ''?></span>
					<?php else: ?>
						<span class="text-muted"><?php echo $spTextKeyword['No'] ?? 'No'?></span>
					<?php endif; ?>
				</td>
				<td style="font-size:0.85rem;">
					<?php if ($aioSupported && !empty($listInfo['aio_reference_count'])): ?>
						<?php echo scriptAJAXLinkHref('reports.php', 'subcontent', "sec=aiosources&keyword_id={$listInfo['keyword_id']}", intval($listInfo['aio_reference_count']))?>
					<?php else: ?>
						<span class="text-muted">0</span>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
</div>