<select name="keyword_id" id="keyword_id" style="width:150px;" onchange="<?php echo $onChange?>">
	<?php if ($keyNull) {
    ?>
		<option value="">-- Select --</option>
	<?php 
} ?>
	<?php foreach ($keywordList as $keywordInfo) {
    ?>
		<?php if ($keywordInfo['id'] == $keywordId) {
    ?>
			<option value="<?php echo $keywordInfo['id']?>" selected><?php echo $keywordInfo['name']?></option>
		<?php 
} else {
    ?>
			<option value="<?php echo $keywordInfo['id']?>"><?php echo $keywordInfo['name']?></option>
		<?php 
}
    ?>
	<?php 
}?>
</select>