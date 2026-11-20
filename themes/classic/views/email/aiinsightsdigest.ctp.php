<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body style="margin:0; padding:0; background-color:#f4f5f8;">
<table cellspacing="0" cellpadding="0" width="100%" style="background-color:#f4f5f8;">
<tr><td align="center" style="padding:20px 12px;">
<table cellspacing="0" cellpadding="0" width="600" style="max-width:600px; width:100%; font-family:Tahoma, verdana, arial, sans-serif; font-size:13px; color:#333333;">
<tr><td>
<?php
$emailBannerSubtitle = $aiTexts['AI Insights digest'] ?? 'AI Insights digest';
include(SP_VIEWPATH.'/email/_email_banner.ctp.php');
?>
<?php echo $commonTexts['Hello']?> <?php echo htmlspecialchars($name)?>,<br><br>

<?php echo $aiTexts['ai_insights_email_body_intro']?><br><br>

<?php
$typeColors = array(
    'error'   => array('bg' => '#fdeaea', 'fg' => '#c0392b', 'label' => 'Error'),
    'warning' => array('bg' => '#fef6e6', 'fg' => '#b8790a', 'label' => 'Warning'),
    'todo'    => array('bg' => '#eaf2fd', 'fg' => '#2f6fce', 'label' => 'To-Do'),
);

foreach ($newInsightsByWebsite as $websiteId => $websiteInfo) {
    $websiteLink = SP_WEBPATH . "/recommendations_dashboard.php?website_id=" . intval($websiteId);
    ?>
    <table cellspacing="0" cellpadding="0" width="100%" style="margin-bottom:20px;">
        <tr>
            <td style="font-size:14px;font-weight:bold;padding-bottom:8px;">
                <a href="<?php echo $websiteLink?>" style="text-decoration:none;color:#333333;"><?php echo htmlspecialchars($websiteInfo['name'])?></a>
            </td>
        </tr>
        <?php foreach ($websiteInfo['rows'] as $rec) {
            $colors = !empty($typeColors[$rec['type']]) ? $typeColors[$rec['type']] : $typeColors['todo'];
            ?>
        <tr>
            <td style="padding:12px 14px;background-color:<?php echo $colors['bg']?>;border-left:4px solid <?php echo $colors['fg']?>;">
                <span style="color:<?php echo $colors['fg']?>;font-size:10px;font-weight:bold;letter-spacing:0.5px;"><?php echo strtoupper($colors['label'])?></span><br>
                <span style="font-weight:bold;color:#333333;font-size:13px;"><?php echo htmlspecialchars($rec['title'])?></span><br>
                <span style="font-size:12px;color:#777777;"><?php echo htmlspecialchars($rec['description'])?></span>
            </td>
        </tr>
        <tr><td style="height:8px;line-height:8px;font-size:1px;">&nbsp;</td></tr>
        <?php }?>
    </table>
    <?php
}
?>

<?php
$loginLink = SP_WEBPATH . "/websites.php";
echo str_replace('[LOGIN_LINK]', "<a href='$loginLink' style='color:#5c4fd6;'>{$loginTexts['Login']}</a>", $aiTexts['ai_insights_email_body_outro']);
?>
<?php include(SP_VIEWPATH.'/email/_email_footer.ctp.php'); ?>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
