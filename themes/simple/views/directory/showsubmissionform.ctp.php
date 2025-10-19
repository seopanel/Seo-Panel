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

<table class="list">
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
		<td class="td_left_col"><?php echo $spText['common']['Domain Authority']?>:</td>
		<td class="td_right_col"><?php echo $dirInfo['domain_authority']?></td>
	</tr>		
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Page Authority']?>:</td>
		<td class="td_right_col"><?php echo $dirInfo['page_authority']?></td>
	</tr>		
	<tr class="white_row" id="category_col">
		<td class="td_left_col"><?php echo $spText['common']['Category']?>:</td>
		<td class="td_right_col"><?php echo $categorySel?></td>
	</tr>
	<?php if (!empty($reciprocalDir)) { ?>
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spTextDir['Reciprocal Link']?>:</td>
			<td class="td_right_col">
				<input type="text" name="reciprocal_url" value="<?php echo $reciprocalUrl?>" class="form-control">
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
				<?php
				$captchaCodeError = "";
				if ( stristr($captchaCode, 'Error:')) {
				    $captchaCodeError = formatErrorMsg($captchaCode);
				    $captchaCode = "";
				}
				?>
				<input type="text" name="<?php echo $dirInfo['cptcha_col']?>" value="<?php echo $captchaCode;?>" id='captcha' class="form-control">
				<p><img src='<?php echo $captchaUrl?>'></p>
				<?php echo $captchaCodeError?>
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
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('directories.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<a onclick="scriptDoLoad('directories.php?sec=skip&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Skip']?>
         	</a>&nbsp;
         	<a onclick="scriptDoLoad('directories.php?sec=reload&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="btn btn-info">
         		<?php echo $spText['button']['Reload']?>
         	</a>&nbsp;
         	<a onclick="checkSubmitInfo('directories.php', 'submissionForm', 'subcontent', '<?php echo $dirInfo['category_col']?>')" href="javascript:void(0);" class="btn btn-primary" id="dir_submit_but">
         		<?php echo $spText['button']['Submit']?>
         	</a>
    	</td>
	</tr>
</table>
</form>

<script>
jQuery.expr[':'].icontains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

var catSelectStr = "<?php echo $catSelectStr?>";
var catList = catSelectStr.split(",");
var found = 0;
for (var i = 0; i < catList.length; i++) {
	$('#category_col option:icontains(' + catList[i].trim() + ')').each(function() {
		$(this).attr('selected', 'selected');
		found = 1;
		return true;
	});

	if (found) {
		break;
	}
	
}

<?php if (defined("CB_ENABLE_DIR_AUTO_SUBMISSION") && CB_ENABLE_DIR_AUTO_SUBMISSION) {?>
    setTimeout(function() {
        $('#dir_submit_but').trigger('click');
    }, <?php echo CB_DIR_AUTO_SUBMISSION_INTERVAL * 1000?>);
<?php }?>
</script>
