<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#wmQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="wmQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('wmQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Webmaster Tools Quick Checker</div>
		<div class="aiv-intro-desc">Pick a website and date range for a live, on-the-spot read of its Google Search Console data - clicks, impressions, CTR and position. <a href="javascript:void(0);" data-toggle="modal" data-target="#wmQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="wmQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="wmQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="wmQuickHelpModalLabel">About Webmaster Tools Quick Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick a website and a date range to query Google Search Console live and see overall clicks/impressions/CTR/average position, plus a per-keyword breakdown, right now.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Nothing is saved - this is a live check, unlike the other reports in this section which show previously-saved data. Needs Google Search Console connected under Settings &gt; Connections.</span>
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
			<select name="website_id" class="custom-select">
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
		<th><?php echo $spText['common']['Period']?>:</th>
    	<td>
    		<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
    		<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script type="text/javascript">
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    	</td>
    </tr>
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td>
			<input type="text" value="" name="name" class="form-control"/>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('webmaster-tools.php', 'search_form', 'subcontent', '&sec=doQuickChecker')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>