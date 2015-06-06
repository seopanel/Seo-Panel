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
			<th><?php echo $spText['common']['Period']?>:</th>
    		<td>
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/>
    			<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/>
    			<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    		</td>
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
			<td width="200px">
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
	$directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal";
	?>
	<div style="float:right;margin-right: 10px;margin-top: 20px; clear: both;">
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
	<table width="100%" border="0" cellspacing="0" cellpadding="2px;" class="list">
    	<tr>
    	<td width='33%'>
    	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list" style="<?php echo $borderCollapseVal; ?>">
    	<tr class="listHead">
    		<?php
    		$linkClass = "";
            if ($orderCol == 'keyword') {
                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
                $linkClass .= "sort_".strtolower($orderVal);
            } else {
                $oVal = 'ASC';
            }
    		$linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=keyword&order_val=$oVal', 'content')\">{$spText['common']['Keyword']}</a>";
    		?>
    		<?php if (empty($websiteId)) {?>
    			<td class="left"><?php echo $spText['common']['Website']?></td>
    			<td><?php echo $linkName?></td>
    		<?php } else { ?>
    			<td class="left"><?php echo $linkName?></td>
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
    				<td class="right"><?php echo $linkName; ?></td>
    				<?php
    			}else{
    				?>
    				<td><?php echo $linkName; ?></td>
    				<?php
    			}

    		}
    		?>
    	</tr>
    	<?php
    	$colCount = empty($websiteId) ? $seCount + 2 : $seCount + 1;
    	if(count($list) > 0){
    		$catCount = count($list);
    		$i = 0;
    		foreach($indexList as $keywordId => $rankValue){
    		    $listInfo = $list[$keywordId];
    			$positionInfo = $listInfo['position_info'];
    			$class = ($i % 2) ? "blue_row" : "white_row";
                if($catCount == ($i + 1)){
                    $leftBotClass = "tab_left_bot";
                    $rightBotClass = "tab_right_bot";
                }else{
                    $leftBotClass = "td_left_border td_br_right";
                    $rightBotClass = "td_br_right";
                }
                $scriptLink = "website_id={$listInfo['website_id']}&keyword_id={$listInfo['id']}&rep=1";
    			?>
    			<tr class="<?php echo $class?>">
    				<?php if (empty($websiteId)) {?>
    					<td class="<?php echo $leftBotClass?> left" width='250px;'><?php echo $listInfo['weburl']; ?></td>
    					<td class='td_br_right left'><?php echo $listInfo['name'] ?></td>
    				<?php } else { ?>
    					<td class="<?php echo $leftBotClass?> left" width='100px;'><?php echo $listInfo['name']; ?></td>
    				<?php }?>
    				<?php
    				foreach ($seList as $index => $seInfo){
    				    $rank = empty($positionInfo[$seInfo['id']]['rank']) ? '-' : $positionInfo[$seInfo['id']]['rank'];
    					$rankDiff = empty($positionInfo[$seInfo['id']]['rank_diff']) ? '' : $positionInfo[$seInfo['id']]['rank_diff'];
    				    $rankPadding = "";
    				    if ($rank != '-') {
    				        if ($cronUserId) {
    				            $rankLink = $rank;
    				            $graphLink = '&nbsp;';
    				        } else {
    				            $rankLink = scriptAJAXLinkHref('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $rank);
    				            $graphLink = scriptAJAXLinkHref('graphical-reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], '&nbsp;', 'graphicon');
    				        }

    				        $rankPadding = empty($rankDiff) ? "" : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    					    $rankLink = $rankPadding . $rankLink;

    					    // if pdf report remove links
    					    if ($pdfVersion) {
    					    	$rankLink = str_replace("href='javascript:void(0);'", "", $rankLink);
    					    	$graphLink = str_replace("href='javascript:void(0);'", "", $graphLink);
    					    }

    				    } else {
    				        $rankDiff = $graphLink = "";
    				        $rankLink = $rank;
    				    }
    					if( ($index+1) == $seCount){
    						?>
    						<td class="<?php echo $rightBotClass?>" style='width:100px' nowrap><?php echo "$rankLink $graphLink $rankDiff"; ?></td>
    						<?php
    					}else{
    						?>
    						<td class='td_br_right' style='width:100px' nowrap><?php echo "$rankLink $graphLink $rankDiff"; ?></td>
    						<?php
    					}
    				}
    				?>
    			</tr>
    			<?php
    			$i++;
    		}
    	}else{
    		?>
    		<tr class="blue_row">
    		    <td class="tab_left_bot_noborder">&nbsp;</td>
    		    <td class="td_bottom_border" colspan="<?php echo ($colCount-2)?>"><?php echo $spText['common']['No Records Found']?>!</td>
    		    <td class="tab_right_bot">&nbsp;</td>
    		</tr>
    		<?
    	}
    	?>
    	<tr class="listBot">
    		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
    		<td class="right"></td>
    	</tr>
    	</table>
    	</td>
    	</tr>
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
    			<td class="subheader">Google</td>
    			<td class="subheader">Alexa</td>
    			<td class="subheader">Google</td>
    			<td class="subheader">Alexa</td>
    			<td class="subheader">Bing</td>
    			<td class="subheader">Google</td>
    			<td class="subheader">Bing</td>
    			<td class="subheader"><?php echo $spText['common']['Total']?></td>
    			<td class="subheader"><?php echo $spText['common']['Active']?></td>
    		</tr>
    		<?php if(count($websiteRankList) > 0){
    		    $mainLink = SP_WEBPATH."/seo-tools.php?menu_sec=";
    		    ?>
    			<?php foreach($websiteRankList as $websiteInfo){
    			    $rankLink = $mainLink."rank-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
    			    $backlinkLink = $mainLink."backlink-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
    			    $indexedLink = $mainLink."saturation-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
    			    $totaldirLink = $mainLink."directory-submission&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
    			    $activeDirLink = $mainLink."directory-submission&default_args=".urlencode("sec=reports&active=approved&&website_id=".$websiteInfo['id']);
    			    ?>
    				<tr>
    					<td class="content" style="border-left: none;"><?php echo $websiteInfo['id']?></td>
    					<td class="content">
    						<?php echo $websiteInfo['name'];?><br>
    						<a href="<?php echo $websiteInfo['url'];?>" target="_blank"><?php echo $websiteInfo['url'];?></a>
    					</td>
    					<td class="content"><a href="<?php echo $rankLink?>"><?php echo $websiteInfo['googlerank'];?></a></td>
    					<td class="content"><a href="<?php echo $rankLink?>"><?php echo $websiteInfo['alexarank'];?></a></td>
    					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['google']['backlinks'];?></a></td>
    					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['alexa']['backlinks'];?></a></td>
    					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['msn']['backlinks'];?></a></td>
    					<td class="content"><a href="<?php echo $indexedLink?>"><?php echo $websiteInfo['google']['indexed'];?></a></td>
    					<td class="content"><a href="<?php echo $indexedLink?>"><?php echo $websiteInfo['msn']['indexed'];?></a></td>
    					<td class="contentmid"><a href="<?php echo $totaldirLink?>"><?php echo $websiteInfo['dirsub']['total'];?></a></td>
    					<td class="contentmid"><a href="<?php echo $activeDirLink?>"><?php echo $websiteInfo['dirsub']['active'];?></a></td>
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