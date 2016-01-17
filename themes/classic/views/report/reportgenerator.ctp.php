<div class="Center" style='width:100%;'>
<div class="col" style="border: none;padding-left:8px;">
<div class="Block">
<table width="100%" border="0" cellspacing="5px" cellpadding="0">
	<?php foreach ($allWebsiteList as $i => $websiteInfo) {
    ?>
	<tr>
		<td style="border: 1px solid #b0c2cc;padding:3px;font-size: 12px;" id="rep<?php echo $i?>">
			<script type="text/javascript">
				scriptDoLoad('cron.php?sec=generate&website_id=<?php echo $websiteInfo['id']?>&repTools=<?php echo $repTools;
    ?>', 'rep<?php echo $i?>');
			</script>
		</td>
	</tr>
	<?php 
}?>
</table>

</div>
</div>
</div>