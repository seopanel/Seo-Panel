<form id="editSubmitInfo">
<input type="hidden" name="sec" value="updatesiteinfo"/>
<input type="hidden" name="website_id" value="<?php echo $websiteInfo['website_id']?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" style="width: 30%"><?php echo $spTextDir['Submission Details']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextDir['Owner Name']?>:</td>
		<td class="td_right_col">
			<input type="text" name="owner_name" value="<?php echo stripslashes($websiteInfo['owner_name'])?>" style="width:300px;"><?php echo $errMsg['owner_name']?>
		</td>
	</tr>	
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextDir['Owner Email']?>:</td>
		<td class="td_right_col">
			<input type="text" name="owner_email" value="<?php echo $websiteInfo['owner_email']?>" style="width:300px;"><?php echo $errMsg['owner_email']?>			
			<p><?php echo $spTextDir['spamemailnote']?></p>
		</td>
	</tr>		
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextDir['Website Category']?>:</td>
		<td class="td_right_col">
			<input type="text" name="category" value="<?php echo stripslashes($websiteInfo['category'])?>" style="width:300px;"><?php echo $errMsg['category']?>
			<p><?php echo $spTextDir['categorynote']?></p>
			<p>Eg: google seo tools, seo tools, seo</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextDir['Website Url']?>:</td>
		<td class="td_right_col">
			<input type="text" id="weburl" name="url" value="<?php echo $websiteInfo['url']?>" style="width:300px;">
			<a style="text-decoration: none;" href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta', 'crawlstats')">&#171&#171 <?php echo $spText['common']['Crawl Meta Data']?></a>
			<div id="crawlstats" style="float: right;padding-right:40px;"></div>
			<br><?php echo $errMsg['url']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextDir['Submit Title']?>1:</td>
		<td class="td_right_col"><input type="text" id="webtitle" name="title" value="<?php echo stripslashes($websiteInfo['title'])?>" style="width:400px;"><?php echo $errMsg['title']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextDir['Submit Description']?>1:</td>
		<td class="td_right_col">
			<textarea name="description" id="webdescription"><?php echo stripslashes($websiteInfo['description'])?></textarea><?php echo $errMsg['description']?>
			<p><?php echo $spTextDir['desnote']?></p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextDir['Submit Keywords']?>:</td>
		<td class="td_right_col"><textarea name="keywords" id="webkeywords"><?php echo stripslashes($websiteInfo['keywords'])?></textarea><?php echo $errMsg['keywords']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextDir['Reciprocal Link']?>:</td>
		<td class="td_right_col"><input type="text" name="reciprocal_url" value="<?php echo stripslashes($websiteInfo['reciprocal_url'])?>" style="width:400px;"></td>
	</tr>	
	<tr class="white_row" style="border-right: none;">
		<td class="td_left_col">&nbsp;</td>
		<td class="td_right_col"><b><?php echo $spTextDir['optionalnote']?></b></td>
	</tr>
	<?php for ($i=2;$i<=$noTitles;$i++) {
    ?>	
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spTextDir['Submit Title']?><?php echo $i?>:</td>
			<td class="td_right_col"><input type="text" name="title<?php echo $i?>" value="<?php echo stripslashes($websiteInfo['title'.$i])?>" style="width:400px;"></td>
		</tr>
		<tr class="white_row">
			<td class="td_left_col"><?php echo $spTextDir['Submit Description']?><?php echo $i?>:</td>
			<td class="td_right_col">
				<textarea name="description<?php echo $i?>"><?php echo stripslashes($websiteInfo['description'.$i])?></textarea>
			</td>
		</tr>
	<?php 
}?>		
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
    		<a onclick="scriptDoLoad('directories.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<a onclick="scriptDoLoadPost('directories.php', 'editSubmitInfo', 'subcontent')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>