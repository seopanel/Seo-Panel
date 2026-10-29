<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$submitAction = "scriptDoLoadPost('backlinks.php', 'search_form', 'content')";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Graphical Position Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#blGraphHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="blGraphHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('blGraphHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-chart-area"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Graphical Reports</div>
		<div class="aiv-intro-desc">The same saved backlink history as Backlinks Reports, drawn as a line chart over time. <a href="javascript:void(0);" data-toggle="modal" data-target="#blGraphHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="blGraphHelpModal" tabindex="-1" role="dialog" aria-labelledby="blGraphHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-chart-area"></i></div>
				<h5 class="modal-title" id="blGraphHelpModalLabel">About Graphical Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Charts backlink count and domain backlink count over time for a single website, instead of showing them as a table.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads from already-saved history only - shows "No Records Found" if there's nothing saved in the selected range.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<input type="hidden" name="sec" value="graphical-reports">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="<?php echo $submitAction;?>">
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
			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
            $( function() {
            	$( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
            } );
		  	</script>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction;?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent' class="col-md-12">
	<?php echo $graphContent; ?>
</div>