<select name="link_id" id="link_id" onchange="<?php echo $onChange?>" class="custom-select">
	<?php if($linkNull){ ?>
		<option value="">-- <?php echo $spText['common']['Select']?> --</option>
	<?php } ?>
	<?php 
	foreach($linkList as $linkInfo){
        $selectedVal = ($linkInfo['id'] == $linkId) ? "selected" : "";
        ?>
	    <option value="<?php echo $linkInfo['id']?>" <?php echo $selectedVal;?> ><?php echo $linkInfo['name']?></option>
		<?php
	}
	?>
</select>