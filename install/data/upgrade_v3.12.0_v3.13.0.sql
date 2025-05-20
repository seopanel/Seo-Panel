--
-- Seo Panel 3.13.0 changes
--

update `settings` set set_val='3.13.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE `currency` SET `symbol` = '£' WHERE `currency`.`id` =25;

UPDATE `searchengines` SET `regex` = '<div.*?class="?g.*?><h3 class="r"><a href="(.*?)".*?>(.*?)<\\/a>.*?<\\/div><span.*?>(.*?)<\\/span>' WHERE `url` LIKE '%google%';

INSERT INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Send custom header with curl request', 'SP_SEND_CUSTOM_HEADER_IN_CURL', '1', 'report', 'bool', 1);

--
-- Quick web proxy plugin
--

INSERT INTO `seoplugins` (`label`, `name`, `author`, `description`, `version`, `website`, `status`, `installed`) VALUES
('Quick Web Proxy', 'QuickWebProxy', 'Seo Panel', 'It will help you to create a web proxy server using your hosting server or external proxy servers', '1.0.0', 'https://www.seopanel.org/plugin/l/94/quick-web-proxy/', 1, 1);

CREATE TABLE IF NOT EXISTS `qwp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `set_name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `set_val` text COLLATE utf8_unicode_ci NOT NULL,
  `set_type` enum('small','bool','medium','large','text') CHARACTER SET latin1 DEFAULT 'small',
  PRIMARY KEY (`id`),
  UNIQUE KEY `set_name` (`set_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `qwp_settings`
--

INSERT INTO `qwp_settings` (`set_label`, `set_name`, `set_val`, `set_type`) VALUES
('Allow user to access the web proxy', 'QWP_ALLOW_USER_WEB_PROXY', '0', 'bool'),
('Allow web server to act as a proxy', 'QWP_ALLOW_WEB_SERVER_ACT_AS_PROXY', '1', 'bool')
ON DUPLICATE KEY UPDATE `set_type`=`set_type`;
