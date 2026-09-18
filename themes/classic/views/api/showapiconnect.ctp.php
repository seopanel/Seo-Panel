<?php
echo showSectionHead($spTextPanel['API Connection']);
?>
<div class="card">
	<div class="card-header bg-primary text-white">
		<h5 class="mb-0"><?php echo $spTextPanel['API Connection']?></h5>
	</div>
	<div class="card-body">
		<table class="table table-bordered">
			<tbody>
				<tr>
					<td class="bg-light" width='30%'><strong><?php echo $spTextAPI['API Url']?>:</strong></td>
					<td><?php echo $apiInfo['api_url']?></td>
				</tr>
				<tr>
					<td class="bg-light"><strong><?php echo $spTextSettings['SP_API_KEY']?>:</strong></td>
					<td>
						<code><?php echo SP_DEMO? "*********" : htmlspecialchars($apiInfo['SP_API_KEY'])?></code>
						<?php if (!SP_DEMO) { ?>
							<form id="regen_api_key_form" onsubmit="return false;" style="display:inline;">
								<input type="hidden" name="sec" value="regenerate_api_key">
							</form>
							<a onclick="confirmSubmit('apimanager.php', 'regen_api_key_form', 'content')" href="javascript:void(0);" class="btn btn-sm btn-secondary">
								<i class="fas fa-sync"></i> <?php echo $spTextAPI['Regenerate'] ?? 'Regenerate'?>
							</a>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="bg-light"><strong><?php echo $spTextSettings['API_SECRET']?>:</strong></td>
					<td>
						<div id="api_secret" style="display: none;"><code><?php echo SP_DEMO? "*********" : htmlspecialchars($apiInfo['API_SECRET'])?></code></div>
						<a href="javascript:void(0);" onclick="showDiv('api_secret');hideDiv('secret_link');" id='secret_link' class="btn btn-sm btn-secondary">
							<i class="fas fa-eye"></i> Show
						</a>
						<?php if (!SP_DEMO) { ?>
							<form id="regen_api_secret_form" onsubmit="return false;" style="display:inline;">
								<input type="hidden" name="sec" value="regenerate_api_secret">
							</form>
							<a onclick="confirmSubmit('apimanager.php', 'regen_api_secret_form', 'content')" href="javascript:void(0);" class="btn btn-sm btn-secondary">
								<i class="fas fa-sync"></i> <?php echo $spTextAPI['Regenerate'] ?? 'Regenerate'?>
							</a>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="alert alert-warning mb-0">
							<i class="fas fa-exclamation-triangle"></i>
							<?php echo $spTextAPI['api_regenerate_warning'] ?? 'Regenerating either value immediately invalidates it for every existing integration using it - update them with the new value right after.'?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="text-end">
						<a class="btn btn-link" href="<?php echo SP_HELP_LINK?>api.html" target="_blank">
							<i class="fas fa-book"></i> <?php echo $spTextAPI['API Guide']?> &raquo;
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<br>