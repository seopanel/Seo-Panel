<?php
echo showSectionHead($spTextPanel['User Type Settings']);
$actionLink = "user-types-manager.php?plugin_id=$pluginId&class_name=$className&sec=edit_plugin_user_type_settings";
$changeUserTypeAction = "doLoad('user_type_id', '$actionLink', 'content')";
$actionLink .= "&user_type_id=$userTypeId";

// if saved successfully
if (!empty($saved)) {
	showSuccessMsg($spTextSettings['allsettingssaved'], false);
}

// save process failed
if (!empty($errorMsg)) {
	echo showErrorMsg($errorMsg, false);
}
?>
<form id="editPluginUserType">
<input type="hidden" name="sec" value="edit_plugin_user_type_settings"/>
<input type="hidden" name="plugin_id" value="<?php echo $pluginId?>"/>
<input type="hidden" name="class_name" value="<?php echo $className?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['User Type Settings']?></td>
		<td class="right">&nbsp;</td>
	</tr>
		
	<tr>
		<td class="td_left_col"><?php echo $spText['login']['User Type']?>:</td>
		<td class="td_right_col">
			<select name="user_type_id" id="user_type_id" class="custom-select" onchange="<?php echo $changeUserTypeAction?>">
				<?php foreach ($userTypeList as $key => $val) {?>
					<?php if ($userTypeId == $val['id']) {?>
						<option value="<?php echo $val['id']?>" selected><?php echo $val['user_type']?></option>
					<?php } else {?>
						<option value="<?php echo $val['id']?>"><?php echo $val['user_type']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	
	<?php
	foreach ($specColList as $specCol => $specColInfo) {

		switch($specColInfo['field_type']){

			case "small":
				$width = 100;
				break;

			case "bool":
				if(empty($specColInfo['spec_value'])){
					$selectYes = "";
					$selectNo = "selected";
				}else{
					$selectYes = "selected";
					$selectNo = "";
				}
				break;

			case "medium":
				$width = 300;
				break;

			case "large":
			case "text":
				$width = 600;
				break;
		}
		?>
		<tr>
			<td class="td_left_col"><?php echo $specText[$specCol]; ?>:</td>
			<td class="td_right_col">
				<?php if ($specColInfo['field_type'] == 'bool') {?>
					<select name="<?php echo $specCol?>" class="custom-select" style="width:150px;">
						<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
						<option value="0" <?php echo $selectNo?>><?php echo $spText['common']['No']?></option>
					</select>
				<?php } else if ($specColInfo['field_type'] == 'select') {?>
					<select name="<?php echo $specCol?>" class="custom-select" style="width:250px;">
						<?php
						foreach ($specColInfo['options'] as $optVal => $optLabel) {
							$selected = ($optVal == $specColInfo['spec_value']) ? " selected" : "";
							?>
							<option value="<?php echo $optVal?>" <?php echo $selected?>><?php echo $optLabel?></option>
							<?php
						}?>
					</select>
				<?php } else if ($specColInfo['field_type'] == 'text') {?>
					<textarea name="<?php echo $specCol?>" class="form-control" style='width:<?php echo $width?>px'><?php echo stripslashes($specColInfo['spec_value'])?></textarea>
				<?php } else {?>
					<input type="text" name="<?php echo $specCol?>" value="<?php echo stripslashes($specColInfo['spec_value'])?>" class="form-control" style='width:<?php echo $width?>px'>
				<?php }?>
				<?php echo $errMsg[$specCol]?>
				<?php if (!empty($specColInfo['help_text'])) { ?>
					<small class="form-text text-muted" style="display: block; margin-top: 10px;">
						<i class="fa fa-info-circle"></i> <?php echo $specColInfo['help_text']?>
					</small>
				<?php } ?>
			</td>
		</tr>
	<?php }?>
	

</table>

<table class="actionSec float-right mt-2">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('<?php echo $actionLink?>', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a> &nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('user-types-manager.php', 'editPluginUserType', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>