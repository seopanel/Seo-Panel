<div class="Left">
	<div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <div class="Block">
                <form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/login.php?sec=forgot">
                <input type="hidden" name="sec" value="requestpass">
                <table width="70%" cellpadding="0" cellspacing="0" class="actionForm" style="margin-top: 20px;">
                	<tr>
                		<td>&nbsp;</td>
                		<th class="main_header" colspan="2"><?php echo $spText['login']['Forgot password?']?></th>
                	</tr>
                	<tr>
                		<th><?php echo $spText['login']['Email']?>:</th>
                		<td><input type="text" class="large" name="email" value="<?php echo $post['email']?>"><?php echo $errMsg['email']?></td>
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
                		<th>&nbsp;</th>
                		<td class="actionsBox">
                			<?php if (!isLoggedIn()) {
    ?>
                				&nbsp;<input class="button" type="submit" name="login" value="<?php echo $spText['login']['Request Password']?> &gt;&gt"/>
                			<?php 
}?>
                		</td>
                	</tr>
                </table>               
                </form>
            </div>
        </div>
    	<?php echo getRoundTabBot(); ?>
	</div>
</div>