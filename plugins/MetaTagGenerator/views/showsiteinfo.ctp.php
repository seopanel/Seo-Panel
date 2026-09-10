<form id="editSubmitInfo">
<input type="hidden" name="sec" value="updatesiteinfo"/>
<input type="hidden" name="website_id" value="<?php echo intval($websiteInfo['website_id'])?>"/>
<table id="cust_tab">
	<tr class="form_head">
		<th width='30%'>Website Information</th>
		<th>&nbsp;</th>
	</tr>
	<tr class="form_data">
		<td>Website Title:</td>
		<td>
			<input type="text" name="title" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['title']))?>" class="form-control"><?php echo $errMsg['title']?>
			<p>Use no more than 100 characters.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Website Description:</td>
		<td>
			<textarea name="description" class="form-control"><?php echo htmlspecialchars(stripslashes($websiteInfo['description']))?></textarea><?php echo $errMsg['description']?>
			<p>Use no more than 255 characters</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Website Keywords:</td>
		<td>
			<textarea name="keywords" class="form-control"><?php echo htmlspecialchars(stripslashes($websiteInfo['keywords']))?></textarea><?php echo $errMsg['keywords']?>
			<p>Use no more than 12 unique  search terms separated by a comma and space.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Author:</td>
		<td>
			<input type="text" name="owner_name" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['owner_name']))?>" class="form-control">
			<p>Your Name/Company</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Copyright:</td>
		<td>
			<input type="text" name="copyright" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['copyright']))?>" class="form-control">
			<p>Copyright YourCompany - 2008</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Email:</td>
		<td>
			<input type="text" name="owner_email" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['owner_email']))?>" class="form-control">
			<p>suppport@yoursite.com</p>
		</td>
	</tr>	
	<tr class="form_data">
		<td>Language:</td>
		<td>
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="form_data">
		<td>Charset:</td>
		<td>
			<?php echo $this->pluginRender('charsetselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr class="form_data">
		<td>Rating:</td>
		<td>
			<select name="rating" class="custom-select">
  				<option value=""></option>
  				<option value="General">General</option>
  				<option value="Mature">Mature</option>
  				<option value="Restricted">Restricted</option>
  			</select>
		</td>
	</tr>				
	<tr class="form_data">
		<td>Distribution:</td>
		<td>
			<select name="distribution" class="custom-select">
  				<option value=""></option>
  				<option value="Global">Global</option>
  				<option value="Local">Local</option>
  			</select>
		</td>
	</tr>
	<tr class="form_data">
		<td>Robots:</td>
		<td>
			<select name="robots" class="custom-select">
	  			<option value=""></option>
	  			<option value="INDEX,FOLLOW">INDEX,FOLLOW</option>
	  			<option value="INDEX,NOFOLLOW">INDEX,NOFOLLOW</option>
	  			<option value="NOINDEX,FOLLOW">NOINDEX,FOLLOW</option>
	  			<option value="NOINDEX,NOFOLLOW">NOINDEX,NOFOLLOW</option>
	  		</select>
		</td>
	</tr>	
	<tr class="form_data">
		<td>Revisit-after:</td>
		<td>
			<select name="revisit-after" class="custom-select">
	  			<option value=""></option>
	  			<option value="1 Day">1 Day</option>
	  			<option value="7 Days">7 Days</option>
	  			<option value="31 Days">31 Days</option>
	  			<option value="180 Days">180 Days</option>
	  			<option value="365 Days">365 Days</option>
	  		</select>
		</td>
	</tr>
	<tr class="form_data">
		<td>Expires:</td>
		<td>
			<input type="text" name="expires" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['expires']))?>" class="form-control">
		</td>
	</tr>
	<tr class="form_head">
		<th colspan="2">Social Sharing (Open Graph / Twitter Card)</th>
	</tr>
	<tr class="form_data">
		<td>Share Title:</td>
		<td>
			<input type="text" name="og_title" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_title']))?>" class="form-control">
			<p>Shown when this page is shared on social media/chat apps. Leave blank to reuse the Website Title above.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Share Description:</td>
		<td>
			<textarea name="og_description" class="form-control"><?php echo htmlspecialchars(stripslashes($websiteInfo['og_description']))?></textarea>
			<p>Leave blank to reuse the Website Description above.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Share Image URL:</td>
		<td>
			<input type="text" name="og_image" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_image']))?>" class="form-control">
			<p>A full image URL (e.g. https://yoursite.com/share-image.jpg). Recommended at least 1200x630px.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Canonical Share URL:</td>
		<td>
			<input type="text" name="og_url" value="<?php echo htmlspecialchars(stripslashes($websiteInfo['og_url']))?>" class="form-control">
			<p>Optional - the canonical URL this page should be credited to when shared.</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Twitter Card Type:</td>
		<td>
			<select name="twitter_card" class="custom-select">
				<option value="">-- None --</option>
				<option value="summary">Summary</option>
				<option value="summary_large_image">Summary with Large Image</option>
			</select>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="<?php echo pluginGETMethod(); ?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<a onclick="<?php echo pluginPOSTMethod('editSubmitInfo', 'subcontent', 'action=createmetatag'); ?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>