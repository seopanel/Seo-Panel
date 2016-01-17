<div class="Left">
	<div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <div class="Block">
                <form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/login.php?sec=forgot">
                <input type="hidden" name="sec" value="requestpass">
                <table width="500px" cellpadding="0" cellspacing="0" class="actionForm" style="margin-top: 20px;">
                	<tr>
                		<td><?php if (!empty($error)) {
    ?>
                    		<h1 class="head"><?php echo $spText['login']['Your Password Reset Failed']?></h1>
                    		<div class="acc_confirm">
                    			<?php showErrorMsg($error, false);
    ?>
                    		</div>
                    	<?php 
} else {
    ?>
                    		<h1 class="head"><?php echo $spText['login']['Your Password Reset Successfully']?></h1>		
                    		<div class="acc_confirm">
                    		<?php echo showSuccessMsg($spText['login']['password_reset_success_message'], false);
    ?>
                    		</div>
                    	<?php 
}?></td>
                	</tr>
                </table>               
                </form>
            </div>
        </div>
    	<?php echo getRoundTabBot(); ?>
	</div>
</div>