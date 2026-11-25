<p class="text-muted mb-2" style="font-size:0.9rem;">
	<?php echo $spText['common']['Keyword'] ?? 'Keyword' ?>: <strong><?php echo htmlspecialchars($keyword['name']) ?></strong>
	&mdash; <?php echo $spTextReport['On-Page Score'] ?? 'On-Page Score' ?>
</p>

<?php if (empty($keyword['target_url'])): ?>
	<div class="alert alert-info mb-0">
		<i class="fa fa-info-circle"></i> <?php echo $spTextReport['No target URL set'] ?? 'No target URL set'?>
	</div>
<?php elseif (($score['status'] ?? '') === 'not_crawled'): ?>
	<div class="alert alert-info mb-0">
		<i class="fa fa-info-circle"></i> <?php echo $spTextReport['Target page not crawled yet'] ?? 'Target page not crawled yet'?>
		<br><small><?php echo htmlspecialchars($keyword['target_url'])?></small>
	</div>
<?php else: ?>
	<p style="font-size:0.85rem; word-break:break-all;">
		<a href="<?php echo htmlspecialchars($keyword['target_url'], ENT_QUOTES)?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($keyword['target_url'])?></a>
	</p>
	<div style="margin-bottom:12px;">
		<strong style="font-size:1.1rem; color:<?php echo $score['passed'] >= 4 ? '#28a745' : ($score['passed'] >= 2 ? '#b8790a' : '#dc3545')?>;"><?php echo intval($score['passed'])?>/<?php echo intval($score['total'])?></strong>
		<span class="text-muted"> <?php echo $spText['label']['Score'] ?? 'Score'?></span>
	</div>
	<table width="100%" class="list">
		<tr class="listHead">
			<td style="text-align:left;">&nbsp;</td>
			<td style="text-align:left;"><?php echo $spText['label']['Check'] ?? 'Check'?></td>
		</tr>
		<?php $rowNum = 0; foreach ($score['checks'] as $check): $rowNum++; ?>
			<tr class="<?php echo ($rowNum % 2) ? 'blue_row' : 'white_row'?>">
				<td style="width:40px;">
					<?php if (!empty($check['pass'])): ?>
						<i class="fas fa-check-circle" style="color:#28a745;"></i>
					<?php else: ?>
						<i class="fas fa-times-circle" style="color:#dc3545;"></i>
					<?php endif; ?>
				</td>
				<td style="text-align:left;"><?php echo htmlspecialchars($spTextReport[$check['label']] ?? $check['label'])?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<div class="text-muted mt-2" style="font-size:0.8rem;">
		<strong><?php echo $spText['label']['Title'] ?? 'Title'?>:</strong> <?php echo htmlspecialchars($score['page']['page_title'] ?? '')?><br>
		<strong>H1:</strong> <?php echo !empty($score['page']['h1_text']) ? htmlspecialchars($score['page']['h1_text']) : '&mdash;'?><br>
		<strong><?php echo $_SESSION['text']['label']['Word Count'] ?? 'Word Count'?>:</strong> <?php echo intval($score['page']['word_count'] ?? 0)?>
	</div>
<?php endif; ?>
