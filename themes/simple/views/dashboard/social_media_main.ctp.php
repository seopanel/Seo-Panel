<?php
// Helper function to get social media platform colors
if (!function_exists('getSocialMediaColor')) {
	function getSocialMediaColor($type) {
		$colors = [
			'facebook' => '#1877f2',
			'twitter' => '#1da1f2',
			'instagram' => '#e4405f',
			'linkedin' => '#0a66c2',
			'pinterest' => '#bd081c',
			'youtube' => '#ff0000',
			'reddit' => '#ff4500'
		];
		return isset($colors[$type]) ? $colors[$type] : '#6c757d';
	}
}

if (!empty($noWebsites)) {
	include(SP_VIEWPATH.'/dashboard/no_websites.ctp.php');
} else { ?>
<form id='social_media_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('social_media_dashboard.php', 'social_media_dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('social_media_dashboard.php', 'social_media_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (isset($period) && $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (!isset($period) || $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('social_media_dashboard.php', 'social_media_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Social Media Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Social Media Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextSocialMedia['Total Social Media Links']?></h6>
							<h3>
								<span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo $socialMediaStats['total_links']?>
								</span>
							</h3>
							<?php if (isset($socialMediaComparison['total_links'])):
								$comp = $socialMediaComparison['total_links'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextSocialMedia['Total Followers']?></h6>
							<h3>
								<span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo number_format($socialMediaStats['total_followers'])?>
								</span>
							</h3>
							<?php if (isset($socialMediaComparison['total_followers'])):
								$comp = $socialMediaComparison['total_followers'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextSocialMedia['Total Likes']?></h6>
							<h3>
								<span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo number_format($socialMediaStats['total_likes'])?>
								</span>
							</h3>
							<?php if (isset($socialMediaComparison['total_likes'])):
								$comp = $socialMediaComparison['total_likes'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if (!empty($socialMediaDistribution)) { ?>
	<!-- Pie Charts Row -->
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Followers by Platform']?></h4>
				</div>
				<div class="card-body">
					<div id="social_media_followers_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Likes by Platform']?></h4>
				</div>
				<div class="card-body">
					<div id="social_media_likes_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Growth Trends Chart -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Social Media Growth Trends']?></h4>
				</div>
				<div class="card-body">
					<div id="social_media_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Profiles Comparison Chart -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Top Social Media Profiles']?></h4>
				</div>
				<div class="card-body">
					<div id="social_media_comparison_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Profiles Table -->
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextSocialMedia['Social Media Profile Details']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($topSocialMediaLinks)) { ?>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo $spText['common']['Name']?></th>
										<th><?php echo $spTextSocialMedia['Platform']?></th>
										<th><?php echo $spText['common']['Followers']?></th>
										<th><?php echo $spText['common']['Likes']?></th>
										<th><?php echo $spTextSocialMedia['Last Checked']?></th>
										<th><?php echo $spText['common']['Actions']?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$counter = 1;
									foreach($topSocialMediaLinks as $link) {
									?>
										<tr>
											<td><?php echo $counter++?></td>
											<td><?php echo htmlspecialchars($link['name'])?></td>
											<td>
												<span class="badge" style="background-color: <?php echo getSocialMediaColor($link['type'])?>;">
													<?php echo ucfirst($link['type'])?>
												</span>
											</td>
											<td><?php echo number_format($link['followers'] ?? 0)?></td>
											<td><?php echo number_format($link['likes'] ?? 0)?></td>
											<td><?php echo !empty($link['report_date']) ? date('M d, Y', strtotime($link['report_date'])) : '-'?></td>
											<td>
												<a href="<?php echo htmlspecialchars($link['url'])?>" target="_blank" class="btn btn-sm btn-info">
													<i class="fas fa-external-link-alt"></i> View
												</a>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spTextSocialMedia['No social media data available']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<!-- No Social Media Data -->
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-info">
				<i class="fas fa-info-circle me-2"></i><?php echo $spTextSocialMedia['No social media data available']?>
			</div>
		</div>
	</div>
	<?php } ?>

</div>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawAllCharts);

function drawAllCharts() {
	drawSocialMediaFollowersChart();
	drawSocialMediaLikesChart();
	drawSocialMediaTrendsChart();
	drawSocialMediaComparisonChart();
}

// Draw followers distribution pie chart
function drawSocialMediaFollowersChart() {
	<?php if (!empty($socialMediaDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Platform', 'Followers'],
		<?php
		foreach ($socialMediaDistribution as $row) {
			echo "['" . ucfirst($row['type']) . "', " . intval($row['total_followers']) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Followers Distribution by Platform',
		pieHole: 0.4,
		height: 350,
		colors: [
			<?php
			foreach ($socialMediaDistribution as $row) {
				echo "'" . getSocialMediaColor($row['type']) . "',\n";
			}
			?>
		],
		legend: { position: 'bottom' },
		chartArea: { width: '90%', height: '75%' }
	};

	var chart = new google.visualization.PieChart(document.getElementById('social_media_followers_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw likes distribution pie chart
function drawSocialMediaLikesChart() {
	<?php if (!empty($socialMediaDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Platform', 'Likes'],
		<?php
		foreach ($socialMediaDistribution as $row) {
			echo "['" . ucfirst($row['type']) . "', " . intval($row['total_likes']) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Likes Distribution by Platform',
		pieHole: 0.4,
		height: 350,
		colors: [
			<?php
			foreach ($socialMediaDistribution as $row) {
				echo "'" . getSocialMediaColor($row['type']) . "',\n";
			}
			?>
		],
		legend: { position: 'bottom' },
		chartArea: { width: '90%', height: '75%' }
	};

	var chart = new google.visualization.PieChart(document.getElementById('social_media_likes_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw social media growth trends line chart
function drawSocialMediaTrendsChart() {
	<?php if (!empty($socialMediaTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Date', 'Followers', 'Likes'],
		<?php
		foreach ($socialMediaTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['total_followers']) . ", " . intval($row['total_likes']) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Social Media Growth Over Time',
		height: 400,
		colors: ['#17a2b8', '#28a745'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		hAxis: {
			title: 'Date',
			slantedText: true,
			slantedTextAngle: 45
		},
		vAxis: {
			title: 'Count',
			minValue: 0
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('social_media_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw top profiles comparison bar chart
function drawSocialMediaComparisonChart() {
	<?php if (!empty($topSocialMediaLinks)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Profile', 'Followers', { role: 'style' }],
		<?php
		foreach ($topSocialMediaLinks as $link) {
			$name = addslashes($link['name']);
			$followers = intval($link['followers'] ?? 0);
			$color = getSocialMediaColor($link['type']);
			echo "['" . $name . "', " . $followers . ", '" . $color . "'],\n";
		}
		?>
	]);

	var options = {
		title: 'Top 10 Social Media Profiles by Followers',
		height: 400,
		legend: { position: 'none' },
		chartArea: { width: '70%', height: '80%' },
		hAxis: {
			title: 'Followers',
			minValue: 0
		}
	};

	var chart = new google.visualization.BarChart(document.getElementById('social_media_comparison_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	if (sessionStorage.getItem('sp_sm_auto_loading')) {
		sessionStorage.removeItem('sp_sm_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_sm_auto_loading', '1');
			scriptDoLoadPost('social_media_dashboard.php', 'social_media_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
<?php } ?>
