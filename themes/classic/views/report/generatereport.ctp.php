<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Generate Keyword Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#kwGenHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="kwGenHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('kwGenHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-sync"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Generate Keyword Reports</div>
		<div class="aiv-intro-desc">Triggers an on-demand re-crawl of the selected keyword(s) right now, instead of waiting for the next scheduled cron run. <a href="javascript:void(0);" data-toggle="modal" data-target="#kwGenHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="kwGenHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwGenHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sync"></i></div>
				<h5 class="modal-title" id="kwGenHelpModalLabel">About Generate Keyword Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Despite the name, this doesn't produce a PDF - it triggers an on-demand re-crawl of the selected keyword(s) against their search engines right now, instead of waiting for the next scheduled cron run.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Only available if report generation is enabled for your account (admins always have it). Crawling many keywords can take a while, since each check is deliberately throttled.</span>
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
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox&keyNull=0')" class="custom-select">
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
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php
				echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('generate-reports.php', 'search_form', 'subcontent')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
	<div class="alert alert-warning">
		<i class="fas fa-exclamation-triangle me-2"></i><?php echo $spTextTools['note_report_generation']?>
	</div>
</div>
