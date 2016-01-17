<?php

$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";

if (!empty($printVersion) || !empty($pdfVersion)) {
    $pdfVersion ? showPdfHeader($spTextTools['Keyword Position Summary']) : showPrintHeader($spTextTools['Keyword Position Summary']);
    ?>
    <table width="80%" border="0" cellspacing="0" cellpadding="0" class="search">
    	<?php if (!empty($websiteUrl)) {
    ?>
    		<tr>
    			<th><?php echo $spText['common']['Website']?>:</th>
        		<td>
        			<?php echo $websiteUrl;
    ?>
    			</td>
    		</tr>
		<?php 
}
    ?>
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
	<?php $submitLink = "scriptDoLoadPost('reports.php', 'search_form', 'content', '&sec=reportsum')";
    ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
		<tr>
			<th><?php echo $spText['common']['Name']?>: </th>
			<td>
				<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>">
			</td>
			<th width="100px"><?php echo $spText['common']['Website']?>: </th>
			<td width="160px">
				<select name="website_id" id="website_id" style='width:100px;' onchange="<?php echo $submitLink?>">
					<option value="">-- <?php echo $spText['common']['All']?> --</option>
					<?php foreach ($websiteList as $websiteInfo) {
    ?>
						<?php if ($websiteInfo['id'] == $websiteId) {
    ?>
							<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
						<?php 
} else {
    ?>
							<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
						<?php 
}
    ?>
					<?php 
}
    ?>
				</select>
			</td>
			<th width="100px;"><?php echo $spText['common']['Period']?>:</th>
    		<td width="236px">
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/> 
    			<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/> 
    			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/> 
    			<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    		</td>
			<td><a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="actionbut"><?php echo $spText['button']['Search']?></a></td>
		</tr>
	</table>
	</form>
	<?php
    if (empty($list)) {
        ?>
		<p class='note'>
			<?php echo $spText['common']['No Keywords Found']?>.
			<a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=new&amp;website_id=')"><?php echo $spText['label']['Click Here']?></a> <?php echo $spTextKeyword['to create new keywords']?>.
		</p>
		<?php
        exit;
    }

    // url parameters
    $mainLink = SP_WEBPATH."/reports.php?sec=reportsum&website_id=$websiteId&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
    $directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal";
    ?>
	<div style="float:left;margin-right: 10px;">
		<a href="<?php echo $directLink?>&doc_type=pdf"><img src="<?php echo SP_IMGPATH?>/icon_pdf.png"></a> &nbsp;
		<a href="<?php echo $directLink?>&doc_type=export"><img src="<?php echo SP_IMGPATH?>/icoExport.gif"></a> &nbsp;
		<a target="_blank" href="<?php echo $directLink?>&doc_type=print"><img src="<?php echo SP_IMGPATH?>/print_button.gif?1"></a>
	</div>
	
<?php 
}?>
<?php echo $pagingDiv?>
<div id='subcontent' style="margin-top: 0px;">
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
        
        $hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";
        
        $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=keyword&order_val=$oVal', 'content')\">{$spText['common']['Keyword']}</a>";
        ?>		
		<?php if (empty($websiteId)) {
    ?>
			<td class="left"><?php echo $spText['common']['Website']?></td>
			<td><?php echo $linkName?></td>
		<?php 
} else {
    ?>
			<td class="left"><?php echo $linkName?></td>
		<?php 
}?>
		<?php
        $seCount = count($seList);
        foreach ($seList as $i => $seInfo) {
            $linkClass = "";
            if ($seInfo['id'] == $orderCol) {
                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
                $linkClass .= "sort_".strtolower($oVal);
            } else {
                $oVal = 'ASC';
            }
            $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col={$seInfo['id']}&order_val=$oVal', 'content')\">{$seInfo['domain']}</a>";
            
            if (($i+1) == $seCount) {
                ?>
				<td class="right"><?php echo $linkName;
                ?></td>
				<?php	
            } else {
                ?>
				<td><?php echo $linkName;
                ?></td>
				<?php

            }
        }
        ?>		
	</tr>
	<?php
    $colCount = empty($websiteId) ? $seCount + 2 : $seCount + 1;
    if (count($list) > 0) {
        $catCount = count($list);
        $i = 0;
        foreach ($indexList as $keywordId => $rankValue) {
            $listInfo = $list[$keywordId];
            $positionInfo = $listInfo['position_info'];
            $class = ($i % 2) ? "blue_row" : "white_row";
            if ($catCount == ($i + 1)) {
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            } else {
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            $scriptLink = "website_id={$listInfo['website_id']}&keyword_id={$listInfo['id']}&rep=1&from_time=$fromTime&to_time=$toTime";
            ?>
			<tr class="<?php echo $class?>">				
				<?php if (empty($websiteId)) {
    ?>
					<td class="<?php echo $leftBotClass?> left" width='250px;'><?php echo $listInfo['weburl'];
    ?></td>
					<td class='td_br_right left'><?php echo $listInfo['name'] ?></td>
				<?php 
} else {
    ?>
					<td class="<?php echo $leftBotClass?> left" width='100px;'><?php echo $listInfo['name'];
    ?></td>
				<?php 
}
            ?>				
				<?php
                foreach ($seList as $index => $seInfo) {
                    $rank = empty($positionInfo[$seInfo['id']]['rank']) ? '-' : $positionInfo[$seInfo['id']]['rank'];
                    $rankDiff = empty($positionInfo[$seInfo['id']]['rank_diff']) ? '' : $positionInfo[$seInfo['id']]['rank_diff'];
                    $rankPadding = "";
                    if ($rank != '-') {
                        $rankLink = scriptAJAXLinkHrefDialog('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $rank);
                        $graphLink = scriptAJAXLinkHrefDialog('graphical-reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], '&nbsp;', 'graphicon');
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
                    if (($index+1) == $seCount) {
                        ?>
						<td class="<?php echo $rightBotClass?>" style='width:100px' nowrap><?php echo "$rankLink $graphLink $rankDiff";
                        ?></td>	
						<?php	
                    } else {
                        ?>
						<td class='td_br_right' style='width:100px' nowrap><?php echo "$rankLink $graphLink $rankDiff";
                        ?></td>
						<?php

                    }
                }
            ?>				
			</tr>
			<?php
            $i++;
        }
    } else {
        ?>
		<tr class="blue_row">
		    <td class="tab_left_bot_noborder">&nbsp;</td>
		    <td class="td_bottom_border" colspan="<?php echo($colCount-2)?>"><?php echo $spText['common']['No Records Found']?>!</td>
		    <td class="tab_right_bot">&nbsp;</td>
		</tr>
		<?php

    }
    ?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
	</table>
	</td>
	</tr>
</table>
</div>
<?php
if (!empty($printVersion) || !empty($pdfVersion)) {
    echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>