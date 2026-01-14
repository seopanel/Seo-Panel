<form id='review_dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('review_dashboard.php', 'review_dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('review_dashboard.php', 'review_dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (isset($period) && $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (!isset($period) || $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('review_dashboard.php', 'review_dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Review Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Review Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextReview['Total Review Links']?></h6>
							<h3>
								<span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo $reviewStats['total_links']?>
								</span>
							</h3>
							<?php if (isset($reviewComparison['total_links'])):
								$comp = $reviewComparison['total_links'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextReview['Total Reviews']?></h6>
							<h3>
								<span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo number_format($reviewStats['total_reviews'])?>
								</span>
							</h3>
							<?php if (isset($reviewComparison['total_reviews'])):
								$comp = $reviewComparison['total_reviews'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'])?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-4 text-center">
							<h6 class="mb-3"><?php echo $spTextReview['Average Rating']?></h6>
							<h3>
								<span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
									<?php echo number_format($reviewStats['avg_rating'], 2)?>
								</span>
							</h3>
							<?php if (isset($reviewComparison['avg_rating'])):
								$comp = $reviewComparison['avg_rating'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<br><small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'], 2)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if (!empty($reviewDistribution)) { ?>
	<!-- Pie Charts Row -->
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Reviews by Platform']?></h4>
				</div>
				<div class="card-body">
					<div id="review_count_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Average Rating by Platform']?></h4>
				</div>
				<div class="card-body">
					<div id="review_rating_chart" style="width: 100%; height: 350px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Growth Trends Chart -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Review Growth Trends']?></h4>
				</div>
				<div class="card-body">
					<div id="review_trends_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Profiles Comparison Chart -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Top Review Links']?></h4>
				</div>
				<div class="card-body">
					<div id="review_comparison_chart" style="width: 100%; height: 400px;"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Review Links Table -->
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextReview['Review Link Details']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($topReviewLinks)) { ?>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo $spText['common']['Name']?></th>
										<th><?php echo $spTextSocialMedia['Platform']?></th>
										<th><?php echo $spTextReview['Total Reviews']?></th>
										<th><?php echo $spTextReview['Average Rating']?></th>
										<th><?php echo $spTextSocialMedia['Last Checked']?></th>
										<th><?php echo $spText['common']['Actions']?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$counter = 1;
									foreach($topReviewLinks as $link) {
									?>
										<tr>
											<td><?php echo $counter++?></td>
											<td><?php echo htmlspecialchars($link['name'])?></td>
											<td>
												<span class="badge" style="background-color: <?php echo getReviewPlatformColor($link['type'])?>;">
													<?php echo ucfirst($link['type'])?>
												</span>
											</td>
											<td><?php echo number_format($link['reviews'] ?? 0)?></td>
											<td>
												<span class="badge bg-warning">
													<?php echo number_format($link['rating'] ?? 0, 2)?>
												</span>
											</td>
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
							<i class="fas fa-info-circle me-2"></i><?php echo $spTextReview['No review data available']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<!-- No Review Data -->
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-info">
				<i class="fas fa-info-circle me-2"></i><?php echo $spTextReview['No review data available']?>
			</div>
		</div>
	</div>
	<?php } ?>

</div>

<?php
// Helper function to get review platform colors
function getReviewPlatformColor($type) {
	$colors = [
		'google' => '#4285f4',
		'yelp' => '#d32323',
		'facebook' => '#1877f2',
		'tripadvisor' => '#00af87',
		'trustpilot' => '#00b67a',
		'amazon' => '#ff9900'
	];
	return isset($colors[$type]) ? $colors[$type] : '#6c757d';
}
?>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawAllCharts);

function drawAllCharts() {
	drawReviewCountChart();
	drawReviewRatingChart();
	drawReviewTrendsChart();
	drawReviewComparisonChart();
}

// Draw review count distribution pie chart
function drawReviewCountChart() {
	<?php if (!empty($reviewDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Platform', 'Reviews'],
		<?php
		foreach ($reviewDistribution as $row) {
			echo "['" . ucfirst($row['type']) . "', " . intval($row['total_reviews']) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Reviews Distribution by Platform',
		pieHole: 0.4,
		height: 350,
		colors: [
			<?php
			foreach ($reviewDistribution as $row) {
				echo "'" . getReviewPlatformColor($row['type']) . "',\n";
			}
			?>
		],
		legend: { position: 'bottom' },
		chartArea: { width: '90%', height: '75%' }
	};

	var chart = new google.visualization.PieChart(document.getElementById('review_count_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw rating distribution pie chart
function drawReviewRatingChart() {
	<?php if (!empty($reviewDistribution)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Platform', 'Average Rating'],
		<?php
		foreach ($reviewDistribution as $row) {
			echo "['" . ucfirst($row['type']) . "', " . round($row['avg_rating'], 2) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Average Rating by Platform',
		pieHole: 0.4,
		height: 350,
		colors: [
			<?php
			foreach ($reviewDistribution as $row) {
				echo "'" . getReviewPlatformColor($row['type']) . "',\n";
			}
			?>
		],
		legend: { position: 'bottom' },
		chartArea: { width: '90%', height: '75%' }
	};

	var chart = new google.visualization.PieChart(document.getElementById('review_rating_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw review growth trends line chart
function drawReviewTrendsChart() {
	<?php if (!empty($reviewTrends)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Date', 'Reviews', 'Rating'],
		<?php
		foreach ($reviewTrends as $row) {
			$date = date('M d', strtotime($row['date']));
			echo "['" . $date . "', " . intval($row['total_reviews']) . ", " . round($row['avg_rating'], 2) . "],\n";
		}
		?>
	]);

	var options = {
		title: 'Review Growth Over Time',
		height: 400,
		colors: ['#17a2b8', '#ffc107'],
		legend: { position: 'bottom' },
		chartArea: { width: '85%', height: '70%' },
		series: {
			0: { targetAxisIndex: 0 },
			1: { targetAxisIndex: 1 }
		},
		vAxes: {
			0: { title: 'Total Reviews', minValue: 0 },
			1: { title: 'Average Rating', minValue: 0, maxValue: 5 }
		},
		hAxis: {
			title: 'Date',
			slantedText: true,
			slantedTextAngle: 45
		}
	};

	var chart = new google.visualization.LineChart(document.getElementById('review_trends_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Draw top review links comparison bar chart
function drawReviewComparisonChart() {
	<?php if (!empty($topReviewLinks)) { ?>
	var data = google.visualization.arrayToDataTable([
		['Link', 'Reviews', { role: 'style' }],
		<?php
		foreach ($topReviewLinks as $link) {
			$name = addslashes($link['name']);
			$reviews = intval($link['reviews'] ?? 0);
			$color = getReviewPlatformColor($link['type']);
			echo "['" . $name . "', " . $reviews . ", '" . $color . "'],\n";
		}
		?>
	]);

	var options = {
		title: 'Top 10 Review Links by Review Count',
		height: 400,
		legend: { position: 'none' },
		chartArea: { width: '70%', height: '80%' },
		hAxis: {
			title: 'Reviews',
			minValue: 0
		}
	};

	var chart = new google.visualization.BarChart(document.getElementById('review_comparison_chart'));
	chart.draw(data, options);
	<?php } ?>
}

// Sync website selection with other tabs
$(document).ready(function() {
	if (sessionStorage.getItem('sp_review_auto_loading')) {
		sessionStorage.removeItem('sp_review_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_review_auto_loading', '1');
			scriptDoLoadPost('review_dashboard.php', 'review_dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
