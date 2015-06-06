<?php $langStyle = empty($langStyle) ? 150 : $langStyle; ?>
<select name="lang_code" id="lang_code" style="width:<?php echo $langStyle?>px;" onchange="<?php echo $onChange?>">
	<?php if($langNull){ ?>
		<option value="">-- all --</option>
	<?php } ?>
	<?php foreach($langList as $langInfo){?>
		<?php if($langInfo['lang_code'] == $post['lang_code']){?>
			<option value="<?php echo $langInfo['lang_code']?>" selected><?php echo $langInfo['lang_name']?></option>
		<?php }else{?>
			<option value="<?php echo $langInfo['lang_code']?>"><?php echo $langInfo['lang_name']?></option>
		<?php }?>
	<?php }?>
</select>
