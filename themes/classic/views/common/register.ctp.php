<div class="Left">
	<div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
    		<div class="Block">
                <form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/register.php">
                <input type="hidden" name="sec" value="register">
                <table width="100%" cellpadding="0" cellspacing="0" class="actionForm">
                	<tr>
                		<td>&nbsp;</td>
                		<th class="main_header"><?php echo $spText['login']['Create New Account']?></th>
                	</tr>
                	<?php if (!empty($_GET['failed'])) {
    ?>
	                	<tr>
	                		<td colspan="2"><?php showErrorMsg($spTextSubscription['internal-error-payment'], false);
    ?></td>
	                	<tr>
                	<?php 
}?>
                	<?php if (!empty($_GET['cancel'])) {
    ?>
	                	<tr>
	                		<td colspan="2"><?php showErrorMsg($spTextSubscription["Your transaction cancelled"], false);
    ?></td>
	                	<tr>
                	<?php 
}?>
                	
                	<?php
                    // if subscription plugin is active
                    if ($subscriptionActive & !empty($userTypeList)) {
                        ?>
						<tr>
							<th width="28%"><?php echo $spTextSubscription['Subscription']?>:*</th>
							<td>
								<select name="utype_id">
									<?php
                                    foreach ($userTypeList as $userTypeInfo) {
                                        $selected = ($post['utype_id'] == $userTypeInfo['id']) ? "selected" : "";
                                        $typeLabel = ucfirst($userTypeInfo['user_type']) . " - ";
                                        
                                        // if user type have price
                                        if ($userTypeInfo['price'] > 0) {
                                            $typeLabel .= $currencyList[SP_PAYMENT_CURRENCY]['symbol'] . $userTypeInfo['price'] . "/" . $spText['label']['Monthly'];
                                        } else {
                                            $typeLabel .= $spText['label']['Free'];
                                        }
                                        ?>
										<option value="<?php echo $userTypeInfo['id']?>" <?php echo $selected;
                                        ?>><?php echo $typeLabel?></option>
										<?php

                                    }
                        ?>
								</select>
								<a class="bold_link" href="<?php echo SP_WEBPATH . "/register.php?sec=pricing";
                        ?>"><?php echo $spTextSubscription['Plans and Pricing']?> &gt;&gt;</a>
								<br>
								<?php echo $errMsg['utype_id']?>
							</td>
						</tr>
						<tr>
							<th><?php echo $spTextSubscription['Term']?>:*</th>
							<td>
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
						<tr>
							<th width="28%"><?php echo $spTextSubscription['Payment Method']?>:*</th>
							<td>
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
						<?php

                    } else {
                        ?>
						<input type="hidden" name="utype_id" value="<?php echo $defaultUserTypeId;
                        ?>">
						<?php

                    }
                    ?>
                	
                	<tr>
                		<th width="28%"><?php echo $spText['login']['Username']?>:*</th>
                		<td><input type="text" name="userName" value="<?php echo $post['userName']?>"><?php echo $errMsg['userName']?></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Password']?>:*</th>
                		<td><input type="password" name="password" value=""><?php echo $errMsg['password']?></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Confirm Password']?>:*</th>
                		<td><input type="password" name="confirmPassword" value=""></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['First Name']?>:*</th>
                		<td><input type="text" name="firstName" value="<?php echo $post['firstName']?>"><?php echo $errMsg['firstName']?></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Last Name']?>:*</th>
                		<td><input type="text" name="lastName" value="<?php echo $post['lastName']?>"><?php echo $errMsg['lastName']?></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Email']?>:*</th>
                		<td><input type="text" name="email" value="<?php echo $post['email']?>"><?php echo $errMsg['email']?></td>
                	</tr>
                	<tr>
                    	<th><?php echo $spText['login']['Enter the code as it is shown']?>:*</th>
                        <td>
                            <div style="margin: 5px 0 10px 0">
                                <img src="<?php echo SP_WEBPATH?>/visual-captcha.php">
                            </div>
                            <div>
                        	    <input type="text" name="code" value="<?php echo $post['code']?>"><?php echo $errMsg['code']?>
                            </div>
                        </td>
                    </tr>    	
                	<tr>
                		<td>&nbsp;</td>
                		<td colspan="0" class="actionsBox">
                			<input class="button" type="submit" name="login" value="<?php echo $spText['login']['Create my account']?> >>"/>
                		</td>
                	</tr>
                </table>
                </form>
    		</div>
		</div>
		<?php echo getRoundTabBot(); ?>
	</div>
</div>