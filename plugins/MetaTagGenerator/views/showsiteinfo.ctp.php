<form id="editSubmitInfo">
<input type="hidden" name="sec" value="updatesiteinfo"/>
<input type="hidden" name="website_id" value="<?php echo $websiteInfo['website_id']?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left">Website Information</td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Website Title:</td>
		<td class="td_right_col">
			<input type="text" name="title" value="<?php echo stripslashes($websiteInfo['title'])?>" style="width:400px;"><?php echo $errMsg['title']?>
			<p>Use no more than 100 characters.</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Website Description:</td>
		<td class="td_right_col">
			<textarea name="description"><?php echo stripslashes($websiteInfo['description'])?></textarea><?php echo $errMsg['description']?>
			<p>Use no more than 255 characters</p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Website Keywords:</td>
		<td class="td_right_col">
			<textarea name="keywords"><?php echo stripslashes($websiteInfo['keywords'])?></textarea><?php echo $errMsg['keywords']?>
			<p>Use no more than 12 unique  search terms separated by a comma and space.</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Author:</td>
		<td class="td_right_col">
			<input type="text" name="owner_name" value="<?php echo stripslashes($websiteInfo['owner_name'])?>" style="width:200px;">
			<p>Your Name/Company</p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Copyright:</td>
		<td class="td_right_col">
			<input type="text" name="copyright" value="<?php echo stripslashes($websiteInfo['copyright'])?>" style="width:200px;">
			<p>Copyright YourCompany - 2008</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Email:</td>
		<td class="td_right_col">
			<input type="text" name="owner_email" value="<?php echo stripslashes($websiteInfo['owner_email'])?>" style="width:200px;">
			<p>suppport@yoursite.com</p>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Language:</td>
		<td class="td_right_col">
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Charset:</td>
		<td class="td_right_col">
			<?php echo $this->pluginRender('charsetselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Rating:</td>
		<td class="td_right_col">
			<select name="rating">
  				<option value=""></option>
  				<option value="General">General</option>
  				<option value="Mature">Mature</option>
  				<option value="Restricted">Restricted</option>
  			</select>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Distribution:</td>
		<td class="td_right_col">
			<select name="distribution">
  				<option value=""></option>
  				<option value="Global">Global</option>
  				<option value="Local">Local</option>
  			</select>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Robots:</td>
		<td class="td_right_col">
			<select name="robots">
	  			<option value=""></option>
	  			<option value="INDEX,FOLLOW">INDEX,FOLLOW</option>
	  			<option value="INDEX,NOFOLLOW">INDEX,NOFOLLOW</option>
	  			<option value="NOINDEX,FOLLOW">NOINDEX,FOLLOW</option>
	  			<option value="NOINDEX,NOFOLLOW">NOINDEX,NOFOLLOW</option>
	  		</select>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Revisit-after:</td>
		<td class="td_right_col">
			<select name="revisit-after">
	  			<option value=""></option>
	  			<option value="1 Day">1 Day</option>
	  			<option value="7 Days">7 Days</option>
	  			<option value="31 Days">31 Days</option>
	  			<option value="180 Days">180 Days</option>
	  			<option value="365 Days">365 Days</option>
	  		</select>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Expires:</td>
		<td class="td_right_col">
			<input type="text" name="expires" value="<?php echo stripslashes($websiteInfo['expires'])?>" style="width:200px;">
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
    		<a onclick="<?php echo pluginGETMethod(); ?>" href="javascript:void(0);">
         		<img border="0" alt="" src="<?php echo SP_IMGPATH?>/cancel.gif"/>
         	</a>
         	<a onclick="<?php echo pluginPOSTMethod('editSubmitInfo', 'subcontent', 'action=createmetatag'); ?>" href="javascript:void(0);">
         		<img border="0" alt="" src="<?php echo SP_IMGPATH?>/proceed.gif"/>
         	</a>
    	</td>
	</tr>
</table>
</form>