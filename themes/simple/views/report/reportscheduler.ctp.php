<?php
echo showSectionHead($spTextPanel['Schedule Reports']);

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
				<select id="user_id" name="user_id" style="width:150px;" onchange="doLoad('user_id', 'reports.php?sec=schedule', 'content')">
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
			<select name="report_interval">
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
    			<select name="email_notification">
    				<option value="0"><?php echo $spText['common']['No']?></option>
    				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
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
    		<a onclick="scriptDoLoad('reports.php?sec=schedule', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a> &nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('reports.php', 'schedule_form', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>