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

# class defines all customizer helper controller functions
class Customizer_Helper extends Controller{

	// function to get customizer pages[guest,user,admin,top]
	function getCustomizerMenu($menuName, $langCode = 'en') {
		$menuInfo = [];		
	
		// check whether plugin installed or not
		if (isPluginActivated("customizer")) {
			$menuName = addslashes($menuName);
			$langCode = addslashes($langCode);
			$custSiteInfo = getCustomizerDetails();
			
			// if custom menu enabled
			if (!empty($custSiteInfo['custom_menu'])) {
				$menuInfo = $this->dbHelper->getRow("cust_menu", "identifier='$menuName'");
				
				// if menu existing
				if (!empty($menuInfo['id'])) {
					$menuInfo['item_list'] = [];
					$whereCond = "status=1 and menu_id=" . $menuInfo['id'] . " order by priority asc, float_type asc";
					$menuItemList = $this->dbHelper->getAllRows("cust_menu_items", $whereCond);
					
					// loop through menu items
					foreach ($menuItemList as $menuItem) {
						
						if ($langCode != 'en') {
							$whereCond = "menu_item_id=" . $menuItem['id'] . " and lang_code='$langCode'";
							$menuTextInfo = $this->dbHelper->getRow("cust_menu_item_texts", $whereCond);
							$menuItem['label'] = !empty($menuTextInfo['content']) ? $menuTextInfo['content'] : $menuItem['name'];
						} else {
							$menuItem['label'] = $menuItem['name']; 
						}
						
						if(!stristr($menuItem['url'],'http://') && !stristr($menuItem['url'],'https://')) {
							$menuItem['url'] = SP_WEBPATH . "/" . $menuItem['url'];
						}
						
						$menuInfo['item_list'][] = $menuItem;
						
					}
					
				}
				
			}
	
		}
	
		return $menuInfo;
	
	}
	
	// function to get custom theme styles
	function getThemeCustomStyles($themeId, $type='css') {
		$style = "";
		$type = addslashes($type);
	
		// check whether plugin installed or not
		if (isPluginActivated("customizer")) {
		    $desc = ($type == 'css') ? "desc" : "asc"; 
			$whereCond = "theme_id=". intval($themeId) . " and status=1 and type='$type' order by priority $desc";
			$styleList = $this->dbHelper->getAllRows("cust_styles", $whereCond);
			
			foreach ($styleList as $styleInfo) {
				$style .= $styleInfo['style_content'] . "\n\n";
			}
			
		}
		
		return $style;
		
	}
		
}
?>