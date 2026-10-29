<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Perception Check'] ?? 'AI Perception Check'); ?>

<!-- same 3-way sub-nav as settings.ctp.php/tracking.ctp.php -->
<div class="aip-subnav" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:15px;">
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')" class="aiv-subnav-pill">
		<i class="fas fa-key"></i> <?php echo $spTextAIV['Setup'] ?? 'Setup'?>
	</a>
	<span class="aiv-subnav-pill aiv-subnav-active">
		<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?>
	</span>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-subnav-pill">
		<i class="fas fa-calendar-check"></i> <?php echo $spTextAIV['Scheduled Tracking'] ?? 'Scheduled Tracking'?>
	</a>
</div>

<?php if (!empty($noWebsites)) { ?>
	<?php echo showNoRecordsList(0); ?>
<?php } else { ?>

<?php $configuredProviders = array_column($providers, 'provider'); ?>
<?php $providerColors = ['openai' => '#10a37f', 'anthropic' => '#cc785c', 'google' => '#4285f4']; ?>
<?php $providerLabels = ['openai' => 'OpenAI (ChatGPT)', 'anthropic' => 'Anthropic (Claude)', 'google' => 'Google (Gemini)']; ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-comment-dots"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Ask an AI'] ?? 'Ask an AI'?></div>
	</div>

	<table class="search" style="width: 60%; margin-bottom:0;">
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
		<div style="text-align:center;padding:30px 20px 10px;">
			<div style="width:46px;height:46px;border-radius:50%;background:#fdf2da;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
				<i class="fas fa-key" style="color:#a9700f;font-size:18px;"></i>
			</div>
			<div style="font-size:15px;font-weight:700;color:#2a2a3d;"><?php echo $spTextAIV['No AI providers configured yet.'] ?? 'No AI providers configured yet.'?></div>
			<div style="font-size:13px;color:#8a8ea3;margin-top:4px;max-width:360px;margin-left:auto;margin-right:auto;"><?php echo $spTextAIV['Add an API key to start asking ChatGPT, Claude, or Gemini what they know about your site.'] ?? 'Add an API key to start asking ChatGPT, Claude, or Gemini what they know about your site.'?></div>
			<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')" class="aiv-discover-cta" style="margin-top:16px;"><?php echo $spTextAIV['Add an API key'] ?? 'Add an API key'?> <i class="fas fa-arrow-right"></i></a>
		</div>
	<?php } else { ?>
		<div style="margin-top:18px;">
			<?php foreach ($configuredProviders as $p) {
				$pColor = $providerColors[$p] ?? '#667eea';
			?>
				<button type="button" class="aiv-subnav-pill" style="cursor:pointer;border-color:<?php echo $pColor?>;color:<?php echo $pColor?>;margin-right:8px;margin-bottom:8px;" onclick="aipAsk('<?php echo $p?>')">
					<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?> <?php echo htmlspecialchars($providerLabels[$p] ?? $p)?>
				</button>
			<?php } ?>
		</div>

		<div id="aip_results" style="margin-top:10px;"></div>
	<?php } ?>
</div>

<div class="aiv-note">
	<i class="fas fa-calendar-check"></i>
	<span>
		<?php echo $spTextAIV['Set up weekly, unattended tracking of your own prompts'] ?? 'Set up weekly, unattended tracking of your own prompts'?>
	</span>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta" style="margin-left:auto;"><?php echo $spTextAIV['Go to Scheduled Tracking'] ?? 'Go to Scheduled Tracking'?> <i class="fas fa-arrow-right"></i></a>
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
