<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextSat['GenerateSaturationReports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#satGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="satGenHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('satGenHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-sync"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Generate Saturation Reports</div>
		<div class="aiv-intro-desc">Runs a live Google/Bing saturation check right now for one or all of your websites and saves the result. <a href="javascript:void(0);" data-toggle="modal" data-target="#satGenHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="satGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="satGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="satGenHelpModalLabel">About Generate Saturation Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Triggers an on-demand saturation check-and-save for one or all of your websites right now, rather than waiting for the scheduled cron - this is what creates the history Saturation Reports shows.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Performs live Google/Bing searches. Restricted to admins or accounts with report-generation permission. Today's existing snapshot for a site is replaced, not duplicated.</span>
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
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('saturationchecker.php', 'search_form', 'subcontent', '&sec=generate')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>