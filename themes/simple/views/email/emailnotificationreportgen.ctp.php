<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body>
<?php echo $commonTexts['Hello']?> <?php echo $name?>,<br><br>

<?php echo $reportTexts['report_email_body_text1']?><br><br><br><br>

<?php echo $reportContent; ?>

<br><br><br><br>
<?php
$reportLink = SP_WEBPATH."/admin-panel.php?menu_selected=report-manager&start_script=archive&website_id=0";
echo str_replace('[LOGIN_LINK]', "<a href='$reportLink'>{$loginTexts['Login']}</a>", $reportTexts['report_email_body_text2']); ?><br><br>

<table cellspacing="0" cellpadding="0" width="100%">
	<tbody>
		<tr style="height: 11px;">
			<td style="vertical-align: middle; margin: 0pt;" colspan="2">
			<hr
				style="margin: 5px 0pt; background-color: rgb(0, 0, 0); color: rgb(0, 0, 0); height: 1px;">
			</td>
		</tr>
		<tr style="height: 20px;">
			<td style="vertical-align: middle; font-size: 11px; padding: 5px; margin: 0pt;">
			<div style="word-wrap: break-word;">
				<p style="font-size: 11px; color: rgb(169, 169, 169);"><?php echo str_replace('[year]', date('Y'), $spText['common']['copyright']); ?></p>
			</div>
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>