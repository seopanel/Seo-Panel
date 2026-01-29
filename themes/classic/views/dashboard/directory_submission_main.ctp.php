<?php
if (!empty($noWebsites)) {
	include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id='directory_submission_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/directory_submission_dashboard.php', 'directory_submission_dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/directory_submission_dashboard.php', 'directory_submission_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (!isset($period) || $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (isset($period) && $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/directory_submission_dashboard.php', 'directory_submission_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Directory Submission Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Directory Submission Overview'] ?? 'Directory Submission Overview'?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-paper-plane text-primary"></i> <?php echo $spTextDir['Total Submissions'] ?? 'Total Submissions'?>
							</h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($dsStats['total_submissions'])?></span></h3>
							<?php if (isset($dsComparison['total_submissions'])):
								$comp = $dsComparison['total_submissions'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-check-circle text-success"></i> <?php echo $spTextDir['Approved'] ?? 'Approved'?>
							</h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($dsStats['approved'])?></span></h3>
							<?php if (isset($dsComparison['approved'])):
								$comp = $dsComparison['approved'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-clock text-warning"></i> <?php echo $spTextDir['Pending'] ?? 'Pending'?>
							</h6>
							<h3><span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($dsStats['pending'])?></span></h3>
							<?php if (isset($dsComparison['pending'])):
								$comp = $dsComparison['pending'];
								$color = $comp['direction'] == 'up' ? 'warning' : ($comp['direction'] == 'down' ? 'success' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-thumbs-up text-info"></i> <?php echo $spTextDir['Confirmed'] ?? 'Confirmed'?>
							</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($dsStats['confirmed'])?></span></h3>
							<?php if (isset($dsComparison['confirmed'])):
								$comp = $dsComparison['confirmed'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-percentage text-secondary"></i> <?php echo $spTextDir['Success Rate'] ?? 'Success Rate'?>
							</h6>
							<?php
							$successRate = $dsStats['success_rate'];
							$rateColor = $successRate >= 50 ? 'success' : ($successRate >= 25 ? 'warning' : 'danger');
							?>
							<h3><span class="badge bg-<?php echo $rateColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $successRate?>%</span></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-history text-dark"></i> <?php echo $spTextDir['All Time'] ?? 'All Time'?>
							</h6>
							<h3><span class="badge bg-dark" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($dsAllTimeStats['total_submissions'])?></span></h3>
							<small class="text-muted"><?php echo $spTextDir['Total Submissions'] ?? 'Total Submissions'?></small>
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
					<h4><?php echo $spTextDashboard['Submission Status'] ?? 'Submission Status'?></h4>
				</div>
				<div class="card-body">
					<div id="submission_status_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Success Rate'] ?? 'Success Rate'?></h4>
				</div>
				<div class="card-body">
					<div id="success_rate_gauge" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['All Time Overview'] ?? 'All Time Overview'?></h4>
				</div>
				<div class="card-body">
					<div id="alltime_status_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Submission Trends Chart -->
	<?php if (!empty($dsTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Submission Trends'] ?? 'Submission Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="submission_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Directory Distribution Table -->
	<?php if (!empty($dsDirectoryDistribution)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Top Directories'] ?? 'Top Directories'?></h4>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><?php echo $spText['common']['Directory']?></th>
									<th><?php echo $spTextDir['Submissions'] ?? 'Submissions'?></th>
									<th><?php echo $spTextDir['Approved'] ?? 'Approved'?></th>
									<th><?php echo $spTextDir['Pending'] ?? 'Pending'?></th>
									<th><?php echo $spText['label']['Domain Authority'] ?? 'DA'?></th>
									<th><?php echo $spText['label']['Page Authority'] ?? 'PA'?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dsDirectoryDistribution as $dir): ?>
								<tr>
									<td>
										<i class="fas fa-folder text-primary"></i>
										<a href="<?php echo addHttpToUrl($dir['domain'])?>" target="_blank"><?php echo $dir['domain']?></a>
									</td>
									<td><span class="badge bg-primary"><?php echo number_format($dir['submission_count'])?></span></td>
									<td><span class="badge bg-success"><?php echo number_format($dir['approved_count'])?></span></td>
									<td><span class="badge bg-warning"><?php echo number_format($dir['pending_count'])?></span></td>
									<td>
										<?php
										$da = floatval($dir['domain_authority']);
										$daColor = $da >= 40 ? 'success' : ($da >= 20 ? 'warning' : 'danger');
										?>
										<span class="badge bg-<?php echo $daColor?>"><?php echo round($da, 1)?></span>
									</td>
									<td>
										<?php
										$pa = floatval($dir['page_authority']);
										$paColor = $pa >= 40 ? 'success' : ($pa >= 20 ? 'warning' : 'danger');
										?>
										<span class="badge bg-<?php echo $paColor?>"><?php echo round($pa, 1)?></span>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($dsDirectoryDistribution)): ?>
								<tr>
									<td colspan="6" class="text-center"><?php echo $spText['common']['No Records Found']?></td>
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
									<td><i class="fas fa-paper-plane text-primary"></i> <?php echo $spTextDir['Total Submissions'] ?? 'Total Submissions'?></td>
									<td><span class="badge bg-primary"><?php echo number_format($dsStats['total_submissions'])?></span></td>
									<td><?php echo number_format($prevDSStats['total_submissions'] ?? 0)?></td>
									<td>
										<?php if (isset($dsComparison['total_submissions'])):
											$comp = $dsComparison['total_submissions'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-check-circle text-success"></i> <?php echo $spTextDir['Approved'] ?? 'Approved'?></td>
									<td><span class="badge bg-success"><?php echo number_format($dsStats['approved'])?></span></td>
									<td><?php echo number_format($prevDSStats['approved'] ?? 0)?></td>
									<td>
										<?php if (isset($dsComparison['approved'])):
											$comp = $dsComparison['approved'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-clock text-warning"></i> <?php echo $spTextDir['Pending'] ?? 'Pending'?></td>
									<td><span class="badge bg-warning"><?php echo number_format($dsStats['pending'])?></span></td>
									<td><?php echo number_format($prevDSStats['pending'] ?? 0)?></td>
									<td>
										<?php if (isset($dsComparison['pending'])):
											$comp = $dsComparison['pending'];
											$color = $comp['direction'] == 'up' ? 'warning' : ($comp['direction'] == 'down' ? 'success' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-thumbs-up text-info"></i> <?php echo $spTextDir['Confirmed'] ?? 'Confirmed'?></td>
									<td><span class="badge bg-info"><?php echo number_format($dsStats['confirmed'])?></span></td>
									<td><?php echo number_format($prevDSStats['confirmed'] ?? 0)?></td>
									<td>
										<?php if (isset($dsComparison['confirmed'])):
											$comp = $dsComparison['confirmed'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
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
	drawSubmissionStatusChart();
	drawSuccessRateGauge();
	drawAllTimeStatusChart();
	drawSubmissionTrendsChart();
}

// Draw submission status pie chart (Approved vs Pending)
function drawSubmissionStatusChart() {
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Status']?>', '<?php echo $spText['common']['Count']?>'],
		['<?php echo $spTextDir['Approved'] ?? 'Approved'?>', <?php echo intval($dsStats['approved'])?>],
		['<?php echo $spTextDir['Pending'] ?? 'Pending'?>', <?php echo intval($dsStats['pending'])?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Approved vs Pending'] ?? 'Approved vs Pending'?>',
		height: 300,
		colors: ['#28a745', '#ffc107'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('submission_status_chart'));
	chart.draw(data, options);
}

// Draw success rate gauge chart
function drawSuccessRateGauge() {
	var data = google.visualization.arrayToDataTable([
		['Label', 'Value'],
		['<?php echo $spTextDir['Success'] ?? 'Success'?>', <?php echo round($dsStats['success_rate'], 1)?>]
	]);

	var options = {
		width: '100%',
		height: 300,
		redFrom: 0, redTo: 25,
		yellowFrom: 25, yellowTo: 50,
		greenFrom: 50, greenTo: 100,
		minorTicks: 5,
		max: 100
	};

	var chart = new google.visualization.Gauge(document.getElementById('success_rate_gauge'));
	chart.draw(data, options);
}

// Draw all-time status pie chart
function drawAllTimeStatusChart() {
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Status']?>', '<?php echo $spText['common']['Count']?>'],
		['<?php echo $spTextDir['Approved'] ?? 'Approved'?>', <?php echo intval($dsAllTimeStats['approved'])?>],
		['<?php echo $spTextDir['Pending'] ?? 'Pending'?>', <?php echo intval($dsAllTimeStats['pending'])?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['All Time Status'] ?? 'All Time Status'?>',
		height: 300,
		colors: ['#28a745', '#ffc107'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('alltime_status_chart'));
	chart.draw(data, options);
}

// Draw submission trends line chart
function drawSubmissionTrendsChart() {
	<?php if (!empty($dsTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextDir['Submissions'] ?? 'Submissions'?>', '<?php echo $spTextDir['Approved'] ?? 'Approved'?>', '<?php echo $spTextDir['Pending'] ?? 'Pending'?>'],
		<?php
		foreach ($dsTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['submissions']) . ", " . intval($row['approved']) . ", " . intval($row['pending']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Submissions Over Time'] ?? 'Submissions Over Time'?>',
		curveType: 'function',
		height: 400,
		colors: ['#007bff', '#28a745', '#ffc107'],
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
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('submission_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	// Save website selection when dropdown changes
	$('#website_id').on('change', function() {
		sessionStorage.setItem('sp_selected_website_id', $(this).val());
	});

	if (sessionStorage.getItem('sp_ds_auto_loading')) {
		sessionStorage.removeItem('sp_ds_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_ds_auto_loading', '1');
			scriptDoLoadPost('<?php echo SP_WEBPATH?>/directory_submission_dashboard.php', 'directory_submission_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
<?php } ?>
