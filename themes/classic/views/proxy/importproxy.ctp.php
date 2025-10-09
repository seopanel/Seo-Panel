<?php echo showSectionHead($spTextPanel['Import Proxy']); ?>
<form id="import_proxy">
<input type="hidden" name="sec" value="importproxy"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Import Proxy']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><strong>Links:</strong></td>
		<td class="td_right_col">
			<textarea name="proxy_list" rows="10" class="form-control"></textarea>
			<p class="mt-2 text-muted" style="font-size: 12px;"><?php echo $spTextProxy['enterproxynote']?></p>
			<p class="mt-2"><strong><?php echo $spText['label']["Syntax"]?>:</strong></p>
			<p><?php echo $spTextProxy['proxysyntax']?></p>
			<p class="mt-2"><strong>Eg:</strong></p>
			<p>123.66.2.3, 67, sp456, s$4A1</p>
			<p>pr1.proxylist.com, 82, , </p>
			<p>123.6.78.9, 899</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><strong><?php echo $spText['button']["Check Status"]?>:</strong></td>
		<td class="td_right_col">
			<div class="form-check">
				<input type="checkbox" name="check_status" value="1" checked="checked" class="form-check-input" id="check_status">
				<label class="form-check-label" for="check_status"><?php echo $spText['common']['Yes']?></label>
			</div>
		</td>
	</tr>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('proxy.php?sec=import', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "$(window).scrollTop($('#subcontent').offset().top);scriptDoLoadPost('proxy.php', 'import_proxy', 'subcontent')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<div id="subcontent"></div>
