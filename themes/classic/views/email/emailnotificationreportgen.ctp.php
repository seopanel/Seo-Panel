<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body style="margin:0; padding:0; background-color:#f4f5f8;">
<table cellspacing="0" cellpadding="0" width="100%" style="background-color:#f4f5f8;">
<tr><td align="center" style="padding:20px 12px;">
<table cellspacing="0" cellpadding="0" width="820" style="max-width:820px; width:100%; font-family:Tahoma, verdana, arial, sans-serif; font-size:13px; color:#333333;">
<tr><td>
<?php
// wider than the other digest emails (600px) - this one carries genuinely
// wide tabular data (Keyword Position Summary gets 3 columns per search
// engine tracked), and .table-responsive (see emailhead.ctp.php) only
// keeps overflow contained, not pretty, so a wider card first reduces how
// often that scrollbar is actually needed
$emailBannerSubtitle = $reportTexts['Your scheduled report'] ?? 'Your scheduled report';
include(SP_VIEWPATH.'/email/_email_banner.ctp.php');
?>
<?php echo $commonTexts['Hello']?> <?php echo htmlspecialchars($name)?>,<br><br>

<?php echo $reportTexts['report_email_body_text1']?><br><br>

<?php echo $reportContent; ?>

<br>
<?php
$reportLink = SP_WEBPATH."/admin-panel.php?menu_selected=report-manager&start_script=archive&website_id=0";
echo str_replace('[LOGIN_LINK]', "<a href='$reportLink' style='color:#5c4fd6;'>{$loginTexts['Login']}</a>", $reportTexts['report_email_body_text2']);
?>
<?php include(SP_VIEWPATH.'/email/_email_footer.ctp.php'); ?>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
