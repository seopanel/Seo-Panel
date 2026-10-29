<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['AI Perception Tracking'] ?? 'AI Perception Tracking'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aipHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- same 3-way sub-nav as settings.ctp.php/check.ctp.php -->
<div class="aip-subnav" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:15px;">
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=check', 'content', '&website_id=<?php echo intval($websiteId)?>')" class="aiv-subnav-pill">
		<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?>
	</a>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')" class="aiv-subnav-pill">
		<i class="fas fa-key"></i> <?php echo $spTextAIV['Setup'] ?? 'Setup'?>
	</a>
	<span class="aiv-subnav-pill aiv-subnav-active">
		<i class="fas fa-calendar-check"></i> <?php echo $spTextAIV['Scheduled Tracking'] ?? 'Scheduled Tracking'?>
	</span>
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
				<p><?php echo $spTextAIV['AI Perception Check lets you ask real AI assistants - ChatGPT, Claude and Gemini - what they actually know about your website, right now.'] ?? 'AI Perception Check lets you ask real AI assistants - ChatGPT, Claude and Gemini - what they actually know about your website, right now.'?></p>
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

<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select class="custom-select" onchange="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id='+this.value)">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo htmlspecialchars($websiteInfo['name'])?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>

<div class="aiv-note">
	<i class="fas fa-info-circle"></i>
	<span>
		<?php echo $spTextAIV['You can track up to'] ?? 'You can track up to'?> <?php echo intval($promptCap)?>
		<?php echo $spTextAIV['prompts per website, checked at most once every'] ?? 'prompts per website, checked at most once every'?> <?php echo intval($trackingIntervalDays)?>
		<?php echo $spTextAIV['days, against every AI provider you have configured a key for.'] ?? 'days, against every AI provider you have configured a key for.'?>
	</span>
</div>

<?php if (!empty($shareOfVoice) || $shareOfVoice === 0) { ?>
	<div class="aiv-card" style="max-width:480px;">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-balance-scale"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Share of Voice vs. Competitors'] ?? 'Share of Voice vs. Competitors'?></div>
		</div>
		<?php
		// same bar for "you" and every tracked competitor, so the
		// comparison reads at a glance - a null share (competitor with no
		// backfilled/checked data yet) is shown as a dash, not 0%, since
		// those are different things (0% = checked and never mentioned;
		// null = nothing checked yet)
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
	</div>
<?php } ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-users"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Tracked Competitors'] ?? 'Tracked Competitors'?></div>
	</div>

	<?php if (empty($competitors)) { ?>
		<?php echo showNoRecordsList(0); ?>
	<?php } else { ?>
		<table class="aiv-table">
			<tr>
				<th><?php echo $spText['common']['Name'] ?? 'Name'?></th>
				<th><?php echo $spTextAIV['Domain'] ?? 'Domain'?></th>
				<th style="width:10%"><?php echo $spText['common']['Action'] ?? 'Action'?></th>
			</tr>
			<?php foreach ($competitors as $competitor) { ?>
				<tr>
					<td><?php echo htmlspecialchars($competitor['name'])?></td>
					<td><?php echo htmlspecialchars($competitor['domain'] ?? '')?></td>
					<td>
						<form id="aip_remove_competitor_form_<?php echo $competitor['id']?>" onsubmit="return false;">
							<input type="hidden" name="sec" value="remove-competitor">
							<input type="hidden" name="competitor_id" value="<?php echo $competitor['id']?>">
						</form>
						<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_competitor_form_<?php echo $competitor['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger btn-sm">
							<?php echo $spText['button']['Delete'] ?? 'Delete'?>
						</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>

	<?php if (count($competitors) < $competitorCap) { ?>
		<form id="aip_add_competitor_form" onsubmit="return false;" style="margin-top:15px;">
			<input type="hidden" name="sec" value="add-competitor">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
			<input type="text" name="name" class="form-control" style="max-width:220px;display:inline-block;" maxlength="150" placeholder="<?php echo $spTextAIV['Competitor name'] ?? 'Competitor name'?>">
			<input type="text" name="domain" class="form-control" style="max-width:220px;display:inline-block;" maxlength="255" placeholder="<?php echo $spTextAIV['Domain (optional)'] ?? 'Domain (optional)'?>">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('ai-perception.php', 'aip_add_competitor_form', 'content')" class="btn btn-primary">
				<?php echo $spTextAIV['Add Competitor'] ?? 'Add Competitor'?>
			</a>
		</form>
		<div style="font-size:12px; color:#8a8ea3; margin-top:8px;">
			<?php echo $spTextAIV['Checked against your existing tracked prompts using the same responses already fetched - no extra AI calls.'] ?? 'Checked against your existing tracked prompts using the same responses already fetched - no extra AI calls.'?>
		</div>
	<?php } ?>
</div>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-list"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Tracked Prompts'] ?? 'Tracked Prompts'?></div>
	</div>

	<?php if (empty($prompts)) { ?>
		<?php echo showNoRecordsList(0); ?>
	<?php } else { ?>
		<table class="aiv-table">
			<tr>
				<th><?php echo $spText['common']['Prompt'] ?? 'Prompt'?></th>
				<th><?php echo $spText['common']['Provider'] ?? 'Provider'?></th>
				<th><?php echo $spText['common']['Status'] ?? 'Status'?></th>
				<th><?php echo $spText['common']['Date'] ?? 'Last Checked'?></th>
				<th style="width:10%"><?php echo $spText['common']['Action'] ?? 'Action'?></th>
			</tr>
			<?php foreach ($prompts as $prompt) { ?>
				<?php $rowspan = max(1, count($prompt['results'])); ?>
				<?php if (empty($prompt['results'])) { ?>
					<tr>
						<td><?php echo htmlspecialchars($prompt['prompt_text'])?></td>
						<td colspan="3"><em style="color:#adb5bd;"><?php echo $spTextAIV['Not checked yet'] ?? 'Not checked yet'?></em></td>
						<td>
							<form id="aip_remove_prompt_form_<?php echo $prompt['id']?>" onsubmit="return false;">
								<input type="hidden" name="sec" value="remove-prompt">
								<input type="hidden" name="prompt_id" value="<?php echo $prompt['id']?>">
							</form>
							<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_prompt_form_<?php echo $prompt['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger btn-sm">
								<?php echo $spText['button']['Delete'] ?? 'Delete'?>
							</a>
						</td>
					</tr>
				<?php } else { ?>
					<?php foreach ($prompt['results'] as $i => $result) { ?>
						<tr>
							<?php if ($i === 0) { ?>
								<td rowspan="<?php echo $rowspan?>"><?php echo htmlspecialchars($prompt['prompt_text'])?></td>
							<?php } ?>
							<td><?php echo htmlspecialchars($result['provider'])?></td>
							<td>
								<?php if (!empty($result['mentioned'])) { ?>
									<span class="badge badge-success py-1 px-2 text-light"><?php echo $spTextAIV['Mentioned'] ?? 'Mentioned'?></span>
									<?php
									$sentimentIcons = [
										'positive' => ['icon' => 'fa-thumbs-up', 'color' => '#28a745', 'label' => $spTextAIV['Positive'] ?? 'Positive'],
										'neutral'  => ['icon' => 'fa-minus', 'color' => '#8a8ea3', 'label' => $spTextAIV['Neutral'] ?? 'Neutral'],
										'negative' => ['icon' => 'fa-thumbs-down', 'color' => '#dc3545', 'label' => $spTextAIV['Negative'] ?? 'Negative'],
									];
									$sentimentInfo = $sentimentIcons[$result['sentiment'] ?? ''] ?? null;
									?>
									<?php if (!empty($sentimentInfo)) { ?>
										<i class="fas <?php echo $sentimentInfo['icon']?>" style="color:<?php echo $sentimentInfo['color']?>; margin-left:6px;" title="<?php echo htmlspecialchars($sentimentInfo['label'])?>"></i>
									<?php } ?>
								<?php } else { ?>
									<span class="badge badge-secondary py-1 px-2 text-light"><?php echo $spTextAIV['Not mentioned'] ?? 'Not mentioned'?></span>
								<?php } ?>
							</td>
							<td><?php echo htmlspecialchars($result['checked_date'])?></td>
							<?php if ($i === 0) { ?>
								<td rowspan="<?php echo $rowspan?>">
									<form id="aip_remove_prompt_form_<?php echo $prompt['id']?>" onsubmit="return false;">
										<input type="hidden" name="sec" value="remove-prompt">
										<input type="hidden" name="prompt_id" value="<?php echo $prompt['id']?>">
									</form>
									<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_prompt_form_<?php echo $prompt['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger btn-sm">
										<?php echo $spText['button']['Delete'] ?? 'Delete'?>
									</a>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</table>
	<?php } ?>

	<?php if (count($prompts) < $promptCap) { ?>
		<form id="aip_add_prompt_form" onsubmit="return false;" style="margin-top:15px;">
			<input type="hidden" name="sec" value="add-prompt">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
			<input type="text" name="prompt_text" class="form-control" style="max-width:420px;display:inline-block;" maxlength="500" placeholder="<?php echo $spTextAIV['Add a prompt to track weekly (e.g. "best CRM for small business")'] ?? 'Add a prompt to track weekly (e.g. "best CRM for small business")'?>">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('ai-perception.php', 'aip_add_prompt_form', 'content')" class="btn btn-primary">
				<?php echo $spTextAIV['Add Prompt'] ?? 'Add Prompt'?>
			</a>
		</form>

		<?php $trackingConfiguredProviders = array_column($providers, 'provider'); ?>
		<?php if (!empty($trackingConfiguredProviders)) { ?>
			<div style="margin-top:12px;">
				<span style="font-size:12px; color:#8a8ea3;"><?php echo $spTextAIV["Not sure what to track? Let AI suggest prompts:"] ?? "Not sure what to track? Let AI suggest prompts:"?></span><br>
				<?php
				$trackingProviderLabels = ['openai' => 'OpenAI (ChatGPT)', 'anthropic' => 'Anthropic (Claude)', 'google' => 'Google (Gemini)'];
				foreach ($trackingConfiguredProviders as $tp) {
				?>
					<button type="button" class="btn btn-secondary btn-sm" style="margin-top:6px;margin-right:6px;" onclick="aipSuggestPrompts('<?php echo $tp?>')">
						<i class="fas fa-lightbulb"></i> <?php echo htmlspecialchars($trackingProviderLabels[$tp] ?? $tp)?>
					</button>
				<?php } ?>
			</div>
			<div id="aip_suggestions" style="margin-top:10px;"></div>
		<?php } ?>
	<?php } ?>
</div>

<script>
function aipSuggestPrompts(provider) {
	var websiteId = <?php echo intval($websiteId)?>;
	var box = document.getElementById('aip_suggestions');
	box.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $spTextAIV['Asking...'] ?? 'Asking...'?>';

	fetch('ai-perception.php?sec=suggest-prompts&website_id=' + encodeURIComponent(websiteId) + '&provider=' + encodeURIComponent(provider), { credentials: 'same-origin' })
		.then(function(res) { return res.json(); })
		.then(function(data) {
			if (!data.ok) {
				box.innerHTML = '<span class="text-danger">' + (data.error || 'Request failed') + '</span>';
				return;
			}
			if (!data.suggestions || !data.suggestions.length) {
				box.innerHTML = '<span class="text-muted"><?php echo $spTextAIV['No suggestions returned - try a different provider.'] ?? 'No suggestions returned - try a different provider.'?></span>';
				return;
			}
			var html = '';
			data.suggestions.forEach(function(s) {
				html += '<div style="display:flex; justify-content:space-between; align-items:center; padding:6px 10px; border:1px solid #eef0f5; border-radius:6px; margin-bottom:6px;">'
					+ '<span style="font-size:13px;">' + s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>'
					+ '<a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="aipAddSuggestedPrompt(this)" data-prompt="' + encodeURIComponent(s) + '"><i class="fas fa-plus"></i></a>'
					+ '</div>';
			});
			box.innerHTML = html;
		})
		.catch(function() {
			box.innerHTML = '<span class="text-danger"><?php echo $spTextAIV['Request failed'] ?? 'Request failed.'?></span>';
		});
}

function aipAddSuggestedPrompt(el) {
	var text = decodeURIComponent(el.getAttribute('data-prompt'));
	document.querySelector('#aip_add_prompt_form input[name="prompt_text"]').value = text;
	scriptDoLoadPost('ai-perception.php', 'aip_add_prompt_form', 'content');
}
</script>

<?php if (empty($providers)) { ?>
	<div class="aiv-note aiv-note-warn">
		<i class="fas fa-exclamation-triangle"></i>
		<span>
			<?php echo $spTextAIV['No AI providers configured yet.'] ?? 'No AI providers configured yet.'?>
			<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')"><?php echo $spTextAIV['Add an API key'] ?? 'Add an API key'?></a>
		</span>
	</div>
<?php } ?>

<?php } ?>
