<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel["Proxy Perfomance"]); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#proxyPerfHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="proxyPerfHelpModal" tabindex="-1" role="dialog" aria-labelledby="proxyPerfHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-tachometer-alt"></i></div>
				<h5 class="modal-title" id="proxyPerfHelpModalLabel">About Proxy Perfomance</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>For a chosen date range, shows each proxy's crawl success/failure counts and average score, drawn from the crawl log of every crawl that was routed through it.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<input type="hidden" name="sec" value="perfomance">
<table class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
    	<td>
    		<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
    		<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    	</td>
		<th class="pl-4"><?php echo $spText['label']['Order By']?>: </th>
		<td>
			<select name="order_by" onchange="<?php echo $searchFun?>" class="custom-select">
				<?php
				$inactCheck = $actCheck = "";
				if ($statVal == 'success') {
				    $actCheck = "selected";
				} elseif($statVal == 'fail') {
				    $inactCheck = "selected";
				}
				?>
				<option value="success" <?php echo $actCheck?> ><?php echo $spText['label']["Success"]?></option>
				<option value="fail" <?php echo $inactCheck?> ><?php echo $spText['label']["Fail"]?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Proxy']?></td>
		<td><?php echo $spTextProxy['Request Count']?></td>
		<td><?php echo $spText['label']['Success']?></td>
		<td><?php echo $spText['label']['Fail']?></td>
	</tr>
	<?php
	$colCount = 6;
	if (count($list) > 0) {
		foreach ($list as $i => $listInfo) {

            $logLink = scriptAJAXLinkHrefDialog('proxy.php', 'content', "sec=edit&proxyId=".$listInfo['proxy_id'], htmlspecialchars($listInfo['proxy'].":".$listInfo['port']));
            $countLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=&proxy_id=".$listInfo['proxy_id'], $listInfo['count'], '', 'OnClick', 1000);
			$successLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=success&proxy_id=".$listInfo['proxy_id'], $listInfo['success'], '', 'OnClick', 1000);
			$failLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=fail&proxy_id=".$listInfo['proxy_id'], $listInfo['fail'], '', 'OnClick', 1000);
            ?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $listInfo['proxy_id']?></td>
				<td><?php echo $logLink?></td>
				<td><?php echo $countLink?></td>
				<td><?php echo $successLink?></td>
				<td><?php echo $failLink?></td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
</form>
