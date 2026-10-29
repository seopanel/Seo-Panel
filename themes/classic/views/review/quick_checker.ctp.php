<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('$pageScriptPath', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#rvQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="rvQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('rvQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Review Quick Checker</div>
		<div class="aiv-intro-desc">Checks a review page's current review count and star rating right now, with nothing saved. <a href="javascript:void(0);" data-toggle="modal" data-target="#rvQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="rvQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="rvQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="rvQuickHelpModalLabel">About Review Quick Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>One-off check of a review page's current review count and star rating, without saving anything. For Google, Trustpilot and TripAdvisor this uses the DataForSEO API; Yelp falls back to scraping its page directly.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>DataForSEO-backed platforms need DataForSEO configured in Settings. Nothing here is saved to Review Reports.</span>
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
		<th width="30%"><?php echo $spText['label']['Type']?>: </th>
		<td>
			<select name="type" id="review_link_type" class="custom-select">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Link']?>: </th>
		<td>
			<input type="url" value="" name="url" class="form-control" required="required"/>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "google";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo implode(', ', $serviceList[$serviceSelName]['example'])?></span></p>
				<?php
			}?>
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

<div id='subcontent'></div>

<script type="text/javascript">
var typeList = [];
<?php foreach ($serviceList as $serviceName => $serviceInfo) {?>
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example'][0]?>";
<?php }?>

(function () {
	$('#review_link_type').change(function() {
		service = $(this).val();
		$('#ex_review_link').html(typeList[service]);
	});
})();
</script>