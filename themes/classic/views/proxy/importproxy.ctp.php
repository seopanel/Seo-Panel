<?php echo showSectionHead($spTextPanel['Import Proxy']); ?>
<form id="import_proxy">
<input type="hidden" name="sec" value="importproxy"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Import Proxy']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Links:</td>
		<td class="td_right_col">
			<textarea name="proxy_list" rows="10"></textarea>
			<p style="font-size: 12px;"><?php echo $spTextProxy['enterproxynote']?></p>
			<p><b><?php echo $spText['label']["Syntax"]?>:</b></p>
			<P><?php echo $spTextProxy['proxysyntax']?></P>
			<p><b>Eg:</b></p>
			<p>123.66.2.3, 67, sp456, s$4A1</p>
			<p>pr1.proxylist.com, 82, , </p>
			<p>123.6.78.9, 899</p>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['button']["Check Status"]?>:</td>
		<td class="td_right_col"><input type="checkbox" name="check_status" value="1" checked="checked"></td>
	</tr>
	<tr class="white_row">
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
    		<a onclick="scriptDoLoad('proxy.php?sec=import', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('proxy.php', 'import_proxy', 'subcontent')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<div id="subcontent"></div>