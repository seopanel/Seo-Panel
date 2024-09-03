<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   *
 *   sendtogeo@gmail.com   												   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

# func to format error message
function formatErrorMsg($msg, $class='error', $star="*"){
	if(!empty($msg)){
		$msg = "<font class='$class'> $star $msg</font>";
	}
	return $msg;
}

# func to format success message
function formatSuccessMsg($msg, $class='success'){
	if(!empty($msg)){
		$msg = "<font class='$class'>$msg</font>";
	}
	return $msg;
}

# func to redirect url
function redirectUrl($url) {
	header("Location: $url");
	exit;
}

# func to redirect url
function redirectUrlByScript($url) {
	print "<script>window.location='$url';</script>";
}

# func to hide div
function hideDiv($divId) {
	print "<script>hideDiv('$divId')</script>";
}


# func to show div
function showDiv($divId) {
	print "<script>showDiv('$divId')</script>";
	exit;
}


# func to show no results
function showNoRecordsList($colspan, $msg='', $plain=false) {
	$msg = empty($msg) ? $_SESSION['text']['common']['No Records Found'] : $msg;
	$data['colspan'] = $colspan;
	$data['msg'] = $msg;
	$data['plain'] = $plain;
	return @View::fetchViewFile('common/norecords', $data);
}

# func to show error msg
function showErrorMsg($errorMsg, $exit=true, $return = false) {
	$data['errorMsg'] = $errorMsg;
	
	// if return is set
	if ($return && !$exit) {
		return @View::fetchViewFile('common/error', $data);
	} else {
		print @View::fetchViewFile('common/error', $data);
		if($exit) exit;
	}
}

# func to show success msg
function showSuccessMsg($successMsg, $exit=true) {
	$data['successMsg'] = $successMsg;
	print @View::fetchViewFile('common/success', $data);
	if($exit) exit;
}

# func to show no results
function showSectionHead($sectionHead) {
	$data['sectionHead'] = $sectionHead;
	return @View::fetchViewFile('common/sectionHead', $data);
}

# function to check whether user logged in
function checkLoggedIn() {
	$userInfo = @Session::readSession('userInfo');
	if(empty($userInfo['userId'])){
		redirectUrlByScript(SP_WEBPATH."/login.php");
		exit;
	}
	
	// check whethere user expired, then redirect to subscribe page
	$userCtrl = New UserController();
	if (!$userCtrl->isUserExpired($userInfo['userId'])) {
		redirectUrl(SP_WEBPATH."/admin-panel.php?sec=myprofile&expired=1");
	}
}

# function to check whether admin logged in
function checkAdminLoggedIn() {
	$userInfo = @Session::readSession('userInfo');
	if(empty($userInfo['userType']) || ($userInfo['userType'] != 'admin') ) {
		redirectUrlByScript(SP_WEBPATH."/login.php");
		exit;
	}
}

# function to user is admin or not
function isAdmin() {
	$userInfo = @Session::readSession('userInfo');
	return ($userInfo['userType'] == 'admin') ? $userInfo['userId'] : false;
}

# function to user logged in or not
function isLoggedIn() {
	$userInfo = @Session::readSession('userInfo');
	return empty($userInfo['userId']) ? false : $userInfo['userId'];
}

# function to chekc quick checker enabled for user
function isQuickCheckerEnabled() {
	$enabled = (isAdmin() || !SP_HOSTED_VERSION) ? true : false;
	return $enabled;
}

# get functions
function scriptGetAJAXLink($file, $area, $args='', $trigger='OnClick'){
	$link = ' '.$trigger.'="scriptDoLoad('."'$file', '$area', '$args')".'"';
	return $link;
}

function confirmScriptAJAXLink($file,$area,$trigger='OnClick'){
	$link = ' '.$trigger.'="confirmLoad('."'$file', '$area', '$args')".'"';
	return $link;
}

function scriptAJAXLinkHref($file, $area, $args='', $linkText='Click', $class='', $trigger='OnClick'){
	if ($file == 'demo') {
		$link = ' '.$trigger.'="alertDemoMsg()"';
	} else {
		$link = ' '.$trigger.'="scriptDoLoad('."'$file', '$area', '$args')".'"';		
	}
	
	$link = "<a href='javascript:void(0);' class='$class' $link>$linkText</a>";
	return $link;
}

function scriptAJAXLinkHrefDialog($file, $area, $args='', $linkText='Click', $class='', $trigger='OnClick', $widthVal = 900, $heightVal = 600){
	if ($file == 'demo') {
		$link = ' '.$trigger.'="alertDemoMsg()"';
	} else {
		$link = ' '.$trigger.'="scriptDoLoadDialog('."'$file', '$area', '$args', $widthVal, $heightVal)".'"';		
	}
	
	$link = "<a href='javascript:void(0);' class='$class' $link>$linkText</a>";
	return $link;
}

function confirmScriptAJAXLinkHref($file, $area, $args='', $linkText='Click', $trigger='OnClick'){
	$link = ' '.$trigger.'="confirmLoad('."'$file', '$area', '$args')".'"';
	$link = "<a href='javascript:void(0);' class='$class' $link>$linkText</a>";
	return $link;
}


#post functions
function scriptPostAJAXLink($file, $form, $area, $trigger='OnClick'){
	$link = ' '.$trigger.'="scriptDoLoadPost('."'$file', '$form', '$area')".'"';
	return $link;
}

function confirmPostAJAXLink($file, $form, $area, $trigger='OnClick'){
	$link = ' '.$trigger.'="confirmSubmit('."'$file', '$form', '$area')".'"';
	return $link;
}

function formatUrl( $url, $removeWWW=true ) {
	$url = str_replace('http://', '', $url);
	$url = str_replace('https://', '', $url);
	
	// if ww needs to be removed
	if ($removeWWW) {
		$url = preg_replace('/^www./i', '', $url);
	}
	
	return $url;
}

function formatDate($date) {
	$date = str_replace("0000-00-00", "", $date);
	return $date;
}

function addHttpToUrl($url){
	if(!stristr($url, 'http://') && !stristr($url, 'https://')){
		$url = 'http://'.trim($url);
	}
	return trim($url);
}

function formatFileName( $fileName ) {
	$search = array(' ', '/', ':');
	$replace = array('_', '', '');
	$fileName = str_replace($search, $replace, $fileName);
	return $fileName;
}

function showActionLog($msg, $area='subcontent'){
	echo "<script type='text/javascript'>updateArea('$area', '$msg')</script>";
}

//function to update perticular area using javascript
function updateJsLocation($area, $text) {
    echo "<script type='text/javascript'>document.getElementById('$area').innerHTML = '$text';</script>";
}

function loadGif ($imgname){
	$im = @imagecreatefromgif ($imgname);
	if (!$im) {
		$im = imagecreatetruecolor (150, 30);
		$bgc = imagecolorallocate ($im, 255, 255, 255);
		$tc = imagecolorallocate ($im, 0, 0, 0);
		imagefilledrectangle ($im, 0, 0, 150, 30, $bgc);
		imagestring ($im, 1, 5, 5, "Error loading $imgname", $tc);
	}
	return $im;
}

# func to check whether logged in user having website
function isHavingWebsite() {
	$userId = isLoggedIn();
	$websiteCtrl = New WebsiteController();
	$count = isAdmin() ? $websiteCtrl->__getCountAllWebsites() : $websiteCtrl->__getCountAllWebsites($userId);
	
	if($count <= 0){
		
		// check for user website access option
		if (SP_CUSTOM_DEV && !isAdmin()) {
			$userCtrl = new UserController();
			$accessCount = $userCtrl->getUserWebsiteAccessCount($userId);
			if ($accessCount > 0) {
				return $accessCount;
			}
		}
		
		redirectUrl(SP_WEBPATH."/admin-panel.php?sec=newweb");
	}
}

# func to chekc s user have access to seo tool
function isUserHaveAccessToSeoTool($urlSection, $showError = true) {

	include_once(SP_CTRLPATH . "/seotools.ctrl.php");
	$userTypeCtrler = new UserTypeController();
	$userSessInfo = Session::readSession('userInfo');
	$toolCtrler = new SeoToolsController();
	$seoToolInfo = $toolCtrler->__getSeoToolInfo($urlSection, 'url_section');
	$haveAccess = $userTypeCtrler->isUserTypeHaveAccessToSeoTool($userSessInfo['userTypeId'], $seoToolInfo['id']);
	
	// if show error and not have access
	if ($showError && !$haveAccess) {
		showErrorMsg($_SESSION['text']['label']['Access denied']);
	}
	
	return $haveAccess;
	
}

# function to create plugin ajax get method
function pluginGETMethod($args='', $area='content', $dialog = false){
	$script = "seo-plugins.php?pid=".PLUGIN_ID;
	$scriptFunc = $dialog ? "scriptDoLoadDialog" : "scriptDoLoad";
	$request = "$scriptFunc('$script', '$area', '$args')";
	return $request;
}

# function to create plugin ajax post method
function pluginPOSTMethod($formName, $area='content', $args='', $dialog = false){
	$args = "&pid=".PLUGIN_ID."&$args";
	$scriptFunc = $dialog ? "popupScriptDoLoadPostDialog" : "scriptDoLoadPost";
	$request = "$scriptFunc('seo-plugins.php', '$formName', '$area', '$args')";
	return $request;
}

# function to create plugin ajax confirm get method
function pluginConfirmGETMethod($args='', $area='content'){
	$script = "seo-plugins.php?pid=".PLUGIN_ID;	
	$request = "confirmLoad('$script', '$area', '$args')";
	return $request;
}

# function to create plugin ajax confirm post method
function pluginConfirmPOSTMethod($formName, $area='content', $args=''){
	$args = "&pid=".PLUGIN_ID."&$args";
	$request = "confirmSubmit('seo-plugins.php', '$formName', '$area', '$args')";
	return $request;
}

# func to create plugin menu
function pluginMenu($args='', $area='content') {
    $pluginId = Session::readSession('plugin_id');
    $script = "seo-plugins.php?pid=".$pluginId;
    $request = "scriptDoLoad('$script', '$area', '$args')";
    return $request;
}

function pluginLink($args = '') {
    $script = SP_WEBPATH . "/seo-plugins.php?pid=" . PLUGIN_ID;
    $script .= (substr($args, 0, 1) == '&') ? $args : "&$args";
    return $script;
}

# func to remove new lines from a string
function removeNewLines($value) {
	$value = preg_replace('/[\r\n]*/', '', $value);
	$value = preg_replace('/[\r\n]+/', '', $value);
	return $value;
}

# func to get current url
function getCurrentUrl() {
    
    // to fix the issues with IIS
    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
        if (isset($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
        }
    }
    
	$reqUrl = $_SERVER['REQUEST_URI'];
	$protocol = empty($_SERVER['HTTPS']) ? "http://" : "https://";
	$port = empty($_SERVER['SERVER_PORT']) ?  "" : (int) $_SERVER['SERVER_PORT'];
	$host =  strtolower($_SERVER['HTTP_HOST']);
	if(!empty($port) && ($port <> 443) && ($port <> 80)){
		if(strpos($host, ':') === false){ $host .= ':' . $port; }
	}
	$webPath = $protocol.$host.$reqUrl;
	return $webPath;
}

# function to check whether refferer is from same site
function isValidReferer($referer) {
	
	if(stristr($referer, SP_WEBPATH)) {
		if (!stristr($referer, 'install') && !stristr($referer, 'login.php')) {
			$referer = str_ireplace("&lang_code=", "&", $referer);
			return $referer;
		}		
	}
	return '';
}

# func to create export content
function createExportContent($list) {
	return '"'.implode('","',$list)."\"\r\n"; 
}

# func to export data to csv file
function exportToCsv($fileName, $content) {
	
	$fileName = $fileName."_".date("Y-m-d",time());
	header("Content-type: application/vnd.ms-excel");
	header("Content-disposition: csv" . date("Y-m-d") . ".csv");
	header( "Content-disposition: filename=".$fileName.".csv");
	print $content;
	exit; 
}

# func to show printer hearder
function showPrintHeader($headMsg='', $doPrint=true) {
    ?>
    <!doctype html>
	<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<script type="text/javascript">
			<?php if ($doPrint) { ?>
				window.print();
			<?php }?>
		</script>		
	    <style type="text/css">
		    <?php echo readFileContent(SP_THEME_ABSPATH . "/css/screen.css"); ?>
	    </style>    
    </head>
    <body>
	<?php
	if (!empty($headMsg)) echo showSectionHead($headMsg);
}

# func to read file content
function readFileContent($fileName) {
	$handle = fopen($fileName, "r");
	$cfgData = fread($handle, filesize($fileName));
	fclose($handle);
	return $cfgData;
}

# func to show printer footer
function showPrintFooter($spText) {
	$custSiteInfo = getCustomizerDetails();
	if (!empty($custSiteInfo['footer_copyright'])) {
		$copyrightTxt = str_replace('[year]', date('Y'), $custSiteInfo['footer_copyright']);
	} else {
		$copyrightTxt = str_replace('[year]', date('Y'), $spText['common']['copyright']);
	}
    ?>
    <div class="center footer-sp"><?php echo $copyrightTxt;?></div>
    </body>
    </html>
	<?php
}

# func to debug the variables
function debugVar($value, $exitFlag = true) {
    echo "<pre>";print_r($value);echo "</pre>";
    
    // if exit flag set terminate execution
    if ($exitFlag) {
		exit;
	}
    
}

# func to send mail
function sendMail($from, $fromName, $to ,$subject,$content, $attachment = ''){
	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';
	
	# check whether the mail send by smtp or not
	if(SP_SMTP_MAIL){
		$mail->IsSMTP();	
		$mail->SMTPAuth = true;
		$mail->Host = SP_SMTP_HOST;
		$mail->Username = SP_SMTP_USERNAME;
		$mail->Password = SP_SMTP_PASSWORD;
		$mail->Port = SP_SMTP_PORT;
		
		// if mail encryption enabled
		if (defined('SP_MAIL_ENCRYPTION') && (SP_MAIL_ENCRYPTION != '')) {
            $mail->SMTPSecure = SP_MAIL_ENCRYPTION;
		}		
	}

	$mail->From = $from;
	$mail->FromName = $fromName;
	$mail->AddAddress($to);
	$mail->WordWrap = 70;                              
	$mail->IsHTML(true);

	$mail->Subject = $subject;
	$mail->Body = $content;
	$mail->msgHTML($content); //creates plaintext alternate automatically
	$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; //fallback
	
	// if attachments are there
	if (!empty($attachment)) {
		$mail->AddAttachment($attachment);
	}
	
	$mailLogInfo = [];
	
	// if sendgrid api should be used, if enabled it
	if ($mail->Host == 'smtp.sendgrid.net' && SP_SENDGRID_API) {
		$sendLog = sendMailBySendgridAPI($mail, $to);
		$mailLogInfo['mail_category'] = 'sendgrid';
	} else {
	
		// normal mail send fails or not
		if($mail->Send()){
			$sendLog['status'] = 1;
			$sendLog['log_message'] = "Success";
		} else {
			$sendLog['status'] = 0;
			$sendLog['log_message'] = $mail->ErrorInfo;
		}
		
	}
	
	// update mail log
	$crawlLogCtrl = new CrawlLogController();	
	$mailLogInfo['subject'] = $subject;
	$mailLogInfo['from_address'] = $from;
	$mailLogInfo['to_address'] = $to;
	$mailLogInfo['status'] = $sendLog['status'];
	$mailLogInfo['log_message'] = $sendLog['log_message'];
	$crawlLogCtrl->createMailLog($mailLogInfo);
	
	return $sendLog['status'];
	
}

function sendMailBySendgridAPI($mail, $subscriberEmail, $subscriberName = '') {
	
	include_once(SP_LIBPATH . "/sendgrid-php/sendgrid-php.php");
	$from = new SendGrid\Email($mail->FromName, $mail->From);
	$subject = $mail->Subject;
	$to = new SendGrid\Email($subscriberName, $subscriberEmail);
	$content = new SendGrid\Content($mail->ContentType, $mail->Body);
	$apiKey = $mail->Password;
	$mail = new SendGrid\Mail($from, $subject, $to, $content);
	$sg = new \SendGrid($apiKey);
	$response = $sg->client->mail()->send()->post($mail);
	 
	$statusCode = $response->statusCode();
	if (in_array($statusCode, array("202", "200"))) {
		$sendLog['status'] = 1;
		$sendLog['log_message'] = "Success";
	} else {
		$sendLog['status'] = 0;
		$sendLog['log_message'] = $response->body();
	}
	 
	return $sendLog;
	 
}


# func to sanitize data to prevent attacks
function sanitizeData($data, $stripTags=true, $addSlashes=false) {
    
    if (is_array($data)) {
        foreach ($data as $col => $val) {

            if ( ($col == 'password') ||  ($col== 'confirmPassword') ) {
                continue;
            }
            
            if ($stripTags) {
                $val = strip_tags($val);
            } 
            
            if ($addSlashes) {
                $val = addslashes($val);
            }
            
            $data[$col] = $val;
        }
    } else {
        if ($stripTags) {
            $data = strip_tags($data);
        } 
        
        if ($addSlashes) {
            $data = addslashes($data);
        }
    }
    return $data;
}

# func to get rounded tab top
function getRoundTabTop(){
	
	$content = '
		<b class="round_border">
			<b class="round_border_layer3"></b>
			<b class="round_border_layer2"></b>
			<b class="round_border_layer1"></b>
		</b>
	';
	return $content;
}

# func to get rounded tab bottom
function getRoundTabBot(){
	
	$content = '
		<b class="round_border">
			<b class="round_border_layer1"></b>
			<b class="round_border_layer2"></b>
			<b class="round_border_layer3"></b>
		</b>
	';
	return $content;
}

# function to convert to pdf  from view file
function exportToPdf($content, $fileName = "reports.pdf") {	
    include_once(SP_LIBPATH . "/mpdf_lib/vendor/autoload.php");
    $mpdf = new \Mpdf\Mpdf(
        [
            'tempDir' => SP_TMPPATH,
            'defaultCssFile' => SP_THEME_ABSPATH ."/css/screen.css",
        ]
    );
    
    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont = true;
	$mpdf->useAdobeCJK = true;
	$mpdf->SetDisplayMode('fullpage');
	$mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
	$mpdf->Output($fileName, "I");
	exit;
}

# func to show pdf header
function showPdfHeader($headMsg = '') {
	?>
    <head>
    	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    </head>
	<?php
	if (!empty($headMsg)) echo showSectionHead($headMsg);
}

# func to show pdf footer
function showPdfFooter($spText) {
	$custSiteInfo = getCustomizerDetails();
	if (!empty($custSiteInfo['footer_copyright'])) {
		$copyrightTxt = str_replace('[year]', date('Y'), $custSiteInfo['footer_copyright']);
	} else {
		$copyrightTxt = str_replace("www.seopanel.in", "<a href='https://www.seopanel.org'>www.seopanel.org</a>", $spText['common']['copyright']);
	}
    ?>
    <div style="clear: both; margin-top: 30px;font-size: 12px; text-align: center;"><?php echo str_replace('[year]', date('Y'), $copyrightTxt)?></div>
	<?php
}

# function to loop through the array values and change it to int or string
function formatSQLParamList($paramList, $type = 'int') {
	
	foreach ($paramList as $key => $value) {
		$paramList[$key] = ($type == 'int') ? intval($value) : addslashes($value);
	}
	
	return $paramList;
	
}

# function to find order by value to prevent sql injection
function getOrderByVal($orderByVal) {
	$orderByVal = (strtolower($orderByVal) == 'desc') ? "DESC" : "ASC";
	return $orderByVal;
}

if (!function_exists('curl_reset')) {
	
	function curl_reset(&$ch) {
		$ch = curl_init();
	}
	
}

function getRequestParamStr() {
	$paramStr = "";
	
	foreach ($_REQUEST as $item => $value) {
		$item = htmlentities($item, ENT_QUOTES);
		$value = htmlentities($value, ENT_QUOTES);
		$paramStr .= "&$item=" . urlencode($value);
	}
	
	return $paramStr;
	
}

// get dates in between a date range
function getDateRange($first, $last, $step = '+1 day', $outputFormat = 'Y-m-d' ) {

	$dates = array();
	$current = strtotime($first);
	$last = strtotime($last);

	while( $current <= $last ) {
		$dates[] = date($outputFormat, $current);
		$current = strtotime($step, $current);
	}

	return $dates;
}

function getCustomizerDetails() {
    $custSiteInfo = array();
    
    // check whetehr plugin installed or not
    $seopluginCtrler = new SeoPluginsController();
    if ($seopluginCtrler->isPluginActive("customizer")) {
        $infoList = $seopluginCtrler->dbHelper->getAllRows("cust_site_details", "status=1");
        foreach ($infoList as $info) $custSiteInfo[$info['col_name']] = $info['col_value'];
        $custSiteInfo['plugin_active'] = 1;
    }
    
    return $custSiteInfo;
    
}

// function to get customizer pages[home,support,aboutus]
function getCustomizerPage($pageName='home') {
    $blogInfo = array();
    $pageName = addslashes($pageName);
    
    // check whetehr plugin installed or not
    $seopluginCtrler = new SeoPluginsController();
    if ($seopluginCtrler->isPluginActive("customizer")) {
        $langCode = !empty($_SESSION['lang_code']) ? $_SESSION['lang_code'] : "en";
        $whereCond = "status=1 and link_page='$pageName'";
        $blogInfo = $seopluginCtrler->dbHelper->getRow("cust_blogs", $whereCond . " and lang_code='$langCode'");
        
        // empty blog and language is not en, check en content
        if (empty($blogInfo['id']) && ($langCode != 'en')) {
            $blogInfo = $seopluginCtrler->dbHelper->getRow("cust_blogs", $whereCond . " and lang_code='en'");
        }
        
        // if blog is not empty
        if (!empty($blogInfo['blog_content'])) {
            $blogInfo['blog_content'] = convertMarkdownToHtml($blogInfo['blog_content']);
        }
        
    }
    
    return $blogInfo;
    
}

// function to get customizer pages[guest,user,admin,top]
function getCustomizerMenu($menuName) {
	$controller = new Controller();		
	$custComp = $controller->createComponent("Customizer_Helper");
	return $custComp->getCustomizerMenu($menuName, $_SESSION['lang_code']);
}

function convertMarkdownToHtml($pageCont) {
    include_once(SP_LIBPATH."/Parsedown.php");
    $Parsedown = new Parsedown();
    $pageCont = $Parsedown->text($pageCont);
    return $pageCont;
}

function isPluginActivated($pluginName) {
	$seopluginCtrler = new SeoPluginsController();
	return $seopluginCtrler->isPluginActive($pluginName);
}

function formatNumber($number) {
	$number = str_replace([",", " "], "", trim($number));
	
	if (stristr($number, 'K')) {
		$number = str_replace("K", "", trim($number));
		$number = $number * 1000;
	}
	
	if (stristr($number, 'M')) {
		$number = str_replace("M", "", trim($number));
		$number = $number * 1000000;
	}
	
	if (stristr($number, 'B')) {
		$number = str_replace("B", "", trim($number));
		$number = $number * 1000000000;
	}
	
	return $number;
}

function showExportDiv($pdfLink, $csvLink, $printLink) {
    ?>
	<div class="export_div">
		<a href="<?php echo $pdfLink?>" target="_blank"><i class="fas fa-file-pdf"></i></a>
		<a href="<?php echo $csvLink?>"><i class="fas fa-file-csv"></i></a>
		<a target="_blank" href="<?php echo $printLink?>"><i class="fas fa-print"></i></a>
	</div>
    <?php
}

function timeElapsedString($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = array(
        'y' => $_SESSION['text']['label']['Year'],
        'm' => $_SESSION['text']['label']['Month'],
        'w' => $_SESSION['text']['label']['Week'],
        'd' => $_SESSION['text']['label']['Day'],
        'h' => $_SESSION['text']['label']['Hour'],
        'i' => $_SESSION['text']['label']['Minute'],
        's' => $_SESSION['text']['label']['Second'],
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' '. $_SESSION['text']['label']['Ago'] : $_SESSION['text']['label']['Just Now'];
}

function createSelectList($list, $nameCol = 'name', $idCol = 'id' ) {
    $newList = [];
    foreach ($list as $listInfo) {
        $newList[$listInfo[$idCol]] = ($nameCol== 'ALL') ? $listInfo : $listInfo[$nameCol];
    }
    
    return $newList;
}

function createValueList($list, $colName='id') {
    $newList = [];
    
    foreach ($list as $listInfo) {
        $newList[] = $listInfo[$colName];
    }
    
    return $newList;
}

function createSelectBoxFromList($optList, $nameAtr, $selectedVal, $emptySelectLabel="", $onChange="", $className='form-control') {
    $selectStr = "<select name='$nameAtr' id='$nameAtr' class='$className' onChange=\"$onChange\">";
    
    if (!empty($emptySelectLabel)) {
        $selectStr .= "<option value=''>-- $emptySelectLabel --</option>";
    }
    
    foreach($optList as $optVal => $optLabel) {
        $selected = ($optVal == $selectedVal) ? "selected" : "";
        $selectStr .= "<option value='$optVal' $selected>$optLabel</option>";
    }
    
    $selectStr .= "<select>";
    return $selectStr;
}

function createSelectBox($optList, $nameAtr, $selectedVal, $labelCol='name', $idCol='id', $emptySelectLabel="", $onChange="", $className='form-control') {
    
    $multipleAttr = "";
    if (stristr($nameAtr, ':')) {
        list($nameAtr, $extraAttr) = explode(':', $nameAtr);
        $multipleAttr = ($extraAttr == "multiple") ? 'multiple="multiple"' : "";
    }
    
    $selectStr = "<select name='$nameAtr' id='$nameAtr' $multipleAttr class='$className' onChange=\"$onChange\">";
    
    if (!empty($emptySelectLabel)) {
        $selectStr .= "<option value=''>-- $emptySelectLabel --</option>";
    }
    
    foreach($optList as $optInfo) {
        $optVal = $optInfo[$idCol];
        $optLabel = ucwords($optInfo[$labelCol]);
        $selected = ($optVal == $selectedVal) ? "selected" : "";
        $selectStr .= "<option value='$optVal' $selected>$optLabel</option>";
    }
    
    $selectStr .= "<select>";
    return $selectStr;
}
?>
