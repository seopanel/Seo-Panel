<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextAIV['AI Perception Check'] ?? 'AI Perception Check'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aipHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> <?php echo $spTextAIV['What does this do?'] ?? 'What does this do?'?></a>
</div>

<!-- 3-way sub-nav shared across settings/check/tracking.ctp.php - each
     page previously only linked forward to the next step (settings ->
     check -> tracking), with no way back except a conditional "Add an
     API key" link, and no direct settings <-> tracking link at all. -->
<div class="aip-subnav" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:15px;">
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=check', 'content')" class="aiv-subnav-pill">
		<i class="fas fa-comment-dots"></i> <?php echo $spTextAIV['Ask'] ?? 'Ask'?>
	</a>
	<span class="aiv-subnav-pill aiv-subnav-active">
		<i class="fas fa-key"></i> <?php echo $spTextAIV['Setup'] ?? 'Setup'?>
	</span>
	<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php?sec=tracking', 'content')" class="aiv-subnav-pill">
		<i class="fas fa-calendar-check"></i> <?php echo $spTextAIV['Scheduled Tracking'] ?? 'Scheduled Tracking'?>
	</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="aipHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('aipHelpModal', this)" aria-label="<?php echo $spText['button']['Close'] ?? 'Close'?>"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-comment-dots"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title"><?php echo $spTextAIV['AI Perception Check'] ?? 'AI Perception Check'?></div>
		<div class="aiv-intro-desc"><?php echo $spTextAIV['Asks real AI assistants - ChatGPT, Claude and Gemini - what they actually know about your site, right now, using your own API key.'] ?? 'Asks real AI assistants - ChatGPT, Claude and Gemini - what they actually know about your site, right now, using your own API key.'?> <a href="javascript:void(0);" data-toggle="modal" data-target="#aipHelpModal" class="aiv-intro-cta"><?php echo $spTextAIV['Learn more'] ?? 'Learn more'?> <i class="fas fa-arrow-right"></i></a></div>
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

<div class="aiv-note aiv-note-warn">
	<i class="fas fa-shield-alt"></i>
	<span><?php echo $spTextAIV['perceptionprivacynotice'] ?? 'Unlike the rest of AI Visibility, this feature sends your website\'s name and URL directly to the AI provider you choose below, using the API key you enter. It only runs when you click "Ask", using a key you supply - nothing is sent automatically, and SEO Panel never sees or pays for these calls.'?></span>
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
