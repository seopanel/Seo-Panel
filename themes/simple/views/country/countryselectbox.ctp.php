<?php $countryStyle = empty($countryStyle) ? 150 : $countryStyle; ?>  
<select name="country_code" id="country_code" style="width:<?php echo $countryStyle?>px;" onchange="<?php echo $onChange?>">
	<?php if ($langNull) {
    ?>
		<option value="">-- all --</option>
	<?php 
} ?>
	<?php foreach ($countryList as $countryInfo) {
    ?>
		<?php if ($countryInfo['country_code'] == $post['country_code']) {
    ?>
			<option value="<?php echo $countryInfo['country_code']?>" selected><?php echo $countryInfo['country_name']?></option>
		<?php 
} else {
    ?>
			<option value="<?php echo $countryInfo['country_code']?>"><?php echo $countryInfo['country_name']?></option>
		<?php 
}
    ?>
	<?php 
}?>
</select>
