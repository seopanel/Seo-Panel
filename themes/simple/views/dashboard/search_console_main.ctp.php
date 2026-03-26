<?php
if (!empty($noWebsites)) {
	include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id='search_console_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/search_console_dashboard.php', 'search_console_dashboard_form', 'content')" class="custom-select">
				<?php foreach($siteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
		<td>
			<select name="period" id="period" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/search_console_dashboard.php', 'search_console_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (!isset($period) || $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (isset($period) && $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/search_console_dashboard.php', 'search_console_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Search Console Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Search Console Overview'] ?? 'Search Console Overview'?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3 text-center">
							<h6 class="mb-3">
								<i class="fas fa-mouse-pointer text-primary"></i> <?php echo $spTextHome['Total Clicks'] ?? 'Total Clicks'?>
							</h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($scStats['total_clicks'])?></span></h3>
							<?php if (isset($scComparison['total_clicks'])):
								$comp = $scComparison['total_clicks'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">
								<i class="fas fa-eye text-info"></i> <?php echo $spTextHome['Total Impressions'] ?? 'Total Impressions'?>
							</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($scStats['total_impressions'])?></span></h3>
							<?php if (isset($scComparison['total_impressions'])):
								$comp = $scComparison['total_impressions'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">
								<i class="fas fa-percentage text-success"></i> <?php echo $spTextHome['Avg. CTR'] ?? 'Avg. CTR'?>
							</h6>
							<?php
							$ctr = $scStats['avg_ctr'];
							$ctrColor = $ctr >= 5 ? 'success' : ($ctr >= 2 ? 'warning' : 'danger');
							?>
							<h3><span class="badge bg-<?php echo $ctrColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo round($ctr, 2)?>%</span></h3>
							<?php if (isset($scComparison['avg_ctr'])):
								$comp = $scComparison['avg_ctr'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?>%</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">
								<i class="fas fa-sort-numeric-down text-warning"></i> <?php echo $spTextHome['Avg. Position'] ?? 'Avg. Position'?>
							</h6>
							<?php
							$position = $scStats['avg_position'];
							$posColor = $position <= 10 ? 'success' : ($position <= 30 ? 'warning' : 'danger');
							?>
							<h3><span class="badge bg-<?php echo $posColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo round($position, 1)?></span></h3>
							<?php if (isset($scComparison['avg_position'])):
								$comp = $scComparison['avg_position'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?></strong>
							</small>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pie Charts Row -->
	<div class="row mb-4">
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Clicks vs Impressions'] ?? 'Clicks vs Impressions'?></h4>
				</div>
				<div class="card-body">
					<div id="clicks_impressions_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['CTR Performance'] ?? 'CTR Performance'?></h4>
				</div>
				<div class="card-body">
					<div id="ctr_gauge_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Clicks by Source'] ?? 'Clicks by Source'?></h4>
				</div>
				<div class="card-body">
					<div id="source_clicks_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Clicks & Impressions Trends Chart -->
	<?php if (!empty($scTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Performance Trends'] ?? 'Performance Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="performance_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- CTR & Position Trends -->
	<?php if (!empty($scTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['CTR Trends'] ?? 'CTR Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="ctr_trends_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Position Trends'] ?? 'Position Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="position_trends_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Source Distribution -->
	<?php if (!empty($scSourceDistribution)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Performance by Search Engine'] ?? 'Performance by Search Engine'?></h4>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><?php echo $spText['common']['Source']?></th>
									<th><?php echo $spTextHome['Clicks'] ?? 'Clicks'?></th>
									<th><?php echo $spTextHome['Impressions'] ?? 'Impressions'?></th>
									<th><?php echo $spTextHome['CTR'] ?? 'CTR'?></th>
									<th><?php echo $spTextHome['Avg. Position'] ?? 'Avg. Position'?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($scSourceDistribution as $source):
									$sourceColors = [
										'google' => 'danger',
										'bing' => 'info',
										'yahoo' => 'purple',
										'baidu' => 'primary',
										'yandex' => 'warning'
									];
									$sourceIcons = [
										'google' => 'fab fa-google',
										'bing' => 'fab fa-microsoft',
										'yahoo' => 'fab fa-yahoo',
										'baidu' => 'fas fa-search',
										'yandex' => 'fas fa-search'
									];
									$srcName = $source['source'];
									$srcColor = $sourceColors[$srcName] ?? 'secondary';
									$srcIcon = $sourceIcons[$srcName] ?? 'fas fa-search';
								?>
								<tr>
									<td>
										<i class="<?php echo $srcIcon?> text-<?php echo $srcColor?>"></i>
										<?php echo ucfirst($srcName)?>
									</td>
									<td><span class="badge bg-primary"><?php echo number_format($source['clicks'])?></span></td>
									<td><span class="badge bg-info"><?php echo number_format($source['impressions'])?></span></td>
									<td>
										<?php
										$srcCtr = floatval($source['ctr']);
										$srcCtrColor = $srcCtr >= 5 ? 'success' : ($srcCtr >= 2 ? 'warning' : 'danger');
										?>
										<span class="badge bg-<?php echo $srcCtrColor?>"><?php echo round($srcCtr, 2)?>%</span>
									</td>
									<td>
										<?php
										$srcPos = floatval($source['position']);
										$srcPosColor = $srcPos <= 10 ? 'success' : ($srcPos <= 30 ? 'warning' : 'danger');
										?>
										<span class="badge bg-<?php echo $srcPosColor?>"><?php echo round($srcPos, 1)?></span>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($scSourceDistribution)): ?>
								<tr>
									<td colspan="5" class="text-center"><?php echo $spText['common']['No Records Found']?></td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Detailed Statistics Table -->
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Detailed Statistics'] ?? 'Detailed Statistics'?></h4>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><?php echo $spText['common']['Metric']?></th>
									<th><?php echo $spText['common']['Current Value']?></th>
									<th><?php echo $spText['common']['Previous Value']?></th>
									<th><?php echo $spText['common']['Change']?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><i class="fas fa-mouse-pointer text-primary"></i> <?php echo $spTextHome['Total Clicks'] ?? 'Total Clicks'?></td>
									<td><span class="badge bg-primary"><?php echo number_format($scStats['total_clicks'])?></span></td>
									<td><?php echo number_format($prevSCStats['total_clicks'] ?? 0)?></td>
									<td>
										<?php if (isset($scComparison['total_clicks'])):
											$comp = $scComparison['total_clicks'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-eye text-info"></i> <?php echo $spTextHome['Total Impressions'] ?? 'Total Impressions'?></td>
									<td><span class="badge bg-info"><?php echo number_format($scStats['total_impressions'])?></span></td>
									<td><?php echo number_format($prevSCStats['total_impressions'] ?? 0)?></td>
									<td>
										<?php if (isset($scComparison['total_impressions'])):
											$comp = $scComparison['total_impressions'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-percentage text-success"></i> <?php echo $spTextHome['Avg. CTR'] ?? 'Avg. CTR'?></td>
									<td><span class="badge bg-success"><?php echo round($scStats['avg_ctr'], 2)?>%</span></td>
									<td><?php echo round($prevSCStats['avg_ctr'] ?? 0, 2)?>%</td>
									<td>
										<?php if (isset($scComparison['avg_ctr'])):
											$comp = $scComparison['avg_ctr'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?>%</span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-sort-numeric-down text-warning"></i> <?php echo $spTextHome['Avg. Position'] ?? 'Avg. Position'?></td>
									<td><span class="badge bg-warning"><?php echo round($scStats['avg_position'], 1)?></span></td>
									<td><?php echo round($prevSCStats['avg_position'] ?? 0, 1)?></td>
									<td>
										<?php if (isset($scComparison['avg_position'])):
											$comp = $scComparison['avg_position'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?></span>
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart', 'gauge']});
google.charts.setOnLoadCallback(drawAllCharts);

function drawAllCharts() {
	drawClicksImpressionsChart();
	drawCTRGaugeChart();
	drawSourceClicksChart();
	drawPerformanceTrendsChart();
	drawCTRTrendsChart();
	drawPositionTrendsChart();
}

// Draw clicks vs impressions pie chart
function drawClicksImpressionsChart() {
	<?php
	$clicks = intval($scStats['total_clicks']);
	$impressionsWithoutClicks = intval($scStats['total_impressions']) - $clicks;
	if ($impressionsWithoutClicks < 0) $impressionsWithoutClicks = 0;
	?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Type']?>', '<?php echo $spText['common']['Count']?>'],
		['<?php echo $spTextHome['Clicks'] ?? 'Clicks'?>', <?php echo $clicks?>],
		['<?php echo $spTextHome['Impressions without Click'] ?? 'Impressions without Click'?>', <?php echo $impressionsWithoutClicks?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Click Distribution'] ?? 'Click Distribution'?>',
		height: 300,
		colors: ['#4285F4', '#EA4335'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('clicks_impressions_chart'));
	chart.draw(data, options);
}

// Draw CTR gauge chart
function drawCTRGaugeChart() {
	var data = google.visualization.arrayToDataTable([
		['Label', 'Value'],
		['CTR', <?php echo round($scStats['avg_ctr'], 2)?>]
	]);

	var options = {
		width: '100%',
		height: 300,
		redFrom: 0, redTo: 2,
		yellowFrom: 2, yellowTo: 5,
		greenFrom: 5, greenTo: 100,
		minorTicks: 5,
		max: 10
	};

	var chart = new google.visualization.Gauge(document.getElementById('ctr_gauge_chart'));
	chart.draw(data, options);
}

// Draw source clicks pie chart
function drawSourceClicksChart() {
	<?php if (!empty($scSourceDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Source']?>', '<?php echo $spTextHome['Clicks'] ?? 'Clicks'?>'],
		<?php
		$sourceColors = ['google' => '#EA4335', 'bing' => '#00BCF2', 'yahoo' => '#6001D2', 'baidu' => '#2319DC', 'yandex' => '#FF0000'];
		foreach ($scSourceDistribution as $source) {
			$sourceName = ucfirst($source['source']);
			echo "['" . $sourceName . "', " . intval($source['clicks']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Clicks by Search Engine'] ?? 'Clicks by Search Engine'?>',
		height: 300,
		colors: [<?php
			$colors = [];
			foreach ($scSourceDistribution as $source) {
				$colors[] = "'" . ($sourceColors[$source['source']] ?? '#6c757d') . "'";
			}
			echo implode(', ', $colors);
		?>],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('source_clicks_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw performance trends line chart (Clicks & Impressions)
function drawPerformanceTrendsChart() {
	<?php if (!empty($scTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Clicks'] ?? 'Clicks'?>', '<?php echo $spTextHome['Impressions'] ?? 'Impressions'?>'],
		<?php
		foreach ($scTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['clicks']) . ", " . intval($row['impressions']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Clicks & Impressions Over Time'] ?? 'Clicks & Impressions Over Time'?>',
		curveType: 'function',
		height: 400,
		colors: ['#4285F4', '#34A853'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spText['common']['Count']?>',
			minValue: 0
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		},
		series: {
			0: {targetAxisIndex: 0},
			1: {targetAxisIndex: 1}
		},
		vAxes: {
			0: {title: '<?php echo $spTextHome['Clicks'] ?? 'Clicks'?>'},
			1: {title: '<?php echo $spTextHome['Impressions'] ?? 'Impressions'?>'}
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('performance_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw CTR trend chart
function drawCTRTrendsChart() {
	<?php if (!empty($scTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['CTR'] ?? 'CTR'?> (%)'],
		<?php
		foreach ($scTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . floatval($row['ctr']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['CTR Over Time'] ?? 'CTR Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#34A853'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spTextHome['CTR'] ?? 'CTR'?> (%)',
			minValue: 0
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('ctr_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw position trend chart
function drawPositionTrendsChart() {
	<?php if (!empty($scTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Avg. Position'] ?? 'Avg. Position'?>'],
		<?php
		foreach ($scTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . floatval($row['position']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Position Over Time'] ?? 'Position Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#FBBC05'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spTextHome['Position'] ?? 'Position'?>',
			minValue: 1,
			direction: -1
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('position_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	// Save website selection when dropdown changes
	$('#website_id').on('change', function() {
		sessionStorage.setItem('sp_selected_website_id', $(this).val());
	});

	if (sessionStorage.getItem('sp_sc_auto_loading')) {
		sessionStorage.removeItem('sp_sc_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_sc_auto_loading', '1');
			scriptDoLoadPost('<?php echo SP_WEBPATH?>/search_console_dashboard.php', 'search_console_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
<?php } ?>
