<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Url']?></th>
		<th><?php echo $spText['label']['Reviews']?></th>
		<th><?php echo $spText['label']['Rating']?></th>
	</tr>
	<tr>
		<td><?php echo htmlspecialchars($smLink, ENT_QUOTES)?></td>
		<td>
			<?php echo $statInfo['reviews'];?>
		</td>
		<td><?php echo $statInfo['rating']?></td>
	</tr>
</table>