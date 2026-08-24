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

-- AI Overview tracking: columns on searchresults (provider + AIO measurement)
ALTER TABLE `searchresults` ADD COLUMN `provider` VARCHAR(20) DEFAULT NULL COMMENT 'dataforseo, spapi; NULL for direct-crawl/legacy rows';
ALTER TABLE `searchresults` ADD COLUMN `aio_present` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `searchresults` ADD COLUMN `aio_cited` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `searchresults` ADD COLUMN `aio_async` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `searchresults` ADD COLUMN `aio_reference_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `searchresults` ADD COLUMN `aio_cited_position` SMALLINT UNSIGNED DEFAULT NULL;
ALTER TABLE `searchresults` ADD COLUMN `aio_supported` TINYINT(1) DEFAULT NULL COMMENT 'NULL=not measured, 0=provider cannot answer, 1=provider checked';
ALTER TABLE `searchresults` ADD COLUMN `aio_checked_at` DATETIME DEFAULT NULL COMMENT 'NULL means this row predates AI Overview tracking';
ALTER TABLE `searchresults` ADD COLUMN `aio_data_date` DATE DEFAULT NULL COMMENT 'freshness date of the AI Overview observation itself';

-- AI Overview tracking: citation detail table
CREATE TABLE IF NOT EXISTS `aio_references` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `keyword_id` int unsigned NOT NULL,
  `result_id` bigint unsigned DEFAULT NULL COMMENT 'FK to searchresults.id, if one exists',
  `checked_date` date NOT NULL,
  `ref_position` smallint unsigned NOT NULL COMMENT '1-based order in references array',
  `domain` varchar(255) NOT NULL,
  `url` varchar(2048) NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword_date` (`keyword_id`, `checked_date`),
  KEY `domain_date` (`domain`, `checked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Overview tracking settings
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('AI Overview reference retention (days)', 'SP_AIO_RETENTION_DAYS', '90', 'report', 'small', 1),
('AI Overview rolling window (observations)', 'SP_AIO_ROLLING_WINDOW', '7', 'report', 'small', 1),
('AI Overview data considered stale after (days)', 'SP_AIO_STALE_DAYS', '7', 'report', 'small', 1),
('AI Overview subdomain match policy (registrable or exact)', 'SP_AIO_SUBDOMAIN_MATCH', 'registrable', 'report', 'small', 1);

-- AI Overview tracking UI labels
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'keyword', 'AI Overview', 'AI Overview'),
('en', 'keyword', 'Cited', 'Cited'),
('en', 'keyword', 'Sources', 'Sources'),
('en', 'keyword', 'Present', 'Present'),
('en', 'keyword', 'Absent', 'Absent'),
('en', 'keyword', 'Not available', 'Not available'),
('en', 'keyword', 'Yes', 'Yes'),
('en', 'keyword', 'No', 'No'),
('en', 'keyword', 'stale', 'stale'),
('en', 'keyword', 'present in', 'present in'),
('en', 'keyword', 'of last observations', 'of last observations'),
('en', 'keyword', 'AI Overview is not available on your current data source', 'AI Overview is not available on your current data source.'),
('en', 'keyword', 'Configure DataForSEO credentials to enable this feature immediately', 'Configure DataForSEO credentials to enable this feature immediately.'),
('en', 'keyword', 'Data older than the configured freshness threshold', 'Data older than the configured freshness threshold'),
('en', 'keyword', 'AI Overview Cited Sources', 'AI Overview Cited Sources'),
('en', 'keyword', 'No AI Overview citations recorded for this keyword yet', 'No AI Overview citations recorded for this keyword yet.');

-- Quick Keyword Position Checker: distinguish "SP API archive hasn't crawled
-- this keyword yet" from a genuine zero-match result
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'keyword', 'SEO Panel API is still processing this keyword', 'SEO Panel API is still processing this keyword. Please check back in a few minutes.');
