<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Detailed Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#smDetailHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="smDetailHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('smDetailHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-table"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Detailed Reports</div>
		<div class="aiv-intro-desc">Day-by-day followers/likes history for one link, with the change from the previous day highlighted. <a href="javascript:void(0);" data-toggle="modal" data-target="#smDetailHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="smDetailHelpModal" tabindex="-1" role="dialog" aria-labelledby="smDetailHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-table"></i></div>
				<h5 class="modal-title" id="smDetailHelpModalLabel">About Detailed Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>For one chosen link, a day-by-day history table of followers/likes over a date range, with each day's change from the previous one highlighted.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads from already-saved history from the daily scheduled scrape - it doesn't check the platform live. Includes an optional AI summary of the trend.</span>
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
			<select name="website_id" id="website_id" onchange="doLoad('website_id', '<?php echo $pageScriptPath ?>', 'link_area', 'sec=linkSelectBox')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
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
		<th class="pl-4"><?php echo $spText['common']['Url']?>: </th>
		<td id="link_area">
			<?php echo $this->render('socialmedia/social_media_link_select_box', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo $pageScriptPath ?>', 'search_form', 'content', '&sec=viewDetailedReports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>


<div id='subcontent'>
<table id="cust_tab">	
	<tr>
		<th><?php echo $spText['common']['Date']?></th>
		<th><?php echo $spText['label']['Followers']?></th>
		<th><?php echo $spText['label']['Likes']?></th>
	</tr>
	<?php
	if (count($list) > 0) {
    	foreach($list as $listInfo){            
    		?>
    		<tr>
    			<td><?php echo $listInfo['report_date']; ?></td>
    			<td><b><?php echo $listInfo['followers'].'</b> '. $listInfo['rank_diff_followers']?></td>
    			<td><b><?php echo $listInfo['likes'].'</b> '. $listInfo['rank_diff_likes']?></td>
    		</tr>
    		<?php	
    	}
	} else {
	    ?>
	    <tr><td colspan="3"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
	    <?php
	}
	?>
</table>

<?php if (!empty($localAiAvailable) && count($list) > 0) { ?>
	<div class="mt-2">
		<button type="button" class="btn btn-outline-secondary btn-sm" onclick="socialMediaSummarizeTrend()">
			<i class="fa fa-magic"></i> Summarize with AI
		</button>
		<div id="socialMediaTrendSummary" class="alert alert-info mt-2" style="display:none;"></div>
	</div>
	<script type="text/javascript">
	function socialMediaSummarizeTrend() {
		var box = document.getElementById('socialMediaTrendSummary');
		box.style.display = 'block';
		box.innerText = 'Generating...';
		$.ajax({
			url: '<?php echo $pageScriptPath ?>',
			data: {
				sec: 'summarizetrend',
				link_id: <?php echo intval($linkId)?>,
				from_time: <?php echo json_encode($fromTime)?>,
				to_time: <?php echo json_encode($toTime)?>
			},
			dataType: 'json',
			success: function(data) {
				box.innerText = (data && data.ok) ? data.summary : ((data && data.error) ? data.error : 'Could not generate a summary.');
			},
			error: function() {
				box.innerText = 'Could not generate a summary.';
			}
		});
	}
	</script>
<?php } ?>
</div>