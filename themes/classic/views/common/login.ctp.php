<div class="Left">
	<div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <div class="Block">
                <form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/login.php">
                <input type="hidden" name="sec" value="login">
                <input type="hidden" name="referer" value="<?php echo $post['referer']?>"></input>
                <table width="500px" cellpadding="0" cellspacing="0" class="actionForm" style="margin-top: 20px;">
                	<tr>
                		<td style="text-align: right;"><img src="<?php echo SP_WEBPATH?>/images/lock-icon.png"></td>
                		<th class="main_header"><?php echo ucwords($spText['common']['signin'])?></th>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Login']?>:</th>
                		<td><input type="text" name="userName" id="userName" value="<?php echo $post['userName']?>"><br><?php echo $errMsg['userName']?></td>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Password']?>:</th>
                		<td><input type="password" name="password" value=""><a href="<?php echo SP_WEBPATH?>/login.php?sec=forgot" class="link"><?php echo $spText['login']['Forgot password?']?></a><br><?php echo $errMsg['password']?></td>
                	</tr>
                	<tr>
                		<th>&nbsp;</th>
                		<td class="actionsBox">
                			<input class="button" type="submit" name="login" value="<?php echo ucwords($spText['common']['signin'])?> >>"/>
                			<?php if(!isLoggedIn() && SP_USER_REGISTRATION){ ?>
                				&nbsp;<a href="<?php echo SP_WEBPATH?>/register.php" style="font-size: 13px;"><?php echo $spText['login']['Register']?></a>
                			<?php }?>
                		</td>
                	</tr>
                </table>

                <script>
                window.onload = function() {
                  document.getElementById("userName").focus();
                }
                </script>
                </form>
            </div>
        </div>
    	<?php echo getRoundTabBot(); ?>
	</div>
</div>