<?php if(!empty($msg)){
	$msgClass = empty($error) ? "success" : "error"; 
	?>
		<p class="dirmsg">
			<font class="<?php echo $msgClass?>"><?php echo htmlspecialchars($msg, ENT_QUOTES)?></font>
		</p>
	<?php 
	}
?>