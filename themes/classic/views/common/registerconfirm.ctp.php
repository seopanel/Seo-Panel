<div class="Left">
    <div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <div class="Block">
            	<table width="100%" cellpadding="0" cellspacing="0" class="actionForm">
	            	<?php if (!empty($paymentForm)) {
    ?>
	                	<tr>
	                		<td colspan="2"><?php echo $paymentForm;
    ?></td>
	                	</tr>
	            	<?php 
} else {
    ?>
	                	<tr>
	                		<td colspan="2">
	                			<?php showSuccessMsg($spText['login']['newaccountsuccess'], false);
    ?>
	                		</td>
	                	</tr>
	                	<tr>
	                		<td colspan="2"><a class="actionbut" href="<?php echo SP_WEBPATH?>/login.php"><?php echo $spText['login']['Sign in to your account']?> >></a></td>
		                	</tr>
            			<?php 
}?>
            	</table>
            </div>
		</div>
		<?php echo getRoundTabBot(); ?>           
    </div>
</div>