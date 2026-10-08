<?php
// Embeds into the existing white-label Overall Report (archive.php) -
// same dual-purpose (on-screen fragment / PDF-safe) shape every other
// report section's own *_report_summary.ctp.php already uses. No own
// filter form: website_id/date range always come from the parent
// Overall Report page, matching how $summaryPage=true is the only way
// AIVisibilityController::viewReportSummary() is ever actually called.

if (!$summaryPage && (!empty($printVersion) || !empty($pdfVersion))) {
	$pdfVersion ? showPdfHeader($spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary') : showPrintHeader($spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary');
} else {
	echo showSectionHead($spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary');
}
?>

<table class="search" width="80%">
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td><?php echo htmlspecialchars($fromTime)?> &ndash; <?php echo htmlspecialchars($toTime)?></td>
	</tr>
</table>

<?php if (empty($summaryByWebsite)) { ?>
	<p><?php echo $spTextAIV['No websites to report on.'] ?? 'No websites to report on.'?></p>
<?php } else { ?>
	<?php foreach ($summaryByWebsite as $summary) { ?>
		<h4 style="margin-top:18px;"><?php echo htmlspecialchars($summary['name'])?></h4>
		<table class="cust_tab" width="100%" cellpadding="6" style="border-collapse:collapse;">
			<tr>
				<th style="border:1px solid #ddd;text-align:left;"><?php echo $spTextAIV['Signal'] ?? 'Signal'?></th>
				<th style="border:1px solid #ddd;text-align:left;"><?php echo $spTextAIV['Score'] ?? 'Score'?></th>
				<th style="border:1px solid #ddd;text-align:left;"><?php echo $spText['common']['Details'] ?? 'Details'?></th>
			</tr>
			<tr>
				<td style="border:1px solid #ddd;"><strong><?php echo $spTextAIV['AI Visibility Score'] ?? 'AI Visibility Score'?></strong></td>
				<td style="border:1px solid #ddd;"><strong><?php echo $summary['score']['overall'] !== null ? intval($summary['score']['overall']) . '%' : '&mdash;'?></strong></td>
				<td style="border:1px solid #ddd;"><?php echo $spTextAIV['blends every AI-era signal this panel tracks into one number'] ?? 'blends every AI-era signal this panel tracks into one number'?></td>
			</tr>
			<?php foreach ($summary['score']['components'] as $component) { ?>
				<tr>
					<td style="border:1px solid #ddd;"><?php echo htmlspecialchars($spTextAIV[$component['label']] ?? $component['label'])?></td>
					<td style="border:1px solid #ddd;"><?php echo $component['measured'] ? intval($component['score']) . '%' : '&mdash;'?></td>
					<td style="border:1px solid #ddd;"><?php echo htmlspecialchars($component['detail'])?></td>
				</tr>
			<?php } ?>
		</table>

		<table class="cust_tab" width="100%" cellpadding="6" style="border-collapse:collapse;margin-top:8px;">
			<tr>
				<th style="border:1px solid #ddd;text-align:left;" colspan="3"><?php echo $spTextAIV['AI Referral ROI'] ?? 'AI Referral ROI'?></th>
			</tr>
			<?php if (empty($summary['roi']['configured'])) { ?>
				<tr>
					<td style="border:1px solid #ddd;" colspan="3"><?php echo $spTextAIV['Google Analytics not connected for this website.'] ?? 'Google Analytics not connected for this website.'?></td>
				</tr>
			<?php } else { ?>
				<tr>
					<td style="border:1px solid #ddd;"><?php echo $spTextAIV['Sessions from AI Platforms'] ?? 'Sessions from AI Platforms'?></td>
					<td style="border:1px solid #ddd;" colspan="2"><?php echo number_format($summary['roi']['sessions'])?></td>
				</tr>
				<tr>
					<td style="border:1px solid #ddd;"><?php echo $spTextAIV['Conversions from AI Platforms'] ?? 'Conversions from AI Platforms'?></td>
					<td style="border:1px solid #ddd;" colspan="2"><?php echo number_format($summary['roi']['conversions'])?></td>
				</tr>
				<tr>
					<td style="border:1px solid #ddd;"><?php echo $spTextAIV['Conversion Rate'] ?? 'Conversion Rate'?></td>
					<td style="border:1px solid #ddd;" colspan="2"><?php echo $summary['roi']['conversionRate'] !== null ? $summary['roi']['conversionRate'] . '%' : '&mdash;'?></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
<?php } ?>

<?php
if (!$summaryPage && (!empty($printVersion) || !empty($pdfVersion))) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>
