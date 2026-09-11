<?php if (empty($serpList)): ?>
	<div class="alert alert-info mb-0">
		<i class="fa fa-info-circle"></i> No SERP data available for this keyword on this date.
	</div>
<?php else: ?>
	<p class="text-muted mb-3" style="font-size:0.9rem;">
		Keyword: <strong><?php echo htmlspecialchars($keyword)?></strong> &mdash; <?php echo htmlspecialchars($date)?>
	</p>
	<ul class="nav nav-tabs mb-3" id="serpTabsSP" role="tablist">
		<?php foreach ($serpList as $i => $seInfo): ?>
			<li class="nav-item">
				<a class="nav-link <?php echo $i == 0 ? 'active' : ''?>"
				   data-toggle="tab"
				   href="#serp-sp-tab-<?php echo intval($seInfo['searchengine_id'])?>"
				   role="tab">
					<i class="fa fa-search"></i> <?php echo htmlspecialchars($seInfo['domain'])?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php
	$matchBase = preg_replace('#^https?://(www\.)?#i', '', $websiteUrl);
	$matchBase = rtrim($matchBase, '/');
	?>
	<div class="tab-content">
		<?php foreach ($serpList as $i => $seInfo): ?>
			<div class="tab-pane fade <?php echo $i == 0 ? 'show active' : ''?>"
			     id="serp-sp-tab-<?php echo intval($seInfo['searchengine_id'])?>">
				<?php if (!empty($seInfo['serp_data'])): ?>
					<div style="max-height: 400px; overflow-y: auto;">
						<table class="table table-sm table-striped table-hover mb-0">
							<thead class="thead-light">
								<tr>
									<th style="width: 50px; text-align: center;">#</th>
									<th>URL</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($seInfo['serp_data'] as $result):
									$resultBase = preg_replace('#^https?://(www\.)?#i', '', $result['url']);
									$isMatch    = !empty($matchBase) && (stripos($resultBase, $matchBase) === 0);
								?>
									<tr <?php echo $isMatch ? 'style="background:#fffbe6;"' : ''?>>
										<td class="text-center text-muted"><?php echo intval($result['rank'])?></td>
										<td style="font-size: 0.85rem; word-break: break-all;">
											<?php if ($isMatch): ?>
												<i class="fas fa-star" style="color:#f0ad4e; margin-right:4px;" title="Your website"></i>
											<?php endif; ?>
											<a href="<?php echo htmlspecialchars($result['url'])?>" target="_blank" rel="noopener"
											   <?php echo $isMatch ? 'style="font-weight:600;"' : ''?>>
												<?php echo htmlspecialchars($result['url'])?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-warning mb-0">
						<i class="fa fa-exclamation-triangle"></i> No SERP entries recorded for this search engine.
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
