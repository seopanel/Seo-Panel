<table width="100%" class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Url']?></td>
		<td><?php echo $spTextPS['Desktop Speed']?></td>
		<td><?php echo $spTextPS['Mobile Speed']?></td>
		<td><?php echo $spText['common']['Details']?></td>
	</tr>
	<?php
	$colCount = 4;
	if(count($list) > 0){
		foreach($list as $url) {          
            $debugVar = !empty($_POST['debug']) ? "&debug=1" : "";
            $debugVar .= !empty($_POST['debug_format']) ? "&debug_format=" . $_POST['debug_format'] : "" ;
			?>
			<tr>
				<td style="text-align:left;padding-left:10px;"><?php echo htmlspecialchars($url)?></td>
				<td><?php echo $reportList[$url]['desktop']['speed_score'] ? $reportList[$url]['desktop']['speed_score'] : 0;?> / 100</td>
				<td><?php echo $reportList[$url]['mobile']['speed_score'] ? $reportList[$url]['mobile']['speed_score'] : 0;?> / 100</td>
				<td>
					<a href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode($url); ?>" target="_blank">
						<?php echo $spText['common']['Details']?> &gt;&gt;
					</a>
				</td>
			</tr>
			<?php
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>