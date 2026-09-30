<select name="source_id" class="custom-select">
	<?php foreach($sourceList as $sourceInfo){?>
		<?php if($sourceInfo['id'] == $sourceId){?>
			<option value="<?php echo $sourceInfo['id']?>" selected><?php echo htmlspecialchars($sourceInfo['source_name'])?></option>
		<?php }else{?>
			<option value="<?php echo $sourceInfo['id']?>"><?php echo htmlspecialchars($sourceInfo['source_name'])?></option>
		<?php }?>
	<?php }?>
</select>