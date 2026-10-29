<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextKeyword['Quick Keyword Position Checker']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#kwQuickHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="kwQuickHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('kwQuickHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-bolt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Quick Position Checker</div>
		<div class="aiv-intro-desc">Checks where your site currently ranks for a keyword, live, right now - without saving it as a tracked keyword. <a href="javascript:void(0);" data-toggle="modal" data-target="#kwQuickHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="kwQuickHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwQuickHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-bolt"></i></div>
				<h5 class="modal-title" id="kwQuickHelpModalLabel">About Quick Position Checker</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Type a keyword and pick a search engine/language/country, and this crawls that search engine live right now to show where your website currently ranks - without saving it as a tracked keyword first.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This is a real-time scrape, so it's slower than the saved reports and only available if Quick Checker is enabled for your account. Nothing here gets added to Keywords Manager.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php echo $this->render('searchengine/seselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['lang']?>: </th>
		<td>
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Country']?>: </th>
		<td>
			<?php echo $this->render('country/countryselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<input type="text" value="" name="url" class="form-control"/>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td>
			<input type="text" value="" name="name" class="form-control"/>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextKeyword['Show All results']?>: </th>
		<td>
			<div class="form-check">
				<input type="checkbox" value="1" name="show_all" class="form-check-input" id="show_all"/>
				<label class="form-check-label" for="show_all"></label>
			</div>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('reports.php', 'search_form', 'subcontent', '&sec=kwchecker')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>
