<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextDir['Check Directory Submission Status']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#dirCheckHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="dirCheckHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('dirCheckHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-sync"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Check Submission Status</div>
		<div class="aiv-intro-desc">Re-checks your website's still-pending directory submissions live, to see if they've since been approved and listed. <a href="javascript:void(0);" data-toggle="modal" data-target="#dirCheckHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="dirCheckHelpModal" tabindex="-1" role="dialog" aria-labelledby="dirCheckHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="dirCheckHelpModalLabel">About Check Submission Status</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick a website and this actively re-checks each of its still-pending directory submissions by searching that directory's site for the submitted URL, updating the status if it's since been approved.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Makes live outbound requests to each pending directory's site to verify listing - can take a while if there are many pending submissions.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td>
			<a onclick="<?php echo $onClick?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='alert alert-info'><?php echo $spTextDir['selectwebsiteschecksub']?></p>
</div>
