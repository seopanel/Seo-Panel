<p class="text-muted mb-2" style="font-size:0.9rem;">
	<?php echo $spText['common']['Keyword'] ?? 'Keyword' ?>: <strong><?php echo htmlspecialchars($keyword) ?></strong>
	&mdash; <?php echo $spTextKeyword['AI Overview Cited Sources'] ?? 'AI Overview Cited Sources' ?>
	<?php if (!empty($sources)): ?>
		(<?php echo htmlspecialchars($sources[0]['checked_date']) ?>)
	<?php endif; ?>
</p>

<?php if (empty($sources)): ?>
	<div class="alert alert-info mb-0">
		<i class="fa fa-info-circle"></i> <?php echo $spTextKeyword['No AI Overview citations recorded for this keyword yet'] ?? 'No AI Overview citations recorded for this keyword yet.' ?>
	</div>
<?php else: ?>
	<table width="100%" class="list">
		<tr class="listHead">
			<td width="10%">#</td>
			<td><?php echo $spText['common']['Website'] ?? 'Domain' ?></td>
			<td>URL</td>
		</tr>
		<?php foreach ($sources as $i => $src): ?>
			<tr class="<?php echo ($i % 2) ? 'blue_row' : 'white_row' ?>" <?php echo !empty($src['is_tracked']) ? 'style="background:#fffbe6;"' : '' ?>>
				<td><?php echo intval($src['ref_position']) ?></td>
				<td>
					<?php if (!empty($src['is_tracked'])): ?>
						<i class="fas fa-star" style="color:#f0ad4e;" title="Your website"></i>
					<?php endif; ?>
					<?php echo htmlspecialchars($src['domain']) ?>
				</td>
				<td style="font-size:0.85rem; word-break:break-all;">
					<a href="<?php echo htmlspecialchars($src['url']) ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($src['title'] ?: $src['url']) ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
