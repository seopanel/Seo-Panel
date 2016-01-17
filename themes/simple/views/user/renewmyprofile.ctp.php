<?php 
echo showSectionHead($spTextSubscription['Renew Subscription']);

// if payment form is to displayed
if (!empty($paymentForm)) {
    echo $paymentForm;
} else {
    if (!empty($msg)) {
        showSuccessMsg($msg, false);
    }
    ?>
	<form id="updateUser">
	<input type="hidden" name="sec" value="update-subscription"/>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
		<tr class="listHead">
			<td class="left" width='30%'><?php echo $spTextSubscription['Renew Subscription'];
    ?></td>
			<td class="right">&nbsp;</td>
		</tr>
		<tr class="white_row">
			<td class="td_left_col"><?php echo $spTextSubscription['Subscription']?>:*</td>
			<td class="td_right_col">
				<select name="utype_id">
					<?php
                    foreach ($userTypeList as $uTypeInfo) {
                        $typeLabel = ucfirst($uTypeInfo['user_type']) . " - ";
                        
                        // if user type have price
                        if ($uTypeInfo['price'] > 0) {
                            $typeLabel .= $currencyList[SP_PAYMENT_CURRENCY]['symbol'] . $uTypeInfo['price'] . "/" . $spText['label']['Monthly'];
                        } else {
                            $typeLabel .= $spText['label']['Free'];
                        }
                        
                        $selected = ($uTypeInfo['id'] == $userTypeInfo['id']) ? "selected" : "";
                        ?>
						<option value="<?php echo $uTypeInfo['id']?>" <?php echo $selected;
                        ?>><?php echo $typeLabel?></option>
						<?php

                    }
    ?>
				</select>
			</td>
		</tr>
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spTextSubscription['Term']?>:*</td>
			<td class="td_right_col">
				<select name="quantity">
					<?php
                    for ($i = 1; $i <= 24; $i++) {
                        ?>
						<option value="<?php echo $i;
                        ?>"><?php echo $i;
                        ?></option>
						<?php

                    }
    ?>
				</select>
			</td>
		</tr>
		<tr class="white_row">
			<td class="td_left_col"><?php echo $spTextSubscription['Payment Method']?>:*</td>
			<td class="td_right_col">
				<select name="pg_id">
					<?php
                    // loop through the payment types
                    foreach ($pgList as $pgInfo) {
                        $checked = ($defaultPgId == $pgInfo['id']) ? "selected" : ""
                        ?>
						<option value="<?php echo $pgInfo['id']?>" <?php echo $checked;
                        ?> ><?php echo $pgInfo['name'];
                        ?></option>
						<?php

                    }
    ?>
				</select>
				<?php echo $errMsg['pg_id']?>
			</td>
		</tr>
		<tr class="white_row">
			<th class="td_left_col"><?php echo $spTextUser['Expiry Date']?>:</th>
			<td class="td_right_col"><?php echo date("d M Y", strtotime($userInfo['expiry_date']));
    ?></td>
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
	    		<a onclick="scriptDoLoad('users.php?sec=my-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="actionbut">
	         		<?php echo $spText['button']['Cancel']?>
	         	</a>&nbsp;
	         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('users.php', 'updateUser', 'content')";
    ?>
	         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
	         		<?php echo $spText['button']['Proceed']?>
	         	</a>
	    	</td>
		</tr>
	</table>
	</form>
<?php

}
?>