<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Report Generation Manager']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#reportGenMgrHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="reportGenMgrHelpModal" tabindex="-1" role="dialog" aria-labelledby="reportGenMgrHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-cogs"></i></div>
				<h5 class="modal-title" id="reportGenMgrHelpModalLabel">About Report Generation Manager</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick a website and trigger report generation for every tool that supports it (Rank, Backlinks, PageSpeed, Saturation, and more) right now, instead of waiting for the next scheduled cron run.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Runs against the same scheduler lock the CLI cron uses, so it won't double-run against an already-in-progress cron pass for the same website.</span>
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
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('cron.php', 'search_form', 'subcontent', '&sec=generate')"; ?>
		<td><a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a></td>
	</tr>
	<tr>
		<th nowrap="nowrap"><?php echo $spText['common']['Seo Tools']?>: </th>
		<td colspan="2" style="font-size: 12px;">
			<?php foreach($repTools as $i => $repInfo){ ?>
				<div class="form-check my-3">
					<input type="checkbox" name="repTools[]" value="<?php echo $repInfo['id']?>" class="form-check-input" id="repTool_<?php echo $repInfo['id']?>">
					<label class="form-check-label" for="repTool_<?php echo $repInfo['id']?>">
						<?php echo $spTextTools[$repInfo['url_section']]?>
					</label>
				</div>
			<?php }?>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
	<p class='note'><?php echo $spTextTools['note_report_generation']?></p>
</div>