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
	<div id="topnewsbox" style="margin-bottom: 20px;">
		<a class="bold_link" href="https://moz.com/help/guides/moz-api/mozscape/getting-started-with-mozscape/create-and-manage-your-account" target="_blank">
			<?php echo $spTextSettings['click-to-get-moz-account']; ?> &gt;&gt;
		</a>
	</div>
	<?php
}
?>
<form id="updateSettings">
<input type="hidden" value="update" name="sec">
<input type="hidden" value="<?php echo $category?>" name="category">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $headLabel?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php 
	foreach( $list as $i => $listInfo){ 
		$class = ($i % 2) ? "blue_row" : "white_row";
		switch($listInfo['set_type']){
			
			case "small":
				$width = 40;
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
				$width = 200;
				break;

			case "large":
			case "text":
				$width = 500;
				break;
		}
		
		// sp demo settings
		$demoCheckArr = array('SP_API_KEY', 'API_SECRET', 'SP_SMTP_PASSWORD', 'SP_MOZ_API_ACCESS_ID', 'SP_MOZ_API_SECRET');
		if (SP_DEMO && in_array($listInfo['set_name'], $demoCheckArr)) {
			$listInfo['set_val'] = "********";
		}
		
		?>
		<tr class="<?php echo $class?>">
			<td class="td_left_col">
				<?php
				if ($listInfo['set_name'] == 'SP_PAYMENT_CURRENCY') {
					echo $spTextSubscription["Currency"] . ":";
				} else {
					echo $spTextSettings[$listInfo['set_name']] . ":";
				}
				?>
			</td>
			<td class="td_right_col">
				<?php if($listInfo['set_type'] != 'text'){?>
					<?php if($listInfo['set_type'] == 'bool'){?>
						<select  name="<?php echo $listInfo['set_name']?>">
							<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
							<option value="0" <?php echo $selectNo?>><?php echo $spText['common']['No']?></option>
						</select>
					<?php }else{?>
						<?php if($listInfo['set_name'] == 'SP_DEFAULTLANG') {?>
							<select name="<?php echo $listInfo['set_name']?>">
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
							<select name="<?php echo $listInfo['set_name']?>">
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
							<select  name="<?php echo $listInfo['set_name']?>">
								<?php						
								foreach ($currencyList as $currencyInfo) {
									$selectedVal = ($listInfo['set_val'] == $currencyInfo['iso_code']) ? "selected" : "";
									?>
									<option value="<?php echo $currencyInfo['iso_code']; ?>" <?php echo $selectedVal; ?>><?php echo $currencyInfo['name']; ?></option>
									<?php
								}
								?>
							</select>
						<?php } else {
							$passTypeList = array('SP_SMTP_PASSWORD', 'API_SECRET');
						    $type = in_array($listInfo['set_name'], $passTypeList) ? "password" : "text";
						    ?>
							<input type="<?php echo $type?>" name="<?php echo $listInfo['set_name']?>" value="<?php echo stripslashes($listInfo['set_val'])?>" style='width:<?php echo $width?>px'>
							<?php if ($listInfo['set_name'] == 'SP_MOZ_API_SECRET') {?>
								<div style="padding: 10px 6px;">
									<a href="javascript:void(0);" onclick="checkMozConnection('settings.php?sec=checkMozCon', 'show_conn_res')" style="text-decoration: none;"><?php echo $spTextSettings['Verify connection']; ?> &gt;&gt;</a>
								</div>
								<div id="show_conn_res" style="padding: 10px 6px;"></div>
							<?php }?>
							
						<?php }?>
					<?php }?>
				<?php }else{?>
					<textarea name="<?php echo $listInfo['set_name']?>" style='width:<?php echo $width?>px'><?php echo stripslashes($listInfo['set_val'])?></textarea>
				<?php }?>
			</td>
		</tr>
		<?php 
	}
	?>		
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
    		<a onclick="scriptDoLoad('settings.php?category=<?php echo $category?>', 'content', 'layout=ajax')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('settings.php', 'updateSettings', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>