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

if (!empty($msg)) {
    showSuccessMsg($msg, false);
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='35%'><?php echo $spTextPanel['My Profile']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="blue_row">
		<th class="td_left_col"><?php echo $spText['login']['First Name']?>:</th>
		<td class="td_right_col">
			<?php echo $userInfo['first_name'] . " " . $userInfo['last_name']; ?>
		</td>
	</tr>
	<tr class="white_row">
		<th class="td_left_col"><?php echo $spText['login']['User Type']?>:</th>
		<td class="td_right_col"><?php echo $userTypeInfo['description']?></td>
	</tr>
	<tr class="blue_row">
		<th class="td_left_col"><?php echo $spText['login']['Username']?>:</th>
		<td class="td_right_col"><?php echo $userInfo['username']?></td>
	</tr>
	<tr class="white_row">
		<th class="td_left_col"><?php echo $spText['login']['Email']?>:</th>
		<td class="td_right_col"><?php echo $userInfo['email']; ?></td>
	</tr>
	<tr class="blue_row">
		<th class="td_left_col"><?php echo $spTextSubscription['Keyword Limit']?>:</th>
		<td class="td_right_col"><?php echo $userTypeInfo['keywordcount']; ?></td>
	</tr>
	<tr class="white_row">
		<th class="td_left_col"><?php echo $spTextSubscription['Website Limit']?>:</th>
		<td class="td_right_col"><?php echo $userTypeInfo['websitecount']; ?></td>
	</tr>
	<tr class="blue_row">
		<th class="td_left_col"><?php echo $spTextUser['Expiry Date']?>:</th>
		<td class="td_right_col"><?php echo empty($userInfo['expiry_date']) ? "" : date("d M Y", strtotime($userInfo['expiry_date'])); ?></td>
	</tr>
	<tr class="white_row">
		<td class="tab_left_bot_noborder" style="text-align: right; padding: 12px;">
			<?php if ($subscriptionActive && !isAdmin()) {
    ?>
	         	<a onclick="scriptDoLoad('users.php?sec=renew-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="actionbut">
	         		 &lt;&lt; <?php echo $spTextSubscription['Renew Subscription'];
    ?>
	         	</a>
         	<?php 
}?>
		</td>
		<td class="tab_right_bot" style="text-align: left; padding: 12px;">
			<a onclick="scriptDoLoad('users.php?sec=edit-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spTextPanel['Edit My Profile']?>  &gt;&gt;
         	</a>
		</td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>