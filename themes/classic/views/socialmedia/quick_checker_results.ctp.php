<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Url']?></th>
		<th><?php echo $spText['label']['Likes']?></th>
		<th><?php echo $spText['label']['Followers']?></th>
	</tr>
	<tr>
		<td><?php echo htmlspecialchars($smLink, ENT_QUOTES)?></td>
		<td>
			<?php echo ($smType == 'facebook') ? $statInfo['likes'] : "-";?>
		</td>
		<td><?php echo $statInfo['followers']?></td>
	</tr>
</table>