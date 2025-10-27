<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Url']?></td>
		<td>
			<?php echo $spTextBack['Backlink Count']?>
			<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to this page"></i>
		</td>
		<td>
			<?php echo $spTextBack['Domain Backlink Count']?>
			<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to this root domain"></i>
		</td>
	</tr>
	<?php
	$colCount = 4;
	if (count($list) > 0) {
		foreach ($list as $i => $url) {
			?>
			<tr>
				<td><?php echo ($i+1)?></td>
				<td class="text-left"><?php echo $url?></td>
				<td width="150px" class="rankarea">
					<?php
					$backlinkCount = !empty($mozRankList[$i]['external_pages_to_page']) ? intval($mozRankList[$i]['external_pages_to_page']) : 0;
					?>
					<?php echo $backlinkCount > 0 ? number_format($backlinkCount) : '-'; ?>
				</td>
				<td width="150px" class="rankarea">
					<?php
					$domainBacklinkCount = !empty($mozRankList[$i]['external_pages_to_root_domain']) ? intval($mozRankList[$i]['external_pages_to_root_domain']) : 0;
					?>
					<?php echo $domainBacklinkCount > 0 ? number_format($domainBacklinkCount) : '-'; ?>
				</td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount - 2);
	}
	?>
</table>