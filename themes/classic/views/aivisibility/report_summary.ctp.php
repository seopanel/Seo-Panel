<?php
// Embeds into the existing white-label Overall Report (archive.php) -
// same dual-purpose (on-screen fragment / PDF-safe) shape every other
// report section's own *_report_summary.ctp.php already uses. No own
// filter form: website_id/date range always come from the parent
// Overall Report page, matching how $summaryPage=true is the only way
// AIVisibilityController::viewReportSummary() is ever actually called -
// in practice that's always the emailed report, so every style here is
// a plain inline attribute rather than a class, matching the rest of
// email/*.ctp.php - an email client only ever keeps what's inlined or
// in the email's own <style> block (email/emailhead.ctp.php), never an
// external stylesheet, so a class-only style here would render as
// plain unstyled text exactly like this did before this rewrite.

if (!$summaryPage && (!empty($printVersion) || !empty($pdfVersion))) {
	$pdfVersion ? showPdfHeader($spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary') : showPrintHeader($spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary');
} else {
	?>
	<div style="background-color:#DAE5EE;border-bottom:1px solid #B0C2CC;padding:8px 12px;font-weight:bold;font-size:14px;color:#343333;font-family:Tahoma, verdana, arial, sans-serif;">
		<?php echo $spTextAIV['AI Visibility Report Summary'] ?? 'AI Visibility Report Summary'?>
	</div>
	<?php
}
?>

<div style="font-size:12px;color:#777777;padding:8px 0 4px;">
	<?php echo $spText['common']['Period']?>: <?php echo htmlspecialchars($fromTime)?> <?php echo $spText['common']['to'] ?? 'to'?> <?php echo htmlspecialchars($toTime)?>
</div>

<?php if (empty($summaryByWebsite)) { ?>
	<p style="font-size:12px;color:#777777;"><?php echo $spTextAIV['No websites to report on.'] ?? 'No websites to report on.'?></p>
<?php } else { ?>
	<?php foreach ($summaryByWebsite as $summary) { ?>
		<div style="font-weight:bold;font-size:13px;color:#333333;margin-top:16px;padding-bottom:4px;"><?php echo htmlspecialchars($summary['name'])?></div>

		<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-family:Tahoma, verdana, arial, sans-serif;">
			<tr style="background-color:#DAE5EE;border-bottom:1px solid #B0C2CC;">
				<td style="padding:6px 8px;font-weight:bold;font-size:11px;color:#343333;border-right:1px solid #B0C2CC;"><?php echo $spTextAIV['Signal'] ?? 'Signal'?></td>
				<td style="padding:6px 8px;font-weight:bold;font-size:11px;color:#343333;border-right:1px solid #B0C2CC;"><?php echo $spTextAIV['Score'] ?? 'Score'?></td>
				<td style="padding:6px 8px;font-weight:bold;font-size:11px;color:#343333;"><?php echo $spText['common']['Details'] ?? 'Details'?></td>
			</tr>
			<tr style="background-color:#F4F7FA;">
				<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><strong><?php echo $spTextAIV['AI Visibility Score'] ?? 'AI Visibility Score'?></strong></td>
				<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><strong><?php echo $summary['score']['overall'] !== null ? intval($summary['score']['overall']) . '%' : 'N/A'?></strong></td>
				<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo $spTextAIV['blends every AI-era signal this panel tracks into one number'] ?? 'blends every AI-era signal this panel tracks into one number'?></td>
			</tr>
			<?php $rowNum = 0; foreach ($summary['score']['components'] as $component) { $rowNum++; ?>
				<tr style="background-color:<?php echo ($rowNum % 2 == 1) ? '#FFFFFF' : '#F4F7FA'?>;">
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo htmlspecialchars($spTextAIV[$component['label']] ?? $component['label'])?></td>
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo $component['measured'] ? intval($component['score']) . '%' : 'N/A'?></td>
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo htmlspecialchars($component['detail'])?></td>
				</tr>
			<?php } ?>
		</table>

		<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-family:Tahoma, verdana, arial, sans-serif;margin-top:10px;">
			<tr style="background-color:#DAE5EE;border-bottom:1px solid #B0C2CC;">
				<td colspan="2" style="padding:6px 8px;font-weight:bold;font-size:11px;color:#343333;"><?php echo $spTextAIV['AI Referral ROI'] ?? 'AI Referral ROI'?></td>
			</tr>
			<?php if (empty($summary['roi']['configured'])) { ?>
				<tr style="background-color:#F4F7FA;">
					<td colspan="2" style="padding:6px 8px;font-size:11px;color:#343333;"><?php echo $spTextAIV['Google Analytics not connected for this website.'] ?? 'Google Analytics not connected for this website.'?></td>
				</tr>
			<?php } else { ?>
				<tr style="background-color:#F4F7FA;">
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo $spTextAIV['Sessions from AI Platforms'] ?? 'Sessions from AI Platforms'?></td>
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo number_format($summary['roi']['sessions'])?></td>
				</tr>
				<tr style="background-color:#FFFFFF;">
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo $spTextAIV['Conversions from AI Platforms'] ?? 'Conversions from AI Platforms'?></td>
					<td style="padding:6px 8px;font-size:11px;color:#343333;border-bottom:1px solid #e3e3e3;"><?php echo number_format($summary['roi']['conversions'])?></td>
				</tr>
				<tr style="background-color:#F4F7FA;">
					<td style="padding:6px 8px;font-size:11px;color:#343333;"><?php echo $spTextAIV['Conversion Rate'] ?? 'Conversion Rate'?></td>
					<td style="padding:6px 8px;font-size:11px;color:#343333;"><?php echo $summary['roi']['conversionRate'] !== null ? $summary['roi']['conversionRate'] . '%' : 'N/A'?></td>
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
