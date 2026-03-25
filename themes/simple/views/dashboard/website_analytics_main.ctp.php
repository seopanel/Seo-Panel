<?php
// Helper functions for authority display
if (!function_exists('getAuthorityColor')) {
	function getAuthorityColor($value) {
		if ($value >= 60) return 'success';
		if ($value >= 40) return 'primary';
		if ($value >= 20) return 'warning';
		return 'secondary';
	}
}

if (!function_exists('getAuthorityLabel')) {
	function getAuthorityLabel($value) {
		if ($value >= 60) return 'Excellent';
		if ($value >= 40) return 'Good';
		if ($value >= 20) return 'Average';
		if ($value > 0) return 'Low';
		return 'N/A';
	}
}

if (!function_exists('getSpamScoreColor')) {
	function getSpamScoreColor($value) {
		if ($value <= 10) return 'success';
		if ($value <= 30) return 'warning';
		return 'danger';
	}
}

if (!function_exists('getSpamScoreLabel')) {
	function getSpamScoreLabel($value) {
		if ($value <= 10) return 'Low Risk';
		if ($value <= 30) return 'Moderate Risk';
		return 'High Risk';
	}
}

if (!empty($noWebsites)) {
	include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id='website_analytics_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/website_analytics_dashboard.php', 'website_analytics_dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/website_analytics_dashboard.php', 'website_analytics_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (isset($period) && $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (!isset($period) || $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/website_analytics_dashboard.php', 'website_analytics_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Website Analytics Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Website Analytics'] ?? 'Website Analytics'?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spText['common']['Domain Authority']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Domain Authority (0-100). Higher is better."></i>
							</h6>
							<?php
							$da = floatval($analyticsStats['domain_authority']);
							$daColor = getAuthorityColor($da);
							$daLabel = getAuthorityLabel($da);
							?>
							<h3>
								<span class="badge bg-<?php echo $daColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $daLabel?>">
									<?php echo round($da, 2)?>
								</span>
							</h3>
							<small class="text-muted"><?php echo $daLabel?></small>
							<?php if (isset($analyticsComparison['domain_authority'])):
								$comp = $analyticsComparison['domain_authority'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spText['common']['Page Authority']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Page Authority (0-100). Higher is better."></i>
							</h6>
							<?php
							$pa = floatval($analyticsStats['page_authority']);
							$paColor = getAuthorityColor($pa);
							$paLabel = getAuthorityLabel($pa);
							?>
							<h3>
								<span class="badge bg-<?php echo $paColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $paLabel?>">
									<?php echo round($pa, 2)?>
								</span>
							</h3>
							<small class="text-muted"><?php echo $paLabel?></small>
							<?php if (isset($analyticsComparison['page_authority'])):
								$comp = $analyticsComparison['page_authority'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spText['common']['Spam Score']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Spam likelihood (0-100%). Lower is better."></i>
							</h6>
							<?php
							$spamScore = floatval($analyticsStats['spam_score']);
							$spamScoreColor = getSpamScoreColor($spamScore);
							$spamScoreLabel = getSpamScoreLabel($spamScore);
							?>
							<h3>
								<span class="badge bg-<?php echo $spamScoreColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $spamScoreLabel?>">
									<?php echo round($spamScore, 2)?>%
								</span>
							</h3>
							<small class="text-muted"><?php echo $spamScoreLabel?></small>
							<?php if (isset($analyticsComparison['spam_score'])):
								$comp = $analyticsComparison['spam_score'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spTextHome['Backlinks'] ?? 'Backlinks'?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to this page"></i>
							</h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($analyticsStats['backlinks'])?></span></h3>
							<?php if (isset($analyticsComparison['backlinks'])):
								$comp = $analyticsComparison['backlinks'];
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
								<?php echo $spTextBack['Domain Backlinks'] ?? 'Domain Backlinks'?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to root domain"></i>
							</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($analyticsStats['domain_backlinks'])?></span></h3>
							<?php if (isset($analyticsComparison['domain_backlinks'])):
								$comp = $analyticsComparison['domain_backlinks'];
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
								<?php echo $spTextHome['Pages Indexed'] ?? 'Pages Indexed'?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Total pages indexed by Google"></i>
							</h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($analyticsStats['google_indexed'])?></span></h3>
							<?php if (isset($analyticsComparison['google_indexed'])):
								$comp = $analyticsComparison['google_indexed'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Authority Trends Chart -->
	<?php if (!empty($analyticsTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Authority Trends'] ?? 'Authority Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="authority_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Backlinks and Indexed Pages Trends -->
	<div class="row mb-4">
		<?php if (!empty($backlinkTrends)) { ?>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Backlink Trends'] ?? 'Backlink Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="backlink_trends_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php if (!empty($saturationTrends)) { ?>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Indexed Pages Trends'] ?? 'Indexed Pages Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="saturation_trends_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>

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
									<td><i class="fas fa-chart-line text-primary"></i> <?php echo $spText['common']['Domain Authority']?></td>
									<td><span class="badge bg-primary"><?php echo round($analyticsStats['domain_authority'], 2)?></span></td>
									<td><?php echo round($prevAnalyticsStats['domain_authority'] ?? 0, 2)?></td>
									<td>
										<?php if (isset($analyticsComparison['domain_authority'])):
											$comp = $analyticsComparison['domain_authority'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-file-alt text-info"></i> <?php echo $spText['common']['Page Authority']?></td>
									<td><span class="badge bg-info"><?php echo round($analyticsStats['page_authority'], 2)?></span></td>
									<td><?php echo round($prevAnalyticsStats['page_authority'] ?? 0, 2)?></td>
									<td>
										<?php if (isset($analyticsComparison['page_authority'])):
											$comp = $analyticsComparison['page_authority'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-exclamation-triangle text-warning"></i> <?php echo $spText['common']['Spam Score']?></td>
									<td><span class="badge bg-warning"><?php echo round($analyticsStats['spam_score'], 2)?>%</span></td>
									<td><?php echo round($prevAnalyticsStats['spam_score'] ?? 0, 2)?>%</td>
									<td>
										<?php if (isset($analyticsComparison['spam_score'])):
											$comp = $analyticsComparison['spam_score'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 2)?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-link text-success"></i> <?php echo $spTextHome['Backlinks'] ?? 'Backlinks'?></td>
									<td><span class="badge bg-success"><?php echo number_format($analyticsStats['backlinks'])?></span></td>
									<td><?php echo number_format($prevAnalyticsStats['backlinks'] ?? 0)?></td>
									<td>
										<?php if (isset($analyticsComparison['backlinks'])):
											$comp = $analyticsComparison['backlinks'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-globe text-primary"></i> <?php echo $spTextBack['Domain Backlinks'] ?? 'Domain Backlinks'?></td>
									<td><span class="badge bg-primary"><?php echo number_format($analyticsStats['domain_backlinks'])?></span></td>
									<td><?php echo number_format($prevAnalyticsStats['domain_backlinks'] ?? 0)?></td>
									<td>
										<?php if (isset($analyticsComparison['domain_backlinks'])):
											$comp = $analyticsComparison['domain_backlinks'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fab fa-google text-danger"></i> <?php echo $spTextHome['Google Indexed'] ?? 'Google Indexed'?></td>
									<td><span class="badge bg-danger"><?php echo number_format($analyticsStats['google_indexed'])?></span></td>
									<td><?php echo number_format($prevAnalyticsStats['google_indexed'] ?? 0)?></td>
									<td>
										<?php if (isset($analyticsComparison['google_indexed'])):
											$comp = $analyticsComparison['google_indexed'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fab fa-microsoft text-info"></i> <?php echo $spTextHome['Bing Indexed'] ?? 'Bing Indexed'?></td>
									<td><span class="badge bg-info"><?php echo number_format($analyticsStats['bing_indexed'])?></span></td>
									<td><?php echo number_format($prevAnalyticsStats['bing_indexed'] ?? 0)?></td>
									<td>
										<?php if (isset($analyticsComparison['bing_indexed'])):
											$comp = $analyticsComparison['bing_indexed'];
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
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawAllCharts);

function drawAllCharts() {
	drawAuthorityTrendsChart();
	drawBacklinkTrendsChart();
	drawSaturationTrendsChart();
}

// Draw authority trends line chart
function drawAuthorityTrendsChart() {
	<?php if (!empty($analyticsTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spText['common']['Domain Authority']?>', '<?php echo $spText['common']['Page Authority']?>', '<?php echo $spText['common']['Spam Score']?>'],
		<?php
		foreach ($analyticsTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . floatval($row['domain_authority']) . ", " . floatval($row['page_authority']) . ", " . floatval($row['spam_score']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Authority & Spam Score Over Time'] ?? 'Authority & Spam Score Over Time'?>',
		curveType: 'function',
		height: 400,
		colors: ['#4285F4', '#34A853', '#EA4335'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spText['common']['Score']?>',
			minValue: 0,
			maxValue: 100
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('authority_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw backlink trends line chart
function drawBacklinkTrendsChart() {
	<?php if (!empty($backlinkTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Backlinks'] ?? 'Backlinks'?>', '<?php echo $spTextBack['Domain Backlinks'] ?? 'Domain Backlinks'?>'],
		<?php
		foreach ($backlinkTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['backlinks']) . ", " . intval($row['domain_backlinks']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Backlinks Over Time'] ?? 'Backlinks Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#17a2b8', '#28a745'],
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

	var chart = new google.visualization.LineChart(document.getElementById('backlink_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw saturation trends line chart
function drawSaturationTrendsChart() {
	<?php if (!empty($saturationTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Google Indexed'] ?? 'Google Indexed'?>', '<?php echo $spTextHome['Bing Indexed'] ?? 'Bing Indexed'?>'],
		<?php
		foreach ($saturationTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['google_indexed']) . ", " . intval($row['bing_indexed']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Indexed Pages Over Time'] ?? 'Indexed Pages Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#dc3545', '#007bff'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spText['common']['Pages']?>',
			minValue: 0
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('saturation_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	// Save website selection when dropdown changes
	$('#website_id').on('change', function() {
		sessionStorage.setItem('sp_selected_website_id', $(this).val());
	});

	if (sessionStorage.getItem('sp_wa_auto_loading')) {
		sessionStorage.removeItem('sp_wa_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_wa_auto_loading', '1');
			scriptDoLoadPost('<?php echo SP_WEBPATH?>/website_analytics_dashboard.php', 'website_analytics_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
<?php } ?>
