<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Schedule Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#scheduleReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="scheduleReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="scheduleReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-calendar-alt"></i></div>
				<h5 class="modal-title" id="scheduleReportsHelpModalLabel">About Schedule Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Set up a recurring combined report (the same kind Archived Reports shows) to be emailed to you automatically on a schedule, instead of checking in manually.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
// if saved successfully
if (!empty($success)) {
    showSuccessMsg($spTextReport['sheduledsuccessfully'], false);
}

?>
<form id="schedule_form">
<input type="hidden" name="sec" value="schedule"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Schedule Reports']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php if(isAdmin()){ ?>
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spText['common']['User']?>:</td>
			<td class="td_right_col">
				<select id="user_id" name="user_id" class="custom-select" style="width:150px;" onchange="doLoad('user_id', 'reports.php?sec=schedule', 'content')">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $repSetInfo['user_id']){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
		</tr>
	<?php }?>
	<tr class="white_row">
		<td  class="td_left_col"><?php echo $spTextReport['Next report generation time']?>:</td>
		<td class="td_right_col"><?php echo $nextReportTime?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextReport['Reports generation interval']?>: </td>
		<td class="td_right_col">
			<select name="report_interval" class="custom-select">
				<?php foreach ($scheduleList as $key => $val) {
				    if ($key < SP_SYSTEM_REPORT_INTERVAL) continue;
					$selected = ($key == $reportInterval) ? "selected" : "";
					?>
					<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
					<?php
				}?>
			</select>
		</td>
	</tr>
	<?php if (SP_REPORT_EMAIL_NOTIFICATION) {?>
    	<tr class="white_row">
    		<td class="td_left_col"><?php echo $spTextReport['Email notification']?>:</td>
    		<td class="td_right_col">
    			<?php
    			$selected = $repSetInfo['email_notification'] ? 'selected' : '';
    			?>
    			<select name="email_notification" class="custom-select">
    				<option value="0"><?php echo $spText['common']['No']?></option>
    				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
    			</select>
    		</td>
    	</tr>
	<?php }?>
	<?php if (defined('SP_AI_INSIGHTS_EMAIL_NOTIFICATION') && SP_AI_INSIGHTS_EMAIL_NOTIFICATION) {?>
    	<tr class="blue_row">
    		<td class="td_left_col"><?php echo $spTextReport['AI Insights email notification'] ?? 'AI Insights email notification'?>:</td>
    		<td class="td_right_col">
    			<?php
    			$aiInsightsSelected = $repSetInfo['ai_insights_email_notification'] ? 'selected' : '';
    			?>
    			<select name="ai_insights_email_notification" class="custom-select">
    				<option value="0"><?php echo $spText['common']['No']?></option>
    				<option value="1" <?php echo $aiInsightsSelected?>><?php echo $spText['common']['Yes']?></option>
    			</select>
    		</td>
    	</tr>
	<?php }?>
	<?php if (defined('SP_AI_VISIBILITY_EMAIL_NOTIFICATION') && SP_AI_VISIBILITY_EMAIL_NOTIFICATION) {?>
    	<tr class="white_row">
    		<td class="td_left_col"><?php echo $spTextReport['AI Visibility email notification'] ?? 'AI Visibility email notification'?>:</td>
    		<td class="td_right_col">
    			<?php
    			$aiVisibilitySelected = $repSetInfo['ai_visibility_email_notification'] ? 'selected' : '';
    			?>
    			<select name="ai_visibility_email_notification" class="custom-select">
    				<option value="0"><?php echo $spText['common']['No']?></option>
    				<option value="1" <?php echo $aiVisibilitySelected?>><?php echo $spText['common']['Yes']?></option>
    			</select>
    		</td>
    	</tr>
	<?php }?>
	<tr class="blue_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('reports.php?sec=schedule', 'content')" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Cancel']?>
         	</a> &nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('reports.php', 'schedule_form', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>