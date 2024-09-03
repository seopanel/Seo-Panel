<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Url']?></td>		
		<td><?php echo $spText['common']['MOZ Rank']?></td>	
		<td><?php echo $spText['common']['Domain Authority']?></td>	
		<td class="right"><?php echo $spText['common']['Page Authority']?></td>
	</tr>
	<?php
	$colCount = 5;
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
					<b class="success"><?php echo empty($mozRankList[$i]['moz_rank']) ? "-" : $mozRankList[$i]['moz_rank']; ?></b>
				</td>
				<td width="150px" class='td_br_right rankarea'>
					<b class="success"><?php echo empty($mozRankList[$i]['domain_authority']) ? "-" : $mozRankList[$i]['domain_authority']; ?></b>
				</td>
				<td class="<?php echo $rightBotClass?>" width="150px" class='td_br_right rankarea'>
					<b class="success"><?php echo empty($mozRankList[$i]['page_authority']) ? "-" : $mozRankList[$i]['page_authority']; ?></b>
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