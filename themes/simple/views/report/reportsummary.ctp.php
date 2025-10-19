<?php

$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";

if(!empty($printVersion) || !empty($pdfVersion)) {
    $pdfVersion ? showPdfHeader($spTextTools['Keyword Position Summary']) : showPrintHeader($spTextTools['Keyword Position Summary']);
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
    <?php
} else {
    echo showSectionHead($spTextTools['Keyword Position Summary']);
    ?>
	<form id='search_form'>
	<?php $submitLink = "scriptDoLoadPost('reports.php', 'search_form', 'content', '&sec=reportsum')";?>
	<table class="search">
		<tr>
			<th><?php echo $spText['common']['Name']?>: </th>
			<td>
				<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
			</td>
			<th class="pl-4"><?php echo $spText['common']['Website']?>: </th>
			<td>
				<select name="website_id" id="website_id" onchange="<?php echo $submitLink?>" class="custom-select">
					<option value="">-- <?php echo $spText['common']['All']?> --</option>
					<?php foreach($websiteList as $websiteInfo){?>
						<?php if($websiteInfo['id'] == $websiteId){?>
							<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
						<?php }else{?>
							<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
			<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
    		<td>
    			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
    			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
				<script>
				  $( function() {
				    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
				  } );
			  	</script>
			</td>
			<td style="text-align: center;">
			  	<a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
			</td>
		</tr>

	</table>
	</form>
	<?php
	if(empty($list)){
		?>
		<div class="alert alert-info">
			<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>.
			<a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=new&amp;website_id=')" class="alert-link"><?php echo $spText['label']['Click Here']?></a> <?php echo $spTextKeyword['to create new keywords']?>.
		</div>
		<?php
		exit;
	}

	// url parameters
	$mainLink = SP_WEBPATH."/reports.php?sec=reportsum&website_id=$websiteId&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
	$directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal&pageno=$pageNo";
	
	// export links
	$pdfLink = "$directLink&doc_type=pdf";
	$csvLink = "$directLink&doc_type=export";
	$printLink = "$directLink&doc_type=print";
	showExportDiv($pdfLink, $csvLink, $printLink);
	?>
	<?php echo $pagingDiv?>
<?php }?>

<div id='subcontent' class="table-responsive">

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
			<th id="head" rowspan="2"><?php echo $linkName?></th>
			<th id="head" rowspan="2"><?php echo $spText['common']['Website']?></th>
		<?php } else { ?>
			<th id="head" rowspan="2"><?php echo $linkName?></th>
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
	$colCount = empty($websiteId) ? ($seCount * 3) + 2 : ($seCount * 3) + 1; 
	if (count($list) > 0) {
		foreach($indexList as $keywordId => $rankValue){
		    $listInfo = $list[$keywordId];
			$positionInfo = $listInfo['position_info'];
            $rangeFromTime = date('Y-m-d', strtotime('-14 days', strtotime($fromTime)));
            $scriptLink = "website_id=$websiteId&keyword_id={$listInfo['id']}&rep=1&from_time=$rangeFromTime&to_time=$toTime";          
			?>
			<tr>				
				<?php if (empty($websiteId)) {?>
					<td><?php echo $listInfo['name'] ?></td>
					<td><?php echo $listInfo['webname']; ?></td>
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
				    ?>
					<td><?php echo $prevRankLink; ?></td>
					<td><?php echo $currRankLink; ?></td>
					<td><?php echo $graphLink . " " . $rankDiffTxt; ?></td>
					<?php					
				}
				?>				
			</tr>
			<?php
		}
	}
	?>
</table>
</div>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>