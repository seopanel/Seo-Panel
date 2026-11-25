<div class="aiv-page-head">
	<?php echo showSectionHead($spTextBack['Backlink Details'] ?? 'Backlink Details'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#blDetailsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<div class="modal fade" id="blDetailsHelpModal" tabindex="-1" role="dialog" aria-labelledby="blDetailsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="blDetailsHelpModalLabel">About Backlink Details</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<p>Shows the individual backlinks pointing to your site (one row per referring domain), with anchor text, dofollow/nofollow status, domain rank, and when each link was first/last seen.</p>
				<div class="alert alert-info mb-0">
					<i class="fas fa-info-circle"></i>
					Collected on a slower cadence than the regular Backlinks Reports crawl (roughly every 2 weeks per site) to limit DataForSEO API cost. Requires DataForSEO to be configured on this server.
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php if (empty($dfsEnabled)) { ?>
	<div class="alert alert-warning">
		<i class="fas fa-exclamation-triangle me-2"></i><?php echo $spTextBack['DataForSEO must be configured to use Backlink Details'] ?? 'DataForSEO must be configured to use Backlink Details'?>
	</div>
<?php } else { ?>

<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" class="custom-select" onchange="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=details')">
				<?php foreach ($websiteList as $websiteInfo) {?>
					<?php if ($websiteInfo['id'] == $websiteId) {?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php } else {?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Status'] ?? 'Status'?>:</th>
		<td>
			<select name="status" id="status" class="custom-select" onchange="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=details')">
				<option value="">-- <?php echo $spText['common']['All'] ?? 'all'?> --</option>
				<option value="live" <?php echo ($status == 'live') ? 'selected' : ''?>><?php echo $spTextBack['Live'] ?? 'Live'?></option>
				<option value="lost" <?php echo ($status == 'lost') ? 'selected' : ''?>><?php echo $spTextBack['Lost'] ?? 'Lost'?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=details')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
			<a href="backlinks.php?sec=details&website_id=<?php echo intval($websiteId)?>&status=<?php echo urlencode($status)?>&export=1" class="btn btn-sm btn-secondary" title="<?php echo $spText['button']['Export'] ?? 'Export'?>"><i class="fas fa-file-csv"></i></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
<?php if (empty($list)) { ?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextBack['No backlink detail data yet - check back after the next scheduled crawl'] ?? 'No backlink detail data yet - check back after the next scheduled crawl'?>
	</div>
<?php } else { ?>
	<?php echo $pagingDiv; ?>
	<table width="100%" class="list">
		<tr class="listHead">
			<td class="left"><?php echo $spTextBack['Referring Domain'] ?? 'Referring Domain'?></td>
			<td class="left"><?php echo $spTextBack['Source Page'] ?? 'Source Page'?></td>
			<td class="left"><?php echo $spTextBack['Anchor Text'] ?? 'Anchor Text'?></td>
			<td><?php echo $spText['common']['Status'] ?? 'Status'?></td>
			<td><?php echo $spTextBack['Domain Rank'] ?? 'Domain Rank'?></td>
			<td><?php echo $spTextBack['First Seen'] ?? 'First Seen'?></td>
			<td class="right"><?php echo $spTextBack['Last Seen'] ?? 'Last Seen'?></td>
		</tr>
		<?php
		$i = 0;
		foreach ($list as $linkInfo) {
			$class = ($i % 2) ? "blue_row" : "white_row";
			?>
			<tr class="<?php echo $class?>">
				<td class="left"><?php echo htmlspecialchars($linkInfo['domain_from'] ?? '')?></td>
				<td class="left" style="word-break:break-all;">
					<a href="<?php echo htmlspecialchars($linkInfo['url_from'], ENT_QUOTES)?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($linkInfo['url_from'])?></a>
				</td>
				<td class="left"><?php echo htmlspecialchars($linkInfo['anchor'] ?? '')?>
					<?php if (isset($linkInfo['dofollow'])) { ?>
						<br><span class="badge bg-<?php echo $linkInfo['dofollow'] ? 'success' : 'secondary'?>"><?php echo $linkInfo['dofollow'] ? ($spTextBack['Dofollow'] ?? 'Dofollow') : ($spTextBack['Nofollow'] ?? 'Nofollow')?></span>
					<?php } ?>
				</td>
				<td>
					<?php if (!empty($linkInfo['is_lost'])) { ?>
						<span class="badge bg-danger"><?php echo $spTextBack['Lost'] ?? 'Lost'?></span>
					<?php } else { ?>
						<span class="badge bg-success"><?php echo $spTextBack['Live'] ?? 'Live'?></span>
					<?php } ?>
				</td>
				<td><?php echo isset($linkInfo['domain_from_rank']) ? intval($linkInfo['domain_from_rank']) : '-'?></td>
				<td><?php echo !empty($linkInfo['first_seen']) ? $linkInfo['first_seen'] : '-'?></td>
				<td class="right"><?php echo !empty($linkInfo['last_seen']) ? $linkInfo['last_seen'] : '-'?></td>
			</tr>
			<?php
			$i++;
		}
		?>
	</table>
<?php } ?>
</div>

<?php } ?>
