<?php echo showSectionHead($spTextTools['Graphical Reports']); ?>
<form id='search_form'>
<table class="search" style="width: 80%">
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
		<td colspan="2">
			<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script type="text/javascript">
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
			</script>
		</td>
	</tr>
	<tr>
		<th class="pl-4"><?php echo $spText['common']['Url']?>: </th>
		<td id="link_area">
			<?php echo $this->render('socialmedia/social_media_link_select_box', 'ajax'); ?>
		</td>
		<th><?php echo $spText['label']['Report Type']?>: </th>
		<td>
			<select name="attr_type" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($colList as $key => $label){?>
					<?php if($key == $searchInfo['attr_type']){?>
						<option value="<?php echo $key?>" selected><?php echo $label?></option>
					<?php }else{?>
						<option value="<?php echo $key?>"><?php echo $label?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('<?php echo $pageScriptPath ?>', 'search_form', 'content', '&sec=viewGraphReports')" class="btn btn-secondary">
				<?php echo $spText['button']['Show Records']?>
			</a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<?php echo $graphContent; ?>
</div>