<?php echo showSectionHead($spTextTools['Website Search Reports']); ?>
<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Url']?></th>
		<th><?php echo $spText['label']['Clicks']?></th>
		<th><?php echo $spText['label']['Impressions']?></th>
		<th><?php echo "CTR"?></th>
		<th><?php echo $spTextWB['Average Position']?></th>
	</tr>
	<tr>
		<td><?php echo $websiteInfo['url']?></td>
		<td><?php echo $websiteReport['clicks']?></td>
		<td><?php echo $websiteReport['impressions']?></td>
		<td><?php echo round($websiteReport['ctr'] * 100, 2)?></td>
		<td><?php echo round($websiteReport['average_position'], 2)?></td>
	</tr>
</table>

<script type="text/javascript">
$(document).ready(function() { 
    $("table").tablesorter({ 
		sortList: [[2,1]]
    });
});
</script>

<br>
<?php echo showSectionHead($spTextTools['Keyword Search Reports']); ?>
<table id="cust_tab" class="tablesorter">
	<thead>
		<tr>
			<th><?php echo $spText['common']['Id']?></th>
			<th><?php echo $spText['common']['Keyword']?></th>
			<th><?php echo $spText['label']['Clicks']?></th>
			<th><?php echo $spText['label']['Impressions']?></th>
			<th><?php echo "CTR"?></th>
			<th><?php echo $spTextWB['Average Position']?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 1;
		foreach($keywordAnalytics as $keyword => $listInfo){
			$foundStyle = !empty($searchInfo['name']) && stristr($keyword, $searchInfo['name']) ? "background-color: #ffff00;" : "";
			?>
			<tr style="<?php echo $foundStyle?>">
				<td><?php echo $i++?></td>
				<td><?php echo htmlspecialchars($keyword, ENT_QUOTES)?></td>
				<td><?php echo $listInfo['clicks']?></td>
				<td><?php echo $listInfo['impressions']?></td>
				<td><?php echo round($listInfo['ctr'] * 100, 2)?></td>
				<td><?php echo round($listInfo['position'], 2)?></td>
			</tr>
		<?php }?>
	</tbody>
</table>