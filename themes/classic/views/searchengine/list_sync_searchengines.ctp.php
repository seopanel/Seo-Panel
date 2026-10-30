<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Sync Search Engines']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#seSyncHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="seSyncHelpModal" tabindex="-1" role="dialog" aria-labelledby="seSyncHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="seSyncHelpModalLabel">About Sync Search Engines</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pulls the latest list of supported search engines from SEO Panel's own update source, so new engines become available without a full software update.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
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