<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Generate Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#psGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="psGenHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('psGenHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-sync"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Generate Reports</div>
		<div class="aiv-intro-desc">Runs a live PageSpeed check right now for one or all of your active websites and saves the result. <a href="javascript:void(0);" data-toggle="modal" data-target="#psGenHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="psGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="psGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="psGenHelpModalLabel">About Generate Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Triggers an on-demand PageSpeed check-and-save (desktop + mobile) for one or all of your active websites right now, instead of waiting for the scheduled cron.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Requires a Google API key configured on this server. If both desktop and mobile checks fail, nothing is saved rather than storing a misleading zero.</span>
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
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo htmlspecialchars($websiteInfo['name'])?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo htmlspecialchars($websiteInfo['name'])?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('pagespeed.php', 'search_form', 'subcontent', '&sec=generate')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>