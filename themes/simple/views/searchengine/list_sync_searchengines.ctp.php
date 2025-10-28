<?php 
echo showSectionHead($spTextPanel['Sync Search Engines']); 
Session::showSessionMessges();
echo $pagingDiv
?>
<table id="cust_tab" class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Date']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td><?php echo $spText['common']['Results']?></td>
	</tr>
		<?php
		$colCount = 4;
		if (!empty($syncList)) {
    		foreach($syncList as $listInfo) {
    			?>
    			<tr>
    				<td><?php echo $listInfo['id'];?></td>
    				<td><?php echo $listInfo['sync_time'];?></td>
    				<td class="text-center">
    					<?php echo showStatusBadge($listInfo['status'], "successfail");?>
					</td>
    				<td><?php echo $listInfo['result'];?></td>
    			</tr>
    			<?php
    		}
		} else {
		    echo showNoRecordsList($colCount);
		}
		?>
</table>
<?php
if (SP_DEMO) {
    $syncFun = "alertDemoMsg()";
} else {
    $syncFun = "confirmSubmit('searchengine.php', 'listform', 'content', '&sec=do-sync-se')";
}
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="<?php echo $syncFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['Sync Search Engines']?>
         	</a>
    	</td>
	</tr>
</table>