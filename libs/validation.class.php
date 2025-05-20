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

# class defines all validations functions
class Validation{
    
    var $flagErr;
    
    function __construct(){
        $this->Filters['email'] = "/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i" ;
        $this->Filters['number'] = "/^[0-9]+$/";
        $this->Filters['floatnumber'] = "/^\d+$|^\d+\.\d+$|^\.\d+$|^\-\d+$/";
        $this->Filters['phone'] = "/^[0-9\-\(\)\s\+]+$/";
        $this->Filters['startPhone'] = "/^[0-9\+\(]$/";
        $this->Filters['alpha']= "/^[a-zA-Z]+$/";
        $this->Filters['name']= "/^[a-zA-Z\'\-\s]+$/";
        $this->Filters['startName'] = "/^[a-zA-Z]$/";
        $this->Filters['nameGen'] = "/^[0-9a-zA-Z\-\s\'\!\@\#\$\%\^\&\*\(\)\-\_\+\?\.\:\;\[\]\/\,\"\=]+$/";
        $this->Filters['startGenName'] = $this->Filters['nameGen'];
        $this->Filters['uname'] = "/^[0-9a-zA-Z\-\_\.]+$/";
    }
    
    function getUniqueChars($entry){
        $arrVar = preg_split("//", $entry);
        return array_unique($arrVar);
    }
    
    function checkBlank($entry){
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkAlpha($entry){
        $entry = stripslashes(trim($entry));
        if (!preg_match($this->Filters['alpha'], $entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkUname($user_name){
        $user_name = stripslashes(trim($user_name));
        if(count($this->getUniqueChars($user_name)) <= 2){
            $msg = $_SESSION['text']['common']['entrynotvalid'];
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['uname'],$user_name)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(strlen($user_name) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkName($entry){
        $entry = stripslashes(trim($entry));
        if(!preg_match($this->Filters['name'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['startName'],$entry[0])){
            $msg = $_SESSION['text']['common']['Invalid value'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkLastName($entry){
        $entry = stripslashes(trim($entry));
        if(!preg_match($this->Filters['name'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['startName'],$entry[0])){
            exit;
            $msg = $_SESSION['text']['common']['Invalid value'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkEmail($entry){
        
        // check email using php function
        $entry = stripslashes(trim($entry));
        if (filter_var($entry, FILTER_VALIDATE_EMAIL) === false) {
            $msg = $_SESSION['text']['common']["Invalid email address entered"];
            $this->flagErr = true;
        }
        
        return $msg;
    }
    
    function checkPasswords($pass1, $pass2){
        if(strlen($pass1) < 6 || strlen($pass1) > 32){
            $msg = $_SESSION['text']['common']['password632'];
            $this->flagErr = true;
        }
        if($pass1!= $pass2){
            $msg = $_SESSION['text']['common']['passwordnotmatch'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkGenName($entry){
        $entry = stripslashes(trim($entry));
        if(count($this->getUniqueChars($entry)) <= 2){
            $msg = "The value doesnt seem to be valid";
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['nameGen'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['startGenName'],$entry[0])){
            $msg = $_SESSION['text']['common']['Invalid value'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        if(strval(floatval($entry)) == $entry){
            $msg = "The entry cant be a number.";
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['alpha'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkPhone($entry){
        if(count($this->getUniqueChars($entry)) <= 2){
            $msg = "The entry doesnt seem to be valid";
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['phone'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(!preg_match($this->Filters['startPhone'],$entry[0])){
            $msg = $_SESSION['text']['common']['Invalid value'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkZip($entry){
        $entry = stripslashes(trim($entry));
        if(!preg_match($this->Filters['number'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkNumber($entry){
        $entry = stripslashes(trim($entry));
        if(!preg_match($this->Filters['floatnumber'],$entry)){
            $msg = $_SESSION['text']['common']['Invalid characters'];
            $this->flagErr = true;
        }
        if(strlen($entry) == 0){
            $msg = $_SESSION['text']['common']['Entry cannot be blank'];
            $this->flagErr = true;
        }
        return $msg;
    }
    
    function checkUnameLength($name){
        if(strlen($name) < 6){
            $msg = "The username string should have a length atleast of 6";
            $this->flagErr = true;
        }elseif(!preg_match('/\d+/',$name)){
            $msg = "The username string should have a atleast a number";
            $this->flagErr = true;
        }elseif(!preg_match('/[a-zA-Z]+/',$name)){
            $msg = "The username string should have a atleast a letter";
            $this->flagErr = true;
        }
        return $msg;
    }
    
    # func to check captcha
    function checkCaptcha() {
        $msg = '';
        
        if (SP_ENABLE_RECAPTCHA && !empty(SP_RECAPTCHA_SITE_KEY) && !empty(SP_RECAPTCHA_SECRET_KEY)) {
            if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify?secret='.SP_RECAPTCHA_SECRET_KEY.'&response='.$_POST['g-recaptcha-response'];
                $spider = new Spider();
                $res = $spider->getContent($recaptchaUrl);
                $responseData = json_decode($res['page']);
                if(empty($responseData->success)) {
                    $msg = $_SESSION['text']['common']['reCAPTCHA verification failed'];
                    $this->flagErr = true;
                }
            } else {
                $msg = $_SESSION['text']['common']['reCAPTCHA verification failed'];
                $this->flagErr = true;
            }
        } else {
            if(!PhpCaptcha::Validate($_POST['code'])){
                $msg = $_SESSION['text']['common']["Invalid code entered"];
                $this->flagErr = true;
            }
        }
        
        return $msg;
    }
    
    # func to check date
    function checkDate($date, $delimiter = '-') {
        $msg = '';
        $dateElements = explode($delimiter, $date);
        
        // explode and check the number of elements
        if (count($dateElements) == 3) {
            if (checkdate($dateElements[1], $dateElements[2], $dateElements[0])) {
                return $msg;
            }
        }
        
        $msg = $_SESSION['text']['common']['Invalid characters'];
        $this->flagErr = true;
        return $msg;
    }
    
    function checkUrl($url) {
        $msg = '';
        
        $url = formatUrl($url, TRUE);
        if(strlen( $url) == 0){
            $msg = $_SESSION['text']['common']['Invalid Url'];
            $this->flagErr = true;
        }
        
        return $msg;
    }
}
?>