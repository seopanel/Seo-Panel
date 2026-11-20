<?php
// Shared branded banner for the periodic/digest emails (report
// generation, AI Insights, AI Visibility, AI Perception) - these used to
// open straight into "Hello, Name," with nothing to visually mark the
// email as coming from this product, right above content that (for the
// report email especially) looked like a broken admin page fragment.
// Text-only by design, no logo <img> - external images are blocked by
// default in most email clients until the recipient clicks "show
// images", so a text wordmark is what actually renders on first open.
// $emailBannerSubtitle (optional) is set by the calling view before
// including this file, e.g. "Your weekly report".
$custSiteInfo = getCustomizerDetails();
$bannerSiteName = !empty($custSiteInfo['site_name']) ? $custSiteInfo['site_name'] : "SEO Panel";
?>
<table cellspacing="0" cellpadding="0" width="100%" style="border-collapse:collapse;">
	<tr>
		<td style="background-color:#6d5bd0;background-image:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:20px 24px;border-radius:6px 6px 0 0;">
			<span style="font-family:Tahoma, verdana, arial, sans-serif;font-size:18px;font-weight:bold;color:#ffffff;"><?php echo htmlspecialchars($bannerSiteName)?></span>
			<?php if (!empty($emailBannerSubtitle)) { ?>
				<br><span style="font-family:Tahoma, verdana, arial, sans-serif;font-size:12px;color:#e4defa;"><?php echo htmlspecialchars($emailBannerSubtitle)?></span>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td style="background-color:#ffffff;border:1px solid #eef0f5;border-top:none;border-radius:0 0 6px 6px;padding:20px 24px;">
