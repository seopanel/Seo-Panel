<ul id='subui'>
	<li><a href="javascript:void(0);" onclick="<?php echo pluginMenu('action=index'); ?>">Test Plugin</a></li>
	<?php if (isAdmin()) {
    ?>
		<li><a href="javascript:void(0);" onclick="<?php echo pluginMenu('action=settings');
    ?>">Settings</a></li>
	<?php 
}?>
</ul>