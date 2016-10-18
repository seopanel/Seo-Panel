<?php echo showSectionHead($spTextKeyword['New Keyword']); ?>
<?php 
if(!empty($validationMsg)){
	?>
	<p class="dirmsg">
		<font class="error"><?php echo $validationMsg?></font>
	</p>
	<?php 
}
?>
<form id="newKeyword">
<input type="hidden" name="sec" value="create"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextKeyword['New Keyword']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Name']?>:</td>
		<td class="td_right_col"><input type="text" name="name" value="<?php echo $post['name']?>" class="long"><?php echo $errMsg['name']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Website']?>:</td>
		<td class="td_right_col">
			<select name="website_id">
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
			</select> <?php echo $errMsg['searchengines']?>
			<br>
			<input type="checkbox" id="select_all" onclick="selectAllOptions('searchengines', true); $('clear_all').checked=false;"> <?php echo $spText['label']['Select All']?>
			&nbsp;&nbsp;
			<input type="checkbox" id="clear_all" onclick="selectAllOptions('searchengines', false); $('select_all').checked=false;"> <?php echo $spText['label']['Clear All']?>
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
    		<a onclick="scriptDoLoad('keywords.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('keywords.php', 'newKeyword', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>