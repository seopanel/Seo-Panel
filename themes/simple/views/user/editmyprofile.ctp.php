<?php echo showSectionHead($spTextPanel['Edit My Profile']); ?>
<?php if(!empty($msg)){ showSuccessMsg($msg, false);} ?>
<form id="updateUser">
<input type="hidden" name="sec" value="updatemyprofile"/>
<input type="hidden" name="oldName" value="<?php echo $post['oldName']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<input type="hidden" name="oldEmail" value="<?php echo $post['oldEmail']?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Edit My Profile']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Username']?>:</td>
		<td class="td_right_col"><input type="text" name="userName" value="<?php echo $post['userName']?>"><?php echo $errMsg['userName']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['Password']?>:</td>
		<td class="td_right_col"><input type="password" name="password" value=""><?php echo $errMsg['password']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Confirm Password']?>:</td>
		<td class="td_right_col"><input type="password" name="confirmPassword" value=""><?php echo $errMsg['confirmPassword']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['First Name']?>:</td>
		<td class="td_right_col"><input type="text" name="firstName" value="<?php echo $post['firstName']?>"><?php echo $errMsg['firstName']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['login']['Last Name']?>:</td>
		<td class="td_right_col"><input type="text" name="lastName" value="<?php echo $post['lastName']?>"><?php echo $errMsg['lastName']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['login']['Email']?>:</td>
		<td class="td_right_col"><input type="text" name="email" value="<?php echo $post['email']?>"><?php echo $errMsg['email']?></td>
	</tr>		
	<tr class="white_row">
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
    		<a onclick="scriptDoLoad('users.php?sec=my-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('users.php', 'updateUser', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
