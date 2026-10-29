<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Backlinks Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#blReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="blReportsHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('blReportsHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-link"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Backlinks Reports</div>
		<div class="aiv-intro-desc">Historical table of saved backlink counts for a website, with change indicators over time. <a href="javascript:void(0);" data-toggle="modal" data-target="#blReportsHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="blReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="blReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-link"></i></div>
				<h5 class="modal-title" id="blReportsHelpModalLabel">About Backlinks Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Shows saved backlink counts (external pages to this page, and to the root domain) for one of your websites over a date range, with change indicators versus the previous entry.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Reads only from already-saved history - it doesn't call Moz live. A website needs saved history first, from Generate Backlinks Reports.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" class="custom-select" onchange="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=reports')">
				<?php foreach($websiteList as $websiteInfo){?>
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
			<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script type="text/javascript">
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
		</td>
		<td style="text-align: center;"><a href="javascript:void(0);" onclick="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=reports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a></td>
	</tr>
</table>
</form>

<?php
	if(empty($websiteId)){
		?>
		<div class="alert alert-danger">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Records Found']?>!
		</div>
		<?php
		exit;
	}
?>

<div id='subcontent'>
<?php if (!empty($hasBrokenBacklinks)) { ?>
<div class="alert alert-secondary">
	<i class="fas fa-info-circle me-2"></i>
	<?php echo $spTextBack['backlinkdfsnotice'] ?? 'Rows measured via DataForSEO show total backlinks and referring domains (not the same page-count metric Moz used) plus a broken-backlinks count. Rows measured via Moz are unaffected.' ?>
</div>
<?php } ?>
<table width="100%" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Date']?></td>
		<td><?php echo $spTextBack['Backlink Count']?></td>
		<td<?php echo empty($hasBrokenBacklinks) ? ' class="right"' : '' ?>><?php echo $spTextBack['Domain Backlink Count']?></td>
		<?php if (!empty($hasBrokenBacklinks)) { ?>
		<td class="right"><?php echo $spTextBack['Broken Backlinks'] ?? 'Broken Backlinks' ?></td>
		<?php } ?>
	</tr>
	<?php
	$colCount = !empty($hasBrokenBacklinks) ? 4 : 3;
	if(count($list) > 0){
		$catCount = count($list);
		$i = 0;
		foreach($list as $listInfo) {
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo $listInfo['result_date']; ?></td>
				<td class='td_br_right' style='text-align:left;padding-left:40px;'><?php echo $listInfo['external_pages_to_page'].' '. $listInfo['rank_diff_external_pages_to_page']?></td>
				<td class='<?php echo empty($hasBrokenBacklinks) ? $rightBotClass : 'td_br_right' ?>' style='text-align:left;padding-left:40px;'><?php echo $listInfo['external_pages_to_root_domain'].' '. $listInfo['rank_diff_external_pages_to_root_domain']?></td>
				<?php if (!empty($hasBrokenBacklinks)) { ?>
				<td class='<?php echo $rightBotClass?>' style='text-align:left;padding-left:40px;'><?php echo ($listInfo['broken_backlinks'] === null) ? '—' : $listInfo['broken_backlinks']?></td>
				<?php } ?>
			</tr>
			<?php
			$i++;
		}
	}else{
		echo showNoRecordsList($colCount-2);
	}
	?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
</table>

<?php if (!empty($localAiAvailable) && count($list) > 0) { ?>
	<div class="mt-2">
		<button type="button" class="btn btn-outline-secondary btn-sm" onclick="backlinkSummarizeTrend()">
			<i class="fa fa-magic"></i> Summarize with AI
		</button>
		<div id="backlinkTrendSummary" class="alert alert-info mt-2" style="display:none;"></div>
	</div>
	<script type="text/javascript">
	function backlinkSummarizeTrend() {
		var box = document.getElementById('backlinkTrendSummary');
		box.style.display = 'block';
		box.innerText = 'Generating...';
		$.ajax({
			url: 'backlinks.php',
			data: {
				sec: 'summarizetrend',
				website_id: <?php echo intval($websiteId)?>,
				from_time: <?php echo json_encode($fromTime)?>,
				to_time: <?php echo json_encode($toTime)?>
			},
			dataType: 'json',
			success: function(data) {
				box.innerText = (data && data.ok) ? data.summary : ((data && data.error) ? data.error : 'Could not generate a summary.');
			},
			error: function() {
				box.innerText = 'Could not generate a summary.';
			}
		});
	}
	</script>
<?php } ?>
</div>