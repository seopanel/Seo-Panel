<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Import Websites'] . "({$spTextTools['webmaster-tools']})"); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#webImportWmtHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="webImportWmtHelpModal" tabindex="-1" role="dialog" aria-labelledby="webImportWmtHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-file-upload"></i></div>
				<h5 class="modal-title" id="webImportWmtHelpModalLabel">About Import Websites (Webmaster Tools)</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pulls the list of properties already verified in your connected Google Search Console account and adds them here as websites, instead of entering each one manually.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Needs Google Search Console connected under Settings &gt; Connections first.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
if (!empty($msg)) {
	?>
	<p class="dirmsg">
		<font class="success"><?php echo $msg?></font>
	</p>
	<?php 
}	

if(!empty($validationMsg)){
	?>
	<p class="dirmsg">
		<font class="error"><?php echo $validationMsg?></font>
	</p>
	<?php 
	}
?>

<form id="projectform">
<input type="hidden" name="sec" value="importWebmasterTools"/>
<table id="cust_tab">
	<tr class="form_head">
		<th width="40%"><?php echo $spTextPanel['Import Websites']; ?></th>
		<th>&nbsp;</th>
	</tr>
	<?php if(!empty($isAdmin)){ ?>	
		<tr class="form_data">
			<td><?php echo $spText['common']['User']?>:</td>
			<td>
				<select name="userid" class="custom-select">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $userSelected){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>						
					<?php }?>
				</select>
			</td>
		</tr>
	<?php } else { ?>
		<tr class="form_data">
			<td><?php echo $spText['common']['User']?>:</td>
			<td><?php echo $userName?></td>
		</tr>
	<?php }?>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('websites.php', 'projectform', 'import_result_div')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<br>
<div id="import_result_div" class="mt-4 pt-4"></div>