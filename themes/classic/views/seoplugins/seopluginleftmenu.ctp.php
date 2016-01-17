<script>
	var menuList = new Array();
	var buttonList = new Array();	
</script>
<ul id="menu">
<?php 
foreach ($menuList as $i => $menuInfo) {
    if ($menuSelected == $menuInfo['id']) {
        $imgSrc = "hide";
        $style = "";
    } else {
        $imgSrc = "more";
        $style = 'none';
    }
    $button = "img".$menuInfo['id'];
    $subMenuId = "sub".$menuInfo['id'];
    ?>
	<script type="text/javascript">
		menuList[<?php echo $i?>] = '<?php echo $subMenuId?>';
		buttonList[<?php echo $i?>] = '<?php echo $button?>';
	</script>
	<li class="tab">
		<a href='javascript:void(0);' onclick="showMenu('<?php echo $button?>','<?php echo $subMenuId?>')"><img id="<?php echo $button?>" src="<?php echo SP_IMGPATH."/".$imgSrc?>.gif"><?php echo $menuInfo['label']?></a>
	</li>
	<li id="<?php echo $subMenuId?>" class="subtab" style="display:<?php echo $style?>;padding-left:0px;"><?php echo $menuInfo['menu']?></li>
	<?php

}
?>
</ul>