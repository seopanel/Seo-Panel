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

include_once 'phpuri.php';

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
	$msg = empty($msg) ? $_SESSION['text']['common']['No Records Found']."!" : $msg;
	$data['colspan'] = $colspan;
	$data['msg'] = $msg;
	$data['plain'] = $plain;
	return @View::fetchViewFile('common/norecords', $data);
}

# func to show error msg
function showErrorMsg($errorMsg, $exit=true) {
	$data['errorMsg'] = $errorMsg;
	print @View::fetchViewFile('common/error', $data);
	if($exit) exit;
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
    $turl = trim($url);
    $limit = 1;
    if(strpos($turl, 'http://') === 0){
	$turl = str_replace('http://', '', $turl, $limit);
    }
    if(strpos($turl, 'https://') === 0){
	$turl = str_replace('https://', '', $turl, $limit);
    }
	if ($removeWWW && strpos($turl, 'www.') === 0){
            $turl = str_replace('www.', '', $turl, $limit);
        }
	return $turl;
}

function formatRelativeUrl($url,$base_url){
    $base = phpUri::parse($base_url);
    $r = $base->join($url);
    if($r == $url){
        return FALSE;
    }else{
        return $r;
    }
}

function formatDate($date) {
	$date = str_replace("0000-00-00", "", $date);
	return $date;
}

function addHttpToUrl($url){
	if(!stristr($url, 'http://') && !stristr($url, 'https://')){
		$url = 'http://'.$url;
	}
	return $url;
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
	if($count<=0){
		redirectUrl(SP_WEBPATH."/admin-panel.php?sec=newweb");
	}
}

# function to create plugin ajax get method
function pluginGETMethod($args='', $area='content'){
	$script = "seo-plugins.php?pid=".PLUGIN_ID;	
	$request = "scriptDoLoad('$script', '$area', '$args')";
	return $request;
}

# function to create plugin ajax post method
function pluginPOSTMethod($formName, $area='content', $args=''){
	$args = "&pid=".PLUGIN_ID."&$args";
	$request = "scriptDoLoadPost('seo-plugins.php', '$formName', '$area', '$args')";
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
		if (!stristr($referer, 'install')) {
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
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    	<title></title>
    	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
		<script type="text/javascript">
			<?php if ($doPrint) { ?>
				window.print();
			<?php }?>
		</script>		
	    <style type="text/css">
		    BODY{background-color:white;padding:50px 10px;}
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
    ?>
    <div style="clear: both; margin-top: 10px;"><?php echo str_replace('[year]', date('Y'), $spText['common']['copyright'])?></div>
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
	
	# check whether the mail send by smtp or not
	if(SP_SMTP_MAIL){
		$mail->IsSMTP();	
		$mail->SMTPAuth = true;
		$mail->Host = SP_SMTP_HOST;
		$mail->Username = SP_SMTP_USERNAME;
		$mail->Password = SP_SMTP_PASSWORD;
		$mail->Port = SP_SMTP_PORT;
	}

	$mail->From = $from;
	$mail->FromName = $fromName;
	$mail->AddAddress($to);
	$mail->WordWrap = 70;                              
	$mail->IsHTML(true);

	$mail->Subject = $subject;
	$mail->Body = $content;
	
	// if attachments are there
	if (!empty($attachment)) {
		$mail->AddAttachment($attachment);
	}
	
	if(!$mail->Send()){
		return 0;
	}else{
		return 1;
	}
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
	
	include_once(SP_LIBPATH . "/mpdf/mpdf.php");
	$mpdf = new mPDF();
	$mpdf->useAdobeCJK = true;
	$mpdf->SetAutoFont(AUTOFONT_ALL);
	$spider = new Spider();
	$ret = $spider->getContent(SP_CSSPATH . "/screen.css");
	$stylesheet = str_replace("../../../images", SP_IMGPATH, $ret['page']);
	$mpdf->WriteHTML($stylesheet,1);
	$mpdf->SetDisplayMode('fullpage');
	$mpdf->WriteHTML($content, 2);
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
	$copyrightTxt = str_replace("www.seopanel.in", "<a href='http://www.seopanel.in'>www.seopanel.in</a>", $spText['common']['copyright']);
    ?>
    <div style="clear: both; margin-top: 30px;font-size: 12px; text-align: center;"><?php echo str_replace('[year]', date('Y'), $copyrightTxt)?></div>
	<?php
}

function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    global $hooks;
    return $hooks->add_filter($tag, $function_to_add, $priority, $accepted_args);
}

function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
    global $hooks;
    return $hooks->remove_filter( $tag, $function_to_remove, $priority );
}

function remove_all_filters($tag, $priority = false) {
    global $hooks;
    return $hooks->remove_all_filters($tag, $priority);
}

function has_filter($tag, $function_to_check = false) {
    global $hooks;
    return $hooks->has_filter($tag, $function_to_check);
}

function apply_filters($tag, $value) {
    global $hooks;
    return $hooks->apply_filters($tag, $value);
}

function apply_filters_ref_array($tag, $args) {
    global $hooks;
    return $hooks->apply_filters_ref_array($tag, $args);
}

function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    global $hooks;
    return $hooks->add_action($tag, $function_to_add, $priority, $accepted_args);
}

function has_action($tag, $function_to_check = false) {
    global $hooks;
    return $hooks->has_action($tag, $function_to_check);
}

function remove_action( $tag, $function_to_remove, $priority = 10 ) {
    global $hooks;
    return $hooks->remove_action( $tag, $function_to_remove, $priority);
}

function remove_all_actions($tag, $priority = false) {
    global $hooks;
    return $hooks->remove_all_actions($tag, $priority);
}

function do_action($tag, $arg = '') {
    global $hooks;
    return $hooks->do_action($tag, $arg);
}

function do_action_ref_array($tag, $args) {
    global $hooks;
    return $hooks->do_action_ref_array($tag, $args);
}

function did_action($tag) {
    global $hooks;
    return $hooks->did_action($tag);
}

function current_filter() {
    global $hooks;
    return $hooks->current_filter();
}

function current_action() {
    global $hooks;
    return $hooks->current_action();
}

function doing_filter( $filter = null ) {
    global $hooks;
    return $hooks->doing_filter( $filter);
}

function doing_action( $action = null ) {
    global $hooks;
    return $hooks->doing_action( $action);
}

function add_setting($set_name,$set_val,$set_label,$set_category = 'ign_misc',$set_type = 'text',$display=1){
    global $sp_db;
    if(empty($set_name) || empty($set_label) || empty($set_category)){
        return False;
    }
    if(isset($set_val)){
        $set_val = maybe_serialize($set_val);
    }
    $data = Array ("set_val" => $set_val,
                   "set_label" => $set_label,
                   "set_category" => $set_category,
                   "set_type" => $set_type,
                   "display" => $display,
    );
    $sp_db->where ('set_name', $set_name);
    $id = $sp_db->getValue('settings','id');
    if($id){
        return FALSE;
    }else{
        $data['set_name'] = $set_name;
        $res = $sp_db->insert ('settings', $data);
    }
    if($res){
        return TRUE;
    }else{
        return FALSE;
    }
}

function update_setting($set_name,$data = array()){
    global $sp_db;
    if(empty($set_name) || !is_array($data)){
        return False;
    }
    if(isset($data['set_val'])){
        $data['set_val'] = maybe_serialize($data['set_val']);
    }
    $data_labels = Array ("set_val" => '',
                   "set_label" => '',
                   "set_category" => 'ign_misc',
                   "set_type" => 'text',
                   "display" => 1
    );
    $sp_db->where ('set_name', $set_name);
    $id = $sp_db->getValue('settings','id');
    $data_update = array();
    if($id){
        foreach($data_labels as $k => $v){
            if(isset($data[$k]) && !empty($data[$k])){
                $data_update[$k] = $data[$k];
            }
        }
       $sp_db->where ('id', $id);
        $res = $sp_db->update ('settings', $data_update);
    }else{
        if(empty($data['set_label']) || empty($data['set_category'])){
            return False;
        }
        foreach($data_labels as $k => $v){
            if(isset($data[$k]) && !empty($data[$k])){
                $data_update[$k] = $data[$k];
            }else{
                $data_update[$k] = $v;
            }
        }
        $data_update['set_name'] = $set_name;
        $res = $sp_db->insert ('settings', $data_update);
    }
    if($res){
        return TRUE;
    }else{
        return FALSE;
    }
}

function get_setting($set_name){
    global $sp_db;
    $sp_db->where ('set_name', $set_name);
    $res = $sp_db->getOne('settings');
    $res['set_val'] = maybe_unserialize($res['set_val']);
    return $res;
}

function get_setting_value($set_name){
    if(defined($set_name)){
        if(version_compare(phpversion(),'5.6') >= 0){
            return constant($set_name);
        }else{
            return maybe_unserialize(constant($set_name));
        }
    }else{
        global $sp_db;
        $sp_db->where ('set_name', $set_name);
        $res = $sp_db->getValue('settings','set_val');
        return maybe_unserialize($res);
    }
}

function register_score($tag,$score_object){
    global $scores;
    $default_types = array('report','website');
    $add_types = apply_filters('score_types', array());
    $score_types = array_merge($default_types,$add_types);
    if(!is_array($scores)){
        $scores = array();
    }
    foreach($score_types as $k){
        if(!isset($scores[$k]) || !is_array($scores[$k])){
            $scores[$k] = array();
        }
    }
    if(is_subclass_of($score_object,'Score',FALSE)){
        $type = $score_object->get_type();
        $tag = $score_object->get_tag();
    }else{
        return FALSE;
    }
    if(key_exists($type, $scores) && !key_exists($tag, $scores[$type])){
        $scores[$type][$tag] = $score_object;
        return TRUE;
    }
    return FALSE;
}

function getProjectInfo($projectId){
    global $sp_db;
    $sp_db->where("p.website_id=w.id");
    $sp_db->where("p.id", $projectId);
    $cols = array("p.*","w.url","w.name");
    $products = $sp_db->get("websites w, auditorprojects p", null, $cols);   
    if (!empty($products)){
        return $products[0];
    }else{
        return NULL;
    }
}
?>