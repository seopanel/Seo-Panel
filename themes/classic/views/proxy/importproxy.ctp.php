<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Import Proxy']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#importProxyHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="importProxyHelpModal" tabindex="-1" role="dialog" aria-labelledby="importProxyHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-file-import"></i></div>
				<h5 class="modal-title" id="importProxyHelpModalLabel">About Import Proxy</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Bulk-add proxies by pasting a list, one per line, as <code>host, port, username, password</code> (username/password are optional). Entries that already exist in the pool are skipped.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Leave "Check Status" ticked to immediately verify each newly imported proxy.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
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
<br>
<div id="subcontent" class="mt-4 pt-4"></div>
