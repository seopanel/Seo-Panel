<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['AI Perception Check'] ?? 'AI Perception Check'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aipHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- same 3-way sub-nav as settings.ctp.php/tracking.ctp.php -->
<div class="aip-subnav" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:15px;">
	<span class="aiv-subnav-pill aiv-subnav-active">
		<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?>
	</span>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')" class="aiv-subnav-pill">
		<i class="fas fa-key"></i> <?php echo $spTextAIV['Setup'] ?? 'Setup'?>
	</a>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-subnav-pill">
		<i class="fas fa-calendar-check"></i> <?php echo $spTextAIV['Scheduled Tracking'] ?? 'Scheduled Tracking'?>
	</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="aipHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('aipHelpModal', this)" aria-label="<?php echo $spText['button']['Close'] ?? 'Close'?>"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-comment-dots"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title"><?php echo $spTextAIV['AI Perception Check'] ?? 'AI Perception Check'?></div>
		<div class="aiv-intro-desc"><?php echo $spTextAIV['Asks real AI assistants - ChatGPT, Claude, Gemini and Perplexity - what they actually know about your site, right now, using your own API key.'] ?? 'Asks real AI assistants - ChatGPT, Claude, Gemini and Perplexity - what they actually know about your site, right now, using your own API key.'?> <a href="javascript:void(0);" data-toggle="modal" data-target="#aipHelpModal" class="aiv-intro-cta"><?php echo $spTextAIV['Learn more'] ?? 'Learn more'?> <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="aipHelpModal" tabindex="-1" role="dialog" aria-labelledby="aipHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-comment-dots"></i></div>
				<h5 class="modal-title" id="aipHelpModalLabel"><?php echo $spTextAIV['About AI Perception Check'] ?? 'About AI Perception Check'?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p><?php echo $spTextAIV['AI Perception Check lets you ask real AI assistants - ChatGPT, Claude, Gemini and Perplexity - what they actually know about your website, right now.'] ?? 'AI Perception Check lets you ask real AI assistants - ChatGPT, Claude, Gemini and Perplexity - what they actually know about your website, right now.'?></p>
				<p><?php echo $spTextAIV['Unlike the rest of AI Visibility, which simulates how AI search engines might answer, this sends a live question straight to the provider you pick, using your own API key. Nothing is cached or guessed.'] ?? 'Unlike the rest of AI Visibility, which simulates how AI search engines might answer, this sends a live question straight to the provider you pick, using your own API key. Nothing is cached or guessed.'?></p>
				<div class="aiv-discover-item">
					<div class="aiv-discover-icon" style="background:linear-gradient(135deg, #4a8fc7 0%, #6faee0 100%);"><i class="fas fa-comment-dots"></i></div>
					<div class="aiv-discover-text"><strong><?php echo $spTextAIV['Ask'] ?? 'Ask'?></strong><small><?php echo $spTextAIV['pick a website and ask any configured AI provider directly what it knows about it.'] ?? 'pick a website and ask any configured AI provider directly what it knows about it.'?></small></div>
				</div>
				<div class="aiv-discover-item">
					<div class="aiv-discover-icon" style="background:linear-gradient(135deg, #e0a23b 0%, #f0c674 100%);"><i class="fas fa-key"></i></div>
					<div class="aiv-discover-text"><strong><?php echo $spTextAIV['Setup'] ?? 'Setup'?></strong><small><?php echo $spTextAIV['add the API key(s) for the providers you want to ask.'] ?? 'add the API key(s) for the providers you want to ask.'?></small></div>
				</div>
				<div class="aiv-discover-item">
					<div class="aiv-discover-icon" style="background:linear-gradient(135deg, #764ba2 0%, #a78bda 100%);"><i class="fas fa-calendar-check"></i></div>
					<div class="aiv-discover-text"><strong><?php echo $spTextAIV['Scheduled Tracking'] ?? 'Scheduled Tracking'?></strong><small><?php echo $spTextAIV['save a prompt once and SEO Panel asks it again automatically every week, so you can watch an answer change over time.'] ?? 'save a prompt once and SEO Panel asks it again automatically every week, so you can watch an answer change over time.'?></small></div>
				</div>
				<div class="aiv-note">
					<i class="fas fa-shield-alt"></i>
					<span><?php echo $spTextAIV['Each "Ask" only runs when you click it. SEO Panel never sees or pays for these calls - they use your own key, directly.'] ?? 'Each "Ask" only runs when you click it. SEO Panel never sees or pays for these calls - they use your own key, directly.'?></span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $spTextAIV['Got it'] ?? 'Got it'?></button>
			</div>
		</div>
	</div>
</div>

<?php if (!empty($noWebsites)) { ?>
	<?php echo showNoRecordsList(0); ?>
<?php } else { ?>

<?php $configuredProviders = array_column($providers, 'provider'); ?>
<?php $providerColors = ['openai' => '#10a37f', 'anthropic' => '#cc785c', 'google' => '#4285f4', 'perplexity' => '#20808d']; ?>
<?php $providerLabels = ['openai' => 'OpenAI (ChatGPT)', 'anthropic' => 'Anthropic (Claude)', 'google' => 'Google (Gemini)', 'perplexity' => 'Perplexity']; ?>

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
			<div style="font-size:13px;color:#8a8ea3;margin-top:4px;max-width:360px;margin-left:auto;margin-right:auto;"><?php echo $spTextAIV['Add an API key to start asking ChatGPT, Claude, Gemini, or Perplexity what they know about your site.'] ?? 'Add an API key to start asking ChatGPT, Claude, Gemini, or Perplexity what they know about your site.'?></div>
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

<?php if (!empty($shareOfVoice) || $shareOfVoice === 0) { ?>
	<!-- same scheduled-tracking Share of Voice card as tracking.ctp.php -
	     only appears once this website actually has scheduled tracking
	     results, so it never shows for a website that's only ever used
	     the one-off "Ask" above -->
	<div class="aiv-card" style="max-width:480px;">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-balance-scale"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Share of Voice vs. Competitors'] ?? 'Share of Voice vs. Competitors'?></div>
		</div>
		<?php
		$compareRows = [['label' => $spTextAIV['You'] ?? 'You', 'share' => $shareOfVoice, 'you' => true]];
		foreach ($competitorShareOfVoice as $c) {
			$compareRows[] = ['label' => $c['name'], 'share' => $c['share'], 'you' => false];
		}
		?>
		<?php foreach ($compareRows as $row) { ?>
			<div style="margin-bottom:10px;">
				<div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:3px;">
					<span style="font-weight:<?php echo $row['you'] ? '700' : '500'; ?>;"><?php echo htmlspecialchars($row['label']); ?></span>
					<span style="color:#8a8ea3;"><?php echo ($row['share'] === null) ? '&mdash;' : intval($row['share']) . '%'; ?></span>
				</div>
				<div style="background:#eef0f5; border-radius:6px; height:10px; overflow:hidden;">
					<div style="height:100%; width:<?php echo intval($row['share'] ?? 0); ?>%; background:<?php echo $row['you'] ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#c3c6d4'; ?>;"></div>
				</div>
			</div>
		<?php } ?>
		<div style="margin-top:4px;">
			<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta" style="font-size:12.5px;"><?php echo $spTextAIV['Go to Scheduled Tracking'] ?? 'Go to Scheduled Tracking'?> <i class="fas fa-arrow-right"></i></a>
		</div>
	</div>
<?php } ?>

<?php if (!empty($shareOfVoiceByProvider)) { ?>
	<!-- the blended Share of Voice card above answers "overall, am I
	     mentioned" - this one answers "which AI engine actually mentions
	     me", since that can vary a lot provider to provider and the
	     blended number alone hides it -->
	<div class="aiv-card" style="max-width:480px;">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-chart-bar"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Share of Voice by AI Engine'] ?? 'Share of Voice by AI Engine'?></div>
		</div>
		<?php foreach ($shareOfVoiceByProvider as $row) {
			$rowColor = $providerColors[$row['provider']] ?? '#667eea';
			$rowLabel = $providerLabels[$row['provider']] ?? $row['provider'];
		?>
			<div style="margin-bottom:10px;">
				<div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:3px;">
					<span style="font-weight:500;"><?php echo htmlspecialchars($rowLabel); ?></span>
					<span style="color:#8a8ea3;"><?php echo intval($row['share']); ?>%</span>
				</div>
				<div style="background:#eef0f5; border-radius:6px; height:10px; overflow:hidden;">
					<div style="height:100%; width:<?php echo intval($row['share']); ?>%; background:<?php echo htmlspecialchars($rowColor); ?>;"></div>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } ?>

<div class="aiv-card" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
	<div class="aiv-card-icon" style="flex-shrink:0;background:linear-gradient(135deg, #764ba2 0%, #a78bda 100%);"><i class="fas fa-calendar-check"></i></div>
	<div style="flex:1;min-width:220px;">
		<div style="font-size:14.5px;font-weight:700;color:#2a2a3d;"><?php echo $spTextAIV['Dont want to ask manually every time?'] ?? "Don't want to ask manually every time?"?></div>
		<div style="font-size:12.5px;color:#8a8ea3;margin-top:2px;"><?php echo $spTextAIV['Save your own prompts once and SEO Panel checks them automatically every week - no need to come back and ask again.'] ?? 'Save your own prompts once and SEO Panel checks them automatically every week - no need to come back and ask again.'?></div>
	</div>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-discover-cta"><?php echo $spTextAIV['Go to Scheduled Tracking'] ?? 'Go to Scheduled Tracking'?> <i class="fas fa-arrow-right"></i></a>
</div>

<script>
// Assigned onto window explicitly, not declared as a plain function -
// this is invoked from an inline onclick="aipAsk(...)" HTML attribute,
// which only ever resolves names via the global scope. This whole
// page is only ever loaded through scriptDoLoad() (js/common.js),
// which runs its <script> tags via a direct eval() nested inside its
// own function scope - a plain `function aipAsk(){}` declared that
// way is scoped to that internal callback, not to window, so the
// button would throw "aipAsk is not defined" (confirmed the same
// class of bug on the intro card's close button elsewhere this
// session - see _styles.ctp.php).
window.aipAsk = function(provider) {
	var websiteId = document.getElementById('aip_website_id').value;
	var resultsEl = document.getElementById('aip_results');
	var providerLabels = {openai: 'OpenAI (ChatGPT)', anthropic: 'Anthropic (Claude)', google: 'Google (Gemini)', perplexity: 'Perplexity'};
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
				bodyEl.textContent = '';
				if (data.grounded) {
					var liveBadge = document.createElement('span');
					liveBadge.className = 'badge badge-info py-1 px-2 text-light';
					liveBadge.style.marginBottom = '8px';
					liveBadge.style.display = 'inline-block';
					liveBadge.innerHTML = '<i class="fas fa-globe"></i> ';
					liveBadge.appendChild(document.createTextNode('<?php echo $spTextAIV['Live search'] ?? 'Live search'?>'));
					bodyEl.appendChild(liveBadge);
					bodyEl.appendChild(document.createElement('br'));
				}
				bodyEl.appendChild(document.createTextNode(data.text));
				// built via DOM methods (textContent/createElement), never
				// innerHTML concatenation, since citation url/title come
				// straight from the third-party provider's own response
				if (data.citations && data.citations.length) {
					var sourcesTitle = document.createElement('div');
					sourcesTitle.style.marginTop = '10px';
					sourcesTitle.style.fontSize = '11px';
					sourcesTitle.style.fontWeight = '700';
					sourcesTitle.style.textTransform = 'uppercase';
					sourcesTitle.style.color = '#8a8ea3';
					sourcesTitle.textContent = '<?php echo $spTextAIV['Sources cited'] ?? 'Sources cited'?>';
					bodyEl.appendChild(sourcesTitle);

					var sourcesList = document.createElement('ul');
					sourcesList.style.margin = '4px 0 0';
					sourcesList.style.paddingLeft = '18px';
					sourcesList.style.fontSize = '13px';
					data.citations.forEach(function(citation) {
						if (!citation.url) return;
						var li = document.createElement('li');
						// only ever an http(s) link - a compromised/malicious
						// response could otherwise hand back a javascript:
						// URI that runs on click
						if (/^https?:\/\//i.test(citation.url)) {
							var a = document.createElement('a');
							a.href = citation.url;
							a.target = '_blank';
							a.rel = 'noopener noreferrer';
							a.textContent = citation.title || citation.url;
							li.appendChild(a);
						} else {
							li.textContent = citation.title || citation.url;
						}
						sourcesList.appendChild(li);
					});
					bodyEl.appendChild(sourcesList);
				}
			} else {
				bodyEl.innerHTML = '<span class="text-danger">' + (data.error || 'Request failed') + '</span>';
			}
		})
		.catch(function() {
			bodyEl.innerHTML = '<span class="text-danger"><?php echo $spTextAIV['Request failed'] ?? 'Request failed.'?></span>';
		});
};
</script>

<?php } ?>
