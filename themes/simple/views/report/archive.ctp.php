<?php 
$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";
$hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";

if(!empty($printVersion) || !empty($pdfVersion)) {
    $doPrint = empty($cronUserId) ? true : false;
    $pdfVersion ? showPdfHeader($sectionHead) : showPrintHeader($sectionHead, $doPrint);
    ?>
    <table width="80%" border="0" cellspacing="0" cellpadding="0" class="search">
    	<?php if (!empty($websiteUrl)) {?>
    		<tr>
    			<th><?php echo $spText['common']['Website']?>:</th>
        		<td>
        			<?php echo $websiteUrl; ?>
    			</td>
    		</tr>
		<?php }?>
		<tr>
			<th><?php echo $spText['common']['Period']?>:</th>
    		<td>
    			<?php echo $fromTime?> - <?php echo $toTime?>
			</td>
		</tr>
	</table>
<?php } else {?>
	<?php echo showSectionHead($sectionHead); ?>
	<form id='search_form'>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">		
		<tr>
			<th><?php echo $spText['common']['Name']?>: </th>
			<td>
				<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>">
			</td>			
			<th><?php echo $spText['common']['Period']?>:</th>
    		<td colspan="2">
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/> 
    			<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/> 
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/> 
    			<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    		</td>
    	<tr>
    	<tr>
		    <th><?php echo $spText['common']['Website']?>: </th>
			<td>
    			<select name="website_id" id="website_id"  onchange="scriptDoLoadPost('archive.php', 'search_form', 'content')" style="width: 120px;">
    				<option value="">-- Select --</option>
    				<?php foreach($siteList as $websiteInfo){?>
    					<?php if($websiteInfo['id'] == $websiteId){?>
    						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
    					<?php }else{?>
    						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
    					<?php }?>
    				<?php }?>
    			</select>				
			</td>
			<th><?php echo $spText['label']['Report Type']?>: </th>
			<td>
				<select name="report_type" id="report_type" onchange="scriptDoLoadPost('archive.php', 'search_form', 'content')" style="width: 150px;">
					<option value="">-- Select --</option>
    				<?php foreach($reportTypes as $type => $info){?>
						<?php if($type == $searchInfo['report_type']){?>
							<option value="<?php echo $type?>" selected><?php echo $info?></option>
						<?php }else{?>
							<option value="<?php echo $type?>"><?php echo $info?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
			<td width="120px">
				<a href="javascript:void(0);" onclick="scriptDoLoadPost('archive.php', 'search_form', 'content')" class="actionbut"><?php echo $spText['button']['Search']?></a>
			</td>
		</tr>
	</table>
	<br>
	</form>
	<?php
	// url parameters
	$mainLink = SP_WEBPATH."/archive.php?$urlarg";
	$directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal&pageno=$pageNo"; 
	?>
	<div style="float:left;margin-right: 10px;margin-top: 20px; clear: both;">
		<a href="<?php echo $directLink?>&doc_type=pdf"><img src="<?php echo SP_IMGPATH?>/icon_pdf.png"></a> &nbsp;
		<a href="<?php echo $directLink?>&doc_type=export"><img src="<?php echo SP_IMGPATH?>/icoExport.gif"></a> &nbsp;
		<a target="_blank" href="<?php echo $directLink?>&doc_type=print"><img src="<?php echo SP_IMGPATH?>/print_button.gif"></a>
	</div>
<?php }?>

<div id='subcontent' style="margin-top: 40px;">
<?php 
if (!empty($keywordPos)) {
	?>
	<div>
	<?php echo $keywordPagingDiv?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list" style="<?php echo $borderCollapseVal; ?>">
		<tr class="squareHead">
			<?php
			$linkClass = "";
	        if ($orderCol == 'keyword') {
	            $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
	            $linkClass .= "sort_".strtolower($orderVal);
	        } else {
	            $oVal = 'ASC';
	        }
	        
	        $hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";
	        
			$linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=keyword&order_val=$oVal', 'content')\">{$spText['common']['Keyword']}</a>"; 
			?>		
			<?php if (empty($websiteId)) {?>
				<td class="left" rowspan="2"><?php echo $spText['common']['Website']?></td>
				<td rowspan="2" style="border-right:2px solid #B0C2CC;"><?php echo $linkName?></td>
			<?php } else { ?>
				<td class="left" rowspan="2" style="border-right:2px solid #B0C2CC;"><?php echo $linkName?></td>
			<?php }?>
			<?php
			$seCount = count($seList);
			foreach ($seList as $i => $seInfo){
			    
			    $linkClass = "";
	            if ($seInfo['id'] == $orderCol) {
	                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
	                $linkClass .= "sort_".strtolower($oVal);
	            } else {
	                $oVal = 'ASC';
	            }
	            $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col={$seInfo['id']}&order_val=$oVal', 'content')\">{$seInfo['domain']}</a>";
			    
				if( ($i+1) == $seCount){			
					?>
					<td class="right" colspan="3" style="border-right:2px solid #B0C2CC;"><?php echo $linkName; ?></td>
					<?php	
				}else{
					?>
					<td colspan="3" style="border-right:2px solid #B0C2CC;"><?php echo $linkName; ?></td>
					<?php
				}
				
			}
			?>
		</tr>	
		<tr class="squareSubHead">
			<?php
			$pTxt = str_replace("-", "/", substr($fromTime, -5));
			$cTxt = str_replace("-", "/", substr($toTime, -5));
			foreach ($seList as $i => $seInfo) {
				?>
				<td><?php echo $pTxt; ?></td>
				<td><?php echo $cTxt; ?></td>
				<td style="border-right:2px solid #B0C2CC;">+ / -</td>
				<?php
			}
			?>
		</tr>
		<?php
		$colCount = empty($websiteId) ? ($seCount * 3) + 2 : ($seCount * 3) + 1; 
		if (count($list) > 0) {
			
			$catCount = count($list);
			$i = 0;
			foreach($indexList as $keywordId => $rankValue){
			    $listInfo = $list[$keywordId];
				$positionInfo = $listInfo['position_info'];
				$class = ($i % 2) ? "blue_row" : "white_row";
				
				if( !$i || ($catCount != ($i + 1)) ){
	                $leftBotClass = "td_left_border td_br_right";
	                $rightBotClass = "td_br_right";
	            }
	            $scriptLink = "website_id={$listInfo['website_id']}&keyword_id={$listInfo['id']}&rep=1&from_time=$fromTime&to_time=$toTime";          
				?>
				<tr class="<?php echo $class?>">				
					<?php if (empty($websiteId)) {?>
						<td class="<?php echo $leftBotClass?> left" width='250px;'><?php echo $listInfo['weburl']; ?></td>
						<td class='td_br_right left' style="border-right:2px solid #B0C2CC;"><?php echo $listInfo['name'] ?></td>
					<?php } else { ?>
						<td class="<?php echo $leftBotClass?> left" width='100px;' style="border-right:2px solid #B0C2CC;"><?php echo $listInfo['name']; ?></td>
					<?php }?>				
					<?php
					foreach ($seList as $index => $seInfo){
						$rankInfo = $positionInfo[$seInfo['id']];
						$prevRank = isset($rankInfo[$fromTime]) ? $rankInfo[$fromTime] : "";
						$currRank = isset($rankInfo[$toTime]) ? $rankInfo[$toTime] : "";
						$rankDiffTxt = "";
						
						// if both ranks are existing
						if ($prevRank != '' && $currRank != '') {
							$rankDiff = $prevRank - $currRank;
							
							if ($rankDiff > 0) {
								$rankDiffTxt = "<font class='green'>($rankDiff)</font>";
							} else if ($rankDiff < 0) {
								$rankDiffTxt = "<font class='red'>($rankDiff)</font>";
							} else {
								$rankDiffTxt = "0";
							}													
						}
	
						$prevRankLink = scriptAJAXLinkHrefDialog('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $prevRank);
						$currRankLink = scriptAJAXLinkHrefDialog('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $currRank);
						$graphLink = scriptAJAXLinkHrefDialog('graphical-reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], '&nbsp;', 'graphicon');
						
						// if pdf report remove links
						if ($pdfVersion) {
							$prevRankLink = str_replace("href='javascript:void(0);'", "", $prevRankLink);
							$currRankLink = str_replace("href='javascript:void(0);'", "", $currRankLink);
							$graphLink = str_replace("href='javascript:void(0);'", "", $graphLink);
						}
						
						$diffOut = empty($cronUserId) ? $graphLink . " " . $rankDiffTxt : $rankDiffTxt;
					    ?>
						<td class="td_br_right"><?php echo $prevRankLink; ?></td>
						<td class="td_br_right"><?php echo $currRankLink; ?></td>
						<td class='td_br_right left' style="border-right:2px solid #B0C2CC; width: 50px;" nowrap><?php echo $diffOut; ?></td>
						<?php					
					}
					?>				
				</tr>
				<?php
				$i++;
			}
		} else {	 
			echo showNoRecordsList($colCount - 2, '', true);		
		}
		?>
	</table>
	</div>
	<?php
}	 
?>

<br>
<div>
	<?php
	if (!empty($websiteStats)) {
    	$colSpan = 11; 
    	?>
    	<?php echo $websitePagingDiv?>
    	<table width="100%" cellspacing="0" cellpadding="0" class="summary" style="<?php echo $borderCollapseVal; ?>">
    		<tr><td class="topheader" colspan="<?php echo $colSpan?>"><?php echo $spTextHome['Website Statistics']?></td></tr>
    		<tr>
    			<td class="subheader" style="border: none;" width="5%" rowspan="2"><?php echo $spText['common']['Id']?></td>
    			<td class="subheader" rowspan="2"><?php echo $spTextHome['SiteNameUrl']?></td>
    			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Ranks']?></td>
    			<td class="subheaderdark" colspan="3"><?php echo $spTextHome['Backlinks']?></td>
    			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Pages Indexed']?></td>
    			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Directory Submission']?></td>
    		</tr>		
    		<tr>
    			<td class="subheader">Moz</td>
    			<td class="subheader">Alexa</td>
    			<td class="subheader">Google</td>
    			<td class="subheader">Alexa</td>
    			<td class="subheader">Bing</td>			
    			<td class="subheader">Google</td>
    			<td class="subheader">Bing</td>
    			<td class="subheader"><?php echo $spText['common']['Total']?></td>
    			<td class="subheader"><?php echo $spText['common']['Active']?></td>
    		</tr>
    		<?php 
    		if(count($websiteRankList) > 0){

				foreach($websiteRankList as $websiteInfo){
    				$timeArg = "&from_time=$fromTime&to_time=$toTime";
    				$googleRankLink = scriptAJAXLinkHrefDialog('rank.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['mozrank']);
    				$alexaRankLink = scriptAJAXLinkHrefDialog('rank.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['alexarank']);
    				$googleBackLInk = scriptAJAXLinkHrefDialog('backlinks.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['google']['backlinks']);
    				$alexaBackLInk = scriptAJAXLinkHrefDialog('backlinks.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['alexa']['backlinks']);
    				$bingBackLInk = scriptAJAXLinkHrefDialog('backlinks.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['msn']['backlinks']);
    				$googleIndexLInk = scriptAJAXLinkHrefDialog('saturationchecker.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['google']['indexed']);
    				$bingIndexLInk = scriptAJAXLinkHrefDialog('saturationchecker.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['msn']['indexed']);
    				$totaldirLink = scriptAJAXLinkHrefDialog('directories.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['dirsub']['total']);
    				$activeDirLink = scriptAJAXLinkHrefDialog('directories.php', 'content', "sec=reports&active=approved&&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['dirsub']['active']);
    				?>
    				<tr>
    					<td class="content" style="border-left: none;"><?php echo $websiteInfo['id']?></td>
    					<td class="content">
    						<?php echo $websiteInfo['name'];?><br>
    						<a href="<?php echo $websiteInfo['url'];?>" target="_blank"><?php echo $websiteInfo['url'];?></a>
    					</td>
    					<td class="content"><?php echo $googleRankLink;?></td>
						<td class="content"><?php echo $alexaRankLink; ?></td>
						<td class="content"><?php echo $googleBackLInk; ?></td>
						<td class="content"><?php echo $alexaBackLInk; ?></td>
						<td class="content"><?php echo $bingBackLInk; ?></td>
						<td class="content"><?php echo $googleIndexLInk; ?></td>
						<td class="content"><?php echo $bingIndexLInk; ?></td>
						<td class="contentmid"><?php echo $totaldirLink?></td>					
						<td class="contentmid"><?php echo $activeDirLink?></td>
    				</tr> 
    			<?php } ?>
    		<?php }else{ ?>
    			<tr><td colspan="<?php echo $colSpan?>" class="norecord"><?php echo $spText['common']['nowebsites']?></td></tr>
    		<?php } ?>		
    	</table>
		<?php
	}
	?>
</div>
</div>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>