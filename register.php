<?php

include_once("includes/sp-load.php");
include_once(SP_CTRLPATH."/user.ctrl.php");
include_once(SP_CTRLPATH."/seoplugins.ctrl.php");
include_once(SP_CTRLPATH."/user-type.ctrl.php");
$controller = New UserController();

if(isLoggedIn() || !SP_USER_REGISTRATION){
	redirectUrl(SP_WEBPATH."/");
}

$controller->view->menu = 'register';

$controller->set('spTitle', 'Seo Tools: User Registration');
$controller->set('spDescription', 'Seo Tools: User Registration');
$controller->set('spKeywords', 'Seo tools User Registration');

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch($_POST['sec']){

		case "register":
			$controller->startRegistration();
			break;
	}

}else{
	switch($_GET['sec']){

		default:
			$controller->register();
			break;
	}
}

?>