<?php echo showSectionHead($spTextPanel['New User']); ?>
<form id="newUser">
<input type="hidden" name="sec" value="create"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New User']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Username']?>:</td>
		<td class="td_right_col">
			<input type="text" name="userName" value="<?php echo $post['userName']?>" class="form-control">
			<?php echo $errMsg['userName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['Password']?>:</td>
		<td class="td_right_col">
			<input type="password" name="password" value="<?php echo $post['password']?>" class="form-control">
			<?php echo $errMsg['password']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Confirm Password']?>:</td>
		<td class="td_right_col">
			<input type="password" name="confirmPassword" value="<?php echo $post['confirmPassword']?>" class="form-control">
			<?php echo $errMsg['confirmPassword']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['First Name']?>:</td>
		<td class="td_right_col">
			<input type="text" name="firstName" value="<?php echo $post['firstName']?>" class="form-control">
			<?php echo $errMsg['firstName']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Last Name']?>:</td>
		<td class="td_right_col">
			<input type="text" name="lastName" value="<?php echo $post['lastName']?>" class="form-control">
			<?php echo $errMsg['lastName']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['Email']?>:</td>
		<td class="td_right_col">
			<input type="text" name="email" value="<?php echo $post['email']?>" class="form-control">
			<?php echo $errMsg['email']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['User Type']?>:</td>
		<td class="td_right_col">
			<select name="userType" class="custom-select">
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
		<td class="td_left_col"><?php echo $spTextUser['Expiry Date']?>:</td>
		<td class="td_right_col">
			<input type="text" name="expiry_date" value="<?php echo $post['expiry_date']?>" class="date_fld form-control">
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
