<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Url']?></td>		
		<td><?php echo $spText['common']['MOZ Rank']?></td>
		<td class="right"><?php echo $spText['common']['Alexa Rank']?></td>
	</tr>
	<?php
	$colCount = 4; 
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
            
            $debugVar = !empty($_POST['debug']) ? "&debug=1" : "";
            $debugVar .= !empty($_POST['debug_format']) ? "&debug_format=" . $_POST['debug_format'] : ""
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo ($i+1)?></td>
				<td class="td_br_right" style="text-align: left;"><?php echo $url?></td>
				<td width="150px" id='mozrank<?php echo $i?>' class='td_br_right rankarea'>
					<b class="success"><?php echo empty($mozRankList[$i]) ? "-" : $mozRankList[$i]; ?></b>
				</td>
				<td class="<?php echo $rightBotClass?>" width="150px" id='alexarank<?php echo $i?>' class='rankarea'>
					<script type="text/javascript">
						scriptDoLoadPost('rank.php', 'tmp', 'alexarank<?php echo $i?>', 'sec=showalexa&url=<?php echo urlencode($url); ?><?php echo $debugVar?>');
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