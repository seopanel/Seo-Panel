<p class="text-muted mb-2" style="font-size:0.9rem;">
	<?php echo $spTextAIV['Competitor'] ?? 'Competitor'?>: <strong><?php echo htmlspecialchars($domain) ?></strong>
	&mdash; <?php echo $spTextAIV['Keywords where this competitor is cited in the AI Overview'] ?? 'Keywords where this competitor is cited in the AI Overview' ?>
</p>

<?php if (empty($rows)): ?>
	<div class="alert alert-info mb-0">
		<i class="fa fa-info-circle"></i> <?php echo $spTextAIV['No overlapping keywords found for this competitor'] ?? 'No overlapping keywords found for this competitor.' ?>
	</div>
<?php else: ?>
	<table width="100%" class="list">
		<tr class="listHead">
			<td><?php echo $spText['common']['Keyword'] ?? 'Keyword' ?></td>
			<td style="text-align:left;"><?php echo $spText['common']['Search Engine'] ?? 'Search Engine' ?></td>
			<td style="text-align:left;"><?php echo $spTextAIV['You Cited?'] ?? 'You Cited?' ?></td>
			<td style="text-align:left;"><?php echo $spTextAIV["Competitor's Citation"] ?? "Competitor's Citation" ?></td>
		</tr>
		<?php foreach ($rows as $i => $row): ?>
			<tr class="<?php echo ($i % 2) ? 'blue_row' : 'white_row' ?>">
				<td><?php echo htmlspecialchars($row['keyword_name']) ?></td>
				<td style="text-align:left;"><?php echo htmlspecialchars($row['se_domain']) ?></td>
				<td style="font-size:0.85rem; word-break:break-all; text-align:left;">
					<?php if (!empty($row['tracked_cited'])): ?>
						<i class="fas fa-check-circle" style="color:#28a745;"></i> <?php echo $spText['common']['Yes'] ?? 'Yes'?>
						<?php if (!empty($row['own_url'])): ?>
							<br>#<?php echo intval($row['own_position']) ?>
							<a href="<?php echo htmlspecialchars($row['own_url']) ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($row['own_title'] ?: $row['own_url']) ?></a>
							<br><small><a href="<?php echo htmlspecialchars($row['own_url']) ?>" target="_blank" rel="noopener" style="color:#8a8ea3; font-weight:400;"><?php echo htmlspecialchars($row['own_url']) ?></a></small>
						<?php endif; ?>
					<?php else: ?>
						<i class="fas fa-times-circle" style="color:#dc3545;"></i> <?php echo $spText['common']['No'] ?? 'No'?>
					<?php endif; ?>
				</td>
				<td style="font-size:0.85rem; word-break:break-all; text-align:left;">
					<?php if (!empty($row['ref_url'])): ?>
						#<?php echo intval($row['ref_position']) ?>
						<a href="<?php echo htmlspecialchars($row['ref_url']) ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($row['ref_title'] ?: $row['ref_url']) ?></a>
						<br><small><a href="<?php echo htmlspecialchars($row['ref_url']) ?>" target="_blank" rel="noopener" style="color:#8a8ea3; font-weight:400;"><?php echo htmlspecialchars($row['ref_url']) ?></a></small>
					<?php else: ?>
						<span class="text-muted">&mdash;</span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
