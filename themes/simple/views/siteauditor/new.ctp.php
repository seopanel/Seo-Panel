<?php echo showSectionHead($spTextPanel['New Project']); ?>
<form id="projectform">
<input type="hidden" name="sec" value="create"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New Project']?></td>
		<td class="right">&nbsp;</td>
	</tr>		
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Website']?>:*</td>
		<td class="td_right_col">
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox')">
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
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextSA['Maximum number of pages to be checked']?>:*</td>
		<td class="td_right_col">
			<input type="text" name="max_links" value="<?php echo $post['max_links']?>"><?php echo $errMsg['max_links']?>
			<p><b><?php echo $spTextSettings['SA_MAX_NO_PAGES']?>:</b> <?php echo SA_MAX_NO_PAGES?></p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextSA['Exclude links']?>:</td>
		<td class="td_right_col">
			<textarea name="exclude_links"><?php echo $post['exclude_links']?></textarea>
			<br><?php echo $errMsg['exclude_links']?>
			<p><?php echo $spTextSA['insertlinkssepcoma']?>.</p>
			<p><b>Note:</b> <?php echo $spTextSA['anylinkcontainabovelinks']?>.</p>
			<p><b>Eg:</b> /plugin/l/, &lang_code=</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextSA['Check pagerank of pages']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['check_pr'] == 1) ? "selected" : ""; ?>
			<select name="check_pr">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextSA['Check backlinks of pages']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['check_backlinks'] == 1) ? "selected" : ""; ?>
			<select name="check_backlinks">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextSA['Check pages indexed or not']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['check_indexed'] == 1) ? "selected" : ""; ?>
			<select name="check_indexed">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextSA['Store all links found in a page']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['store_links_in_page'] == 1) ? "selected" : ""; ?>
			<select name="store_links_in_page">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextSA['Check broken links in a page']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['check_brocken'] == 1) ? "selected" : ""; ?>
			<select name="check_brocken">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
			<p><b>Note:</b> <?php echo $spTextSA['checkborckenlinkwait']?>.</p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextSA['Execute with cron']?>:*</td>
		<td class="td_right_col">
			<?php $selected = ($post['cron'] == 1) ? "selected" : ""; ?>
			<select name="cron">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>		
	<tr class="blue_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('siteauditor.php', 'projectform', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>