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

	// PDF/Print content
	$paLevelFirst = defined('SA_PA_CHECK_LEVEL_FIRST') ? SA_PA_CHECK_LEVEL_FIRST : 40;
	$paLevelSecond = defined('SA_PA_CHECK_LEVEL_SECOND') ? SA_PA_CHECK_LEVEL_SECOND : 75;
	?>
	<table width="100%" cellpadding="5" cellspacing="0" style="<?php echo $borderCollapseVal?>border:1px solid #B0C2CC;margin-bottom:15px;">
		<tr style="background:#f5f5f5;">
			<td colspan="4" style="border-bottom:1px solid #B0C2CC;font-weight:bold;font-size:16px;padding:10px;">
				<?php echo $spTextSA['Project Summary']?>
			</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;width:25%;"><strong><?php echo $spTextSA['Project Url']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;" colspan="3"><?php echo $projectInfo['url']?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spText['label']['Updated']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo $projectInfo['last_updated']?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spText['label']['Score']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo round($projectInfo['score'], 2)?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Maximum Pages']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['max_links'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Pages Found']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['total_links'])?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Crawled Pages']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;" colspan="3"><?php echo number_format($projectInfo['crawled_links'])?></td>
		</tr>
	</table>

	<table width="100%" cellpadding="5" cellspacing="0" style="<?php echo $borderCollapseVal?>border:1px solid #B0C2CC;margin-bottom:15px;">
		<tr style="background:#f5f5f5;">
			<td colspan="4" style="border-bottom:1px solid #B0C2CC;font-weight:bold;font-size:16px;padding:10px;">
				SEO Metrics
			</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;width:25%;"><strong><?php echo $spTextSA['Excellent Page Authority'] ?? 'Excellent Page Authority'?> (&ge;<?php echo $paLevelSecond?>):</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['pa_excellent'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Good Page Authority'] ?? 'Good Page Authority'?> (<?php echo $paLevelFirst?>-<?php echo $paLevelSecond-1?>):</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['pa_good'])?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Low Page Authority'] ?? 'Low Page Authority'?> (1-<?php echo $paLevelFirst-1?>):</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['pa_low'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['No Page Authority'] ?? 'No Page Authority'?> (0):</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['pa_none'])?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Has Backlinks']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['google_backlinks'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong>No Backlinks:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['no_backlinks'])?></td>
		</tr>
		<?php foreach ($seArr as $se) { ?>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo ucfirst($se)?> <?php echo $spTextHome['Indexed']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo[$se."_indexed"])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo ucfirst($se)?> Not Indexed:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo[$se."_not_indexed"])?></td>
		</tr>
		<?php } ?>
	</table>

	<table width="100%" cellpadding="5" cellspacing="0" style="<?php echo $borderCollapseVal?>border:1px solid #B0C2CC;margin-bottom:15px;">
		<tr style="background:#f5f5f5;">
			<td colspan="4" style="border-bottom:1px solid #B0C2CC;font-weight:bold;font-size:16px;padding:10px;">
				<?php echo $spTextSA['Modern SEO Features'] ?? 'Modern SEO Features'?>
			</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;width:25%;"><strong><?php echo $spTextSA['Mobile Friendly']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['mobile_friendly'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['HTTPS Secure']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['https_secure'])?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['AI Robot Compatibility']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['ai_robot_allowed'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Open Graph Tags']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['has_og_tags'])?></td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Twitter Cards']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['has_twitter_cards'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong><?php echo $spTextSA['Robots.txt Allowed'] ?? 'Robots.txt Allowed'?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['allowed_by_robots'])?></td>
		</tr>
	</table>

	<table width="100%" cellpadding="5" cellspacing="0" style="<?php echo $borderCollapseVal?>border:1px solid #B0C2CC;margin-bottom:15px;">
		<tr style="background:#f5f5f5;">
			<td colspan="4" style="border-bottom:1px solid #B0C2CC;font-weight:bold;font-size:16px;padding:10px;">
				Areas for Improvement
			</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd;width:25%;"><strong><?php echo $spText['label']['Brocken']?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['brocken'])?></td>
			<td style="border-bottom:1px solid #ddd;"><strong>No Backlinks:</strong></td>
			<td style="border-bottom:1px solid #ddd;"><?php echo number_format($projectInfo['no_backlinks'])?></td>
		</tr>
	</table>

	<table width="100%" cellpadding="5" cellspacing="0" style="<?php echo $borderCollapseVal?>border:1px solid #B0C2CC;margin-bottom:15px;">
		<tr style="background:#f5f5f5;">
			<td colspan="4" style="border-bottom:1px solid #B0C2CC;font-weight:bold;font-size:16px;padding:10px;">
				Meta Information
			</td>
		</tr>
		<?php foreach ($metaArr as $meta => $val) { ?>
		<tr>
			<td style="border-bottom:1px solid #ddd;width:25%;"><strong>Duplicate <?php echo $val?>:</strong></td>
			<td style="border-bottom:1px solid #ddd;" colspan="3"><?php echo number_format($projectInfo["duplicate_".$meta])?></td>
		</tr>
		<?php } ?>
	</table>
	<?php

} else {
?>
<style>
@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(20px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

@keyframes pulse {
	0%, 100% {
		opacity: 1;
	}
	50% {
		opacity: 0.8;
	}
}

@keyframes countUp {
	from {
		opacity: 0;
		transform: translateY(10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

@keyframes numberGlow {
	0%, 100% {
		filter: drop-shadow(0 0 0 transparent);
	}
	50% {
		filter: drop-shadow(0 0 8px rgba(102, 126, 234, 0.4));
	}
}

.export_div {
	display: inline-flex;
	gap: 8px;
	align-items: center;
}
.export_div a {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 36px;
	height: 36px;
	border-radius: 8px;
	transition: all 0.3s ease;
	text-decoration: none;
	font-size: 16px;
	background: rgba(255, 255, 255, 0.2);
}
.export_div a:hover {
	transform: translateY(-2px);
	background: rgba(255, 255, 255, 0.3);
	box-shadow: 0 4px 12px rgba(0,0,0,0.2);
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
	border: none;
	border-radius: 16px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 10px 40px rgba(0,0,0,0.08);
	overflow: hidden;
	animation: fadeInUp 0.6s ease-out;
}

.summary-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 5px 25px;
	font-size: 18px;
	font-weight: 600;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.summary-body {
	padding: 30px;
	background: linear-gradient(to bottom, #f8f9ff 0%, #ffffff 100%);
}

.summary-section {
	background: white;
	border-radius: 12px;
	padding: 25px;
	margin-bottom: 20px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.04);
	border: 1px solid rgba(102, 126, 234, 0.1);
	transition: all 0.3s ease;
}

.summary-section:hover {
	box-shadow: 0 4px 16px rgba(0,0,0,0.08);
	border-color: rgba(102, 126, 234, 0.2);
}

.summary-section-title {
	color: #2d3748;
	font-size: 15px;
	font-weight: 700;
	margin-bottom: 20px;
	padding-bottom: 12px;
	border-bottom: 3px solid #667eea;
	text-transform: uppercase;
	letter-spacing: 1px;
	display: flex;
	align-items: center;
	gap: 10px;
}

.summary-section-title i {
	color: #667eea;
	font-size: 18px;
}

.summary-row {
	display: flex;
	flex-wrap: wrap;
	margin: -10px;
}

.summary-item {
	flex: 0 0 33.333%;
	padding: 10px;
	min-width: 250px;
}

.summary-item-quarter {
	flex: 0 0 25%;
	padding: 10px;
	min-width: 220px;
}

.summary-item-full {
	flex: 0 0 100%;
	padding: 10px;
}

.summary-item-inner {
	background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
	border-radius: 10px;
	padding: 20px;
	border-left: 4px solid #667eea;
	transition: all 0.3s ease;
	height: 100%;
	position: relative;
	overflow: hidden;
}

.summary-item-inner::before {
	content: '';
	position: absolute;
	top: 0;
	right: 0;
	width: 60px;
	height: 60px;
	background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
	border-radius: 0 0 0 100%;
}

.summary-item-inner:hover {
	transform: translateY(-4px);
	box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
	border-left-color: #764ba2;
}

.summary-label {
	color: #718096;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 4px;
	display: block;
}

.summary-label i {
	color: #667eea;
	font-size: 14px;
}

.summary-value {
	color: #2d3748;
	font-size: 30px;
	font-weight: 800;
	display: block;
	position: relative;
	z-index: 1;
	background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
	letter-spacing: -1px;
	animation: countUp 0.6s ease-out;
	line-height: 1.2;
	margin-left: 2px;
}

.summary-value a {
	color: #667eea;
	text-decoration: none;
	font-size: 30px;
	font-weight: 800;
	transition: all 0.3s ease;
	word-break: break-all;
	overflow-wrap: break-word;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
	display: inline-block;
	text-shadow: none;
	letter-spacing: -1px;
	animation: countUp 0.6s ease-out;
	line-height: 1.1;
	margin-left: 5px;
}

.summary-value a:hover {
	transform: scale(1.08);
	background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
	text-decoration: none;
	animation: numberGlow 1.5s ease-in-out infinite;
}

/* Special styling for zero/not found values */
.summary-value a[style*="color: #999"] {
	font-size: 13px !important;
	font-weight: 500 !important;
	background: none !important;
	-webkit-text-fill-color: #999 !important;
	letter-spacing: 0 !important;
	animation: none !important;
}

/* URL links should not be huge */
.summary-value a[href*="://"] {
	font-size: 15px;
	font-weight: 600;
	letter-spacing: 0;
}

/* Score Circle */
.score-circle {
	display: inline-flex;
	align-items: center;
	gap: 15px;
}

.score-progress {
	position: relative;
	width: 80px;
	height: 80px;
}

.score-progress svg {
	transform: rotate(-90deg);
}

.score-progress-bg {
	fill: none;
	stroke: #e2e8f0;
	stroke-width: 8;
}

.score-progress-bar {
	fill: none;
	stroke-width: 8;
	stroke-linecap: round;
	transition: stroke-dashoffset 1s ease;
}

.score-progress-bar.positive {
	stroke: url(#scoreGradientPositive);
}

.score-progress-bar.negative {
	stroke: url(#scoreGradientNegative);
}

.score-text {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	font-size: 18px;
	font-weight: 700;
	color: #2d3748;
	-webkit-text-fill-color: #2d3748;
	background: none;
}
.score-text.positive {
	color: #28a745;
	-webkit-text-fill-color: #28a745;
}
.score-text.negative {
	color: #dc3545;
	-webkit-text-fill-color: #dc3545;
}

.score-label {
	font-size: 14px;
	color: #718096;
	font-weight: 600;
}

.score-info {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.score-status {
	font-size: 14px;
	font-weight: 600;
}
.score-status.excellent { color: #28a745; }
.score-status.good { color: #20c997; }
.score-status.average { color: #ffc107; }
.score-status.poor { color: #fd7e14; }
.score-status.bad { color: #dc3545; }
.score-description {
	font-size: 12px;
	color: #6c757d;
}

/* Progress Bar for Pages */
.pages-progress {
	margin-top: 10px;
	background: #e2e8f0;
	height: 8px;
	border-radius: 10px;
	overflow: hidden;
	position: relative;
}

.pages-progress-bar {
	height: 100%;
	background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
	border-radius: 10px;
	transition: width 1s ease;
	position: relative;
	overflow: hidden;
}

.pages-progress-bar::after {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
	background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
	animation: shimmer 2s infinite;
}

@keyframes shimmer {
	0% { transform: translateX(-100%); }
	100% { transform: translateX(100%); }
}

.pages-stats {
	display: flex;
	justify-content: space-between;
	margin-top: 8px;
	font-size: 12px;
	color: #718096;
	font-weight: 600;
}

/* Status Badges */
.status-badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.status-badge.success {
	background: linear-gradient(135deg, #10b981 0%, #059669 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.status-badge.danger {
	background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.status-badge.warning {
	background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.status-badge i {
	font-size: 14px;
}

/* Metric Cards with Icons */
/* Metric header with icon and label inline */
.metric-header {
	display: flex;
	align-items: center;
	margin-bottom: 8px;
}

.metric-icon {
	width: 36px;
	height: 36px;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 16px;
	margin-right: 10px;
	flex-shrink: 0;
}

.metric-icon.blue {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.metric-icon.green {
	background: linear-gradient(135deg, #10b981 0%, #059669 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.metric-icon.orange {
	background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.metric-icon.purple {
	background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
	color: white;
	box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

/* Responsive */
@media (max-width: 1400px) {
	.summary-item-quarter {
		flex: 0 0 33.333%;
	}
}

@media (max-width: 992px) {
	.summary-item, .summary-item-quarter {
		flex: 0 0 50%;
	}
}

@media (max-width: 576px) {
	.summary-item, .summary-item-quarter {
		flex: 0 0 100%;
	}
	.summary-body {
		padding: 20px;
	}
	.summary-header {
		padding: 8px 20px;
		font-size: 16px;
	}
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
				<div class="summary-item">
					<div class="summary-item-inner">
						<div class="metric-header">
							<div class="metric-icon blue">
								<i class="fas fa-globe"></i>
							</div>
							<span class="summary-label"><?php echo $spTextSA['Project Url']?></span>
						</div>
						<span class="summary-value"><a href="<?php echo $projectInfo['url']?>" target="_blank"><?php echo $projectInfo['url']?></a></span>
					</div>
				</div>
				<div class="summary-item">
					<div class="summary-item-inner">
						<div class="metric-header">
							<div class="metric-icon green">
								<i class="fas fa-star"></i>
							</div>
							<span class="summary-label"><?php echo $spText['label']['Score']?></span>
						</div>
						<span class="summary-value">
							<div class="score-circle">
								<?php
								$score = round($projectInfo['score'], 2);
								$isPositive = $score >= 0;
								$absScore = abs($score);
								$maxScore = 100;
								$percentage = min(($absScore / $maxScore) * 100, 100);
								$circumference = 2 * 3.14159 * 36;
								$dashOffset = $circumference - ($percentage / 100) * $circumference;

								// Determine score status
								if ($absScore >= 80) {
									$statusClass = $isPositive ? 'excellent' : 'bad';
									$statusText = $isPositive ? 'Excellent' : 'Critical';
								} elseif ($absScore >= 60) {
									$statusClass = $isPositive ? 'good' : 'poor';
									$statusText = $isPositive ? 'Good' : 'Poor';
								} elseif ($absScore >= 40) {
									$statusClass = 'average';
									$statusText = 'Average';
								} elseif ($absScore >= 20) {
									$statusClass = $isPositive ? 'average' : 'poor';
									$statusText = $isPositive ? 'Fair' : 'Needs Work';
								} else {
									$statusClass = $isPositive ? 'average' : 'bad';
									$statusText = $isPositive ? 'Low' : 'Critical';
								}
								?>
								<div class="score-progress">
									<svg width="80" height="80">
										<defs>
											<linearGradient id="scoreGradientPositive" x1="0%" y1="0%" x2="100%" y2="100%">
												<stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
												<stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
											</linearGradient>
											<linearGradient id="scoreGradientNegative" x1="0%" y1="0%" x2="100%" y2="100%">
												<stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
												<stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
											</linearGradient>
										</defs>
										<circle class="score-progress-bg" cx="40" cy="40" r="36"></circle>
										<circle class="score-progress-bar <?php echo $isPositive ? 'positive' : 'negative'; ?>"
											cx="40" cy="40" r="36"
											stroke-dasharray="<?php echo $circumference; ?>"
											stroke-dashoffset="<?php echo $dashOffset; ?>"></circle>
									</svg>
									<div class="score-text <?php echo $isPositive ? 'positive' : 'negative'; ?>"><?php echo ($isPositive ? '+' : '') . $score; ?></div>
								</div>
								<div class="score-info">
									<span class="score-status <?php echo $statusClass?>"><?php echo $statusText?></span>
									<span class="score-description">SEO Score Rating</span>
								</div>
							</div>
						</span>
					</div>
				</div>
				<div class="summary-item">
					<div class="summary-item-inner">
						<div class="metric-header">
							<div class="metric-icon orange">
								<i class="fas fa-clock"></i>
							</div>
							<span class="summary-label"><?php echo $spText['label']['Updated']?></span>
						</div>
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
							<div class="metric-header">
							<div class="metric-icon purple">
								<i class="fas fa-list-ol"></i>
							</div>
							<span class="summary-label"><?php echo $spTextSA['Maximum Pages']?></span>
							</div>
							<span class="summary-value"><?php echo number_format($projectInfo['max_links'])?></span>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<div class="metric-header">
							<div class="metric-icon blue">
								<i class="fas fa-search"></i>
							</div>
							<span class="summary-label"><?php echo $spTextSA['Pages Found']?></span>
							</div>
							<span class="summary-value"><?php echo number_format($projectInfo['total_links'])?></span>
							<?php
							$foundPercentage = $projectInfo['max_links'] > 0 ? ($projectInfo['total_links'] / $projectInfo['max_links']) * 100 : 0;
							?>
							<div class="pages-progress">
								<div class="pages-progress-bar" style="width: <?php echo min($foundPercentage, 100); ?>%"></div>
							</div>
							<div class="pages-stats">
								<span><?php echo number_format($foundPercentage, 1); ?>% of maximum</span>
							</div>
						</div>
					</div>
					<div class="summary-item">
						<div class="summary-item-inner">
							<div class="metric-header">
							<div class="metric-icon green">
								<i class="fas fa-check-circle"></i>
							</div>
							<span class="summary-label"><?php echo $spTextSA['Crawled Pages']?></span>
							</div>
							<span class="summary-value"><?php echo number_format($projectInfo['crawled_links'])?></span>
							<?php
							$crawledPercentage = $projectInfo['total_links'] > 0 ? ($projectInfo['crawled_links'] / $projectInfo['total_links']) * 100 : 0;
							?>
							<div class="pages-progress">
								<div class="pages-progress-bar" style="width: <?php echo min($crawledPercentage, 100); ?>%"></div>
							</div>
							<div class="pages-stats">
								<span><?php echo number_format($crawledPercentage, 1); ?>% crawled</span>
							</div>
						</div>
					</div>
				</div>
			</div>

    		<!-- SEO Metrics Section -->
    		<div class="summary-section">
    			<div class="summary-section-title"><i class="fas fa-chart-bar"></i> SEO Metrics</div>
    			<!-- Page Authority Row -->
    			<div class="summary-row">
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon green">
    							<i class="fas fa-trophy"></i>
    						</div>
    						<span class="summary-label"><?php echo $spTextSA['Excellent Page Authority'] ?? 'Excellent Page Authority'; ?> (&ge;75)</span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['pa_excellent'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=excellent')">
    								<?php echo number_format($projectInfo['pa_excellent'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=excellent')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon blue">
    							<i class="fas fa-thumbs-up"></i>
    						</div>
    						<span class="summary-label"><?php echo $spTextSA['Good Page Authority'] ?? 'Good Page Authority'; ?> (40-74)</span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['pa_good'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=good')">
    								<?php echo number_format($projectInfo['pa_good'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=good')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon orange">
    							<i class="fas fa-exclamation-circle"></i>
    						</div>
    						<span class="summary-label"><?php echo $spTextSA['Low Page Authority'] ?? 'Low Page Authority'; ?> (1-39)</span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['pa_low'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=low')">
    								<?php echo number_format($projectInfo['pa_low'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=low')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon <?php echo $projectInfo['pa_none'] > 0 ? 'orange' : 'green'; ?>">
    							<i class="fas fa-times-circle"></i>
    						</div>
    						<span class="summary-label"><?php echo $spTextSA['No Page Authority'] ?? 'No Page Authority'; ?> (0)</span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['pa_none'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=none')">
    								<?php echo number_format($projectInfo['pa_none'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&pa_type=none')" style="color: #999; font-size: 11px;">None</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    			</div>
    			<!-- Backlinks and Indexed Row -->
    			<div class="summary-row" style="margin-top: 10px;">
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon green">
    							<i class="fas fa-link"></i>
    						</div>
    						<span class="summary-label"><?php echo $spTextSA['Has Backlinks']?></span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['google_backlinks'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&backlinks_filter=has')">
    								<?php echo number_format($projectInfo['google_backlinks'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&backlinks_filter=has')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<?php
    				$seIcons = array('google' => 'google', 'bing' => 'search');
    				foreach ($seArr as $se) {
    					$iconClass = isset($seIcons[$se]) ? $seIcons[$se] : 'database';
    					$indexedCount = $projectInfo[$se."_indexed"];
    				?>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon <?php echo $indexedCount > 0 ? 'green' : 'orange'; ?>">
    							<i class="fab fa-<?php echo $iconClass; ?>"></i>
    						</div>
    						<span class="summary-label"><?php echo ucfirst($se)?> <?php echo $spTextHome['Indexed']?></span>
							</div>
    						<span class="summary-value">
							<?php if ($indexedCount > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&indexed_filter=<?php echo $se?>_yes')">
    								<?php echo number_format($indexedCount)?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&indexed_filter=<?php echo $se?>_yes')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<?php } ?>
    			</div>
    		</div>

			<!-- Modern SEO Features Section -->
			<div class="summary-section">
			<div class="summary-section-title"><i class="fas fa-rocket"></i> Modern SEO Features</div>
			<div class="summary-row">
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['mobile_friendly'] > 0 ? 'green' : 'orange'; ?>">
							<i class="fas fa-mobile-alt"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['Mobile Friendly']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['mobile_friendly'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&mobile_friendly=1')"><?php echo number_format($projectInfo['mobile_friendly'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&mobile_friendly=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['https_secure'] > 0 ? 'green' : 'orange'; ?>">
							<i class="fas fa-lock"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['HTTPS Secure']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['https_secure'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&https_secure=1')"><?php echo number_format($projectInfo['https_secure'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&https_secure=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['ai_robot_allowed'] > 0 ? 'green' : 'orange'; ?>">
							<i class="fas fa-robot"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['AI Robot Compatibility']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['ai_robot_allowed'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&ai_robot_allowed=1')"><?php echo number_format($projectInfo['ai_robot_allowed'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&ai_robot_allowed=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['has_og_tags'] > 0 ? 'blue' : 'orange'; ?>">
							<i class="fab fa-facebook"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['Open Graph Tags']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['has_og_tags'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&has_og_tags=1')"><?php echo number_format($projectInfo['has_og_tags'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&has_og_tags=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
			<div class="summary-row" style="margin-top: 10px;">
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['has_twitter_cards'] > 0 ? 'blue' : 'orange'; ?>">
							<i class="fab fa-twitter"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['Twitter Cards']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['has_twitter_cards'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&has_twitter_cards=1')"><?php echo number_format($projectInfo['has_twitter_cards'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&has_twitter_cards=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="summary-item-quarter">
					<div class="summary-item-inner">
						<div class="metric-header">
						<div class="metric-icon <?php echo $projectInfo['allowed_by_robots'] > 0 ? 'green' : 'orange'; ?>">
							<i class="fas fa-file-code"></i>
						</div>
						<span class="summary-label"><?php echo $spTextSA['Robots.txt Allowed']?></span>
						</div>
						<span class="summary-value">
							<?php if ($projectInfo['allowed_by_robots'] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&allowed_by_robots=1')"><?php echo number_format($projectInfo['allowed_by_robots'])?></a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&allowed_by_robots=1')" style="color: #999; font-size: 11px;">Not found</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

    		<!-- Areas for Improvement Section -->
    		<div class="summary-section">
    			<div class="summary-section-title"><i class="fas fa-exclamation-triangle"></i> Areas for Improvement</div>
    			<div class="summary-row">
    				<?php
    				foreach ($seArr as $se) {
    					$iconClass = isset($seIcons[$se]) ? $seIcons[$se] : 'database';
    					$notIndexedCount = $projectInfo[$se."_not_indexed"];
    				?>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon <?php echo $notIndexedCount == 0 ? 'green' : 'orange'; ?>">
    							<i class="fab fa-<?php echo $iconClass; ?>"></i>
    						</div>
    						<span class="summary-label"><?php echo ucfirst($se)?> Not Indexed</span>
							</div>
    						<span class="summary-value">
							<?php if ($notIndexedCount > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&indexed_filter=<?php echo $se?>_no')">
    								<?php echo number_format($notIndexedCount)?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&indexed_filter=<?php echo $se?>_no')" style="color: #999; font-size: 11px;">None</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<?php } ?>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon <?php echo $projectInfo['brocken'] > 0 ? 'orange' : 'green'; ?>">
    							<i class="fas fa-unlink"></i>
    						</div>
    						<span class="summary-label"><?php echo $spText['label']['Brocken']?></span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['brocken'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&brocken=1')">
    								<?php echo number_format($projectInfo['brocken'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&brocken=1')" style="color: #999; font-size: 11px;">None</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    				<div class="summary-item-quarter">
    					<div class="summary-item-inner">
							<div class="metric-header">
    						<div class="metric-icon <?php echo $projectInfo['no_backlinks'] > 0 ? 'orange' : 'green'; ?>">
    							<i class="fas fa-chain-broken"></i>
    						</div>
    						<span class="summary-label">No Backlinks</span>
							</div>
    						<span class="summary-value">
							<?php if ($projectInfo['no_backlinks'] > 0): ?>
    							<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&backlinks_filter=no')">
    								<?php echo number_format($projectInfo['no_backlinks'])?>
    							</a>
							<?php else: ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=rp_links&backlinks_filter=no')" style="color: #999; font-size: 11px;">None</a>
							<?php endif; ?>
    						</span>
    					</div>
    				</div>
    			</div>
    		</div>

			<!-- Meta Information Section -->
			<div class="summary-section">
				<div class="summary-section-title"><i class="fas fa-tags"></i> Meta Information</div>
				<div class="summary-row">
					<?php
					$metaIcons = array(
						'title' => 'heading',
						'description' => 'align-left',
						'keywords' => 'key'
					);
					foreach ($metaArr as $col => $label) {
						$iconClass = isset($metaIcons[$col]) ? $metaIcons[$col] : 'tag';
					?>
					<div class="summary-item">
						<div class="summary-item-inner">
							<div class="metric-header">
							<div class="metric-icon blue">
								<i class="fas fa-<?php echo $iconClass; ?>"></i>
							</div>
							<span class="summary-label"><?php echo $label?></span>
							</div>
							<span class="summary-value">
								<?php if ($projectInfo["duplicate_".$col] > 0): ?>
								<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=<?php echo $col?>&')"><?php echo number_format($projectInfo["duplicate_".$col])?></a>
								<?php else: ?>
									<a <?php echo $hrefAction; ?> onclick="scriptDoLoad('siteauditor.php', 'content', '&sec=viewreports&project_id=<?php echo $projectInfo['id']?>&report_type=<?php echo $col?>&')" style="color: #999; font-size: 11px;">None</a>
								<?php endif; ?>
							</span>
						</div>
					</div>
					<?php } ?>
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
