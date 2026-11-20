<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body style="margin:0; padding:0; background-color:#f4f5f8;">
<table cellspacing="0" cellpadding="0" width="100%" style="background-color:#f4f5f8;">
<tr><td align="center" style="padding:20px 12px;">
<table cellspacing="0" cellpadding="0" width="600" style="max-width:600px; width:100%; font-family:Tahoma, verdana, arial, sans-serif; font-size:13px; color:#333333;">
<tr><td>
<?php
$emailBannerSubtitle = $aivTexts['AI Visibility digest'] ?? 'AI Visibility digest';
include(SP_VIEWPATH.'/email/_email_banner.ctp.php');
?>
<?php echo $commonTexts['Hello']?> <?php echo htmlspecialchars($name)?>,<br><br>

<?php echo $aivTexts['ai_visibility_email_body_intro']?><br><br>

<?php foreach ($summaryByWebsite as $websiteId => $s) {
    $websiteLink = SP_WEBPATH . "/aivisibility.php?sec=overview&website_id=" . intval($websiteId);
    $aioRate = !empty($s['aio']['present']) ? round((intval($s['aio']['cited']) / intval($s['aio']['present'])) * 100) : null;
    ?>
    <table cellspacing="0" cellpadding="0" width="100%" style="margin-bottom:16px;border-collapse:collapse;font-family:Tahoma, verdana, arial, sans-serif;">
        <tr>
            <td colspan="3" style="background-color:#DAE5EE;border-bottom:1px solid #B0C2CC;padding:8px 12px;font-size:13px;font-weight:bold;">
                <a href="<?php echo $websiteLink?>" style="text-decoration:none;color:#333333;"><?php echo htmlspecialchars($s['name'])?></a>
            </td>
        </tr>
        <tr>
            <td width="33%" style="background-color:#F4F7FA;padding:14px 12px;text-align:center;border-right:1px solid #ffffff;">
                <span style="font-size:22px;font-weight:bold;color:#5c4fd6;"><?php echo intval($s['referrals'])?></span><br>
                <span style="font-size:11px;color:#777777;"><?php echo $aivTexts['AI Referrals'] ?? 'AI Referrals'?></span>
            </td>
            <td width="33%" style="background-color:#F4F7FA;padding:14px 12px;text-align:center;border-right:1px solid #ffffff;">
                <span style="font-size:22px;font-weight:bold;color:#5c4fd6;"><?php echo intval($s['bot_hits'])?></span><br>
                <span style="font-size:11px;color:#777777;"><?php echo $aivTexts['AI Bot Crawls'] ?? 'AI Bot Crawls'?></span>
            </td>
            <td width="34%" style="background-color:#F4F7FA;padding:14px 12px;text-align:center;">
                <span style="font-size:22px;font-weight:bold;color:#5c4fd6;"><?php echo $aioRate !== null ? $aioRate . '%' : 'N/A'?></span><br>
                <span style="font-size:11px;color:#777777;"><?php echo $aivTexts['AI Overview Citations'] ?? 'AI Overview Citations'?></span>
            </td>
        </tr>
    </table>
    <?php
}
?>

<?php
$loginLink = SP_WEBPATH . "/websites.php";
echo str_replace('[LOGIN_LINK]', "<a href='$loginLink' style='color:#5c4fd6;'>{$loginTexts['Login']}</a>", $aivTexts['ai_visibility_email_body_outro']);
?>
<?php include(SP_VIEWPATH.'/email/_email_footer.ctp.php'); ?>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
