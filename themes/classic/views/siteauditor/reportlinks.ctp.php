<table  width="98%" border="0" cellspacing="0px" cellpadding="0px" align="center" class="report_head">
	<tr>
		<td align="left" valign="bottom">
			<div><b><?php echo $spTextSA['Project Url']?></b>: <?php echo $projectInfo['url']?></div>
			<div style="margin-bottom: 0px;"><b><?php echo $spText['label']['Updated']?></b>: <?php echo $projectInfo['last_updated']?></div>
			<div style="margin-bottom: 0px;margin-top: 10px;"><b><?php echo $spText['label']['Total Results']?></b>: <?php echo $totalResults?></div>
		</td>

<?php
$borderCollapseVal = "";
$hrefAction = 'href="javascript:void(0)"';
$mainLink = SP_WEBPATH."/siteauditor.php?project_id=$projectId&pageno=$pageNo"."$filter";
foreach ($headArr as $col => $val) {
    $linkName = $col."Link";
    $linkClass = "";
    if ($col == $orderCol) {
        $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
        $linkClass .= "sort_".strtolower($orderVal);
    } else {
        $oVal = $orderVal;
    }
    $$linkName = "<a id='sortLink' class='$linkClass' href='javascript:void(0)' onclick=\"scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&order_col=$col&order_val=$oVal')\">$val</a>";
}

if(!empty($pdfVersion) || !empty($printVersion)) {

	// if pdf report to be generated
	if ($pdfVersion) {
		showPdfHeader($spTextTools['Auditor Reports']);
		$borderCollapseVal = "border-collapse: collapse;";
		$hrefAction = "";
	} else {
		showPrintHeader($spTextTools['Auditor Reports']);
	}

} else {
    ?>
	<td align="right" valign="bottom">
		<a href="<?php echo $mainLink?>&sec=showreport&report_type=rp_links&doc_type=pdf"><img src="<?php echo SP_IMGPATH?>/icon_pdf.png"></a> &nbsp;
		<a href="<?php echo $mainLink?>&sec=showreport&report_type=rp_links&doc_type=export"><img src="<?php echo SP_IMGPATH?>/icoExport.gif"></a> &nbsp;
		<a target="_blank" href="<?php echo $mainLink?>&sec=showreport&report_type=rp_links&doc_type=print"><img src="<?php echo SP_IMGPATH?>/print_button.gif"></a>
		<?php echo $pagingDiv?>
	</td>
<?php }?>
	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list" style="<?php echo $borderCollapseVal; ?>">
	<tr class="plainHead">
		<td class="left" style="width: 30%;"><?php echo $page_urlLink?></td>
		<td><?php echo $pagerankLink?></td>
		<td><?php echo $google_backlinksLink?></td>
		<td><?php echo $bing_backlinksLink?></td>
		<td><?php echo $google_indexedLink?></td>
		<td><?php echo $bing_indexedLink?></td>
		<td><?php echo $external_linksLink?></td>
		<td><?php echo $total_linksLink?></td>
		<td><?php echo $scoreLink?></td>
		<td><?php echo $brockenLink?></td>
		<?php if (empty($pdfVersion) && empty($printVersion)) {?>
			<td class="right"><?php echo $spText['common']['Action']?></td>
		<?php }?>
	</tr>
	<?php
	$colCount = 11;
	if(count($list) > 0){
		$catCount = count($list);
		foreach($list as $i => $listInfo){
			$class = ($i % 2) ? "blue_row" : "white_row";
			if( !$i || ($catCount != ($i + 1)) ){
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }

            $pageLink = scriptAJAXLinkHref('siteauditor.php', 'subcontent', "sec=pagedetails&report_id={$listInfo['id']}&pageno=$pageNo&order_col=$orderCol&order_val=$orderVal", wordwrap($listInfo['page_url'], 100, "<br>", true));
            $pageLink = !empty($pdfVersion) ? str_replace("href='javascript:void(0);'", "", $pageLink) : $pageLink;
            ?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?> left"><?php echo $pageLink?></td>
				<td class="td_br_right"><?php echo $listInfo['pagerank']?></td>
				<td class="td_br_right"><?php echo $listInfo['google_backlinks']?></td>
				<td class="td_br_right"><?php echo $listInfo['bing_backlinks']?></td>
				<td class="td_br_right"><?php echo $listInfo['google_indexed']?></td>
				<td class="td_br_right"><?php echo $listInfo['bing_indexed']?></td>
				<td class="td_br_right"><?php echo $listInfo['external_links']?></td>
				<td class="td_br_right"><?php echo $listInfo['total_links']?></td>
				<td class="td_br_right" style="text-align: right;">
				    <?php
				    	if ($pdfVersion) {
							echo "<b>{$listInfo['score']}</b>";
						} else {

					        if ($listInfo['score'] < 0) {
					            $scoreClass = 'minus';
					            $listInfo['score'] = $listInfo['score'] * -1;
					        } else {
					            $scoreClass = 'plus';
					        }

					        for($b = 0; $b <= $listInfo['score']; $b++) {
								echo "<span class='$scoreClass'>&nbsp;</span>";
							}

				        }
				    ?>
				</td>
				<td class="td_br_right">
				    <?php echo $listInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No']; ?>
				</td>
				<?php if (empty($pdfVersion) && empty($printVersion)) {?>
					<td class="<?php echo $rightBotClass?>">
					    <select style="width: 80px;" name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('siteauditor.php', 'subcontent', 'report_id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>&order_col=<?php echo $orderCol?>&order_val=<?php echo $orderVal?>', 'action<?php echo $listInfo['id']?>')">
							<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
							<option value="pagedetails"><?php echo $spTextSA['Page Details']?></option>
							<option value="checkscore"><?php echo $spTextSA['Check Score']?></option>
							<option value="deletepage"><?php echo $spText['common']['Delete']?></option>
						</select>
					</td>
				<?php }?>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2, '', true);
	}
	?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
</table>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
} else if(empty($printVersion)) {?>
    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
    	<tr>
        	<td style="padding-top: 6px;">
             	<a onclick="scriptDoLoad('siteauditor.php?sec=importlinks&project_id=<?php echo $projectId?>', 'content')" href="javascript:void(0);" class="actionbut">
             		<?php echo $spTextSA['Import Project Links']?>
             	</a>
        	</td>
    	</tr>
    </table>
<?php } ?>