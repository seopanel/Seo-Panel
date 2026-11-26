<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<table class="table table-bordered">
				<?php foreach($dirList as $i => $dirInfo){ ?>
				<tr>
					<td class="p-3" id="rep<?php echo $i?>">
						<script type="text/javascript">
							// bug fix: these used to all fire at once, with no
							// delay between them, unlike directorycheckercron.php's
							// own sleep(SP_CRAWL_DELAY) between each directory
							// (added specifically to avoid hammering target
							// directory sites / exhausting PHP workers) - this is
							// a client-driven fan-out (N independent AJAX calls),
							// not a single PHP process, so the same server-side
							// sleep() doesn't apply; staggering via setTimeout with
							// the same SP_CRAWL_DELAY setting achieves the same
							// spacing. Also now passes checkpr=1 (so Domain
							// Authority/Page Authority actually get refreshed - the
							// same param Directory Manager's own per-row "Check
							// Status" button already sends, see list.ctp.php) and
							// nodebug=1 (so the real Active/Inactive outcome shows
							// via getStatusLink(), instead of an always-green
							// "Saved status..." note regardless of what happened).
							setTimeout(function() {
								scriptDoLoad('directories.php?sec=checkdir&dir_id=<?php echo $dirInfo['id']?>&checkpr=1&nodebug=1', 'rep<?php echo $i?>');
							}, <?php echo intval($i) * intval(SP_CRAWL_DELAY) * 1000?>);
						</script>
					</td>
				</tr>
				<?php }?>
			</table>
		</div>
	</div>
</div>
