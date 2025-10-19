<?php
$userInfo = @Session::readSession('userInfo');
$userType = empty($userInfo['userType']) ? "guest" : $userInfo['userType'];
$homeClass = "";
$loginClass = "";
$registerClass = "";
$pricingClass = "";
$blogClass = "";
$adminClass = "";
$seoToolsClass = "";
$seoPluginsClass = "";
switch($this->menu){			
	
	case "register":
		$registerClass = "active";
		break;
		
	case "login":
		$loginClass = "active";
		break;
		
	case "pricing":
		$pricingClass = "active";
		break;
		
	case "blog":
		$blogClass = "active";
		break;
		
	case "adminpanel":
		$adminClass = "active";
		break;		
		
	case "seotools":
		$seoToolsClass = "active";
		break;
		
	case "seoplugins":
		$seoPluginsClass = "active";
		break;
		
	case "home":
	default:
		$homeClass = "active";
		break;
}

// reduce menu padding if larger text languages
if (in_array($_SESSION['lang_code'], array('ru'))) {
    ?>
    <style>
    #Tabs ul#MainTabs a:link,#Tabs ul#MainTabs a:visited {
    	padding: 6px 10px;
    }
    </style>
    <?php
}

$menuName = ($userType != "guest" && $userType != "admin") ? "user" : $userType;
$menuInfo = getCustomizerMenu($menuName);

if (!empty($menuInfo['item_list'])) {
	$linkStyle = !empty($menuInfo['bg_color']) ? "background-color: {$menuInfo['bg_color']};border-bottom-color: {$menuInfo['bg_color']}" : "";
	$linkStyle = !empty($menuInfo['font_color']) ? ";color: " . $menuInfo['font_color'] : "";
		
	// loop through menu items
	foreach ($menuInfo['item_list'] as $menuItem) {
		$linkTarget = ($menuItem['window_target'] == 'new_tab') ? "_blank" : "";
		$currClass = stristr($menuItem['url'], $_SERVER['SCRIPT_NAME']) ? "active" : "";
		?>
		<li class="nav-item <?php echo $currClass?>">
			<a class="nav-link" href="<?php echo $menuItem['url']?>" target="<?php echo $linkTarget?>" style="<?php echo $linkStyle?>" >
				<?php echo $menuItem['label']?>
			</a>
		</li>
		<?php
	}
	
} else {
	?>
	<li class="nav-item <?php echo $homeClass?>">
		<a class="nav-link" href="<?php echo SP_WEBPATH?>/">
			<i class="fas fa-home"></i> <?php echo ($userType == "guest") ? $spText['common']['Home'] : $spText['common']['Dashboard']?>
		</a>
	</li>
	
	<li class="nav-item <?php echo $seoToolsClass?>">
		<a class="nav-link" href="<?php echo SP_WEBPATH?>/seo-tools.php">
			<i class="fas fa-tools"></i> <?php echo $spText['common']['Tools']?>
		</a>
	</li>
	<li class="nav-item <?php echo $seoPluginsClass?>">
		<a class="nav-link" href="<?php echo SP_WEBPATH?>/seo-plugins.php?sec=show">
			<i class="fas fa-wrench"></i> <?php echo $spText['common']['Plugins']?>
		</a>
	</li>

	<?php if (SP_DEMO) {?>
		<li class="nav-item">
			<a class="nav-link" href="<?php echo SP_DOWNLOAD_LINK?>" target="_blank"><i class="fas fa-download"></i> <?php echo $spText['label']['Download']?></a>
		</li>
	<?php }?>
	
	<?php if (!empty($custSiteInfo['plugin_active'])) {?>
		<li class="nav-item <?php echo $blogClass?>">
			<a class="nav-link" href="<?php echo SP_WEBPATH?>/blog.php" >
			<i class="fas fa-blog"></i> <?php echo $spText['common']['Blog']?></a>
		</li>
	<?php } else { ?>
		<li class="nav-item">
			<a class="nav-link" href="<?php echo SP_DONATE_LINK?>" target="_blank" rel="nofollow">
			<i class="fas fa-donate"></i> <?php echo $spText['common']['Donate']?></a>
		</li>
	<?php }?>	
	
	<?php if (SP_HOSTED_VERSION) {?>
		<li class="nav-item <?php echo $pricingClass?>">
			<a class="nav-link" href="<?php echo SP_WEBPATH?>/register.php?sec=pricing">
			<i class="fas fa-dollar-sign"></i> <?php echo $spText['common']['Pricing']?></a>
		</li>
	<?php }?>
	
	<?php if ($userType == "guest") {?>
		<li class="nav-item <?php echo $loginClass?>">
			<a class="nav-link" href="<?php echo SP_WEBPATH?>/login.php">
			<i class="fas fa-sign-in-alt"></i> <?php echo $spTextLogin['Login']?></a>
		</li>				
	<?php } else {?>
		<li class="nav-item <?php echo $adminClass?>">
			<a class="nav-link" href="<?php echo SP_WEBPATH?>/admin-panel.php">
				<i class="fas fa-cogs"></i>
				<?php echo $spTextPanel['Settings']?>
			</a>
		</li>		
		<li class="nav-item <?php echo $loginClass?>">
			<a class="nav-link" href="<?php echo SP_WEBPATH?>/login.php?sec=logout">
			<i class="fas fa-sign-out-alt"></i> <?php echo $spText['common']['Logout']?></a>
		</li>
	<?php }?>	
<?php }?>