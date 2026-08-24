<?php if (!empty($aio)): ?>
<div class="alert <?php echo $aio['supported'] ? ($aio['present'] ? 'alert-warning' : 'alert-secondary') : 'alert-info' ?>">
	<strong><?php echo $spTextKeyword['AI Overview']?>:</strong>
	<?php if (empty($aio['supported'])): ?>
		<?php echo $spTextKeyword['AI Overview is not available on your current data source']?>
		<?php echo $spTextKeyword['Configure DataForSEO credentials to enable this feature immediately']?>
	<?php else: ?>
		<?php echo !empty($aio['present']) ? $spTextKeyword['Present'] : $spTextKeyword['Absent'] ?>
		<?php if (!empty($aio['present'])): ?>
			&nbsp;|&nbsp;
			<strong><?php echo $spTextKeyword['Cited']?>:</strong>
			<?php if (!is_null($aio['citedPosition'])): ?>
				<?php echo $spTextKeyword['Yes']?> (#<?php echo intval($aio['citedPosition'])?>)
			<?php else: ?>
				<?php echo $spTextKeyword['No']?>
			<?php endif; ?>
			&nbsp;|&nbsp;
			<strong><?php echo $spTextKeyword['Sources']?>:</strong> <?php echo count($aio['refs'])?>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php if (!empty($aio['present']) && !empty($aio['refs'])): ?>
<table class="list">
	<tr class="listHead">
		<th><?php echo $spTextKeyword['AI Overview Cited Sources']?></th>
	</tr>
	<?php foreach ($aio['refs'] as $refInfo): ?>
	<tr <?php echo ($aio['citedPosition'] == $refInfo['position']) ? "style='background:#fffbe6;'" : "" ?>>
		<td>
			<a href='<?php echo $refInfo['url']?>' target='_blank' style="font-size: 14px;"><?php echo stripslashes($refInfo['title'] ?: $refInfo['url'])?></a>
			<label style="font-size: 13px; display:block;"><?php echo $refInfo['domain']?></label>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>
<?php endif; ?>
<table class="list">
	<tr class="listHead">
		<th><?php echo $spText['common']['Rank']?></th>
		<th><?php echo $spText['common']['Details']?></th>
	</tr>
	<?php
	$colCount = 2; 
	if(count($list) > 0) {
		foreach($list as $listInfo) {
            $foundClass = !empty($listInfo['found']) ? "bg-warning" : "";            
			?>
			<tr class="<?php echo $foundClass?>">
				<td class="fw-bold"><?php echo $listInfo['rank']; ?></td>
				<td id='seresult'>
					<a href='<?php echo $listInfo['url']?>' target='_blank' style="font-size: 14px;"><?php echo stripslashes($listInfo['title']);?></a>
					<p style="font-size: 13px;"><?php echo stripslashes($listInfo['description']);?><p>
					<label style="font-size: 13px;"><?php echo $listInfo['url']?></label>
				</td>
			</tr>
			<?php
		}
	} else {
	    if (!empty($pending)) {
	        echo showNoRecordsList($colCount - 2, $spTextKeyword['SEO Panel API is still processing this keyword']);
	    } else {
	        echo showNoRecordsList($colCount - 2);
	    }
	}
	?>
</table>