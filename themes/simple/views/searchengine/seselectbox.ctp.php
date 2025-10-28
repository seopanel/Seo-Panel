<?php $seStyle = empty($seStyle) ? 200 : $seStyle; ?>  
<select name="se_id" id="se_id" style="width:<?php echo $seStyle?>px;" onchange="<?php echo $onChange?>" class="custom-select">
	<?php if($seNull){ ?>
		<option value="">-- <?php echo $spText['common']['Select']?> --</option>
	<?php } ?>
	<?php foreach($seList as $seInfo){?>
		<?php if($seInfo['id'] == $seId){?>
			<option value="<?php echo $seInfo['id']?>" selected><?php echo $seInfo['domain']?></option>
		<?php }else{?>
			<option value="<?php echo $seInfo['id']?>"><?php echo $seInfo['domain']?></option>
		<?php }?>
	<?php }?>
</select>
