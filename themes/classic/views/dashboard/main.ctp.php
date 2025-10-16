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
							<?php if (isset($websiteComparison['backlinks'])):
								$comp = $websiteComparison['backlinks'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spTextHome['Pages Indexed']?></h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($websiteStats['indexed_pages'])?></span></h3>
							<?php if (isset($websiteComparison['indexed_pages'])):
								$comp = $websiteComparison['indexed_pages'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Moz Rank</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['mozrank']?></span></h3>
							<?php if (isset($websiteComparison['mozrank'])):
								$comp = $websiteComparison['mozrank'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'], 2)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3"><?php echo $spText['common']['Domain Authority']?></h6>
							<h3><span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['domain_authority']?></span></h3>
							<?php if (isset($websiteComparison['domain_authority'])):
								$comp = $websiteComparison['domain_authority'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'], 1)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spText['common']['Page Authority']?></h6>
							<h3><span class="badge bg-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $websiteStats['page_authority']?></span></h3>
							<?php if (isset($websiteComparison['page_authority'])):
								$comp = $websiteComparison['page_authority'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo number_format($comp['diff'], 1)?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
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
							<?php if (isset($keywordComparison['total'])):
								$comp = $keywordComparison['total'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3"><?php echo $spTextKeyword['Keywords Tracked']?></h6>
							<h3><span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['tracked']?></span></h3>
							<?php if (isset($keywordComparison['tracked'])):
								$comp = $keywordComparison['tracked'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Top 3 <?php echo $spText['common']['Rankings']?></h6>
							<h3><span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['top3']?></span></h3>
							<?php if (isset($keywordComparison['top3'])):
								$comp = $keywordComparison['top3'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-3 text-center">
							<h6 class="mb-3">Top 10 <?php echo $spText['common']['Rankings']?></h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo $keywordStats['top10']?></span></h3>
							<?php if (isset($keywordComparison['top10'])):
								$comp = $keywordComparison['top10'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
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
					<?php if (!empty($keywordDistribution)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawKeywordDistChart);

							function drawKeywordDistChart() {
								var data = google.visualization.arrayToDataTable([
									['Rank Range', 'Number of Keywords'],
									['Top 10 (1-10)', <?php echo $keywordDistribution['top10']['count']?>],
									['Top 20 (11-20)', <?php echo $keywordDistribution['top20']['count']?>],
									['Top 50 (21-50)', <?php echo $keywordDistribution['top50']['count']?>],
									['Top 100 (51-100)', <?php echo $keywordDistribution['top100']['count']?>],
									['Not Ranked', <?php echo $keywordDistribution['not_ranked']['count']?>]
								]);

								var options = {
									title: 'Keywords by Ranking Position',
									pieHole: 0.4,
									height: 350,
									colors: ['#17a2b8', '#fd7e14', '#e83e8c', '#dc3545', '#6c757d'],
									legend: { position: 'bottom' },
									chartArea: { width: '90%', height: '75%' }
								};

								var chart = new google.visualization.PieChart(document.getElementById('keyword_dist_chart'));
								chart.draw(data, options);
							}
						</script>
						<div id="keyword_dist_chart" style="width: 100%; height: 350px;"></div>

						<!-- Detailed Distribution Tables -->
						<div class="mt-4">
							<ul class="nav nav-tabs" id="distTab" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="top10-tab" data-toggle="tab" href="#top10" role="tab" onclick="showDistTab('top10'); return false;">
										<span class="badge bg-info"><?php echo $keywordDistribution['top10']['count']?></span> Top 1-10
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top20-tab" data-toggle="tab" href="#top20" role="tab" onclick="showDistTab('top20'); return false;">
										<span class="badge" style="background-color: #fd7e14;"><?php echo $keywordDistribution['top20']['count']?></span> Top 11-20
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top50-tab" data-toggle="tab" href="#top50" role="tab" onclick="showDistTab('top50'); return false;">
										<span class="badge" style="background-color: #e83e8c;"><?php echo $keywordDistribution['top50']['count']?></span> Top 21-50
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top100-tab" data-toggle="tab" href="#top100" role="tab" onclick="showDistTab('top100'); return false;">
										<span class="badge bg-danger"><?php echo $keywordDistribution['top100']['count']?></span> Top 51-100
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="notranked-tab" data-toggle="tab" href="#notranked" role="tab" onclick="showDistTab('notranked'); return false;">
										<span class="badge bg-secondary"><?php echo $keywordDistribution['not_ranked']['count']?></span> Not Ranked
									</a>
								</li>
							</ul>
							<div class="tab-content border border-top-0 p-3" id="distTabContent">
								<!-- Top 1-10 Tab -->
								<div class="tab-pane fade show active" id="top10" role="tabpanel">
									<?php
									if (!empty($keywordDistribution['top10']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th>Keyword</th>
														<th>Rank</th>
														<th>Search Engine</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($keywordDistribution['top10']['rows'] as $kw): ?>
														<tr>
															<td><?php echo htmlspecialchars($kw['name'])?></td>
															<td><span class="badge bg-info"><?php echo $kw['rank']?></span></td>
															<td><?php echo htmlspecialchars(formatUrl($kw['search_engine']))?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									<?php else: ?>
										<p class="text-muted">No keywords in positions 1-10.</p>
									<?php endif; ?>
								</div>

								<!-- Top 11-20 Tab -->
								<div class="tab-pane fade" id="top20" role="tabpanel">
									<?php if (!empty($keywordDistribution['top20']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th>Keyword</th>
														<th>Rank</th>
														<th>Search Engine</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($keywordDistribution['top20']['rows'] as $kw): ?>
														<tr>
															<td><?php echo htmlspecialchars($kw['name'])?></td>
															<td><span class="badge" style="background-color: #fd7e14;"><?php echo $kw['rank']?></span></td>
															<td><?php echo htmlspecialchars(formatUrl($kw['search_engine']))?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									<?php else: ?>
										<p class="text-muted">No keywords in positions 11-20.</p>
									<?php endif; ?>
								</div>

								<!-- Top 21-50 Tab -->
								<div class="tab-pane fade" id="top50" role="tabpanel">
									<?php if (!empty($keywordDistribution['top50']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th>Keyword</th>
														<th>Rank</th>
														<th>Search Engine</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($keywordDistribution['top50']['rows'] as $kw): ?>
														<tr>
															<td><?php echo htmlspecialchars($kw['name'])?></td>
															<td><span class="badge" style="background-color: #e83e8c;"><?php echo $kw['rank']?></span></td>
															<td><?php echo htmlspecialchars(formatUrl($kw['search_engine']))?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									<?php else: ?>
										<p class="text-muted">No keywords in positions 21-50.</p>
									<?php endif; ?>
								</div>

								<!-- Top 51-100 Tab -->
								<div class="tab-pane fade" id="top100" role="tabpanel">
									<?php if (!empty($keywordDistribution['top100']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th>Keyword</th>
														<th>Rank</th>
														<th>Search Engine</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($keywordDistribution['top100']['rows'] as $kw): ?>
														<tr>
															<td><?php echo htmlspecialchars($kw['name'])?></td>
															<td><span class="badge bg-danger"><?php echo $kw['rank']?></span></td>
															<td><?php echo htmlspecialchars(formatUrl($kw['search_engine']))?></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									<?php else: ?>
										<p class="text-muted">No keywords in positions 51-100.</p>
									<?php endif; ?>
								</div>

								<!-- Not Ranked Tab -->
								<div class="tab-pane fade" id="notranked" role="tabpanel">
									<?php if (!empty($keywordDistribution['not_ranked']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th>Keyword</th>
														<th>Status</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($keywordDistribution['not_ranked']['rows'] as $kw): ?>
														<tr>
															<td><?php echo htmlspecialchars($kw['name'])?></td>
															<td><span class="badge bg-secondary">Not Ranked</span></td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									<?php else: ?>
										<p class="text-muted">All keywords are ranked.</p>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<script type="text/javascript">
							function showDistTab(tabName) {
								// Hide all tab panes
								var tabPanes = document.querySelectorAll('#distTabContent .tab-pane');
								for (var i = 0; i < tabPanes.length; i++) {
									tabPanes[i].classList.remove('show', 'active');
								}

								// Remove active class from all tabs
								var tabs = document.querySelectorAll('#distTab .nav-link');
								for (var i = 0; i < tabs.length; i++) {
									tabs[i].classList.remove('active');
								}

								// Show selected tab pane
								var selectedPane = document.getElementById(tabName);
								if (selectedPane) {
									selectedPane.classList.add('show', 'active');
								}

								// Add active class to clicked tab
								var selectedTab = document.getElementById(tabName + '-tab');
								if (selectedTab) {
									selectedTab.classList.add('active');
								}
							}
						</script>
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
					<h4>Ranking Volatility</h4>
					<small class="text-white">Keywords with most ranking fluctuations</small>
				</div>
				<div class="card-body">
					<?php if (!empty($rankingVolatility)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawVolatilityChart);

							function drawVolatilityChart() {
								var data = google.visualization.arrayToDataTable([
									['Keyword', 'Volatility Score', { role: 'style' }, { role: 'annotation' }],
									<?php
									foreach ($rankingVolatility as $row) {
										// Color based on volatility score - higher = more red
										$score = $row['volatility_score'];
										if ($score > 15) {
											$color = '#dc3545'; // Red - high volatility
										} elseif ($score > 10) {
											$color = '#fd7e14'; // Orange - medium volatility
										} elseif ($score > 5) {
											$color = '#ffc107'; // Yellow - moderate volatility
										} else {
											$color = '#28a745'; // Green - low volatility
										}

										$keyword = strlen($row['keyword']) > 20 ? substr($row['keyword'], 0, 20) . '...' : $row['keyword'];
										echo "['" . addslashes($keyword) . "', " . $score . ", '" . $color . "', " . $score . "],\n";
									}
									?>
								]);

								var options = {
									title: 'Top 10 Most Volatile Keywords',
									height: 350,
									legend: { position: 'none' },
									chartArea: { width: '70%', height: '70%' },
									hAxis: {
										title: 'Volatility Score (Standard Deviation)',
										minValue: 0
									},
									vAxis: {
										title: 'Keywords'
									},
									annotations: {
										alwaysOutside: true,
										textStyle: {
											fontSize: 11,
											bold: true,
											color: '#000'
										}
									},
									tooltip: { isHtml: true }
								};

								var chart = new google.visualization.BarChart(document.getElementById('volatility_chart'));
								chart.draw(data, options);
							}
						</script>
						<div id="volatility_chart" style="width: 100%; height: 350px;"></div>

						<!-- Volatility Details Table -->
						<div class="table-responsive mt-3">
							<table class="table table-sm table-hover">
								<thead>
									<tr>
										<th>Keyword</th>
										<th>Min Rank</th>
										<th>Max Rank</th>
										<th>Avg Rank</th>
										<th>Volatility</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($rankingVolatility as $row): ?>
										<tr>
											<td><?php echo htmlspecialchars($row['keyword'])?></td>
											<td><span class="badge bg-success"><?php echo $row['min_rank']?></span></td>
											<td><span class="badge bg-danger"><?php echo $row['max_rank']?></span></td>
											<td><?php echo $row['avg_rank']?></td>
											<td>
												<?php
												$score = $row['volatility_score'];
												if ($score > 15) {
													$badge = 'bg-danger';
													$label = 'High';
												} elseif ($score > 10) {
													$badge = 'bg-warning';
													$label = 'Medium';
												} elseif ($score > 5) {
													$badge = 'bg-info';
													$label = 'Moderate';
												} else {
													$badge = 'bg-success';
													$label = 'Low';
												}
												?>
												<span class="badge <?php echo $badge?>"><?php echo $score?> (<?php echo $label?>)</span>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php } else { ?>
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Records Found']?>
							<p class="mb-0 mt-2"><small>Volatility data requires at least 2 ranking checks within the selected period.</small></p>
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
											<td><?php echo htmlspecialchars(formatUrl($activity['search_engine']))?></td>
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
