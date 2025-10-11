<?php echo showSectionHead($spTextTools['Featured Submission']); ?>
<table class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>		
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spText['common']['Pagerank']?></td>
		<!--
		<td><?php echo $spTextDir['Coupon Code']?></td>
		<td><?php echo $spTextDir['Coupon Offer']?></td>
		-->
		<td width="15%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4; 
	if(count($list) > 0) {
		foreach($list as $i => $listInfo) {            
            ?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo ($i)?></td>    				
				<td class="td_br_right left"><a target="_blank" href="<?php echo addHttpToUrl($listInfo['directory_link']); ?>"><?php echo $listInfo['directory_name']?></a></td>    				
				<td class="td_br_right"><?php echo $listInfo['google_pagerank']?></td>
				<?php /*?>
				<td class="td_br_right" style="color: red;"><?php echo $listInfo['coupon_code']?></td>
				<td class="td_br_right" style="color: red;">
				    <?php echo empty($listInfo['coupon_offer']) ? "" : $listInfo['coupon_offer']."%"; ?>
				</td>
				<?php */?>
				<td class="text-center">
					<a href="<?php echo $listInfo['directory_link']?>" target="_blank" class="btn btn-success btn-sm"><?php echo $spText['button']['Submit']?> &gt;&gt;</a>
				</td>
			</tr>
			<?php
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a href="<?php echo SP_CONTACT_LINK?>" class="btn btn-primary" target="_blank"><?php echo $spTextDir['clickaddfeatureddirectory']?> &gt;&gt;</a>
    	</td>
	</tr>
</table>