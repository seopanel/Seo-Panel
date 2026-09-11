<style>
/* ============================================================
   Dashboard visual refresh - scoped to .sp-dashboard so it never
   bleeds into the shared card-header-gradient-blue/.badge/.table
   styles used elsewhere in the app.
   ============================================================ */
.sp-dashboard { color: #1e293b; }

.sp-dashboard .card {
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
	overflow: hidden;
}

.sp-dashboard .card-header-gradient-blue {
	background: #1e3a5f !important;
	box-shadow: none !important;
	border-bottom: 2px solid #16304d;
	padding: 0.85rem 1.25rem !important;
}
.sp-dashboard .card-header-gradient-blue h4 {
	font-size: 0.95rem !important;
	font-weight: 600 !important;
	letter-spacing: 0.02em;
}
.sp-dashboard .card-header-gradient-blue small {
	opacity: 0.75;
}

.sp-dashboard .card-body { padding: 1.5rem; }

/* Stat tiles - replaces oversized colored "badge" numbers with clean,
   large typography; semantic color lives in the number/icon, not a
   filled pill, which reads calmer and more editorial. */
.sp-dashboard .stat-tile { padding: 0.25rem 0.5rem; }
.sp-dashboard .stat-label {
	font-size: 0.72rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: #64748b;
	margin-bottom: 0.6rem;
	display: block;
}
.sp-dashboard .stat-label i { color: #94a3b8; margin-left: 0.15rem; cursor: help; }
.sp-dashboard .stat-value {
	font-size: 2rem;
	font-weight: 700;
	line-height: 1.15;
	color: #0f172a;
}
.sp-dashboard .stat-value.text-success { color: #15803d; }
.sp-dashboard .stat-value.text-warning { color: #b45309; }
.sp-dashboard .stat-value.text-danger  { color: #b91c1c; }
.sp-dashboard .stat-value.text-info    { color: #0369a1; }
.sp-dashboard .stat-value.text-primary { color: #1e3a5f; }
.sp-dashboard .stat-sublabel { font-size: 0.78rem; color: #94a3b8; margin-top: 0.15rem; }

/* Soft trend chips (replaces plain colored <strong> text) */
.sp-dashboard .trend-chip {
	display: inline-block;
	margin-top: 0.5rem;
	padding: 0.2rem 0.55rem;
	border-radius: 20px;
	font-size: 0.75rem;
	font-weight: 600;
}
.sp-dashboard .trend-chip.trend-up      { background: rgba(21, 128, 61, 0.1); color: #15803d; }
.sp-dashboard .trend-chip.trend-down    { background: rgba(185, 28, 28, 0.1); color: #b91c1c; }
.sp-dashboard .trend-chip.trend-neutral { background: rgba(71, 85, 105, 0.1); color: #475569; }

/* Soft badges everywhere else on the dashboard (rank chips, volatility,
   trend, distribution tab counters) - same saturated bg-* class names
   the controller already emits, restyled here into muted pills instead
   of solid saturated ones, so no markup/controller changes are needed. */
.sp-dashboard .badge.bg-success   { background-color: rgba(21, 128, 61, 0.12) !important; color: #15803d !important; }
.sp-dashboard .badge.bg-danger    { background-color: rgba(185, 28, 28, 0.12) !important; color: #b91c1c !important; }
.sp-dashboard .badge.bg-warning   { background-color: rgba(180, 83, 9, 0.14) !important; color: #b45309 !important; }
.sp-dashboard .badge.bg-info      { background-color: rgba(3, 105, 161, 0.12) !important; color: #0369a1 !important; }
.sp-dashboard .badge.bg-primary   { background-color: rgba(30, 58, 95, 0.12) !important; color: #1e3a5f !important; }
.sp-dashboard .badge.bg-secondary { background-color: rgba(71, 85, 105, 0.12) !important; color: #475569 !important; }
.sp-dashboard .badge { font-weight: 600; padding: 0.32rem 0.6rem; border-radius: 20px; }

.sp-dashboard .alert-info    { background: #f0f7fc; border: 1px solid #d7e9f7; color: #1e3a5f; }
.sp-dashboard .alert-warning { background: #fdf6ec; border: 1px solid #f5e3c8; color: #92400e; }

/* Tables */
.sp-dashboard table.table thead th {
	background: #f8fafc;
	color: #64748b;
	font-size: 0.72rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	border-bottom: 2px solid #e2e8f0;
	border-top: none;
	white-space: nowrap;
}
.sp-dashboard table.table td { vertical-align: middle; font-size: 0.9rem; }
.sp-dashboard table.table-striped tbody tr:nth-of-type(odd) { background-color: #f8fafc; }
.sp-dashboard table.table-hover tbody tr:hover { background-color: #eef2f9; }

/* Distribution tabs - flat underline style instead of boxed tabs */
.sp-dashboard .nav-tabs { background: none; border-bottom: 2px solid #e2e8f0; }
.sp-dashboard .nav-tabs .nav-link {
	border: none;
	color: #64748b;
	font-weight: 600;
	font-size: 0.85rem;
	border-radius: 0;
}
.sp-dashboard .nav-tabs .nav-link.active {
	color: #1e3a5f;
	background: none;
	box-shadow: inset 0 -2px 0 #1e3a5f;
}

.sp-dashboard .section-gap { margin-bottom: 1.75rem !important; }
</style>

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

<?php
// Renders one stat tile: label + big number (colored via the same
// bootstrap-style color name the controller already computes) + an
// optional soft trend chip. Kept local to this view (not promoted to a
// shared helper) since its only caller is this one page. Guarded with
// function_exists() since a plain top-level function declaration inside
// an included .ctp.php view would fatal ("cannot redeclare") if this
// view is ever rendered more than once within the same PHP process.
if (!function_exists('renderStatTile')) {
	function renderStatTile($label, $tooltip, $value, $color = null, $sublabel = null, $comparison = null) {
		$colorClass = $color ? "text-$color" : '';
		echo "<div class='stat-tile text-center'>";
		echo "<span class='stat-label'>$label";
		if (!empty($tooltip)) echo " <i class='fas fa-info-circle' data-toggle='tooltip' title=\"" . htmlspecialchars($tooltip) . "\"></i>";
		echo "</span>";
		echo "<div class='stat-value $colorClass'>$value</div>";
		if (!empty($sublabel)) echo "<div class='stat-sublabel'>$sublabel</div>";
		if (!empty($comparison)) {
			$direction = $comparison['direction'];
			$trendClass = $direction == 'up' ? 'trend-up' : ($direction == 'down' ? 'trend-down' : 'trend-neutral');
			$icon = $direction == 'up' ? '↑' : ($direction == 'down' ? '↓' : '→');
			$diff = $comparison['diff'] >= 0 ? '+' . $comparison['diff'] : $comparison['diff'];
			echo "<span class='trend-chip $trendClass'>$icon $diff ({$comparison['percent']}%)</span>";
		}
		echo "</div>";
	}
}
?>

<div class="dashboard-container sp-dashboard" style="margin-top: 32px;">

	<!-- Website Overview Stats -->
	<div class="row section-gap">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><i class="fas fa-globe"></i> <?php echo $spTextHome['Website Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2">
							<?php
							$da = floatval($websiteStats['domain_authority']);
							renderStatTile($spText['common']['Domain Authority'], 'Domain Authority (0-100). Higher is better.', round($da, 2), getAuthorityColor($da), getAuthorityLabel($da), $websiteComparison['domain_authority'] ?? null);
							?>
						</div>
						<div class="col-md-2">
							<?php
							$pa = floatval($websiteStats['page_authority']);
							renderStatTile($spText['common']['Page Authority'], 'Page Authority (0-100). Higher is better.', round($pa, 2), getAuthorityColor($pa), getAuthorityLabel($pa), $websiteComparison['page_authority'] ?? null);
							?>
						</div>
						<div class="col-md-2">
							<?php
							$spamScore = floatval($websiteStats['spam_score']);
							renderStatTile($spText['common']['Spam Score'], 'Spam likelihood (0-100%). Lower is better.', round($spamScore, 2) . '%', getSpamScoreColor($spamScore), getSpamScoreLabel($spamScore), $websiteComparison['spam_score'] ?? null);
							?>
						</div>
						<div class="col-md-2">
							<?php renderStatTile($spTextHome['Backlinks'], 'External pages linking to this page', number_format($websiteStats['external_pages_to_page']), 'primary', null, $websiteComparison['external_pages_to_page'] ?? null); ?>
						</div>
						<div class="col-md-2">
							<?php renderStatTile($spTextBack['Domain Backlinks'], 'External pages linking to this root domain', number_format($websiteStats['external_pages_to_root_domain']), 'info', null, $websiteComparison['external_pages_to_root_domain'] ?? null); ?>
						</div>
						<div class="col-md-2">
							<?php renderStatTile($spTextHome['Pages Indexed'], 'Total pages indexed by Google', number_format($websiteStats['indexed_pages']), 'success', null, $websiteComparison['indexed_pages'] ?? null); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- AI Visibility -->
	<?php
	$aioMeasured = intval($aiVisibilityStats['aioMeasured']);
	$aioPresent = intval($aiVisibilityStats['aioPresent']);
	$aioCited = intval($aiVisibilityStats['aioCited']);
	$referralHits = intval($aiVisibilityStats['referralHits']);
	$topPlatform = $aiVisibilityStats['topPlatform'];

	if ($referralHits > 0) {
		$aiFindingTitle = "AI crawlers visited $referralHits time" . ($referralHits == 1 ? '' : 's') . " in the last 30 days";
		$aiFindingDesc = "ChatGPT, Perplexity, Gemini, Claude and similar AI crawlers fetched this site $referralHits time" . ($referralHits == 1 ? '' : 's') . " over the last 30 days" . (!empty($topPlatform) ? ", most often via $topPlatform." : ".");
	} else {
		$aiFindingTitle = "No AI crawler activity in the last 30 days";
		$aiFindingDesc = "No AI crawler visit has been recorded for this site in the selected period.";
	}
	?>
	<div class="row section-gap">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue d-flex justify-content-between align-items-center">
					<h4><i class="fas fa-robot"></i> AI Visibility</h4>
					<?php if (!empty($seoDiaryPluginId)) {
						// Opt-in only - never auto-created, same pattern as
						// RecommendationsController's own "Add to SEO Diary"
						// action. Opens in the app's existing modal dialog
						// mechanism (scriptDoLoadDialog(), js/popup.js) rather
						// than navigating away - see recommendations_main.ctp.php's
						// own comment on this for the full rationale (also
						// sidesteps the layout/jQuery bug a plain full-page
						// link to seo-plugins.php used to hit).
						$diaryArgs = "&pid=" . intval($seoDiaryPluginId)
							. "&action=newDiary"
							. "&title=" . urlencode($aiFindingTitle)
							. "&description=" . urlencode($aiFindingDesc);
						$diaryOnclick = "scriptDoLoadDialog('seo-plugins.php', 'content', '" . addslashes($diaryArgs) . "')";
					?>
					<a href="javascript:void(0);" onclick="<?php echo htmlspecialchars($diaryOnclick, ENT_QUOTES)?>" class="btn btn-sm btn-light" title="Add to SEO Diary">
						<i class="fas fa-book"></i> Add to SEO Diary
					</a>
					<?php } ?>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<?php renderStatTile('AI Overview Keywords', 'Keywords with a recorded Google AI Overview check', $aioMeasured, 'secondary'); ?>
						</div>
						<div class="col-md-3">
							<?php renderStatTile('Appears in AI Overview', 'Measured keywords where an AI Overview was shown', $aioPresent, 'info'); ?>
						</div>
						<div class="col-md-3">
							<?php renderStatTile('Cited in AI Overview', 'Keywords where this site was cited as a source', $aioCited, 'success'); ?>
						</div>
						<div class="col-md-3">
							<?php renderStatTile('AI Crawler Visits (30d)', 'Requests from AI crawlers (ChatGPT, Perplexity, Gemini, Claude, etc.)', number_format($referralHits), 'primary', !empty($topPlatform) ? 'Top: ' . htmlspecialchars($topPlatform) : null); ?>
						</div>
					</div>
					<div class="alert <?php echo $referralHits > 0 ? 'alert-info' : 'alert-warning'?> mt-3 mb-0">
						<i class="fas <?php echo $referralHits > 0 ? 'fa-info-circle' : 'fa-exclamation-circle'?> me-2"></i>
						<?php echo htmlspecialchars($aiFindingTitle)?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Keyword Statistics -->
	<div class="row section-gap">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><i class="fas fa-key"></i> <?php echo $spTextDashboard['Keyword Statistics']?></h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-2">
							<?php renderStatTile($spText['common']['Total'] . ' ' . $spText['common']['Keywords'], null, $keywordStats['total'], 'primary', null, $keywordComparison['total'] ?? null); ?>
						</div>
						<div class="col-md-2">
							<?php renderStatTile($spTextKeyword['Keywords Tracked'], null, $keywordStats['tracked'], 'success', null, $keywordComparison['tracked'] ?? null); ?>
						</div>
						<div class="col-md-3">
							<?php renderStatTile($spTextDashboard['Top 3'] . ' ' . $spText['common']['Rankings'], null, $keywordStats['top3'], 'warning', null, $keywordComparison['top3'] ?? null); ?>
						</div>
						<div class="col-md-3">
							<?php renderStatTile($spTextDashboard['Top 10'] . ' ' . $spText['common']['Rankings'], null, $keywordStats['top10'], 'info', null, $keywordComparison['top10'] ?? null); ?>
						</div>
						<div class="col-md-2">
							<?php renderStatTile($spTextDashboard['Not Ranked'], null, $keywordStats['total'] - $keywordStats['tracked'], 'secondary'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Pie Charts Row -->
	<div class="row section-gap">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><i class="fas fa-chart-pie"></i> <?php echo $spTextDashboard['Keyword Distribution by Rank']?></h4>
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
									titleTextStyle: { color: '#334155', fontSize: 14, bold: false },
									pieHole: 0.4,
									height: 350,
									colors: ['#0369a1', '#b45309', '#7c3aed', '#b91c1c', '#94a3b8'],
									legend: { position: 'bottom', textStyle: { color: '#475569' } },
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
										<span class="badge bg-warning"><?php echo $keywordDistribution['top20']['count']?></span> Top 11-20
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="top50-tab" data-toggle="tab" href="#top50" role="tab" onclick="showDistTab('top50'); return false;" style="padding: 0.5rem 1rem;">
										<span class="badge" style="background-color: rgba(124,58,237,.12); color:#7c3aed;"><?php echo $keywordDistribution['top50']['count']?></span> Top 21-50
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
															<td><span class="badge bg-warning"><?php echo $kw['rank']?></span></td>
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
															<td><span class="badge" style="background-color: rgba(124,58,237,.12); color:#7c3aed;"><?php echo $kw['rank']?></span></td>
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
					<h4><i class="fas fa-chart-bar"></i> <?php echo $spTextDashboard['Ranking Volatility']?></h4>
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
											$color = '#b91c1c'; // High volatility
										} elseif ($score > 10) {
											$color = '#b45309'; // Medium volatility
										} elseif ($score > 5) {
											$color = '#ca8a04'; // Moderate volatility
										} else {
											$color = '#15803d'; // Low volatility
										}

										$keyword = strlen($row['keyword']) > 20 ? substr($row['keyword'], 0, 20) . '...' : $row['keyword'];
										echo "['" . addslashes($keyword) . "', " . $score . ", '" . $color . "', " . $score . "],\n";
									}
									?>
								]);

								var options = {
									title: '<?php echo $spTextDashboard['Top 10 Most Volatile Keywords']?>',
									titleTextStyle: { color: '#334155', fontSize: 14, bold: false },
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
											color: '#334155'
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
	<div class="row section-gap">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-gradient-blue">
					<h4><i class="fas fa-chart-line"></i> <?php echo $spTextKeyword['Ranking Trends']?></h4>
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
									titleTextStyle: { color: '#334155', fontSize: 14, bold: false },
									curveType: 'function',
									legend: { position: 'bottom', textStyle: { color: '#475569' } },
									height: 400,
									series: {
										0: { targetAxisIndex: 0, color: '#0369a1' },
										1: { targetAxisIndex: 0, color: '#15803d' },
										2: { targetAxisIndex: 1, color: '#b91c1c' }
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
					<h4><i class="fas fa-trophy"></i> <?php echo $spTextKeyword['Top Keywords']?></h4>
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
					<h4><i class="fas fa-history"></i> <?php echo $spText['label']['Recent Activity']?></h4>
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
