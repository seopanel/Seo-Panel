<?php echo showSectionHead($sectionHead); ?>
<div class="mtg-card" style="max-width: 1100px;">
<p class='note'>Checks title/description/keywords on each of your websites against recommended SEO length ranges - title 10-60 characters, description 50-160 characters. "Fix" opens the full generator, which now saves back to the website record.</p>
<table id="cust_tab">
	<tr>
		<th>Website</th>
		<th>Title</th>
		<th>Description</th>
		<th>Keywords</th>
		<th style="width: 10%">Action</th>
	</tr>
	<?php
	// function_exists() guard - this view can be included more than once
	// within one long-running PHP process, and a bare function
	// declaration would fatal with "cannot redeclare" on the second
	// include (same reasoning as showsiteinfo.ctp.php's mtgSel()).
	if (!function_exists('mtgAuditBadge')) {
	function mtgAuditBadge($value, $minLen = 0, $maxLen = null) {
		$value = trim(stripslashes((string) $value));
		$len = strlen($value);
		if ($len === 0) {
			return '<span class="badge bg-danger py-2 px-3 text-light"><i class="fas fa-times"></i> Missing</span>';
		}
		if (($minLen && $len < $minLen) || ($maxLen && $len > $maxLen)) {
			return '<span class="badge bg-warning py-2 px-3 text-dark"><i class="fas fa-exclamation-triangle"></i> ' . $len . ' chars</span>';
		}
		return '<span class="badge bg-success py-2 px-3 text-light"><i class="fas fa-check"></i> ' . $len . ' chars</span>';
	}
	}

	if (count($websiteList) > 0) {
		foreach ($websiteList as $websiteInfo) {
			?>
			<tr>
				<td><?php echo htmlspecialchars(stripslashes($websiteInfo['name']))?></td>
				<td><?php echo mtgAuditBadge($websiteInfo['title'], 10, 60)?></td>
				<td><?php echo mtgAuditBadge($websiteInfo['description'], 50, 160)?></td>
				<td><?php echo mtgAuditBadge($websiteInfo['keywords'])?></td>
				<td>
					<a onclick="<?php echo pluginGETMethod('action=show&website_id=' . intval($websiteInfo['id']))?>" href="javascript:void(0);" class="btn btn-sm btn-primary">
						Fix
					</a>
				</td>
			</tr>
			<?php
		}
	} else {
		?>
		<tr><td colspan="5"><b>No Records Found</b></td></tr>
		<?php
	}
	?>
</table>
</div>
