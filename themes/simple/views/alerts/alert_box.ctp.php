<?php
if (!empty($alertList)) { 
	foreach ($alertList as $alertInfo) {
	    $alertType = "light";
	    if (empty($alertInfo['visited'])) {
	       $alertType = !empty($alertInfo['alert_type']) ? $alertInfo['alert_type'] : "success";
	    }
	    
	    $alertLink = "#";
	    if (!empty($alertInfo['alert_url'])) {
	        $alertLink = stristr($alertInfo['alert_url'], 'http') ? $alertInfo['alert_url'] : Spider::addTrailingSlash(SP_WEBPATH) . $alertInfo['alert_url'];
	    }
		?>
		<li class="list-group-item list-group-item-<?php echo $alertType?>">
			<div>
				<a href="<?php echo $alertLink?>"><?php echo $alertInfo["alert_subject"]?></a>
			</div>
			<div style="margin-top: 8px; font-size: 13px; color: #4a5568; line-height: 1.6; word-wrap: break-word; white-space: normal;"><?php echo $alertInfo["alert_message"]?></div>
			<div style="margin-top: 8px; font-size: 12px; text-align: right; color: #718096;"><?php echo timeElapsedString($alertInfo['alert_time'])?></div>
		</li>
		<?php
	}
} else {
	?>
	<li class="list-group-item list-group-item-light"><a href="#" class="text-bold text-italic">No Notification Found</a></li>
	<?php
}
?>
<li class="list-group-item list-group-item-secondary" style="text-align: center;line-height: 0px; border-bottom: none;">
	<a href="<?php echo SP_WEBPATH?>/admin-panel.php?sec=alerts" style="font-size: 10px; ">See All</a>
</li>