<?php echo showSectionHead($spTextPlugin['Edit Seo Plugin']); ?>
<form id="updateplugin">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="id" value="<?php echo $post['id']?>"/>

<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPlugin['Edit Seo Plugin']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextPlugin['Plugin Name']?>:</td>
		<td class="td_right_col">
			<input type="text" name="plugin_name" value="<?php echo $post['label']?>" class="form-control">
			<?php echo $errMsg['plugin_name']?>
		</td>
	</tr>
	<tr class="blue_row">
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
    		<a onclick="scriptDoLoad('seo-plugins-manager.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('seo-plugins-manager.php', 'updateplugin', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
