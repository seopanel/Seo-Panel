--
-- Seo Panel 6.1.0 changes
--
update `settings` set set_val='7.0.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

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

-- Setup wizard columns and setting. Hidden (display=0) for this version -
-- not ready to expose on the System Settings page yet.
ALTER TABLE `users` ADD COLUMN `setup_wizard_step` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `setup_wizard_dismissed` tinyint(1) NOT NULL DEFAULT 0;
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Initial Setup Wizard', 'SP_SETUP_WIZARD', '0', 'system', 'bool', 0);

-- 'settings'-category label for the above, kept even while hidden so it's
-- ready whenever this is switched back to display=1 in a future version.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_SETUP_WIZARD', 'Initial Setup Wizard');

-- Daily login version-upgrade notice popup: skip-for-today column, same
-- convention as spapi_upgrade_skip_date - see
-- SettingsController::showVersionUpgradePopup().
ALTER TABLE `users` ADD COLUMN `version_upgrade_skip_date` date DEFAULT NULL;

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

-- 'settings'-category labels for the above, for existing installs upgrading
-- via this file alone (textlang.sql already carries these but isn't
-- necessarily re-imported on every upgrade).
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_ENABLE_DFS_SEARCH_VOLUME', 'Enable for Search Volume'),
('en', 'settings', 'SP_ENABLE_SPAPI_SEARCH_VOLUME', 'Enable for Search Volume');

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
('AI Overview reference retention (days)', 'SP_AIO_RETENTION_DAYS', '90', 'report', 'medium', 1),
('AI Overview rolling window (observations)', 'SP_AIO_ROLLING_WINDOW', '7', 'report', 'medium', 1),
('AI Overview data considered stale after (days)', 'SP_AIO_STALE_DAYS', '7', 'report', 'medium', 1),
('AI Overview subdomain match policy (registrable or exact)', 'SP_AIO_SUBDOMAIN_MATCH', 'registrable', 'report', 'medium', 1);

-- 'settings'-category labels for the above - were missing, which made all 4
-- render with a blank label on the admin's Report Settings page (same class
-- of bug fixed for SP_AI_INSIGHTS_EMAIL_NOTIFICATION and SP_SETUP_WIZARD).
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_AIO_RETENTION_DAYS', 'AI Overview reference retention (days)'),
('en', 'settings', 'SP_AIO_ROLLING_WINDOW', 'AI Overview rolling window (observations)'),
('en', 'settings', 'SP_AIO_STALE_DAYS', 'AI Overview data considered stale after (days)'),
('en', 'settings', 'SP_AIO_SUBDOMAIN_MATCH', 'AI Overview subdomain match policy (registrable or exact)');

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

-- AI Visibility tool (Phase 1: AI referral tracking via JS snippet)
CREATE TABLE IF NOT EXISTS `ai_visibility_sites` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ai_referrals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `hit_date` date NOT NULL,
  `platform` varchar(64) NOT NULL,
  `url_path` varchar(2048) NOT NULL,
  `url_hash` binary(16) NOT NULL,
  `hits` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_date_platform_url` (`website_id`,`hit_date`,`platform`,`url_hash`),
  KEY `site_date` (`website_id`,`hit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ai_platforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(64) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `display_name` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- fixed-window rate-limit counters for the public beacon endpoint; pruned
-- opportunistically alongside ai_referrals retention on the existing cron
CREATE TABLE IF NOT EXISTS `ai_visibility_rate_limit` (
  `bucket_key` varchar(100) NOT NULL,
  `window_start` int unsigned NOT NULL,
  `hit_count` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`bucket_key`,`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `ai_platforms` (`platform`,`hostname`,`display_name`,`is_active`) VALUES
('chatgpt','chatgpt.com','ChatGPT',1),
('chatgpt','chat.openai.com','ChatGPT',1),
('perplexity','perplexity.ai','Perplexity',1),
('claude','claude.ai','Claude',1),
('gemini','gemini.google.com','Gemini',1),
('copilot','copilot.microsoft.com','Copilot',1),
('you','you.com','You.com',1),
('poe','poe.com','Poe',1),
('grok','grok.com','Grok',1),
('mistral','mistral.ai','Mistral',1);

-- seotools has no unique key on url_section, so guard the insert manually
INSERT INTO `seotools` (`name`,`url_section`,`user_access`,`reportgen`,`cron`,`priority`,`status`)
SELECT 'AI Visibility','ai-visibility',1,0,0,5,1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `seotools` WHERE `url_section`='ai-visibility');

-- AI Visibility should lead the menu, ahead of Keyword Position Checker (priority 10)
UPDATE `seotools` SET `priority`=5 WHERE `url_section`='ai-visibility';

-- Backfill the missing per-usertype access grant for seotool_9..12
-- (Social Media Checker, Website Analytics, Review Manager, AI
-- Visibility). Every SEO tool is deny-by-default unless a usertype has
-- an explicit user_specs row for it - seotools 1-8 were seeded that way
-- from the original install, but 9-12 never were, on either a fresh
-- install or an upgrade. Net effect: every non-admin customer has been
-- unable to see or use any of these 4 tools, including the one this
-- feature specifically shipped to attract AI-era customers. Grants each
-- tool to any usertype that already has seotool_1 (the same "this
-- account gets the SEO tools" signal every non-locked-down account
-- already carries), and only where no explicit row exists yet for the
-- new column - so an admin who has since manually toggled one of these
-- off for a specific usertype is left alone, and re-running this file is
-- a no-op.
INSERT INTO `user_specs` (`user_type_id`, `spec_column`, `spec_value`, `spec_category`)
SELECT us.user_type_id, 'seotool_9', '1', 'system'
FROM `user_specs` us
WHERE us.spec_column = 'seotool_1' AND us.spec_value = '1'
AND NOT EXISTS (SELECT 1 FROM `user_specs` us2 WHERE us2.user_type_id = us.user_type_id AND us2.spec_column = 'seotool_9');

INSERT INTO `user_specs` (`user_type_id`, `spec_column`, `spec_value`, `spec_category`)
SELECT us.user_type_id, 'seotool_10', '1', 'system'
FROM `user_specs` us
WHERE us.spec_column = 'seotool_1' AND us.spec_value = '1'
AND NOT EXISTS (SELECT 1 FROM `user_specs` us2 WHERE us2.user_type_id = us.user_type_id AND us2.spec_column = 'seotool_10');

INSERT INTO `user_specs` (`user_type_id`, `spec_column`, `spec_value`, `spec_category`)
SELECT us.user_type_id, 'seotool_11', '1', 'system'
FROM `user_specs` us
WHERE us.spec_column = 'seotool_1' AND us.spec_value = '1'
AND NOT EXISTS (SELECT 1 FROM `user_specs` us2 WHERE us2.user_type_id = us.user_type_id AND us2.spec_column = 'seotool_11');

INSERT INTO `user_specs` (`user_type_id`, `spec_column`, `spec_value`, `spec_category`)
SELECT us.user_type_id, 'seotool_12', '1', 'system'
FROM `user_specs` us
WHERE us.spec_column = 'seotool_1' AND us.spec_value = '1'
AND NOT EXISTS (SELECT 1 FROM `user_specs` us2 WHERE us2.user_type_id = us.user_type_id AND us2.spec_column = 'seotool_12');

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('AI referral data retention (days)','AIV_REFERRAL_RETENTION_DAYS','365','aivisibility','small',1),
('Rate limit per site token (requests/min)','AIV_RATE_LIMIT_PER_TOKEN','120','aivisibility','small',1),
('Rate limit per source IP (requests/min)','AIV_RATE_LIMIT_PER_IP','60','aivisibility','small',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'AIV_REFERRAL_RETENTION_DAYS', 'AI referral data retention (days)'),
('en', 'settings', 'AIV_RATE_LIMIT_PER_TOKEN', 'Rate limit per site token (requests/min)'),
('en', 'settings', 'AIV_RATE_LIMIT_PER_IP', 'Rate limit per source IP (requests/min)'),
('en', 'seotools', 'ai-visibility', 'AI Visibility'),
('en', 'seotools', 'AI Visibility', 'AI Visibility'),
('en', 'seotools', 'Setup', 'Setup'),
('en', 'seotools', 'AI Referral Report', 'AI Referral Report'),
('en', 'aivisibility', 'AI Visibility', 'AI Visibility'),
('en', 'aivisibility', 'Privacy note', 'No cookies, no localStorage, no visitor identifiers are ever stored - only that a visit arrived from a given AI platform to a given page. Data stays on your own server.'),
('en', 'aivisibility', 'Install snippet', 'Install snippet'),
('en', 'aivisibility', 'snippetinstructions', 'Paste this snippet just before the closing </body> tag on every page of your site.'),
('en', 'aivisibility', 'Waiting for first hit', 'Waiting for first hit...'),
('en', 'aivisibility', 'Receiving data', 'Receiving data'),
('en', 'aivisibility', 'floornotice', 'Some AI clients strip or omit the referrer, and native mobile apps often send nothing - treat these counts as a floor, not a complete measure.'),
('en', 'aivisibility', 'WordPress note', 'WordPress:'),
('en', 'aivisibility', 'wordpressinstructions', 'Paste the snippet using a header/footer plugin (e.g. Insert Headers and Footers), or your theme''s footer.php.'),
('en', 'aivisibility', 'AI Referral Report', 'AI Referral Report'),
('en', 'aivisibility', 'Platform breakdown', 'Platform breakdown'),
('en', 'aivisibility', 'Platform', 'Platform'),
('en', 'aivisibility', 'Referrals', 'Referrals'),
('en', 'aivisibility', 'Top landing pages', 'Top landing pages'),
('en', 'aivisibility', 'Page', 'Page'),
('en', 'aivisibility', 'Referrals over time', 'Referrals over time');

-- AI Visibility "AI Overview" tab: website-level view of existing AI
-- Overview presence/citation data (searchresults.aio_* + aio_references),
-- no new ingest - reuses what the AI Overview Tracking feature collects
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'seotools', 'AI Overview', 'AI Overview'),
('en', 'aivisibility', 'AI Overview', 'AI Overview'),
('en', 'aivisibility', 'Measured Keywords', 'Measured Keywords'),
('en', 'aivisibility', 'Cited Keywords', 'Cited Keywords'),
('en', 'aivisibility', 'Domain', 'Domain'),
('en', 'aivisibility', 'Citations', 'Citations'),
('en', 'aivisibility', 'Competitor domains cited in your AI Overviews', 'Competitor domains cited in your AI Overviews'),
('en', 'aivisibility', 'you', 'you');

-- Zero-Setup Scheduler, Phase 1 pre-work: locking + timing/failure
-- instrumentation for cron.php. No queue yet - this only makes the
-- existing execution model observable and safe against overlapping
-- invocations, which today has neither (see spec discovery notes).
CREATE TABLE IF NOT EXISTS `cron_run_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trigger_source` varchar(20) NOT NULL DEFAULT 'cli',
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `duration_ms` int unsigned DEFAULT NULL,
  `status` enum('running','completed','incomplete') NOT NULL DEFAULT 'running',
  `websites_processed` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cron_job_timing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `run_id` bigint unsigned NOT NULL,
  `website_id` int unsigned NOT NULL,
  `url_section` varchar(100) NOT NULL,
  `started_at` datetime NOT NULL,
  `duration_ms` int unsigned NOT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `run_id` (`run_id`),
  KEY `url_section_started` (`url_section`,`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Zero-Setup Scheduler, Phase 1 proper: resumable job queue. Chunk rows are
-- recycled (re-armed pending -> completed -> pending) rather than inserted
-- fresh per cron cycle, so this table stays bounded by (websites x tools)
-- plus in-flight keyword/link chunks rather than growing per day.
CREATE TABLE IF NOT EXISTS `job_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `url_section` varchar(100) NOT NULL,
  `chunk_key` varchar(191) NOT NULL,
  `payload` text,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `attempts` tinyint unsigned NOT NULL DEFAULT 0,
  `max_attempts` tinyint unsigned NOT NULL DEFAULT 4,
  `available_at` datetime NOT NULL,
  `claimed_at` datetime DEFAULT NULL,
  `claimed_by_run_id` bigint unsigned DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `last_error` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_chunk` (`website_id`,`url_section`,`chunk_key`),
  KEY `claim_lookup` (`website_id`,`url_section`,`status`,`available_at`),
  KEY `run_id` (`claimed_by_run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rollout flag: old monolithic *Cron() bodies vs. new enqueue+drain
-- bodies, selected per tool inside routeCronJob(). Originally defaulted
-- off for existing installs pending confirmation the job_queue path was
-- clean; the queued path has been the DEFAULT for every fresh install
-- (seopanel.sql) since, and is now covered end to end by spTests -
-- flipped on here too. INSERT IGNORE means this only affects an install
-- upgrading through this file for the first time (never run either path
-- yet) - an install that already stored '0' from an earlier upgrade run
-- keeps that value; this is not retroactive.
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable resumable job queue for cron execution', 'SP_JOB_QUEUE_ENABLED', '1', 'report', 'small', 0);

-- Zero-Setup Scheduler, Phase 2: secret-protected external ping trigger.
-- Fails closed by default - disabled, and no secret (settings can't
-- generate randomness at install time; the Scheduler Health page's
-- "Generate secret" button does that via bin2hex(random_bytes(16))).
-- display=0 on all three - managed from the health page, not the generic
-- settings grid, same reasoning as SP_NUMBER_KEYWORDS_CRON.
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable external ping trigger for cron', 'SP_CRON_PING_ENABLED', '0', 'report', 'bool', 0),
('Ping trigger secret key', 'SP_CRON_PING_SECRET', '', 'report', 'medium', 0),
('Ping-triggered run budget (seconds)', 'SP_JOB_QUEUE_BUDGET_SECONDS', '20', 'report', 'small', 0);

-- Scheduler Health dashboard + ping trigger card i18n (category 'panel',
-- matching the existing Cron Command page's texts)
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'panel', 'Scheduler Health', 'Scheduler Health'),
('en', 'panel', 'A cron run is currently in progress', 'A cron run is currently in progress'),
('en', 'panel', 'Last run', 'Last run'),
('en', 'panel', 'websites processed', 'websites processed'),
('en', 'panel', 'No cron runs recorded yet', 'No cron runs recorded yet'),
('en', 'panel', 'Recent runs', 'Recent runs'),
('en', 'panel', 'Websites', 'Websites'),
('en', 'panel', 'Per-tool activity (last 7 days)', 'Per-tool activity (last 7 days)'),
('en', 'panel', 'Tool', 'Tool'),
('en', 'panel', 'Success', 'Success'),
('en', 'panel', 'Failed', 'Failed'),
('en', 'panel', 'Avg duration', 'Avg duration'),
('en', 'panel', 'No activity recorded in the last 7 days', 'No activity recorded in the last 7 days'),
('en', 'panel', 'Job queue backlog', 'Job queue backlog'),
('en', 'panel', 'Count', 'Count'),
('en', 'panel', 'Oldest pending since', 'Oldest pending since'),
('en', 'panel', 'Queue is empty', 'Queue is empty'),
('en', 'panel', 'Recently failed chunks', 'Recently failed chunks'),
('en', 'panel', 'Chunk', 'Chunk'),
('en', 'panel', 'Error', 'Error'),
('en', 'panel', 'When', 'When'),
('en', 'panel', 'External ping trigger', 'External ping trigger'),
('en', 'panel', 'pingtriggerdesc', 'Point an external cron/uptime service (or your own crontab) at this URL to trigger short, budget-limited cron runs - useful on hosts where you can''t set up a real system cron job.'),
('en', 'panel', 'Enable ping trigger', 'Enable ping trigger'),
('en', 'panel', 'Budget (seconds)', 'Budget (seconds)'),
('en', 'panel', 'No secret generated yet - generate one below before enabling the ping trigger.', 'No secret generated yet - generate one below before enabling the ping trigger.'),
('en', 'panel', 'Regenerating the secret will invalidate the current ping URL. Continue?', 'Regenerating the secret will invalidate the current ping URL. Continue?'),
('en', 'panel', 'Generate new secret', 'Generate new secret'),
('en', 'panel', 'pingsecretnote', 'The secret identifies and authorizes the caller - anyone with this URL can trigger a cron run, so treat it like a password. The endpoint always responds with no output.');

-- AI Visibility: AI Bot Crawler Tracking. AI crawlers never execute
-- JavaScript, so the referral snippet is structurally blind to them - this
-- is a separate PHP collector script the site owner hosts on their own
-- server, which does its own reverse-DNS (FCrDNS) verification at the
-- point of truth (see plan notes) before reporting a hit.
CREATE TABLE IF NOT EXISTS `ai_bot_hits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `hit_date` date NOT NULL,
  `platform` varchar(64) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `url_path` varchar(2048) NOT NULL,
  `url_hash` binary(16) NOT NULL,
  `hits` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_date_platform_verified_url` (`website_id`,`hit_date`,`platform`,`verified`,`url_hash`),
  KEY `site_date` (`website_id`,`hit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ai_platforms` ADD COLUMN `bot_ua_pattern` varchar(255) DEFAULT NULL;
ALTER TABLE `ai_platforms` ADD COLUMN `verify_suffix` varchar(255) DEFAULT NULL;
ALTER TABLE `ai_visibility_sites` ADD COLUMN `bot_last_seen_at` datetime DEFAULT NULL;

-- Seed known crawler UA substrings. verify_suffix is left NULL except where
-- a reverse-DNS verification scheme is well established (Google's) - this
-- is not an assertion about other vendors' policies, just what's currently
-- known; admins can adjust ai_platforms directly as vendors publish/change
-- their own verification schemes.
UPDATE `ai_platforms` SET bot_ua_pattern='GPTBot' WHERE platform='chatgpt';
UPDATE `ai_platforms` SET bot_ua_pattern='ClaudeBot' WHERE platform='claude';
UPDATE `ai_platforms` SET bot_ua_pattern='PerplexityBot' WHERE platform='perplexity';
INSERT IGNORE INTO `ai_platforms` (`platform`,`hostname`,`display_name`,`is_active`,`bot_ua_pattern`,`verify_suffix`) VALUES
('google-extended','google.com','Google-Extended (AI training)',1,'Google-Extended','.googlebot.com'),
('bytespider','bytedance.com','Bytespider',1,'Bytespider',NULL),
('ccbot','commoncrawl.org','CCBot',1,'CCBot',NULL),
('applebot-extended','apple.com','Applebot-Extended',1,'Applebot-Extended',NULL),
('meta-externalagent','meta.com','Meta AI',1,'meta-externalagent',NULL);

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('AI bot hit data retention (days)','AIB_BOT_RETENTION_DAYS','365','aivisibility','small',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'AIB_BOT_RETENTION_DAYS', 'AI bot hit data retention (days)');

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'AI Bot Crawlers', 'AI Bot Crawlers'),
('en', 'aivisibility', 'AI Bot Crawler Tracking', 'AI Bot Crawler Tracking'),
('en', 'aivisibility', 'botcollectordesc', 'AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and others) never execute JavaScript, so the referral snippet above cannot see them. Download this collector script and include it on your server to track real crawler visits.'),
('en', 'aivisibility', 'Download collector script', 'Download collector script'),
('en', 'aivisibility', 'botinstallinstructions', 'Generic PHP: include this file at the very top of your site''s bootstrap (e.g. the first line of index.php or wp-config.php).'),
('en', 'aivisibility', 'botwordpressinstructions', 'WordPress: save it into wp-content/mu-plugins/ so it loads automatically on every request.'),
('en', 'aivisibility', 'Waiting for first bot visit', 'Waiting for first bot visit...'),
('en', 'aivisibility', 'Verified', 'Verified'),
('en', 'aivisibility', 'Unverified', 'Unverified'),
('en', 'aivisibility', 'botverifiednotice', '"Verified" means the crawler''s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'),
('en', 'aivisibility', 'Bot crawls over time', 'Bot crawls over time'),
('en', 'aivisibility', 'Crawls', 'Crawls'),
('en', 'aivisibility', 'Top crawled pages', 'Top crawled pages');

-- AI Insights email digest: opt-out email when a website has genuinely new
-- AI Insights (not the same unresolved issue re-appearing with a different
-- count - see RecommendationsController::__recommendationIdentity()).
-- Reuses the existing per-user reports_settings row/UI rather than a new table.
ALTER TABLE `reports_settings` ADD COLUMN `ai_insights_email_notification` tinyint(1) NOT NULL DEFAULT 1;

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Enable AI Insights email notification','SP_AI_INSIGHTS_EMAIL_NOTIFICATION','1','report','bool',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aiinsights', 'ai_insights_email_subject', 'New AI Insights for your website'),
('en', 'aiinsights', 'ai_insights_email_body_intro', 'Our daily scan found new AI Insights for your website(s) that need your attention:'),
('en', 'aiinsights', 'ai_insights_email_body_outro', 'View the full details and take action from your dashboard: [LOGIN_LINK]'),
('en', 'report', 'AI Insights email notification', 'AI Insights email notification'),
-- 'settings' category, keyed by the literal set_name - this is the key the
-- admin's auto-generated Report Settings page (showreportsettings.ctp.php)
-- looks up via $spTextSettings[$listInfo['set_name']], distinct from the
-- 'report' category text above used by the per-user reportscheduler page.
('en', 'settings', 'SP_AI_INSIGHTS_EMAIL_NOTIFICATION', 'Enable AI Insights email notification');

-- Backlink Checker modernization: DataForSEO backlink summary as an
-- alternative to the existing Moz-based path (unchanged, still the default -
-- this is a NEW, separate, default-off flag, deliberately not reusing
-- SP_ENABLE_DFS_BACK_SATU, since that flag already being on for Saturation
-- Checker on an existing install must not silently start a different,
-- billed API call for Backlink Checker too).
ALTER TABLE `backlinkresults` ADD COLUMN `broken_backlinks` int(11) DEFAULT NULL COMMENT 'DataForSEO-only; NULL means this row was measured via Moz';

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Enable DataForSEO for Backlink Checker','SP_ENABLE_DFS_BACKLINK','0','dataforseo','bool',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_ENABLE_DFS_BACKLINK', 'Enable DataForSEO for Backlink Checker'),
('en', 'backlink', 'Broken Backlinks', 'Broken Backlinks'),
('en', 'backlink', 'backlinkdfsnotice', 'Rows measured via DataForSEO show total backlinks and referring domains (not the same page-count metric Moz used) plus a broken-backlinks count. Rows measured via Moz are unaffected.');

-- Online (in-app) upgrade: Settings > Version's "Upgrade Now" button
-- downloads and applies the latest release's files automatically, then
-- hands off to this existing upgrade wizard for the database migration
-- step - see libs/onlineupgrade.class.php.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'Upgrade Now', 'Upgrade Now');

-- AI Visibility fix: ai_platforms conflated two concerns in one hostname
-- column - real click referrers (chatgpt.com, perplexity.ai) and bot-only
-- vendor domains (google.com for Google-Extended, apple.com, meta.com,
-- bytedance.com, commoncrawl.org). aivisibility.js.php queried is_active
-- alone for the browser-side referral snippet, so an ordinary organic
-- Google Search click (referrer host google.com/www.google.com) was being
-- misclassified as an AI referral from "Google-Extended". is_active still
-- gates both ingest paths; is_referral_source additionally scopes the
-- referral snippet to hostnames that are actually click sources.
ALTER TABLE `ai_platforms` ADD COLUMN `is_referral_source` tinyint(1) NOT NULL DEFAULT 1 AFTER `is_active`;
UPDATE `ai_platforms` SET `is_referral_source`=0 WHERE `platform` IN ('google-extended','bytespider','ccbot','applebot-extended','meta-externalagent');

-- AI Visibility: admin-editable platform catalog (add/toggle/edit hostnames
-- without a code deploy) and CSV export on the AI Referral / AI Bot
-- Crawler reports.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Manage AI Platforms', 'Manage AI Platforms'),
('en', 'aivisibility', 'AI Platforms', 'AI Platforms'),
('en', 'aivisibility', 'Platform code', 'Platform code'),
('en', 'aivisibility', 'Hostname', 'Hostname'),
('en', 'aivisibility', 'Display name', 'Display name'),
('en', 'aivisibility', 'Bot UA pattern', 'Bot UA pattern'),
('en', 'aivisibility', 'Verify suffix', 'Verify suffix'),
('en', 'aivisibility', 'Referral source', 'Referral source'),
('en', 'aivisibility', 'referralsourcenotice', 'Only enable this for hostnames real visitors click through from. Vendor domains used solely for bot user-agent matching (e.g. google.com for Google-Extended) must stay off, or ordinary traffic from that domain will be miscounted as an AI referral.'),
('en', 'aivisibility', 'Add Platform', 'Add Platform'),
('en', 'aivisibility', 'Export CSV', 'Export CSV'),
('en', 'aivisibility', 'platformexistsnotice', 'A platform with this hostname already exists.');

-- AI Visibility: on-premise robots.txt/llms.txt control panel + access-log
-- based bot detection, for websites co-located on the same server as SEO
-- Panel (opt-in, admin-configured - see
-- AIVisibilityController::__validateDocrootPath()/__validateAccessLogPath()).
-- Only an admin can set ai_visibility_site_access's paths (real filesystem
-- read/write with the web server's OS permissions); a website's own owner
-- can then self-serve day-to-day robots.txt toggles/llms.txt regeneration
-- once an admin has authorized the path.
CREATE TABLE IF NOT EXISTS `ai_visibility_site_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `docroot_path` varchar(500) DEFAULT NULL,
  `access_log_path` varchar(500) DEFAULT NULL,
  `log_offset` bigint unsigned NOT NULL DEFAULT 0,
  `log_inode` bigint unsigned DEFAULT NULL,
  `log_last_run_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-website-per-platform desired robots.txt state - absence of a row
-- means "allowed". Written only inside a clearly delimited managed block in
-- the website's own robots.txt (see AIVisibilityController::__writeRobotsTxt()).
CREATE TABLE IF NOT EXISTS `ai_visibility_robots_rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `platform` varchar(64) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_platform` (`website_id`,`platform`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Access log bytes read per cron run','AIB_LOG_BYTES_PER_CRON_RUN','5242880','aivisibility','small',1),
('Access log unique IPs verified per cron run','AIB_LOG_MAX_IPS_PER_CRON_RUN','500','aivisibility','small',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'AIB_LOG_BYTES_PER_CRON_RUN', 'Access log bytes read per cron run'),
('en', 'settings', 'AIB_LOG_MAX_IPS_PER_CRON_RUN', 'Access log unique IPs verified per cron run');

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Server Access Configuration', 'Server Access Configuration'),
('en', 'aivisibility', 'Document Root Path', 'Document Root Path'),
('en', 'aivisibility', 'Access Log Path', 'Access Log Path'),
('en', 'aivisibility', 'serveraccessnotice', 'Admin-only. Only set these if SEO Panel and this website are on the same server. Grants SEO Panel real filesystem read/write to this path with the web server''s own permissions.'),
('en', 'aivisibility', 'combinedlogformatnotice', 'Expects standard Apache/Nginx Combined Log Format. Custom log_format configs may not parse.'),
('en', 'aivisibility', 'Path not writable - view only', 'Path not writable - view only'),
('en', 'aivisibility', 'Path not configured', 'Path not configured'),
('en', 'aivisibility', 'AI Crawler Rules', 'AI Crawler Rules'),
('en', 'aivisibility', 'Live robots.txt', 'Live robots.txt'),
('en', 'aivisibility', 'robotswritenotice', 'Toggling a platform below adds or removes a Disallow rule for it inside a clearly marked block in this site''s robots.txt - everything else in the file is left untouched. This only works when an admin has configured a writable Document Root above. A platform being "Allowed" here means SEO Panel isn''t additionally blocking it, not that it is guaranteed crawlable - other rules elsewhere in the file still apply.'),
('en', 'aivisibility', 'Block this platform', 'Block this platform'),
('en', 'aivisibility', 'Regenerate llms.txt', 'Regenerate llms.txt'),
('en', 'aivisibility', 'llmsoverwritenotice', 'llms.txt already exists and wasn''t generated by SEO Panel. Regenerating will overwrite it.'),
('en', 'aivisibility', 'Yes, overwrite', 'Yes, overwrite'),
('en', 'aivisibility', 'View live llms.txt', 'View live llms.txt');

-- Append-only history of every robots.txt AI-crawler-rule change - proof of
-- when a given AI crawler was allowed/blocked and by whom, for legal/
-- compliance use (see AIVisibilityController::exportRobotsAuditLog()).
CREATE TABLE IF NOT EXISTS `ai_visibility_robots_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `platform` varchar(64) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL,
  `changed_by` int unsigned DEFAULT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Export Audit Trail', 'Export Audit Trail'),
('en', 'aivisibility', 'auditlognotice', 'A timestamped record of every AI crawler rule change made through SEO Panel for this website - exportable as proof of policy enforcement for legal/compliance review.'),
('en', 'aivisibility', 'Install Automatically', 'Install Automatically'),
('en', 'aivisibility', 'wpdetectednotice', 'WordPress detected at your configured Document Root. Skip the manual download/paste step - install the collector directly as a must-use plugin.'),
('en', 'aivisibility', 'Installed automatically', 'Installed automatically'),
('en', 'aivisibility', 'wpinstallfailed', 'Could not write the collector to wp-content/mu-plugins/ - check filesystem permissions.');

-- AI-bot response headers via a managed .htaccess block (v1: X-Robots-Tag
-- only, not a full alternate-HTML pipeline) - reuses the same admin-
-- authorized docroot as robots.txt/llms.txt. A malformed .htaccess can 500
-- an entire live site (unlike robots.txt, which is inert if wrong), so
-- __writeHtaccessRules() self-tests via a live HTTP fetch before and after
-- writing and auto-rolls back on failure - see the audit log below for why
-- that isn't optional here.
ALTER TABLE `ai_visibility_site_access`
  ADD COLUMN `htaccess_ai_headers_enabled` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `htaccess_extensions` varchar(255) DEFAULT NULL,
  ADD COLUMN `htaccess_last_written_at` datetime DEFAULT NULL,
  ADD COLUMN `htaccess_last_error` varchar(500) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `ai_visibility_htaccess_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `action` enum('write','rollback') NOT NULL,
  `extensions` varchar(255) DEFAULT NULL,
  `self_test_http_code` int DEFAULT NULL,
  `self_test_ok` tinyint(1) NOT NULL DEFAULT 0,
  `changed_by` int unsigned DEFAULT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'AI-Bot Response Headers', 'AI-Bot Response Headers'),
('en', 'aivisibility', 'htaccessnotice', 'Adds an X-Robots-Tag header for the selected file types, inside a clearly marked block in this site''s .htaccess - everything else in the file is left untouched. Every save is verified live against your site before it is kept; if the new rules make your site unreachable, they are automatically reverted.'),
('en', 'aivisibility', 'Save & Apply', 'Save & Apply'),
('en', 'aivisibility', 'htaccessrollback', 'The new rules made your site unreachable and were automatically reverted. No changes were kept.'),
('en', 'aivisibility', 'htaccessalreadydown', 'Your site is not currently reachable, so SEO Panel cannot safely verify a change. No changes were made.'),
('en', 'aivisibility', 'Last applied', 'Last applied'),
('en', 'aivisibility', 'Last error', 'Last error');

-- Local AI: optional on-server LLM (e.g. Ollama) for AI Insights summaries
-- and meta-description suggestions - content never sent to a third party,
-- unlike a cloud AI feature. See LocalAIController, SettingsController::isLocalAIEnabled().
INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Enable Local AI', 'SP_ENABLE_LOCAL_AI', '0', 'local_ai', 'bool', 1),
('Ollama Base URL', 'SP_LOCAL_AI_URL', 'http://localhost:11434', 'local_ai', 'large', 1),
('Ollama Model', 'SP_LOCAL_AI_MODEL', 'llama3.2:3b', 'local_ai', 'large', 1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_ENABLE_LOCAL_AI', 'Enable Local AI'),
('en', 'settings', 'SP_LOCAL_AI_URL', 'Ollama Base URL'),
('en', 'settings', 'SP_LOCAL_AI_MODEL', 'Ollama Model'),
('en', 'panel', 'Local AI Settings', 'Local AI Settings'),
('en', 'recommendations', 'Generate AI summary', 'Generate AI summary'),
('en', 'recommendations', 'ai-summary-unavailable', 'Local AI summary is not available right now.'),
('en', 'siteauditor', 'Suggest with AI', 'Suggest with AI');

-- MCP (Model Context Protocol) server tokens - see MCPController and
-- install/data/seopanel.sql's ai_visibility_site_access-adjacent comment
-- for the full rationale (per-user scoping, unlike api/api.php's global key).
CREATE TABLE IF NOT EXISTS `mcp_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'panel', 'MCP Access', 'MCP Access'),
('en', 'myaccount', 'MCP Access Tokens', 'MCP Access Tokens'),
('en', 'myaccount', 'mcpaccessnotice', 'Generate a personal access token to let your own AI agent (e.g. Claude Desktop) query your SEO Panel data directly - keyword rankings, backlinks, AI Visibility stats - entirely self-hosted. Nothing leaves your server.'),
('en', 'myaccount', 'Generate new token', 'Generate new token'),
('en', 'myaccount', 'Token Label', 'Token Label'),
('en', 'myaccount', 'mcptokenonceNotice', 'Copy this token now - it will not be shown again.'),
('en', 'myaccount', 'Revoke', 'Revoke'),
('en', 'myaccount', 'Last used', 'Last used'),
('en', 'myaccount', 'Never', 'Never');

-- MCP token expiry - a leaked/forgotten token previously stayed valid
-- forever (revoke was the only way to invalidate one). Optional at
-- creation time (NULL = never expires, unchanged default behavior).
ALTER TABLE `mcp_tokens` ADD COLUMN `expires_at` datetime DEFAULT NULL AFTER `created_at`;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'myaccount', 'Expires', 'Expires'),
('en', 'myaccount', 'Never expires', 'Never expires'),
('en', 'myaccount', 'Expired', 'Expired'),
('en', 'myaccount', '30 days', '30 days'),
('en', 'myaccount', '90 days', '90 days'),
('en', 'myaccount', '1 year', '1 year');

-- AI Visibility Setup page: server-side config (docroot access, robots.txt/
-- llms.txt rules, .htaccess AI-bot headers) collapsed into a progressive-
-- disclosure "Advanced" section rather than always-expanded, to lighten
-- the page for the common case of a user who only needs the install snippet.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Advanced: Server-Side Configuration', 'Advanced: Server-Side Configuration'),
('en', 'aivisibility', 'advancedsectionnotice', 'Document root access, robots.txt/llms.txt crawler rules, and .htaccess AI-bot headers - optional, for sites hosted on this same server.');

-- robots_user_agent_token: the literal token written into robots.txt's
-- User-agent line previously always reused bot_ua_pattern verbatim - a
-- case-insensitive substring pattern meant for classifying raw User-Agent
-- HTTP headers, not guaranteed to be the exact token a crawler's own
-- robots.txt documentation specifies. NULL (the default, unaffected for
-- every existing row) falls back to bot_ua_pattern in __writeRobotsTxt().
ALTER TABLE `ai_platforms` ADD COLUMN `robots_user_agent_token` varchar(100) DEFAULT NULL AFTER `bot_ua_pattern`;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Robots.txt User-agent token', 'Robots.txt User-agent token'),
('en', 'aivisibility', 'robotsuseragenttokenhint', 'Optional. The exact token this crawler documents for its own robots.txt User-agent line (e.g. Google-Extended). Leave blank to reuse the UA match pattern above.');

-- AI Visibility Setup page: replaced the collapsible "Advanced" <details>
-- section with two tabs (Setup / Advanced) - client-side only, active tab
-- persisted in localStorage since every action on this page reloads the
-- whole view via AJAX.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Setup', 'Setup'),
('en', 'aivisibility', 'Advanced', 'Advanced');

-- Setup tab: a link to the Advanced tab, for users who'd otherwise never
-- notice it exists (docroot access, crawler rules, .htaccess AI headers).
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'advancedlinknotice', 'Need document root access, custom crawler rules, or AI-bot response headers?'),
('en', 'aivisibility', 'Go to Advanced settings', 'Go to Advanced settings');

-- New crawler/agent tokens: OpenAI ships 3 distinct tokens under the
-- ChatGPT brand (GPTBot already seeded; OAI-SearchBot for SearchGPT
-- indexing, ChatGPT-User for on-demand live-user browsing fetches) -
-- same 'chatgpt' platform grouping + display_name so the Setup page's
-- single toggle still covers all three. Amazonbot and DuckAssistBot are
-- newly-common AI crawlers with no prior row at all. INSERT IGNORE relies
-- on the UNIQUE hostname key to stay idempotent across repeated upgrades.
INSERT IGNORE INTO `ai_platforms` (`platform`,`hostname`,`display_name`,`is_active`,`is_referral_source`,`bot_ua_pattern`,`verify_suffix`) VALUES
('chatgpt','oai-searchbot.openai.com','ChatGPT',1,0,'OAI-SearchBot',NULL),
('chatgpt','chatgpt-user.openai.com','ChatGPT',1,0,'ChatGPT-User',NULL),
('amazon','amazon.com','Amazonbot',1,0,'Amazonbot',NULL),
('duckassist','duckduckgo.com','DuckAssistBot',1,0,'DuckAssistBot',NULL);

-- Admin platform manager: per-row Edit action needed a way to distinguish
-- "editing row N" from "adding a new row" in the reused add/edit form -
-- no schema change, purely a view/JS addition (see platforms.ctp.php).

-- AI Visibility: unified "Overview" dashboard combining referral totals,
-- bot crawl totals, and AI Overview citation rate in one screen - no new
-- storage, purely new queries over ai_referrals/ai_bot_hits/searchresults.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Overview', 'Overview'),
('en', 'aivisibility', 'AI Referrals (30 days)', 'AI Referrals (30 days)'),
('en', 'aivisibility', 'AI Bot Crawls (30 days)', 'AI Bot Crawls (30 days)'),
('en', 'aivisibility', 'AI Overview Citation Rate', 'AI Overview Citation Rate'),
('en', 'aivisibility', 'overviewaiocaption', 'of keywords where Google AI Overview cited this site, among keywords where an AI Overview appeared'),
('en', 'aivisibility', 'Top AI Platforms (30 days)', 'Top AI Platforms (30 days)'),
('en', 'aivisibility', 'Bot Crawls', 'Bot Crawls'),
('en', 'aivisibility', 'Total', 'Total'),
('en', 'aivisibility', 'View full report', 'View full report'),
('en', 'aivisibility', 'No AI Overview data measured yet for this website.', 'No AI Overview data measured yet for this website.'),
('en', 'aivisibility', 'No AI traffic recorded yet - install the snippet and collector script from the Setup tab.', 'No AI traffic recorded yet - install the snippet and collector script from the Setup tab.');

-- AI Visibility: week-over-week traffic anomaly alerts (referral + bot
-- crawl totals per website) - reuses the existing AlertController/
-- ai_referrals/ai_bot_hits data, no new tables. Gated to run at most
-- once/day via information_list, same idiom as refreshAllAIInsights().
-- No new text strings: alert_subject/alert_message are built directly
-- (mirrors __alertOnFirstPlatformHit()'s existing style, not a
-- $spTextAIV-driven UI string).

-- MCP server: 2 new tools (get_ai_overview_summary, toggle_robots_rule) -
-- no schema change, both reuse existing tables/ownership checks.

-- Local AI: new suggestLlmsTxtDescription() use case (drafts an llms.txt
-- site description from already-crawled Site Auditor page data, same
-- restate-only-what's-there pattern as suggestMetaDescription()) - no
-- schema change.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Suggest description with Local AI', 'Suggest description with Local AI'),
('en', 'aivisibility', 'llmsdescriptionhint', 'Paste this into your website''s Description field (Website Manager) so it appears in llms.txt.'),
('en', 'aivisibility', 'nositeauditordatanotice', 'Run Site Auditor for this website first - no crawled page data to summarize yet.'),
('en', 'aivisibility', 'Generating...', 'Generating...'),
('en', 'aivisibility', 'Edit Platform', 'Edit Platform');

-- Round 3: weekly AI Visibility digest email, Overview dashboard CSV
-- export/date range, tab accessibility, showOverview() test coverage,
-- MCP regenerate_llms_txt tool, newer crawler tokens.

-- weekly AI Visibility digest email - opt-out, sent at most once per 7
-- days per user (ai_visibility_last_digest_sent), independent of the main
-- report scheduler's configurable interval. Mirrors the existing AI
-- Insights email digest's reports_settings/settings pattern exactly.
ALTER TABLE `reports_settings` ADD COLUMN `ai_visibility_email_notification` tinyint(1) NOT NULL DEFAULT 1 AFTER `ai_insights_email_notification`;
ALTER TABLE `reports_settings` ADD COLUMN `ai_visibility_last_digest_sent` date DEFAULT NULL AFTER `ai_visibility_email_notification`;

INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Enable AI Visibility email notification','SP_AI_VISIBILITY_EMAIL_NOTIFICATION','1','report','bool',1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'ai_visibility_email_subject', 'Your AI Visibility summary this week'),
('en', 'aivisibility', 'ai_visibility_email_body_intro', 'Here''s how your website(s) performed with AI platforms this past week:'),
('en', 'aivisibility', 'ai_visibility_email_body_outro', 'View the full dashboard: [LOGIN_LINK]'),
('en', 'aivisibility', 'AI Referrals', 'AI Referrals'),
('en', 'aivisibility', 'AI Bot Crawls', 'AI Bot Crawls'),
('en', 'aivisibility', 'AI Overview Citations', 'AI Overview Citations'),
('en', 'report', 'AI Visibility email notification', 'AI Visibility email notification'),
('en', 'settings', 'SP_AI_VISIBILITY_EMAIL_NOTIFICATION', 'Enable AI Visibility email notification');

-- Overview dashboard: date range + CSV export, matching the AI Referral/
-- AI Bot Crawler reports (no schema change, just new text for the form).
-- "Top AI Platforms"/the AIO caption replace the old, now-orphaned
-- "Top AI Platforms (30 days)" key - a hardcoded "(30 days)" no longer
-- makes sense once the range is user-selectable.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Export Overview CSV', 'Export Overview CSV'),
('en', 'aivisibility', 'Top AI Platforms', 'Top AI Platforms'),
('en', 'aivisibility', 'reflects the latest measured state, not this date range', 'reflects the latest measured state, not this date range');

-- Setup/Advanced tabs: ARIA tablist/tab/tabpanel roles + arrow-key
-- navigation (previously two unlabeled buttons with no aria-selected -
-- a screen-reader user had no indication which tab was active).
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'AI Visibility settings', 'AI Visibility settings');

-- Newer crawler tokens: Anthropic publishes 2 more tokens under the
-- Claude brand beyond ClaudeBot (training crawl, seeded earlier) -
-- Claude-User (on-demand fetch when a live user asks Claude to browse a
-- page) and Claude-SearchBot (search-index crawling) - same split
-- pattern already applied to ChatGPT/OpenAI. Same 'claude' platform
-- grouping + display_name so the existing toggle still covers all three.
INSERT IGNORE INTO `ai_platforms` (`platform`,`hostname`,`display_name`,`is_active`,`is_referral_source`,`bot_ua_pattern`,`verify_suffix`) VALUES
('claude','claude-user.anthropic.com','Claude',1,0,'Claude-User',NULL),
('claude','claude-searchbot.anthropic.com','Claude',1,0,'Claude-SearchBot',NULL);

-- Overview and Setup footer notes: cross-link to the MCP Access token
-- manager, which was previously only reachable via top nav > Settings >
-- My Profile, with no link anywhere inside AI Visibility pointing to it -
-- discoverability gap for the one feature that lets a customer's own AI
-- agent query this data directly.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Prefer to ask your own AI agent directly?', 'Prefer to ask your own AI agent directly?'),
('en', 'aivisibility', 'Connect Claude Desktop or any MCP client', 'Connect Claude Desktop or any MCP client'),
('en', 'aivisibility', 'self-hosted, no data leaves this server', 'self-hosted, no data leaves this server');

-- Site Auditor: structured data (JSON-LD schema.org) check. AI answer
-- engines (ChatGPT, Perplexity, Google AI Overview) rely on structured
-- data as a machine-facing fact layer distinct from OG/Twitter tags,
-- which only affect social share previews - Site Auditor had no coverage
-- of this at all until now.
ALTER TABLE `auditorreports` ADD COLUMN `has_structured_data` tinyint(1) NOT NULL DEFAULT '0' AFTER `has_twitter_cards`;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'siteauditor', 'The page has structured data (JSON-LD) that AI models and search engines can parse', 'The page has structured data (JSON-LD) that AI models and search engines can parse'),
('en', 'siteauditor', 'The page is missing structured data (JSON-LD) - limits how AI models and search engines understand its content', 'The page is missing structured data (JSON-LD) - limits how AI models and search engines understand its content'),
('en', 'siteauditor', 'Structured Data', 'Structured Data');

-- AI Overview report: Share of Voice column + competitor keyword
-- drill-down (which of your keywords a competitor domain is cited for,
-- and whether you're also cited for the same keyword). Built entirely
-- from data the AI Overview Tracking feature already collects
-- (aio_references) - no new ingest, no new schema.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'Share of Voice', 'Share of Voice'),
('en', 'aivisibility', 'Share of Voice is the percentage of your measured keywords where this domain is cited in the AI Overview.', 'Share of Voice is the percentage of your measured keywords where this domain is cited in the AI Overview.'),
('en', 'aivisibility', 'Competitor', 'Competitor'),
('en', 'aivisibility', 'Keywords where this competitor is cited in the AI Overview', 'Keywords where this competitor is cited in the AI Overview'),
('en', 'aivisibility', 'No overlapping keywords found for this competitor', 'No overlapping keywords found for this competitor.'),
('en', 'aivisibility', 'You Cited?', 'You Cited?');

-- AI Perception Check: a customer-initiated, one-off diagnostic that asks
-- a real hosted AI model (the customer's OWN OpenAI/Anthropic/Google API
-- key) what it knows about their website. Deliberately separate from the
-- rest of AI Visibility / Local AI, which never send data to a third
-- party - this is opt-in, per-provider, and only ever runs with a key
-- the customer themselves entered.
CREATE TABLE IF NOT EXISTS `llm_api_keys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `provider` enum('openai','anthropic','google') NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_provider` (`user_id`,`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'AI Perception Check', 'AI Perception Check'),
('en', 'aivisibility', 'perceptionprivacynotice', 'Unlike the rest of AI Visibility, this feature sends your website''s name and URL directly to the AI provider you choose below, using the API key you enter. It only runs when you click "Ask", using a key you supply - nothing is sent automatically, and SEO Panel never sees or pays for these calls.'),
('en', 'aivisibility', 'API Key', 'API Key'),
('en', 'aivisibility', 'Configured', 'Configured'),
('en', 'aivisibility', 'Not configured', 'Not configured'),
('en', 'aivisibility', 'Remove', 'Remove'),
('en', 'aivisibility', 'Add key', 'Add key'),
('en', 'aivisibility', 'Paste your API key', 'Paste your API key'),
('en', 'aivisibility', 'Go to AI Perception Check', 'Go to AI Perception Check'),
('en', 'aivisibility', 'No AI providers configured yet.', 'No AI providers configured yet.'),
('en', 'aivisibility', 'Add an API key', 'Add an API key'),
('en', 'aivisibility', 'Ask', 'Ask'),
('en', 'aivisibility', 'Asking...', 'Asking...'),
('en', 'aivisibility', 'Request failed', 'Request failed.'),
('en', 'aivisibility', 'Curious what ChatGPT or Claude actually says about your site?', 'Curious what ChatGPT or Claude actually says about your site?'),
('en', 'aivisibility', 'Run an AI Perception Check', 'Run an AI Perception Check'),
('en', 'aivisibility', 'uses your own API key', 'uses your own API key');

-- Scheduled AI Perception tracking (weekly, customer-defined prompts,
-- customer's own provider keys) - see aiperception.ctrl.php's
-- MAX_PROMPTS_PER_WEBSITE/TRACKING_INTERVAL_DAYS for the unattended-
-- spend guardrails.
CREATE TABLE IF NOT EXISTS `llm_perception_prompts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `prompt_text` varchar(500) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `llm_perception_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prompt_id` int unsigned NOT NULL,
  `provider` enum('openai','anthropic','google') NOT NULL,
  `checked_date` date NOT NULL,
  `response_text` text,
  `mentioned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prompt_provider_date` (`prompt_id`,`provider`,`checked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'aivisibility', 'AI Perception Tracking', 'AI Perception Tracking'),
('en', 'aivisibility', 'Share of Voice (latest checks)', 'Share of Voice (latest checks)'),
('en', 'aivisibility', 'Tracked Prompts', 'Tracked Prompts'),
('en', 'aivisibility', 'Add a prompt to track weekly (e.g. \"best CRM for small business\")', 'Add a prompt to track weekly (e.g. "best CRM for small business")'),
('en', 'aivisibility', 'Add Prompt', 'Add Prompt'),
('en', 'aivisibility', 'You can track up to', 'You can track up to'),
('en', 'aivisibility', 'prompts per website, checked at most once every', 'prompts per website, checked at most once every'),
('en', 'aivisibility', 'days, against every AI provider you have configured a key for.', 'days, against every AI provider you have configured a key for.'),
('en', 'aivisibility', 'Mentioned', 'Mentioned'),
('en', 'aivisibility', 'Not mentioned', 'Not mentioned'),
('en', 'aivisibility', 'Not checked yet', 'Not checked yet'),
('en', 'aivisibility', 'No prompts tracked yet for this website.', 'No prompts tracked yet for this website.'),
('en', 'aivisibility', 'Go to Scheduled Tracking', 'Go to Scheduled Tracking'),
('en', 'aivisibility', 'Set up weekly, unattended tracking of your own prompts', 'Set up weekly, unattended tracking of your own prompts');

-- Report generation reliability fix: executeCron() previously advanced
-- last_generated / wrote the log row / raised the "success" alert
-- unconditionally, BEFORE sendMail() was even called - a failed send
-- (SMTP/SendGrid outage) was reported to the customer as success, never
-- retried, and invisible to everyone. Now gated on sendMail()'s actual
-- result; on failure last_generated is left alone (retried next run)
-- and this new alert fires instead.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'reports', 'Report Email Failed', 'Report Email Failed'),
('en', 'reports', 'report_email_failed_message', 'Your scheduled SEO report was generated but could not be emailed - it will be retried automatically.');

-- Scheduler operational-table retention (job_queue completed/failed rows,
-- cron_run_log, cron_job_timing) - previously never pruned at all.
-- Editable via the generic Report Settings page, same as
-- SP_AIO_RETENTION_DAYS.
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Job queue finished-row retention (days)', 'SP_JOB_QUEUE_RETENTION_DAYS', '7', 'report', 'small', 1),
('Cron run log retention (days)', 'SP_CRON_RUN_LOG_RETENTION_DAYS', '30', 'report', 'small', 1),
('Cron job timing retention (days)', 'SP_CRON_JOB_TIMING_RETENTION_DAYS', '14', 'report', 'small', 1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_JOB_QUEUE_RETENTION_DAYS', 'Job queue finished-row retention (days)'),
('en', 'settings', 'SP_CRON_RUN_LOG_RETENTION_DAYS', 'Cron run log retention (days)'),
('en', 'settings', 'SP_CRON_JOB_TIMING_RETENTION_DAYS', 'Cron job timing retention (days)');

-- Security fix: every fresh install used to ship with the exact SAME
-- hardcoded SP_API_KEY value (11a9b9070c7d7633831f603f0454ef5b, publicly
-- visible in this project's own source on GitHub), and API_SECRET had no
-- generator at all and defaulted to an empty string - so any install
-- that never manually edited these directly in the database was
-- authenticatable by anyone who'd read that file (verifyAPICredentials()
-- compared both with a plain ==, and an empty stored API_SECRET matched
-- an omitted request parameter). Rotate both for any install still on
-- that known-compromised state; leave alone if the admin already
-- changed them to something else. This is a one-time SQL-side rotation
-- (decent but not PHP random_bytes()-grade entropy) - admins should use
-- the new "Regenerate" buttons on the API Connection page right after
-- upgrading for a cryptographically strong replacement.
UPDATE `settings` SET set_val = MD5(CONCAT(UUID(), RAND(), NOW(6), CONNECTION_ID())) WHERE set_name='SP_API_KEY' AND set_val='11a9b9070c7d7633831f603f0454ef5b';
UPDATE `settings` SET set_val = MD5(CONCAT(UUID(), RAND(), NOW(6), CONNECTION_ID())) WHERE set_name='API_SECRET' AND set_val='';

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'api', 'Regenerate', 'Regenerate'),
('en', 'api', 'api_regenerate_warning', 'Regenerating either value immediately invalidates it for every existing integration using it - update them with the new value right after.');

-- Security fix: login() had no rate limiting - unlimited password
-- guesses against any account/from any IP. Now throttled through the
-- same rate-limit bucket table AI Visibility's endpoints already use.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'login', 'Too many login attempts', 'Too many login attempts. Please wait a minute and try again.');

-- Security fix: Google OAuth access_token/refresh_token were stored in
-- plaintext - UserTokenController now encrypts both at rest (see its
-- own comments). refresh_token was only varchar(255); encrypting adds
-- ~40 raw bytes (nonce+MAC) plus base64 overhead, which could push a
-- longer refresh token past that limit - widened to match access_token's
-- already-unbounded `text` type so encryption can never get truncated.
ALTER TABLE `user_tokens` MODIFY COLUMN `refresh_token` text COLLATE utf8_unicode_ci NOT NULL;

-- Performance fix: rankresults/backlinkresults had no index on
-- website_id at all (only on result_date), and searchresults had none
-- on keyword_id, and searchresultdetails none on searchresult_id -
-- these are the app's ever-growing per-check history tables, queried
-- by website_id/keyword_id constantly (including once per website on
-- EVERY cron pass just to check "does today's report already exist" -
-- see RankController::isReportsExists()/BacklinkController::
-- isReportsExists()). At a few hundred websites with a year of daily
-- history these were full table scans. The composite (id, result_date)
-- shape matches how every report/graph view actually queries these
-- tables (equality on website_id/keyword_id, filtered/ordered by
-- result_date) - keeping the existing single-column result_date index
-- too, since some cron/pruning queries filter by date alone.
ALTER TABLE `rankresults` ADD KEY `website_id_result_date` (`website_id`,`result_date`);
ALTER TABLE `backlinkresults` ADD KEY `website_id_result_date` (`website_id`,`result_date`);
ALTER TABLE `searchresults` ADD KEY `keyword_id_result_date` (`keyword_id`,`result_date`);
ALTER TABLE `searchresultdetails` ADD KEY `searchresult_id` (`searchresult_id`);

-- Data integrity fix: websites.url / users.username / users.email had no
-- DB-level UNIQUE constraint at all - only the app-level "SELECT then
-- decide" checks in __checkWebsiteUrl()/__checkUserName()/__checkEmail(),
-- which have a real TOCTOU race under two concurrent requests (both pass
-- the check, both INSERT, producing a real duplicate with no backstop).
-- createWebsite()/createUser() now also check the INSERT's own result
-- and surface the same "already exists" error for an actual duplicate-key
-- failure (1062), so a caught race degrades to a clean rejection instead
-- of a silent no-op. On an install that already has duplicate url/
-- username/email values from before this fix, the matching ALTER below
-- fails and is skipped (this upgrade path already tolerates a failed
-- individual statement) - existing duplicates must be resolved manually
-- before that specific constraint can be added.
ALTER TABLE `websites` ADD UNIQUE KEY `url` (`url`);
ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`);
ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`);

-- Performance fix: same shape as the website_id/result_date composite
-- indexes above, for 4 more ever-growing per-check-history tables that
-- were missed in that pass - every report/graph view for these tools
-- queries by website_id (or its equivalent FK) filtered/grouped by date,
-- and dirsubmitinfo had no secondary index at all (full table scan on
-- every directory-submission status check and per-directory lookup).
ALTER TABLE `saturationresults` ADD KEY `website_id_result_date` (`website_id`,`result_date`);
ALTER TABLE `review_link_results` ADD KEY `review_link_id_report_date` (`review_link_id`,`report_date`);
ALTER TABLE `social_media_link_results` ADD KEY `sm_link_id_report_date` (`sm_link_id`,`report_date`);
ALTER TABLE `website_search_analytics` ADD KEY `website_id_report_date` (`website_id`,`report_date`);
ALTER TABLE `dirsubmitinfo` ADD KEY `website_id_directory_id` (`website_id`,`directory_id`);

-- Opt-in TOTP two-factor authentication - see install/data/seopanel.sql's
-- own CREATE TABLE comment for the full design.
CREATE TABLE IF NOT EXISTS `user_totp` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `secret` text NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_totp_backup_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin action audit log - see install/data/seopanel.sql's own CREATE
-- TABLE comment for the full design.
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` int unsigned DEFAULT NULL,
  `actor_username` varchar(64) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int unsigned DEFAULT NULL,
  `target_label` varchar(255) DEFAULT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `actor_user_id` (`actor_user_id`),
  KEY `action_created_at` (`action`,`created_at`),
  KEY `target_type_id` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Competitive AI share-of-voice - see install/data/seopanel.sql's own
-- CREATE TABLE comments for the full design.
CREATE TABLE IF NOT EXISTS `llm_perception_competitors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `llm_perception_competitor_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `competitor_id` int unsigned NOT NULL,
  `prompt_id` int unsigned NOT NULL,
  `provider` enum('openai','anthropic','google') NOT NULL,
  `checked_date` date NOT NULL,
  `mentioned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competitor_provider_date` (`competitor_id`,`provider`,`checked_date`),
  KEY `prompt_id` (`prompt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
