<?php
// Rendered with no layout (see showResultDetail()) - loaded into the
// shared #ajaxModalSP (js/popup.js), not a full page. $result here is the
// raw llm_perception_results row (joined with its own prompt_text),
// never trimmed/summarized - this is specifically the "what did the AI
// actually say" view the tracking table itself has no room for.
$isError = strpos($result['response_text'], 'ERROR:') === 0;

// Every provider here (OpenAI/Anthropic/Google) replies in loose
// Markdown - **bold** was showing as literal asterisks since this was
// just htmlspecialchars()'d into a <pre>. Escape FIRST (this is
// third-party, untrusted text), then turn only the escaped text's own
// **pairs** into real <strong> tags - safe regardless of what the raw
// response contains, since anything that looks like a tag has already
// been neutralized to harmless text (&lt;...&gt;) by the time this runs.
function aipFormatResponse($text) {
    $escaped = htmlspecialchars($text);
    return preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
}
?>
<div style="padding:4px 2px;">
	<div style="font-size:11px;color:#8a8ea3;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">
		<?php echo $spTextAIV['Prompt'] ?? 'Prompt'?>
	</div>
	<div style="font-size:14px;color:#2a2a3d;margin-bottom:16px;">
		<?php echo htmlspecialchars($result['prompt_text'])?>
	</div>

	<div style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:16px;font-size:12.5px;color:#5a6372;">
		<div><strong><?php echo $spTextAIV['Provider'] ?? 'Provider'?>:</strong> <?php echo htmlspecialchars($result['provider'])?></div>
		<div><strong><?php echo $spText['common']['Date'] ?? 'Checked'?>:</strong> <?php echo htmlspecialchars($result['checked_date'])?></div>
		<?php if (!empty($result['grounded'])) { ?>
			<div><span class="badge badge-info py-1 px-2 text-light" title="<?php echo htmlspecialchars($spTextAIV['This check used the provider live web search, not just its training-data recall.'] ?? 'This check used the provider live web search, not just its training-data recall.')?>"><i class="fas fa-globe"></i> <?php echo $spTextAIV['Live search'] ?? 'Live search'?></span></div>
		<?php } ?>
		<?php if (!$isError) { ?>
			<div>
				<strong><?php echo $spTextAIV['Mentioned'] ?? 'Mentioned'?>:</strong>
				<?php if (!empty($result['mentioned'])) { ?>
					<span class="badge badge-success py-1 px-2 text-light"><?php echo $spTextAIV['Mentioned'] ?? 'Mentioned'?></span>
					<?php
					$sentimentLabels = [
						'positive' => $spTextAIV['Positive'] ?? 'Positive',
						'neutral'  => $spTextAIV['Neutral'] ?? 'Neutral',
						'negative' => $spTextAIV['Negative'] ?? 'Negative',
					];
					?>
					<?php if (!empty($result['sentiment']) && !empty($sentimentLabels[$result['sentiment']])) { ?>
						<span style="margin-left:4px;color:#8a8ea3;">(<?php echo htmlspecialchars($sentimentLabels[$result['sentiment']])?>)</span>
					<?php } ?>
					<?php
					$prominenceLabels = [
						'early' => $spTextAIV['early'] ?? 'early',
						'mid'   => $spTextAIV['mid'] ?? 'mid',
						'late'  => $spTextAIV['late'] ?? 'late',
					];
					?>
					<?php if (!empty($result['prominence']) && !empty($prominenceLabels[$result['prominence']])) { ?>
						<span style="margin-left:4px;color:#adb5bd;" title="<?php echo htmlspecialchars($spTextAIV['How far into the response this mention first appears.'] ?? 'How far into the response this mention first appears.')?>">(<?php echo htmlspecialchars($prominenceLabels[$result['prominence']])?> <?php echo $spTextAIV['mention'] ?? 'mention'?>)</span>
					<?php } ?>
				<?php } else { ?>
					<span class="badge badge-secondary py-1 px-2 text-light"><?php echo $spTextAIV['Not mentioned'] ?? 'Not mentioned'?></span>
				<?php } ?>
			</div>
		<?php } ?>
	</div>

	<div style="font-size:11px;color:#8a8ea3;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">
		<?php echo $isError ? ($spTextAIV['Error'] ?? 'Error') : ($spTextAIV['AI Response'] ?? 'AI Response')?>
	</div>
	<?php if ($isError) { ?>
		<div style="background:#fdeeee;border:1px solid #f3c6c6;border-radius:8px;padding:12px 14px;color:#c0392b;font-size:13px;">
			<?php echo htmlspecialchars(substr($result['response_text'], strlen('ERROR:')))?>
		</div>
	<?php } else { ?>
		<pre style="white-space:pre-wrap;word-break:break-word;background:#fafbfd;border:1px solid #e8edf5;border-radius:8px;padding:14px 16px;font-family:inherit;font-size:13px;color:#2a2a3d;max-height:50vh;overflow-y:auto;margin:0;"><?php echo aipFormatResponse($result['response_text'])?></pre>

		<?php if (!empty($citations)) { ?>
			<div style="font-size:11px;color:#8a8ea3;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin:16px 0 4px;">
				<?php echo $spTextAIV['Sources cited'] ?? 'Sources cited'?>
			</div>
			<ul style="margin:0;padding-left:18px;font-size:13px;">
				<?php foreach ($citations as $citation) {
					if (empty($citation['url'])) continue;
					// only ever an http(s) link - htmlspecialchars() alone
					// doesn't stop a javascript: URI in href, and this is
					// third-party provider data, not something SEO Panel
					// generated itself
					$citationIsHttp = preg_match('#^https?://#i', $citation['url']);
					$citationLabel = !empty($citation['title']) ? $citation['title'] : $citation['url'];
				?>
					<li style="margin-bottom:4px;">
						<?php if ($citationIsHttp) { ?>
							<a href="<?php echo htmlspecialchars($citation['url'], ENT_QUOTES)?>" target="_blank" rel="noopener noreferrer">
								<?php echo htmlspecialchars($citationLabel)?>
							</a>
						<?php } else { ?>
							<?php echo htmlspecialchars($citationLabel)?>
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>
	<?php } ?>
</div>
