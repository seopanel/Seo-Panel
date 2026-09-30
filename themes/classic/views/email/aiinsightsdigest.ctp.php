<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body>
<?php echo $commonTexts['Hello']?> <?php echo $name?>,<br><br>

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
            <td style="padding:8px 0;border-bottom:1px solid #f2f2f2;">
                <span style="background:<?php echo $colors['bg']?>;color:<?php echo $colors['fg']?>;font-size:10px;font-weight:bold;padding:2px 8px;border-radius:10px;text-transform:uppercase;"><?php echo $colors['label']?></span><br>
                <span style="font-weight:bold;color:#333333;"><?php echo htmlspecialchars($rec['title'])?></span><br>
                <span style="font-size:12px;color:#777777;"><?php echo htmlspecialchars($rec['description'])?></span>
            </td>
        </tr>
        <?php }?>
    </table>
    <?php
}
?>

<br>
<?php
$custSiteInfo = getCustomizerDetails();
$loginLink = SP_WEBPATH . "/websites.php";
echo str_replace('[LOGIN_LINK]', "<a href='$loginLink'>{$loginTexts['Login']}</a>", $aiTexts['ai_insights_email_body_outro']);
?>
<br><br>
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
		    	<?php if (!empty($custSiteInfo['footer_copyright'])) {?>
		    		<div style="word-wrap: break-word;"><?php echo str_replace('[year]', date('Y'), $custSiteInfo['footer_copyright'])?></div>
		    	<?php } else {?>
					<div style="word-wrap: break-word;">
						<p style="font-size: 11px; color: rgb(169, 169, 169);"><?php echo str_replace('[year]', date('Y'), $spText['common']['copyright']); ?></p>
					</div>
				<?php }?>
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>
