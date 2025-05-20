--
-- Seo Panel 3.3.0 changes
--
INSERT INTO `settings`(`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES 
('Crawl relative links in a page', 'SP_RELATIVE_LINK_CRAWL', '1', 'siteauditor', 'bool', '1');

UPDATE `searchengines` SET 
`regex` = '<li.*?class="?g.*?<a.*?href="\\/url\\?q=(.*?)&amp;sa=U.*?>(.*?)<\\/a>.*?<\\/div><span.*?>(.*?)<\\/span>' 
WHERE `url` LIKE '%google%';

--
-- Table structure for table `featured_directories`
--
DROP TABLE IF EXISTS `featured_directories`;
CREATE TABLE IF NOT EXISTS `featured_directories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `directory_name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `directory_link` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `google_pagerank` smallint(6) NOT NULL DEFAULT '0',
  `coupon_code` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `coupon_offer` float NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `featured_directories`
--

INSERT INTO `featured_directories` (`id`, `directory_name`, `directory_link`, `google_pagerank`, `coupon_code`, `coupon_offer`, `status`) VALUES
(1, 'directory.seopanel.in', 'http://directory.seofreetools.net/submit.php?LINK_TYPE=featured', 4, '', 0, 1),
(2, 'directorypage.org', 'http://directorypage.org/submit.php?LINK_TYPE=4', 3, '', 0, 1),
(3, 'directorybook.net', 'http://directorybook.net/submit.php?LINK_TYPE=4', 3, '', 0, 1),
(4, 'beta-i.org', 'http://beta-i.org/submit.php?LINK_TYPE=4', 6, '', 0, 1);


--
-- Table structure for table `themes`
--
DROP TABLE IF EXISTS `themes`;
CREATE TABLE IF NOT EXISTS `themes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `folder` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `version` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `installed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `folder`, `author`, `description`, `version`, `website`, `status`, `installed`) VALUES
(1, 'Classic', 'classic', 'Geo Varghese', 'Classic theme of Seo Panel', '1.0.0', 'https://www.seopanel.org/theme/l/1/classic/', 1, 1),
(2, 'Simple', 'simple', 'Geo Varghese', 'Simple theme of Seo Panel', '1.0.0', 'https://www.seopanel.org/theme/l/2/simple/', 0, 1);
