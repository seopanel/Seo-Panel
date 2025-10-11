<?php
$headText = ($editAction == 'updateSocialMediaLink') ? $spTextSMC['Edit Social Media Link'] : $spTextSMC['New Social Media Link'];
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
<?php if ($editAction == 'updateSocialMediaLink') {?>
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
			<select name="type" id="sm_type" class="custom-select">
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
		<td class="td_left_col" id='sm_url_label'><?php echo $spText['common']['Link']?>:</td>
		<td class="td_right_col">
			<input type="text" name="url" value="<?php echo $post['url']?>" class="form-control"><?php echo $errMsg['url']?>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "facebook";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo $serviceList[$serviceSelName]['example']?></span></p>
				<?php
			}?>
			<div style="padding: 10px 6px; display: none;" id="sm_url_note">
				<a target="_blank" href="<?php echo SP_MAIN_SITE?>/blog/2020/07/how-do-i-find-the-linkedin-company-id/">
					<?php echo $spTextSMC['Click here to get LinkedIn Company Id']; ?> &gt;&gt;
				</a>
			</div>
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
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example']?>";
<?php }?>

$(function() {	
	$('#sm_type').change(function() {
		smType = $(this).val();
		$('#ex_review_link').html(typeList[smType]);
		
		if (smType == 'linkedin') {
			$('#sm_url_label').html("<?php echo $spTextSMC['Company Id']?>:");
			$('#sm_url_note').show();
		} else {
			$('#sm_url_label').html("<?php echo $spText['common']['Link']?>:");
			$('#sm_url_note').hide();
		}		
	});
	
});
</script>