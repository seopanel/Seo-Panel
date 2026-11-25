<p>
	<?php echo $spTextKeyword['Seed Keyword'] ?? 'Seed Keyword'?>: <strong><?php echo htmlspecialchars($seedKeyword)?></strong>
</p>

<?php if (empty($result['status'])) { ?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($result['message'] ?? '')?>
	</div>
<?php } else { ?>
	<table class="list">
		<tr class="listHead">
			<td><?php echo $spText['common']['Id']?></td>
			<td><?php echo $spText['common']['Keyword']?></td>
			<td><?php echo $spTextKeyword['Search Volume'] ?? 'Search Volume'?></td>
			<td><?php echo $spTextKeyword['Competition'] ?? 'Competition'?></td>
			<td>CPC</td>
			<td><?php echo $spText['common']['Action']?></td>
		</tr>
		<?php
		$colCount = 6;
		$ideas = $result['data'] ?? array();
		if (count($ideas) > 0) {
			foreach ($ideas as $i => $idea) {
				$competition = $idea['competition'] ?? '';
				$competitionColor = ($competition == 'LOW') ? 'success' : (($competition == 'HIGH') ? 'danger' : 'warning');
				?>
				<tr>
					<td><?php echo ($i+1)?></td>
					<td class="text-left"><?php echo htmlspecialchars($idea['keyword'])?></td>
					<td><?php echo isset($idea['search_volume']) ? intval($idea['search_volume']) : '-'?></td>
					<td>
						<?php if (!empty($competition)) { ?>
							<span class="badge bg-<?php echo $competitionColor?>"><?php echo htmlspecialchars($competition)?></span>
						<?php } else { ?>
							-
						<?php } ?>
					</td>
					<td><?php echo isset($idea['cpc']) ? '$'.number_format($idea['cpc'], 2) : '-'?></td>
					<td>
						<a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=new&name=<?php echo urlencode($idea['keyword'])?>')" class="btn btn-sm btn-secondary"><?php echo $spTextKeyword['Add as Keyword'] ?? 'Add as Keyword'?></a>
					</td>
				</tr>
				<?php
			}
		} else {
			echo showNoRecordsList($colCount-2);
		}
		?>
	</table>
<?php } ?>
