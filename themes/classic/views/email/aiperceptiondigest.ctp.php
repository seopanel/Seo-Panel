<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php echo $this->getViewContent('email/emailhead'); ?>
<body>
<?php echo $commonTexts['Hello']?> <?php echo $name?>,<br><br>

<?php echo $aivTexts['ai_perception_email_body_intro']?><br><br>

<?php foreach ($summaryByWebsite as $websiteId => $s) {
    $websiteLink = SP_WEBPATH . "/ai-perception.php?sec=tracking&website_id=" . intval($websiteId);

    $trendLabel = '';
    $trendColor = '#777777';
    if ($s['trend_direction'] === 'up') {
        $trendLabel = '&#9650; ' . abs(intval($s['trend_points'])) . ' pt';
        $trendColor = '#2e8b57';
    } elseif ($s['trend_direction'] === 'down') {
        $trendLabel = '&#9660; ' . abs(intval($s['trend_points'])) . ' pt';
        $trendColor = '#c0392b';
    } elseif ($s['trend_direction'] === 'flat') {
        $trendLabel = '&mdash; ' . ($aivTexts['no change'] ?? 'no change');
    } else {
        $trendLabel = $aivTexts['first tracked week'] ?? 'first tracked week';
    }
    ?>
    <table cellspacing="0" cellpadding="0" width="100%" style="margin-bottom:16px;border-collapse:collapse;font-family:Tahoma, verdana, arial, sans-serif;">
        <tr>
            <td colspan="2" style="background-color:#DAE5EE;border-bottom:1px solid #B0C2CC;padding:8px 12px;font-size:13px;font-weight:bold;">
                <a href="<?php echo $websiteLink?>" style="text-decoration:none;color:#333333;"><?php echo htmlspecialchars($s['name'])?></a>
            </td>
        </tr>
        <tr>
            <td width="50%" style="background-color:#F4F7FA;padding:14px 12px;text-align:center;border-right:1px solid #ffffff;">
                <span style="font-size:22px;font-weight:bold;color:#5c4fd6;"><?php echo intval($s['share'])?>%</span><br>
                <span style="font-size:11px;color:#777777;"><?php echo $aivTexts['Share of Voice'] ?? 'Share of Voice'?></span>
            </td>
            <td width="50%" style="background-color:#F4F7FA;padding:14px 12px;text-align:center;">
                <span style="font-size:15px;font-weight:bold;color:<?php echo $trendColor?>;"><?php echo $trendLabel?></span><br>
                <span style="font-size:11px;color:#777777;"><?php echo $aivTexts['vs. last cycle'] ?? 'vs. last cycle'?></span>
            </td>
        </tr>
        <?php if (!empty($s['competitors'])) { ?>
        <tr>
            <td colspan="2" style="padding:10px 12px;font-size:11px;color:#777777;border-top:1px solid #ffffff;">
                <?php echo $aivTexts['Competitors'] ?? 'Competitors'?>:
                <?php
                $competitorParts = [];
                foreach ($s['competitors'] as $c) {
                    $competitorParts[] = htmlspecialchars($c['name']) . ' (' . ($c['share'] === null ? '&mdash;' : intval($c['share']) . '%') . ')';
                }
                echo implode(', ', $competitorParts);
                ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="2" style="padding:6px 12px 12px;font-size:11px;color:#777777;">
                <?php echo $aivTexts['Sentiment'] ?? 'Sentiment'?>:
                <?php echo intval($s['sentiment']['positive'])?> <?php echo $aivTexts['positive'] ?? 'positive'?>,
                <?php echo intval($s['sentiment']['neutral']) + intval($s['sentiment']['unset'])?> <?php echo $aivTexts['neutral'] ?? 'neutral'?>,
                <?php echo intval($s['sentiment']['negative'])?> <?php echo $aivTexts['negative'] ?? 'negative'?>
            </td>
        </tr>
    </table>
    <?php
}
?>

<br>
<?php
$custSiteInfo = getCustomizerDetails();
$loginLink = SP_WEBPATH . "/websites.php";
echo str_replace('[LOGIN_LINK]', "<a href='$loginLink'>{$loginTexts['Login']}</a>", $aivTexts['ai_perception_email_body_outro']);
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
