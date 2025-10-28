<?php echo showSectionHead($spTextProxy['Edit Proxy']); ?>
<form id="editProxy">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="oldProxy" value="<?php echo $post['oldProxy']?>"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextProxy['Edit Proxy']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Proxy']?>:</strong></td>
		<td class="td_right_col"><input type="text" name="proxy" value="<?php echo $post['proxy']?>" class="form-control"><?php echo $errMsg['proxy']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Port']?>:</strong></td>
		<td class="td_right_col">
			<input type="text" name="port" value="<?php echo $post['port']?>" class="form-control" style="width:60px;"><?php echo $errMsg['port']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['label']['Authentication']?>:</strong></td>
		<td class="td_right_col"><input type="checkbox" id="proxy_auth" name="proxy_auth" <?php echo empty($post['proxy_auth']) ? "" : "checked"; ?> > Yes</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spTextProxy['Proxy Username']?>:</strong></td>
		<td class="td_right_col"><input type="text" name="proxy_username" value="<?php echo $post['proxy_username']?>" class="form-control"><?php echo $errMsg['proxy_username']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spTextProxy['Proxy Password']?>:</strong></td>
		<td class="td_right_col">
			<input type="password" name="proxy_password" value="<?php echo $post['proxy_password']?>" class="form-control"><?php echo $errMsg['proxy_password']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['common']['Status']?>:</strong></td>
		<td class="td_right_col">
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
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
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('proxy.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('proxy.php', 'editProxy', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
