<?php
if (empty($linkCount)) {
	?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
	<?php
} else {
	if ($estimatedSeconds >= 60) {
		$estimateText = round($estimatedSeconds / 60) . ' min';
	} else {
		$estimateText = $estimatedSeconds . ' sec';
	}

	if ($websiteCount > 1) {
		$estimateMsg = $spTextSMC['[count] links across [website_count] websites will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]'] ?? '[count] links across [website_count] websites will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]';
		$estimateMsg = str_replace('[website_count]', $websiteCount, $estimateMsg);
	} else {
		$estimateMsg = $spTextSMC['[count] links will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]'] ?? '[count] links will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]';
	}
	$estimateMsg = str_replace(['[count]', '[estimate]'], [$linkCount, "~$estimateText"], $estimateMsg);
	?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $estimateMsg?>.
	</div>
	<?php
}
?>
