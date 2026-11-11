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
	<?php } ?>
</div>
