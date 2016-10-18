<?php if(!empty($msg)){
	$msgClass = empty($error) ? "success" : "error"; 
	?>
		<p class="dirmsg">
			<font class="<?php echo $msgClass?>"><?php echo $msg?></font>
		</p>
	<?php 
	}
?>
<form id="submissionForm" name="submissionForm">
<input type="hidden" name="sec" value="submitsite"/>
<input type="hidden" name="website_id" value="<?php echo $websiteId?>"/>
<input type="hidden" name="dir_id" value="<?php echo $dirInfo['id']?>"/>
<input type="hidden" name="add_params" value="<?php echo $addParams?>">

<?php if(!empty($phpsessid)){?>
    <input type="hidden" name="phpsessid" value="<?php echo $phpsessid?>">
<?php }?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" style="width: 30%"><?php echo $spTextTools['directory-submission']?></td>
		<td class="right">&nbsp;</td>
	</tr>		
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Directory']?>:</td>
		<td class="td_right_col"><a href="<?php echo $dirInfo['submit_url']?>" target="_blank"><?php echo $dirInfo['domain']?></a></td>
	</tr>		
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Pagerank']?>:</td>
		<td class="td_right_col"><?php echo $dirInfo['pagerank']?></td>
	</tr>		
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Category']?>:</td>
		<td class="td_right_col"><?php echo $categorySel?></td>
	</tr>
	<?php if (!empty($reciprocalDir)) { ?>
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spTextDir['Reciprocal Link']?>:</td>
			<td class="td_right_col">
				<input type="text" name="reciprocal_url" value="<?php echo $reciprocalUrl?>" style="width: 300px;">
			</td>
		</tr>
	<?php } ?>	
	<?php if(!empty($captchaUrl)){ ?>
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spTextDir['Enter the code shown']?>:</td>
			<td class="td_right_col">
				<?php if(!empty($imageHash)){?>
					<input type="hidden" name="<?php echo $dirInfo['imagehash_col']?>" value="<?php echo $imageHash?>">
				<?php }?>
				<input type="text" name="<?php echo $dirInfo['cptcha_col']?>" value="" id='captcha'>
				<p><img src='<?php echo $captchaUrl?>'></p>
			</td>
		</tr>
	<?php } ?>		
	<tr class="white_row">
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
         	<a onclick="scriptDoLoad('directories.php?sec=skip&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Skip']?>
         	</a>&nbsp;
         	<a onclick="scriptDoLoad('directories.php?sec=reload&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Reload']?>
         	</a>&nbsp;
         	<a onclick="checkSubmitInfo('directories.php', 'submissionForm', 'subcontent', '<?php echo $dirInfo['category_col']?>')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Submit']?>
         	</a>
    	</td>
	</tr>
</table>
</form>