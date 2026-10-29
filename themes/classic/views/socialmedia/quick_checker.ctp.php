<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('$pageScriptPath', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Quick Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#smQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="smQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('smQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Social Media Quick Checker</div>
		<div class="aiv-intro-desc">Paste a profile URL and platform to get its current like/follower count right now, with nothing saved. <a href="javascript:void(0);" data-toggle="modal" data-target="#smQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="smQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="smQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="smQuickHelpModalLabel">About Social Media Quick Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Paste a profile URL and pick its platform to scrape that public page right now for its current like/follower count. Nothing is saved - it's an instant preview, not part of the scheduled reporting.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Relies on reading the platform's public page HTML (no official API), so results can break if that platform changes its page layout. LinkedIn needs a numeric company ID instead of a URL.</span>
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
			<select name="type" id="sm_type" class="custom-select">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th id="sm_url_label"><?php echo $spText['common']['Link']?>: </th>
		<td>
			<input type="text" value="" name="url" class="form-control" required="required"/>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "facebook";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo $serviceList[$serviceSelName]['example']?></span></p>
				<?php
			}?>
			<div style="padding: 10px 6px; display: none;" id="sm_url_note">
				<a target="_blank" href="<?php echo SP_MAIN_SITE?>/blog/2020/07/how-do-i-find-the-linkedin-company-id/">
					<?php echo $spTextSMC['Click here to get LinkedIn Company Id']; ?> &gt;&gt;
				</a>
			</div>
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
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example']?>";
<?php }?>

$(function() {	
	$('#sm_type').change(function() {
		smType = $(this).val();
		$('#ex_review_link').html(typeList[smType]);
		
		if (smType == 'linkedin') {
			$('#sm_url_label').html("<?php echo $spTextSMC['Company Id']?>:");
			$('#sm_url_note').show();
		} else {
			$('#sm_url_label').html("<?php echo $spText['common']['Link']?>:");
			$('#sm_url_note').hide();
		}		
	});
	
});
</script>