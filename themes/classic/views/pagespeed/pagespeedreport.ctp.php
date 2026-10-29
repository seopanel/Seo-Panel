<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$webUrl = "";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['PageSpeed Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#psReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="psReportsHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('psReportsHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-tachometer-alt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">PageSpeed Reports</div>
		<div class="aiv-intro-desc">History of a website's saved desktop/mobile PageSpeed scores, with trend-change indicators. <a href="javascript:void(0);" data-toggle="modal" data-target="#psReportsHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="psReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="psReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-tachometer-alt"></i></div>
				<h5 class="modal-title" id="psReportsHelpModalLabel">About PageSpeed Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Shows a date-range history of a website's previously saved desktop and mobile PageSpeed scores, with trend-change indicators and a details panel.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads only already-saved scores - no new API call happens here. Needs data saved by Generate Reports or the scheduled cron first.</span>
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
			<select name="website_id" id="website_id" class="custom-select" onchange="scriptDoLoadPost('pagespeed.php', 'search_form', 'content', '&sec=reports')">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php
					if($websiteInfo['id'] == $websiteId){
						$webUrl = $websiteInfo['url'];
						?>
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
		<td style="text-align: center;"><a href="javascript:void(0);" onclick="scriptDoLoadPost('pagespeed.php', 'search_form', 'content', '&sec=reports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a></td>
	</tr>
</table>
</form>

<?php
if(empty($websiteId)){
	?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Records Found']?>!
	</div>
	<?php
	exit;
}
?>

<div id='subcontent'>
<table width="100%" class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Date']?></td>
		<td><?php echo $spTextPS['Desktop Speed']?></td>
		<td><?php echo $spTextPS['Mobile Speed']?></td>
		<td><?php echo $spText['common']['Details']?></td>
	</tr>
	<?php
	$colCount = 4; 
	if(count($list) > 0){
		foreach($list as $listInfo){ 
			?>
			<tr>
				<td><?php echo $listInfo['result_date']; ?></td>
				<td><a><?php echo $listInfo['desktop_speed_score'].'</a> '. $listInfo['rank_diff_desktop_speed_score']?></td>
				<td><a><?php echo $listInfo['mobile_speed_score'].'</a> '. $listInfo['rank_diff_mobile_speed_score']?></td>
				<td>
					<a href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode($webUrl); ?>" target="_blank">
						<?php echo $spText['common']['Details']?> &gt;&gt;
					</a>
				</td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
</div>