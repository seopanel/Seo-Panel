<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Directory']?></td>
		<td><?php echo $spText['common']['Date']?></td>
		<td><?php echo $spTextDir['Confirmation']?></td>
		<td><?php echo $spText['common']['Status']?></td>
	</tr>
	<?php
	$colCount = 4;
	if(count($list) > 0){
		$catCount = count($list);
		$i = 0;
		foreach($list as $listInfo){

			$class = ($i % 2) ? "blue_row" : "white_row";
			$confirm = empty($listInfo['status']) ? $spText['common']["No"] : $spText['common']["Yes"];
			$statusId = "status_".$listInfo['id'];
			$checkStatusLink = "<script>scriptDoLoad('directories.php', '$statusId', 'sec=checkstatus&id={$listInfo['id']}');</script>";
			?>
			<tr class="<?php echo $class?>">
				<td><?php echo $listInfo['domain']?></td>
				<td><?php echo date('Y-m-d', $listInfo['submit_time']); ?></td>
				<td><?php echo $confirm?></td>
				<td id="<?php echo $statusId?>"><?php echo $checkStatusLink?></td>
			</tr>
			<?php
			$i++;
		}
	}else{
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>