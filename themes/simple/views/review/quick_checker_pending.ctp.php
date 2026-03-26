<div class="alert alert-info">
	<h5><i class="fas fa-spinner fa-spin"></i> Task Submitted to DataForSEO</h5>
	<p>Your review check request has been submitted and is being processed.</p>
	<p><strong>URL:</strong> <?php echo htmlentities($smLink, ENT_QUOTES)?></p>
	<p><strong>Platform:</strong> <?php echo ucfirst($smType)?></p>
	<?php if (!empty($statusMessage)) {?>
		<p><strong>Status:</strong> <?php echo $statusMessage?></p>
	<?php }?>
	<p class="mt-3 mb-0">
		<small>Click "Check Status" to see if results are ready. It may take a few seconds to process.</small>
	</p>
</div>

<form id="check_status_form">
	<input type="hidden" name="sec" value="checkQuickCheckerStatus">
	<input type="hidden" name="task_id" value="<?php echo htmlentities($taskId, ENT_QUOTES)?>">
	<input type="hidden" name="type" value="<?php echo htmlentities($smType, ENT_QUOTES)?>">
	<input type="hidden" name="url" value="<?php echo htmlentities($smLink, ENT_QUOTES)?>">
</form>

<table class="actionSec mt-2">
	<tr>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo $pageScriptPath?>', 'check_status_form', 'subcontent')" class="btn btn-primary">
				<i class="fas fa-sync-alt"></i> <?php echo $spText['button']['Check Status'] ?? 'Check Status'?>
			</a>
		</td>
	</tr>
</table>
