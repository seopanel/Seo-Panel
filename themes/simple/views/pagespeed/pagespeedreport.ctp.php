<?php
echo showSectionHead($spTextTools['PageSpeed Reports']);
$webUrl = "";
?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" class="custom-select" onchange="scriptDoLoadPost('pagespeed.php', 'search_form', 'content', '&sec=reports')">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php
					if($websiteInfo['id'] == $websiteId){
						$webUrl = $websiteInfo['url'];
						?>
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
		<td style="text-align: center;"><a href="javascript:void(0);" onclick="scriptDoLoadPost('pagespeed.php', 'search_form', 'content', '&sec=reports')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a></td>
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
		<td><?php echo $spText['common']['Date']?></td>
		<td><?php echo $spTextPS['Desktop Speed']?></td>
		<td><?php echo $spTextPS['Mobile Speed']?></td>
		<td><?php echo $spText['common']['Details']?></td>
	</tr>
	<?php
	$colCount = 4; 
	if(count($list) > 0){
		foreach($list as $listInfo){ 
			?>
			<tr>
				<td><?php echo $listInfo['result_date']; ?></td>
				<td><a><?php echo $listInfo['desktop_speed_score'].'</a> '. $listInfo['rank_diff_desktop_speed_score']?></td>
				<td><a><?php echo $listInfo['mobile_speed_score'].'</a> '. $listInfo['rank_diff_mobile_speed_score']?></td>
				<td>
					<a href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo $webUrl; ?>" target="_blank">
						<?php echo $spText['common']['Details']?> &gt;&gt;
					</a>
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