<style>
.report-info-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.export_div {
	display: inline-flex;
	gap: 1px;
	align-items: center;
}
.export_div a {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 20px;
	height: 40px;
	border-radius: 8px;
	transition: all 0.3s ease;
	text-decoration: none;
	font-size: 18px;
}
.export_div a:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.export_div a i.fa-file-pdf {
	color: #dc3545;
}
.export_div a:hover i.fa-file-pdf {
	color: #c82333;
}
.export_div a i.fa-file-csv {
	color: #28a745;
}
.export_div a:hover i.fa-file-csv {
	color: #218838;
}
.export_div a i.fa-print {
	color: #007bff;
}
.export_div a:hover i.fa-print {
	color: #0056b3;
}
.report-info-item {
	display: inline-block;
	margin-right: 30px;
	margin-bottom: 8px;
	font-size: 14px;
}
.report-info-label {
	color: #666;
	font-weight: 600;
	margin-right: 8px;
}
.report-info-value {
	color: #333;
	font-weight: 400;
}
.report-info-url {
	color: #0066cc;
	text-decoration: none;
}
.report-info-url:hover {
	text-decoration: underline;
}
.link-report-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	overflow: hidden;
}
.link-report-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 5px 25px;
	font-size: 18px;
	font-weight: 600;
}
.link-report-table {
	width: 100%;
	border-collapse: collapse;
}
.link-report-table thead tr {
	background: #f8f9fa;
	border-bottom: 2px solid #dee2e6;
}
.link-report-table thead th {
	padding: 15px;
	text-align: left;
	font-size: 13px;
	font-weight: 600;
	color: #495057;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.link-report-table tbody tr {
	border-bottom: 1px solid #e9ecef;
	transition: background-color 0.2s;
}
.link-report-table tbody tr:hover {
	background-color: #f8f9fa;
}
.link-report-table tbody td {
	padding: 15px;
	font-size: 14px;
	color: #212529;
	vertical-align: middle;
}
.link-report-table tbody td:first-child {
	font-size: 15px;
}
.link-report-table tbody td:first-child a {
	color: #0066cc;
	text-decoration: none;
	font-weight: 500;
}
.link-report-table tbody td:first-child a:hover {
	text-decoration: underline;
}

/* Score Circle for table */
.score-circle-small {
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.score-progress-small {
	position: relative;
	width: 50px;
	height: 50px;
}

.score-progress-small svg {
	transform: rotate(-90deg);
}

.score-progress-bg-small {
	fill: none;
	stroke: #e2e8f0;
	stroke-width: 6;
}

.score-progress-bar-small {
	fill: none;
	stroke-width: 6;
	stroke-linecap: round;
	transition: stroke-dashoffset 1s ease;
}

.score-progress-bar-small.positive {
	stroke: url(#scoreGradientPositiveSmall);
}

.score-progress-bar-small.negative {
	stroke: url(#scoreGradientNegativeSmall);
}

.score-text-small {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	font-size: 14px;
	font-weight: 700;
	color: #2d3748;
}
</style>

<div class="report-info-card">
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-globe"></i> <?php echo $spTextSA['Project Url']?>:</span>
		<a href="<?php echo $projectInfo['url']?>" target="_blank" class="report-info-value report-info-url"><?php echo $projectInfo['url']?></a>
	</div>
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-clock"></i> <?php echo $spText['label']['Updated']?>:</span>
		<span class="report-info-value"><?php echo $projectInfo['last_updated']?></span>
	</div>
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-list"></i> <?php echo $spText['label']['Total Results']?>:</span>
		<span class="report-info-value"><strong><?php echo $totalResults?></strong></span>
	</div>
<?php
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
	<div style="float: right; margin-top: -10px;">
		<?php
		$mainLink = SP_WEBPATH."/siteauditor.php?project_id=$projectId&pageno=$pageNo"."$filter";
		$pdfLink = "$mainLink&sec=showreport&report_type=rp_links&doc_type=pdf";
		$csvLink = "$mainLink&sec=showreport&report_type=rp_links&doc_type=export";
		$printLink = "$mainLink&sec=showreport&report_type=rp_links&doc_type=print";
		showExportDiv($pdfLink, $csvLink, $printLink);
		?>
	</div>
<?php }?>
</div>

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
?>

<?php echo $pagingDiv;?>

<div class="link-report-card">
	<div class="link-report-header">
		<i class="fas fa-link"></i> <?php echo $spTextSA["Link Reports"]?>
	</div>
	<table class="link-report-table">
		<thead>
			<tr>
				<th style="width: 25%;"><?php echo $page_urlLink?></th>
				<th><?php echo $page_authorityLink?></th>
				<th><?php echo $google_backlinksLink?></th>
				<th><?php echo $google_indexedLink?></th>
				<th><?php echo $bing_indexedLink?></th>
				<th>Robots</th>
				<th>AI Bot</th>
				<th>Mobile</th>
				<th>HTTPS</th>
				<th>OG</th>
				<th>Twitter</th>
				<th><?php echo $scoreLink?></th>
				<th><?php echo $brockenLink?></th>
				<?php if (empty($pdfVersion) && empty($printVersion)) {?>
					<th style="width: 8%;"><?php echo $spText['common']['Action']?></th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
	<?php
	$colCount = 15;
	if(count($list) > 0){
		$catCount = count($list);
		foreach($list as $i => $listInfo){            
            $pageLink = scriptAJAXLinkHref('siteauditor.php', 'subcontent', "sec=pagedetails&report_id={$listInfo['id']}&pageno=$pageNo&order_col=$orderCol&order_val=$orderVal", wordwrap($listInfo['page_url'], 100, "<br>", true));             
            $pageLink = !empty($pdfVersion) ? str_replace("href='javascript:void(0);'", "", $pageLink) : $pageLink;
            ?>
			<tr>
				<td><?php echo $pageLink?></td>
				<td><?php echo $listInfo['page_authority']?></td>
				<td><?php echo $listInfo['google_backlinks']?></td>
				<td><?php echo showStatusBadge($listInfo['google_indexed'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['bing_indexed'], 'yesno')?></td>
				<td><?php echo showStatusBadge(!$listInfo['blocked_by_robots'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['ai_robot_allowed'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['mobile_friendly'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['https_secure'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['has_og_tags'], 'yesno')?></td>
				<td><?php echo showStatusBadge($listInfo['has_twitter_cards'], 'yesno')?></td>
				<td style="text-align: center;">
				    <?php
				    	if ($pdfVersion) {
							echo "<b>{$listInfo['score']}</b>";
						} else {
							$score = $listInfo['score'];
							$isPositive = $score >= 0;
							$absScore = abs($score);
							$maxScore = 100;
							$percentage = min(($absScore / $maxScore) * 100, 100);
							$circumference = 2 * 3.14159 * 22;
							$dashOffset = $circumference - ($percentage / 100) * $circumference;
							?>
							<div class="score-circle-small">
								<div class="score-progress-small">
									<svg width="50" height="50">
										<defs>
											<linearGradient id="scoreGradientPositiveSmall<?php echo $listInfo['id']; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
												<stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
												<stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
											</linearGradient>
											<linearGradient id="scoreGradientNegativeSmall<?php echo $listInfo['id']; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
												<stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
												<stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
											</linearGradient>
										</defs>
										<circle class="score-progress-bg-small" cx="25" cy="25" r="22"></circle>
										<circle class="score-progress-bar-small <?php echo $isPositive ? 'positive' : 'negative'; ?>"
											cx="25" cy="25" r="22"
											stroke="url(#scoreGradient<?php echo $isPositive ? 'Positive' : 'Negative'; ?>Small<?php echo $listInfo['id']; ?>)"
											stroke-dasharray="<?php echo $circumference; ?>"
											stroke-dashoffset="<?php echo $dashOffset; ?>"></circle>
									</svg>
									<div class="score-text-small"><?php echo $score; ?></div>
								</div>
							</div>
							<?php
				        }
				    ?>
				</td>
				<td>
				    <?php
				        $badgeClass = $listInfo['brocken'] ? 'badge bg-danger' : 'badge bg-success';
				        $badgeText = $listInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No'];
				        echo "<span class='$badgeClass py-2 px-3 text-light'>$badgeText</span>";
				    ?>
				</td>
				<?php if (empty($pdfVersion) && empty($printVersion)) {?>
					<td>
					    <select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select" style="width: 120px;" onchange="doAction('siteauditor.php', 'subcontent', 'report_id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>&order_col=<?php echo $orderCol?>&order_val=<?php echo $orderVal?>', 'action<?php echo $listInfo['id']?>')">
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
		?>
		<tr>
			<td colspan="<?php echo $colCount?>" style="text-align: center; padding: 40px; color: #6c757d;">
				<i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
				<div style="font-size: 16px;"><?php echo $spText['common']['No Records Found']?></div>
			</td>
		</tr>
		<?php
	}
	?>
		</tbody>
	</table>
</div>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
} else if(empty($printVersion)) {
    ?>
    <table class="actionSec mt-2">
    	<tr>
        	<td>
             	<a onclick="scriptDoLoad('siteauditor.php?sec=importlinks&project_id=<?php echo $projectId?>', 'content')" href="javascript:void(0);" class="btn btn-primary">
             		<?php echo $spTextSA['Import Project Links']?>
             	</a>
        	</td>
    	</tr>
    </table>
	<?php 
}?>