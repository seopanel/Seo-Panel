<?php
$backLink = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport&pageno={$post['pageno']}&order_col={$post['order_col']}&order_val={$post['order_val']}')";
?>
<style>
.page-details-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	overflow: hidden;
}
.page-details-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 5px 25px;
	font-size: 18px;
	font-weight: 600;
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.page-details-body {
	padding: 25px;
}
.detail-section {
	background: #f8f9fa;
	border-radius: 6px;
	padding: 20px;
	margin-bottom: 15px;
}
.detail-section-title {
	color: #495057;
	font-size: 14px;
	font-weight: 600;
	margin-bottom: 15px;
	padding-bottom: 10px;
	border-bottom: 2px solid #dee2e6;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.detail-row {
	display: flex;
	flex-wrap: wrap;
	margin: -8px;
}
.detail-item {
	flex: 0 0 50%;
	padding: 8px;
	min-width: 300px;
}
.detail-item-full {
	flex: 0 0 100%;
	padding: 8px;
}
.detail-item-third {
	flex: 0 0 33.333%;
	padding: 8px;
	min-width: 250px;
}
.detail-item-inner {
	background: white;
	border-radius: 4px;
	padding: 15px;
	border-left: 3px solid #667eea;
	min-height: 80px;
}
.detail-label {
	color: #6c757d;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	margin-bottom: 8px;
	display: block;
}
.detail-value {
	color: #212529;
	font-size: 14px;
	line-height: 1.5;
	display: block;
	word-break: break-word;
}
.detail-value a {
	color: #0066cc;
	text-decoration: none;
}
.detail-value a:hover {
	text-decoration: underline;
}
.score-bar {
	display: inline-block;
	height: 8px;
	width: 8px;
	border-radius: 2px;
	margin-right: 2px;
}
.score-bar.plus {
	background: #28a745;
}
.score-bar.minus {
	background: #dc3545;
}
.back-button {
	margin-bottom: 15px;
}
</style>

<div class="back-button">
	<a href="javascript:void(0)" onclick="<?php echo $backLink?>" class="btn btn-info">
		<i class="fas fa-arrow-left"></i> Back
	</a>
</div>

<div class="page-details-card">
	<div class="page-details-header">
		<span><i class="fas fa-file-alt"></i> <?php echo $spTextSA['Page Details']?></span>
	</div>
	<div class="page-details-body">
		<!-- Page URL Section -->
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-link"></i> Page Information</div>
			<div class="detail-row">
				<div class="detail-item-full">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-globe"></i> <?php echo $spTextSA['Page Link']?></span>
						<span class="detail-value">
							<a href="<?php echo $reportInfo['page_url']?>" target="_blank"><?php echo $reportInfo['page_url']?></a>
						</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Score and Authority Section -->
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-chart-line"></i> Performance Metrics</div>
			<div class="detail-row">
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-star"></i> <?php echo $spText['label']['Score']?></span>
						<span class="detail-value">
							<?php
					        if ($reportInfo['score'] < 0) {
					            $scoreClass = 'minus';
					            $scoreValue = $reportInfo['score'] * -1;
					        } else {
					            $scoreClass = 'plus';
					            $scoreValue = $reportInfo['score'];
					        }
					        for($b=0;$b<=$scoreValue;$b++) echo "<span class='score-bar $scoreClass'></span>";
					        ?>
							<strong><?php echo $reportInfo['score']?></strong>
						</span>
					</div>
				</div>
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-certificate"></i> <?php echo $_SESSION['text']['common']['Page Authority']; ?></span>
						<span class="detail-value"><strong><?php echo $reportInfo['page_authority']?></strong></span>
					</div>
				</div>
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-link"></i> <?php echo $spTextHome['Backlinks']?></span>
						<span class="detail-value"><strong><?php echo $reportInfo['google_backlinks']?></strong></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Meta Tags Section -->
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-tags"></i> Meta Tags</div>
			<div class="detail-row">
				<div class="detail-item-full">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-heading"></i> <?php echo $spText['label']['Title']?></span>
						<span class="detail-value"><?php echo strip_tags($reportInfo['page_title'])?></span>
					</div>
				</div>
				<div class="detail-item-full">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-align-left"></i> <?php echo $spText['label']['Description']?></span>
						<span class="detail-value"><?php echo strip_tags($reportInfo['page_description'])?></span>
					</div>
				</div>
				<div class="detail-item-full">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-key"></i> <?php echo $spText['label']['Keywords']?></span>
						<span class="detail-value"><?php echo strip_tags($reportInfo['page_keywords'])?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Indexing Status Section -->
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-search"></i> Search Engine Indexing</div>
			<div class="detail-row">
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fab fa-google"></i> Google <?php echo $spTextHome['Indexed']?></span>
						<span class="detail-value"><?php echo showStatusBadge($reportInfo['google_indexed'], 'yesno')?></span>
					</div>
				</div>
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fab fa-microsoft"></i> Bing <?php echo $spTextHome['Indexed']?></span>
						<span class="detail-value"><?php echo showStatusBadge($reportInfo['bing_indexed'], 'yesno')?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Links Statistics Section -->
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-external-link-alt"></i> Links Statistics</div>
			<div class="detail-row">
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-list"></i> <?php echo $spTextSA['Total Links']?></span>
						<span class="detail-value"><strong><?php echo $reportInfo['total_links']?></strong></span>
					</div>
				</div>
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-external-link-square-alt"></i> <?php echo $spTextSA['External Links']?></span>
						<span class="detail-value"><strong><?php echo $reportInfo['external_links']?></strong></span>
					</div>
				</div>
				<div class="detail-item-third">
					<div class="detail-item-inner">
						<span class="detail-label"><i class="fas fa-unlink"></i> <?php echo $spText['label']['Brocken']?></span>
						<span class="detail-value"><?php
						    $badgeClass = $reportInfo['brocken'] ? 'badge bg-danger' : 'badge bg-success';
						    $badgeText = $reportInfo['brocken'] ? $spText['common']['Yes'] : $spText['common']['No'];
						    echo "<span class='$badgeClass py-2 px-3 text-light'>$badgeText</span>";
						?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Comments Section -->
		<?php if (!empty($reportInfo['comments'])) { ?>
		<div class="detail-section">
			<div class="detail-section-title"><i class="fas fa-comment-alt"></i> Analysis Comments</div>
			<div class="detail-row">
				<div class="detail-item-full">
					<div class="detail-item-inner">
						<span class="detail-value"><?php echo $reportInfo['comments']?></span>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<!-- Page Links Section -->
<div class="page-details-card">
	<div class="page-details-header">
		<span><i class="fas fa-link"></i> <?php echo $spTextSA['Page Links']?></span>
	</div>
	<div class="page-details-body" style="padding: 0;">
		<table class="list">
			<tr class="listHead">
				<td class="leftid">#</td>
				<td><?php echo $spText['common']['Url']?></td>
				<td><?php echo $spTextSA['Anchor']?></td>
				<td><?php echo $spTextSA['Link Title']?></td>
				<td><?php echo $spTextSA['Nofollow']?></td>
				<td class="right"><?php echo $spTextSA['External']?></td>
			</tr>
			<?php
			$colCount = 6;
			if(count($linkList) > 0) {
				foreach($linkList as $i => $listInfo) {
					?>
					<tr class="<?php echo $class?>">
						<td class="<?php echo $leftBotClass?>"><?php echo $i+1?></td>
						<td class="td_br_right left">
							<a href="<?php echo $listInfo['link_url']?>" target="_blank"><?php echo $listInfo['link_url']?></a>
						</td>
						<td class="td_br_right left"><?php echo $listInfo['link_anchor']?></td>
						<td class="td_br_right left"><?php echo $listInfo['link_title']?></td>
						<td class="td_br_right">
							<?php echo $listInfo['nofollow'] ? $spText['common']['Yes'] : $spText['common']['No']; ?>
						</td>
						<td class="<?php echo $rightBotClass?>">
							<?php echo $listInfo['extrenal'] ? $spText['common']['Yes'] : $spText['common']['No']; ?>
						</td>
					</tr>
					<?php
				}
			}else{
				echo showNoRecordsList($colCount-2);
			}
			?>
		</table>
	</div>
</div>
