<?php 
$backLink = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&pageno={$post['pageno']}&order_col={$post['order_col']}&order_val={$post['order_val']}')";
?>
<br>
<div id="run_project">
	<div>&nbsp;<a href="javascript:void(0)" onclick="<?php echo $backLink?>" class="btn btn-info"><i class="fas fa-arrow-left"></i> Back</a></div>
	<div id="run_info">
		<table class="summary_tab">
        	<tr>
        		<td class="topheader" colspan="10"><?php echo $spTextSA['Page Details']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell" width="20%"><?php echo $spTextSA['Page Link']?>:</th>
        		<td colspan="3" style="text-align: left;"><a href="<?php echo $reportInfo['page_url']?>" target="_blank"><?php echo $reportInfo['page_url']?></a></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Score']?>:</th>
        		<td style="text-align: left;">
        		    <?php
				        if ($reportInfo['score'] < 0) {
				            $scoreClass = 'minus';
				            $reportInfo['score'] = $reportInfo['score'] * -1;
				        } else {
				            $scoreClass = 'plus';
				        }
				        for($b=0;$b<=$reportInfo['score'];$b++) echo "<span class='$scoreClass'>&nbsp;</span>";
				    ?>
        		    <?php echo $reportInfo['score']?>
    			</td>
        		<th width="20%"><?php echo $_SESSION['text']['common']['Page Authority']; ?>:</th>
        		<td><?php echo $reportInfo['page_authority']?></td>        		
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Title']?>:</th>
        		<td style="text-align: left;"><?php echo strip_tags($reportInfo['page_title'])?></td>
        		<th>Google <?php echo $spTextHome['Backlinks']?>:</th>
        		<td><?php echo $reportInfo['google_backlinks']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Description']?>:</th>
        		<td style="text-align: left;"><?php echo strip_tags($reportInfo['page_description'])?></td>
        		<th>Bing <?php echo $spTextHome['Backlinks']?>:</th>
        		<td><?php echo $reportInfo['bing_backlinks']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Keywords']?>:</th>
        		<td style="text-align: left;"><?php echo strip_tags($reportInfo['page_keywords'])?></td>
        		<th>Google <?php echo $spTextHome['Indexed']?>:</th>
        		<td><?php echo $reportInfo['google_indexed']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Comments']?>:</th>
        		<td style="text-align: left;"><?php echo $reportInfo['comments']?></td>
        		<th>Bing <?php echo $spTextHome['Indexed']?>:</th>
        		<td><?php echo $reportInfo['bing_indexed']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spTextSA['Total Links']?>:</th>
        		<td style="text-align: left;"><?php echo $reportInfo['total_links']?></td>
        		<th><?php echo $spTextSA['External Links']?>:</th>
        		<td><?php echo $reportInfo['external_links']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Brocken']?>:</th>
        		<td style="text-align: left;"><?php echo $reportInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No']; ?></td>
        		<th></th>
        		<td></td>
        	</tr>
        </table>
	</div>
	
	<br><br>
	<?php echo showSectionHead($spTextSA['Page Links']); ?>
	<div>		
		<table class="list">
        	<tr class="listHead">
        		<td class="leftid">#</td>		
        		<td><?php echo $spText['common']['Url']?></td>		
        		<td><?php echo $spTextSA['Anchor']?></td>		
        		<td><?php echo $spTextSA['Link Title']?></td>		
        		<td><?php echo $spTextSA['Nofollow']?></td>
        		<td class="right"><?php echo $spTextSA['External']?></td>
        	</tr>
        	<?php
        	$colCount = 6; 
        	if(count($linkList) > 0) {
        		foreach($linkList as $i => $listInfo) {
        			?>
        			<tr class="<?php echo $class?>">
        				<td class="<?php echo $leftBotClass?>"><?php echo $i+1?></td>				
        				<td class="td_br_right left">
        				    <a href="<?php echo $listInfo['link_url']?>" target="_blank"><?php echo $listInfo['link_url']?></a>
        				</td>
        				<td class="td_br_right left"><?php echo $listInfo['link_anchor']?></td>
        				<td class="td_br_right left"><?php echo $listInfo['link_title']?></td>
        				<td class="td_br_right">
        				    <?php echo $listInfo['nofollow'] ? $spText['common']['Yes'] : $spText['common']['No']; ?>
        				</td>
        				<td class="<?php echo $rightBotClass?>">
        					<?php echo $listInfo['extrenal'] ? $spText['common']['Yes'] : $spText['common']['No']; ?>
        				</td>
        			</tr>
        			<?php
        		}
        	}else{	 
        		echo showNoRecordsList($colCount-2);		
        	} 
        	?>
        </table>
	</div>
</div>