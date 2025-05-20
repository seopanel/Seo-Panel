<?php echo showSectionHead($spText['common']['Edit User Type']); ?>
<form id="editUserType">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="old_user_type" value="<?php echo $post['old_user_type']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<table id="cust_tab">
	<tr class="form_head">
		<th width='30%'><?php echo $spText['common']['Edit User Type']?></th>
		<th>&nbsp;</th>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Name']?>:</td>
		<td><input type="text" name="user_type" value="<?php echo $post['user_type']?>"><?php echo $errMsg['user_type']?></td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['label']['Description']?>:</td>
		<td><textarea name="description" id="usertypedescription"><?php echo $post['description']?></textarea><?php echo $errMsg['description']?></td>
	</tr>
	<?php if ($isPluginSubsActive) {?>
		<tr class="form_data">
			<td><?php echo $spTextSubscription['Access Type']?>:</td>
			<td>
				<select name="access_type">
					<?php foreach ($accessTypeList as $accessType => $atLabel) {
						$selectedVal = ($accessType == $post['access_type']) ? 'selected="selected"' : "";
						?>
						<option value="<?php echo $accessType ?>" <?php echo $selectedVal; ?> ><?php echo $atLabel; ?></option>
					<?php }?>
				</select>
			</td>
		</tr>
	<?php } else { ?>
		<input type="hidden" name="access_type" value="<?php echo $post['access_type'] ?>">
	<?php } ?>
	<tr class="form_data">
		<td><?php echo $spText['common']['Keywords Count']?>:</td>
		<td>
			<input type="number" name="keywordcount" id="keywordcount" value="<?php echo $post['keywordcount']?>"><?php echo $errMsg['keywordcount']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Websites Count']?>:</td>
		<td>
			<input type="number" name="websitecount" id="websitecount" value="<?php echo $post['websitecount']?>"><?php echo $errMsg['websitecount']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spTextSubscription['Social Media Link Count']?>:</td>
		<td>
			<input type="number" name="social_media_link_count" id="social_media_link_count" value="<?php echo $post['social_media_link_count']?>">
			<?php echo $errMsg['social_media_link_count']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spTextSubscription['Review Link Count']?>:</td>
		<td>
			<input type="number" name="review_link_count" id="review_link_count" value="<?php echo $post['review_link_count']?>">
			<?php echo $errMsg['review_link_count']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Search Engine Count']?>:</td>
		<td>
			<input type="number" name="searchengine_count" id="searchengine_count" value="<?php echo $post['searchengine_count']?>">
			<?php echo $errMsg['searchengine_count']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spTextSubscription['Directory Submit Limit']?>:</td>
		<td>
			<input type="number" name="directory_submit_limit" id="directory_submit_limit" value="<?php echo $post['directory_submit_limit']?>">
			<?php echo $errMsg['directory_submit_limit']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>	
	
	<tr class="form_data">
		<td><?php echo $spTextSubscription['Directory Submit Daily Limit']?>:</td>
		<td>
			<input type="number" name="directory_submit_daily_limit" id="directory_submit_daily_limit" value="<?php echo $post['directory_submit_daily_limit']?>">
			<?php echo $errMsg['directory_submit_daily_limit']?>
			<p><?php echo $spTextSubscription['infinite_limit_text']?></p>
		</td>
	</tr>	
	
	<tr class="form_data">
		<td><?php echo $spTextSubscription['site_auditor_max_page_limit']?>:</td>
		<td>
			<input type="number" name="site_auditor_max_page_limit" id="site_auditor_max_page_limit" value="<?php echo $post['site_auditor_max_page_limit']?>">
			<?php echo $errMsg['site_auditor_max_page_limit']?>
		</td>
	</tr>
	
	<?php if ($isPluginSubsActive) {?>
		<tr class="form_data">
			<td><?php echo $spText['common']['Price']?>(<?php echo $spText['label']['Monthly']?>):</td>
			<td>
				<?php echo $currencyList[SP_PAYMENT_CURRENCY]['symbol']; ?><input type="text" name="price" id="price" value="<?php echo $post['price']?>"><?php echo $errMsg['price']?>
			</td>
		</tr>
		<tr class="form_data">
			<td><?php echo $spTextSubscription['free_trial_period']?>(<?php echo $spText['label']['Days']?>):</td>
			<td>
				<input type="text" name="free_trial_period" id="free_trial_period" value="<?php echo $post['free_trial_period']?>"><?php echo $errMsg['free_trial_period']?>
			</td>
		</tr>
	<?php }?>
	<tr class="form_data">
		<td><?php echo $spTextSubscription['enable_email_activation']?>:</td>
		<td>
			<select name="enable_email_activation" id="enable_email_activation">
				<?php if ($post['enable_email_activation']) { ?>
					<option value="1" selected="selected"><?php echo $_SESSION['text']['common']['Yes']?></option>
					<option value="0"><?php echo $_SESSION['text']['common']['No']?></option>
				<?php } else { ?>
					<option value="1"><?php echo $_SESSION['text']['common']['Yes']?></option>
					<option value="0" selected="selected"><?php echo $_SESSION['text']['common']['No']?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Status']?>:</td>
		<td>
			<select name="user_type_status" id="user_type_status">
				<?php if (!empty($post['status']) || !empty($post['user_type_status'])) { ?>
					<option value="1" selected="selected"><?php echo $_SESSION['text']['common']['Active']?></option>
					<option value="0"><?php echo $_SESSION['text']['common']['Inactive']?></option>
				<?php } else { ?>
					<option value="1"><?php echo $_SESSION['text']['common']['Active']?></option>
					<option value="0" selected="selected"><?php echo $_SESSION['text']['common']['Inactive']?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	
	<tr class="form_data"><th colspan="2"><?php echo $spTextSubscription['Plugin Access Settings']?></th></tr>
	<?php 
	foreach ($pluginAccessList as $i => $pluginInfo) {
		$selectYes = $pluginInfo['value'] ? " selected" : "";
		?>
		<tr class="form_data">
			<td><?php echo $pluginInfo['label']?>:</td>
			<td>
				<select  name="<?php echo $pluginInfo['name']?>">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
				</select>
				<?php echo $errMsg[$pluginInfo['name']]?>
			</td>
		</tr>	
	<?php }?>
	
	<tr class="form_data"><th colspan="2"><?php echo $spTextSubscription['Seo Tools Access Settings']?></th></tr>
	<?php 
	foreach ($toolAccessList as $i => $toolInfo) {
		$selectYes = $toolInfo['value'] ? " selected" : "";
		?>
		<tr class="form_data">
			<td><?php echo $toolInfo['label']?>:</td>
			<td>
				<select  name="<?php echo $toolInfo['name']?>">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
				</select>
				<?php echo $errMsg[$toolInfo['name']]?>
			</td>
		</tr>	
	<?php }?>
</table>
<br>
<table width="100%" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('user-types-manager.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a> &nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('user-types-manager.php', 'editUserType', 'content')"; ?>         		
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>