<?php
// Mobile/PWA bottom tab bar - native-app-style navigation for small
// viewports (installed PWA home screen use is the primary target), kept
// in lockstep with main_menu.ctp.php's own active-state logic and URLs
// so the two navigations never disagree about where a link goes. Hidden
// at the md breakpoint and up (see .sp-pwa-bottomnav's d-md-none), where
// the existing top navbar already covers this.
$userInfo = @Session::readSession('userInfo');
$userType = empty($userInfo['userType']) ? "guest" : $userInfo['userType'];
$pwaHomeActive = "";
$pwaToolsActive = "";
$pwaPluginsActive = "";
$pwaSettingsActive = "";
$pwaLoginActive = "";
switch ($this->menu) {
	case "adminpanel":
		$pwaSettingsActive = "active";
		break;
	case "seotools":
		$pwaToolsActive = "active";
		break;
	case "seoplugins":
		$pwaPluginsActive = "active";
		break;
	case "login":
		$pwaLoginActive = "active";
		break;
	case "home":
	default:
		$pwaHomeActive = "active";
		break;
}
?>
<nav class="sp-pwa-bottomnav d-md-none">
	<a class="sp-pwa-bottomnav-item <?php echo $pwaHomeActive?>" href="<?php echo SP_WEBPATH?>/">
		<i class="fas fa-th-large"></i>
		<span><?php echo ($userType == "guest") ? $spText['common']['Home'] : $spText['common']['Dashboard']?></span>
	</a>
	<a class="sp-pwa-bottomnav-item <?php echo $pwaToolsActive?>" href="<?php echo SP_WEBPATH?>/seo-tools.php">
		<i class="fas fa-tools"></i>
		<span><?php echo $spText['common']['Tools']?></span>
	</a>
	<a class="sp-pwa-bottomnav-item <?php echo $pwaPluginsActive?>" href="<?php echo SP_WEBPATH?>/seo-plugins.php?sec=show">
		<i class="fas fa-wrench"></i>
		<span><?php echo $spText['common']['Plugins']?></span>
	</a>
	<?php if ($userType == "guest") { ?>
		<a class="sp-pwa-bottomnav-item <?php echo $pwaLoginActive?>" href="<?php echo SP_WEBPATH?>/login.php">
			<i class="fas fa-sign-in-alt"></i>
			<span><?php echo $spTextLogin['Login']?></span>
		</a>
	<?php } else { ?>
		<a class="sp-pwa-bottomnav-item <?php echo $pwaSettingsActive?>" href="<?php echo SP_WEBPATH?>/admin-panel.php">
			<i class="fas fa-cogs"></i>
			<span><?php echo $spTextPanel['Settings']?></span>
		</a>
		<a class="sp-pwa-bottomnav-item" href="<?php echo SP_WEBPATH?>/login.php?sec=logout">
			<i class="fas fa-sign-out-alt"></i>
			<span><?php echo $spText['common']['Logout']?></span>
		</a>
	<?php } ?>
</nav>
