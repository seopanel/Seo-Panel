<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['New User']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#userNewHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="userNewHelpModal" tabindex="-1" role="dialog" aria-labelledby="userNewHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-user-plus"></i></div>
				<h5 class="modal-title" id="userNewHelpModalLabel">About New User</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Create a new user account - username, password, user type (permission level), and account details.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id="newUser">
<input type="hidden" name="sec" value="create"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New User']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="new_userName"><?php echo $spText['login']['Username']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="new_userName" name="userName" value="<?php echo $post['userName']?>" class="form-control">
			<?php echo $errMsg['userName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="new_password"><?php echo $spText['login']['Password']?>:</label></td>
		<td class="td_right_col">
			<input type="password" id="new_password" name="password" value="<?php echo $post['password']?>" class="form-control">
			<?php echo $errMsg['password']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="new_confirmPassword"><?php echo $spText['login']['Confirm Password']?>:</label></td>
		<td class="td_right_col">
			<input type="password" id="new_confirmPassword" name="confirmPassword" value="<?php echo $post['confirmPassword']?>" class="form-control">
			<?php echo $errMsg['confirmPassword']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="new_firstName"><?php echo $spText['login']['First Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="new_firstName" name="firstName" value="<?php echo htmlspecialchars($post['firstName'], ENT_QUOTES)?>" class="form-control">
			<?php echo $errMsg['firstName']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="new_lastName"><?php echo $spText['login']['Last Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="new_lastName" name="lastName" value="<?php echo htmlspecialchars($post['lastName'], ENT_QUOTES)?>" class="form-control">
			<?php echo $errMsg['lastName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="new_email"><?php echo $spText['login']['Email']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="new_email" name="email" value="<?php echo htmlspecialchars($post['email'], ENT_QUOTES)?>" class="form-control">
			<?php echo $errMsg['email']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="new_userType"><?php echo $spText['login']['User Type']?>:</label></td>
		<td class="td_right_col">
			<select name="userType" id="new_userType" class="custom-select">
				<?php foreach ($userTypeList as $key => $val) {?>
					<?php if ($post['userType'] == $val['id']) {?>
						<option value="<?php echo $val['id']?>" selected><?php echo $val['user_type']?></option>
					<?php } else {?>
						<option value="<?php echo $val['id']?>"><?php echo $val['user_type']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="new_expiry_date"><?php echo $spTextUser['Expiry Date']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="new_expiry_date" name="expiry_date" value="<?php echo $post['expiry_date']?>" class="date_fld form-control">
			<script type="text/javascript">
			$(function() {
				$( "input[name='expiry_date']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    		<p><?php echo $errMsg['expiry_date']?></p>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('users.php', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('users.php', 'newUser', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
