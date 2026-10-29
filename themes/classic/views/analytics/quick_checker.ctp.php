<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('analytics.php', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#anQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="anQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('anQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Analytics Quick Checker</div>
		<div class="aiv-intro-desc">Pulls live Google Analytics metrics for a website and date range, right now, with nothing saved. <a href="javascript:void(0);" data-toggle="modal" data-target="#anQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="anQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="anQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="anQuickHelpModalLabel">About Analytics Quick Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pulls live GA4 metrics (users, new users, sessions, bounce rate, average session duration, goal completions) for a chosen website and date range, broken down by traffic source, directly from Google Analytics.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Needs Google Analytics connected under Settings &gt; Connections and a GA4 property configured for the website. Nothing here is saved.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form' onsubmit="<?php echo $actFun; ?>;return false;">
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select">
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
		<th>&nbsp;</th>
		<td>
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