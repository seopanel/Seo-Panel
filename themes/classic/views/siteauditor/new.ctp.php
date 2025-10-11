<?php echo showSectionHead($spTextPanel['New Project']); ?>
<form id="projectform">
<input type="hidden" name="sec" value="create"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New Project']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr>
		<td><?php echo $spText['common']['Website']?>:*</td>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
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
	<tr>
		<td><?php echo $spTextSA['Maximum number of pages to be checked']?>:*</td>
		<td>
			<input type="text" name="max_links" value="<?php echo $post['max_links']?>" class="form-control"><?php echo $errMsg['max_links']?>
			<p><b><?php echo $spTextSettings['SA_MAX_NO_PAGES']?>:</b> <?php echo SA_MAX_NO_PAGES?></p>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Exclude links']?>:</td>
		<td>
			<textarea name="exclude_links" class="form-control"><?php echo $post['exclude_links']?></textarea>
			<br><?php echo $errMsg['exclude_links']?>
			<p><?php echo $spTextSA['insertlinkssepcoma']?>.</p>
			<p><b>Note:</b> <?php echo $spTextSA['anylinkcontainabovelinks']?>.</p>
			<p><b>Eg:</b> /plugin/l/, &lang_code=</p>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Check pagerank of pages']?>:*</td>
		<td>
			<?php $selected = ($post['check_pr'] == 1) ? "selected" : ""; ?>
			<select name="check_pr" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Check backlinks of pages']?>:*</td>
		<td>
			<?php $selected = ($post['check_backlinks'] == 1) ? "selected" : ""; ?>
			<select name="check_backlinks" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Check pages indexed or not']?>:*</td>
		<td>
			<?php $selected = ($post['check_indexed'] == 1) ? "selected" : ""; ?>
			<select name="check_indexed" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Store all links found in a page']?>:*</td>
		<td>
			<?php $selected = ($post['store_links_in_page'] == 1) ? "selected" : ""; ?>
			<select name="store_links_in_page" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Check broken links in a page']?>:*</td>
		<td>
			<?php $selected = ($post['check_brocken'] == 1) ? "selected" : ""; ?>
			<select name="check_brocken" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
			<p><b>Note:</b> <?php echo $spTextSA['checkborckenlinkwait']?>.</p>
		</td>
	</tr>
	<tr>
		<td><?php echo $spTextSA['Execute with cron']?>:*</td>
		<td>
			<?php $selected = ($post['cron'] == 1) ? "selected" : ""; ?>
			<select name="cron" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('siteauditor.php', 'projectform', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>