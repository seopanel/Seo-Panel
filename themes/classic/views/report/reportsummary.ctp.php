<?php

$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";

if(!empty($printVersion) || !empty($pdfVersion)) {
    $pdfVersion ? showPdfHeader($spTextTools['Keyword Position Summary']) : showPrintHeader($spTextTools['Keyword Position Summary']);
    ?>
    <table class="search">
    	<?php if (!empty($websiteUrl)) {?>
    		<tr>
    			<th><?php echo $spText['common']['Website']?>:</th>
        		<td>
        			<?php echo htmlspecialchars($websiteUrl); ?>
    			</td>
    		</tr>
		<?php }?>
		<tr>
			<th><?php echo $spText['common']['Period']?>:</th>
    		<td>
    			<?php echo $fromTime?> - <?php echo $toTime?>
			</td>
		</tr>
	</table>
    <?php
} else {
    include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
    ?>
    <div class="aiv-page-head">
        <?php echo showSectionHead($spTextTools['Keyword Position Summary']); ?>
        <a href="javascript:void(0);" data-toggle="modal" data-target="#kwSummaryHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
    </div>

    <!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
    <div class="modal fade aiv-help-modal" id="kwSummaryHelpModal" tabindex="-1" role="dialog" aria-labelledby="kwSummaryHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="aiv-help-modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="aiv-help-modal-icon"><i class="fas fa-list-alt"></i></div>
                    <h5 class="modal-title" id="kwSummaryHelpModalLabel">About Keyword Position Summary</h5>
                </div>
                <div class="modal-body aiv-help-modal-body">
                    <p>A sortable table comparing every active keyword's rank on a "from" and a "to" date, across all your websites or one chosen website, with the change between them. Pick Day, Week, Month or Year for a quick comparison, or set your own two dates.</p>
                    <div class="aiv-note">
                        <i class="fas fa-info-circle"></i>
                        <span>With no filter applied yet, this shows the earliest and latest dates that actually have saved data, not today's date, and a Day/Week/Month/Year pick is anchored to that latest saved date too. A keyword with no result on a given date shows as rank 1000 (not found), not blank. You can export the same list as CSV or PDF instead of viewing it on screen.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
                </div>
            </div>
        </div>
    </div>
	<form id='search_form'>
	<?php $submitLink = "scriptDoLoadPost('reports.php', 'search_form', 'content', '&sec=reportsum')";?>
	<table class="search">
		<tr>
			<th><?php echo $spText['common']['Name']?>: </th>
			<td>
				<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
			</td>
			<th class="pl-4"><?php echo $spText['common']['Website']?>: </th>
			<td>
				<select name="website_id" id="website_id" onchange="<?php echo $submitLink?>" class="custom-select">
					<option value="">-- <?php echo $spText['common']['All']?> --</option>
					<?php foreach($websiteList as $websiteInfo){?>
						<?php if($websiteInfo['id'] == $websiteId){?>
							<option value="<?php echo $websiteInfo['id']?>" selected><?php echo htmlspecialchars($websiteInfo['name'])?></option>
						<?php }else{?>
							<option value="<?php echo $websiteInfo['id']?>"><?php echo htmlspecialchars($websiteInfo['name'])?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
			<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
			<td>
				<select name="period" id="period_preset" class="custom-select" style="display:inline-block; width:auto; margin-bottom:4px;" onchange="<?php echo $submitLink?>">
					<option value="" <?php echo empty($period) ? 'selected' : ''?>>-- <?php echo $spText['label']['Custom'] ?? 'Custom'?> --</option>
					<option value="day" <?php echo $period == 'day' ? 'selected' : ''?>><?php echo $spText['label']['Day']?></option>
					<option value="week" <?php echo $period == 'week' ? 'selected' : ''?>><?php echo $spText['label']['Week']?></option>
					<option value="month" <?php echo $period == 'month' ? 'selected' : ''?>><?php echo $spText['label']['Month']?></option>
					<option value="year" <?php echo $period == 'year' ? 'selected' : ''?>><?php echo $spText['label']['Year']?></option>
				</select><br>
    			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
    			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
				<script>
				  $( function() {
				    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
				    // picking a date directly means "custom range", not
				    // whichever preset was last selected - without this,
				    // the still-submitted period value would silently
				    // override the manually-picked date right back
				    $( "#from_time, #to_time").on("change", function() {
				        $("#period_preset").val("");
				    });
				  } );
			  	</script>
			</td>
			<td style="text-align: center;">
			  	<a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
			</td>
		</tr>

	</table>
	</form>
	<?php
	if(empty($list)){
		?>
		<div class="alert alert-info">
			<i class="fas fa-info-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>.
			<a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=new&amp;website_id=')" class="alert-link"><?php echo $spText['label']['Click Here']?></a> <?php echo $spTextKeyword['to create new keywords']?>.
		</div>
		<?php
		exit;
	}

	// url parameters
	$mainLink = SP_WEBPATH."/reports.php?sec=reportsum&website_id=$websiteId&from_time=$fromTime&to_time=$toTime&search_name=" . $searchInfo['search_name'];
	$directLink = $mainLink . "&order_col=$orderCol&order_val=$orderVal&pageno=$pageNo";
	
	// export links
	$pdfLink = "$directLink&doc_type=pdf";
	$csvLink = "$directLink&doc_type=export";
	$printLink = "$directLink&doc_type=print";
	showExportDiv($pdfLink, $csvLink, $printLink);
	?>
	<?php echo $pagingDiv?>
<?php }?>

<div id='subcontent' class="table-responsive">

<table id="cust_tab">
	<tr>
		<?php
		$linkClass = "";
        if ($orderCol == 'keyword') {
            $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
            $linkClass .= "sort_".strtolower($oVal);
        } else {
            $oVal = 'ASC';
        }
        
        $hrefAttr = $pdfVersion ? "" : "href='javascript:void(0)'";
        
		$linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col=keyword&order_val=$oVal', 'content')\">{$spText['common']['Keyword']}</a>"; 
		?>		
		<?php if (empty($websiteId)) {?>
			<th id="head" rowspan="2"><?php echo $linkName?></th>
			<th id="head" rowspan="2"><?php echo $spText['common']['Website']?></th>
		<?php } else { ?>
			<th id="head" rowspan="2"><?php echo $linkName?></th>
		<?php }?>
		<?php
		$seCount = count($seList);
		foreach ($seList as $i => $seInfo){
		    $linkClass = "";
            if ($seInfo['id'] == $orderCol) {
                $oVal = ($orderVal == 'DESC') ? "ASC" : "DESC";
                $linkClass .= "sort_".strtolower($oVal);
            } else {
                $oVal = 'ASC';
            }
            $linkName = "<a id='sortLink' class='$linkClass' $hrefAttr onclick=\"scriptDoLoad('$mainLink&order_col={$seInfo['id']}&order_val=$oVal', 'content')\">{$seInfo['domain']}</a>";
            ?>
			<th id="head" colspan="3"><?php echo $linkName; ?></th>
			<?php
		}
		if (!empty($showAiOverview)): ?>
		<th id="head" rowspan="2" class="text-center"><?php echo $spTextKeyword['AI Overview'] ?? 'AI Overview'?><br><small style="font-weight:normal;font-size:0.75em;">Google</small></th>
		<?php endif; ?>
		<?php if (!empty($showSearchVolume)): ?>
		<th id="head" rowspan="2" class="text-center"><?php echo $spTextKeyword['Search Volume']?><br><small style="font-weight:normal;font-size:0.75em;">Google</small></th>
		<?php endif; ?>
	</tr>
	<tr>
		<?php
		$pTxt = str_replace("-", "/", substr($fromTime, -5));
		$cTxt = str_replace("-", "/", substr($toTime, -5));
		foreach ($seList as $i => $seInfo) {
			?>
			<th><?php echo $pTxt; ?></th>
			<th><?php echo $cTxt; ?></th>
			<th>+ / -</th>
			<?php
		}
		?>
	</tr>
	<?php
	$svExtra = !empty($showSearchVolume) ? 1 : 0;
	$aioExtra = !empty($showAiOverview) ? 1 : 0;
	$colCount = empty($websiteId) ? ($seCount * 3) + 2 + $svExtra + $aioExtra : ($seCount * 3) + 1 + $svExtra + $aioExtra;
	if (count($list) > 0) {
		foreach($indexList as $keywordId => $rankValue){
		    $listInfo = $list[$keywordId];
			$positionInfo = $listInfo['position_info'];
            $rangeFromTime = date('Y-m-d', strtotime('-14 days', strtotime($fromTime)));
            $scriptLink = "website_id=$websiteId&keyword_id={$listInfo['id']}&rep=1&from_time=$rangeFromTime&to_time=$toTime";
			?>
			<tr>
				<?php
				$serpKwIcon = '';
				if (empty($pdfVersion) && empty($printVersion)) {
					$serpKwUrl = SP_WEBPATH . "/reports.php?sec=serpresults&keyword_id={$listInfo['id']}&date=$toTime";
					$serpKwIcon = ' <a href="javascript:void(0);" onclick="openSerpModalSP(\'' . addslashes($serpKwUrl) . '\')" title="View SERP Results"><i class="fas fa-list-ol" style="color:#d35400; font-size:0.75rem;"></i></a>';
				}

				// Google only, same as the Search Volume column - Bing/Yahoo can't actually
				// return AI Overview data via the current integration at all right now
				// (DataForSEO rejects the request field for them), so showing their
				// permanently-"Not available" rows here would just be noise.
				$aioGoogleInfo = null;
				foreach ($listInfo['aio_info'] as $aioInfo) {
					if (stristr($aioInfo['se_domain'], 'google')) {
						$aioGoogleInfo = $aioInfo;
						break;
					}
				}
				$aioColumnLines = '&mdash;';
				if ($aioGoogleInfo !== null && !empty($aioGoogleInfo['aio_supported'])) {
					if (!empty($aioGoogleInfo['aio_cited'])) {
						$aioColor = '#2e7d32';
						$aioLabel = 'Cited' . (!empty($aioGoogleInfo['aio_cited_position']) ? ' (#' . intval($aioGoogleInfo['aio_cited_position']) . ')' : '');
						$aioTitle = 'AI Overview: present, cited' . (!empty($aioGoogleInfo['aio_cited_position']) ? ' (#' . intval($aioGoogleInfo['aio_cited_position']) . ')' : '');
					} elseif (!empty($aioGoogleInfo['aio_present'])) {
						$aioColor = '#b8790a';
						$aioLabel = 'Not cited';
						$aioTitle = 'AI Overview: present, not cited';
					} else {
						$aioColor = '#9296a8';
						$aioLabel = 'Not present';
						$aioTitle = 'AI Overview: not present';
					}
					if (empty($pdfVersion) && empty($printVersion)) {
						$aioKwUrl = SP_WEBPATH . "/reports.php?sec=aiosources&keyword_id={$listInfo['id']}";
						$aioColumnLines = '<a href="javascript:void(0);" onclick="openAjaxModalSP(\'' . addslashes($aioKwUrl) . '\', \'<i class=&quot;fas fa-robot&quot;></i> AI Overview Cited Sources\')" title="' . htmlspecialchars($aioTitle) . '" style="color:' . $aioColor . '; font-weight:500; text-decoration:none;">' . htmlspecialchars($aioLabel) . '</a>';
					} else {
						$aioColumnLines = '<span style="color:' . $aioColor . ';">' . htmlspecialchars($aioLabel) . '</span>';
					}
				}
				?>
				<?php if (empty($websiteId)) {?>
					<td><?php echo htmlspecialchars($listInfo['name']) . $serpKwIcon?></td>
					<td><?php echo htmlspecialchars($listInfo['webname']); ?></td>
				<?php } else { ?>
					<td><?php echo htmlspecialchars($listInfo['name']) . $serpKwIcon?></td>
				<?php }?>
				<?php
				foreach ($seList as $index => $seInfo){
					$rankInfo = $positionInfo[$seInfo['id']];
					$prevRank = isset($rankInfo[$fromTime]) ? $rankInfo[$fromTime] : "";
					$currRank = isset($rankInfo[$toTime]) ? $rankInfo[$toTime] : "";
					$rankDiffTxt = "";

					// if both ranks are existing
					if ($prevRank != '' && $currRank != '') {
						$rankDiff = $prevRank - $currRank;

						if ($rankDiff > 0) {
							$rankDiffTxt = "<font class='green'>($rankDiff)</font>";
						} else if ($rankDiff < 0) {
							$rankDiffTxt = "<font class='red'>($rankDiff)</font>";
						} else {
							$rankDiffTxt = "0";
						}
					}

					$prevRankLink = scriptAJAXLinkHrefDialog('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $prevRank);
					$currRankLink = scriptAJAXLinkHrefDialog('reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], $currRank);
					$graphLink = scriptAJAXLinkHrefDialog('graphical-reports.php', 'content', $scriptLink."&se_id=".$seInfo['id'], '&nbsp;', 'graphicon');

					// if pdf report remove links
					if ($pdfVersion) {
						$prevRankLink = str_replace("href='javascript:void(0);'", "", $prevRankLink);
						$currRankLink = str_replace("href='javascript:void(0);'", "", $currRankLink);
						$graphLink = str_replace("href='javascript:void(0);'", "", $graphLink);
					}
				    ?>
					<td><?php echo $prevRankLink; ?></td>
					<td><?php echo $currRankLink; ?></td>
					<td><?php echo $graphLink . " " . $rankDiffTxt; ?></td>
					<?php
				}
				?>
				<?php if (!empty($showAiOverview)): ?>
				<td class="text-center"><?php echo $aioColumnLines?></td>
				<?php endif; ?>
				<?php if (!empty($showSearchVolume)): ?>
				<td class="text-center">
					<?php if (!is_null($listInfo['search_volume'])): ?>
						<strong><?php echo number_format($listInfo['search_volume'])?></strong>
					<?php elseif (!empty($listInfo['sv_status'])): ?>
						<span class="badge badge-secondary"><?php echo htmlspecialchars($listInfo['sv_status'])?></span>
					<?php else: ?>
						&mdash;
					<?php endif; ?>
				</td>
				<?php endif; ?>
			</tr>
			<?php
		}
	}
	?>
</table>
</div>
<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
}
?>

<?php if (empty($printVersion) && empty($pdfVersion)): ?>
<div class="modal fade" id="serpModalSP" tabindex="-1" role="dialog" aria-labelledby="serpModalSPLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="serpModalSPLabel"><i class="fas fa-list-ol"></i> SERP Results</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="serpModalSPBody">
				<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>