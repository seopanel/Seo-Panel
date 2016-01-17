<?php
$borderCollapseVal = $lastTdStyle = "";
$hrefAction = 'href="javascript:void(0)"';

if (!empty($pdfVersion) || !empty($printVersion)) {
    
    // if pdf report to be generated
    if ($pdfVersion) {
        showPdfHeader($spTextTools['Auditor Reports']);
        $borderCollapseVal = "border-collapse: collapse;";
        $lastTdStyle = "border-right:1px solid #B0C2CC;";
        $hrefAction = "";
    } else {
        showPrintHeader($spTextTools['Auditor Reports']);
    }
} else {
    ?>
    <div style="float:right;margin-right: 10px;margin-top: -38px;">
		<a href="<?php echo $mainLink?>&doc_type=pdf"><img src="<?php echo SP_IMGPATH?>/icon_pdf.png"></a> &nbsp;
    	<a href="<?php echo $mainLink?>&doc_type=export"><img src="<?php echo SP_IMGPATH?>/icoExport.gif"></a> &nbsp;
    	<a target="_blank" href="<?php echo $mainLink?>&doc_type=print"><img src="<?php echo SP_IMGPATH?>/print_button.gif"></a>
    </div>
<?php

}
?>
<div id="run_project" style="margin-top: 40px;">
	<div id="run_info">
		<table width="100%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab" style="<?php echo $borderCollapseVal; ?>">
        	<tr>
        		<td class="topheader" colspan="10"><?php echo $spTextSA['Project Summary']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell" width="20%"><?php echo $spTextSA['Project Url']?>:</th>
        		<td style="text-align: left;"><?php echo $projectInfo['url']?></td>
        		<th><?php echo $spText['label']['Score']?>:</th>
        		<td style="text-align: left;">
        		    <?php
                        if ($projectInfo['score'] < 0) {
                            $scoreClass = 'minus';
                            $projectInfo['score'] = $projectInfo['score'] * -1;
                        } else {
                            $scoreClass = 'plus';
                        }
                        for ($b=0;$b<=$projectInfo['score'];$b++) {
                            echo "<span class='$scoreClass'>&nbsp;</span>";
                        }
                    ?>
        		    <?php echo $projectInfo['score']?>
    			</td>
        		<th><?php echo $spText['label']['Updated']?>:</th>
        		<td style="text-align: left;<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['last_updated']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spTextSA['Maximum Pages']?>:</th>
        		<td><?php echo $projectInfo['max_links']?></td>
        		<th><?php echo $spTextSA['Pages Found']?>:</th>
        		<td><?php echo $projectInfo['total_links']?></td>
        		<th><?php echo $spTextSA['Crawled Pages']?>:</th>
        		<td style="<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['crawled_links']?></td>
        	</tr>
        	<tr>
            	<?php
                $i = 1;
                foreach ($metaArr as $col => $label) {
                    $class = ($col == "page_title") ? "leftcell" : "";
                    $tdStyle = ($i++ == count($metaArr)) ? $lastTdStyle : "";
                    ?>
    		        <th class="<?php echo $class?>"><?php echo $label?>:</th>
        			<td style="<?php echo $tdStyle;
                    ?>">
        				<a <?php echo $hrefAction;
                    ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=<?php echo $col?>&')"><?php echo $projectInfo["duplicate_".$col]?></a>
        			</td>
    		        <?php	
                }
                ?>
        	</tr>
        	<tr>
        		<th class="leftcell">PR10:</th>
        		<td><?php echo $projectInfo['PR10']?></td>
        		<th>PR9:</th>
        		<td><?php echo $projectInfo['PR9']?></td>
        		<th>PR8:</th>
        		<td style="<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['PR8']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell">PR7:</th>
        		<td><?php echo $projectInfo['PR7']?></td>
        		<th>PR6:</th>
        		<td><?php echo $projectInfo['PR6']?></td>
        		<th>PR5:</th>
        		<td style="<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['PR5']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell">PR4:</th>
        		<td><?php echo $projectInfo['PR4']?></td>
        		<th>PR3:</th>
        		<td><?php echo $projectInfo['PR3']?></td>
        		<th>PR2:</th>
        		<td style="<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['PR2']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell">PR1:</th>
        		<td><?php echo $projectInfo['PR1']?></td>
        		<th>PR0:</th>
        		<td><?php echo $projectInfo['PR0']?></td>
        		<th><?php echo $spText['label']['Brocken']?>:</th>
        		<td style="<?php echo $lastTdStyle; ?>"><?php echo $projectInfo['brocken']?></td>
        	</tr>
        	
        	<tr>
            	<?php
                foreach ($seArr as $i => $se) {
                    $class = $i ? "" :"leftcell";
                    ?>
    		        <th class="<?php echo $class?>"><?php echo ucfirst($se)?> <?php echo $spTextHome['Backlinks']?>:</th>
        			<td><?php echo $projectInfo[$se."_backlinks"]?></td>
    		        <?php	
                }
                ?>
        		<th>&nbsp;</th>
        		<td style="<?php echo $lastTdStyle; ?>">&nbsp;</td>
        	</tr>
        	<tr>
            	<?php
                foreach ($seArr as $i => $se) {
                    $class = $i ? "" :"leftcell";
                    ?>
    		        <th class="<?php echo $class?>"><?php echo ucfirst($se)?> <?php echo $spTextHome['Indexed']?>:</th>
        			<td><?php echo $projectInfo[$se."_indexed"]?></td>
    		        <?php	
                }
                ?>
        		<th>&nbsp;</th>
        		<td style="<?php echo $lastTdStyle; ?>">&nbsp;</td>
        	</tr>
        </table>
	</div>
</div>
<?php
if (!empty($printVersion) || !empty($pdfVersion)) {
    echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>