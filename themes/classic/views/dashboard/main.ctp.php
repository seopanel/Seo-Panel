<form id='dashboard_form' method="post">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="scriptDoLoadPost('dashboard.php', 'dashboard_form', 'content')" class="custom-select">
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
			<select name="period" id="period" onchange="scriptDoLoadPost('dashboard.php', 'dashboard_form', 'content')" class="custom-select">
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>>Day</option>
				<option value="week" <?php echo (isset($period) && $period == 'week') ? 'selected' : ''?>>Week</option>
				<option value="month" <?php echo (!isset($period) || $period == 'month') ? 'selected' : ''?>>Month</option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>>Year</option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('dashboard.php', 'dashboard_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="dashboard-container" style="margin-top: 40px;">

	<!-- Website Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextHome['Website Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spTextHome['Backlinks']?></h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($websiteStats['backlinks'])?></span></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spTextHome['Pages Indexed']?></h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($websiteStats['indexed_pages'])?></span></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Moz Rank</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['mozrank']?></span></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3"><?php echo $spText['common']['Domain Authority']?></h6>
							<h3><span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['domain_authority']?></span></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spText['common']['Page Authority']?></h6>
							<h3><span class="badge bg-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['page_authority']?></span></h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Keyword Statistics -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4>Keyword Statistics</h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spText['common']['Total']?> <?php echo $spText['common']['Keywords']?></h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['total']?></span></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spTextKeyword['Keywords Tracked']?></h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['tracked']?></span></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Top 3 <?php echo $spText['common']['Rankings']?></h6>
							<h3><span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['top3']?></span></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Top 10 <?php echo $spText['common']['Rankings']?></h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['top10']?></span></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">Not Ranked</h6>
							<h3><span class="badge bg-secondary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['total'] - $keywordStats['tracked']?></span></h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pie Charts Row -->
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4>Keyword Distribution by Rank</h4>
				</div>
				<div class="card-body">
					<?php if (!empty($keywordStats) && $keywordStats['total'] > 0) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawKeywordDistChart);

							function drawKeywordDistChart() {
								var data = google.visualization.arrayToDataTable([
									['Rank Range', 'Number of Keywords'],
									['Top 3 (1-3)', <?php echo $keywordStats['top3']?>],
									['Top 10 (4-10)', <?php echo $keywordStats['top10'] - $keywordStats['top3']?>],
									['Beyond Top 10', <?php echo $keywordStats['tracked'] - $keywordStats['top10']?>],
									['Not Ranked', <?php echo $keywordStats['total'] - $keywordStats['tracked']?>]
								]);

								var options = {
									title: 'Keywords by Ranking Position',
									pieHole: 0.4,
									height: 350,
									colors: ['#28a745', '#17a2b8', '#ffc107', '#6c757d'],
									legend: { position: 'bottom' },
									chartArea: { width: '90%', height: '75%' }
								};

								var chart = new google.visualization.PieChart(document.getElementById('keyword_dist_chart'));
								chart.draw(data, options);
							}
						</script>
						<div id="keyword_dist_chart" style="width: 100%; height: 350px;"></div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4>Search Engine Distribution</h4>
				</div>
				<div class="card-body">
					<?php if (!empty($searchEngineStats)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawSearchEngineChart);

							function drawSearchEngineChart() {
								var data = google.visualization.arrayToDataTable([
									['Search Engine', 'Keywords'],
									<?php
									foreach ($searchEngineStats as $seName => $count) {
										echo "['" . addslashes($seName) . "', " . $count . "],\n";
									}
									?>
								]);

								var options = {
									title: 'Keywords by Search Engine',
									pieHole: 0.4,
									height: 350,
									colors: ['#4285F4', '#EA4335', '#FBBC04', '#34A853', '#5F6368', '#9AA0A6'],
									legend: { position: 'bottom' },
									chartArea: { width: '90%', height: '75%' }
								};

								var chart = new google.visualization.PieChart(document.getElementById('search_engine_chart'));
								chart.draw(data, options);
							}
						</script>
						<div id="search_engine_chart" style="width: 100%; height: 350px;"></div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Ranking Trends Graph -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextKeyword['Ranking Trends']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($rankingTrends)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawRankingTrendsChart);

							function drawRankingTrendsChart() {
								var data = google.visualization.arrayToDataTable([
									['Date', 'Top 10 Keywords', 'Top 3 Keywords', 'Average Rank'],
									<?php
									foreach ($rankingTrends as $trend) {
										echo "['" . date('M d', strtotime($trend['date'])) . "', " .
										     $trend['top10_count'] . ", " .
										     $trend['top3_count'] . ", " .
										     $trend['avg_rank'] . "],\n";
									}
									?>
								]);

								var options = {
									title: '<?php echo $spTextKeyword["Keyword Ranking Trends"]?>',
									curveType: 'function',
									legend: { position: 'bottom' },
									height: 400,
									series: {
										0: { targetAxisIndex: 0, color: '#4285F4' },
										1: { targetAxisIndex: 0, color: '#34A853' },
										2: { targetAxisIndex: 1, color: '#EA4335' }
									},
									vAxes: {
										0: { title: 'Number of Keywords' },
										1: { title: 'Average Rank', direction: -1 }
									},
									hAxis: {
										title: 'Date'
									}
								};

								var chart = new google.visualization.LineChart(document.getElementById('ranking_trends_chart'));
								chart.draw(data, options);
							}
						</script>
						<div id="ranking_trends_chart" style="width: 100%; height: 400px;"></div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Keywords and Recent Activity -->
	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spTextKeyword['Top Keywords']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($topKeywords)) { ?>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo $spText['common']['Keyword']?></th>
										<th><?php echo $spText['common']['Rank']?></th>
										<th><?php echo $spText['common']['Search Engine']?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$counter = 1;
									foreach($topKeywords as $keyword) {
										$rankClass = $keyword['rank'] <= 3 ? 'badge bg-success' :
										             ($keyword['rank'] <= 10 ? 'badge bg-info' : 'badge bg-secondary');
									?>
										<tr>
											<td><?php echo $counter++?></td>
											<td><?php echo htmlspecialchars($keyword['name'])?></td>
											<td><span class="<?php echo $rankClass?>"><?php echo $keyword['rank']?></span></td>
											<td><?php echo htmlspecialchars($keyword['search_engine'])?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><?php echo $spText['label']['Recent Activity']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($recentActivity)) { ?>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th><?php echo $spText['common']['Keyword']?></th>
										<th><?php echo $spText['common']['Rank']?></th>
										<th><?php echo $spText['common']['Date']?></th>
										<th><?php echo $spText['common']['Search Engine']?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($recentActivity as $activity) { ?>
										<tr>
											<td><?php echo htmlspecialchars($activity['keyword'])?></td>
											<td><?php echo $activity['rank'] > 0 ? $activity['rank'] : '-'?></td>
											<td><?php echo date('M d, Y', strtotime($activity['result_date']))?></td>
											<td><?php echo htmlspecialchars($activity['search_engine'])?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

</div>
