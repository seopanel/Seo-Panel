<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	           *
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

# super class defines all seo panel functions
class Seopanel{
	
	var $data;
	
	# function load seo panel
	function loadSeoPanel() {
		
		# include main classes
		include_once(SP_LIBPATH.'/session.class.php');
		include_once(SP_LIBPATH.'/controller.class.php');
		include_once(SP_LIBPATH.'/view.class.php');
		include_once(SP_LIBPATH.'/validation.class.php');
		include_once(SP_LIBPATH.'/spider.class.php');
		include_once(SP_LIBPATH.'/paging.class.php');
		include_once(SP_LIBPATH.'/pchart.class.php');
		include_once(SP_LIBPATH.'/pdata.class.php');
		include_once(SP_LIBPATH.'/xmlparser.class.php');		
		include_once(SP_LIBPATH.'/captcha.class.php');		
		include_once(SP_LIBPATH.'/phpmailer.class.php');		
		include_once(SP_LIBPATH.'/smtp.class.php');
		@Session::startSession();
		
		# include common functions		
		include_once(SP_INCPATH.'/sp-common.php');
		
		# include coomon controllers classes
		include_once(SP_CTRLPATH.'/country.ctrl.php');
		include_once(SP_CTRLPATH.'/language.ctrl.php');
		include_once(SP_CTRLPATH.'/website.ctrl.php');
		include_once(SP_CTRLPATH.'/user.ctrl.php');
		include_once(SP_CTRLPATH.'/user-type.ctrl.php');
		include_once(SP_CTRLPATH.'/settings.ctrl.php');
		include_once(SP_CTRLPATH."/crawllog.ctrl.php");
		include_once(SP_CTRLPATH.'/timezone.ctrl.php');
		include_once(SP_CTRLPATH.'/searchengine.ctrl.php');	
		include_once(SP_CTRLPATH.'/currency.ctrl.php');	
		include_once(SP_CTRLPATH.'/seoplugins.ctrl.php');
		
	}	
	
	# to set variable to render
	function set($varName, $varValue){
		$this->controller->set($varName, $varValue);
	}
	
}
?>
