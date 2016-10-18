<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)			   *
 *   sendtogeo@gmail.com   						   						   *
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

/*
 * the extra config variable of seo panel  
 */

# The seo panel plugin directory
define('SP_PLUGINDIR', 'plugins');

# The seo panel plugin config file
define('SP_PLUGINCONF', 'conf.php');

# The seo panel plugin db file
define('SP_PLUGINDBFILE', 'database.sql');

# The seo panel plugin menu file
define('SP_PLUGINMENUFILE', 'menu.ctp.php');

# seo panel is demo system or not
define('SP_DEMO', 0);

# seo panel language testing mode
define('SP_LANGTESTING', 0);

# seo panel multiple cron execution possible same day
define('SP_MULTIPLE_CRON_EXEC', 1);

# seo panel plugin info xml file
define('SP_PLUGININFOFILE', 'plugin.xml');

# seo panel theme info xml file
define('SP_THEMEINFOFILE', 'theme.xml');

# seo panel plugin site info
define('SP_PLUGINSITE', 'http://www.seopanel.in/plugins/');

# The seo panel plugin upgrade db file
define('SP_PLUGINUPGRADEFILE', 'upgrade.sql');

# The seo panel pagination default
define('SP_PAGINGNO_DEFAULT', 10);

# prevent sql injection
define('SP_PREVENT_SQL_INJECTION', true);

# database persistent connection
define('SP_DB_PERSISTENT_CONNECTION', false);

# The seo panel api file
define('SP_API_FILE', 'api/api.php');

# The crawl log clear interval in days
define('SP_CRAWL_LOG_CLEAR_TIME', 90);

# The api language code
define('SP_API_LANG_CODE', 'en');

# The seo panel help page
define('SP_HELP_LINK', 'http://help.seopanel.in/');

# The seo panel forum page
define('SP_FORUM_LINK', 'http://forum.seopanel.in/');

# The seo panel support page
define('SP_SUPPORT_LINK', 'http://support.seofreetools.net/');

# The seo panel contact page
define('SP_CONTACT_LINK', 'http://www.seopanel.in/contact/');

# The seo panel donate page
define('SP_DONATE_LINK', 'http://www.seopanel.in/donate/');

# The seo panel download page
define('SP_DOWNLOAD_LINK', 'http://www.seopanel.in/download/');

# The seo panel demo page
define('SP_DEMO_LINK', 'http://www.seopanel.in/demo/');

# The seo panel news page
define('SP_NEWS_PAGE', 'http://www.seopanel.in/news.php');

# The seo panel sponsors
define('SP_SPONSOR_PAGE', 'http://www.seopanel.in/sponsors.php');

# The seo panel version page
define('SP_VERSION_PAGE', 'http://www.seopanel.in/getversion.php');

# seo panel theme site url
define('SP_THEMESITE', 'http://www.seopanel.in/themes/');

# payment related variables
define('SP_PAYMENT_CANCEL_LINK', SP_WEBPATH."/payment_cancel.php");
define('SP_PAYMENT_RETURN_LINK', SP_WEBPATH."/payment_return.php");
?>
