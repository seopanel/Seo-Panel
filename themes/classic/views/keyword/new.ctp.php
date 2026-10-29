<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextKeyword['New Keyword']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#kwNewHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="kwNewHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('kwNewHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-plus-circle"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">New Keyword</div>
		<div class="aiv-intro-desc">Add a single keyword to track - choose its website, language, country and which search engines to monitor it on. <a href="javascript:void(0);" data-toggle="modal" data-target="#kwNewHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="kwNewHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwNewHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-plus-circle"></i></div>
				<h5 class="modal-title" id="kwNewHelpModalLabel">About New Keyword</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Add a single new keyword to track - pick the website, language, country, and which search engines to monitor it on.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>A keyword can't be added twice for the same website, and you're limited by your plan's keyword and search-engine count.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id="newKeyword">
<input type="hidden" name="sec" value="create"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextKeyword['New Keyword']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="keywordname"><?php echo $spText['common']['Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="keywordname" name="name" value="<?php echo $post['name']?>" class="form-control">
			<?php echo $errMsg['name']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="keyword_website_id"><?php echo $spText['common']['Website']?>:</label></td>
		<td class="td_right_col">
			<select name="website_id" id="keyword_website_id" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $post['website_id']){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
			<?php echo $errMsg['website_id']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="lang_code"><?php echo $spText['common']['lang']?>:</label></td>
		<td class="td_right_col">
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="country_code"><?php echo $spText['common']['Country']?>:</label></td>
		<td class="td_right_col">
			<?php echo $this->render('country/countryselectbox', 'ajax'); ?>
		</td>
	</tr>
	<?php $post['searchengines'] = is_array($post['searchengines']) ? $post['searchengines'] : array(); ?>
	<tr class="white_row">
		<td class="td_left_col"><label for="searchengines"><?php echo $spText['common']['Search Engine']?>:</label></td>
		<td class="td_right_col">
			<select name="searchengines[]" class="multi" multiple="multiple" id="searchengines">
				<?php foreach($seList as $seInfo){?>
					<?php $selected = in_array($seInfo['id'], $post['searchengines']) ? "selected" : ""?>
					<option value="<?php echo $seInfo['id']?>" <?php echo $selected?>><?php echo $seInfo['domain']?></option>
				<?php }?>
			</select> <?php echo $errMsg['searchengines']?>
			<br>
			<input type="checkbox" id="select_all" onclick="selectAllOptions('searchengines', true); $('clear_all').checked=false;"> <label for="select_all"><?php echo $spText['label']['Select All']?></label>
			&nbsp;&nbsp;
			<input type="checkbox" id="clear_all" onclick="selectAllOptions('searchengines', false); $('select_all').checked=false;"> <label for="clear_all"><?php echo $spText['label']['Clear All']?></label>
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
