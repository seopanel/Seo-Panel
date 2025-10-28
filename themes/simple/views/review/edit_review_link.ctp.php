<?php
$headText = ($editAction == 'updateReviewLink') ? $spTextRM['Edit Review Link'] : $spTextRM['New Review Link'];
echo showSectionHead($headText);

// if error occured
if(!empty($validationMsg)){
	?>
	<div class="alert alert-danger">
		<i class="fas fa-exclamation-circle me-2"></i><?php echo $validationMsg?>
	</div>
	<?php
}
?>
<form id="edit_form" onsubmit="return false;">
<input type="hidden" name="sec" value="<?php echo $editAction?>"/>
<?php if ($editAction == 'updateReviewLink') {?>
	<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<?php }?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $headText?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Website']?>:</td>
		<td class="td_right_col">
			<select name="website_id" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $post['website_id']){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
			<?php echo $errMsg['website_id']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Type']?>:</td>
		<td class="td_right_col">
			<select name="type" id="review_link_type" class="custom-select">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<?php if($serviceName == $post['type']){?>
						<option value="<?php echo $serviceName?>" selected><?php echo $serviceInfo['label']?></option>
					<?php }else{?>
						<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
					<?php }?>
				<?php }?>
			</select>
			<?php echo $errMsg['service_name']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Name']?>:</td>
		<td class="td_right_col"><input type="text" name="name" value="<?php echo $post['name']?>" class="form-control"><?php echo $errMsg['name']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Link']?>:</td>
		<td class="td_right_col">
			<input type="url" name="url" value="<?php echo $post['url']?>" class="form-control" required="required">
			<?php echo $errMsg['url']?>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "google";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo implode(', ', $serviceList[$serviceSelName]['example'])?></span></p>
				<?php
			}?>
		</td>
	</tr>
</table>

<table class="actionSec float-right mt-2">
	<tr>
		<td>
			<a onclick="scriptDoLoad('<?php echo $pageScriptPath?>', 'content')" href="javascript:void(0);" class="btn btn-warning">
				<?php echo $spText['button']['Cancel']?>
			</a>&nbsp;
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('$pageScriptPath', 'edit_form', 'content')"; ?>
			<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
				<?php echo $spText['button']['Proceed']?>
			</a>
		</td>
	</tr>
</table>
</form>

<script type="text/javascript">
var typeList = [];
<?php foreach ($serviceList as $serviceName => $serviceInfo) {?>
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example'][0]?>";
<?php }?>

(function () {
	$('#review_link_type').change(function() {
		service = $(this).val();
		$('#ex_review_link').html(typeList[service]);
	});
})();
</script>