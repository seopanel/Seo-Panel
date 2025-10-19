<?php
echo showSectionHead($spTextTools['Graphical Position Reports']);
$submitAction = "scriptDoLoadPost('rank.php', 'search_form', 'content')";
?>
<form id='search_form'>
<input type="hidden" name="sec" value="graphical-reports">
<input type="hidden" name="search_engine" value="moz">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="<?php echo $submitAction;?>">
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
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction;?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<?php echo $graphContent; ?>
</div>