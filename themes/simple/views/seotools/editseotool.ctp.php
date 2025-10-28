<?php echo showSectionHead($spTextTools['Edit Seo Tool']); ?>
<form id="update_seo_tool">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>

<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextTools['Edit Seo Tool']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Priority']?>:</td>
		<td class="td_right_col">
			<input type="text" name="priority" value="<?php echo $post['priority']?>" class="form-control">
			<?php echo $errMsg['priority']?>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('seo-tools-manager.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('seo-tools-manager.php', 'update_seo_tool', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
