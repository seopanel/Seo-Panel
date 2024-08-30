<?php
$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";

if(!$summaryPage && (!empty($printVersion) || !empty($pdfVersion))) {
    $pdfVersion ? showPdfHeader($spTextTools['Keyword Search Summary']) : showPrintHeader($spTextTools['Keyword Search Summary']);
    ?>
    <table width="80%" class="search">
    	<?php if (!empty($websiteId)) {?>
    		<tr>
    			<th><?php echo $spText['common']['Website']?>:</th>
        		<td>
        			<?php echo $websiteList[$websiteId]['url']; ?>
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
    <?php
} else {
    echo showSectionHead($spTextTools['Keyword Search Summary']);
    
    // if not summary page show the filters
    if(!$summaryPage) {
    	$scriptName = "webmaster-tools.php";
	    ?>
		<form id='search_form'>
		<?php $submitLink = "scriptDoLoadPost('webmaster-tools.php', 'search_form', 'content', '&sec=viewKeywordSearchSummary')";?>
		<table width="100%" class="search">
			<tr>
				<th><?php echo $spText['common']['Name']?>: </th>
				<td>
					<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>">
				</td>
				<th width="100px"><?php echo $spText['common']['Website']?>: </th>
				<td>
					<select name="website_id" id="website_id" onchange="<?php echo $submitLink?>">
						<option value="">-- <?php echo $spText['common']['Select']?> --</option>
						<?php foreach($websiteList as $websiteInfo){?>
							<?php if($websiteInfo['id'] == $websiteId){?>
								<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
							<?php }else{?>
								<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
							<?php }?>
						<?php }?>
					</select>
				</td>
				<th width="100px;"><?php echo $spText['common']['Period']?>:</th>
	    		<td>
	    			<input type="text" value="<?php echo $fromTime?>" name="from_time"/> 
	    			<input type="text" value="<?php echo $toTime?>" name="to_time"/>
        			<script type="text/javascript">
        			$(function() {
        				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
        			});
        		  	</script>
	    		</td>
				<td><a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="actionbut"><?php echo $spText['button']['Search']?></a></td>
			</tr>
		</table>
		</form>
		<?php	
    } else {
    	$scriptName = "archive.php";
    }

	// url parameters
	$mainLink = SP_WEBPATH."/$scriptName?sec=viewKeywordSearchSummary&website_id=$websiteId&from_time=$fromTime&to_time=$toTime";
	$mainLink .= "&search_name=" . $searchInfo['search_name'] . "&report_type=keyword-search-reports";
	
	// if not summary page show the filters
	if(!$summaryPage) {
	    $directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal&pageno=$pageNo";
	    $pdfLink = "$directLink&doc_type=pdf";
	    $csvLink = "$directLink&doc_type=export";
	    $printLink = "$directLink&doc_type=print";
	    showExportDiv($pdfLink, $csvLink, $printLink);
	}
	
	if (empty($pdfVersion) && empty($cronUserId)) echo $pagingDiv;
}

$baseColCount = count($colList);
$colCount = ($baseColCount * 3) + 2;
?>
<div id='subcontent' style="margin-top: 0px;">

<table id="cust_tab">
	<tr>
		<th id="head" rowspan="2"><?php echo $spText['common']['Website']?></th>
		<?php
		$hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";
		foreach (array_keys($colList) as $i => $colName){
		    
		    $linkClass = "";
            if ($colName == $orderCol) {
                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
                $linkClass .= "sort_".strtolower($orderVal);
            } else {
                $oVal = 'DESC';
            }
            
            $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=$colName&order_val=$oVal', 'content')\">$colList[$colName]</a>";
		    $rowSpan = ($colName == "name") ? 2 : 1;
			?>
			<th rowspan="<?php echo $rowSpan?>" colspan="3" id="head"><?php echo $linkName; ?></th>
			<?php
			
		}
		?>
	</tr>	
	<tr>
		<?php
		$pTxt = str_replace("-", "/", substr($fromTime, -5));
		$cTxt = str_replace("-", "/", substr($toTime, -5));
		foreach ($colList as $colName => $colVal) {
			if ($colName == 'name') continue;
			?>
			<th><?php echo $pTxt; ?></th>
			<th><?php echo $cTxt; ?></th>
			<th>+ / -</th>
			<?php
		}
		?>
	</tr>
	<?php
	if (count($baseReportList) > 0) {
		foreach($baseReportList as $listInfo){
			$keywordId = $listInfo['id'];
			$rangeFromTime = date('Y-m-d', strtotime('-14 days', strtotime($fromTime)));
			$scriptLink = "website_id=$websiteId&keyword_id={$listInfo['id']}&rep=1&from_time=$rangeFromTime&to_time=$toTime";          
			?>
			<tr>
				<td>
					<a href="javascript:void(0)"><?php echo $websiteList[$listInfo['website_id']]['name']; ?></a>
				</td>
				<td colspan="3"><?php echo $listInfo['name']; ?></td>
				<?php
				foreach ($colList as $colName => $colVal){
					if ($colName == 'name') continue;
					
					$currRank = isset($listInfo[$colName]) ? $listInfo[$colName] : 0;
					$prevRank = isset($compareReportList[$keywordId][$colName]) ? $compareReportList[$keywordId][$colName] : 0;
					$rankDiffTxt = "";
					
					// find rank difefrence
					$rankDiff = $currRank - $prevRank;
					$rankDiff = round($rankDiff, 2);
					if ($colName == 'average_position') $rankDiff = $rankDiff * -1;
					
					if ($rankDiff > 0) {
						$rankDiffTxt = "<font class='green'>($rankDiff)</font>";
					} else if ($rankDiff < 0) {
						$rankDiffTxt = "<font class='red'>($rankDiff)</font>";
					} else {
						$rankDiffTxt = "";
					}

					$prevRankLink = scriptAJAXLinkHrefDialog('webmaster-tools.php', 'content', $scriptLink . "&sec=viewKeywordSearchReports", round($prevRank, 2));
					$currRankLink = scriptAJAXLinkHrefDialog('webmaster-tools.php', 'content', $scriptLink . "&sec=viewKeywordSearchReports", round($currRank, 2));
					$graphLink = scriptAJAXLinkHrefDialog('webmaster-tools.php', 'content', $scriptLink . "&sec=viewKeywordSearchGraphReports&attr_type=$colName", '&nbsp;', 'graphicon');
					
					// if pdf report remove links
					if ($pdfVersion) {
						$prevRankLink = str_replace("href='javascript:void(0);'", "", $prevRankLink);
						$currRankLink = str_replace("href='javascript:void(0);'", "", $currRankLink);
						$graphLink = str_replace("href='javascript:void(0);'", "", $graphLink);
					}
				    ?>
					<td><?php echo $prevRankLink; ?></td>
					<td><?php echo $currRankLink; ?></td>
					<td class="text-nowrap"><?php echo $graphLink . " " . $rankDiffTxt; ?></td>
					<?php					
				}
				?>				
			</tr>
		<?php
		}
	} else {
	    ?>
	    <tr><td colspan="<?php echo $colCount?>"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
	    <?php
	}
	?>
</table>
</div>
<?php
if(!$summaryPage && (!empty($printVersion) || !empty($pdfVersion))) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>