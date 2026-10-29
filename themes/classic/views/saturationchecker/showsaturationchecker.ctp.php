<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextSat['Quick Search Engine Saturation Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#satQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="satQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('satQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Quick Saturation Checker</div>
		<div class="aiv-intro-desc">Paste URLs and see how many of that site's pages are indexed by Google and Bing right now, with nothing saved. <a href="javascript:void(0);" data-toggle="modal" data-target="#satQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="satQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="satQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="satQuickHelpModalLabel">About Quick Saturation Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Paste a list of URLs and this runs a "site:" search against Google and Bing for each, showing how many of that site's pages are indexed - its "saturation" - with a link to the live results.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Nothing is saved - this is a one-off check. Only available to admins, or when enabled for your account.</span>
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
			<textarea name="website_urls" rows="8" class="form-control"></textarea>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('saturationchecker.php', 'search_form', 'subcontent')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextSat['clickproceedsaturation']?>
	</div>
</div>