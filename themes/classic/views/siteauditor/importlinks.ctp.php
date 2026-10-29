<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextSA['Import Project Links']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#saImportHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="saImportHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('saImportHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-file-upload"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Import Project Links</div>
		<div class="aiv-intro-desc">Manually add extra page URLs to an existing project's crawl queue, instead of waiting for the crawler to discover them. <a href="javascript:void(0);" data-toggle="modal" data-target="#saImportHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="saImportHelpModal" tabindex="-1" role="dialog" aria-labelledby="saImportHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-file-upload"></i></div>
				<h5 class="modal-title" id="saImportHelpModalLabel">About Import Project Links</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Paste a comma-separated list of page URLs to queue them into an existing project's crawl, rather than relying on the crawler to find them by following links or a sitemap.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Each URL must belong to the project's own website domain and not already be queued or excluded. The whole batch is rejected if it would exceed the project's max-pages limit.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id="importlinks">
<input type="hidden" name="sec" value="importlinks"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextSA['Import Project Links']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr>
		<td><?php echo $spText['label']['Project']?>: </td>
		<td>
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" class="custom-select" style="width: 180px;">
				<?php foreach($projectList as $list) {?>
					<?php if($list['id'] == $projectId) {?>
						<option value="<?php echo $list['id']?>" selected="selected"><?php echo htmlspecialchars($list['name'])?></option>
					<?php } else {?>
						<option value="<?php echo $list['id']?>"><?php echo htmlspecialchars($list['name'])?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Links:</td>
		<td>
			<textarea name="links" rows="10" class="form-control"><?php echo $post['links']?></textarea>
			<br><?php echo $errMsg['links']?>
			<p style="font-size: 12px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<P><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</P>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('siteauditor.php', 'importlinks', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>