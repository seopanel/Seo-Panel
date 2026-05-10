--
-- Seo Panel 6.0.0 changes
--
update `settings` set set_val='6.0.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable for Review Checker', 'SP_ENABLE_DFS_REVIEW', '1', 'dataforseo', 'bool', 1),
('Enable for SERP Checker', 'SP_ENABLE_DFS_SERP', '1', 'dataforseo', 'bool', 1);

--
-- Seo Panel API settings
--
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Seo Panel API URL', 'SP_SPAPI_URL', 'https://api.seopanel.org/api/v1', 'seopanel_api', 'large', 0),
('Seo Panel API Registered', 'SP_SPAPI_REGISTERED', '0', 'seopanel_api', 'bool', 0),
('API Key', 'SP_SPAPI_KEY', '', 'seopanel_api', 'large', 1),
('Email', 'SP_SPAPI_EMAIL', '', 'seopanel_api', 'large', 1),
('Name', 'SP_SPAPI_NAME', '', 'seopanel_api', 'large', 1),
('Enable for SERP Checker', 'SP_ENABLE_SPAPI_SERP', '1', 'seopanel_api', 'bool', 1);

ALTER TABLE `users` ADD COLUMN `spapi_skip` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `spapi_upgrade_skip_date` date DEFAULT NULL;

--
-- Table for storing pending DataForSEO tasks
--
CREATE TABLE IF NOT EXISTS `dfs_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL COMMENT 'review, serp, backlink, etc.',
  `platform` varchar(50) NOT NULL COMMENT 'google, trustpilot, tripadvisor, etc.',
  `ref_id` int(11) NOT NULL COMMENT 'Reference ID (review_link_id, keyword_id, etc.)',
  `ref_url` varchar(500) DEFAULT NULL COMMENT 'Original URL being checked',
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `report_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `category` (`category`),
  KEY `status` (`status`),
  KEY `report_date` (`report_date`),
  KEY `ref_id_category` (`ref_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- GDPR / RGPD cookie consent banner settings
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable GDPR/RGPD Cookie Consent Banner', 'SP_GDPR_COOKIE_BANNER', '0', 'system', 'bool', 1);



-- v6.1.0 changes
-- Store full SERP snapshot in searchresults
ALTER TABLE `searchresults` ADD COLUMN `serp_results` MEDIUMTEXT DEFAULT NULL;
