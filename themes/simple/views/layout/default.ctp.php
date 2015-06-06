<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <?php
    $spTitle = empty($spTitle) ? SP_TITLE : $spTitle;
    $spDescription = empty($spDescription) ? SP_DESCRIPTION : $spDescription;
    $spKeywords = empty($spKeywords) ? SP_KEYWORDS : $spKeywords;
    $spKey = "v" . substr(SP_INSTALLED, 2);
    ?>
    <title><?php echo stripslashes($spTitle)?></title>
    <meta name="description" content="<?php echo $spDescription?>" />
    <meta name="keywords" content="<?php echo $spKeywords?>" />
    <link type="text/css" href="<?php echo SP_WEBPATH?>/jquery-ui-custom/css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen.css?<?php echo $spKey?>" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/datepicker.css?<?php echo $spKey?>" media="all" />
    <?php if (in_array($_SESSION['lang_code'], array('ar', 'he', 'fa'))) {?>
    	<link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen_rtl.css?<?php echo $spKey?>" media="all" />
    <?php }?>
    <link rel="shortcut icon" href="<?php echo SP_IMGPATH?>/favicon.ico" />
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/jquery-1.10.1.min.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/common.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/popup.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_JSPATH?>/datepicker.js?<?php echo $spKey?>"></script>
    <script type="text/javascript" src="<?php echo SP_WEBPATH?>/jquery-ui-custom/js/jquery-ui-1.10.3.custom.min.js?<?php echo $spKey?>"></script>
</head>
<body>
<script type="text/javascript">
var spdemo = <?php echo SP_DEMO; ?>;
var wantproceed = '<?php  echo $spText['label']['wantproceed']; ?>';
</script>

<div class="main_container">

    <div id="Header">

    	<div id="round_content_header">

            <?php include_once(SP_VIEWPATH."/menu/topmenu.ctp.php");?>

            <div style="width:300px;">
            	<a href="<?php echo SP_WEBPATH; ?>" style="text-decoration: none; padding: 0px;"><h1 style="width:200px;">Seo Panel</h1></a>
            </div>

            <!-- TABS -->
            <div id="Tabs">
                <ul id="MainTabs">
                    <?php
                    $userInfo = @Session::readSession('userInfo');
                    $userType = empty($userInfo['userType']) ? "guest" : $userInfo['userType'];
                    include(SP_VIEWPATH.'/menu/'.$userType.'menu.ctp.php');
                    ?>
                </ul>
            </div>
        </div>

        <?php echo getRoundTabBot(); ?>
    </div>

    <div id="Wrapper">
        <table width="100%" cellspacing="0px" cellpadding="0px">
        	<tr><td id="newsalert"></td></tr>
        	<tr>
        		<td class="Container">
            		<div id="ContentFrame">
            			<noscript>
            				<p class="note error">JavaScript is turned off in your web browser. Turn it on to take full advantage of this site, then refresh the page.</p>
            			</noscript>
            			<?php echo $viewContent?>
            		</div>
        		</td>
        	</tr>
        </table>
    </div>
    <?php include_once(SP_VIEWPATH."/common/footer.ctp.php"); ?>
</div>
<div id="tmp"><form name="tmp" id="tmp"></form></div>
<div id="dialogContent" style="display:none;"></div>
<?php if(empty($_COOKIE['hidenews'])){ ?>
	<script>scriptDoLoad('<?php echo SP_WEBPATH?>/index.php?sec=news', 'newsalert');</script>
<?php }?>
</body>
</html>