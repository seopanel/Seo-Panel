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
				<?php if (!empty($seInfo['aio_checked_at'])): ?>
					<div class="mb-2" style="font-size:0.82rem;">
						<strong><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?>:</strong>
						<?php if (empty($seInfo['aio_supported'])): ?>
							<span class="text-muted"><?php echo $spTextKeyword['Not available'] ?? 'Not available'?></span>
						<?php else: ?>
							<span class="<?php echo !empty($seInfo['aio_present']) ? 'text-success' : 'text-muted'?>">
								<?php echo !empty($seInfo['aio_present']) ? ($spTextKeyword['Present'] ?? 'Present') : ($spTextKeyword['Absent'] ?? 'Absent')?>
							</span>
							<?php if (!empty($seInfo['aio_present'])): ?>
								&nbsp;&middot;&nbsp;
								<?php echo $spTextKeyword['Cited'] ?? 'Cited'?>:
								<?php if (!empty($seInfo['aio_cited'])): ?>
									<span class="text-success"><?php echo $spText['common']['Yes'] ?? 'Yes'?><?php echo !empty($seInfo['aio_cited_position']) ? ' (#' . intval($seInfo['aio_cited_position']) . ')' : ''?></span>
								<?php else: ?>
									<span class="text-muted"><?php echo $spText['common']['No'] ?? 'No'?></span>
								<?php endif; ?>
								&nbsp;&middot;&nbsp;
								<?php echo $spTextKeyword['Sources'] ?? 'Sources'?>:
								<?php if (!empty($seInfo['aio_reference_count'])): ?>
									<a href="javascript:void(0);" onclick="openAjaxModalSP('<?php echo SP_WEBPATH?>/reports.php?sec=aiosources&keyword_id=<?php echo intval($keywordId)?>', '<i class=&quot;fas fa-robot&quot;></i> AI Overview Cited Sources')"><?php echo intval($seInfo['aio_reference_count'])?></a>
								<?php else: ?>
									0
								<?php endif; ?>
							<?php endif; ?>
							&nbsp;&middot;&nbsp;
							<small class="text-muted" title="<?php echo $spTextKeyword['Data source and date this AI Overview result was last checked'] ?? 'Data source and date this AI Overview result was last checked'?>">
								<?php $aioProviderLabel = $seInfo['provider'] === 'dataforseo' ? 'DataForSEO' : ($seInfo['provider'] === 'spapi' ? 'SEO Panel API' : $seInfo['provider']); ?>
								via <?php echo htmlspecialchars($aioProviderLabel)?> &middot; checked <?php echo htmlspecialchars($seInfo['aio_data_date'])?>
							</small>
						<?php endif; ?>
					</div>
				<?php endif; ?>
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
												<?php echo htmlspecialchars(!empty($result['title']) ? $result['title'] : $result['url'])?>
											</a>
											<br><a href="<?php echo htmlspecialchars($result['url'])?>" target="_blank" rel="noopener" style="color:#8a8ea3; font-weight:400; font-size:13px;"><?php echo htmlspecialchars($result['url'])?></a>
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
