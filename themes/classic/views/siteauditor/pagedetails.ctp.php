<?php
$backLink = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&pageno={$post['pageno']}&order_col={$post['order_col']}&order_val={$post['order_val']}')";

// Calculate link statistics
$totalLinks = count($linkList);
$externalCount = 0;
$nofollowCount = 0;
foreach($linkList as $link) {
	if (!empty($link['extrenal'])) $externalCount++;
	if (!empty($link['nofollow'])) $nofollowCount++;
}
$internalCount = $totalLinks - $externalCount;
$dofollowCount = $totalLinks - $nofollowCount;
?>
<style>
/* Tab Styles */
.page-analyzer-container {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.08);
	overflow: hidden;
}
.page-url-banner {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 20px 25px;
}
.page-url-banner h4 {
	margin: 0 0 8px 0;
	font-size: 14px;
	opacity: 0.9;
	font-weight: 500;
}
.page-url-banner a {
	color: white;
	font-size: 16px;
	word-break: break-all;
	text-decoration: none;
}
.page-url-banner a:hover {
	text-decoration: underline;
}
.tabs-header {
	display: flex;
	background: #f8f9fa;
	border-bottom: 1px solid #dee2e6;
}
.tab-btn {
	flex: 1;
	padding: 18px 25px;
	border: none;
	background: linear-gradient(135deg, #e8f4f8 0%, #d4e5f7 100%);
	cursor: pointer;
	font-size: 15px;
	font-weight: 600;
	color: #3498db;
	transition: all 0.3s;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 10px;
	position: relative;
	border-bottom: 3px solid #3498db;
}
.tab-btn:hover {
	color: #2980b9;
	background: linear-gradient(135deg, #d4e5f7 0%, #c4daf0 100%);
}
.tab-btn.active {
	color: #667eea;
	background: #fff;
	border-bottom: 3px solid transparent;
}
.tab-btn.active::after {
	content: '';
	position: absolute;
	bottom: -3px;
	left: 0;
	right: 0;
	height: 3px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.tab-btn .badge-count {
	background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
	color: #fff;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 700;
	box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);
}
.tab-btn.active .badge-count {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}
.tab-content {
	display: none;
	padding: 25px;
}
.tab-content.active {
	display: block;
}

/* Page Details Styles */
.detail-section {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
}
.detail-section:last-child {
	margin-bottom: 0;
}
.detail-section-title {
	color: #495057;
	font-size: 13px;
	font-weight: 700;
	margin-bottom: 15px;
	padding-bottom: 10px;
	border-bottom: 2px solid #dee2e6;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	display: flex;
	align-items: center;
	gap: 8px;
}
.detail-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 15px;
}
.detail-card {
	background: white;
	border-radius: 6px;
	padding: 15px 18px;
	border-left: 3px solid #667eea;
}
.detail-card.full-width {
	grid-column: 1 / -1;
}
.detail-label {
	color: #6c757d;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	margin-bottom: 8px;
	display: flex;
	align-items: center;
	gap: 6px;
}
.detail-value {
	color: #212529;
	font-size: 14px;
	line-height: 1.6;
	word-break: break-word;
}
.detail-value a {
	color: #0066cc;
	text-decoration: none;
}
.detail-value a:hover {
	text-decoration: underline;
}

.status-badge {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	padding: 4px 12px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
}
.status-badge.success {
	background: #d4edda;
	color: #155724;
}
.status-badge.danger {
	background: #f8d7da;
	color: #721c24;
}
.score-display {
	display: flex;
	align-items: center;
	gap: 10px;
}
.score-bars {
	display: flex;
	gap: 2px;
}
.score-bar {
	width: 6px;
	height: 18px;
	border-radius: 2px;
}
.score-bar.plus { background: #28a745; }
.score-bar.minus { background: #dc3545; }
.score-number {
	font-size: 20px;
	font-weight: 700;
}
.score-number.positive { color: #28a745; }
.score-number.negative { color: #dc3545; }
.metric-value {
	font-size: 24px;
	font-weight: 700;
	color: #212529;
}

/* Page Links Styles */
.links-stats-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 15px;
	margin-bottom: 25px;
}
@media (max-width: 768px) {
	.links-stats-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}
.stat-card {
	background: #fff;
	border-radius: 10px;
	padding: 20px;
	text-align: center;
	border: 1px solid #e9ecef;
	transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.stat-card.internal { border-top: 4px solid #28a745; }
.stat-card.external { border-top: 4px solid #fd7e14; }
.stat-card.dofollow { border-top: 4px solid #007bff; }
.stat-card.nofollow { border-top: 4px solid #6c757d; }
.stat-number {
	font-size: 32px;
	font-weight: 700;
	color: #212529;
	line-height: 1;
}
.stat-label {
	font-size: 12px;
	color: #6c757d;
	text-transform: uppercase;
	margin-top: 8px;
	font-weight: 600;
}
.links-toolbar {
	display: flex;
	gap: 12px;
	margin-bottom: 20px;
	flex-wrap: wrap;
	align-items: center;
}
.search-box {
	flex: 1;
	min-width: 250px;
	position: relative;
}
.search-box input {
	width: 100%;
	padding: 12px 20px 12px 45px;
	border: 2px solid #e9ecef;
	border-radius: 25px;
	font-size: 14px;
	transition: border-color 0.2s, box-shadow 0.2s;
}
.search-box input:focus {
	outline: none;
	border-color: #667eea;
	box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}
.search-box i {
	position: absolute;
	left: 18px;
	top: 50%;
	transform: translateY(-50%);
	color: #adb5bd;
}
.filter-pills {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}
.filter-pill {
	padding: 10px 18px;
	border: 2px solid #e9ecef;
	background: #fff;
	border-radius: 25px;
	cursor: pointer;
	font-size: 13px;
	font-weight: 600;
	color: #495057;
	transition: all 0.2s;
}
.filter-pill:hover {
	border-color: #667eea;
	color: #667eea;
}
.filter-pill.active {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	border-color: transparent;
}
.links-table-container {
	border-radius: 10px;
	overflow: hidden;
	border: 1px solid #e9ecef;
}
.links-table {
	width: 100%;
	border-collapse: collapse;
}
.links-table thead {
	background: linear-gradient(135deg, #343a40 0%, #495057 100%);
}
.links-table th {
	padding: 14px 18px;
	text-align: left;
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.links-table tbody tr {
	transition: background 0.2s;
}
.links-table tbody tr:hover {
	background: #f8f9fa;
}
.links-table td {
	padding: 14px 18px;
	border-bottom: 1px solid #e9ecef;
	font-size: 14px;
}
.links-table tbody tr:last-child td {
	border-bottom: none;
}
.link-cell {
	max-width: 300px;
}
.link-url {
	display: block;
	color: #0066cc;
	text-decoration: none;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.link-url:hover {
	color: #004499;
	text-decoration: underline;
}
.link-anchor {
	display: block;
	max-width: 180px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: #495057;
}
.link-title {
	display: block;
	max-width: 140px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: #868e96;
	font-style: italic;
	font-size: 13px;
}
.type-badge {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	padding: 5px 12px;
	border-radius: 15px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
}
.type-badge.internal {
	background: #d4edda;
	color: #155724;
}
.type-badge.external {
	background: #fff3cd;
	color: #856404;
}
.type-badge.dofollow {
	background: #cce5ff;
	color: #004085;
}
.type-badge.nofollow {
	background: #e9ecef;
	color: #495057;
}
.row-number {
	color: #adb5bd;
	font-weight: 600;
	font-size: 12px;
}
.empty-state {
	text-align: center;
	padding: 60px 20px;
	color: #6c757d;
}
.empty-state i {
	font-size: 60px;
	color: #dee2e6;
	margin-bottom: 20px;
}
.empty-state p {
	font-size: 16px;
	margin: 0;
}
.back-button {
	margin-bottom: 20px;
}
</style>

<div class="back-button">
	<a href="javascript:void(0)" onclick="<?php echo $backLink?>" class="btn btn-info">
		<i class="fas fa-arrow-left"></i> Back
	</a>
</div>

<div class="page-analyzer-container">
	<!-- Page URL Banner -->
	<div class="page-url-banner">
		<h4><i class="fas fa-globe"></i> Analyzing Page</h4>
		<a href="<?php echo $reportInfo['page_url']?>" target="_blank">
			<?php echo $reportInfo['page_url']?>
			<i class="fas fa-external-link-alt" style="margin-left: 8px; font-size: 12px;"></i>
		</a>
	</div>

	<!-- Tabs Header -->
	<div class="tabs-header">
		<button class="tab-btn active" onclick="document.querySelectorAll('.tab-content').forEach(function(t){t.classList.remove('active')});document.querySelectorAll('.tab-btn').forEach(function(b){b.classList.remove('active')});document.getElementById('tab-details').classList.add('active');this.classList.add('active');">
			<i class="fas fa-file-alt"></i>
			<?php echo $spTextSA['Page Details']?>
		</button>
		<button class="tab-btn" onclick="document.querySelectorAll('.tab-content').forEach(function(t){t.classList.remove('active')});document.querySelectorAll('.tab-btn').forEach(function(b){b.classList.remove('active')});document.getElementById('tab-links').classList.add('active');this.classList.add('active');">
			<i class="fas fa-link"></i>
			<?php echo $spTextSA['Page Links']?>
			<span class="badge-count"><?php echo $totalLinks?></span>
		</button>
	</div>

	<!-- Tab: Page Details -->
	<div id="tab-details" class="tab-content active">
		<!-- Performance Metrics -->
		<div class="detail-section">
			<div class="detail-section-title">
				<i class="fas fa-chart-line"></i> Performance Metrics
			</div>
			<div class="detail-grid">
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-star"></i> <?php echo $spText['label']['Score']?></div>
					<div class="detail-value">
						<div class="score-display">
							<div class="score-bars">
								<?php
								$scoreValue = abs($reportInfo['score']);
								$scoreClass = $reportInfo['score'] < 0 ? 'minus' : 'plus';
								for($b=0; $b < min($scoreValue, 15); $b++) echo "<span class='score-bar $scoreClass'></span>";
								?>
							</div>
							<span class="score-number <?php echo $reportInfo['score'] >= 0 ? 'positive' : 'negative'?>">
								<?php echo round($reportInfo['score'], 2)?>
							</span>
						</div>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-certificate"></i> <?php echo $_SESSION['text']['common']['Page Authority'] ?? 'Page Authority'; ?></div>
					<div class="detail-value">
						<span class="metric-value"><?php echo $reportInfo['page_authority'] ?: '0'?></span>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-link"></i> <?php echo $spTextHome['Backlinks']?></div>
					<div class="detail-value">
						<span class="metric-value"><?php echo number_format($reportInfo['google_backlinks'] ?: 0)?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- SEO Health -->
		<div class="detail-section">
			<div class="detail-section-title">
				<i class="fas fa-heartbeat"></i> SEO Health
			</div>
			<div class="detail-grid">
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-mobile-alt"></i> <?php echo $spTextSA['Mobile Friendly'] ?? 'Mobile Friendly'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['mobile_friendly']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Yes</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-times"></i> No</span>
						<?php } ?>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-lock"></i> <?php echo $spTextSA['HTTPS Secure'] ?? 'HTTPS Secure'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['https_secure']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Secure</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-times"></i> Not Secure</span>
						<?php } ?>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-robot"></i> <?php echo $spTextSA['AI Robot Compatibility'] ?? 'AI Robots'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['ai_robot_allowed']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Allowed</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-ban"></i> Blocked</span>
						<?php } ?>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-file-code"></i> <?php echo $spTextSA['Robots.txt Status'] ?? 'Robots.txt'?></div>
					<div class="detail-value">
						<?php if (!$reportInfo['blocked_by_robots']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Allowed</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-ban"></i> Blocked</span>
						<?php } ?>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fab fa-facebook"></i> <?php echo $spTextSA['Open Graph Tags'] ?? 'Open Graph'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['has_og_tags']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Found</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-times"></i> Missing</span>
						<?php } ?>
					</div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fab fa-twitter"></i> <?php echo $spTextSA['Twitter Cards'] ?? 'Twitter Cards'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['has_twitter_cards']) { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> Found</span>
						<?php } else { ?>
							<span class="status-badge danger"><i class="fas fa-times"></i> Missing</span>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Meta Tags -->
		<div class="detail-section">
			<div class="detail-section-title">
				<i class="fas fa-tags"></i> Meta Tags
			</div>
			<div class="detail-grid">
				<div class="detail-card full-width">
					<div class="detail-label"><i class="fas fa-heading"></i> <?php echo $spText['label']['Title']?></div>
					<div class="detail-value"><?php echo strip_tags($reportInfo['page_title']) ?: '<em style="color:#adb5bd">Not found</em>'?></div>
				</div>
				<div class="detail-card full-width">
					<div class="detail-label"><i class="fas fa-align-left"></i> <?php echo $spText['label']['Description']?></div>
					<div class="detail-value"><?php echo strip_tags($reportInfo['page_description']) ?: '<em style="color:#adb5bd">Not found</em>'?></div>
				</div>
				<div class="detail-card full-width">
					<div class="detail-label"><i class="fas fa-key"></i> <?php echo $spText['label']['Keywords']?></div>
					<div class="detail-value"><?php echo strip_tags($reportInfo['page_keywords']) ?: '<em style="color:#adb5bd">Not found</em>'?></div>
				</div>
				<?php if (!empty($reportInfo['canonical_url'])) { ?>
				<div class="detail-card full-width">
					<div class="detail-label"><i class="fas fa-link"></i> <?php echo $spTextSA['Canonical URL'] ?? 'Canonical URL'?></div>
					<div class="detail-value">
						<a href="<?php echo $reportInfo['canonical_url']?>" target="_blank"><?php echo $reportInfo['canonical_url']?></a>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<!-- Search Engine Indexing -->
		<div class="detail-section">
			<div class="detail-section-title">
				<i class="fas fa-search"></i> Search Engine Indexing
			</div>
			<div class="detail-grid">
				<div class="detail-card">
					<div class="detail-label"><i class="fab fa-google"></i> Google <?php echo $spTextHome['Indexed']?></div>
					<div class="detail-value"><?php echo showStatusBadge($reportInfo['google_indexed'], 'yesno')?></div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fab fa-microsoft"></i> Bing <?php echo $spTextHome['Indexed']?></div>
					<div class="detail-value"><?php echo showStatusBadge($reportInfo['bing_indexed'], 'yesno')?></div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-unlink"></i> <?php echo $spText['label']['Brocken'] ?? 'Broken'?></div>
					<div class="detail-value">
						<?php if ($reportInfo['brocken']) { ?>
							<span class="status-badge danger"><i class="fas fa-times"></i> Yes</span>
						<?php } else { ?>
							<span class="status-badge success"><i class="fas fa-check"></i> No</span>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Link Statistics -->
		<div class="detail-section">
			<div class="detail-section-title">
				<i class="fas fa-external-link-alt"></i> Links Overview
			</div>
			<div class="detail-grid">
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-list"></i> <?php echo $spTextSA['Total Links']?></div>
					<div class="detail-value"><span class="metric-value"><?php echo $reportInfo['total_links']?></span></div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-external-link-square-alt"></i> <?php echo $spTextSA['External Links']?></div>
					<div class="detail-value"><span class="metric-value"><?php echo $reportInfo['external_links']?></span></div>
				</div>
				<div class="detail-card">
					<div class="detail-label"><i class="fas fa-home"></i> Internal Links</div>
					<div class="detail-value"><span class="metric-value"><?php echo $reportInfo['total_links'] - $reportInfo['external_links']?></span></div>
				</div>
			</div>
		</div>

		<?php if (!empty($reportInfo['comments'])) {
		    $reportInfo['comments'] = preg_replace('/^(<br\s*\/?>\s*)+/i', '', $reportInfo['comments']);
		    $reportInfo['comments'] = str_ireplace("<br><br>", "<br>", $reportInfo['comments']);
            ?>
    		<div class="detail-section">
    			<div class="detail-section-title">
    				<i class="fas fa-comment-alt"></i> Analysis Comments
    			</div>
    			<div class="detail-grid">
    				<div class="detail-card full-width">
    					<div class="detail-value"><?php echo  $reportInfo['comments']?></div>
    				</div>
    			</div>
    		</div>
		<?php } ?>
	</div>

	<!-- Tab: Page Links -->
	<div id="tab-links" class="tab-content">
		<!-- Stats Cards -->
		<div class="links-stats-grid">
			<div class="stat-card internal">
				<div class="stat-number"><?php echo $internalCount?></div>
				<div class="stat-label">Internal</div>
			</div>
			<div class="stat-card external">
				<div class="stat-number"><?php echo $externalCount?></div>
				<div class="stat-label">External</div>
			</div>
			<div class="stat-card dofollow">
				<div class="stat-number"><?php echo $dofollowCount?></div>
				<div class="stat-label">Dofollow</div>
			</div>
			<div class="stat-card nofollow">
				<div class="stat-number"><?php echo $nofollowCount?></div>
				<div class="stat-label">Nofollow</div>
			</div>
		</div>

		<?php if($totalLinks > 0) { ?>
		<!-- Toolbar -->
		<div class="links-toolbar">
			<div class="search-box">
				<i class="fas fa-search"></i>
				<input type="text" id="linkSearchInput" placeholder="Search links, anchors, titles..." onkeyup="var f=this.value.toLowerCase();document.querySelectorAll('#linksTable tbody tr').forEach(function(r){r.style.display=r.textContent.toLowerCase().indexOf(f)>-1?'':'none'});">
			</div>
			<div class="filter-pills">
				<button class="filter-pill active" onclick="document.querySelectorAll('.filter-pill').forEach(function(b){b.classList.remove('active')});this.classList.add('active');document.querySelectorAll('#linksTable tbody tr').forEach(function(r){r.style.display=''});">All</button>
				<button class="filter-pill" onclick="document.querySelectorAll('.filter-pill').forEach(function(b){b.classList.remove('active')});this.classList.add('active');document.querySelectorAll('#linksTable tbody tr').forEach(function(r){r.style.display=r.dataset.type==='internal'?'':'none'});">Internal</button>
				<button class="filter-pill" onclick="document.querySelectorAll('.filter-pill').forEach(function(b){b.classList.remove('active')});this.classList.add('active');document.querySelectorAll('#linksTable tbody tr').forEach(function(r){r.style.display=r.dataset.type==='external'?'':'none'});">External</button>
				<button class="filter-pill" onclick="document.querySelectorAll('.filter-pill').forEach(function(b){b.classList.remove('active')});this.classList.add('active');document.querySelectorAll('#linksTable tbody tr').forEach(function(r){r.style.display=r.dataset.follow==='nofollow'?'':'none'});">Nofollow</button>
			</div>
		</div>

		<!-- Links Table -->
		<div class="links-table-container">
			<table class="links-table" id="linksTable">
				<thead>
					<tr>
						<th style="width: 50px;">#</th>
						<th><?php echo $spText['common']['Url']?></th>
						<th><?php echo $spTextSA['Anchor']?></th>
						<th><?php echo $spTextSA['Link Title']?></th>
						<th style="width: 100px;">Type</th>
						<th style="width: 100px;">Follow</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($linkList as $i => $listInfo) {
						$isExternal = !empty($listInfo['extrenal']);
						$isNofollow = !empty($listInfo['nofollow']);
					?>
					<tr class="link-row" data-type="<?php echo $isExternal ? 'external' : 'internal'?>" data-follow="<?php echo $isNofollow ? 'nofollow' : 'dofollow'?>">
						<td><span class="row-number"><?php echo $i+1?></span></td>
						<td class="link-cell">
							<a href="<?php echo htmlspecialchars($listInfo['link_url'])?>" target="_blank" class="link-url" title="<?php echo htmlspecialchars($listInfo['link_url'])?>">
								<?php echo htmlspecialchars($listInfo['link_url'])?>
							</a>
						</td>
						<td>
							<span class="link-anchor" title="<?php echo htmlspecialchars($listInfo['link_anchor'])?>"><?php echo htmlspecialchars($listInfo['link_anchor']) ?: '-'?></span>
						</td>
						<td>
							<span class="link-title" title="<?php echo htmlspecialchars($listInfo['link_title'])?>"><?php echo htmlspecialchars($listInfo['link_title']) ?: '-'?></span>
						</td>
						<td>
							<span class="type-badge <?php echo $isExternal ? 'external' : 'internal'?>">
								<i class="fas <?php echo $isExternal ? 'fa-external-link-alt' : 'fa-home'?>"></i>
								<?php echo $isExternal ? 'Ext' : 'Int'?>
							</span>
						</td>
						<td>
							<span class="type-badge <?php echo $isNofollow ? 'nofollow' : 'dofollow'?>">
								<?php echo $isNofollow ? 'NF' : 'DF'?>
							</span>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php } else { ?>
		<div class="empty-state">
			<i class="fas fa-link"></i>
			<p>No links found on this page</p>
		</div>
		<?php } ?>
	</div>
</div>

