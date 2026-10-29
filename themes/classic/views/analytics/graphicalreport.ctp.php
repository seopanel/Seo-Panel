<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Graphical Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#anGraphHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="anGraphHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('anGraphHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-chart-area"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Graphical Reports</div>
		<div class="aiv-intro-desc">The same saved GA4 metric trend as Detailed Reports, drawn as a chart for one website/source over time. <a href="javascript:void(0);" data-toggle="modal" data-target="#anGraphHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="anGraphHelpModal" tabindex="-1" role="dialog" aria-labelledby="anGraphHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-chart-area"></i></div>
				<h5 class="modal-title" id="anGraphHelpModalLabel">About Graphical Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Charts a per-source Google Analytics metric trend over time for one website, instead of showing it as a table. Bounce rate is excluded from the combined chart by default, since it's an inverse metric.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads from already-saved GA4 data only - no live API call.</span>
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
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'analytics.php', 'source_area', 'sec=source_box')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo htmlspecialchars($websiteInfo['name'])?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo htmlspecialchars($websiteInfo['name'])?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
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
		<th><?php echo $spText['common']['Source']?>: </th>
		<td id="source_area">
			<?php echo $this->render('analytics/source_select_box', 'ajax'); ?>
		</td>
		<th class="pl-4"><?php echo $spText['label']['Report Type']?>: </th>
		<td>
			<select name="attr_type" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($colList as $key => $label){?>
					<?php if($key == $searchInfo['attr_type']){?>
						<option value="<?php echo $key?>" selected><?php echo $label?></option>
					<?php }else{?>
						<option value="<?php echo $key?>"><?php echo $label?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('analytics.php', 'search_form', 'content', '&sec=viewAnalyticsGraphReports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<?php echo $graphContent; ?>
</div>