<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
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

# class defines all session functions
class Session extends Seopanel{

	# starts session
	public static function startSession(){
		ini_set("session.gc_probability", 100);
		ini_set("session.gc_divisor", 100);
		ini_set("session.gc_maxlifetime", SP_TIMEOUT);

		// harden the session cookie - previously left entirely to the
		// server's php.ini defaults, which are httponly=0/samesite=""
		// out of the box on most distros. HttpOnly blocks JS
		// (document.cookie) from ever reading the session id, closing
		// off token theft via any XSS that might slip through; SameSite=
		// Lax stops the cookie being sent on a cross-site POST, blunting
		// CSRF further on top of the token-based defenses already in
		// place. Secure is only set when this request actually arrived
		// over HTTPS - forcing it unconditionally would silently break
		// every login on an install still served over plain HTTP.
		if (!headers_sent()) {
			$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
			session_set_cookie_params([
				'lifetime' => 0,
				'path' => '/',
				'domain' => '',
				'secure' => $isHttps,
				'httponly' => true,
				'samesite' => 'Lax',
			]);
		}

		session_start();
	}
	
	# to set session
	public static function setSession($varName, $varValue){
		$_SESSION[$varName] = $varValue;
	}

	# function read session
	public static function readSession($varName) {
		return $_SESSION[$varName];
	}

	# fucntion to destroy session
	public static function destroySession() {	    
		@Session::setSession('userInfo', "");
		@Session::setSession('lang_code', "");
		@Session::setSession('text', "");
        session_destroy();	    
	}
	
	public static function setSessionMessages($msg, $error=true) {
	    $sessionVar = $error ? "sp_error_msg" : "sp_success_msg";
	    $_SESSION[$sessionVar] = $msg;
	}
	
	public static function showSessionMessges($exit=false) {
	    if (!empty($_SESSION['sp_success_msg'])) {
	        showSuccessMsg($_SESSION['sp_success_msg'], $exit);
	        $_SESSION['sp_success_msg'] = "";
	    }
	    
	    if (!empty($_SESSION['sp_error_msg'])) {
	        showErrorMsg($_SESSION['sp_error_msg'], $exit);
	        $_SESSION['sp_error_msg'] = "";
	    }
	}
	
}
?>