<?php if(!empty($msg)){
	$msgClass = empty($error) ? "success" : "error"; 
	?>
		<p class="dirmsg">
			<font class="<?php echo $msgClass?>"><?php echo $msg?></font>
		</p>
	<?php 
	}
?>