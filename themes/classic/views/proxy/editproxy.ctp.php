<?php echo showSectionHead($spTextProxy['Edit Proxy']); ?>
<form id="editProxy">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="oldProxy" value="<?php echo $post['oldProxy']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextProxy['Edit Proxy']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Proxy']?>:</td>
		<td class="td_right_col"><input type="text" name="proxy" value="<?php echo $post['proxy']?>"><?php echo $errMsg['proxy']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Port']?>:</td>
		<td class="td_right_col">
			<input type="text" name="port" value="<?php echo $post['port']?>" style="width:60px;"><?php echo $errMsg['port']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Authentication']?>:</td>
		<td class="td_right_col"><input type="checkbox" id="proxy_auth" name="proxy_auth" <?php echo empty($post['proxy_auth']) ? "" : "checked"; ?> > Yes</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextProxy['Proxy Username']?>:</td>
		<td class="td_right_col"><input type="text" name="proxy_username" value="<?php echo $post['proxy_username']?>"><?php echo $errMsg['proxy_username']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spTextProxy['Proxy Password']?>:</td>
		<td class="td_right_col">
			<input type="password" name="proxy_password" value="<?php echo $post['proxy_password']?>"><?php echo $errMsg['proxy_password']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Status']?>:</td>
		<td class="td_right_col">
			<select name="status" onchange="<?php echo $searchFun?>">
				<?php	
                $inactCheck = $actCheck = "";
                if ($post['status']) {
                    $actCheck = "selected";
                } else {
                    $inactCheck = "selected";
                }
                ?>
				<option value="1" <?php echo $actCheck?> ><?php echo $spText['common']["Active"]?></option>
				<option value="0" <?php echo $inactCheck?> ><?php echo $spText['common']["Inactive"]?></option>
			</select>
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
    		<a onclick="scriptDoLoad('proxy.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('proxy.php', 'editProxy', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>