<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextTools['SERP Results Archive'] ?? 'SERP Results Archive'); ?>

<style>
/* Page-scoped, not shared via _styles.ctp.php (only the .aiv-card/
   .aiv-table/.aiv-pill/.aiv-btn primitives are reused from there) -
   restyles this page's own Bootstrap nav-tabs to match the gradient-
   pill tab look used elsewhere, without touching Bootstrap's generic
   .nav-tabs/.nav-link classes (used unrelated elsewhere in the app)
   or its native data-toggle="tab" JS, which this page still relies on. */
/* .nav-tabs itself carries a dark gradient bar background (screen.css,
   designed for a different dark-tab-bar look elsewhere in the app) -
   has to be explicitly cleared here, not just have its spacing
   tweaked, or that dark bar shows through behind these lighter pills. */
.sra-tabs.nav-tabs {
	background: transparent; box-shadow: none; padding: 0; border-radius: 0;
	border-bottom: none; gap: 8px;
}
.sra-tabs.nav-tabs .nav-item { margin-bottom: 0; margin-right: 0; }
.sra-tabs.nav-tabs .nav-link {
	background: #fff;
	border: 1px solid #d8dee8; border-radius: 9px;
	color: #4a4e69; font-weight: 600; font-size: 13.5px;
	padding: 9px 18px; margin-bottom: 0;
	display: inline-flex; align-items: center; gap: 7px;
	box-shadow: none; transform: none;
	transition: all 0.15s ease;
}
.sra-tabs.nav-tabs .nav-link:hover {
	background: #f4f5fb; border-color: #8890e0; color: #5c4fd6;
	box-shadow: none; transform: none;
}
.sra-tabs.nav-tabs .nav-link.active {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-color: #667eea; color: #fff;
	box-shadow: 0 3px 10px rgba(102,126,234,0.35); transform: none;
}
.sra-tabs.nav-tabs .nav-link.active:hover {
	background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
	color: #fff;
}
.sra-stat-row { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; font-size: 13px; color: #5a5e78; margin-bottom: 14px; }
.sra-stat-row strong { color: #2a2a3d; }
.sra-stat-row .sep { color: #d8dee8; }
</style>

<div class="aiv-card">
	<form id="serp_archive_form">
	<table class="search" style="margin-bottom:0;">
		<tr>
			<th><?php echo $spText['common']['Website']?>: </th>
			<td>
				<select name="website_id" id="sa_website_id" class="custom-select" style="width:220px;"
				        onchange="scriptDoLoad('reports.php', 'content', 'sec=serparchive&website_id=' + this.value)">
					<?php foreach ($websiteList as $w): ?>
						<option value="<?php echo $w['id']?>" <?php echo ($w['id'] == $websiteId) ? 'selected' : ''?>>
							<?php echo htmlspecialchars($w['name'])?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<th class="pl-4"><?php echo $spText['common']['Keyword']?>: </th>
			<td>
				<select name="keyword_id" id="sa_keyword_id" class="custom-select" style="width:220px;"
				        onchange="scriptDoLoadPost('reports.php', 'serp_archive_form', 'content', '&sec=serparchive')">
					<?php foreach ($keywordList as $kw): ?>
						<option value="<?php echo $kw['id']?>" <?php echo ($kw['id'] == $keywordId) ? 'selected' : ''?>>
							<?php echo htmlspecialchars($kw['name'])?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td class="pl-3">
				<a href="javascript:void(0);" onclick="scriptDoLoadPost('reports.php', 'serp_archive_form', 'content', '&sec=serparchive')" class="aiv-btn aiv-btn-primary">
					<i class="fa fa-search"></i> <?php echo $spText['common']['Go'] ?? 'Go'?>
				</a>
			</td>
		</tr>
	</table>
	</form>
</div>

<div id="subcontent">
<?php if (empty($engines)): ?>
	<div class="aiv-note aiv-note-danger">
		<i class="fas fa-exclamation-circle"></i>
		<span><?php echo $spText['common']['No Records Found']?>!</span>
	</div>
<?php else: ?>
	<?php
	// normalise the website URL for matching (strip protocol + www for flexible comparison)
	$matchBase = preg_replace('#^https?://(www\.)?#i', '', $websiteUrl);
	$matchBase = rtrim($matchBase, '/');
	?>
	<div class="form-group" style="max-width:300px;margin-bottom:14px;">
		<input type="text" id="serp_archive_quick_filter" class="form-control" placeholder="<?php echo $spText['common']['Quick Search'] ?? 'Quick Search'?>..." onkeyup="spQuickFilterRows('serp-archive-row', this.value)">
	</div>
	<!-- spQuickFilterRows() lives in js/common.js now - this view loads via
	     AJAX innerHTML-swap, which never executes an inline <script> tag,
	     so defining it here meant the quick filter silently did nothing. -->
	<ul class="nav nav-tabs sra-tabs mb-3" role="tablist">
		<?php foreach ($engines as $i => $seInfo): ?>
			<li class="nav-item">
				<a class="nav-link <?php echo $i == 0 ? 'active' : ''?>"
				   data-toggle="tab"
				   href="#serp-se-<?php echo $i?>"
				   role="tab">
					<i class="fa fa-search"></i> <?php echo htmlspecialchars($seInfo['domain'])?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if (!empty($showSearchVolume)): ?>
		<div class="sra-stat-row">
			<i class="fas fa-chart-line" style="color:#667eea;"></i>
			<strong><?php echo $spTextKeyword['Search Volume'] ?? 'Search Volume'?> (Google):</strong>
			<?php if (!is_null($searchVolume ?? null)): ?>
				<span><?php echo number_format($searchVolume)?></span>
			<?php elseif (!empty($searchVolumeStatus)): ?>
				<span class="aiv-pill aiv-pill-muted"><?php echo htmlspecialchars($searchVolumeStatus)?></span>
			<?php else: ?>
				<span class="text-muted">&mdash;</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="aiv-card" style="padding:0; overflow:hidden;">
		<?php foreach ($engines as $i => $seInfo): ?>
			<div class="tab-pane fade <?php echo $i == 0 ? 'show active' : ''?>"
			     id="serp-se-<?php echo $i?>">
				<div class="sra-stat-row" style="padding:18px 22px 0;">
					<i class="fas fa-calendar-alt" style="color:#8a8ea3;"></i>
					<?php echo $spText['common']['Date'] ?? 'Date'?>: <strong><?php echo htmlspecialchars($seInfo['date'])?></strong>
					<span class="sep">&middot;</span>
					<?php echo $spText['common']['Rank'] ?? 'Rank'?>: <strong><?php echo intval($seInfo['rank'])?></strong>
				</div>
				<?php if (!empty($seInfo['aio_checked_at'])): ?>
					<div class="sra-stat-row" style="padding:0 22px 14px;">
						<strong><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?>:</strong>
						<?php if (empty($seInfo['aio_supported'])): ?>
							<span class="aiv-pill aiv-pill-muted"><?php echo $spTextKeyword['Not available'] ?? 'Not available'?></span>
						<?php else: ?>
							<span class="aiv-pill <?php echo !empty($seInfo['aio_present']) ? 'aiv-pill-success' : 'aiv-pill-muted'?>">
								<?php echo !empty($seInfo['aio_present']) ? ($spTextKeyword['Present'] ?? 'Present') : ($spTextKeyword['Absent'] ?? 'Absent')?>
							</span>
							<?php if (!empty($seInfo['aio_present'])): ?>
								<span class="sep">&middot;</span>
								<?php echo $spTextKeyword['Cited'] ?? 'Cited'?>:
								<?php if (!empty($seInfo['aio_cited'])): ?>
									<span class="aiv-pill aiv-pill-success"><?php echo $spText['common']['Yes'] ?? 'Yes'?><?php echo !empty($seInfo['aio_cited_position']) ? ' (#' . intval($seInfo['aio_cited_position']) . ')' : ''?></span>
								<?php else: ?>
									<span class="aiv-pill aiv-pill-muted"><?php echo $spText['common']['No'] ?? 'No'?></span>
								<?php endif; ?>
								<span class="sep">&middot;</span>
								<?php echo $spTextKeyword['Sources'] ?? 'Sources'?>:
								<?php if (!empty($seInfo['aio_reference_count'])): ?>
									<a href="javascript:void(0);" onclick="openAjaxModalSP('<?php echo SP_WEBPATH?>/reports.php?sec=aiosources&keyword_id=<?php echo intval($keywordId)?>', '<i class=&quot;fas fa-robot&quot;></i> AI Overview Cited Sources')"><?php echo intval($seInfo['aio_reference_count'])?></a>
								<?php else: ?>
									0
								<?php endif; ?>
							<?php endif; ?>
							<span class="sep">&middot;</span>
							<small class="text-muted" title="<?php echo $spTextKeyword['Data source and date this AI Overview result was last checked'] ?? 'Data source and date this AI Overview result was last checked'?>">
								<?php $aioProviderLabel = $seInfo['provider'] === 'dataforseo' ? 'DataForSEO' : ($seInfo['provider'] === 'spapi' ? 'SEO Panel API' : $seInfo['provider']); ?>
								via <?php echo htmlspecialchars($aioProviderLabel)?> &middot; checked <?php echo htmlspecialchars($seInfo['aio_data_date'])?>
							</small>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if (!empty($seInfo['serp_data'])): ?>
				<div style="max-height:480px; overflow-y:auto; padding:0 6px 6px;">
					<table class="aiv-table" style="margin:0;">
						<thead>
							<tr>
								<th style="width:46px; text-align:center;">#</th>
								<th>URL</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($seInfo['serp_data'] as $result):
								$resultUrl  = $result['url'];
								$resultBase = preg_replace('#^https?://(www\.)?#i', '', $resultUrl);
								$isMatch    = !empty($matchBase) && (stripos($resultBase, $matchBase) === 0);
							?>
							<tr class="serp-archive-row" data-txt="<?php echo htmlspecialchars(strtolower(($result['title'] ?: '') . ' ' . $resultUrl))?>" <?php echo $isMatch ? 'style="background:#fdf2da;"' : ''?>>
								<td class="text-center text-muted"><?php echo intval($result['rank'])?></td>
								<td style="font-size:0.85rem; word-break:break-all;">
									<?php if ($isMatch): ?>
										<i class="fas fa-star" style="color:#a9700f; margin-right:4px;" title="Your website"></i>
									<?php endif; ?>
									<a href="<?php echo htmlspecialchars($resultUrl)?>" target="_blank" rel="noopener"
									   <?php echo $isMatch ? 'style="font-weight:600;"' : ''?>>
										<?php echo htmlspecialchars($result['title'] ?: $resultUrl)?>
									</a>
									<br><a href="<?php echo htmlspecialchars($resultUrl)?>" target="_blank" rel="noopener" style="color:#8a8ea3; font-weight:400; font-size:13px;"><?php echo htmlspecialchars($resultUrl)?></a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
				<div class="aiv-note aiv-note-warn" style="margin:14px 22px;">
					<i class="fas fa-exclamation-triangle"></i>
					<span>No SERP entries for this search engine.</span>
				</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
</div>
