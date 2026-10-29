<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextKeyword['Graphical Keyword Position Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#kwGraphHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="kwGraphHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('kwGraphHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-chart-area"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Graphical Position Reports</div>
		<div class="aiv-intro-desc">The same keyword rank history as Detailed Position Reports, drawn as a line chart instead of a table. <a href="javascript:void(0);" data-toggle="modal" data-target="#kwGraphHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="kwGraphHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwGraphHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-chart-area"></i></div>
				<h5 class="modal-title" id="kwGraphHelpModalLabel">About Graphical Position Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>The same keyword rank history as Detailed Position Reports, drawn as a line chart (one line per search engine) instead of a table - easier to spot a trend at a glance.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Needs at least one day of crawled rank data to draw a real chart - otherwise it shows "No Records Found" instead.</span>
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
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area">
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			  $( function() {
			    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
			  } );
		  	</script>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php
				echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('graphical-reports.php', 'search_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
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

<div id='subcontent'>
	<?php echo $graphContent; ?>
</div>
