<?php echo showSectionHead($spTextSA["Run Project"]); ?>
<div id="run_project">
	<div id="run_info">
		<table width="100%" border="0" cellspacing="0" cellpadding="0px" class="summary_tab">
        	<tr>
        		<td class="topheader" colspan="10"><?php echo $spTextSA['Project Summary']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell" width="20%"><?php echo $spText['common']['Website']?>:</th>
        		<td width="40%" style="text-align: left;"><?php echo htmlspecialchars($projectInfo['url'])?></td>
        		<th width="20%"><?php echo $spTextSA['Maximum Pages']?>:</th>
        		<td><?php echo $projectInfo['max_links']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spText['label']['Updated']?>:</th>
        		<td style="text-align: left;" id="last_updated"><?php echo $projectInfo['last_updated']?></td>
        		<th><?php echo $spTextSA['Pages Found']?>:</th>
        		<td id="total_links"><?php echo $projectInfo['total_links']?></td>
        	</tr>
        	<tr>
        		<th class="leftcell"><?php echo $spTextSA['Crawling Page']?>:</th>
        		<td style="text-align: left;" id="crawling_url"><?php echo htmlspecialchars($projectInfo['crawling_url'])?></td>
        		<th><?php echo $spTextSA['Crawled Pages']?>:</th>
        		<td id="crawled_pages"><?php echo $projectInfo['crawled_links']?></td>
        	</tr>
        </table>
	</div>
	<p class='note'>
		<?php echo $spTextSA['pressescapetostopexecution']?>.
	    <?php echo scriptAJAXLinkHref('siteauditor.php', 'content', "sec=showrunproject&project_id=".$projectInfo['id'], $spText['label']['Click Here'])?> <?php echo $spTextSA['to run project again if you stopped execution']?>.
	</p>
	<div id="subcontmed">
		<script>scriptDoLoad('siteauditor.php?sec=runproject&project_id=<?php echo $projectInfo['id']?>', 'subcontmed');</script>
	</div>
</div>