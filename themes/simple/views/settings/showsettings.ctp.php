<?php
$headLabel = empty($headLabel) ? $spTextPanel['System Settings'] : $headLabel;
echo showSectionHead($headLabel);

// if saved successfully
if (!empty($saved)) {
    showSuccessMsg($spTextSettings['allsettingssaved'], false);
}

// save process failed
if (!empty($errorMsg)) {
    echo showErrorMsg($errorMsg, false);
}

// help text to get MOZ account
if ($category == "moz") {
	?>
	<div class="alert alert-info mb-3">
		<a class="btn btn-link" href="https://moz.com/checkout/api" target="_blank">
			<?php echo $spTextSettings['click-to-get-moz-account']; ?> &gt;&gt;
		</a>
	</div>
	<?php
} else if ($category == "google") {
	?>
	<div class="alert alert-info">
		<a class="btn btn-link" href="https://support.google.com/googleapi/answer/6158862?hl=en" target="_blank">
			<?php echo $spTextSettings['click-to-get-google-api-key']; ?> &gt;&gt;
		</a>
	</div>
	<div class="alert alert-info mb-3">
		<a class="btn btn-link" href="<?php echo SP_HELP_LINK?>user_guide/settings.html#google-oauth2-credentials" target="_blank">
			<?php echo $spTextSettings['click-to-get-google-api-client-id']; ?> &gt;&gt;
		</a>
	</div>
	<?php
} else if ($category == "dataforseo") {
    ?>
	<div class="alert alert-info mb-3">
		<a class="btn btn-link" href="https://www.seopanel.org/blog/2020/11/how-to-integrate-dataforseo-with-seo-panel/" target="_blank">
			<?php echo $spTextSettings['click-to-get-dataforseo-account']; ?> &gt;&gt;
		</a>
	</div>
	<?php
} else if ($category == "seopanel_api") {
	if (defined('SP_SPAPI_REGISTERED') && SP_SPAPI_REGISTERED) {
		if (empty($spapiCheckResult) || !in_array($spapiCheckResult, ['expired', 'monthly_limit'])) {
			include_once(SP_VIEWPATH."/settings/spapi_upgrade_popup.ctp.php");
		}
		?>
		<div class="alert alert-success mb-3">
			You are registered with the Seo Panel API.
			<div class="mt-2" style="font-size:13px;">For any questions about your Seo Panel API token, please <a href="<?php echo SP_CONTACT_LINK?>" target="_blank"><strong><i class="fas fa-envelope"></i> contact Seo Panel support</strong></a>.</div>
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-info mb-3">
			Register for the Seo Panel API to access additional features and services. It's free!
			<div class="mt-2">
				<a href="javascript:void(0);" onclick="window.spapiShowPopup(false)" class="btn btn-primary">Register</a>
			</div>
		</div>
		<?php
		include(SP_VIEWPATH."/settings/spapi_register_popup.ctp.php");
		?>
		<script type="text/javascript">
		$('#spapi_popup_overlay').hide();
		</script>
		<?php
	}
}
?>
<?php if ($category == 'seopanel_api' && !empty($spapiCheckResult) && $spapiCheckResult === 'unconfirmed'): ?>
<div class="alert alert-info mb-3" style="display:flex; align-items:center; gap:12px;">
    <i class="fas fa-envelope-open-text" style="font-size:20px; flex-shrink:0;"></i>
    <div style="flex:1;">
        Please <strong>verify your email address</strong> to activate your Seo Panel API account. Check your inbox for the verification email.
    </div>
</div>
<?php elseif ($category == 'seopanel_api' && !empty($spapiCheckResult) && in_array($spapiCheckResult, ['expired', 'monthly_limit'])): ?>
<?php include_once(SP_VIEWPATH."/settings/spapi_upgrade_popup.ctp.php"); ?>
<?php
$inlineAlertClass = 'alert-warning';
$inlineIcon       = $spapiCheckResult === 'expired' ? 'fa-calendar-times' : 'fa-tachometer-alt';
$inlineMsg        = $spapiCheckResult === 'expired'
    ? 'Your Seo Panel API subscription has <strong>expired</strong>. Upgrade your plan to restore access.'
    : 'You have reached your <strong>monthly API request limit</strong>. Upgrade your plan to continue.';
?>
<div class="alert <?php echo $inlineAlertClass?> mb-3" style="display:flex; align-items:center; gap:12px;">
    <i class="fas <?php echo $inlineIcon?>" style="font-size:20px; flex-shrink:0;"></i>
    <div style="flex:1;">
        <?php echo $inlineMsg?>
    </div>
    <a href="javascript:void(0);" onclick="window.spapiShowUpgradePopup()" class="btn btn-warning btn-sm" style="flex-shrink:0;">
        <i class="fas fa-rocket" style="margin-right:4px;"></i>Upgrade Plan
    </a>
</div>
<?php endif; ?>
<form id="updateSettings">
<input type="hidden" value="update" name="sec">
<input type="hidden" value="<?php echo $category?>" name="category">
<table class="list">
	<tr class="listHead">
		<td width='30%'><?php echo $headLabel?></td>
		<td>&nbsp;</td>
	</tr>
	<?php
	foreach( $list as $listInfo){
		switch($listInfo['set_type']){
			case "small":
				$width = 100;
				break;

			case "bool":
				if(empty($listInfo['set_val'])){
					$selectYes = "";
					$selectNo = "selected";
				}else{
					$selectYes = "selected";
					$selectNo = "";
				}
				break;

			case "medium":
				$width = 300;
				break;

			case "large":
			case "text":
			    $width = 'large';
				break;
		}

		// sp demo settings
		$demoCheckArr = array(
			'SP_API_KEY', 'API_SECRET', 'SP_SMTP_PASSWORD', 'SP_MOZ_API_ACCESS_ID', 'SP_MOZ_API_SECRET', 'SP_GOOGLE_API_KEY',
			'SP_GOOGLE_API_CLIENT_ID', 'SP_GOOGLE_API_CLIENT_SECRET', 'SP_GOOGLE_ANALYTICS_TRACK_CODE', 'SP_RECAPTCHA_SITE_KEY',
			'SP_RECAPTCHA_SECRET_KEY', 'SP_DFS_API_LOGIN', 'SP_DFS_API_PASSWORD',
		);
		if (SP_DEMO && in_array($listInfo['set_name'], $demoCheckArr)) {
			$listInfo['set_val'] = "********";
		}
		?>
		<tr>
			<td class="td_left_col">
				<strong>
				<?php
				if ($listInfo['set_name'] == 'SP_PAYMENT_CURRENCY') {
				    echo $spTextSubscription["Currency"] . ":";
				} elseif ($listInfo['set_name'] == 'SP_DEFAULT_COUNTRY') {
				    echo $spText['common']["Country"] . ":";
				} elseif ($listInfo['set_name'] == 'SP_MOZ_API_SECRET') {
				    echo $spText['common']["API Token"] . ":";
				} else {
					echo $spTextSettings[$listInfo['set_name']] . ":";
				}
				?>
				</strong>
			</td>
			<td class="td_right_col">
				<?php if($listInfo['set_type'] != 'text'){?>
					<?php if($listInfo['set_type'] == 'bool'){?>
						<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
							<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
							<option value="0" <?php echo $selectNo?>><?php echo $spText['common']['No']?></option>
						</select>
					<?php }else{?>
						<?php if($listInfo['set_name'] == 'SP_DEFAULTLANG') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
								<?php
								foreach ($langList as $langInfo) {
									$selected = ($langInfo['lang_code'] == $listInfo['set_val']) ? "selected" : "";
									?>
									<option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
									<?php
								}
								?>
							</select>
						<?php } else if($listInfo['set_name'] == 'SP_TIME_ZONE') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
								<?php
								$listInfo['set_val'] = empty($listInfo['set_val']) ? ini_get('date.timezone') : $listInfo['set_val'];
								foreach ($timezoneList as $timezoneInfo) {
									$selected = ($timezoneInfo['timezone_name'] == $listInfo['set_val']) ? "selected" : "";
									?>
									<option value="<?php echo $timezoneInfo['timezone_name']?>" <?php echo $selected?>><?php echo $timezoneInfo['timezone_label']?></option>
									<?php
								}
								?>
							</select>
						<?php } else if ($listInfo['set_name'] == 'SP_PAYMENT_CURRENCY') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
								<?php
								foreach ($currencyList as $currencyInfo) {
									$selectedVal = ($listInfo['set_val'] == $currencyInfo['iso_code']) ? "selected" : "";
									?>
									<option value="<?php echo $currencyInfo['iso_code']; ?>" <?php echo $selectedVal; ?>><?php echo $currencyInfo['name']; ?></option>
									<?php
								}
								?>
							</select>
						<?php } else if ($listInfo['set_name'] == 'SP_DEFAULT_COUNTRY') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
								<?php
								foreach ($countryList as $countryCode => $countryName) {
									$selectedVal = ($listInfo['set_val'] == $countryCode) ? "selected" : "";
									?>
									<option value="<?php echo $countryCode; ?>" <?php echo $selectedVal; ?>><?php echo $countryName; ?></option>
									<?php
								}
								?>
							</select>
						<?php } else if ($listInfo['set_name'] == 'SP_MAIL_ENCRYPTION') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select" style="width:150px;">
								<option value="">-- <?php echo $spText['common']['Select']?> --</option>
								<?php
								$encryptions = ['ssl', 'tls'];
								foreach ($encryptions as $encryption) {
									$selectedVal = (strtolower($listInfo['set_val']) == $encryption) ? 'selected' : '';
									?>
									<option value="<?php echo $encryption?>" <?php echo $selectedVal?>><?php echo strtoupper($encryption)?></option>
									<?php
								}
								?>
							</select>
						<?php } else if ($listInfo['set_name'] == 'SP_DFS_BALANCE') {?>
							<label id='sp_dfs_balance'><?php echo stripslashes($listInfo['set_val'])?></label>
						<?php } else {
							$passTypeList = array('SP_SMTP_PASSWORD', 'API_SECRET');
						    $type = in_array($listInfo['set_name'], $passTypeList) ? "password" : "text";
						    $styleOpt = ($width == 'large') ? "class='form-control'" : "class='form-control' style='width: $width"."px'"
						    ?>
							<input type="<?php echo $type?>" name="<?php echo $listInfo['set_name']?>" value="<?php echo stripslashes($listInfo['set_val'])?>" <?php echo $styleOpt?>>
							<?php if ($listInfo['set_name'] == 'SP_MOZ_API_SECRET') {?>
								<div class="mt-2">
									<a href="javascript:void(0);" onclick="checkMozConnection('settings.php?sec=checkMozCon', 'show_conn_res')" class="btn btn-info"><?php echo $spTextSettings['Verify connection']; ?> &gt;&gt;</a>
								</div>
								<div id="show_conn_res" class="mt-2"></div>
							<?php } else if ($listInfo['set_name'] == 'SP_GOOGLE_API_KEY') {?>
								<div class="mt-2">
									<a href="javascript:void(0);" onclick="checkGoogleAPIConnection('settings.php?sec=checkGoogleAPI', 'show_conn_res')" class="btn btn-info"><?php echo $spTextSettings['Verify connection']; ?> &gt;&gt;</a>
								</div>
								<div id="show_conn_res" class="mt-2"></div>
							<?php } else if ($listInfo['set_name'] == 'SP_DFS_API_PASSWORD') {?>
								<div class="mt-2">
									<a href="javascript:void(0);" onclick="checkDataForSEOAPIConnection('settings.php?sec=checkDataForSEOAPI', 'show_conn_res')" class="btn btn-info"><?php echo $spTextSettings['Verify connection']; ?> &gt;&gt;</a>
								</div>
								<div id="show_conn_res" class="mt-2"></div>
							<?php } else if ($listInfo['set_name'] == 'SP_SPAPI_KEY') {?>
								<div class="mt-2">
									<a href="javascript:void(0);" onclick="checkSpApiConnection('settings.php?sec=checkSpApiCon', 'show_conn_res')" class="btn btn-info"><?php echo $spTextSettings['Verify connection']; ?> &gt;&gt;</a>
									<a href="javascript:void(0);" onclick="resetSpApiToken('show_conn_res')" class="btn btn-warning">Reset API Token &gt;&gt;</a>
									<a href="javascript:void(0);" onclick="window.spapiShowUpgradePopup()" class="btn btn-success">Upgrade Token &gt;&gt;</a>
								</div>
								<div id="show_conn_res" class="mt-2"></div>
							<?php }?>

						<?php }?>
					<?php }?>
				<?php }else{?>
					<textarea name="<?php echo $listInfo['set_name']?>" class="form-control"><?php echo stripslashes($listInfo['set_val'])?></textarea>
				<?php }?>
			</td>
		</tr>
		<?php
	}

	if ($category == "google") {
		?>
		<tr class="white_row">
			<td class="td_left_col"><strong><?php echo $spTextSettings["Authorised redirect URI"]?></strong></td>
			<td class="td_right_col"><?php echo SP_WEBPATH . "/admin-panel.php?sec=connections&action=connect_return&category=google"?></td>
		</tr>
		<?php
	}
	?>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('settings.php?category=<?php echo $category?>', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('settings.php', 'updateSettings', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
