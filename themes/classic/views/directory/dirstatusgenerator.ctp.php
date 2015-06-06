<div class="Center" style='width:100%;'>
<div class="col" style="border: none;padding-left:8px;">
<div class="Block">
<table width="100%" border="0" cellspacing="5px" cellpadding="0">
	<?php foreach($dirList as $i => $dirInfo){ ?>
	<tr>
		<td style="border: 1px solid #b0c2cc;padding:3px;font-size: 12px;" id="rep<?php echo $i?>">
			<script type="text/javascript">
				scriptDoLoad('directories.php?sec=checkdir&dir_id=<?php echo $dirInfo['id']?>', 'rep<?php echo $i?>');
			</script>
		</td>
	</tr>
	<?php }?>
</table>

</div>
</div>
</div>