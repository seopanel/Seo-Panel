<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick Rank Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#rankQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="rankQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('rankQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Quick Rank Checker</div>
		<div class="aiv-intro-desc">Paste a list of URLs and get Moz's Spam Score, Domain Authority and Page Authority for each, instantly. <a href="javascript:void(0);" data-toggle="modal" data-target="#rankQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="rankQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="rankQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="rankQuickHelpModalLabel">About Quick Rank Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Paste a list of URLs (up to 10 in demo mode) and get an on-the-spot Moz authority check - Spam Score, Domain Authority and Page Authority - for each, with no setup needed.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Results are shown immediately and not saved anywhere - it's a one-off lookup. Requires a configured Moz API key on this server.</span>
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
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('rank.php', 'search_form', 'subcontent', '&sec=quickrank')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextRank['enterurlproceed']?>
	</div>
	<?php
	// check MOZ api setup
	echo SettingsController::showCheckCategorySettings('moz', false);
	?>
</div>