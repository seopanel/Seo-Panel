<?php echo showSectionHead($spTextTools['Featured Submission']); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>		
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spText['common']['Pagerank']?></td>
		<!--
		<td><?php echo $spTextDir['Coupon Code']?></td>
		<td><?php echo $spTextDir['Coupon Offer']?></td>
		-->
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4; 
	if(count($list) > 0){
		$catCount = count($list);
		foreach($list as $i => $listInfo){
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            
            ?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo $listInfo['id']?></td>    				
				<td class="td_br_right left"><a target="_blank" href="<?php echo addHttpToUrl($listInfo['directory_name']); ?>"><?php echo $listInfo['directory_name']?></a></td>    				
				<td class="td_br_right"><img src="<?php echo SP_IMGPATH?>/pr/pr<?php echo $listInfo['google_pagerank']?>.gif"></td>
				<!--
				<td class="td_br_right" style="color: red;"><?php echo $listInfo['coupon_code']?></td>
				<td class="td_br_right" style="color: red;">
				    <?php echo empty($listInfo['coupon_offer']) ? "" : $listInfo['coupon_offer']."%"; ?>
				</td>
				-->
				<td class="<?php echo $rightBotClass?>">
					<a href="<?php echo $listInfo['directory_link']?>" target="_blank"><b><?php echo $spText['button']['Submit']?> &gt;&gt;</b></a>
				</td>
			</tr>
			<?php
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
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a href="<?php echo SP_CONTACT_LINK?>" class="actionbut" target="_blank"><?php echo $spTextDir['clickaddfeatureddirectory']?> &gt;&gt;</a>
    	</td>
	</tr>
</table>