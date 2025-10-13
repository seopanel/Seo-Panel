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

	<!-- Statistics Cards Row -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card text-white bg-primary mb-3">
				<div class="card-body">
					<h5 class="card-title"><?php echo $spText['common']['Total']?> <?php echo $spText['common']['Keywords']?></h5>
					<h2 class="card-text"><?php echo $keywordStats['total']?></h2>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-success mb-3">
				<div class="card-body">
					<h5 class="card-title"><?php echo $spTextKeyword['Keywords Tracked']?></h5>
					<h2 class="card-text"><?php echo $keywordStats['tracked']?></h2>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-info mb-3">
				<div class="card-body">
					<h5 class="card-title">Top 10 <?php echo $spText['common']['Rankings']?></h5>
					<h2 class="card-text"><?php echo $keywordStats['top10']?></h2>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-warning mb-3">
				<div class="card-body">
					<h5 class="card-title">Top 3 <?php echo $spText['common']['Rankings']?></h5>
					<h2 class="card-text"><?php echo $keywordStats['top3']?></h2>
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

	<!-- Website Overview Stats -->
	<div class="row mb-4">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-green">
					<h4><?php echo $spTextHome['Website Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2 text-center">
							<h6><?php echo $spText['common']['Backlinks']?></h6>
							<h3 class="text-primary"><?php echo number_format($websiteStats['backlinks'])?></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6><?php echo $spTextHome['Pages Indexed']?></h6>
							<h3 class="text-success"><?php echo number_format($websiteStats['indexed_pages'])?></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6>Moz Rank</h6>
							<h3 class="text-info"><?php echo $websiteStats['mozrank']?></h3>
						</div>
						<div class="col-md-3 text-center">
							<h6><?php echo $spText['common']['Domain Authority']?></h6>
							<h3 class="text-warning"><?php echo $websiteStats['domain_authority']?></h3>
						</div>
						<div class="col-md-2 text-center">
							<h6><?php echo $spText['common']['Page Authority']?></h6>
							<h3 class="text-danger"><?php echo $websiteStats['page_authority']?></h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Keywords and Recent Activity -->
	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-orange">
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
				<div class="card-header card-header-gradient-teal">
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
