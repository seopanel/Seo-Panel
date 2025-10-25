<select name="link_id" id="link_id" onchange="<?php echo $onChange?>" style="width: 200px;" class="custom-select">
	<?php if($linkNull){ ?>
		<option value="">-- <?php echo $spText['common']['Select']?> --</option>
	<?php } ?>
	<?php foreach($linkList as $linkInfo){?>
		<?php if($linkInfo['id'] == $linkId){?>
			<option value="<?php echo $linkInfo['id']?>" selected><?php echo $linkInfo['name']?></option>
		<?php }else{?>
			<option value="<?php echo $linkInfo['id']?>"><?php echo $linkInfo['name']?></option>
		<?php }?>
	<?php }?>
</select>