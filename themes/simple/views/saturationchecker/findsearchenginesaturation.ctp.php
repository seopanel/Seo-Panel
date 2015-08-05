<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Url']?></td>
		<td>Google</td>
		<td class="right">Bing</td>
	</tr>
	<?php
	$colCount = 3; 
	if(count($list) > 0){
		$catCount = count($list);
		$i = 0;
		foreach($list as $url){
			
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            $tdWidth = "25%";               
            $debugVar = !empty($_POST['debug']) ? "&debug=1" : "";
            $debugVar .= !empty($_POST['debug_format']) ? "&debug_format=" . $_POST['debug_format'] : "" ;          
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>" style="text-align:left;padding-left:10px;"><?php echo $url?></td>
				<td class="td_br_right" width="<?php echo $tdWidth?>" id='googlerank<?php echo $i?>'>
					<script type="text/javascript">
						scriptDoLoadPost('saturationchecker.php', 'tmp', 'googlerank<?php echo $i?>', 'sec=saturation&engine=google&url=<?php echo urlencode($url); ?><?php echo $debugVar?>');
					</script>
				</td>
				<td class="<?php echo $rightBotClass?>" width="<?php echo $tdWidth?>" id='msnrank<?php echo $i?>'>
					<script type="text/javascript">
						scriptDoLoadPost('saturationchecker.php', 'tmp', 'msnrank<?php echo $i?>', 'sec=saturation&engine=msn&url=<?php echo urlencode($url); ?><?php echo $debugVar?>');
					</script>
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
</table>
