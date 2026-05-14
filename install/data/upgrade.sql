--
-- Seo Panel 6.1.0 changes
--
update `settings` set set_val='6.1.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

-- Store full SERP snapshot in searchresults
ALTER TABLE `searchresults` ADD COLUMN `serp_results` MEDIUMTEXT DEFAULT NULL;

-- Recommendations table
CREATE TABLE IF NOT EXISTS `sp_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('error','warning','todo') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'todo',
  `category` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci,
  `meta` text COLLATE utf8_unicode_ci,
  `refreshed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_user` (`website_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



-- Setup wizard columns and setting
ALTER TABLE `users` ADD COLUMN `setup_wizard_step` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `setup_wizard_dismissed` tinyint(1) NOT NULL DEFAULT 0;
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Initial Setup Wizard', 'SP_SETUP_WIZARD', '1', 'system', 'bool', 1);

-- Search volume results table (populated via SP API /v1/search-volume)
CREATE TABLE IF NOT EXISTS `keyword_search_volume` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `keyword_id` bigint unsigned NOT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'google',
  `sv_mapping_id` int DEFAULT NULL,
  `search_volume` int DEFAULT NULL,
  `cpc` decimal(10,2) DEFAULT NULL,
  `competition` float DEFAULT NULL,
  `keyword_difficulty` float DEFAULT NULL,
  `monthly_searches` text DEFAULT NULL,
  `crawled_result` text DEFAULT NULL,
  `last_crawl_status` varchar(20) DEFAULT 'pending',
  `crawled_time` datetime DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_keyword_source` (`keyword_id`, `source`),
  KEY `idx_keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Search volume feature toggles (DataForSEO and SP API)
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable for Search Volume', 'SP_ENABLE_DFS_SEARCH_VOLUME', '1', 'dataforseo', 'bool', 1),
('Enable for Search Volume', 'SP_ENABLE_SPAPI_SEARCH_VOLUME', '1', 'seopanel_api', 'bool', 1);
