<?php
echo showSectionHead($spTextPanel['My Profile']);

// if payment cancelled
if (!empty($_GET['cancel'])) {
	showErrorMsg($spTextSubscription["Your transaction cancelled"], false);
}

// if payment error
if (!empty($_GET['failed'])) {
	showErrorMsg($spTextSubscription['internal-error-payment'], false);
}

// if payment error
if (!empty($_GET['expired'])) {
	showErrorMsg($spTextSubscription['account-expired'], false);
}

// if payment error
if (!empty($_GET['success'])) {
	showSuccessMsg($spTextSubscription['transaction-success'], false);
}

if(!empty($msg)){ showSuccessMsg($msg, false);}
?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='35%'><?php echo $spTextPanel['My Profile']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['First Name']?>:</strong></td>
		<td class="td_right_col">
			<?php echo $userInfo['first_name'] . " " . $userInfo['last_name']; ?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['User Type']?>:</strong></td>
		<td class="td_right_col"><?php echo $userTypeInfo['description']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['Username']?>:</strong></td>
		<td class="td_right_col"><?php echo $userInfo['username']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['Email']?>:</strong></td>
		<td class="td_right_col"><?php echo $userInfo['email']; ?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spTextSubscription['Keyword Limit']?>:</strong></td>
		<td class="td_right_col"><?php echo $userTypeInfo['keywordcount']; ?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spTextSubscription['Website Limit']?>:</strong></td>
		<td class="td_right_col"><?php echo $userTypeInfo['websitecount']; ?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spTextUser['Expiry Date']?>:</strong></td>
		<td class="td_right_col"><?php echo empty($userInfo['expiry_date']) ? "" : date("d M Y", strtotime($userInfo['expiry_date'])); ?></td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
		<td>
			<?php if ($subscriptionActive && !isAdmin() && !SP_CUSTOM_DEV) {?>
	         	<a onclick="scriptDoLoad('users.php?sec=renew-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
	         		 <?php echo $spTextSubscription['Renew Subscription']; ?>
	         	</a>
         	<?php }?>
			<a onclick="scriptDoLoad('users.php?sec=edit-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['Edit My Profile']?>
         	</a>
		</td>
	</tr>
</table>
