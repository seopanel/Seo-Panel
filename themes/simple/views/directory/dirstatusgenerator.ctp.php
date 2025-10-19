<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<table class="table table-bordered">
				<?php foreach($dirList as $i => $dirInfo){ ?>
				<tr>
					<td class="p-3" id="rep<?php echo $i?>">
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
