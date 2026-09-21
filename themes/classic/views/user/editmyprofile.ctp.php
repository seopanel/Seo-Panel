<?php echo showSectionHead($spTextPanel['Edit My Profile']); ?>
<?php if(!empty($msg)){ showSuccessMsg($msg, false);} ?>
<form id="updateUser">
<input type="hidden" name="sec" value="updatemyprofile"/>
<input type="hidden" name="oldName" value="<?php echo $post['oldName']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<input type="hidden" name="oldEmail" value="<?php echo $post['oldEmail']?>"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Edit My Profile']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="myprofile_userName"><strong><?php echo $spText['login']['Username']?>:</strong></label></td>
		<td class="td_right_col"><input type="text" id="myprofile_userName" name="userName" value="<?php echo $post['userName']?>" class="form-control"><?php echo $errMsg['userName']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="myprofile_password"><strong><?php echo $spText['login']['Password']?>:</strong></label></td>
		<td class="td_right_col"><input type="password" id="myprofile_password" name="password" value="" class="form-control"><?php echo $errMsg['password']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="myprofile_confirmPassword"><strong><?php echo $spText['login']['Confirm Password']?>:</strong></label></td>
		<td class="td_right_col"><input type="password" id="myprofile_confirmPassword" name="confirmPassword" value="" class="form-control"><?php echo $errMsg['confirmPassword']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="myprofile_firstName"><strong><?php echo $spText['login']['First Name']?>:</strong></label></td>
		<td class="td_right_col"><input type="text" id="myprofile_firstName" name="firstName" value="<?php echo $post['firstName']?>" class="form-control"><?php echo $errMsg['firstName']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="myprofile_lastName"><strong><?php echo $spText['login']['Last Name']?>:</strong></label></td>
		<td class="td_right_col"><input type="text" id="myprofile_lastName" name="lastName" value="<?php echo $post['lastName']?>" class="form-control"><?php echo $errMsg['lastName']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="myprofile_email"><strong><?php echo $spText['login']['Email']?>:</strong></label></td>
		<td class="td_right_col"><input type="text" id="myprofile_email" name="email" value="<?php echo $post['email']?>" class="form-control"><?php echo $errMsg['email']?></td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('users.php?sec=my-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('users.php', 'updateUser', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
