<?php
$borderCollapseVal = $lastTdStyle = "";
$hrefAction = 'href="javascript:void(0)"';

if(!empty($pdfVersion) || !empty($printVersion)) {

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
<style>
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
	color: #fff;
}
.export_div a:hover i.fa-file-pdf {
	color: #f0f0f0;
}
.export_div a i.fa-file-csv {
	color: #fff;
}
.export_div a:hover i.fa-file-csv {
	color: #f0f0f0;
}
.export_div a i.fa-print {
	color: #fff;
}
.export_div a:hover i.fa-print {
	color: #f0f0f0;
}
.summary-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	overflow: hidden;
}
.summary-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 1px 25px;
	font-size: 18px;
	font-weight: 600;
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.summary-body {
	padding: 25px;
}
.summary-section {
	background: #f8f9fa;
	border-radius: 6px;
	padding: 20px;
	margin-bottom: 15px;
}
.summary-section-title {
	color: #495057;
	font-size: 14px;
	font-weight: 600;
	margin-bottom: 15px;
	padding-bottom: 10px;
	border-bottom: 2px solid #dee2e6;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.summary-row {
	display: flex;
	flex-wrap: wrap;
	margin: -8px;
}
.summary-item {
	flex: 0 0 33.333%;
	padding: 8px;
	min-width: 250px;
}
.summary-item-full {
	flex: 0 0 100%;
	padding: 8px;
}
.summary-item-inner {
	background: white;
	border-radius: 4px;
	padding: 15px;
	border-left: 3px solid #667eea;
	transition: all 0.2s;
}
.summary-item-inner:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.summary-label {
	color: #6c757d;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	margin-bottom: 5px;
	display: block;
}
.summary-value {
	color: #212529;
	font-size: 18px;
	font-weight: 600;
	display: block;
}
.summary-value a {
	color: #0066cc;
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
}
.summary-value a:hover {
	text-decoration: underline;
}
.score-bar {
	display: inline-block;
	height: 8px;
	width: 8px;
	border-radius: 2px;
	margin-right: 2px;
}
.score-bar.plus {
	background: #28a745;
}
.score-bar.minus {
	background: #dc3545;
}
</style>
    <div class="summary-card">
    	<div class="summary-header">
    		<span><i class="fas fa-chart-line"></i> <?php echo $spTextSA['Project Summary']?></span>
    		<div>
				<?php
				$pdfLink = "$mainLink&doc_type=pdf";
				$csvLink = "$mainLink&doc_type=export";
				$printLink = "$mainLink&doc_type=print";
				showExportDiv($pdfLink, $csvLink, $printLink);
				?>
			</div>
		</div>
		<div class="summary-body">
			<!-- Project Info Section -->
			<div class="summary-section">
				<div class="summary-section-title"><i class="fas fa-info-circle"></i> Project Information</div>
				<div class="summary-row">
					<div class="summary-item-full">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-globe"></i> <?php echo $spTextSA['Project Url']?></span>
							<span class="summary-value"><a href="<?php echo $projectInfo['url']?>" target="_blank"><?php echo $projectInfo['url']?></a></span>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-star"></i> <?php echo $spText['label']['Score']?></span>
							<span class="summary-value">
								<?php
						        if ($projectInfo['score'] < 0) {
						            $scoreClass = 'minus';
						            $scoreValue = $projectInfo['score'] * -1;
						        } else {
						            $scoreClass = 'plus';
						            $scoreValue = $projectInfo['score'];
						        }
						        for($b=0;$b<=$scoreValue;$b++) echo "<span class='score-bar $scoreClass'></span>";
						        ?>
								<?php echo $projectInfo['score']?>
							</span>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-clock"></i> <?php echo $spText['label']['Updated']?></span>
							<span class="summary-value"><?php echo $projectInfo['last_updated']?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Pages Statistics Section -->
			<div class="summary-section">
				<div class="summary-section-title"><i class="fas fa-file-alt"></i> Pages Statistics</div>
				<div class="summary-row">
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-list-ol"></i> <?php echo $spTextSA['Maximum Pages']?></span>
							<span class="summary-value"><?php echo $projectInfo['max_links']?></span>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-search"></i> <?php echo $spTextSA['Pages Found']?></span>
							<span class="summary-value"><?php echo $projectInfo['total_links']?></span>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-check-circle"></i> <?php echo $spTextSA['Crawled Pages']?></span>
							<span class="summary-value"><?php echo $projectInfo['crawled_links']?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Meta Information Section -->
			<div class="summary-section">
				<div class="summary-section-title"><i class="fas fa-tags"></i> Meta Information</div>
				<div class="summary-row">
					<?php foreach ($metaArr as $col => $label) { ?>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><?php echo $label?></span>
							<span class="summary-value">
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=<?php echo $col?>&')"><?php echo $projectInfo["duplicate_".$col]?></a>
							</span>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>

			<!-- SEO Metrics Section -->
			<div class="summary-section">
				<div class="summary-section-title"><i class="fas fa-chart-bar"></i> SEO Metrics</div>
				<div class="summary-row">
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-link"></i> <?php echo $spTextHome['Backlinks']?></span>
							<span class="summary-value"><?php echo $projectInfo['google_backlinks']?></span>
						</div>
					</div>
					<?php foreach ($seArr as $se) { ?>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-database"></i> <?php echo ucfirst($se)?> <?php echo $spTextHome['Indexed']?></span>
							<span class="summary-value"><?php echo showStatusBadge($projectInfo[$se."_indexed"], 'yesno')?></span>
						</div>
					</div>
					<?php } ?>
					<div class="summary-item">
						<div class="summary-item-inner">
							<span class="summary-label"><i class="fas fa-exclamation-triangle"></i> <?php echo $spText['label']['Brocken']?></span>
							<span class="summary-value"><?php
							    $badgeClass = $projectInfo['brocken'] ? 'badge bg-danger' : 'badge bg-success';
							    $badgeText = $projectInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No'];
							    echo "<span class='$badgeClass py-2 px-3 text-light'>$badgeText</span>";
							?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>
