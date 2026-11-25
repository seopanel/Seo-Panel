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
.linkgraph-report-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	overflow: hidden;
}
.linkgraph-report-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 5px 25px;
	font-size: 18px;
	font-weight: 600;
}
.linkgraph-table {
	width: 100%;
	border-collapse: collapse;
}
.linkgraph-table thead tr {
	background: #f8f9fa;
	border-bottom: 2px solid #dee2e6;
}
.linkgraph-table thead th {
	padding: 15px;
	text-align: left;
	font-size: 13px;
	font-weight: 600;
	color: #495057;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.linkgraph-table tbody tr {
	border-bottom: 1px solid #e9ecef;
	transition: background-color 0.2s;
}
.linkgraph-table tbody tr:hover {
	background-color: #f8f9fa;
}
.linkgraph-table tbody td {
	padding: 15px;
	font-size: 14px;
	color: #212529;
	vertical-align: top;
}
.linkgraph-table tbody td.number-cell {
	text-align: center;
	font-weight: 600;
	color: #6c757d;
	width: 60px;
}
.linkgraph-table tbody td.count-cell {
	text-align: center;
	font-weight: 600;
	width: 100px;
}
.linkgraph-page-url {
	color: #0066cc;
	text-decoration: none;
	word-break: break-all;
}
.linkgraph-page-url:hover {
	text-decoration: underline;
}
</style>

<?php if (!empty($linkGraphNotEnabled)) { ?>
	<div class="report-info-card">
		<i class="fas fa-info-circle" style="color:#b8790a;"></i>
		<?php echo $spTextSA['Store links in page is required for this report'] ?? 'This report needs "Store links in page" enabled on the project - edit the project and turn it on, then re-run the crawl.'?>
		<a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=edit&project_id=<?php echo intval($projectId)?>')"><?php echo $spTextSA['Edit Project Settings'] ?? 'Edit Project Settings'?></a>
	</div>
<?php } else { ?>

<div class="report-info-card">
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-globe"></i> <?php echo $spTextSA['Project Url']?>:</span>
		<a href="<?php echo htmlspecialchars($projectInfo['url'], ENT_QUOTES)?>" target="_blank" class="report-info-value report-info-url"><?php echo htmlspecialchars($projectInfo['url'])?></a>
	</div>
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-clock"></i> <?php echo $spText['label']['Updated']?>:</span>
		<span class="report-info-value"><?php echo $projectInfo['last_updated']?></span>
	</div>
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-list"></i> <?php echo $spText['label']['Total Results']?>:</span>
		<span class="report-info-value"><strong><?php echo $totalResults?></strong></span>
	</div>
	<div class="report-info-item">
		<span class="report-info-label"><i class="fas fa-unlink" style="color:#dc3545;"></i> <?php echo $spTextSA['Orphan Pages'] ?? 'Orphan Pages'?>:</span>
		<span class="report-info-value"><strong style="color:<?php echo $orphanCount > 0 ? '#dc3545' : '#28a745'?>;"><?php echo $orphanCount?></strong></span>
	</div>
<?php
$borderCollapseVal = "";
$hrefAction = 'href="javascript:void(0)"';
$mainLink = SP_WEBPATH."/siteauditor.php?project_id=$projectId&sec=showreport&report_type=link_graph&pageno=$pageNo".$filter;

// a closure assigned to a local variable, NOT a named function
// declaration - this view can be include()'d more than once in the
// same PHP process (e.g. a PDF export right after a screen render), and
// a plain "function ... () {}" here would fatal with "Cannot redeclare"
// on the second include
$linkGraphSortLink = function($col, $label) use ($orderCol, $orderVal) {
	$oVal = ($col == $orderCol) ? (($orderVal == 'DESC') ? 'ASC' : 'DESC') : 'ASC';
	$linkClass = ($col == $orderCol) ? ('sort_' . strtolower($orderVal)) : '';
	return "<a id='sortLink' class='$linkClass' href='javascript:void(0)' onclick=\"scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&report_type=link_graph&order_col=$col&order_val=$oVal')\">$label</a>";
};

if (!empty($pdfVersion) || !empty($printVersion)) {
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

<div class="linkgraph-report-card">
	<div class="linkgraph-report-header">
		<i class="fas fa-sitemap"></i> <?php echo $spTextSA['Internal Link Graph'] ?? 'Internal Link Graph'?>
	</div>
	<table class="linkgraph-table">
		<thead>
			<tr>
				<th style="width: 60px; text-align: center;"><?php echo $spText['common']['No']?></th>
				<th><?php echo $linkGraphSortLink('page_url', $spTextSA['Page Link'])?></th>
				<th style="width: 180px; text-align: center;"><?php echo $linkGraphSortLink('inbound_links', $spTextSA['Inbound Internal Links'] ?? 'Inbound Internal Links')?></th>
				<th style="width: 180px; text-align: center;"><?php echo $linkGraphSortLink('outbound_links', $spTextSA['Outbound Internal Links'] ?? 'Outbound Internal Links')?></th>
				<th style="width: 100px; text-align: center;"><?php echo $spTextSA['Orphan'] ?? 'Orphan'?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$colCount = 5;
			if (count($list) > 0) {
				foreach ($list as $i => $pageInfo) {
					$rowNum = $this->paging->start + $i + 1;
					?>
					<tr>
						<td class="number-cell"><?php echo $rowNum?></td>
						<td>
							<a target="_blank" class="linkgraph-page-url" href="<?php echo htmlspecialchars($pageInfo['page_url'], ENT_QUOTES)?>"><?php echo htmlspecialchars($pageInfo['page_url'])?></a>
						</td>
						<td class="count-cell"><?php echo intval($pageInfo['inbound_links'])?></td>
						<td class="count-cell"><?php echo intval($pageInfo['outbound_links'])?></td>
						<td class="count-cell">
							<?php if (!empty($pageInfo['is_orphan'])) { ?>
								<span class="badge bg-danger"><?php echo $spTextSA['Orphan'] ?? 'Orphan'?></span>
							<?php } ?>
						</td>
					</tr>
					<?php
				}
			} else {
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
if (!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>
<?php } ?>
