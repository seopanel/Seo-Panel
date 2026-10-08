<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Perception Check'] ?? 'AI Perception Check'); ?>

<div class="alert alert-warning">
	<?php echo $spTextAIV['perceptionprivacynotice'] ?? 'Unlike the rest of AI Visibility, this feature sends your website\'s name and URL directly to the AI provider you choose below, using the API key you enter. It only runs when you click "Ask", using a key you supply - nothing is sent automatically, and SEO Panel never sees or pays for these calls.'?>
</div>

<?php
$providerLabels = [
	'openai'    => 'OpenAI (ChatGPT)',
	'anthropic' => 'Anthropic (Claude)',
	'google'    => 'Google (Gemini)',
];
$configuredByProvider = [];
foreach ($providers as $p) { $configuredByProvider[$p['provider']] = $p; }
?>

<table class="list" style="margin-top:15px;">
	<tr class="listHead">
		<td><?php echo $spText['common']['Provider'] ?? 'Provider'?></td>
		<td><?php echo $spText['common']['Status'] ?? 'Status'?></td>
		<td><?php echo $spTextAIV['API Key'] ?? 'API Key'?></td>
		<td style="width: 15%"><?php echo $spText['common']['Action'] ?? 'Action'?></td>
	</tr>
	<?php foreach ($providerLabels as $providerKey => $providerLabel) { ?>
		<?php $configured = $configuredByProvider[$providerKey] ?? null; ?>
		<tr>
			<td><?php echo htmlspecialchars($providerLabel)?></td>
			<td class="text-center">
				<?php if (!empty($configured)) { ?>
					<span class="badge badge-success py-2 px-3 text-light"><?php echo $spTextAIV['Configured'] ?? 'Configured'?></span>
				<?php } else { ?>
					<span class="badge badge-secondary py-2 px-3 text-light"><?php echo $spTextAIV['Not configured'] ?? 'Not configured'?></span>
				<?php } ?>
			</td>
			<td>
				<?php if (!empty($configured)) { ?>
					<code>&bull;&bull;&bull;&bull;<?php echo htmlspecialchars($configured['key_tail'])?></code>
				<?php } else { ?>
					<em style="color:#adb5bd;">&mdash;</em>
				<?php } ?>
			</td>
			<td class="text-center">
				<?php if (!empty($configured)) { ?>
					<form id="aip_remove_form_<?php echo $providerKey?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="remove-key">
						<input type="hidden" name="provider" value="<?php echo $providerKey?>">
					</form>
					<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_form_<?php echo $providerKey?>', 'content')" href="javascript:void(0);" class="btn btn-danger">
						<?php echo $spTextAIV['Remove'] ?? 'Remove'?>
					</a>
				<?php } else { ?>
					<a href="javascript:void(0);" onclick="document.getElementById('aip_add_row_<?php echo $providerKey?>').style.display='table-row';" class="btn btn-secondary">
						<?php echo $spTextAIV['Add key'] ?? 'Add key'?>
					</a>
				<?php } ?>
			</td>
		</tr>
		<?php if (empty($configured)) { ?>
			<tr id="aip_add_row_<?php echo $providerKey?>" style="display:none;">
				<td colspan="4">
					<form id="aip_save_form_<?php echo $providerKey?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="save-key">
						<input type="hidden" name="provider" value="<?php echo $providerKey?>">
						<input type="password" name="api_key" class="form-control" style="max-width:400px;display:inline-block;" placeholder="<?php echo $spTextAIV['Paste your API key'] ?? 'Paste your API key'?>">
						<a href="javascript:void(0);" onclick="scriptDoLoadPost('ai-perception.php', 'aip_save_form_<?php echo $providerKey?>', 'content')" class="btn btn-primary">
							<?php echo $spText['button']['Save'] ?? 'Save'?>
						</a>
					</form>
				</td>
			</tr>
		<?php } ?>
	<?php } ?>
</table>

<p style="margin-top:15px;">
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=check', 'content')" class="btn btn-outline-primary">
		<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Go to AI Perception Check'] ?? 'Go to AI Perception Check'?>
	</a>
</p>
