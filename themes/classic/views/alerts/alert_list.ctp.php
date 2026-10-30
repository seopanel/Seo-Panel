<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel["Alerts"]); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#alertsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="alertsHelpModal" tabindex="-1" role="dialog" aria-labelledby="alertsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bell"></i></div>
				<h5 class="modal-title" id="alertsHelpModalLabel">About Alerts</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>These are your own in-app notifications, covering things like a new SEO Panel version being available, Seo Panel API connection or verification problems, and report-related warnings. Filter the list by category, keyword, or date, and click any row to see its full message.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
$searchFun = "scriptDoLoadPost('$pgScriptPath', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<table class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($post['keyword'], ENT_QUOTES)?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Category']?>: </th>
		<td>
			<select name="alert_category" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($alertCategory as $cat => $catLabel) {
				    $selectType = ($cat == $post['alert_category']) ? "selected" : "";
					?>
					<option value="<?php echo $cat?>" <?php echo $selectType; ?> ><?php echo $catLabel?></option>
					<?php
				}
				?>
			</select>
		</td>
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
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>

<div class="mt-4 mb-2">
	<strong><?php echo $spTextPanel["Current Time"]?>:</strong> <?php echo date("Y-m-d H:i:s <b>T(P)</b>"); ?>
</div>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Category']?></td>
		<td><?php echo $spText['label']['Subject']?></td>
		<td><?php echo $spText['common']['Details']?></td>
		<td><?php echo $spText['label']['Updated']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 7;
	if (count($list) > 0) {
		foreach ($list as $listInfo) {
		    $alertLink = scriptAJAXLinkHrefDialog($pgScriptPath, 'content', "sec=alert_info&id=".$listInfo['id'], $listInfo['id']);
			?>
			<tr class="table-<?php echo $listInfo['alert_type']?>">
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $alertLink?></td>
				<td><?php echo $alertCategory[$listInfo['alert_category']]?></td>
				<td><?php echo stripslashes($listInfo['alert_subject'])?></td>
				<td><?php echo stripslashes($listInfo['alert_message'])?></td>
				<td><?php echo $listInfo['alert_time']?></td>
				<td>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select" style="width: 180px;"
						onchange="doAction('<?php echo $pgScriptPath?>', 'content', 'id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="delete_alert"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount);
	}
	?>
</table>
<?php
$delFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('$pgScriptPath', 'listform', 'content', '&sec=delete_all_alerts&pageno=$pageNo')";
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
