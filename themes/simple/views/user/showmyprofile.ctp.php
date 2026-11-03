<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['My Profile']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#myProfileHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="myProfileHelpModal" tabindex="-1" role="dialog" aria-labelledby="myProfileHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-id-badge"></i></div>
				<h5 class="modal-title" id="myProfileHelpModalLabel">About My Profile</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Your own account details - username, email, password, two-factor authentication, and (if applicable) subscription/plan status.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
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
         	<a onclick="scriptDoLoad('users.php?sec=two-factor', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-secondary">
         		<i class="fas fa-shield-alt"></i> Two-Factor Authentication
         	</a>
		</td>
	</tr>
</table>
