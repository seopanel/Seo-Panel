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
	    echo showNoRecordsList($colCount - 2);		
	} 
	?>
</table>