<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['sitemap-generator']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#smGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="smGenHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('smGenHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-sitemap"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Google Sitemap Generator</div>
		<div class="aiv-intro-desc">Generate a sitemap file from a Site Auditor project's already-crawled pages. <a href="javascript:void(0);" data-toggle="modal" data-target="#smGenHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="smGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="smGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sitemap"></i></div>
				<h5 class="modal-title" id="smGenHelpModalLabel">About Google Sitemap Generator</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick one of your Site Auditor projects and generate a sitemap file (XML, TXT or HTML) from the pages it already discovered - with options to exclude URLs, set change-frequency/priority, gzip-compress, and generate a sitemap index.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Needs a project with at least one crawled link first. Files are written to disk on this server, not uploaded anywhere.</span>
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
		<th width="30%"><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" class="custom-select">
				<?php foreach($projectList as $list) {?>
					<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextSitemap['Sitemap Type']?>: </th>
		<td>
			<select name="sm_type" id="sm_type" class="custom-select">
				<option value="xml">XML</option>
				<option value="txt">Text</option>
				<option value="html">HTML</option>
			</select>
		</td>
	</tr>
	<tr id="sitemap_options_row">
		<th><?php echo $spText['common']['Options']?>: </th>
		<td>
			<div class="form-check">
				<input type="checkbox" class="form-check-input" id="compress_sitemap" name="compress_sitemap" value="1">
				<label class="form-check-label" for="compress_sitemap">
					<?php echo $spTextSitemap['Compress sitemap files']?> (.xml.gz)
				</label>
				<p class="text-muted mb-2"><small><?php echo $spTextSitemap['Reduces file size by 70-90%, recommended for large sitemaps']?></small></p>
			</div>
			<div class="form-check">
				<input type="checkbox" class="form-check-input" id="generate_index" name="generate_index" value="1">
				<label class="form-check-label" for="generate_index">
					<?php echo $spTextSitemap['Generate sitemap index file']?>
				</label>
				<p class="text-muted mb-0"><small><?php echo $spTextSitemap['Creates sitemap_index.xml for multiple sitemap files']?></small></p>
			</div>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextSitemap['Change frequency']?>: </th>
		<td>
			<select name="freq" class="custom-select">
				<option value="">None</option>
				<option value="always">Always</option>
				<option value="hourly">Hourly</option>
				<option value="daily">Daily</option>
				<option value="weekly">Weekly</option>
				<option value="monthly">Monthly</option>
				<option value="yearly">Yearly</option>
				<option value="never">Never</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Priority']?>: </th>
		<td>
			<select name="priority" class="custom-select">
				<option value="0.5">0.5</option>
				<option value="1">1</option>
				<option value="auto">Automatic Priority</option>
			</select>
		</td>
	</tr>
	<tr>
		<th style="vertical-align: text-top;"><?php echo $spTextSitemap['Exclude Url']?>: </th>
		<td>
			<textarea name="exclude_url" class="form-control"><?php echo $post['exclude_url']?></textarea>
			<p style="margin-top: 6px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<p><b>Note:</b> <?php echo $spTextSA['anylinkcontainexcludesitemap']?>.</p>
			<p><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</p>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('sitemap.php', 'search_form', 'subcontent')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextSitemap['clickproceedsitemap']?>
	</div>
</div>

<script type="text/javascript">
// Define function in global scope
window.toggleSitemapOptions = function() {
	var smType = document.getElementById('sm_type');
	var optionsRow = document.getElementById('sitemap_options_row');
	var compressCheckbox = document.getElementById('compress_sitemap');
	var indexCheckbox = document.getElementById('generate_index');

	if (!smType || !optionsRow) return; // Safety check

	if (smType.value === 'xml') {
		// Show options for XML sitemaps
		optionsRow.style.display = '';
	} else {
		// Hide options for TXT and HTML sitemaps
		optionsRow.style.display = 'none';
		// Uncheck the options when hidden
		if (compressCheckbox) compressCheckbox.checked = false;
		if (indexCheckbox) indexCheckbox.checked = false;
	}
};

// Initialize and attach event handler
(function() {
	var smType = document.getElementById('sm_type');
	if (smType) {
		// Attach onchange event handler
		smType.onchange = window.toggleSitemapOptions;

		// Initialize on load
		window.toggleSitemapOptions();
	}
})();

// Additional fallback for AJAX-loaded content
setTimeout(function() {
	var smType = document.getElementById('sm_type');
	if (smType && !smType.onchange) {
		smType.onchange = window.toggleSitemapOptions;
		window.toggleSitemapOptions();
	}
}, 100);
</script>