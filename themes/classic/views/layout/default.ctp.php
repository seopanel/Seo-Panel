<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
    $custSiteInfo = getCustomizerDetails();
    $spTitle = empty($spTitle) ? SP_TITLE : $spTitle;
    $spDescription = empty($spDescription) ? SP_DESCRIPTION : $spDescription;
    $spKeywords = empty($spKeywords) ? SP_KEYWORDS : $spKeywords;
    $spKey = "v" . substr(SP_INSTALLED, 2);
    $userInfo = @Session::readSession('userInfo');
    $userType = empty($userInfo['userType']) ? "guest" : $userInfo['userType'];
    
    $menuName = ($userType != "guest" && $userType != "admin") ? "user" : $userType;
    $menuInfo = getCustomizerMenu($menuName);
    
    if (!empty($menuInfo['bg_color'])) {
    	$siteBgClass = $menuInfo['bg_color'];
    	$siteFooterBgClass = $menuInfo['bg_color'];
    } else {
	    // theme wise changes
	    if (stristr(SP_VIEWPATH, '/simple/')) {
	    	$siteBgClass = "navbar-expand-md-bg";
	    	$siteFooterBgClass = "footer-sp-bg";
	    } else {
	    	$siteBgClass = "bg-dark";
	    	$siteFooterBgClass = "bg-dark text-muted";
	    }
    }
    
    $siteNavFontClass = !empty($menuInfo['font_color']) ? $menuInfo['font_color'] : "navbar-dark"; 
    ?>
    <title><?php echo stripslashes($spTitle)?></title>
    <meta name="description" content="<?php echo $spDescription?>" />
    <meta name="keywords" content="<?php echo $spKeywords?>" />
    <link rel="shortcut icon" href="<?php echo !empty($custSiteInfo['site_favicon']) ? $custSiteInfo['site_favicon'] : SP_IMGPATH . "/favicon.ico"?>" />
    
    <!-- Css files -->
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/bootstrap.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/jquery-ui/jquery-ui.min.css?<?php echo $spKey?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/datepicker.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/fontawesome/css/all.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/simplemde.min.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen.css?<?php echo $spKey?>" media="all" />
    
    <?php if (in_array($_SESSION['lang_code'], array('ar', 'he', 'fa'))) {?>
    	<link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen_rtl.css?<?php echo $spKey?>" media="all" />
    <?php }?>
    
    <!-- JS Files -->
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/jquery-3.3.1.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/popper.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/bootstrap.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/datepicker.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_WEBPATH?>/jquery-ui/jquery-ui.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH; ?>/loader.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH; ?>/jquery.tablesorter.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/simplemde.min.js?<?php echo $spKey?>"></script>
    
     <!-- tinymce editor -->
	<script type="text/javascript" src="<?php echo SP_JSPATH?>/tinymce/tinymce.min.js?<?php echo $spKey?>"></script>
	
    <!-- sp specific files -->    
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/common.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/popup.js?<?php echo $spKey?>"></script>
    
    <?php if (isPluginActivated("customizer")) {?>
    	<link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/custom_style.php?<?php echo $spKey?>" media="all" />
    	<script type="text/javascript" src="<?php echo SP_WEBPATH?>/custom_js.php?<?php echo $spKey?>"></script>
    <?php }?>
    
</head>
<body class="bg-light">
    <script type="text/javascript">
    var spdemo = <?php echo SP_DEMO; ?>;
    var wantproceed = '<?php  echo $spText['label']['wantproceed']; ?>';
    </script>
    
    <nav class="navbar navbar-expand-md <?php echo $siteNavFontClass?> <?php echo $siteBgClass;?>">
    	<a class="navbar-brand" href="<?php echo SP_WEBPATH?>">
    		<img src="<?php echo !empty($custSiteInfo['site_logo']) ? $custSiteInfo['site_logo'] : SP_IMGPATH . "/logo_red_sm.png";?>">
    	</a>
      	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
      		aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        	<span class="navbar-toggler-icon"></span>
      	</button>
      	
    	<div class="collapse navbar-collapse" id="navbarSupportedContent">
    		<ul class="navbar-nav mr-auto">
    			<?php include(SP_VIEWPATH.'/menu/main_menu.ctp.php');?>
    		</ul>		
    		<form class="form-inline mt-2 mt-md-0">
    			<?php include_once(SP_VIEWPATH."/menu/topmenu.ctp.php");?>
    		</form>

            <?php
            if (isLoggedIn()) {
                include_once(SP_VIEWPATH."/menu/notifications_menu.ctp.php");
            }
            ?>
    	</div>
    </nav>
    	
    <?php include_once(SP_VIEWPATH."/common/top_notification.ctp.php");?>
    <?php
    // show spAPI registration popup for admin users who haven't registered or skipped
    if (isLoggedIn() && isAdmin()) {
        if (defined('SP_SPAPI_REGISTERED') && !SP_SPAPI_REGISTERED) {
            include_once(SP_CTRLPATH."/settings.ctrl.php");
            $spApiCtrl = new SettingsController();
            if ($spApiCtrl->showSpApiRegistrationPopup()) {
                include_once(SP_VIEWPATH."/settings/spapi_register_popup.ctp.php");
            }
        }
    }
    ?>

    <div class="container-fluid" style="margin-bottom: 50px;">  	
    	<div class="row">
    		<?php echo $viewContent?>
    	</div>
    </div>
    
    <div class="container-fluid fixed-bottom <?php echo $siteNavFontClass?> <?php echo $siteFooterBgClass;?> center footer-sp">
    	<?php include_once(SP_VIEWPATH."/common/footer.ctp.php"); ?>
    </div>
    
    <div id="tmp"><form name="tmp" id="tmp"></form></div>
    <div id="dialogContent" style="display:none;"></div>
    <?php
    // add google analytics code to verify the site hits 
    if ( defined('SP_GOOGLE_ANALYTICS_TRACK_CODE')) {
    	if (!stristr(SP_GOOGLE_ANALYTICS_TRACK_CODE, '<script')) {
    		?>
    		<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo SP_GOOGLE_ANALYTICS_TRACK_CODE?>"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());
			
			  gtag('config', '<?php echo SP_GOOGLE_ANALYTICS_TRACK_CODE?>');
			</script>
    		<?php
    	} else {
    		echo SP_GOOGLE_ANALYTICS_TRACK_CODE;
    	}
    }
    ?>
</body>
</html>