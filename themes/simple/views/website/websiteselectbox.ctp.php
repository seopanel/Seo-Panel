<select name="website_id" id="website_id" style="width:150px;" onchange="<?php echo $onChange?>">
	<?php if ($websiteNull) {
    ?>
		<option value="">-- <?php echo $spText['common']['Select']?> --</option>
	<?php 
} ?>
	<?php foreach ($websiteList as $websiteInfo) {
    ?>
		<?php if ($websiteInfo['id'] == $websiteId) {
    ?>
			<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
		<?php 
} else {
    ?>
			<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
		<?php 
}
    ?>
	<?php 
}?>
</select>