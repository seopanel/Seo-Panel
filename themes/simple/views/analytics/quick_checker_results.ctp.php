<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Source']?></th>
		<?php foreach ($metricColList as $metricLabel) {?>
			<th><?php echo $metricLabel?></th>
		<?php }?>
	</tr>
	<tr>
		<td><?php echo$spText['common']['Total']?></td>
		<?php foreach ($metricColList as $metricName => $metricLabel) {?>
			<td>
				<?php echo $websiteReport[$metricName]?><?php echo $metricName == 'bounceRate' ? "%" : ""?>
			</td>
		<?php }?>
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
<?php //echo showSectionHead($spTextTools['Keyword Search Reports']); ?>
<table id="cust_tab" class="tablesorter">
	<thead>
		<tr>
			<th><?php echo $spText['common']['Source']?></th>
			<?php foreach ($metricColList as $metricLabel) {?>
				<th><?php echo $metricLabel?></th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($sourceReport as $sourceName => $listInfo){
			?>
			<tr>
				<td><?php echo htmlspecialchars($sourceName, ENT_QUOTES)?></td>
				<?php foreach ($metricColList as $metricName => $metricLabel) {?>
					<td>
						<?php echo $listInfo[$metricName]?><?php echo $metricName == 'bounceRate' ? "%" : ""?>
					</td>
				<?php }?>
			</tr>
		<?php }?>
	</tbody>
</table>