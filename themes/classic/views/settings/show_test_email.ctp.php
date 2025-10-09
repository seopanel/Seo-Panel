<?php echo showSectionHead($spTextPanel['Test Email Settings']); ?>
<form id="emailForm">
<input type="hidden" value="send_test_email" name="sec">
<table class="list">
	<tr class="listHead">
		<td width='30%'><?php echo $spTextPanel['Test Email Settings']?></td>
		<td>&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong><?php echo $spText['login']['Email']?>:</strong></td>
		<td class="td_right_col"><input type="email" name="test_email" value="" class="form-control"><?php echo $errMsg['name']?></td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('settings.php?sec=test_email', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('settings.php', 'emailForm', 'subcontent')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextSettings['Send Email']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<div id="subcontent"></div>
