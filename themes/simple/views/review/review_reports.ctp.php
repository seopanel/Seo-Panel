<?php
echo showSectionHead($spTextTools['Detailed Reports']);
$submitLink = "scriptDoLoadPost('$pageScriptPath', 'search_form', 'content', '&sec=viewDetailedReports')";
?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', '<?php echo $pageScriptPath ?>', 'link_area', 'sec=linkSelectBox')" class="custom-select">
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
		<th class="pl-4"><?php echo $spText['common']['Name']?>: </th>
		<td id="link_area">
			<?php
			$this->data['onChange'] = $submitLink;
			echo $this->render('review/review_link_select_box', 'ajax');
			?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>


<div id='subcontent'>
<table id="cust_tab">	
	<tr>
		<th><?php echo $spText['common']['Date']?></th>
		<th><?php echo $spText['label']['Reviews']?></th>
		<th><?php echo $spText['label']['Rating']?></th>
	</tr>
	<?php
	if (count($list) > 0) {
    	foreach($list as $listInfo){            
    		?>
    		<tr>
    			<td><?php echo $listInfo['report_date']; ?></td>
    			<td><b><?php echo $listInfo['reviews'].'</b> '. $listInfo['rank_diff_reviews']?></td>
    			<td><b><?php echo $listInfo['rating'].'</b> '. $listInfo['rank_diff_rating']?></td>
    		</tr>
    		<?php	
    	}
	} else {
	    ?>
	    <tr><td colspan="3"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
	    <?php
	}
	?>
</table>
</div>