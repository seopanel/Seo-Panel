<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Perception Check'] ?? 'AI Perception Check'); ?>

<?php if (!empty($noWebsites)) { ?>
	<?php echo showNoRecordsList(0); ?>
<?php } else { ?>

<?php $configuredProviders = array_column($providers, 'provider'); ?>

<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select id="aip_website_id" class="custom-select">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo htmlspecialchars($websiteInfo['name'])?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>

<?php if (empty($configuredProviders)) { ?>
	<div class="aiv-note aiv-note-warn">
		<i class="fas fa-exclamation-triangle"></i>
		<span>
			<?php echo $spTextAIV['No AI providers configured yet.'] ?? 'No AI providers configured yet.'?>
			<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')"><?php echo $spTextAIV['Add an API key'] ?? 'Add an API key'?></a>
		</span>
	</div>
<?php } else { ?>
	<div style="margin-top:15px;">
		<?php
		$providerLabels = ['openai' => 'OpenAI (ChatGPT)', 'anthropic' => 'Anthropic (Claude)', 'google' => 'Google (Gemini)'];
		foreach ($configuredProviders as $p) {
		?>
			<button type="button" class="btn btn-secondary" style="margin-right:8px;margin-bottom:8px;" onclick="aipAsk('<?php echo $p?>')">
				<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?> <?php echo htmlspecialchars($providerLabels[$p] ?? $p)?>
			</button>
		<?php } ?>
	</div>

	<div id="aip_results" style="margin-top:10px;"></div>
<?php } ?>

<div class="aiv-note" style="margin-top:15px;">
	<i class="fas fa-calendar-check"></i>
	<span>
		<?php echo $spTextAIV['Set up weekly, unattended tracking of your own prompts'] ?? 'Set up weekly, unattended tracking of your own prompts'?>
		<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')"><?php echo $spTextAIV['Go to Scheduled Tracking'] ?? 'Go to Scheduled Tracking'?></a>
	</span>
</div>

<script>
function aipAsk(provider) {
	var websiteId = document.getElementById('aip_website_id').value;
	var resultsEl = document.getElementById('aip_results');
	var providerLabels = {openai: 'OpenAI (ChatGPT)', anthropic: 'Anthropic (Claude)', google: 'Google (Gemini)'};
	var label = providerLabels[provider] || provider;

	var box = document.createElement('div');
	box.className = 'aiv-card';
	box.innerHTML = '<div class="aiv-card-header"><div class="aiv-card-title">' + label + '</div></div>'
		+ '<div class="aip-result-body"><i class="fas fa-spinner fa-spin"></i> <?php echo $spTextAIV['Asking...'] ?? 'Asking...'?></div>';
	resultsEl.prepend(box);
	var bodyEl = box.querySelector('.aip-result-body');

	fetch('ai-perception.php?sec=ask&website_id=' + encodeURIComponent(websiteId) + '&provider=' + encodeURIComponent(provider), { credentials: 'same-origin' })
		.then(function(res) { return res.json(); })
		.then(function(data) {
			if (data.ok) {
				bodyEl.textContent = data.text;
			} else {
				bodyEl.innerHTML = '<span class="text-danger">' + (data.error || 'Request failed') + '</span>';
			}
		})
		.catch(function() {
			bodyEl.innerHTML = '<span class="text-danger"><?php echo $spTextAIV['Request failed'] ?? 'Request failed.'?></span>';
		});
}
</script>

<?php } ?>
