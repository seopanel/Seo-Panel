<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Report Generation Logs']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#reportGenLogsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="reportGenLogsHelpModal" tabindex="-1" role="dialog" aria-labelledby="reportGenLogsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-list"></i></div>
				<h5 class="modal-title" id="reportGenLogsHelpModalLabel">About Report Generation Logs</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A grid showing, per user and date, whether their scheduled combined report (the one set up under Schedule Reports) was actually generated and emailed on that day.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Read-only - useful for confirming scheduled report emails are actually going out as expected.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
		</td>
		<th class="pl-4"><?php echo $spText['common']['User']?>: </th>
		<td>
			<select name="user_id" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($userList as $userInfo){?>
					<?php if($userInfo['id'] == $userId){?>
						<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
					<?php }else{?>
						<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('reports.php', 'search_form', 'content', '&sec=report_gen_logs')" class="btn btn-secondary">
				<?php echo $spText['button']['Show Records']?>
			</a>
		</td>
	</tr>
</table>
</form>

<script type="text/javascript">
$(document).ready(function() {
    $("#cust_tab").tablesorter({
		sortList: [[2,0]]
    });
});
</script>

<br><br>
<b><?php echo $spTextPanel["Current Time"]?>:</b> <?php echo date("Y-m-d H:i:s <b>T(P)</b>"); ?>
<br><br>
<div id='subcontent'>
<table id="cust_tab" class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['User']?></td>
		<td><?php echo $spText['common']['User Type']?></td>
		<td><?php echo $spTextUser['Expiry Date']?></td>
		<?php foreach ($logDateList as $logDate) {?>
			<td><?php echo $logDate?></td>
		<?php }?>
	</tr>
	<?php foreach ($logUserList as $i => $userInfo) {?>
		<tr>
			<td><?php echo $i + 1?></td>
			<td><?php echo $userInfo['username']?></td>
			<td><?php echo $userTypeList[$userInfo['utype_id']]['user_type']?></td>
			<td><?php echo formatDate($userInfo['expiry_date']); ?></td>
			<?php foreach ($logDateList as $logDate) {?>
				<td class="text-center">
					<?php
					echo !empty($logList[$logDate][$userInfo['id']]) ? showStatusBadge(1, "yesno") : showStatusBadge(0, "yesno");
					?>
				</td>
			<?php }?>
		</tr>
	<?php }?>
</table>
</div>