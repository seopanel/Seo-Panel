<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextKeyword['Import Keywords']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#kwImportHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="kwImportHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('kwImportHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-file-upload"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Import Keywords</div>
		<div class="aiv-intro-desc">A bulk version of New Keyword - paste a comma-separated list of keyword phrases and add them all at once. <a href="javascript:void(0);" data-toggle="modal" data-target="#kwImportHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="kwImportHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwImportHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-file-upload"></i></div>
				<h5 class="modal-title" id="kwImportHelpModalLabel">About Import Keywords</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A bulk version of New Keyword - paste a comma-separated list of keyword phrases and they're all added at once, sharing one website, language, country and set of search engines.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>If any keyword in the list already exists for that website, the whole import is rejected - nothing is partially imported. Still subject to your plan's keyword count limit.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id="newKeyword">
<input type="hidden" name="sec" value="import"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextKeyword['Import Keywords']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Website']?>:</td>
		<td class="td_right_col">
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'websites.php', 'weburlarea', 'sec=updateurl')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $post['website_id']){
					    $websiteUrl = $websiteInfo['url'];
					    ?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
			<span id="weburlarea"><input type="hidden" name="weburl" id="weburl" value="<?php echo $websiteUrl?>"></span>
			<?php echo $errMsg['website_id']?>
			<div class="float-right mt-2">
				<a href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta&keyinput=keywords', 'crawlstats')" class="btn btn-info btn-sm">&#171&#171 <?php echo $spText['common']['Crawl Meta Data']?></a>
				<span id="crawlstats" class="ml-2"></span>
			</div>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['lang']?>:</td>
		<td class="td_right_col">
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Country']?>:</td>
		<td class="td_right_col">
			<?php echo $this->render('country/countryselectbox', 'ajax'); ?>
		</td>
	</tr>
	<?php $post['searchengines'] = is_array($post['searchengines']) ? $post['searchengines'] : array(); ?>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Search Engine']?>:</td>
		<td class="td_right_col">
			<select name="searchengines[]" class="multi" multiple="multiple" id="searchengines">
				<?php foreach($seList as $seInfo){?>
					<?php $selected = in_array($seInfo['id'], $post['searchengines']) ? "selected" : ""?>
					<option value="<?php echo $seInfo['id']?>" <?php echo $selected?>><?php echo $seInfo['domain']?></option>
				<?php }?>
			</select>
			<?php echo $errMsg['searchengines']?>
			<br>
			<input type="checkbox" id="select_all" onclick="selectAllOptions('searchengines', true); $('clear_all').checked=false;"> <?php echo $spText['label']['Select All']?>
			&nbsp;&nbsp;
			<input type="checkbox" id="clear_all" onclick="selectAllOptions('searchengines', false); $('select_all').checked=false;"> <?php echo $spText['label']['Clear All']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Keywords']?>:</td>
		<td class="td_right_col">
			<textarea name="keywords" rows="10" id="webkeywords" class="form-control"><?php echo $post['keywords']?></textarea>
			<p><?php echo $errMsg['keywords']?></p>
			<p style="font-size: 12px;"><?php echo $spTextKeyword['Insert keywords separated with comma']?></p>
			<P><b>Eg:</b> google seo tools,seo tools,seo</P>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('keywords.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('keywords.php', 'newKeyword', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
