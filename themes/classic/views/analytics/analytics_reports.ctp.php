<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Detailed Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#anDetailHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="anDetailHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('anDetailHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-table"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Detailed Reports</div>
		<div class="aiv-intro-desc">Day-by-day metrics for one website and traffic source, with day-over-day change highlighted. <a href="javascript:void(0);" data-toggle="modal" data-target="#anDetailHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="anDetailHelpModal" tabindex="-1" role="dialog" aria-labelledby="anDetailHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-table"></i></div>
				<h5 class="modal-title" id="anDetailHelpModalLabel">About Detailed Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A day-by-day table of one website's Google Analytics metrics for one traffic source over a date range, with day-over-day change highlighted.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads already-saved GA4 data from the daily sync - it doesn't call the Google API live.</span>
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
		<th><?php echo $spText['common']['Source']?>: </th>
		<td id="source_area">
			<?php echo $this->render('analytics/source_select_box', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('analytics.php', 'search_form', 'content', '&sec=viewAnalyticsReports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php
if(empty($sourceId)){
	?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Records Found']?>!
	</div>
	<?php
	exit;
}
?>
<br><br>
<div id='subcontent'>
<table id="cust_tab">	
	<tr>
		<th><?php echo $spText['common']['Date']?></th>
		<?php foreach ($metricColList as $colName => $colVal) {?>
			<th><?php echo $colVal?></th>
		<?php }?>
	</tr>
	<?php
	if (!empty($list)) {
    	foreach($list as $listInfo){            
    		?>
    		<tr>
    			<td><?php echo $listInfo['report_date']; ?></td>
    			<?php foreach ($metricColList as $colName => $colVal) {?>
    				<td><?php echo $listInfo[$colName].'</b> '. $listInfo['rank_diff_' . $colName]?></td>
    			<?php }?>
    		</tr>
    		<?php	
    	}
	} else {
	    ?>
	    <tr><td colspan="7"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
	    <?php
	}
	?>
</table>
</div>