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
				<option value="day" <?php echo (isset($period) && $period == 'day') ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
				<option value="week" <?php echo (isset($period) && $period == 'week') ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
				<option value="month" <?php echo (!isset($period) || $period == 'month') ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
				<option value="year" <?php echo (isset($period) && $period == 'year') ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
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
							<h6 class="mb-3">
								<?php echo $spText['common']['Domain Authority']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Domain Authority (0-100). Higher is better. Red: 1-20 (Weak), Yellow: 21-50 (Moderate), Blue: 51-70 (Strong), Green: 71-100 (Very Strong)"></i>
							</h6>
							<?php
							$da = floatval($websiteStats['domain_authority']);
							$daColor = getAuthorityColor($da);
							$daLabel = getAuthorityLabel($da);
							?>
							<h3>
								<span class="badge bg-<?php echo $daColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $daLabel?>">
									<?php echo round($da, 2)?>
								</span>
							</h3>
							<small class="text-muted"><?php echo $daLabel?></small>
							<?php if (isset($websiteComparison['domain_authority'])):
								$comp = $websiteComparison['domain_authority'];
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
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Page Authority (0-100). Higher is better. Red: 1-20 (Weak), Yellow: 21-50 (Moderate), Blue: 51-70 (Strong), Green: 71-100 (Very Strong)"></i>
							</h6>
							<?php
							$pa = floatval($websiteStats['page_authority']);
							$paColor = getAuthorityColor($pa);
							$paLabel = getAuthorityLabel($pa);
							?>
							<h3>
								<span class="badge bg-<?php echo $paColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $paLabel?>">
									<?php echo round($pa, 2)?>
								</span>
							</h3>
							<small class="text-muted"><?php echo $paLabel?></small>
							<?php if (isset($websiteComparison['page_authority'])):
								$comp = $websiteComparison['page_authority'];
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
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Spam likelihood (0-100%). Lower is better. Green: 0-30% (Low Risk), Yellow: 31-60% (Medium Risk), Red: 61-100% (High Risk)"></i>
							</h6>
							<?php
							$spamScore = floatval($websiteStats['spam_score']);
							$spamScoreColor = getSpamScoreColor($spamScore);
							$spamScoreLabel = getSpamScoreLabel($spamScore);
							?>
							<h3>
								<span class="badge bg-<?php echo $spamScoreColor?>" style="font-size: 1.5rem; padding: 0.5rem 1rem;" title="<?php echo $spamScoreLabel?>">
									<?php echo round($spamScore, 2)?>%
								</span>
							</h3>
							<small class="text-muted"><?php echo $spamScoreLabel?></small>
							<?php if (isset($websiteComparison['spam_score'])):
								$comp = $websiteComparison['spam_score'];
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
								<?php echo $spTextHome['Backlinks']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to this page"></i>
							</h6>
							<h3><span class="badge bg-primary" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($websiteStats['external_pages_to_page'])?></span></h3>
							<?php if (isset($websiteComparison['external_pages_to_page'])):
								$comp = $websiteComparison['external_pages_to_page'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spTextBack['Domain Backlinks']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="External pages linking to this root domain"></i>
							</h6>
							<h3><span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;"><?php echo number_format($websiteStats['external_pages_to_root_domain'])?></span></h3>
							<?php if (isset($websiteComparison['external_pages_to_root_domain'])):
								$comp = $websiteComparison['external_pages_to_root_domain'];
								$color = $comp['direction'] == 'up' ? 'success' : ($comp['direction'] == 'down' ? 'danger' : 'secondary');
								$icon = $comp['direction'] == 'up' ? '↑' : ($comp['direction'] == 'down' ? '↓' : '→');
							?>
							<small class="text-<?php echo $color?>">
								<strong><?php echo $icon?> <?php echo $comp['diff'] >= 0 ? '+' : ''?><?php echo $comp['diff']?> (<?php echo $comp['percent']?>%)</strong>
							</small>
							<?php endif; ?>
						</div>
						<div class="col-md-2 text-center">
							<h6 class="mb-3">
								<?php echo $spTextHome['Pages Indexed']?>
								<i class="fas fa-info-circle" data-toggle="tooltip" title="Total pages indexed by search engines (Google + Bing)"></i>
							</h6>
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
					<h4><?php echo $spTextDashboard['Keyword Statistics']?></h4>
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
							<h6 class="mb-3"><?php echo $spTextDashboard['Top 3']?> <?php echo $spText['common']['Rankings']?></h6>
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
							<h6 class="mb-3"><?php echo $spTextDashboard['Top 10']?> <?php echo $spText['common']['Rankings']?></h6>
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
							<h6 class="mb-3"><?php echo $spTextDashboard['Not Ranked']?></h6>
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
					<h4><?php echo $spTextDashboard['Keyword Distribution by Rank']?></h4>
				</div>
				<div class="card-body">
					<?php if (!empty($keywordDistribution)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawKeywordDistChart);

							function drawKeywordDistChart() {
								var data = google.visualization.arrayToDataTable([
									['<?php echo $spText['common']['Rank']?> <?php echo $spText['common']['Range']?>', '<?php echo $spText['common']['Number']?> <?php echo $spText['common']['Keywords']?>'],
									['Top 10 (1-10)', <?php echo $keywordDistribution['top10']['count']?>],
									['Top 20 (11-20)', <?php echo $keywordDistribution['top20']['count']?>],
									['Top 50 (21-50)', <?php echo $keywordDistribution['top50']['count']?>],
									['Top 100 (51-100)', <?php echo $keywordDistribution['top100']['count']?>],
									['Not Ranked', <?php echo $keywordDistribution['not_ranked']['count']?>]
								]);

								var options = {
									title: '<?php echo $spTextDashboard['Keywords by Ranking Position']?>',
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
									<a class="nav-link active" id="top10-tab" data-toggle="tab" href="#top10" role="tab" onclick="showDistTab('top10'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge bg-info"><?php echo $keywordDistribution['top10']['count']?></span> Top 1-10
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top20-tab" data-toggle="tab" href="#top20" role="tab" onclick="showDistTab('top20'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge" style="background-color: #fd7e14;"><?php echo $keywordDistribution['top20']['count']?></span> Top 11-20
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top50-tab" data-toggle="tab" href="#top50" role="tab" onclick="showDistTab('top50'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge" style="background-color: #e83e8c;"><?php echo $keywordDistribution['top50']['count']?></span> Top 21-50
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top100-tab" data-toggle="tab" href="#top100" role="tab" onclick="showDistTab('top100'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge bg-danger"><?php echo $keywordDistribution['top100']['count']?></span> Top 51-100
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="notranked-tab" data-toggle="tab" href="#notranked" role="tab" onclick="showDistTab('notranked'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge bg-secondary"><?php echo $keywordDistribution['not_ranked']['count']?></span> Not Ranked
									</a>
								</li>
							</ul>
							<div class="tab-content border-0 p-0" id="distTabContent">
								<!-- Top 1-10 Tab -->
								<div class="tab-pane fade show active" id="top10" role="tabpanel">
									<?php
									if (!empty($keywordDistribution['top10']['rows'])): ?>
										<div class="table-responsive">
											<table class="table table-sm table-hover">
												<thead>
													<tr>
														<th><?php echo $spText['common']['Keyword']?></th>
														<th><?php echo $spText['common']['Rank']?></th>
														<th><?php echo $spText['common']['Search Engine']?></th>
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
														<th><?php echo $spText['common']['Keyword']?></th>
														<th><?php echo $spText['common']['Rank']?></th>
														<th><?php echo $spText['common']['Search Engine']?></th>
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
														<th><?php echo $spText['common']['Keyword']?></th>
														<th><?php echo $spText['common']['Rank']?></th>
														<th><?php echo $spText['common']['Search Engine']?></th>
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
														<th><?php echo $spText['common']['Keyword']?></th>
														<th><?php echo $spText['common']['Rank']?></th>
														<th><?php echo $spText['common']['Search Engine']?></th>
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
														<th><?php echo $spText['common']['Keyword']?></th>
														<th><?php echo $spText['common']['Status']?></th>
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
					<h4><?php echo $spTextDashboard['Ranking Volatility']?></h4>
					<small class="text-white"><?php echo $spTextDashboard['Keywords with most ranking fluctuations']?></small>
				</div>
				<div class="card-body">
					<?php if (!empty($rankingVolatility)) { ?>
						<script type="text/javascript">
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback(drawVolatilityChart);

							function drawVolatilityChart() {
								var data = google.visualization.arrayToDataTable([
									['<?php echo $spText['common']['Keyword']?>', '<?php echo $spTextDashboard['Volatility Score']?>', { role: 'style' }, { role: 'annotation' }],
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
									title: '<?php echo $spTextDashboard['Top 10 Most Volatile Keywords']?>',
									height: 350,
									legend: { position: 'none' },
									chartArea: { width: '70%', height: '70%' },
									hAxis: {
										title: '<?php echo $spTextDashboard['Volatility Score (Standard Deviation)']?>',
										minValue: 0
									},
									vAxis: {
										title: '<?php echo $spText['common']['Keywords']?>'
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
										<th><?php echo $spText['common']['Keyword']?></th>
										<th><?php echo $spText['common']['Search Engine']?></th>
										<th><?php echo $spTextDashboard['Best Rank']?></th>
										<th><?php echo $spTextDashboard['Worst Rank']?></th>
										<th><?php echo $spTextDashboard['Avg Rank']?></th>
										<th><?php echo $spTextDashboard['Trend']?></th>
										<th><?php echo $spTextDashboard['Volatility']?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($rankingVolatility as $row): ?>
										<tr>
											<td><?php echo htmlspecialchars($row['keyword'])?></td>
											<td>
												<?php
												// Display search engine - now single engine per row
												$engine = !empty($row['search_engine']) ? $row['search_engine'] : '-';
												echo '<small>' . htmlspecialchars($engine) . '</small>';
												?>
											</td>
											<td><span class="badge bg-success"><?php echo $row['min_rank']?></span></td>
											<td><span class="badge bg-danger"><?php echo $row['max_rank']?></span></td>
											<td><?php echo $row['avg_rank']?></td>
											<td>
												<?php
												// Show trend with direction indicator
												// Negative change = improvement (rank decreased)
												// Positive change = decline (rank increased)
												$change = $row['rank_change'];
												$changeAbs = $row['rank_change_abs'];
												$trendDir = $row['trend_direction'];

												if ($trendDir == 'improving') {
													// Rank decreased = better position
													$trendBadge = 'bg-success';
													$trendIcon = '↑'; // Up arrow for improvement
													$trendText = "{$changeAbs} {$spTextDashboard['positions']}";
												} elseif ($trendDir == 'declining') {
													// Rank increased = worse position
													$trendBadge = 'bg-danger';
													$trendIcon = '↓'; // Down arrow for decline
													$trendText = "{$changeAbs} {$spTextDashboard['positions']}";
												} else {
													$trendBadge = 'bg-secondary';
													$trendIcon = '→';
													$trendText = 'No change';
												}
												?>
												<span class="badge <?php echo $trendBadge?>" title="From rank <?php echo $row['first_rank']?> to <?php echo $row['last_rank']?>">
													<?php echo $trendIcon?> <?php echo $trendText?>
												</span>
											</td>
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
							<p class="mb-0 mt-2"><small><?php echo $spTextDashboard['Volatility data requires at least 2 ranking checks within the selected period']?></small></p>
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

<script type="text/javascript">
// Sync website selection with other tabs
$(document).ready(function() {
	if (sessionStorage.getItem('sp_dashboard_auto_loading')) {
		sessionStorage.removeItem('sp_dashboard_auto_loading');
		return;
	}

	var storedWebsiteId = sessionStorage.getItem('sp_selected_website_id');
	var currentWebsiteId = '<?php echo $websiteId?>';

	if (storedWebsiteId && storedWebsiteId != currentWebsiteId) {
		if ($('#website_id option[value="' + storedWebsiteId + '"]').length) {
			$('#website_id').val(storedWebsiteId);
			sessionStorage.setItem('sp_dashboard_auto_loading', '1');
			scriptDoLoadPost('dashboard.php', 'dashboard_form', 'content');
			return;
		}
	}

	if (currentWebsiteId) {
		sessionStorage.setItem('sp_selected_website_id', currentWebsiteId);
	}
});
</script>
