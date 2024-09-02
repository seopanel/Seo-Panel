<?php 
$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";
$hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";

if(!empty($printVersion) || !empty($pdfVersion)) {
    $doPrint = empty($cronUserId) ? true : false;
    $pdfVersion ? showPdfHeader($sectionHead) : showPrintHeader($sectionHead, $doPrint);
    ?>
    <table class="search">
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
	<table width="100%" class="search">		
		<tr>
			<th><?php echo $spText['common']['Name']?>: </th>
			<td>
				<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>">
			</td>			
			<th><?php echo $spText['common']['Period']?>:</th>
    		<td colspan="2">
    			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time_summary"/>
    			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time_summary"/>
				<script>
				  $( function() {
				    $( "#from_time_summary, #to_time_summary").datepicker({dateFormat: "yy-mm-dd"});
				  } );
			  	</script>
    		</td>
    	<tr>
    	<tr>
		    <th><?php echo $spText['common']['Website']?>: </th>
			<td>
    			<select name="website_id" id="website_id"  onchange="scriptDoLoadPost('archive.php', 'search_form', 'content')" style="width: 180px;">
    				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
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
				<select name="report_type" id="report_type" onchange="scriptDoLoadPost('archive.php', 'search_form', 'content')" style="width: 210px;">
					<option value="">-- <?php echo $spText['common']['Select']?> --</option>
    				<?php foreach($reportTypes as $type => $info){?>
						<?php if($type == $searchInfo['report_type']){?>
							<option value="<?php echo $type?>" selected><?php echo $info?></option>
						<?php }else{?>
							<option value="<?php echo $type?>"><?php echo $info?></option>
						<?php }?>
					<?php }?>
				</select>
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
	
	// export links
	$pdfLink = "$directLink&doc_type=pdf";
	$csvLink = "$directLink&doc_type=export";
	$printLink = "$directLink&doc_type=print";
	showExportDiv($pdfLink, $csvLink, $printLink);
	?>
<?php }?>

<div id='subcontent' class="dashboard">
<?php
$seCount = count($seList);
if (!empty($keywordPos) && !empty($seCount)) {
	$colCount = empty($websiteId) ? ($seCount * 3) + 2 : ($seCount * 3) + 1;
	?>
	<br>
	<div class="table-responsive">
	<?php
	echo showSectionHead($spTextTools['Keyword Position Summary']);
	if (empty($pdfVersion)) echo $keywordPagingDiv;
	?>
	<table id="cust_tab">
		<tr>
			<?php
			$linkClass = "";
	        if ($orderCol == 'keyword') {
	            $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
	            $linkClass .= "sort_".strtolower($oVal);
	        } else {
	            $oVal = 'ASC';
	        }
	        
	        $hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";
	        
			$linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=keyword&order_val=$oVal', 'content')\">{$spText['common']['Keyword']}</a>"; 
			?>		
			<?php if (empty($websiteId)) {?>
				<th rowspan="2" id="head"><?php echo $linkName?></th>
				<th id="head" rowspan="2"><?php echo $spText['common']['Website']?></th>
			<?php } else { ?>
				<th id="head" rowspan="2" style="border-right:1px solid #B0C2CC;"><?php echo $linkName?></th>
			<?php }?>
			<?php
			foreach ($seList as $i => $seInfo){
			    
			    $linkClass = "";
	            if ($seInfo['id'] == $orderCol) {
	                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
	                $linkClass .= "sort_".strtolower($oVal);
	            } else {
	                $oVal = 'ASC';
	            }
	            $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col={$seInfo['id']}&order_val=$oVal', 'content')\">{$seInfo['domain']}</a>";
			    ?>
				<th id="head" colspan="3"><?php echo $linkName; ?></th>
				<?php
			}
			?>
		</tr>	
		<tr>
			<?php
			$pTxt = str_replace("-", "/", substr($fromTime, -5));
			$cTxt = str_replace("-", "/", substr($toTime, -5));
			foreach ($seList as $i => $seInfo) {
				?>
				<th><?php echo $pTxt; ?></th>
				<th><?php echo $cTxt; ?></th>
				<th>+ / -</th>
				<?php
			}
			?>
		</tr>
		<?php		 
		if (!empty($list) && count($list) > 0) {
			foreach($indexList as $keywordId => $rankValue){
			    $listInfo = $list[$keywordId];
				$positionInfo = $listInfo['position_info'];
	            $rangeFromTime = date('Y-m-d', strtotime('-14 days', strtotime($fromTime)));
	            $scriptLink = "website_id=$websiteId&keyword_id={$listInfo['id']}&rep=1&from_time=$rangeFromTime&to_time=$toTime";          
				?>
				<tr>				
					<?php if (empty($websiteId)) {?>
						<td><?php echo $listInfo['name'] ?></td>
						<td><a href="javascript:void(0)"><?php echo $listInfo['website']; ?></a></td>
					<?php } else { ?>
						<td><?php echo $listInfo['name']; ?></td>
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
						<td><?php echo $prevRankLink; ?></td>
						<td><?php echo $currRankLink; ?></td>
						<td><?php echo $diffOut; ?></td>
						<?php					
					}
					?>				
				</tr>
				<?php
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
<div class="table-responsive">
	<?php
	if (!empty($websiteStats)) {
    	echo showSectionHead($spTextHome['Website Statistics']);
    	$colSpan = 13; 
    	if (empty($pdfVersion)) echo $websitePagingDiv;
    	?>
    	<table id="cust_tab">
    		<tr>
    			<th id="head" rowspan="2"><?php echo $spText['common']['Website']?></th>
    			<th id="head" colspan="3"><?php echo $spTextHome['Ranks']?></th>
    			<th id="head" colspan="2"><?php echo $spTextHome['Backlinks']?></th>
    			<th id="head" colspan="2"><?php echo $spTextHome['Pages Indexed']?></th>
    			<th id="head" colspan="2"><?php echo $spTextPS['Page Speed']?></th>
    			<th id="head" colspan="2"><?php echo $spTextHome['Directory Submission']?></th>
    		</tr>		
    		<tr>
    			<th>Moz</th>
    			<th><?php echo $spText['common']['Domain Authority']?></th>
    			<th><?php echo $spText['common']['Page Authority']?></th>
    			<th>Google</th>
    			<th>Bing</th>			
    			<th>Google</th>
    			<th>Bing</th>
    			<th><?php echo $spTextPS['Desktop Speed']?></th>
    			<th><?php echo $spTextPS['Mobile Speed']?></th>
    			<th><?php echo $spText['common']['Total']?></th>
    			<th><?php echo $spText['common']['Active']?></th>
    		</tr>
    		<?php 
    		if(count($websiteRankList) > 0){

				foreach($websiteRankList as $websiteInfo){
					$rangeFromTime = date('Y-m-d', strtotime('-14 days', strtotime($fromTime)));
    				$timeArg = "&from_time=$rangeFromTime&to_time=$toTime";
    				$googleRankLink = scriptAJAXLinkHrefDialog('rank.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['mozrank']);
    				$daLink = scriptAJAXLinkHrefDialog('rank.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['domain_authority']);
    				$paLink = scriptAJAXLinkHrefDialog('rank.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['page_authority']);
    				$googleBackLInk = scriptAJAXLinkHrefDialog('backlinks.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['google']['backlinks']);
    				$bingBackLInk = scriptAJAXLinkHrefDialog('backlinks.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['msn']['backlinks']);
    				$googleIndexLInk = scriptAJAXLinkHrefDialog('saturationchecker.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['google']['indexed']);
    				$bingIndexLInk = scriptAJAXLinkHrefDialog('saturationchecker.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['msn']['indexed']);
    				$totaldirLink = scriptAJAXLinkHrefDialog('directories.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['dirsub']['total']);
    				$activeDirLink = scriptAJAXLinkHrefDialog('directories.php', 'content', "sec=reports&active=approved&&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['dirsub']['active']);
    				$desktopPageSpeedLink = scriptAJAXLinkHrefDialog('pagespeed.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['desktop_speed_score']);
    				$mobilePageSpeedLink = scriptAJAXLinkHrefDialog('pagespeed.php', 'content', "sec=reports&website_id=".$websiteInfo['id'] . $timeArg, $websiteInfo['mobile_speed_score']);
    				?>
    				<tr>
    					<td>
    						<a href="javascript:void(0)"><?php echo $websiteInfo['name'];?></a>
    					</td>
    					<td><?php echo $googleRankLink;?></td>
						<td><?php echo $daLink; ?></td>
						<td><?php echo $paLink; ?></td>
						<td><?php echo $googleBackLInk; ?></td>
						<td><?php echo $bingBackLInk; ?></td>
						<td><?php echo $googleIndexLInk; ?></td>
						<td><?php echo $bingIndexLInk; ?></td>
						<td><?php echo $desktopPageSpeedLink; ?></td>
						<td><?php echo $mobilePageSpeedLink; ?></td>
						<td><?php echo $totaldirLink?></td>					
						<td><?php echo $activeDirLink?></td>
    				</tr> 
    			<?php
				}
    		} else { 
    		    echo showNoRecordsList($colSpan - 2, '', true); 
    		}?>		
    	</table>
		<?php
	}
	?>
</div>

<?php
if (!empty($websiteSearchReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $websiteSearchReport;?></div>
	<?php
}
?>

<?php
if (!empty($sitemapReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $sitemapReport;?></div>
	<?php
}
?>

<?php
if (!empty($keywordSearchReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $keywordSearchReport;?></div>
	<?php
}
?>

<?php
if (!empty($socialMediaReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $socialMediaReport;?></div>
	<?php
}
?>

<?php
if (!empty($analyticsReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $analyticsReport;?></div>
	<?php
}
?>

<?php
if (!empty($reviewReport)) {
    ?>
    <br>
	<div class="table-responsive"><?php echo $reviewReport;?></div>
	<?php
}
?>

</div>
<br>
<?php
if(empty($cronUserId) && (!empty($printVersion) || !empty($pdfVersion))) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>