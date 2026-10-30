<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Website Access Manager']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#webAccessHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="webAccessHelpModal" tabindex="-1" role="dialog" aria-labelledby="webAccessHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-user-lock"></i></div>
				<h5 class="modal-title" id="webAccessHelpModalLabel">About Website Access Manager</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick a user and choose exactly which of your websites they're allowed to see and work with, instead of giving them access to everything you own.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Part of the Subscription add-on - only visible when that plugin is active.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php echo $msg;?>
<form id="listform" name="listform">
<input type="hidden" name="sec" value="dowebsiteAccessManager"/>
<table class="search">
	<?php $fetchLink = "scriptDoLoadPost('users.php', 'listform', 'content')";?>
	<tr>
		<th><?php echo $spText['common']['User']?> : </th>
		<td>
			<select name="wam_user" onchange="<?php echo $fetchLink;?>">
				<?php foreach($userList as $key => $val) { 
				    if ($userId == $val['id']) { ?>
					<option value="<?php echo $val['id'];?>" selected><?php echo $val['username'];?></option>
				<?php } else { ?>
					<option value="<?php echo $val['id'];?>"><?php echo $val['username'];?></option>
				<?php } } ?>
			</select>
		</td>
	</tr>
</table>
<table id="cust_tab">
	<tr>
		<th width='80px'><input type="checkbox" id="checkall" onclick="checkList('checkall')"></th>
		<th><?php echo $spText['common']['Website']?></th>
		<!-- <th width='30%'>Access</th> -->
	</tr>
	<?php if ($userWebsiteList) { ?>
		<?php foreach($userWebsiteList as $key => $val) { ?>
			<tr>
    			<td>
    				<?php if ($val['access']) { ?>
    					<input type="checkbox" name="check_ws[]" value="<?php echo $val['id'];?>" checked>
    				<?php } else { ?>
    					<input type="checkbox" name="check_ws[]" value="<?php echo $val['id'];?>">
    				
    				<?php } ?>
    			</td>
    			<td><?php echo $val['url'];?></td>
    			<!-- <td>
    				<select name="user_ws_access_<?php echo $val['id'];?>">
    					<option value="read">Read</option>
    					<option value="write">Write</option>
    				</select>
    			</td> -->
			</tr>
		<?php }?>
	<?php } ?>
</table>
<?php if ($userId) { ?>
    <?php $submitLink = "scriptDoLoadPost('users.php', 'listform', 'content', '&action=updateUserWebsite')";?>
    <table class="actionSec">
    	<tr><td style="padding-top: 6px;text-align:right;"><a href="javascript:void(0);" class="actionbut" onclick="<?php echo $submitLink;?>">Update</a></td></tr>
    </table>
<?php } ?>
</form>