--
-- Table structure for table `auditorpagelinks`
--

CREATE TABLE IF NOT EXISTS `auditorpagelinks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `report_id` bigint(20) NOT NULL,
  `link_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `link_anchor` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `link_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `nofollow` tinyint(1) NOT NULL DEFAULT '0',
  `extrenal` tinyint(1) NOT NULL DEFAULT '0',
  `brocken` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `auditorprojects`
--

CREATE TABLE IF NOT EXISTS `auditorprojects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) NOT NULL,
  `max_links` int(11) NOT NULL DEFAULT '500',
  `exclude_links` text COLLATE utf8_unicode_ci NOT NULL,
  `check_pr` tinyint(1) NOT NULL DEFAULT '0',
  `check_backlinks` tinyint(1) NOT NULL DEFAULT '0',
  `check_indexed` tinyint(1) NOT NULL DEFAULT '0',
  `store_links_in_page` tinyint(1) NOT NULL DEFAULT '0',
  `check_brocken` tinyint(1) NOT NULL DEFAULT '0',
  `score` float NOT NULL DEFAULT '0',
  `cron` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `auditorreports`
--

CREATE TABLE IF NOT EXISTS `auditorreports` (
  `id` bigint(24) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `page_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `page_title` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `page_description` text COLLATE utf8_unicode_ci NOT NULL,
  `page_keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `pagerank` smallint(6) NOT NULL DEFAULT '0',
  `google_backlinks` int(11) NOT NULL DEFAULT '0',
  `yahoo_backlinks` int(11) NOT NULL DEFAULT '0',
  `bing_backlinks` int(11) NOT NULL DEFAULT '0',
  `google_indexed` int(11) NOT NULL DEFAULT '0',
  `yahoo_indexed` int(11) NOT NULL DEFAULT '0',
  `bing_indexed` int(11) NOT NULL DEFAULT '0',
  `total_links` int(11) NOT NULL DEFAULT '0',
  `external_links` int(11) NOT NULL DEFAULT '0',
  `brocken` tinyint(1) NOT NULL DEFAULT '0',
  `crawled` tinyint(1) NOT NULL DEFAULT '0',
  `score` smallint(6) NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id_2` (`project_id`,`page_url`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


--
-- Table structure for table `keywordcrontracker`
--

CREATE TABLE IF NOT EXISTS `keywordcrontracker` (
  `keyword_id` int(16) DEFAULT NULL,
  `searchengine_id` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  KEY `keyword_id` (`keyword_id`),
  KEY `searchengine_id` (`searchengine_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- To save reciprocal url under website 
--
ALTER TABLE `websites` ADD `reciprocal_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `description5`; 


--
-- Table structure for table `di_directory_meta`
--

DROP TABLE IF EXISTS `di_directory_meta`;
CREATE TABLE IF NOT EXISTS `di_directory_meta` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `captcha_script` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT 'captcha.php',
  `search_script` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT 'index.php?q=[--keyword--]',
  `title_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'TITLE',
  `url_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'URL',
  `description_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'DESCRIPTION',
  `name_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'OWNER_NAME',
  `email_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'OWNER_EMAIL',
  `category_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'CATEGORY_ID',
  `cptcha_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'CAPTCHA',
  `imagehash_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'IMAGEHASH',
  `imagehashurl_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'imagehash',
  `reciprocal_col` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'RECPR_URL',
  `extra_val` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'LINK_TYPE=normal&submit=Continue&AGREERULES=on',
  `link_type_col` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'LINK_TYPE',
  `normal` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal',
  `free` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'free',
  `reciprocal` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'reciprocal',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `di_directory_meta`
--

INSERT INTO `di_directory_meta` (`id`, `name`, `captcha_script`, `search_script`, `title_col`, `url_col`, `description_col`, `name_col`, `email_col`, `category_col`, `cptcha_col`, `imagehash_col`, `imagehashurl_col`, `reciprocal_col`, `extra_val`, `link_type_col`, `normal`, `free`, `reciprocal`, `status`) VALUES
(1, 'phpLD', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=[--type--]&submit=Continue&AGREERULES=on', 'LINK_TYPE', 'normal', 'free', 'reciprocal', 1);


--
-- Table structure for table `seotools`
--

DROP TABLE IF EXISTS `seotools`;
CREATE TABLE IF NOT EXISTS `seotools` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_section` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_access` tinyint(1) NOT NULL DEFAULT '1',
  `reportgen` tinyint(1) NOT NULL DEFAULT '1',
  `cron` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `seotools`
--

INSERT INTO `seotools` (`id`, `name`, `url_section`, `user_access`, `reportgen`, `cron`, `status`) VALUES
(1, 'Keyword Position Checker', 'keyword-position-checker', 1, 1, 1, 1),
(2, 'Site Auditor', 'site-auditor', 1, 1, 0, 1),
(3, 'Rank Checker', 'rank-checker', 1, 1, 1, 1),
(4, 'Backlinks Checker', 'backlink-checker', 1, 1, 1, 1),
(5, 'Directory Submission', 'directory-submission', 1, 1, 0, 1),
(6, 'Search Engine Saturation', 'saturation-checker', 1, 1, 1, 1);


--
-- To remove some unwanted values from system settings interface
--
ALTER TABLE `settings` ADD `display` BOOL NOT NULL DEFAULT '1';
ALTER TABLE `settings` ADD `set_category` VARCHAR( 32 ) NOT NULL DEFAULT 'system' AFTER `set_val`; 
UPDATE `settings` SET `display` = '0' WHERE `settings`.`id` =7;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Maximum number of pages allowed per website', 'SA_MAX_NO_PAGES', '500', 'siteauditor', 'small', 1),
('Site auditor crawl delay', 'SA_CRAWL_DELAY_TIME', '20', 'siteauditor', 'small', 1),
('Maximum length of page title', 'SA_TITLE_MAX_LENGTH', '80', 'siteauditor', 'small', 0),
('Minimum length of page title', 'SA_TITLE_MIN_LENGTH', '50', 'siteauditor', 'small', 0),
('Maximum length of meta description', 'SA_DES_MAX_LENGTH', '200', 'siteauditor', 'small', 0),
('Minimumlength of meta description', 'SA_DES_MIN_LENGTH', '120', 'siteauditor', 'small', 0),
('Maximum length of meta keywords', 'SA_KEY_MAX_LENGTH', '200', 'siteauditor', 'small', 0),
('Minimumlength of meta keyword', 'SA_KEY_MIN_LENGTH', '80', 'siteauditor', 'small', 0),
('Google pagerank check level first', 'SA_PR_CHECK_LEVEL_FIRST', '3', 'siteauditor', 'small', 0),
('Backlinks check level', 'SA_BL_CHECK_LEVEL', '25', 'siteauditor', 'small', 0),
('Google pagerank check level second', 'SA_PR_CHECK_LEVEL_SECOND', '6', 'siteauditor', 'small', 0),
('Maximum links in a page', 'SA_TOTAL_LINKS_MAX', '50', 'siteauditor', 'small', 0);


--
-- Table structure for table `directories`
--

DROP TABLE IF EXISTS `directories`;
CREATE TABLE IF NOT EXISTS `directories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `submit_url` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `captcha_script` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT 'captcha.php',
  `search_script` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT 'index.php?q=[--keyword--]',
  `title_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'TITLE',
  `url_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'URL',
  `description_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'DESCRIPTION',
  `name_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'OWNER_NAME',
  `email_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'OWNER_EMAIL',
  `category_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'CATEGORY_ID',
  `cptcha_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'CAPTCHA',
  `imagehash_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'IMAGEHASH',
  `imagehashurl_col` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT 'imagehash',
  `reciprocal_col` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'RECPR_URL',
  `extra_val` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'LINK_TYPE=normal&submit=Continue&AGREERULES=on',
  `is_captcha` tinyint(1) NOT NULL DEFAULT '0',
  `working` tinyint(1) NOT NULL DEFAULT '1',
  `google_pagerank` smallint(6) NOT NULL DEFAULT '0',
  `alexa_rank` int(11) NOT NULL DEFAULT '-1',
  `lang_code` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `script_type_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `submit_url` (`submit_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=161 ;

--
-- Dumping data for table `directories`
--

INSERT INTO `directories` (`id`, `domain`, `submit_url`, `captcha_script`, `search_script`, `title_col`, `url_col`, `description_col`, `name_col`, `email_col`, `category_col`, `cptcha_col`, `imagehash_col`, `imagehashurl_col`, `reciprocal_col`, `extra_val`, `is_captcha`, `working`, `google_pagerank`, `alexa_rank`, `lang_code`, `checked`, `script_type_id`) VALUES
(1, 'http://directory.seopanel.in', 'http://directory.seopanel.in/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(2, 'http://www.fat64.net', 'http://www.fat64.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(3, 'http://www.onpaco.com', 'http://www.onpaco.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 1, -1, 'en', 1, 1),
(4, 'http://www.bluefootbuys.com', 'http://www.bluefootbuys.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 2, -1, 'en', 1, 1),
(5, 'http://www.777media.com', 'http://www.777media.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(6, 'http://www.freewebsitedirectory.com', 'http://www.freewebsitedirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(7, 'http://www.mygreencorner.com', 'http://www.mygreencorner.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 0, 5, -1, 'en', 1, 1),
(8, 'http://www.webhotlink.com', 'http://www.webhotlink.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 3, -1, 'en', 1, 1),
(9, 'http://www.skypemedia.com', 'http://www.skypemedia.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(10, 'http://www.directoryvault.com', 'http://www.directoryvault.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 4, -1, 'en', 1, 1),
(11, 'http://www.pop-net.org', 'http://www.pop-net.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 3, -1, 'en', 1, 1),
(12, 'http://www.123hitlinks.info', 'http://www.123hitlinks.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(13, 'http://www.pr3plus.com', 'http://www.pr3plus.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(14, 'http://www.tfwd.org', 'http://www.tfwd.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(15, 'http://www.ewebdir.com', 'http://www.ewebdir.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(16, 'http://www.cheapdirectory.net', 'http://www.cheapdirectory.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(17, 'http://www.alistsites.com', 'http://www.alistsites.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 4, -1, 'en', 1, 1),
(18, 'http://www.miriblack.com', 'http://www.miriblack.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(19, 'http://www.domaining.in', 'http://www.domaining.in/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(20, 'http://www.sanory.com', 'http://www.sanory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(21, 'http://www.aaawebdirectory.com', 'http://www.aaawebdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(22, 'http://www.pblake.com', 'http://www.pblake.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(23, 'http://www.1abc.org', 'http://www.1abc.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(24, 'http://www.therobot.info', 'http://www.therobot.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(25, 'http://www.yvir.com', 'http://www.yvir.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 1, -1, 'en', 1, 1),
(26, 'http://www.triplewdirectory.com', 'http://www.triplewdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(27, 'http://www.linkedout.info', 'http://www.linkedout.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(28, 'http://www.seocourt.com', 'http://www.seocourt.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(29, 'http://www.placeyourlinks.com', 'http://www.placeyourlinks.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(30, 'http://www.clickmybrick.com', 'http://www.clickmybrick.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 4, -1, 'en', 1, 1),
(31, 'http://directory.allaboutadtips.com', 'http://directory.allaboutadtips.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(32, 'http://directory.seoexecutive.com', 'http://directory.seoexecutive.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(33, 'http://www.hitalyzer.com', 'http://www.hitalyzer.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 3, -1, 'en', 1, 1),
(34, 'http://www.linkspremium.com', 'http://www.linkspremium.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(35, 'http://www.crazyleafdesign.com/webdirectory', 'http://www.crazyleafdesign.com/webdirectory/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(36, 'http://www.generalwebdirectory.info', 'http://www.generalwebdirectory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 1, -1, 'en', 1, 1),
(37, 'http://www.freelistingdirectory.info', 'http://www.freelistingdirectory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.freelistingdirectory.info', 1, 1, 0, -1, 'en', 1, 1),
(38, 'http://www.webdirectory1.info', 'http://www.webdirectory1.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(39, 'http://www.nuclearland.com', 'http://www.nuclearland.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(40, 'http://www.webslink.info', 'http://www.webslink.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(41, 'http://www.omega-link.co.uk', 'http://www.omega-link.co.uk/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(42, 'http://www.indiabusinessdirectory.info', 'http://www.indiabusinessdirectory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(43, 'http://www.gainweb.org', 'http://www.gainweb.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(44, 'http://www.goobz.biz', 'http://www.goobz.biz/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(45, 'http://www.magdalyns.com', 'http://www.magdalyns.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(46, 'http://www.directorybin.com', 'http://www.directorybin.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(47, 'http://www.deblinley.com', 'http://www.deblinley.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(48, 'http://www.jhucr.org', 'http://www.jhucr.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(49, 'http://www.boomdirectory.com', 'http://www.boomdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 1, -1, 'en', 1, 1),
(50, 'http://www.doubledirectory.com', 'http://www.doubledirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 4, -1, 'en', 1, 1),
(51, 'http://www.lutonengineering.com', 'http://www.lutonengineering.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(52, 'http://www.sblinks.org', 'http://www.sblinks.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 1, -1, 'en', 1, 1),
(53, 'http://www.mymaxlinks.org', 'http://www.mymaxlinks.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(54, 'http://www.holidaydig.com', 'http://www.holidaydig.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(55, 'http://www.yrkdirectory.com', 'http://www.yrkdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(56, 'http://www.addyourlnksnow.com', 'http://www.addyourlnksnow.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(57, 'http://www.submitsitenow.info', 'http://www.submitsitenow.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(58, 'http://www.directoryrank.net', 'http://www.directoryrank.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(59, 'http://www.webdiro.com', 'http://www.webdiro.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 3, -1, 'en', 1, 1),
(60, 'http://www.aokdirectory.com', 'http://www.aokdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(61, 'http://www.tkdirectory.com', 'http://www.tkdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(62, 'http://www.jambezi.com', 'http://www.jambezi.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.jambezi.com', 0, 1, 0, -1, 'en', 1, 1),
(63, 'http://www.greatdir.net', 'http://www.greatdir.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 1, -1, 'en', 1, 1),
(64, 'http://www.insectuniverse.com', 'http://www.insectuniverse.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.insectuniverse.com', 0, 1, 0, -1, 'en', 1, 1),
(65, 'http://www.gkiv.com', 'http://www.gkiv.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(66, 'http://www.haqj.com', 'http://www.haqj.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(67, 'http://www.owdirectory.com', 'http://www.owdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(68, 'http://www.picna.com', 'http://www.picna.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.picna.com', 0, 1, 0, -1, 'en', 1, 1),
(69, 'http://www.w4directory.com', 'http://www.w4directory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(70, 'http://www.towerpromote.com', 'http://www.towerpromote.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(71, 'http://www.increasedirectory.com', 'http://www.increasedirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(72, 'http://www.thelivinglink.net', 'http://www.thelivinglink.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 4, -1, 'en', 1, 1),
(73, 'http://www.mytopdirectory.info', 'http://www.mytopdirectory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(74, 'http://www.seoname.com', 'http://www.seoname.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(75, 'http://www.businesssitesonthenet.co.uk', 'http://www.businesssitesonthenet.co.uk/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(76, 'http://www.counterdeal.com', 'http://www.counterdeal.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(77, 'http://www.mymaxlinks.info', 'http://www.mymaxlinks.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(78, 'http://www.prolificpi.com', 'http://www.prolificpi.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.prolificpi.com', 1, 1, 0, -1, 'en', 1, 1),
(79, 'http://www.migliana.com', 'http://www.migliana.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.migliana.com', 0, 1, 0, -1, 'en', 1, 1),
(80, 'http://www.tuenschel.com', 'http://www.tuenschel.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.tuenschel.com', 0, 1, 0, -1, 'en', 1, 1),
(81, 'http://www.hrce.com', 'http://www.hrce.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(82, 'http://www.lemurgene.com', 'http://www.lemurgene.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(83, 'http://www.biowatchmed.net', 'http://www.biowatchmed.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(84, 'http://www.gfxmedia.us', 'http://www.gfxmedia.us/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(85, 'http://www.ns8.biz', 'http://www.ns8.biz/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(86, 'http://www.coolwebsitelistings.com', 'http://www.coolwebsitelistings.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(87, 'http://www.freewd.org', 'http://www.freewd.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 4, -1, 'en', 1, 1),
(88, 'http://www.agrieducation.org', 'http://www.agrieducation.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(89, 'http://www.liveurls.net', 'http://www.liveurls.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(90, 'http://www.zzdirectory.com', 'http://www.zzdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(91, 'http://www.homessearchengine.com', 'http://www.homessearchengine.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 1, -1, 'en', 1, 1),
(92, 'http://www.teacherslounge.info', 'http://www.teacherslounge.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(93, 'http://www.yournetdirectory.com', 'http://www.yournetdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(94, 'http://z0p.com', 'http://z0p.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 2, -1, 'en', 1, 1),
(95, 'http://www.babelea.org', 'http://www.babelea.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.babelea.org', 0, 1, 2, -1, 'en', 1, 1),
(96, 'http://www.etup.org', 'http://www.etup.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(97, 'http://www.ggfb.org', 'http://www.ggfb.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(98, 'http://www.jordangreen.info', 'http://www.jordangreen.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 1, -1, 'en', 1, 1),
(99, 'http://www.linkfly.info', 'http://www.linkfly.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=free&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(100, 'http://www.thehdb.com', 'http://www.thehdb.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(101, 'http://www.zerotres.com', 'http://www.zerotres.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(102, 'http://www.anyweblist.com', 'http://www.anyweblist.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.anyweblist.com', 1, 1, 0, -1, 'en', 1, 1),
(103, 'http://www.cheapwebdir.com', 'http://www.cheapwebdir.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(104, 'http://www.directoryfreelink.com', 'http://www.directoryfreelink.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.directoryfreelink.com', 0, 1, 0, -1, 'en', 1, 1),
(105, 'http://www.directoryseofriendly.com', 'http://www.directoryseofriendly.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 1, -1, 'en', 1, 1),
(106, 'http://www.freeonlineindex.com', 'http://www.freeonlineindex.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.freeonlineindex.com', 0, 1, 0, -1, 'en', 1, 1),
(107, 'http://www.freeweblist.info', 'http://www.freeweblist.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(108, 'http://www.generalnetdirectory.com', 'http://www.generalnetdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(109, 'http://www.heydirectory.com', 'http://www.heydirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(110, 'http://www.inlinkdirectory.com', 'http://www.inlinkdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.inlinkdirectory.com', 0, 1, 0, -1, 'en', 1, 1),
(111, 'http://www.monsterlinkdirectory.com', 'http://www.monsterlinkdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.monsterlinkdirectory.com', 1, 1, 3, -1, 'en', 1, 1),
(112, 'http://www.netdirectorylink.com', 'http://www.netdirectorylink.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(113, 'http://www.searchpowertour.com', 'http://www.searchpowertour.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.searchpowertour.com', 0, 1, 0, -1, 'en', 1, 1),
(114, 'http://www.seolistsite.com', 'http://www.seolistsite.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.seolistsite.com', 0, 1, 0, -1, 'en', 1, 1),
(115, 'http://www.urlvault.info', 'http://www.urlvault.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.urlvault.info', 0, 1, 0, -1, 'en', 1, 1),
(116, 'http://www.websitelists.info', 'http://www.websitelists.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(117, 'http://www.worldurllink.com', 'http://www.worldurllink.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.worldurllink.com', 0, 1, 0, -1, 'en', 1, 1),
(118, 'http://www.netwerker.com', 'http://www.netwerker.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.netwerker.com', 1, 1, 5, -1, 'en', 1, 1),
(119, 'http://www.netnamesindex.com', 'http://www.netnamesindex.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.netnamesindex.com', 0, 1, 1, -1, 'en', 1, 1),
(120, 'http://www.edirectori.com', 'http://www.edirectori.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(121, 'http://www.stepmind.com', 'http://www.stepmind.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(122, 'http://www.devdir.biz', 'http://www.devdir.biz/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.devdir.biz', 0, 1, 3, -1, 'en', 1, 1),
(123, 'http://www.findinfo.ws', 'http://www.findinfo.ws/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(124, 'http://www.vccllc.com', 'http://www.vccllc.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(125, 'http://www.freeonlineregister.com', 'http://www.freeonlineregister.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.freeonlineregister.com', 0, 1, 0, -1, 'en', 1, 1),
(126, 'http://www.thecommercialdirectory.com', 'http://www.thecommercialdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(127, 'http://www.visuallinkdirectory.com', 'http://www.visuallinkdirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 1, -1, 'en', 1, 1),
(128, 'http://directorywind.com', 'http://directorywind.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(129, 'http://www.directorydr.com', 'http://www.directorydr.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(130, 'http://www.seodir.eu', 'http://www.seodir.eu/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(131, 'http://www.corporategoof.com', 'http://www.corporategoof.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 1, -1, 'en', 1, 1),
(132, 'http://www.easyadsworld.com', 'http://www.easyadsworld.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(133, 'http://www.lexormedia.com', 'http://www.lexormedia.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 1, -1, 'en', 1, 1),
(134, 'http://www.minidirectory.info', 'http://www.minidirectory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(135, 'http://www.extreme-directory.net', 'http://www.extreme-directory.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(136, 'http://www.faceahead.com', 'http://www.faceahead.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(137, 'http://www.firstfindengine.com', 'http://www.firstfindengine.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=reciprocal&submit=Continue&AGREERULES=on&RECPR_URL=http://www.firstfindengine.com', 0, 1, 0, -1, 'en', 1, 1),
(138, 'http://www.go2directory.info', 'http://www.go2directory.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(139, 'http://www.interactive-directory.com', 'http://www.interactive-directory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(140, 'http://www.w3c-software.com', 'http://www.w3c-software.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(141, 'http://www.wishdc.org', 'http://www.wishdc.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(142, 'http://www.westcelt.org', 'http://www.westcelt.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(143, 'http://www.webdirectorybook.com', 'http://www.webdirectorybook.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(144, 'http://www.oroop.com', 'http://www.oroop.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(145, 'http://www.listasweb.info', 'http://www.listasweb.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 2, -1, 'en', 1, 1),
(146, 'http://www.sblinks.info', 'http://www.sblinks.info/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(147, 'http://www.thehopedirectory.com', 'http://www.thehopedirectory.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(148, 'http://www.clocktowerstudio.com', 'http://www.clocktowerstudio.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 3, -1, 'en', 1, 1),
(149, 'http://www.infotechwv.com', 'http://www.infotechwv.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(150, 'http://www.dawsoneng.com', 'http://www.dawsoneng.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 2, -1, 'en', 1, 1),
(151, 'http://www.cafrid.com', 'http://www.cafrid.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(152, 'http://www.fusionsalcedo.com', 'http://www.fusionsalcedo.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1);
INSERT INTO `directories` (`id`, `domain`, `submit_url`, `captcha_script`, `search_script`, `title_col`, `url_col`, `description_col`, `name_col`, `email_col`, `category_col`, `cptcha_col`, `imagehash_col`, `imagehashurl_col`, `reciprocal_col`, `extra_val`, `is_captcha`, `working`, `google_pagerank`, `alexa_rank`, `lang_code`, `checked`, `script_type_id`) VALUES
(153, 'http://www.justaskluke.com', 'http://www.justaskluke.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 0, -1, 'en', 1, 1),
(154, 'http://www.logha.com', 'http://www.logha.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(155, 'http://www.pacdec.org', 'http://www.pacdec.org/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(156, 'http://www.sitechakra.com', 'http://www.sitechakra.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(157, 'http://www.webbacklinks.com', 'http://www.webbacklinks.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 0, 0, -1, 'en', 1, 1),
(158, 'http://www.searchnsearch.com', 'http://www.searchnsearch.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(159, 'http://www.interwebindex.com', 'http://www.interwebindex.com/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 1, 1, 0, -1, 'en', 1, 1),
(160, 'http://www.o53.net', 'http://www.o53.net/submit.php', 'captcha.php', 'index.php?q=[--keyword--]', 'TITLE', 'URL', 'DESCRIPTION', 'OWNER_NAME', 'OWNER_EMAIL', 'CATEGORY_ID', 'CAPTCHA', 'IMAGEHASH', 'imagehash', 'RECPR_URL', 'LINK_TYPE=normal&submit=Continue&AGREERULES=on', 0, 1, 1, -1, 'en', 1, 1);


--
-- To fix the issues with google parsing
--
ALTER TABLE `searchengines` ADD `from_pattern` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `regex`;
ALTER TABLE `searchengines` ADD `to_pattern` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `from_pattern`;
update searchengines set from_pattern='<div id="ires">',to_pattern='<\\/ol>' where  url LIKE '%google%';

--
-- To fix yahoo search engine url issue
--
update searchengines set regex='<li.*?<h3><a.*?\\*\\*(.*?)".*?>(.*?)<\\/a><\\/h3>.*?<div.*?>(.*?)<\\/div>.*?<\\/span>',url_index=1,title_index=2,description_index=3 where url LIKE '%yahoo%';

--
-- Changes for search engine like yandex 
--
ALTER TABLE searchengines ADD `start_offset` INT( 8 ) NOT NULL DEFAULT '0' AFTER `start`;
UPDATE `searchengines` SET `start_offset` = `no_of_results_page`;

--
-- Fix for google SE in 3.0.0
--

UPDATE searchengines SET from_pattern='<div id="?ires"?>' where  url LIKE '%google%';
