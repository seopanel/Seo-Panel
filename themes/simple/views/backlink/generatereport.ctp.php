<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Generate Backlinks Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#blGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="blGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="blGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="blGenHelpModalLabel">About Generate Backlinks Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Calls the Moz API for all (or one) of your active websites and saves today's backlink counts into Backlinks Reports - this is what actually creates the history those reports show.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Also saves matching rank/authority metrics, keeping Backlink and Rank history in sync. Requires a working Moz API connection - a failed site is skipped, not saved as a false zero. Restricted to admins or accounts with report-generation permission.</span>
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
			<select name="website_id" id="website_id" class="custom-select">
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
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('backlinks.php', 'search_form', 'subcontent', '&sec=generate')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>