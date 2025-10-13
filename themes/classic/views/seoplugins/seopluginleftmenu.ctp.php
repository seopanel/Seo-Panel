<script>
	var menuList = new Array();
	var buttonList = new Array();	
</script>
<ul id="menu">
<?php 
foreach($menuList as $i => $menuInfo){
    if($menuSelected == $menuInfo['id']){
		$menuClass = "fa-bars";
        $style = "";
    }else{
		$menuClass = "fa-bars";
        $style = "none";
	}
	$button = "img".$menuInfo['id'];
	$subMenuId = "sub".$menuInfo['id'];
	?>
	<script type="text/javascript">
		menuList[<?php echo $i?>] = '<?php echo $subMenuId?>';
		buttonList[<?php echo $i?>] = '<?php echo $button?>';
	</script>
	<li class="tab" onclick="showMenu('<?php echo $button?>','<?php echo $subMenuId?>')">
		<i id="<?php echo $button?>" class="fas <?php echo $menuClass?>"></i>
		<a href='javascript:void(0);'>
			<?php echo $menuInfo['label']?>
		</a>
	</li>
	<li id="<?php echo $subMenuId?>" class="subtab" style="display:<?php echo $style?>;padding-left:0px;"><?php echo $menuInfo['menu']?></li>
	<?php
}
?>
</ul>