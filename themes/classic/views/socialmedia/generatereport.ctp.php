<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Generate Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#smGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="smGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="smGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="smGenHelpModalLabel">About Generate Social Media Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Triggers an on-demand likes/followers check-and-save for one or all of your websites' social media links right now, rather than waiting for the scheduled cron.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Fetches each link's live page. Restricted to admins or accounts with report-generation permission. A link already checked today is skipped, not duplicated.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" class="custom-select" onchange="doLoad('website_id', 'social_media.php', 'generate_estimate', 'sec=estimateGenerateReports')">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('social_media.php', 'search_form', 'subcontent', '&sec=generate')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='generate_estimate'>
	<?php echo $this->render('socialmedia/generatereport_estimate', 'ajax'); ?>
</div>

<div id='subcontent'></div>
