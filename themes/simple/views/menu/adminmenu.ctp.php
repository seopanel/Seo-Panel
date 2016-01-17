<?php
$homeClass = "";
$adminClass = "";
$seoToolsClass = "";
$seoPluginsClass = "";
switch ($this->menu) {
    
    case "support":
        $supportClass = "current";
        break;
        
    case "adminpanel":
        $adminClass = "current";
        break;
        
    case "seotools":
        $seoToolsClass = "current";
        break;
        
    case "seoplugins":
        $seoPluginsClass = "current";
        break;
        
    case "home":
    default:
        $homeClass = "current";
        break;
}
?>
<li><a class="<?php echo $homeClass?>" href="<?php echo SP_WEBPATH?>/"><?php echo $spText['common']['Dashboard']?></a></li>
<li><a class="<?php echo $seoToolsClass?>" href="<?php echo SP_WEBPATH?>/seo-tools.php"><?php echo $spText['common']['Seo Tools']?></a></li>
<li><a class="<?php echo $seoPluginsClass?>" href="<?php echo SP_WEBPATH?>/seo-plugins.php?sec=show"><?php echo $spText['common']['Seo Plugins']?></a></li>
<li><a class="<?php echo $supportClass?>" href="<?php echo SP_WEBPATH?>/support.php"><?php echo $spText['common']['Support']?></a></li>
<li><a href="<?php echo SP_DONATE_LINK?>" target="_blank"><?php echo $spText['common']['Donate']?></a></li>
<?php if (SP_DEMO) {
    ?>
	<li><a href="<?php echo SP_DOWNLOAD_LINK?>" target="_blank"><?php echo $spText['label']['Download']?></a></li>
<?php 
}?>

<li style="float: right; margin-right: 12px;"><a class="<?php echo $adminClass?>" href="<?php echo SP_WEBPATH?>/admin-panel.php"><?php echo $spText['common']['Admin Panel']?></a>