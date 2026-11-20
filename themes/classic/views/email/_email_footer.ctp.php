<?php
// Closes the wrapper table opened by _email_banner.ctp.php and adds the
// copyright line - replaces the plain <hr>+copyright block that used to
// be copy-pasted into every digest email individually.
$custSiteInfo = getCustomizerDetails();
?>
			<table cellspacing="0" cellpadding="0" width="100%" style="margin-top:20px;border-collapse:collapse;">
				<tr>
					<td style="border-top:1px solid #eef0f5;padding-top:12px;font-family:Tahoma, verdana, arial, sans-serif;font-size:11px;color:#9296a8;">
						<?php if (!empty($custSiteInfo['footer_copyright'])) {?>
							<?php echo str_replace('[year]', date('Y'), $custSiteInfo['footer_copyright'])?>
						<?php } else {?>
							<?php echo str_replace('www.seopanel.org', "<a href='https://www.seopanel.org' style='color:#9296a8;'>www.seopanel.org</a>", str_replace('[year]', date('Y'), $spText['common']['copyright']))?>
						<?php }?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
