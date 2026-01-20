--
-- Seo Panel 5.1.0 changes
--
update `settings` set set_val='5.1.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

ALTER TABLE `auditorreports` ADD `ai_robot_allowed` TINYINT(1) NOT NULL DEFAULT '1' AFTER `spam_score`;
ALTER TABLE `auditorreports` ADD `mobile_friendly` TINYINT(1) NOT NULL DEFAULT '1' AFTER `ai_robot_allowed`;
ALTER TABLE `auditorreports` ADD `https_secure` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mobile_friendly`;
ALTER TABLE `auditorreports` ADD `has_og_tags` TINYINT(1) NOT NULL DEFAULT '0' AFTER `https_secure`;
ALTER TABLE `auditorreports` ADD `has_twitter_cards` TINYINT(1) NOT NULL DEFAULT '0' AFTER `has_og_tags`;
ALTER TABLE `auditorreports` ADD `blocked_by_robots` TINYINT(1) NOT NULL DEFAULT '0' AFTER `has_twitter_cards`;

INSERT INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Use Sample API Data (for testing - saves API credits)', 'SP_USE_SAMPLE_API_DATA', '0', 'system', 'bool', 1);

INSERT INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Exclude file extensions (comma-separated)', 'SA_EXCLUDE_FILE_EXTENSIONS', 'zip,gz,tar,png,jpg,jpeg,gif,mp3,flv,pdf,m4a,avi,mov,wmv,mp4,doc,docx,xls,xlsx,ppt,pptx,rar,7z,exe,dmg,iso', 'siteauditor', 'text', 1);

ALTER TABLE `auditorprojects` ADD `exclude_extensions` TEXT COLLATE utf8_unicode_ci NULL AFTER `exclude_links`;

ALTER TABLE `auditorreports` ADD `canonical_url` VARCHAR(2000) COLLATE utf8_unicode_ci NULL AFTER `page_url`;
ALTER TABLE `auditorreports` ADD `discovered_via` ENUM('crawl','sitemap','robots','canonical','import') DEFAULT 'crawl' AFTER `canonical_url`;

CREATE TABLE IF NOT EXISTS `auditorsitemaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `sitemap_url` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `sitemap_type` ENUM('xml','txt','index') DEFAULT 'xml',
  `last_parsed` timestamp NULL,
  `urls_found` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `auditorpagelinks` CHANGE `link_url` `link_url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
CHANGE `link_anchor` `link_anchor` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
CHANGE `link_title` `link_title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `auditorreports` CHANGE `page_url` `page_url` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, 
CHANGE `canonical_url` `canonical_url` VARCHAR(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, 
CHANGE `page_title` `page_title` VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, 
CHANGE `page_description` `page_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, 
CHANGE `page_keywords` `page_keywords` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE auditorprojects ADD COLUMN sitemap_url VARCHAR(500) NULL AFTER exclude_extensions;
