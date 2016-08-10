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

# class defines all view functions
class View extends Seopanel{

	function render($viewFile, $layout='default', $printContent=true){
		
		$spText = $_SESSION['text'];
		if(count($this->data) > 0){
			foreach ($this->data as $varName => $varValue){
				$$varName = $varValue;
			}
		}
		ob_start();
		include_once(SP_VIEWPATH."/".$viewFile.".ctp.php");
		$viewContent = ob_get_contents();
		ob_end_clean();
		
		ob_start();
		if($layout == 'ajax'){
			if($printContent){
				print $viewContent;
			}else{
				return $viewContent;
			}
		}else{
			
			$langCtrler = New LanguageController();
			$langList = $langCtrler->__getAllLanguages(" where translated=1");
			$userLang = empty($_SESSION['lang_code']) ? SP_DEFAULTLANG : $_SESSION['lang_code'];
			$redirectUrl = $langCtrler->getRedirectUrl();			
			
			include_once(SP_VIEWPATH."/layout/".$layout.".ctp.php");
		}
	}

	function getViewContent($viewFile){
		
		$spText = $_SESSION['text'];
		if(count($this->data) > 0){
			foreach ($this->data as $varName => $varValue){
				$$varName = $varValue;
			}
		}

		ob_start();
		include(SP_VIEWPATH."/".$viewFile.".ctp.php");
		$viewContent = ob_get_contents();
		ob_end_clean();
		return $viewContent;
	}

	#func to fetch the ctp file content
	function fetchViewFile($viewFile, $data=array()) {
		
		$spText = $_SESSION['text'];
		if(count($data) > 0){
			foreach ($data as $varName => $varValue){
				$$varName = $varValue;
			}
		}

		ob_start();
		include(SP_VIEWPATH."/".$viewFile.".ctp.php");
		$viewContent = ob_get_contents();
		ob_end_clean();
		return $viewContent;
	}
	
	# plugin render functions
	function pluginRender($viewFile, $layout='default', $printContent=true){
		
		$spText = $_SESSION['text'];
		$viewContent = $this->getPluginViewContent($viewFile);
		ob_start();
		if($layout == 'ajax'){
			if($printContent){
				print $viewContent;
			}else{
				return $viewContent;
			}
		}else{
			include(SP_VIEWPATH."/layout/".$layout.".ctp.php");
		}
	}

	function getPluginViewContent($viewFile){
		
		$spText = $_SESSION['text'];
		if(count($this->data) > 0){
			foreach ($this->data as $varName => $varValue){
				$$varName = $varValue;
			}
		}

		ob_start();
		include(PLUGIN_VIEWPATH."/".$viewFile.".ctp.php");
		$viewContent = ob_get_contents();
		ob_end_clean();
		return $viewContent;
	}
	
	#func to fetch the ctp file 
	function fetchFile($viewFile, $data=array()) {
		
		$spText = $_SESSION['text'];
		if(count($data) > 0){
			foreach ($data as $varName => $varValue){
				$$varName = $varValue;
			}
		}

		ob_start();
		include($viewFile);
		$viewContent = ob_get_contents();
		ob_end_clean();
		return $viewContent;
	}

}
?>