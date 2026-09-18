<?php echo showSectionHead($spTextTools['SERP Results Archive'] ?? 'SERP Results Archive'); ?>
<form id="serp_archive_form">
<table class="search">
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
			<button type="button" class="btn btn-primary btn-sm" onclick="scriptDoLoadPost('reports.php', 'serp_archive_form', 'content', '&sec=serparchive')">
				<i class="fa fa-search"></i> <?php echo $spText['common']['Go'] ?? 'Go'?>
			</button>
		</td>
	</tr>
</table>
</form>


<div id="subcontent" style="margin-top:15px;">
<?php if (empty($engines)): ?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Records Found']?>!
	</div>
<?php else: ?>
	<?php
	// normalise the website URL for matching (strip protocol + www for flexible comparison)
	$matchBase = preg_replace('#^https?://(www\.)?#i', '', $websiteUrl);
	$matchBase = rtrim($matchBase, '/');
	?>
	<ul class="nav nav-tabs mb-0" role="tablist" style="border-bottom:1px solid #dee2e6;">
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

	<div class="tab-content" style="border:1px solid #dee2e6; border-top:none; border-radius:0 0 4px 4px;">
		<?php foreach ($engines as $i => $seInfo): ?>
			<div class="tab-pane fade <?php echo $i == 0 ? 'show active' : ''?>"
			     id="serp-se-<?php echo $i?>">
				<p class="text-muted mb-2 px-3 pt-3" style="font-size:0.82rem;">
					<?php echo $spText['common']['Date'] ?? 'Date'?>: <strong><?php echo htmlspecialchars($seInfo['date'])?></strong>
					&nbsp;&mdash;&nbsp;
					<?php echo $spText['common']['Rank'] ?? 'Rank'?>: <strong><?php echo intval($seInfo['rank'])?></strong>
				</p>
				<?php if (!empty($seInfo['serp_data'])): ?>
				<div style="max-height:480px; overflow-y:auto; padding:0 12px 12px;">
					<table class="table table-sm table-striped table-hover mb-0">
						<thead class="thead-light">
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
							<tr <?php echo $isMatch ? 'style="background:#fffbe6;"' : ''?>>
								<td class="text-center text-muted"><?php echo intval($result['rank'])?></td>
								<td style="font-size:0.85rem; word-break:break-all;">
									<?php if ($isMatch): ?>
										<i class="fas fa-star" style="color:#f0ad4e; margin-right:4px;" title="Your website"></i>
									<?php endif; ?>
									<a href="<?php echo htmlspecialchars($resultUrl)?>" target="_blank" rel="noopener"
									   <?php echo $isMatch ? 'style="font-weight:600;"' : ''?>>
										<?php echo htmlspecialchars($resultUrl)?>
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
				<div class="alert alert-warning mx-3 mb-3">
					<i class="fa fa-exclamation-triangle"></i> No SERP entries for this search engine.
				</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
</div>
