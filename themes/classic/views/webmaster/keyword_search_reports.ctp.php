<?php echo showSectionHead($spTextTools['Keyword Search Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'webmaster-tools.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
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
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area">
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('webmaster-tools.php', 'search_form', 'content', '&sec=viewKeywordSearchReports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php
if(empty($keywordId)){
	?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>!
	</div>
	<?php
	exit;
}
?>
<br>
<div id='subcontent'>
<table id="cust_tab">	
	<tr>
		<th><?php echo $spText['common']['Date']?></th>
		<th><?php echo $spText['label']['Clicks']?></th>
		<th><?php echo $spText['label']['Impressions']?></th>
		<th><?php echo "CTR"?></th>
		<th><?php echo $spTextWB['Average Position']?></th>
	</tr>
	<?php
	$colCount = 5;
	if (count($list) > 0) {
    	foreach($list as $listInfo) {            
    		?>
    		<tr>
    			<td><?php echo $listInfo['report_date']; ?></td>
    			<td><b><?php echo $listInfo['clicks'].'</b> '. $listInfo['rank_diff_clicks']?></td>
    			<td><b><?php echo $listInfo['impressions'].'</b> '. $listInfo['rank_diff_impressions']?></td>
    			<td><b><?php echo $listInfo['ctr'].'</b> '. $listInfo['rank_diff_ctr']?></td>
    			<td><b><?php echo $listInfo['average_position'].'</b> '. $listInfo['rank_diff_average_position']?></td>
    		</tr>
    		<?php	
    	}
	} else {
	    echo showNoRecordsList($colCount - 2);
	}
	?>
</table>
</div>