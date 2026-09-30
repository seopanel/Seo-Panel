<?php echo showSectionHead($spTextSat['Search Engine Saturation Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" class="custom-select" onchange="scriptDoLoadPost('saturationchecker.php', 'search_form', 'content', '&sec=reports')">
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
		<td style="text-align: center;"><a href="javascript:void(0);" onclick="scriptDoLoadPost('saturationchecker.php', 'search_form', 'content', '&sec=reports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a></td>
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
<table width="100%" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Date']?></td>
		<td>Google</td>
		<td class="right">Bing</td>
	</tr>
	<?php
	$colCount = 3; 
	if(count($list) > 0){
		$catCount = count($list);
		$i = 0;
		foreach($list as $listInfo){
			
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
				<td class='td_br_right' style='text-align:left;padding-left:40px;'><a href="<?php echo htmlspecialchars($directLinkList['google'])?>" target="_blank"><?php echo intval($listInfo['google'])?></a> <?php echo $listInfo['rank_diff_google']?></td>
				<td class='<?php echo $rightBotClass?>' style='text-align:left;padding-left:40px;'><a href="<?php echo htmlspecialchars($directLinkList['msn'])?>" target="_blank"><?php echo intval($listInfo['msn'])?></a> <?php echo $listInfo['rank_diff_msn']?></td>
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
		<button type="button" class="btn btn-outline-secondary btn-sm" onclick="saturationSummarizeTrend()">
			<i class="fa fa-magic"></i> Summarize with AI
		</button>
		<div id="saturationTrendSummary" class="alert alert-info mt-2" style="display:none;"></div>
	</div>
	<script type="text/javascript">
	function saturationSummarizeTrend() {
		var box = document.getElementById('saturationTrendSummary');
		box.style.display = 'block';
		box.innerText = 'Generating...';
		$.ajax({
			url: 'saturationchecker.php',
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