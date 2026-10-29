<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Keyword Search Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#wmKwReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="wmKwReportsHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('wmKwReportsHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-table"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Keyword Search Reports</div>
		<div class="aiv-intro-desc">Day-by-day Search Console history for one keyword (or all of a website's keywords), with day-over-day change highlighted. <a href="javascript:void(0);" data-toggle="modal" data-target="#wmKwReportsHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="wmKwReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="wmKwReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-table"></i></div>
				<h5 class="modal-title" id="wmKwReportsHelpModalLabel">About Keyword Search Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A day-by-day table of clicks/impressions/CTR/average position for one keyword (or all keywords of a website) over a date range, from Google Search Console.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads already-saved Search Console data, synced daily - it doesn't call the Google API live.</span>
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
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'webmaster-tools.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
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
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area">
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('webmaster-tools.php', 'search_form', 'content', '&sec=viewKeywordSearchReports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php
if(empty($keywordId)){
	?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>!
	</div>
	<?php
	exit;
}
?>
<br>
<div id='subcontent'>
<table id="cust_tab">	
	<tr>
		<th><?php echo $spText['common']['Date']?></th>
		<th><?php echo $spText['label']['Clicks']?></th>
		<th><?php echo $spText['label']['Impressions']?></th>
		<th><?php echo "CTR"?></th>
		<th><?php echo $spTextWB['Average Position']?></th>
	</tr>
	<?php
	$colCount = 5;
	if (count($list) > 0) {
    	foreach($list as $listInfo) {            
    		?>
    		<tr>
    			<td><?php echo $listInfo['report_date']; ?></td>
    			<td><b><?php echo $listInfo['clicks'].'</b> '. $listInfo['rank_diff_clicks']?></td>
    			<td><b><?php echo $listInfo['impressions'].'</b> '. $listInfo['rank_diff_impressions']?></td>
    			<td><b><?php echo $listInfo['ctr'].'</b> '. $listInfo['rank_diff_ctr']?></td>
    			<td><b><?php echo $listInfo['average_position'].'</b> '. $listInfo['rank_diff_average_position']?></td>
    		</tr>
    		<?php	
    	}
	} else {
	    echo showNoRecordsList($colCount - 2);
	}
	?>
</table>
</div>