<?php echo showSectionHead($spTextUser['Edit User']); ?>
<form id="updateUser">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="oldName" value="<?php echo $post['oldName']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<input type="hidden" name="oldEmail" value="<?php echo $post['oldEmail']?>"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextUser['Edit User']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="edit_userName"><?php echo $spText['login']['Username']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="edit_userName" name="userName" value="<?php echo $post['userName']?>" class="form-control">
			<?php echo $errMsg['userName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="edit_password"><?php echo $spText['login']['Password']?>:</label></td>
		<td class="td_right_col">
			<input type="password" id="edit_password" name="password" value="" class="form-control">
			<?php echo $errMsg['password']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="edit_confirmPassword"><?php echo $spText['login']['Confirm Password']?>:</label></td>
		<td class="td_right_col">
			<input type="password" id="edit_confirmPassword" name="confirmPassword" value="" class="form-control">
			<?php echo $errMsg['confirmPassword']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="edit_firstName"><?php echo $spText['login']['First Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="edit_firstName" name="firstName" value="<?php echo $post['firstName']?>" class="form-control">
			<?php echo $errMsg['firstName']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="edit_lastName"><?php echo $spText['login']['Last Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="edit_lastName" name="lastName" value="<?php echo $post['lastName']?>" class="form-control">
			<?php echo $errMsg['lastName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="edit_email"><?php echo $spText['login']['Email']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="edit_email" name="email" value="<?php echo $post['email']?>" class="form-control">
			<?php echo $errMsg['email']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="edit_userType"><?php echo $spText['login']['User Type']?>:</label></td>
		<td class="td_right_col">
			<select name="userType" id="edit_userType" class="custom-select">
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
		<td class="td_left_col"><label for="edit_expiry_date"><?php echo $spTextUser['Expiry Date']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="edit_expiry_date" name="expiry_date" value="<?php echo $post['expiry_date']?>" class="date_fld form-control">
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
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('users.php', 'updateUser', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
