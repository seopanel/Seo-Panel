<?php
if (!empty($noWebsites)) {
	include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id='analytics_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/analytics_dashboard.php', 'analytics_dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('<?php echo SP_WEBPATH?>/analytics_dashboard.php', 'analytics_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (!isset($period) || $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (isset($period) && $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo SP_WEBPATH?>/analytics_dashboard.php', 'analytics_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Analytics Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Website Analytics Overview'] ?? 'Website Analytics Overview'?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-users text-primary"></i> <?php echo $spTextHome['Users'] ?? 'Users'?>
							</h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($waStats['total_users'])?></span></h3>
							<?php if (isset($waComparison['total_users'])):
								$comp = $waComparison['total_users'];
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
								<i class="fas fa-user-plus text-success"></i> <?php echo $spTextHome['New Users'] ?? 'New Users'?>
							</h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($waStats['total_new_users'])?></span></h3>
							<?php if (isset($waComparison['total_new_users'])):
								$comp = $waComparison['total_new_users'];
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
								<i class="fas fa-chart-line text-info"></i> <?php echo $spTextHome['Sessions'] ?? 'Sessions'?>
							</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($waStats['total_sessions'])?></span></h3>
							<?php if (isset($waComparison['total_sessions'])):
								$comp = $waComparison['total_sessions'];
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
								<i class="fas fa-sign-out-alt text-warning"></i> <?php echo $spTextHome['Bounce Rate'] ?? 'Bounce Rate'?>
							</h6>
							<?php
							$bounceRate = $waStats['avg_bounce_rate'];
							$bounceColor = $bounceRate <= 40 ? 'success' : ($bounceRate <= 60 ? 'warning' : 'danger');
							?>
							<h3><span class="badge bg-<?php echo $bounceColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo round($bounceRate, 1)?>%</span></h3>
							<?php if (isset($waComparison['avg_bounce_rate'])):
								$comp = $waComparison['avg_bounce_rate'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?>%</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-clock text-secondary"></i> <?php echo $spTextHome['Avg. Session'] ?? 'Avg. Session'?>
							</h6>
							<?php
							$avgDuration = $waStats['avg_session_duration'];
							$minutes = floor($avgDuration / 60);
							$seconds = round($avgDuration % 60);
							$durationStr = sprintf("%d:%02d", $minutes, $seconds);
							?>
							<h3><span class="badge bg-secondary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $durationStr?></span></h3>
							<?php if (isset($waComparison['avg_session_duration'])):
								$comp = $waComparison['avg_session_duration'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?>s</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<i class="fas fa-bullseye text-danger"></i> <?php echo $spTextHome['Goals'] ?? 'Goals'?>
							</h6>
							<h3><span class="badge bg-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($waStats['total_goal_completions'])?></span></h3>
							<?php if (isset($waComparison['total_goal_completions'])):
								$comp = $waComparison['total_goal_completions'];
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

	<!-- Pie Charts Row -->
	<div class="row mb-4">
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['User Types'] ?? 'User Types'?></h4>
				</div>
				<div class="card-body">
					<div id="user_types_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Engagement Quality'] ?? 'Engagement Quality'?></h4>
				</div>
				<div class="card-body">
					<div id="engagement_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Session Overview'] ?? 'Session Overview'?></h4>
				</div>
				<div class="card-body">
					<div id="session_overview_chart" style="width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Users & Sessions Trends Chart -->
	<?php if (!empty($waTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Traffic Trends'] ?? 'Traffic Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="traffic_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Bounce Rate & Session Duration Trends -->
	<?php if (!empty($waTrends)) { ?>
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Bounce Rate Trends'] ?? 'Bounce Rate Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="bounce_rate_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Session Duration Trends'] ?? 'Session Duration Trends'?></h4>
				</div>
				<div class="card-body">
					<div id="session_duration_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- Source Distribution -->
	<?php if (!empty($waSourceDistribution)) { ?>
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Traffic Sources'] ?? 'Traffic Sources'?></h4>
				</div>
				<div class="card-body">
					<div id="source_distribution_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextDashboard['Top Sources'] ?? 'Top Sources'?></h4>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><?php echo $spText['common']['Source']?></th>
									<th><?php echo $spTextHome['Users'] ?? 'Users'?></th>
									<th><?php echo $spTextHome['Sessions'] ?? 'Sessions'?></th>
									<th><?php echo $spTextHome['Bounce Rate'] ?? 'Bounce Rate'?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($waSourceDistribution as $source): ?>
								<tr>
									<td>
										<i class="fas fa-globe text-primary"></i>
										<?php echo htmlspecialchars($source['source_name'] ?? 'Unknown')?>
									</td>
									<td><span class="badge bg-primary"><?php echo number_format($source['users'])?></span></td>
									<td><span class="badge bg-info"><?php echo number_format($source['sessions'])?></span></td>
									<td>
										<?php
										$srcBounce = floatval($source['bounce_rate']);
										$srcBounceColor = $srcBounce <= 40 ? 'success' : ($srcBounce <= 60 ? 'warning' : 'danger');
										?>
										<span class="badge bg-<?php echo $srcBounceColor?>"><?php echo round($srcBounce, 1)?>%</span>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($waSourceDistribution)): ?>
								<tr>
									<td colspan="4" class="text-center"><?php echo $spText['common']['No Records Found']?></td>
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
									<td><i class="fas fa-users text-primary"></i> <?php echo $spTextHome['Users'] ?? 'Users'?></td>
									<td><span class="badge bg-primary"><?php echo number_format($waStats['total_users'])?></span></td>
									<td><?php echo number_format($prevWAStats['total_users'] ?? 0)?></td>
									<td>
										<?php if (isset($waComparison['total_users'])):
											$comp = $waComparison['total_users'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-user-plus text-success"></i> <?php echo $spTextHome['New Users'] ?? 'New Users'?></td>
									<td><span class="badge bg-success"><?php echo number_format($waStats['total_new_users'])?></span></td>
									<td><?php echo number_format($prevWAStats['total_new_users'] ?? 0)?></td>
									<td>
										<?php if (isset($waComparison['total_new_users'])):
											$comp = $waComparison['total_new_users'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-chart-line text-info"></i> <?php echo $spTextHome['Sessions'] ?? 'Sessions'?></td>
									<td><span class="badge bg-info"><?php echo number_format($waStats['total_sessions'])?></span></td>
									<td><?php echo number_format($prevWAStats['total_sessions'] ?? 0)?></td>
									<td>
										<?php if (isset($waComparison['total_sessions'])):
											$comp = $waComparison['total_sessions'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-sign-out-alt text-warning"></i> <?php echo $spTextHome['Bounce Rate'] ?? 'Bounce Rate'?></td>
									<td><span class="badge bg-warning"><?php echo round($waStats['avg_bounce_rate'], 1)?>%</span></td>
									<td><?php echo round($prevWAStats['avg_bounce_rate'] ?? 0, 1)?>%</td>
									<td>
										<?php if (isset($waComparison['avg_bounce_rate'])):
											$comp = $waComparison['avg_bounce_rate'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?>%</span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-clock text-secondary"></i> <?php echo $spTextHome['Avg. Session Duration'] ?? 'Avg. Session Duration'?></td>
									<td>
										<?php
										$avgDuration = $waStats['avg_session_duration'];
										$minutes = floor($avgDuration / 60);
										$seconds = round($avgDuration % 60);
										?>
										<span class="badge bg-secondary"><?php echo sprintf("%d:%02d", $minutes, $seconds)?></span>
									</td>
									<td>
										<?php
										$prevDuration = $prevWAStats['avg_session_duration'] ?? 0;
										$prevMinutes = floor($prevDuration / 60);
										$prevSeconds = round($prevDuration % 60);
										echo sprintf("%d:%02d", $prevMinutes, $prevSeconds);
										?>
									</td>
									<td>
										<?php if (isset($waComparison['avg_session_duration'])):
											$comp = $waComparison['avg_session_duration'];
											$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
											$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
										?>
										<span class="text-<?php echo $color?>"><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo round($comp['diff'], 1)?>s</span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><i class="fas fa-bullseye text-danger"></i> <?php echo $spTextHome['Goal Completions'] ?? 'Goal Completions'?></td>
									<td><span class="badge bg-danger"><?php echo number_format($waStats['total_goal_completions'])?></span></td>
									<td><?php echo number_format($prevWAStats['total_goal_completions'] ?? 0)?></td>
									<td>
										<?php if (isset($waComparison['total_goal_completions'])):
											$comp = $waComparison['total_goal_completions'];
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
	drawUserTypesChart();
	drawEngagementChart();
	drawSessionOverviewChart();
	drawTrafficTrendsChart();
	drawBounceRateChart();
	drawSessionDurationChart();
	drawSourceDistributionChart();
}

// Draw user types pie chart (New vs Returning)
function drawUserTypesChart() {
	<?php
	$newUsers = intval($waStats['total_new_users']);
	$returningUsers = intval($waStats['total_users']) - $newUsers;
	if ($returningUsers < 0) $returningUsers = 0;
	?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Type']?>', '<?php echo $spTextHome['Users'] ?? 'Users'?>'],
		['<?php echo $spTextHome['New Users'] ?? 'New Users'?>', <?php echo $newUsers?>],
		['<?php echo $spTextHome['Returning Users'] ?? 'Returning Users'?>', <?php echo $returningUsers?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['New vs Returning Users'] ?? 'New vs Returning Users'?>',
		height: 300,
		colors: ['#34A853', '#4285F4'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('user_types_chart'));
	chart.draw(data, options);
}

// Draw engagement quality pie chart
function drawEngagementChart() {
	<?php
	$bounceRate = floatval($waStats['avg_bounce_rate']);
	$engagedRate = 100 - $bounceRate;
	?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Type']?>', '<?php echo $spText['common']['Percentage']?>'],
		['<?php echo $spTextHome['Engaged Sessions'] ?? 'Engaged Sessions'?>', <?php echo round($engagedRate, 1)?>],
		['<?php echo $spTextHome['Bounced Sessions'] ?? 'Bounced Sessions'?>', <?php echo round($bounceRate, 1)?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Session Engagement'] ?? 'Session Engagement'?>',
		height: 300,
		colors: ['#34A853', '#EA4335'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('engagement_chart'));
	chart.draw(data, options);
}

// Draw session overview pie chart
function drawSessionOverviewChart() {
	<?php
	$totalSessions = intval($waStats['total_sessions']);
	$goalCompletions = intval($waStats['total_goal_completions']);
	$sessionsWithoutGoals = $totalSessions - $goalCompletions;
	if ($sessionsWithoutGoals < 0) $sessionsWithoutGoals = 0;
	?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Type']?>', '<?php echo $spTextHome['Sessions'] ?? 'Sessions'?>'],
		['<?php echo $spTextHome['Goal Conversions'] ?? 'Goal Conversions'?>', <?php echo $goalCompletions?>],
		['<?php echo $spTextHome['Other Sessions'] ?? 'Other Sessions'?>', <?php echo $sessionsWithoutGoals?>]
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Goal Conversions'] ?? 'Goal Conversions'?>',
		height: 300,
		colors: ['#FBBC05', '#4285F4'],
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'bottom' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('session_overview_chart'));
	chart.draw(data, options);
}

// Draw traffic trends line chart (Users, New Users, Sessions)
function drawTrafficTrendsChart() {
	<?php if (!empty($waTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Users'] ?? 'Users'?>', '<?php echo $spTextHome['New Users'] ?? 'New Users'?>', '<?php echo $spTextHome['Sessions'] ?? 'Sessions'?>'],
		<?php
		foreach ($waTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['users']) . ", " . intval($row['new_users']) . ", " . intval($row['sessions']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Users & Sessions Over Time'] ?? 'Users & Sessions Over Time'?>',
		curveType: 'function',
		height: 400,
		colors: ['#4285F4', '#34A853', '#17a2b8'],
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

	var chart = new google.visualization.LineChart(document.getElementById('traffic_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw bounce rate trend chart
function drawBounceRateChart() {
	<?php if (!empty($waTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Bounce Rate'] ?? 'Bounce Rate'?>'],
		<?php
		foreach ($waTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . floatval($row['bounce_rate']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Bounce Rate Over Time'] ?? 'Bounce Rate Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#ffc107'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spTextHome['Bounce Rate'] ?? 'Bounce Rate'?> (%)',
			minValue: 0,
			maxValue: 100
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('bounce_rate_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw session duration trend chart
function drawSessionDurationChart() {
	<?php if (!empty($waTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Date']?>', '<?php echo $spTextHome['Session Duration'] ?? 'Session Duration'?> (sec)'],
		<?php
		foreach ($waTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . floatval($row['session_duration']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Session Duration Over Time'] ?? 'Session Duration Over Time'?>',
		curveType: 'function',
		height: 350,
		colors: ['#6c757d'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		vAxis: {
			title: '<?php echo $spTextHome['Duration'] ?? 'Duration'?> (seconds)',
			minValue: 0
		},
		hAxis: {
			title: '<?php echo $spText['common']['Date']?>',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('session_duration_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw source distribution pie chart
function drawSourceDistributionChart() {
	<?php if (!empty($waSourceDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['<?php echo $spText['common']['Source']?>', '<?php echo $spTextHome['Users'] ?? 'Users'?>'],
		<?php
		foreach ($waSourceDistribution as $source) {
			$sourceName = addslashes($source['source_name'] ?? 'Unknown');
			echo "['" . $sourceName . "', " . intval($source['users']) . "],\n";
		}
		?>
	]);

	var options = {
		title: '<?php echo $spTextDashboard['Traffic by Source'] ?? 'Traffic by Source'?>',
		height: 350,
		chartArea: { width: '90%', height: '80%' },
		legend: { position: 'right' },
		pieHole: 0.4
	};

	var chart = new google.visualization.PieChart(document.getElementById('source_distribution_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	// Save website selection when dropdown changes
	$('#website_id').on('change', function() {
		sessionStorage.setItem('sp_selected_website_id', $(this).val());
	});

	if (sessionStorage.getItem('sp_analytics_auto_loading')) {
		sessionStorage.removeItem('sp_analytics_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_analytics_auto_loading', '1');
			scriptDoLoadPost('<?php echo SP_WEBPATH?>/analytics_dashboard.php', 'analytics_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
<?php } ?>
