<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	           *
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

# class defines all index controller functions
class IndexController extends Controller{
	
	# index function
	function index($searchInfo=[]) {
		$spTextHome = $this->getLanguageTexts('home', $_SESSION['lang_code']);
		$this->set('spTextHome', $spTextHome);
		$this->set('post', $searchInfo);
		if(isLoggedIn()){
			$this->render('user/userhome');
		}else{
			$this->render('home');
		}
	}
	
	# show login form
	function showLoginForm(){		
		$this->render('common/login');
	}
	
	# function to show support page
	function showSupport() {
		$this->set('spTextSupport', $this->getLanguageTexts('support', $_SESSION['lang_code']));
		$this->render('support');
	}
	
}
?>