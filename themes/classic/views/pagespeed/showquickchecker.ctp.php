<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick PageSpeed Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#psQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="psQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('psQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Quick PageSpeed Checker</div>
		<div class="aiv-intro-desc">Paste up to 5 URLs and get live desktop + mobile PageSpeed scores from Google, instantly. <a href="javascript:void(0);" data-toggle="modal" data-target="#psQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="psQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="psQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="psQuickHelpModalLabel">About Quick PageSpeed Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Paste up to 5 URLs and get an immediate desktop and mobile PageSpeed score for each, by calling Google's PageSpeed Insights API live. Nothing is saved.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Requires a Google API key configured on this server. Only available to admins, or when enabled for your account.</span>
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
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('pagespeed.php', 'search_form', 'subcontent')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickproceedaction']?>
	</div>
</div>