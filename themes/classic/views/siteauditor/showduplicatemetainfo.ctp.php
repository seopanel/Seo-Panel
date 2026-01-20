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
.duplicate-report-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	overflow: hidden;
}
.duplicate-report-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 5px 25px;
	font-size: 18px;
	font-weight: 600;
}
.duplicate-table {
	width: 100%;
	border-collapse: collapse;
}
.duplicate-table thead tr {
	background: #f8f9fa;
	border-bottom: 2px solid #dee2e6;
}
.duplicate-table thead th {
	padding: 15px;
	text-align: left;
	font-size: 13px;
	font-weight: 600;
	color: #495057;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.duplicate-table tbody tr {
	border-bottom: 1px solid #e9ecef;
	transition: background-color 0.2s;
}
.duplicate-table tbody tr:hover {
	background-color: #f8f9fa;
}
.duplicate-table tbody td {
	padding: 15px;
	font-size: 14px;
	color: #212529;
	vertical-align: top;
}
.duplicate-table tbody td.number-cell {
	text-align: center;
	font-weight: 600;
	color: #6c757d;
	width: 60px;
}
.duplicate-table tbody td.count-cell {
	text-align: center;
	font-weight: 600;
	width: 100px;
}
.duplicate-table tbody td.count-cell .badge {
	display: inline-block;
	padding: 6px 12px;
	border-radius: 20px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	font-size: 13px;
	font-weight: 600;
}
.duplicate-content {
	background: #f8f9fa;
	padding: 10px 12px;
	border-radius: 4px;
	border-left: 3px solid #667eea;
	font-size: 13px;
	line-height: 1.5;
	margin-bottom: 5px;
	word-break: break-word;
}
.page-url-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}
.page-url-item {
	background: white;
	padding: 8px 12px;
	border-radius: 4px;
	border: 1px solid #e0e0e0;
	font-size: 12px;
}
.page-url-item a {
	color: #0066cc;
	text-decoration: none;
	word-break: break-all;
}
.page-url-item a:hover {
	text-decoration: underline;
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
$borderCollapseVal = "";
$hrefAction = 'href="javascript:void(0)"';
$mainLink = SP_WEBPATH."/siteauditor.php?project_id=$projectId&sec=showreport&report_type=$repType&pageno=$pageNo".$filter;
foreach ($headArr as $col => $val) {
    if( ($col == $repType) || ($col == 'count')) {
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
	<div style="float: right; margin-top: -10px;">
		<?php
		$pdfLink = "$mainLink&doc_type=pdf";
		$csvLink = "$mainLink&doc_type=export";
		$printLink = "$mainLink&doc_type=print";
		showExportDiv($pdfLink, $csvLink, $printLink);
		?>
	</div>
<?php }?>
</div>

<?php echo $pagingDiv;?>

<div class="duplicate-report-card">
	<div class="duplicate-report-header">
		<i class="fas fa-copy"></i> <?php echo $headArr[$repType]?> <?php echo $spText['common']['Reports']?>
	</div>
	<table class="duplicate-table">
		<thead>
			<tr>
				<th style="width: 60px; text-align: center;"><?php echo $spText['common']['No']?></th>
				<th><?php
					$linkLabel = $repType."Link";
					echo $$linkLabel;
				?></th>
				<th style="width: 30%;"><?php echo $headArr["page_urls"]?></th>
				<th style="width: 100px; text-align: center;"><?php echo $countLink?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$colCount = 4;
			if(count($list) > 0){
				$catCount = count($list);
				foreach($list as $i => $listInfo){
					?>
					<tr>
						<td class="number-cell"><?php echo $i+1?></td>
						<td>
							<div class="duplicate-content">
								<?php echo htmlspecialchars($listInfo[$repType])?>
							</div>
						</td>
						<td>
							<div class="page-url-list">
								<?php foreach($listInfo['page_urls'] as $urlInfo) { ?>
									<div class="page-url-item">
										<a target='_blank' href='<?php echo $urlInfo['page_url']?>'><?php echo $urlInfo['page_url']?></a>
									</div>
								<?php } ?>
							</div>
						</td>
						<td class="count-cell">
							<span class="badge"><?php echo $listInfo['count']?></span>
						</td>
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
}
?>
