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

-- AI Schema Markup Generator - see install/data/seopanel.sql's own
-- CREATE TABLE comment for the full design.
CREATE TABLE IF NOT EXISTS `schema_markup` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `schema_type` varchar(50) NOT NULL,
  `field_data` text NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_schema_type` (`website_id`,`schema_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI Perception Check: mention sentiment - see
-- AiPerceptionController::__classifySentiment().
ALTER TABLE `llm_perception_results` ADD COLUMN `sentiment` varchar(10) DEFAULT NULL COMMENT 'positive/neutral/negative; NULL when not mentioned or the check errored';

-- Site Auditor: AI-readiness checks - see
-- WebsiteController::crawlMetaData()/AuditorComponent::countReportPageScore().
ALTER TABLE `auditorreports` ADD COLUMN `heading_structure_ok` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `auditorreports` ADD COLUMN `has_faq_content` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `auditorreports` ADD COLUMN `word_count` int(11) NOT NULL DEFAULT '0';

-- Convert every remaining MyISAM table to InnoDB, including the ones
-- every cron run writes to most often (rankresults, backlinkresults,
-- keywords, crawl_log, searchresults/searchresultdetails,
-- keyword_analytics, pagespeeddetails/pagespeedresults,
-- saturationresults, webmaster_keywords/webmaster_sitemaps,
-- website_search_analytics). MyISAM locks the whole table per write, so
-- a cron job writing new results could block a user's own report page
-- from reading that same table at the same time; it's also not
-- crash-safe, unlike InnoDB. No FULLTEXT indexes exist on any of these
-- tables, so the conversion needs no other schema change.
ALTER TABLE `auditorpagelinks` ENGINE=InnoDB;
ALTER TABLE `auditorprojects` ENGINE=InnoDB;
ALTER TABLE `auditorreports` ENGINE=InnoDB;
ALTER TABLE `auditorsitemaps` ENGINE=InnoDB;
ALTER TABLE `backlinkresults` ENGINE=InnoDB;
ALTER TABLE `country` ENGINE=InnoDB;
ALTER TABLE `crawl_log` ENGINE=InnoDB;
ALTER TABLE `currency` ENGINE=InnoDB;
ALTER TABLE `directories` ENGINE=InnoDB;
ALTER TABLE `dirsubmitinfo` ENGINE=InnoDB;
ALTER TABLE `di_directory_meta` ENGINE=InnoDB;
ALTER TABLE `featured_directories` ENGINE=InnoDB;
ALTER TABLE `information_list` ENGINE=InnoDB;
ALTER TABLE `keywordcrontracker` ENGINE=InnoDB;
ALTER TABLE `keywords` ENGINE=InnoDB;
ALTER TABLE `keyword_analytics` ENGINE=InnoDB;
ALTER TABLE `pagespeeddetails` ENGINE=InnoDB;
ALTER TABLE `pagespeedresults` ENGINE=InnoDB;
ALTER TABLE `proxylist` ENGINE=InnoDB;
ALTER TABLE `qwp_settings` ENGINE=InnoDB;
ALTER TABLE `rankresults` ENGINE=InnoDB;
ALTER TABLE `reports_settings` ENGINE=InnoDB;
ALTER TABLE `saturationresults` ENGINE=InnoDB;
ALTER TABLE `searchengines` ENGINE=InnoDB;
ALTER TABLE `searchresultdetails` ENGINE=InnoDB;
ALTER TABLE `searchresults` ENGINE=InnoDB;
ALTER TABLE `seoplugins` ENGINE=InnoDB;
ALTER TABLE `seotools` ENGINE=InnoDB;
ALTER TABLE `settings` ENGINE=InnoDB;
ALTER TABLE `skipdirectories` ENGINE=InnoDB;
ALTER TABLE `testplugin` ENGINE=InnoDB;
ALTER TABLE `themes` ENGINE=InnoDB;
ALTER TABLE `timezone` ENGINE=InnoDB;
ALTER TABLE `usertypes` ENGINE=InnoDB;
ALTER TABLE `user_report_logs` ENGINE=InnoDB;
ALTER TABLE `user_specs` ENGINE=InnoDB;
ALTER TABLE `user_tokens` ENGINE=InnoDB;
ALTER TABLE `webmaster_keywords` ENGINE=InnoDB;
ALTER TABLE `webmaster_sitemaps` ENGINE=InnoDB;
ALTER TABLE `website_search_analytics` ENGINE=InnoDB;

-- these four live in textlang.sql/plugin database.sql rather than
-- seopanel.sql, so a first pass over just seopanel.sql's table list
-- missed them - `texts` in particular is read on effectively every page
-- load (all UI copy comes from it) and written on every language-pack
-- update or Customizer text edit.
ALTER TABLE `languages` ENGINE=InnoDB;
ALTER TABLE `texts` ENGINE=InnoDB;
ALTER TABLE `translators` ENGINE=InnoDB;
ALTER TABLE `sd_settings` ENGINE=InnoDB;

-- Feature tour: a short, skippable educational walkthrough shown once to
-- brand-new users, pointing out the dashboard, SEO tools, settings tabs,
-- and plugins - separate from the (disabled) Setup Wizard, which drives
-- specific setup ACTIONS rather than orienting a new user to the app's
-- layout. Default 1 ("already seen") on the column itself so every
-- EXISTING user row (this ALTER runs against them directly) and any
-- future plain insert both default to "don't show" - only
-- UserController::startRegistration()/createUser() explicitly insert 0
-- for a genuinely new account, which is what actually triggers the tour.
ALTER TABLE `users` ADD COLUMN `feature_tour_seen` tinyint(1) NOT NULL DEFAULT 1;
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Show feature tour to new users', 'SP_FEATURE_TOUR', '1', 'system', 'bool', 1);
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_FEATURE_TOUR', 'Show feature tour to new users');

-- Setup Tour: absorbs the old Setup Wizard's one genuinely useful piece
-- (creating a website inline) as its own step 2, plus a new Cron Job
-- step, so the wizard has no remaining reason to exist. Retired
-- entirely - files, columns, and setting - rather than left dormant, to
-- avoid the two ever confusing each other again if SP_SETUP_WIZARD were
-- flipped back on by accident.
ALTER TABLE `users` DROP COLUMN `setup_wizard_step`;
ALTER TABLE `users` DROP COLUMN `setup_wizard_dismissed`;
DELETE FROM `settings` WHERE `set_name`='SP_SETUP_WIZARD';
DELETE FROM `texts` WHERE `label`='SP_SETUP_WIZARD';

-- Setup Tour step persistence: mirrors the just-removed
-- setup_wizard_step - a user who closes the tab mid-tour resumes at
-- this step on reload instead of replaying Welcome.
ALTER TABLE `users` ADD COLUMN `feature_tour_step` tinyint(1) NOT NULL DEFAULT 0;

-- Setup Tour copy, translatable via the standard texts mechanism
-- (category 'featuretour'). Checked against the live texts table
-- before seeding any of these: strings that already existed elsewhere
-- (all 12 SEO Tools card labels under 'seotools', several Settings row
-- labels as "X Settings" under 'panel', Dashboard/Plugins/Country/
-- Search Engine/Language/Seo Tools under 'common', Website Url under
-- 'directory', Skip under 'button') are reused directly in the view
-- rather than duplicated here - only genuinely tour-specific copy got
-- a new key.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'featuretour', 'tour_add_website_btn', 'Add Website'),
('en', 'featuretour', 'tour_admin_only_cron', 'This is a server setting, so only an admin on your account can set it up. Current status:'),
('en', 'featuretour', 'tour_admin_only_settings', 'These are account-wide settings, so only an admin on your account can change them - nothing to do here.'),
('en', 'featuretour', 'tour_admin_only_spapi', 'This is an account-wide setting, so only an admin on your account can register it. Current status:'),
('en', 'featuretour', 'tour_back', 'Back'),
('en', 'featuretour', 'tour_connected', 'Connected'),
('en', 'featuretour', 'tour_cron_desc', 'The exact command to add to your server\'s crontab'),
('en', 'featuretour', 'tour_dashboard_desc', 'The screen you land on after logging in'),
('en', 'featuretour', 'tour_detected', 'Detected'),
('en', 'featuretour', 'tour_dfs_desc', 'The data provider behind rank checking and SERP data'),
('en', 'featuretour', 'tour_field_keyword', 'Primary Keyword'),
('en', 'featuretour', 'tour_field_review', 'Review Link'),
('en', 'featuretour', 'tour_field_social', 'Social Media'),
('en', 'featuretour', 'tour_field_website_name', 'Website Name'),
('en', 'featuretour', 'tour_generic_error', 'Something went wrong. Please try again.'),
('en', 'featuretour', 'tour_get_started', 'Get Started'),
('en', 'featuretour', 'tour_google_desc', 'Connect Analytics and Search Console'),
('en', 'featuretour', 'tour_important', 'Important'),
('en', 'featuretour', 'tour_keyword_plural', 'keywords'),
('en', 'featuretour', 'tour_keyword_singular', 'keyword'),
('en', 'featuretour', 'tour_localai_desc', 'Point AI-powered features at your own Ollama server'),
('en', 'featuretour', 'tour_mail_desc', 'Scheduled reports, password resets, and registration emails all go through here'),
('en', 'featuretour', 'tour_more_websites', '+ %d more %s - click above to manage all of them'),
('en', 'featuretour', 'tour_moz_desc', 'Domain Authority, Page Authority, and Spam Score'),
('en', 'featuretour', 'tour_next', 'Next'),
('en', 'featuretour', 'tour_no_plugins', 'No plugins are available to use on your account right now.'),
('en', 'featuretour', 'tour_none_option', '-- none --'),
('en', 'featuretour', 'tour_not_added_suffix', 'not added: %s'),
('en', 'featuretour', 'tour_not_detected', 'Not detected yet'),
('en', 'featuretour', 'tour_not_set_up', 'Not set up'),
('en', 'featuretour', 'tour_optional_badge', 'Optional'),
('en', 'featuretour', 'tour_optional_extras', 'Optional extras'),
('en', 'featuretour', 'tour_optional_option', '-- optional --'),
('en', 'featuretour', 'tour_placeholder_keyword', 'e.g. seo software'),
('en', 'featuretour', 'tour_placeholder_profile_url', 'Profile URL'),
('en', 'featuretour', 'tour_placeholder_review_url', 'Review page URL'),
('en', 'featuretour', 'tour_placeholder_website_name', 'e.g. My Company Website'),
('en', 'featuretour', 'tour_placeholder_website_url', 'https://example.com'),
('en', 'featuretour', 'tour_proxy_desc', 'Proxies used for crawling and directory submission'),
('en', 'featuretour', 'tour_refresh', 'Refresh'),
('en', 'featuretour', 'tour_refresh_tooltip', 'Just saved something in another tab? Refresh to update the connection badges below'),
('en', 'featuretour', 'tour_review_hint', 'The URL must contain the platform\'s name, e.g. a Yelp link should include \"yelp\".'),
('en', 'featuretour', 'tour_review_hint_dynamic', 'The URL must contain \"%s\" (e.g. a %s link should include that word).'),
('en', 'featuretour', 'tour_review_not_added', 'Review link'),
('en', 'featuretour', 'tour_seopanel_api_desc', 'Rank tracking and SERP data, ready in a couple of minutes'),
('en', 'featuretour', 'tour_seopanel_api_label', 'Seo Panel API'),
('en', 'featuretour', 'tour_skip', 'Skip'),
('en', 'featuretour', 'tour_skip_tooltip', 'Skip this tour'),
('en', 'featuretour', 'tour_social_not_added', 'Social media link'),
('en', 'featuretour', 'tour_step1_body', 'SEO Panel tracks rankings, audits your sites, checks backlinks, and monitors how you show up in AI answer engines - all from one self-hosted control room.'),
('en', 'featuretour', 'tour_step1_heading', 'Welcome to SEO Panel'),
('en', 'featuretour', 'tour_step1_info', 'This is a short tour of where everything lives, and gets your first website set up along the way. Skip it anytime, or take it again later from the <strong>Setup Tour</strong> link in the top menu.'),
('en', 'featuretour', 'tour_step2_already_set', 'You\'re all set - nothing to do here.'),
('en', 'featuretour', 'tour_step2_heading', 'Add Your First Website'),
('en', 'featuretour', 'tour_step2_intro', 'Everything below - the dashboard, rank tracking, audits - needs at least one website to work with. Takes a few seconds:'),
('en', 'featuretour', 'tour_step3_body', 'The fastest way to get real rank and SERP data flowing without hunting down your own DataForSEO or MOZ keys - free to register, no credit card.'),
('en', 'featuretour', 'tour_step3_heading', 'Seo Panel API'),
('en', 'featuretour', 'tour_step4_body', 'Worth doing before the Tools menu next: without these connected, several tools won\'t have any real data to show yet. Click any row to go straight there in a new tab:'),
('en', 'featuretour', 'tour_step4_heading', 'Settings: Where Your Integrations Live'),
('en', 'featuretour', 'tour_step5_body', 'SEO Panel checks rankings, runs audits, and generates reports on a schedule - but only once your server is actually calling <code>cron.php</code>. Nothing above matters if this isn\'t running.'),
('en', 'featuretour', 'tour_step5_heading', 'Set Up the Cron Job'),
('en', 'featuretour', 'tour_step6_body', 'Pick a website from the dropdown to see its ranking trends, top keywords, and recent activity at a glance.'),
('en', 'featuretour', 'tour_step6_heading', 'Your Dashboard'),
('en', 'featuretour', 'tour_step6_info', 'Every tool you connect (Analytics, Social Media, Reviews...) gets its own dashboard tab here too.'),
('en', 'featuretour', 'tour_step7_body', 'The <strong>Tools</strong> menu is where the actual work happens - twelve tools in one place. Click any card to open it in a new tab:'),
('en', 'featuretour', 'tour_step8_body', 'The <strong>Plugins</strong> menu extends SEO Panel beyond the core tools. Click any card to open it in a new tab:'),
('en', 'featuretour', 'tour_step9_body', 'That\'s the layout. Everything above will make a lot more sense once real data starts coming in.'),
('en', 'featuretour', 'tour_step9_heading', 'You\'re All Set'),
('en', 'featuretour', 'tour_step9_info', 'Want to see this again? Look for <strong>Setup Tour</strong> in the top menu bar.'),
('en', 'featuretour', 'tour_system_desc', 'Language, timezone, pagination, and other app-wide defaults'),
('en', 'featuretour', 'tour_website_added', 'Website and primary keyword added.'),
('en', 'featuretour', 'tour_website_plural', 'websites'),
('en', 'featuretour', 'tour_website_singular', 'website');

-- Setup Tour translations into major world languages (Spanish,
-- French, German, Portuguese-Brazil, Italian, Russian, Chinese
-- Simplified, Japanese, Arabic) - same 73 keys seeded in English
-- above, translated into each of these languages. Any language not
-- covered here falls back to English automatically (getLanguageTexts()'s
-- own built-in fallback), so this list can grow over time without
-- breaking anything.
-- 657 rows
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('es', 'featuretour', 'tour_add_website_btn', 'Añadir sitio web'),
('es', 'featuretour', 'tour_admin_only_cron', 'Esta es una configuración del servidor, solo un administrador de tu cuenta puede configurarla. Estado actual:'),
('es', 'featuretour', 'tour_admin_only_settings', 'Estas son configuraciones de toda la cuenta, solo un administrador de tu cuenta puede cambiarlas - nada que hacer aquí.'),
('es', 'featuretour', 'tour_admin_only_spapi', 'Esta es una configuración de toda la cuenta, solo un administrador de tu cuenta puede registrarla. Estado actual:'),
('es', 'featuretour', 'tour_back', 'Atrás'),
('es', 'featuretour', 'tour_connected', 'Conectado'),
('es', 'featuretour', 'tour_cron_desc', 'El comando exacto para añadir al crontab de tu servidor'),
('es', 'featuretour', 'tour_dashboard_desc', 'La pantalla en la que aterrizas después de iniciar sesión'),
('es', 'featuretour', 'tour_detected', 'Detectado'),
('es', 'featuretour', 'tour_dfs_desc', 'El proveedor de datos detrás de la verificación de posición y los datos SERP'),
('es', 'featuretour', 'tour_field_keyword', 'Palabra clave principal'),
('es', 'featuretour', 'tour_field_review', 'Enlace de reseña'),
('es', 'featuretour', 'tour_field_social', 'Redes sociales'),
('es', 'featuretour', 'tour_field_website_name', 'Nombre del sitio web'),
('es', 'featuretour', 'tour_generic_error', 'Algo salió mal. Por favor, inténtalo de nuevo.'),
('es', 'featuretour', 'tour_get_started', 'Empezar'),
('es', 'featuretour', 'tour_google_desc', 'Conecta Analytics y Search Console'),
('es', 'featuretour', 'tour_important', 'Importante'),
('es', 'featuretour', 'tour_keyword_plural', 'palabras clave'),
('es', 'featuretour', 'tour_keyword_singular', 'palabra clave'),
('es', 'featuretour', 'tour_localai_desc', 'Dirige las funciones con IA a tu propio servidor Ollama'),
('es', 'featuretour', 'tour_mail_desc', 'Los informes programados, restablecimientos de contraseña y correos de registro pasan todos por aquí'),
('es', 'featuretour', 'tour_more_websites', '+ %d %s más - haz clic arriba para gestionarlos todos'),
('es', 'featuretour', 'tour_moz_desc', 'Autoridad de dominio, autoridad de página y puntuación de spam'),
('es', 'featuretour', 'tour_next', 'Siguiente'),
('es', 'featuretour', 'tour_no_plugins', 'No hay complementos disponibles para usar en tu cuenta en este momento.'),
('es', 'featuretour', 'tour_none_option', '-- ninguno --'),
('es', 'featuretour', 'tour_not_added_suffix', 'no añadido: %s'),
('es', 'featuretour', 'tour_not_detected', 'Aún no detectado'),
('es', 'featuretour', 'tour_not_set_up', 'No configurado'),
('es', 'featuretour', 'tour_optional_badge', 'Opcional'),
('es', 'featuretour', 'tour_optional_extras', 'Extras opcionales'),
('es', 'featuretour', 'tour_optional_option', '-- opcional --'),
('es', 'featuretour', 'tour_placeholder_keyword', 'p. ej. software de seo'),
('es', 'featuretour', 'tour_placeholder_profile_url', 'URL del perfil'),
('es', 'featuretour', 'tour_placeholder_review_url', 'URL de la página de reseñas'),
('es', 'featuretour', 'tour_placeholder_website_name', 'p. ej. Mi Empresa'),
('es', 'featuretour', 'tour_placeholder_website_url', 'https://ejemplo.com'),
('es', 'featuretour', 'tour_proxy_desc', 'Proxies usados para el rastreo y el envío a directorios'),
('es', 'featuretour', 'tour_refresh', 'Actualizar'),
('es', 'featuretour', 'tour_refresh_tooltip', '¿Acabas de guardar algo en otra pestaña? Actualiza para renovar las insignias de conexión'),
('es', 'featuretour', 'tour_review_hint', 'La URL debe contener el nombre de la plataforma, p. ej. un enlace de Yelp debe incluir \"yelp\".'),
('es', 'featuretour', 'tour_review_hint_dynamic', 'La URL debe contener \"%s\" (p. ej. un enlace de %s debe incluir esa palabra).'),
('es', 'featuretour', 'tour_review_not_added', 'Enlace de reseña'),
('es', 'featuretour', 'tour_seopanel_api_desc', 'Datos de posición y SERP en tiempo real, listos en un par de minutos'),
('es', 'featuretour', 'tour_seopanel_api_label', 'API de Seo Panel'),
('es', 'featuretour', 'tour_skip', 'Omitir'),
('es', 'featuretour', 'tour_skip_tooltip', 'Omitir este recorrido'),
('es', 'featuretour', 'tour_social_not_added', 'Enlace de red social'),
('es', 'featuretour', 'tour_step1_body', 'SEO Panel rastrea posiciones, audita tus sitios, comprueba backlinks y monitoriza tu presencia en motores de respuesta de IA - todo desde una única sala de control autoalojada.'),
('es', 'featuretour', 'tour_step1_heading', 'Bienvenido a SEO Panel'),
('es', 'featuretour', 'tour_step1_info', 'Este es un breve recorrido por dónde está todo, y de paso configura tu primer sitio web. Omítelo cuando quieras, o vuelve a verlo más tarde desde el enlace <strong>Recorrido de configuración</strong> en el menú superior.'),
('es', 'featuretour', 'tour_step2_already_set', 'Ya está todo listo - nada que hacer aquí.'),
('es', 'featuretour', 'tour_step2_heading', 'Añade tu primer sitio web'),
('es', 'featuretour', 'tour_step2_intro', 'Todo lo de abajo - el panel, el seguimiento de posiciones, las auditorías - necesita al menos un sitio web con el que trabajar. Toma unos segundos:'),
('es', 'featuretour', 'tour_step3_body', 'La forma más rápida de obtener datos reales de posición y SERP sin buscar tus propias claves de DataForSEO o MOZ - gratis para registrarse, sin tarjeta de crédito.'),
('es', 'featuretour', 'tour_step3_heading', 'API de Seo Panel'),
('es', 'featuretour', 'tour_step4_body', 'Vale la pena hacerlo antes del menú Herramientas: sin estas conexiones, varias herramientas no tendrán datos reales que mostrar todavía. Haz clic en cualquier fila para ir directamente allí en una nueva pestaña:'),
('es', 'featuretour', 'tour_step4_heading', 'Configuración: dónde viven tus integraciones'),
('es', 'featuretour', 'tour_step5_body', 'SEO Panel comprueba posiciones, ejecuta auditorías y genera informes según un horario - pero solo una vez que tu servidor esté realmente llamando a <code>cron.php</code>. Nada de lo anterior importa si esto no está funcionando.'),
('es', 'featuretour', 'tour_step5_heading', 'Configura la tarea cron'),
('es', 'featuretour', 'tour_step6_body', 'Elige un sitio web en el desplegable para ver sus tendencias de posición, palabras clave principales y actividad reciente de un vistazo.'),
('es', 'featuretour', 'tour_step6_heading', 'Tu panel de control'),
('es', 'featuretour', 'tour_step6_info', 'Cada herramienta que conectes (Analytics, Redes Sociales, Reseñas...) obtiene también su propia pestaña aquí en el panel.'),
('es', 'featuretour', 'tour_step7_body', 'El menú <strong>Herramientas</strong> es donde ocurre el trabajo real - doce herramientas en un solo lugar. Haz clic en cualquier tarjeta para abrirla en una nueva pestaña:'),
('es', 'featuretour', 'tour_step8_body', 'El menú <strong>Complementos</strong> amplía SEO Panel más allá de las herramientas principales. Haz clic en cualquier tarjeta para abrirla en una nueva pestaña:'),
('es', 'featuretour', 'tour_step9_body', 'Ese es el diseño general. Todo lo anterior tendrá mucho más sentido en cuanto empiecen a llegar datos reales.'),
('es', 'featuretour', 'tour_step9_heading', 'Todo listo'),
('es', 'featuretour', 'tour_step9_info', '¿Quieres verlo de nuevo? Busca <strong>Recorrido de configuración</strong> en la barra del menú superior.'),
('es', 'featuretour', 'tour_system_desc', 'Idioma, zona horaria, paginación y otros valores predeterminados de toda la aplicación'),
('es', 'featuretour', 'tour_website_added', 'Sitio web y palabra clave principal añadidos.'),
('es', 'featuretour', 'tour_website_plural', 'sitios web'),
('es', 'featuretour', 'tour_website_singular', 'sitio web'),
('fr', 'featuretour', 'tour_add_website_btn', 'Ajouter un site web'),
('fr', 'featuretour', 'tour_admin_only_cron', 'Ceci est un paramètre serveur, seul un administrateur de votre compte peut le configurer. Statut actuel :'),
('fr', 'featuretour', 'tour_admin_only_settings', 'Ce sont des paramètres valables pour tout le compte, seul un administrateur peut les modifier - rien à faire ici.'),
('fr', 'featuretour', 'tour_admin_only_spapi', 'Ceci est un paramètre valable pour tout le compte, seul un administrateur peut l\'enregistrer. Statut actuel :'),
('fr', 'featuretour', 'tour_back', 'Retour'),
('fr', 'featuretour', 'tour_connected', 'Connecté'),
('fr', 'featuretour', 'tour_cron_desc', 'La commande exacte à ajouter à la crontab de votre serveur'),
('fr', 'featuretour', 'tour_dashboard_desc', 'L\'écran sur lequel vous arrivez après connexion'),
('fr', 'featuretour', 'tour_detected', 'Détecté'),
('fr', 'featuretour', 'tour_dfs_desc', 'Le fournisseur de données derrière la vérification de position et les données SERP'),
('fr', 'featuretour', 'tour_field_keyword', 'Mot-clé principal'),
('fr', 'featuretour', 'tour_field_review', 'Lien d\'avis'),
('fr', 'featuretour', 'tour_field_social', 'Réseaux sociaux'),
('fr', 'featuretour', 'tour_field_website_name', 'Nom du site web'),
('fr', 'featuretour', 'tour_generic_error', 'Une erreur est survenue. Veuillez réessayer.'),
('fr', 'featuretour', 'tour_get_started', 'Commencer'),
('fr', 'featuretour', 'tour_google_desc', 'Connectez Analytics et Search Console'),
('fr', 'featuretour', 'tour_important', 'Important'),
('fr', 'featuretour', 'tour_keyword_plural', 'mots-clés'),
('fr', 'featuretour', 'tour_keyword_singular', 'mot-clé'),
('fr', 'featuretour', 'tour_localai_desc', 'Dirigez les fonctionnalités IA vers votre propre serveur Ollama'),
('fr', 'featuretour', 'tour_mail_desc', 'Les rapports programmés, réinitialisations de mot de passe et e-mails d\'inscription passent tous par ici'),
('fr', 'featuretour', 'tour_more_websites', '+ %d %s de plus - cliquez ci-dessus pour tous les gérer'),
('fr', 'featuretour', 'tour_moz_desc', 'Autorité de domaine, autorité de page et score de spam'),
('fr', 'featuretour', 'tour_next', 'Suivant'),
('fr', 'featuretour', 'tour_no_plugins', 'Aucun plugin n\'est disponible sur votre compte pour le moment.'),
('fr', 'featuretour', 'tour_none_option', '-- aucun --'),
('fr', 'featuretour', 'tour_not_added_suffix', 'non ajouté : %s'),
('fr', 'featuretour', 'tour_not_detected', 'Pas encore détecté'),
('fr', 'featuretour', 'tour_not_set_up', 'Non configuré'),
('fr', 'featuretour', 'tour_optional_badge', 'Optionnel'),
('fr', 'featuretour', 'tour_optional_extras', 'Options supplémentaires'),
('fr', 'featuretour', 'tour_optional_option', '-- facultatif --'),
('fr', 'featuretour', 'tour_placeholder_keyword', 'p. ex. logiciel seo'),
('fr', 'featuretour', 'tour_placeholder_profile_url', 'URL du profil'),
('fr', 'featuretour', 'tour_placeholder_review_url', 'URL de la page d\'avis'),
('fr', 'featuretour', 'tour_placeholder_website_name', 'p. ex. Mon Entreprise'),
('fr', 'featuretour', 'tour_placeholder_website_url', 'https://exemple.com'),
('fr', 'featuretour', 'tour_proxy_desc', 'Proxys utilisés pour l\'exploration et la soumission dans les annuaires'),
('fr', 'featuretour', 'tour_refresh', 'Actualiser'),
('fr', 'featuretour', 'tour_refresh_tooltip', 'Vous venez d\'enregistrer quelque chose dans un autre onglet ? Actualisez pour mettre à jour les badges de connexion'),
('fr', 'featuretour', 'tour_review_hint', 'L\'URL doit contenir le nom de la plateforme, par ex. un lien Yelp doit inclure \"yelp\".'),
('fr', 'featuretour', 'tour_review_hint_dynamic', 'L\'URL doit contenir \"%s\" (par ex. un lien %s doit inclure ce mot).'),
('fr', 'featuretour', 'tour_review_not_added', 'Lien d\'avis'),
('fr', 'featuretour', 'tour_seopanel_api_desc', 'Suivi de position et données SERP, prêt en quelques minutes'),
('fr', 'featuretour', 'tour_seopanel_api_label', 'API Seo Panel'),
('fr', 'featuretour', 'tour_skip', 'Passer'),
('fr', 'featuretour', 'tour_skip_tooltip', 'Passer cette visite guidée'),
('fr', 'featuretour', 'tour_social_not_added', 'Lien de réseau social'),
('fr', 'featuretour', 'tour_step1_body', 'SEO Panel suit les positions, audite vos sites, vérifie les backlinks et surveille votre présence dans les moteurs de réponse IA - le tout depuis une seule salle de contrôle auto-hébergée.'),
('fr', 'featuretour', 'tour_step1_heading', 'Bienvenue sur SEO Panel'),
('fr', 'featuretour', 'tour_step1_info', 'Voici une courte visite guidée pour découvrir où se trouve tout, qui configure aussi votre premier site web au passage. Passez-la à tout moment, ou revoyez-la plus tard via le lien <strong>Visite guidée</strong> dans le menu du haut.'),
('fr', 'featuretour', 'tour_step2_already_set', 'Tout est prêt - rien à faire ici.'),
('fr', 'featuretour', 'tour_step2_heading', 'Ajoutez votre premier site web'),
('fr', 'featuretour', 'tour_step2_intro', 'Tout ce qui suit - le tableau de bord, le suivi de position, les audits - nécessite au moins un site web pour fonctionner. Cela prend quelques secondes :'),
('fr', 'featuretour', 'tour_step3_body', 'Le moyen le plus rapide d\'obtenir de vraies données de position et SERP sans chercher vos propres clés DataForSEO ou MOZ - inscription gratuite, sans carte bancaire.'),
('fr', 'featuretour', 'tour_step3_heading', 'API Seo Panel'),
('fr', 'featuretour', 'tour_step4_body', 'À faire avant le menu Outils : sans ces connexions, plusieurs outils n\'auront pas encore de données réelles à afficher. Cliquez sur une ligne pour y accéder directement dans un nouvel onglet :'),
('fr', 'featuretour', 'tour_step4_heading', 'Paramètres : où vivent vos intégrations'),
('fr', 'featuretour', 'tour_step5_body', 'SEO Panel vérifie les positions, exécute des audits et génère des rapports selon un planning - mais seulement une fois que votre serveur appelle réellement <code>cron.php</code>. Rien de tout cela n\'a d\'importance si ce n\'est pas actif.'),
('fr', 'featuretour', 'tour_step5_heading', 'Configurez la tâche cron'),
('fr', 'featuretour', 'tour_step6_body', 'Choisissez un site web dans la liste déroulante pour voir ses tendances de position, ses principaux mots-clés et son activité récente en un coup d\'œil.'),
('fr', 'featuretour', 'tour_step6_heading', 'Votre tableau de bord'),
('fr', 'featuretour', 'tour_step6_info', 'Chaque outil que vous connectez (Analytics, Réseaux sociaux, Avis...) obtient aussi son propre onglet ici.'),
('fr', 'featuretour', 'tour_step7_body', 'Le menu <strong>Outils</strong> est l\'endroit où le vrai travail se fait - douze outils réunis en un seul endroit. Cliquez sur une carte pour l\'ouvrir dans un nouvel onglet :'),
('fr', 'featuretour', 'tour_step8_body', 'Le menu <strong>Plugins</strong> étend SEO Panel au-delà des outils principaux. Cliquez sur une carte pour l\'ouvrir dans un nouvel onglet :'),
('fr', 'featuretour', 'tour_step9_body', 'Voilà pour l\'ensemble. Tout ce qui précède prendra beaucoup plus de sens une fois que de vraies données commenceront à arriver.'),
('fr', 'featuretour', 'tour_step9_heading', 'Tout est prêt'),
('fr', 'featuretour', 'tour_step9_info', 'Envie de la revoir ? Cherchez <strong>Visite guidée</strong> dans la barre de menu du haut.'),
('fr', 'featuretour', 'tour_system_desc', 'Langue, fuseau horaire, pagination et autres réglages par défaut de l\'application'),
('fr', 'featuretour', 'tour_website_added', 'Site web et mot-clé principal ajoutés.'),
('fr', 'featuretour', 'tour_website_plural', 'sites web'),
('fr', 'featuretour', 'tour_website_singular', 'site web'),
('de', 'featuretour', 'tour_add_website_btn', 'Website hinzufügen'),
('de', 'featuretour', 'tour_admin_only_cron', 'Dies ist eine Servereinstellung, nur ein Administrator deines Kontos kann sie einrichten. Aktueller Status:'),
('de', 'featuretour', 'tour_admin_only_settings', 'Dies sind kontoweite Einstellungen, nur ein Administrator deines Kontos kann sie ändern - hier gibt es nichts zu tun.'),
('de', 'featuretour', 'tour_admin_only_spapi', 'Dies ist eine kontoweite Einstellung, nur ein Administrator deines Kontos kann sie registrieren. Aktueller Status:'),
('de', 'featuretour', 'tour_back', 'Zurück'),
('de', 'featuretour', 'tour_connected', 'Verbunden'),
('de', 'featuretour', 'tour_cron_desc', 'Der genaue Befehl für die Crontab deines Servers'),
('de', 'featuretour', 'tour_dashboard_desc', 'Der Bildschirm, auf dem du nach der Anmeldung landest'),
('de', 'featuretour', 'tour_detected', 'Erkannt'),
('de', 'featuretour', 'tour_dfs_desc', 'Der Datenanbieter hinter der Rangprüfung und den SERP-Daten'),
('de', 'featuretour', 'tour_field_keyword', 'Hauptkeyword'),
('de', 'featuretour', 'tour_field_review', 'Bewertungslink'),
('de', 'featuretour', 'tour_field_social', 'Soziale Medien'),
('de', 'featuretour', 'tour_field_website_name', 'Website-Name'),
('de', 'featuretour', 'tour_generic_error', 'Etwas ist schiefgelaufen. Bitte versuche es erneut.'),
('de', 'featuretour', 'tour_get_started', 'Loslegen'),
('de', 'featuretour', 'tour_google_desc', 'Analytics und Search Console verbinden'),
('de', 'featuretour', 'tour_important', 'Wichtig'),
('de', 'featuretour', 'tour_keyword_plural', 'Keywords'),
('de', 'featuretour', 'tour_keyword_singular', 'Keyword'),
('de', 'featuretour', 'tour_localai_desc', 'KI-gestützte Funktionen mit deinem eigenen Ollama-Server verbinden'),
('de', 'featuretour', 'tour_mail_desc', 'Geplante Berichte, Passwort-Zurücksetzungen und Registrierungs-E-Mails laufen alle hierüber'),
('de', 'featuretour', 'tour_more_websites', '+ %d weitere %s - klicke oben, um alle zu verwalten'),
('de', 'featuretour', 'tour_moz_desc', 'Domain-Autorität, Seiten-Autorität und Spam-Score'),
('de', 'featuretour', 'tour_next', 'Weiter'),
('de', 'featuretour', 'tour_no_plugins', 'Für dein Konto sind derzeit keine Plugins verfügbar.'),
('de', 'featuretour', 'tour_none_option', '-- keine --'),
('de', 'featuretour', 'tour_not_added_suffix', 'nicht hinzugefügt: %s'),
('de', 'featuretour', 'tour_not_detected', 'Noch nicht erkannt'),
('de', 'featuretour', 'tour_not_set_up', 'Nicht eingerichtet'),
('de', 'featuretour', 'tour_optional_badge', 'Optional'),
('de', 'featuretour', 'tour_optional_extras', 'Optionale Extras'),
('de', 'featuretour', 'tour_optional_option', '-- optional --'),
('de', 'featuretour', 'tour_placeholder_keyword', 'z. B. seo software'),
('de', 'featuretour', 'tour_placeholder_profile_url', 'Profil-URL'),
('de', 'featuretour', 'tour_placeholder_review_url', 'URL der Bewertungsseite'),
('de', 'featuretour', 'tour_placeholder_website_name', 'z. B. Meine Firma'),
('de', 'featuretour', 'tour_placeholder_website_url', 'https://beispiel.de'),
('de', 'featuretour', 'tour_proxy_desc', 'Proxys für Crawling und Verzeichnis-Einreichung'),
('de', 'featuretour', 'tour_refresh', 'Aktualisieren'),
('de', 'featuretour', 'tour_refresh_tooltip', 'Gerade etwas in einem anderen Tab gespeichert? Aktualisieren, um die Verbindungs-Badges zu erneuern'),
('de', 'featuretour', 'tour_review_hint', 'Die URL muss den Namen der Plattform enthalten, z. B. sollte ein Yelp-Link \"yelp\" enthalten.'),
('de', 'featuretour', 'tour_review_hint_dynamic', 'Die URL muss \"%s\" enthalten (z. B. sollte ein %s-Link dieses Wort enthalten).'),
('de', 'featuretour', 'tour_review_not_added', 'Bewertungslink'),
('de', 'featuretour', 'tour_seopanel_api_desc', 'Rang- und SERP-Daten in Echtzeit, in ein paar Minuten einsatzbereit'),
('de', 'featuretour', 'tour_seopanel_api_label', 'Seo Panel API'),
('de', 'featuretour', 'tour_skip', 'Überspringen'),
('de', 'featuretour', 'tour_skip_tooltip', 'Diese Tour überspringen'),
('de', 'featuretour', 'tour_social_not_added', 'Social-Media-Link'),
('de', 'featuretour', 'tour_step1_body', 'SEO Panel verfolgt Rankings, prüft deine Websites, kontrolliert Backlinks und beobachtet deine Sichtbarkeit in KI-Antwortmaschinen - alles aus einer selbst gehosteten Kommandozentrale.'),
('de', 'featuretour', 'tour_step1_heading', 'Willkommen bei SEO Panel'),
('de', 'featuretour', 'tour_step1_info', 'Dies ist eine kurze Tour, die zeigt, wo alles zu finden ist, und dabei gleich deine erste Website einrichtet. Überspringe sie jederzeit oder rufe sie später über den Link <strong>Einrichtungstour</strong> im oberen Menü erneut auf.'),
('de', 'featuretour', 'tour_step2_already_set', 'Alles eingerichtet - hier gibt es nichts zu tun.'),
('de', 'featuretour', 'tour_step2_heading', 'Deine erste Website hinzufügen'),
('de', 'featuretour', 'tour_step2_intro', 'Alles Folgende - Dashboard, Rangverfolgung, Audits - benötigt mindestens eine Website. Dauert nur ein paar Sekunden:'),
('de', 'featuretour', 'tour_step3_body', 'Der schnellste Weg zu echten Rang- und SERP-Daten, ohne eigene DataForSEO- oder MOZ-Schlüssel suchen zu müssen - kostenlose Registrierung, keine Kreditkarte nötig.'),
('de', 'featuretour', 'tour_step3_heading', 'Seo Panel API'),
('de', 'featuretour', 'tour_step4_body', 'Lohnt sich vor dem Werkzeuge-Menü: Ohne diese Verbindungen haben mehrere Werkzeuge noch keine echten Daten anzuzeigen. Klicke auf eine Zeile, um direkt in einem neuen Tab dorthin zu gelangen:'),
('de', 'featuretour', 'tour_step4_heading', 'Einstellungen: Wo deine Integrationen leben'),
('de', 'featuretour', 'tour_step5_body', 'SEO Panel prüft Rankings, führt Audits durch und erstellt Berichte nach Zeitplan - aber nur, sobald dein Server tatsächlich <code>cron.php</code> aufruft. Nichts davon funktioniert, wenn das nicht läuft.'),
('de', 'featuretour', 'tour_step5_heading', 'Cron-Job einrichten'),
('de', 'featuretour', 'tour_step6_body', 'Wähle eine Website aus dem Dropdown, um Rangtrends, Top-Keywords und aktuelle Aktivitäten auf einen Blick zu sehen.'),
('de', 'featuretour', 'tour_step6_heading', 'Dein Dashboard'),
('de', 'featuretour', 'tour_step6_info', 'Jedes verbundene Werkzeug (Analytics, Social Media, Bewertungen...) bekommt hier auch seinen eigenen Dashboard-Tab.'),
('de', 'featuretour', 'tour_step7_body', 'Im Menü <strong>Werkzeuge</strong> findet die eigentliche Arbeit statt - zwölf Werkzeuge an einem Ort. Klicke auf eine Karte, um sie in einem neuen Tab zu öffnen:'),
('de', 'featuretour', 'tour_step8_body', 'Das Menü <strong>Plugins</strong> erweitert SEO Panel über die Kernwerkzeuge hinaus. Klicke auf eine Karte, um sie in einem neuen Tab zu öffnen:'),
('de', 'featuretour', 'tour_step9_body', 'Das war der Überblick. Alles oben wird viel mehr Sinn ergeben, sobald echte Daten eintreffen.'),
('de', 'featuretour', 'tour_step9_heading', 'Alles bereit'),
('de', 'featuretour', 'tour_step9_info', 'Willst du das nochmal sehen? Suche nach <strong>Einrichtungstour</strong> in der oberen Menüleiste.'),
('de', 'featuretour', 'tour_system_desc', 'Sprache, Zeitzone, Seitenzahl und andere app-weite Standardeinstellungen'),
('de', 'featuretour', 'tour_website_added', 'Website und Hauptkeyword hinzugefügt.'),
('de', 'featuretour', 'tour_website_plural', 'Websites'),
('de', 'featuretour', 'tour_website_singular', 'Website'),
('pt-br', 'featuretour', 'tour_add_website_btn', 'Adicionar site'),
('pt-br', 'featuretour', 'tour_admin_only_cron', 'Esta é uma configuração de servidor, apenas um administrador da sua conta pode configurá-la. Status atual:'),
('pt-br', 'featuretour', 'tour_admin_only_settings', 'Estas são configurações de toda a conta, apenas um administrador pode alterá-las - nada a fazer aqui.'),
('pt-br', 'featuretour', 'tour_admin_only_spapi', 'Esta é uma configuração de toda a conta, apenas um administrador pode registrá-la. Status atual:'),
('pt-br', 'featuretour', 'tour_back', 'Voltar'),
('pt-br', 'featuretour', 'tour_connected', 'Conectado'),
('pt-br', 'featuretour', 'tour_cron_desc', 'O comando exato para adicionar ao crontab do seu servidor'),
('pt-br', 'featuretour', 'tour_dashboard_desc', 'A tela em que você chega depois de fazer login'),
('pt-br', 'featuretour', 'tour_detected', 'Detectado'),
('pt-br', 'featuretour', 'tour_dfs_desc', 'O provedor de dados por trás da verificação de posição e dos dados de SERP'),
('pt-br', 'featuretour', 'tour_field_keyword', 'Palavra-chave principal'),
('pt-br', 'featuretour', 'tour_field_review', 'Link de avaliação'),
('pt-br', 'featuretour', 'tour_field_social', 'Redes sociais'),
('pt-br', 'featuretour', 'tour_field_website_name', 'Nome do site'),
('pt-br', 'featuretour', 'tour_generic_error', 'Algo deu errado. Tente novamente.'),
('pt-br', 'featuretour', 'tour_get_started', 'Começar'),
('pt-br', 'featuretour', 'tour_google_desc', 'Conecte o Analytics e o Search Console'),
('pt-br', 'featuretour', 'tour_important', 'Importante'),
('pt-br', 'featuretour', 'tour_keyword_plural', 'palavras-chave'),
('pt-br', 'featuretour', 'tour_keyword_singular', 'palavra-chave'),
('pt-br', 'featuretour', 'tour_localai_desc', 'Direcione recursos de IA para o seu próprio servidor Ollama'),
('pt-br', 'featuretour', 'tour_mail_desc', 'Relatórios agendados, redefinições de senha e e-mails de registro passam todos por aqui'),
('pt-br', 'featuretour', 'tour_more_websites', '+ %d %s a mais - clique acima para gerenciar todos'),
('pt-br', 'featuretour', 'tour_moz_desc', 'Autoridade de domínio, autoridade de página e pontuação de spam'),
('pt-br', 'featuretour', 'tour_next', 'Próximo'),
('pt-br', 'featuretour', 'tour_no_plugins', 'Nenhum plugin está disponível para uso na sua conta no momento.'),
('pt-br', 'featuretour', 'tour_none_option', '-- nenhum --'),
('pt-br', 'featuretour', 'tour_not_added_suffix', 'não adicionado: %s'),
('pt-br', 'featuretour', 'tour_not_detected', 'Ainda não detectado'),
('pt-br', 'featuretour', 'tour_not_set_up', 'Não configurado'),
('pt-br', 'featuretour', 'tour_optional_badge', 'Opcional'),
('pt-br', 'featuretour', 'tour_optional_extras', 'Extras opcionais'),
('pt-br', 'featuretour', 'tour_optional_option', '-- opcional --'),
('pt-br', 'featuretour', 'tour_placeholder_keyword', 'ex.: software de seo'),
('pt-br', 'featuretour', 'tour_placeholder_profile_url', 'URL do perfil'),
('pt-br', 'featuretour', 'tour_placeholder_review_url', 'URL da página de avaliações'),
('pt-br', 'featuretour', 'tour_placeholder_website_name', 'ex.: Minha Empresa'),
('pt-br', 'featuretour', 'tour_placeholder_website_url', 'https://exemplo.com'),
('pt-br', 'featuretour', 'tour_proxy_desc', 'Proxies usados para rastreamento e envio a diretórios'),
('pt-br', 'featuretour', 'tour_refresh', 'Atualizar'),
('pt-br', 'featuretour', 'tour_refresh_tooltip', 'Acabou de salvar algo em outra aba? Atualize para renovar os selos de conexão'),
('pt-br', 'featuretour', 'tour_review_hint', 'A URL deve conter o nome da plataforma, ex.: um link do Yelp deve incluir \"yelp\".'),
('pt-br', 'featuretour', 'tour_review_hint_dynamic', 'A URL deve conter \"%s\" (ex.: um link do %s deve incluir essa palavra).'),
('pt-br', 'featuretour', 'tour_review_not_added', 'Link de avaliação'),
('pt-br', 'featuretour', 'tour_seopanel_api_desc', 'Dados de posição e SERP em tempo real, prontos em poucos minutos'),
('pt-br', 'featuretour', 'tour_seopanel_api_label', 'API do Seo Panel'),
('pt-br', 'featuretour', 'tour_skip', 'Pular'),
('pt-br', 'featuretour', 'tour_skip_tooltip', 'Pular este tour'),
('pt-br', 'featuretour', 'tour_social_not_added', 'Link de rede social'),
('pt-br', 'featuretour', 'tour_step1_body', 'O SEO Panel rastreia posições, audita seus sites, verifica backlinks e monitora sua presença em mecanismos de resposta de IA - tudo a partir de uma única central autogerenciada.'),
('pt-br', 'featuretour', 'tour_step1_heading', 'Bem-vindo ao SEO Panel'),
('pt-br', 'featuretour', 'tour_step1_info', 'Este é um tour rápido por onde tudo fica, e de quebra configura seu primeiro site. Pule quando quiser, ou reveja depois pelo link <strong>Tour de Configuração</strong> no menu superior.'),
('pt-br', 'featuretour', 'tour_step2_already_set', 'Tudo pronto - nada a fazer aqui.'),
('pt-br', 'featuretour', 'tour_step2_heading', 'Adicione seu primeiro site'),
('pt-br', 'featuretour', 'tour_step2_intro', 'Tudo abaixo - painel, rastreamento de posição, auditorias - precisa de pelo menos um site para funcionar. Leva alguns segundos:'),
('pt-br', 'featuretour', 'tour_step3_body', 'A forma mais rápida de obter dados reais de posição e SERP sem precisar caçar suas próprias chaves do DataForSEO ou MOZ - registro gratuito, sem cartão de crédito.'),
('pt-br', 'featuretour', 'tour_step3_heading', 'API do Seo Panel'),
('pt-br', 'featuretour', 'tour_step4_body', 'Vale a pena fazer antes do menu Ferramentas: sem essas conexões, várias ferramentas ainda não terão dados reais para mostrar. Clique em qualquer linha para ir direto até lá em uma nova aba:'),
('pt-br', 'featuretour', 'tour_step4_heading', 'Configurações: onde suas integrações vivem'),
('pt-br', 'featuretour', 'tour_step5_body', 'O SEO Panel verifica posições, executa auditorias e gera relatórios em um cronograma - mas somente quando seu servidor estiver realmente chamando <code>cron.php</code>. Nada disso importa se isso não estiver rodando.'),
('pt-br', 'featuretour', 'tour_step5_heading', 'Configure a tarefa cron'),
('pt-br', 'featuretour', 'tour_step6_body', 'Escolha um site no menu suspenso para ver suas tendências de posição, principais palavras-chave e atividade recente de relance.'),
('pt-br', 'featuretour', 'tour_step6_heading', 'Seu painel'),
('pt-br', 'featuretour', 'tour_step6_info', 'Cada ferramenta que você conectar (Analytics, Redes Sociais, Avaliações...) também ganha sua própria aba aqui no painel.'),
('pt-br', 'featuretour', 'tour_step7_body', 'O menu <strong>Ferramentas</strong> é onde o trabalho de verdade acontece - doze ferramentas em um só lugar. Clique em qualquer card para abri-lo em uma nova aba:'),
('pt-br', 'featuretour', 'tour_step8_body', 'O menu <strong>Plugins</strong> estende o SEO Panel além das ferramentas principais. Clique em qualquer card para abri-lo em uma nova aba:'),
('pt-br', 'featuretour', 'tour_step9_body', 'Esse é o panorama geral. Tudo acima fará muito mais sentido assim que dados reais começarem a chegar.'),
('pt-br', 'featuretour', 'tour_step9_heading', 'Tudo pronto'),
('pt-br', 'featuretour', 'tour_step9_info', 'Quer ver de novo? Procure por <strong>Tour de Configuração</strong> na barra de menu superior.'),
('pt-br', 'featuretour', 'tour_system_desc', 'Idioma, fuso horário, paginação e outros padrões de todo o aplicativo'),
('pt-br', 'featuretour', 'tour_website_added', 'Site e palavra-chave principal adicionados.'),
('pt-br', 'featuretour', 'tour_website_plural', 'sites'),
('pt-br', 'featuretour', 'tour_website_singular', 'site'),
('it', 'featuretour', 'tour_add_website_btn', 'Aggiungi sito web'),
('it', 'featuretour', 'tour_admin_only_cron', 'Questa è un\'impostazione del server, solo un amministratore del tuo account può configurarla. Stato attuale:'),
('it', 'featuretour', 'tour_admin_only_settings', 'Queste sono impostazioni a livello di account, solo un amministratore può modificarle - niente da fare qui.'),
('it', 'featuretour', 'tour_admin_only_spapi', 'Questa è un\'impostazione a livello di account, solo un amministratore può registrarla. Stato attuale:'),
('it', 'featuretour', 'tour_back', 'Indietro'),
('it', 'featuretour', 'tour_connected', 'Connesso'),
('it', 'featuretour', 'tour_cron_desc', 'Il comando esatto da aggiungere al crontab del tuo server'),
('it', 'featuretour', 'tour_dashboard_desc', 'La schermata in cui atterri dopo l\'accesso'),
('it', 'featuretour', 'tour_detected', 'Rilevato'),
('it', 'featuretour', 'tour_dfs_desc', 'Il fornitore di dati dietro il controllo delle posizioni e i dati SERP'),
('it', 'featuretour', 'tour_field_keyword', 'Parola chiave principale'),
('it', 'featuretour', 'tour_field_review', 'Link recensione'),
('it', 'featuretour', 'tour_field_social', 'Social media'),
('it', 'featuretour', 'tour_field_website_name', 'Nome del sito web'),
('it', 'featuretour', 'tour_generic_error', 'Qualcosa è andato storto. Riprova.'),
('it', 'featuretour', 'tour_get_started', 'Inizia'),
('it', 'featuretour', 'tour_google_desc', 'Collega Analytics e Search Console'),
('it', 'featuretour', 'tour_important', 'Importante'),
('it', 'featuretour', 'tour_keyword_plural', 'parole chiave'),
('it', 'featuretour', 'tour_keyword_singular', 'parola chiave'),
('it', 'featuretour', 'tour_localai_desc', 'Indirizza le funzionalità IA verso il tuo server Ollama'),
('it', 'featuretour', 'tour_mail_desc', 'Report programmati, reimpostazioni della password ed e-mail di registrazione passano tutti da qui'),
('it', 'featuretour', 'tour_more_websites', '+ altri %d %s - clicca sopra per gestirli tutti'),
('it', 'featuretour', 'tour_moz_desc', 'Autorità di dominio, autorità di pagina e punteggio spam'),
('it', 'featuretour', 'tour_next', 'Avanti'),
('it', 'featuretour', 'tour_no_plugins', 'Al momento non ci sono plugin disponibili per il tuo account.'),
('it', 'featuretour', 'tour_none_option', '-- nessuno --'),
('it', 'featuretour', 'tour_not_added_suffix', 'non aggiunto: %s'),
('it', 'featuretour', 'tour_not_detected', 'Non ancora rilevato'),
('it', 'featuretour', 'tour_not_set_up', 'Non configurato'),
('it', 'featuretour', 'tour_optional_badge', 'Facoltativo'),
('it', 'featuretour', 'tour_optional_extras', 'Extra facoltativi'),
('it', 'featuretour', 'tour_optional_option', '-- facoltativo --'),
('it', 'featuretour', 'tour_placeholder_keyword', 'es. software seo'),
('it', 'featuretour', 'tour_placeholder_profile_url', 'URL del profilo'),
('it', 'featuretour', 'tour_placeholder_review_url', 'URL della pagina recensioni'),
('it', 'featuretour', 'tour_placeholder_website_name', 'es. La Mia Azienda'),
('it', 'featuretour', 'tour_placeholder_website_url', 'https://esempio.com'),
('it', 'featuretour', 'tour_proxy_desc', 'Proxy usati per la scansione e l\'invio a directory'),
('it', 'featuretour', 'tour_refresh', 'Aggiorna'),
('it', 'featuretour', 'tour_refresh_tooltip', 'Hai appena salvato qualcosa in un\'altra scheda? Aggiorna per rinnovare i badge di connessione'),
('it', 'featuretour', 'tour_review_hint', 'L\'URL deve contenere il nome della piattaforma, es. un link Yelp deve includere \"yelp\".'),
('it', 'featuretour', 'tour_review_hint_dynamic', 'L\'URL deve contenere \"%s\" (es. un link %s deve includere quella parola).'),
('it', 'featuretour', 'tour_review_not_added', 'Link recensione'),
('it', 'featuretour', 'tour_seopanel_api_desc', 'Dati di posizione e SERP in tempo reale, pronti in un paio di minuti'),
('it', 'featuretour', 'tour_seopanel_api_label', 'API di Seo Panel'),
('it', 'featuretour', 'tour_skip', 'Salta'),
('it', 'featuretour', 'tour_skip_tooltip', 'Salta questo tour'),
('it', 'featuretour', 'tour_social_not_added', 'Link social'),
('it', 'featuretour', 'tour_step1_body', 'SEO Panel traccia le posizioni, controlla i tuoi siti, verifica i backlink e monitora la tua presenza nei motori di risposta IA - tutto da un\'unica sala di controllo self-hosted.'),
('it', 'featuretour', 'tour_step1_heading', 'Benvenuto in SEO Panel'),
('it', 'featuretour', 'tour_step1_info', 'Questo è un breve tour di dove si trova tutto, che configura anche il tuo primo sito web lungo il percorso. Saltalo quando vuoi, o rivedilo più tardi dal link <strong>Tour di configurazione</strong> nel menu superiore.'),
('it', 'featuretour', 'tour_step2_already_set', 'Tutto pronto - niente da fare qui.'),
('it', 'featuretour', 'tour_step2_heading', 'Aggiungi il tuo primo sito web'),
('it', 'featuretour', 'tour_step2_intro', 'Tutto quanto segue - dashboard, tracciamento posizioni, controlli - richiede almeno un sito web. Richiede pochi secondi:'),
('it', 'featuretour', 'tour_step3_body', 'Il modo più veloce per ottenere dati reali di posizione e SERP senza cercare le tue chiavi DataForSEO o MOZ - registrazione gratuita, senza carta di credito.'),
('it', 'featuretour', 'tour_step3_heading', 'API di Seo Panel'),
('it', 'featuretour', 'tour_step4_body', 'Vale la pena farlo prima del menu Strumenti: senza queste connessioni, diversi strumenti non avranno ancora dati reali da mostrare. Clicca su una riga per andare direttamente lì in una nuova scheda:'),
('it', 'featuretour', 'tour_step4_heading', 'Impostazioni: dove vivono le tue integrazioni'),
('it', 'featuretour', 'tour_step5_body', 'SEO Panel controlla le posizioni, esegue verifiche e genera report secondo una pianificazione - ma solo quando il tuo server chiama effettivamente <code>cron.php</code>. Niente di tutto ciò conta se questo non è attivo.'),
('it', 'featuretour', 'tour_step5_heading', 'Configura il cron job'),
('it', 'featuretour', 'tour_step6_body', 'Scegli un sito web dal menu a tendina per vedere a colpo d\'occhio le tendenze di posizione, le parole chiave principali e l\'attività recente.'),
('it', 'featuretour', 'tour_step6_heading', 'La tua dashboard'),
('it', 'featuretour', 'tour_step6_info', 'Ogni strumento che colleghi (Analytics, Social Media, Recensioni...) ottiene anche la propria scheda qui nella dashboard.'),
('it', 'featuretour', 'tour_step7_body', 'Il menu <strong>Strumenti</strong> è dove avviene il vero lavoro - dodici strumenti in un unico posto. Clicca su una card per aprirla in una nuova scheda:'),
('it', 'featuretour', 'tour_step8_body', 'Il menu <strong>Plugin</strong> estende SEO Panel oltre gli strumenti principali. Clicca su una card per aprirla in una nuova scheda:'),
('it', 'featuretour', 'tour_step9_body', 'Questo è il quadro generale. Tutto quanto sopra avrà molto più senso non appena inizieranno ad arrivare dati reali.'),
('it', 'featuretour', 'tour_step9_heading', 'Tutto pronto'),
('it', 'featuretour', 'tour_step9_info', 'Vuoi rivederlo? Cerca <strong>Tour di configurazione</strong> nella barra del menu superiore.'),
('it', 'featuretour', 'tour_system_desc', 'Lingua, fuso orario, paginazione e altre impostazioni predefinite dell\'app'),
('it', 'featuretour', 'tour_website_added', 'Sito web e parola chiave principale aggiunti.'),
('it', 'featuretour', 'tour_website_plural', 'siti web'),
('it', 'featuretour', 'tour_website_singular', 'sito web'),
('ru', 'featuretour', 'tour_add_website_btn', 'Добавить сайт'),
('ru', 'featuretour', 'tour_admin_only_cron', 'Это настройка сервера, изменить её может только администратор вашей учётной записи. Текущий статус:'),
('ru', 'featuretour', 'tour_admin_only_settings', 'Это настройки всей учётной записи, изменить их может только администратор - здесь делать нечего.'),
('ru', 'featuretour', 'tour_admin_only_spapi', 'Это настройка всей учётной записи, зарегистрировать её может только администратор. Текущий статус:'),
('ru', 'featuretour', 'tour_back', 'Назад'),
('ru', 'featuretour', 'tour_connected', 'Подключено'),
('ru', 'featuretour', 'tour_cron_desc', 'Точная команда для добавления в crontab вашего сервера'),
('ru', 'featuretour', 'tour_dashboard_desc', 'Экран, на который вы попадаете после входа'),
('ru', 'featuretour', 'tour_detected', 'Обнаружено'),
('ru', 'featuretour', 'tour_dfs_desc', 'Поставщик данных для проверки позиций и данных SERP'),
('ru', 'featuretour', 'tour_field_keyword', 'Основной ключевой запрос'),
('ru', 'featuretour', 'tour_field_review', 'Ссылка на отзыв'),
('ru', 'featuretour', 'tour_field_social', 'Социальные сети'),
('ru', 'featuretour', 'tour_field_website_name', 'Название сайта'),
('ru', 'featuretour', 'tour_generic_error', 'Что-то пошло не так. Попробуйте снова.'),
('ru', 'featuretour', 'tour_get_started', 'Начать'),
('ru', 'featuretour', 'tour_google_desc', 'Подключите Analytics и Search Console'),
('ru', 'featuretour', 'tour_important', 'Важно'),
('ru', 'featuretour', 'tour_keyword_plural', 'ключевых запросов'),
('ru', 'featuretour', 'tour_keyword_singular', 'ключевой запрос'),
('ru', 'featuretour', 'tour_localai_desc', 'Направьте функции ИИ на ваш собственный сервер Ollama'),
('ru', 'featuretour', 'tour_mail_desc', 'Запланированные отчёты, сброс пароля и письма регистрации проходят через это'),
('ru', 'featuretour', 'tour_more_websites', '+ ещё %d %s - нажмите выше, чтобы управлять всеми'),
('ru', 'featuretour', 'tour_moz_desc', 'Авторитетность домена, авторитетность страницы и показатель спама'),
('ru', 'featuretour', 'tour_next', 'Далее'),
('ru', 'featuretour', 'tour_no_plugins', 'Сейчас для вашей учётной записи нет доступных плагинов.'),
('ru', 'featuretour', 'tour_none_option', '-- нет --'),
('ru', 'featuretour', 'tour_not_added_suffix', 'не добавлено: %s'),
('ru', 'featuretour', 'tour_not_detected', 'Пока не обнаружено'),
('ru', 'featuretour', 'tour_not_set_up', 'Не настроено'),
('ru', 'featuretour', 'tour_optional_badge', 'Необязательно'),
('ru', 'featuretour', 'tour_optional_extras', 'Необязательные дополнения'),
('ru', 'featuretour', 'tour_optional_option', '-- необязательно --'),
('ru', 'featuretour', 'tour_placeholder_keyword', 'напр. seo софт'),
('ru', 'featuretour', 'tour_placeholder_profile_url', 'URL профиля'),
('ru', 'featuretour', 'tour_placeholder_review_url', 'URL страницы отзывов'),
('ru', 'featuretour', 'tour_placeholder_website_name', 'напр. Моя Компания'),
('ru', 'featuretour', 'tour_placeholder_website_url', 'https://example.com'),
('ru', 'featuretour', 'tour_proxy_desc', 'Прокси, используемые для сканирования и отправки в каталоги'),
('ru', 'featuretour', 'tour_refresh', 'Обновить'),
('ru', 'featuretour', 'tour_refresh_tooltip', 'Только что сохранили что-то в другой вкладке? Обновите, чтобы обновить значки подключения'),
('ru', 'featuretour', 'tour_review_hint', 'URL должен содержать название платформы, например ссылка Yelp должна включать \"yelp\".'),
('ru', 'featuretour', 'tour_review_hint_dynamic', 'URL должен содержать \"%s\" (например ссылка %s должна включать это слово).'),
('ru', 'featuretour', 'tour_review_not_added', 'Ссылка на отзыв'),
('ru', 'featuretour', 'tour_seopanel_api_desc', 'Данные о позициях и SERP в реальном времени, готово за пару минут'),
('ru', 'featuretour', 'tour_seopanel_api_label', 'API Seo Panel'),
('ru', 'featuretour', 'tour_skip', 'Пропустить'),
('ru', 'featuretour', 'tour_skip_tooltip', 'Пропустить этот тур'),
('ru', 'featuretour', 'tour_social_not_added', 'Ссылка на соцсеть'),
('ru', 'featuretour', 'tour_step1_body', 'SEO Panel отслеживает позиции, проверяет ваши сайты, контролирует обратные ссылки и следит за вашим присутствием в ИИ-системах ответов - всё из единого центра управления на вашем сервере.'),
('ru', 'featuretour', 'tour_step1_heading', 'Добро пожаловать в SEO Panel'),
('ru', 'featuretour', 'tour_step1_info', 'Это короткий тур по тому, где всё находится, и заодно он настроит ваш первый сайт. Пропустите его в любой момент или откройте снова позже по ссылке <strong>Обучающий тур</strong> в верхнем меню.'),
('ru', 'featuretour', 'tour_step2_already_set', 'Всё готово - здесь делать нечего.'),
('ru', 'featuretour', 'tour_step2_heading', 'Добавьте свой первый сайт'),
('ru', 'featuretour', 'tour_step2_intro', 'Всё ниже - панель управления, отслеживание позиций, проверки - требует хотя бы одного сайта. Это займёт пару секунд:'),
('ru', 'featuretour', 'tour_step3_body', 'Самый быстрый способ получить реальные данные о позициях и SERP без поиска собственных ключей DataForSEO или MOZ - бесплатная регистрация, без банковской карты.'),
('ru', 'featuretour', 'tour_step3_heading', 'API Seo Panel'),
('ru', 'featuretour', 'tour_step4_body', 'Стоит сделать перед меню Инструменты: без этих подключений у некоторых инструментов пока не будет реальных данных для отображения. Нажмите на любую строку, чтобы перейти туда напрямую в новой вкладке:'),
('ru', 'featuretour', 'tour_step4_heading', 'Настройки: где живут ваши интеграции'),
('ru', 'featuretour', 'tour_step5_body', 'SEO Panel проверяет позиции, запускает проверки и создаёт отчёты по расписанию - но только когда ваш сервер действительно вызывает <code>cron.php</code>. Ничего из вышеперечисленного не работает, если это не настроено.'),
('ru', 'featuretour', 'tour_step5_heading', 'Настройте задачу cron'),
('ru', 'featuretour', 'tour_step6_body', 'Выберите сайт из выпадающего списка, чтобы увидеть тенденции позиций, основные ключевые запросы и недавнюю активность на одном экране.'),
('ru', 'featuretour', 'tour_step6_heading', 'Ваша панель управления'),
('ru', 'featuretour', 'tour_step6_info', 'Каждый подключённый инструмент (Analytics, Социальные сети, Отзывы...) также получает свою вкладку здесь, в панели управления.'),
('ru', 'featuretour', 'tour_step7_body', 'Меню <strong>Инструменты</strong> - это место, где происходит настоящая работа: двенадцать инструментов в одном месте. Нажмите на любую карточку, чтобы открыть её в новой вкладке:'),
('ru', 'featuretour', 'tour_step8_body', 'Меню <strong>Плагины</strong> расширяет SEO Panel за пределы основных инструментов. Нажмите на любую карточку, чтобы открыть её в новой вкладке:'),
('ru', 'featuretour', 'tour_step9_body', 'Вот и вся структура. Всё вышеперечисленное станет намного понятнее, как только начнут поступать реальные данные.'),
('ru', 'featuretour', 'tour_step9_heading', 'Всё готово'),
('ru', 'featuretour', 'tour_step9_info', 'Хотите посмотреть снова? Ищите <strong>Обучающий тур</strong> в верхней панели меню.'),
('ru', 'featuretour', 'tour_system_desc', 'Язык, часовой пояс, постраничный вывод и другие общие настройки приложения'),
('ru', 'featuretour', 'tour_website_added', 'Сайт и основной ключевой запрос добавлены.'),
('ru', 'featuretour', 'tour_website_plural', 'сайтов'),
('ru', 'featuretour', 'tour_website_singular', 'сайт'),
('zh', 'featuretour', 'tour_add_website_btn', '添加网站'),
('zh', 'featuretour', 'tour_admin_only_cron', '这是服务器设置，只有您账户的管理员才能进行配置。当前状态：'),
('zh', 'featuretour', 'tour_admin_only_settings', '这些是账户级别的设置，只有管理员才能更改 - 这里无需操作。'),
('zh', 'featuretour', 'tour_admin_only_spapi', '这是账户级别的设置，只有管理员才能注册。当前状态：'),
('zh', 'featuretour', 'tour_back', '上一步'),
('zh', 'featuretour', 'tour_connected', '已连接'),
('zh', 'featuretour', 'tour_cron_desc', '添加到服务器 crontab 的确切命令'),
('zh', 'featuretour', 'tour_dashboard_desc', '登录后进入的屏幕'),
('zh', 'featuretour', 'tour_detected', '已检测到'),
('zh', 'featuretour', 'tour_dfs_desc', '排名检查和 SERP 数据背后的数据提供商'),
('zh', 'featuretour', 'tour_field_keyword', '主要关键词'),
('zh', 'featuretour', 'tour_field_review', '评论链接'),
('zh', 'featuretour', 'tour_field_social', '社交媒体'),
('zh', 'featuretour', 'tour_field_website_name', '网站名称'),
('zh', 'featuretour', 'tour_generic_error', '出现问题，请重试。'),
('zh', 'featuretour', 'tour_get_started', '开始使用'),
('zh', 'featuretour', 'tour_google_desc', '连接 Analytics 和 Search Console'),
('zh', 'featuretour', 'tour_important', '重要'),
('zh', 'featuretour', 'tour_keyword_plural', '个关键词'),
('zh', 'featuretour', 'tour_keyword_singular', '个关键词'),
('zh', 'featuretour', 'tour_localai_desc', '将 AI 驱动的功能指向您自己的 Ollama 服务器'),
('zh', 'featuretour', 'tour_mail_desc', '定时报告、密码重置和注册邮件都通过此处发送'),
('zh', 'featuretour', 'tour_more_websites', '+ 还有 %d 个%s - 点击上方管理全部'),
('zh', 'featuretour', 'tour_moz_desc', '域名权重、页面权重和垃圾分数'),
('zh', 'featuretour', 'tour_next', '下一步'),
('zh', 'featuretour', 'tour_no_plugins', '您的账户目前没有可用的插件。'),
('zh', 'featuretour', 'tour_none_option', '-- 无 --'),
('zh', 'featuretour', 'tour_not_added_suffix', '未添加：%s'),
('zh', 'featuretour', 'tour_not_detected', '尚未检测到'),
('zh', 'featuretour', 'tour_not_set_up', '未设置'),
('zh', 'featuretour', 'tour_optional_badge', '可选'),
('zh', 'featuretour', 'tour_optional_extras', '可选附加项'),
('zh', 'featuretour', 'tour_optional_option', '-- 可选 --'),
('zh', 'featuretour', 'tour_placeholder_keyword', '例如：seo 软件'),
('zh', 'featuretour', 'tour_placeholder_profile_url', '主页链接'),
('zh', 'featuretour', 'tour_placeholder_review_url', '评论页面链接'),
('zh', 'featuretour', 'tour_placeholder_website_name', '例如：我的公司'),
('zh', 'featuretour', 'tour_placeholder_website_url', 'https://example.com'),
('zh', 'featuretour', 'tour_proxy_desc', '用于抓取和目录提交的代理'),
('zh', 'featuretour', 'tour_refresh', '刷新'),
('zh', 'featuretour', 'tour_refresh_tooltip', '刚在其他标签页中保存了内容？点击刷新以更新连接状态标签'),
('zh', 'featuretour', 'tour_review_hint', '链接必须包含平台名称，例如 Yelp 链接应包含 \"yelp\"。'),
('zh', 'featuretour', 'tour_review_hint_dynamic', '链接必须包含 \"%s\"（例如 %s 链接应包含该词）。'),
('zh', 'featuretour', 'tour_review_not_added', '评论链接'),
('zh', 'featuretour', 'tour_seopanel_api_desc', '实时排名和 SERP 数据，几分钟内即可就绪'),
('zh', 'featuretour', 'tour_seopanel_api_label', 'Seo Panel API'),
('zh', 'featuretour', 'tour_skip', '跳过'),
('zh', 'featuretour', 'tour_skip_tooltip', '跳过此导览'),
('zh', 'featuretour', 'tour_social_not_added', '社交媒体链接'),
('zh', 'featuretour', 'tour_step1_body', 'SEO Panel 可追踪排名、审核您的网站、检查反向链接，并监控您在 AI 问答引擎中的表现 - 这一切都在一个自托管的控制中心完成。'),
('zh', 'featuretour', 'tour_step1_heading', '欢迎使用 SEO Panel'),
('zh', 'featuretour', 'tour_step1_info', '这是一次简短的导览，帮您了解各项功能的位置，同时顺便设置您的第一个网站。随时可以跳过，之后也可以通过顶部菜单中的<strong>设置导览</strong>链接重新查看。'),
('zh', 'featuretour', 'tour_step2_already_set', '一切就绪 - 这里无需操作。'),
('zh', 'featuretour', 'tour_step2_heading', '添加您的第一个网站'),
('zh', 'featuretour', 'tour_step2_intro', '以下所有功能 - 仪表盘、排名追踪、审核 - 都需要至少一个网站才能运作。只需几秒钟：'),
('zh', 'featuretour', 'tour_step3_body', '无需自行申请 DataForSEO 或 MOZ 密钥，即可获取真实排名和 SERP 数据的最快方式 - 免费注册，无需信用卡。'),
('zh', 'featuretour', 'tour_step3_heading', 'Seo Panel API'),
('zh', 'featuretour', 'tour_step4_body', '建议在进入工具菜单前完成此项：未连接这些功能前，多个工具将暂无真实数据可显示。点击任意行即可在新标签页中直接前往：'),
('zh', 'featuretour', 'tour_step4_heading', '设置：您的各项集成所在位置'),
('zh', 'featuretour', 'tour_step5_body', 'SEO Panel 会按计划检查排名、运行审核并生成报告 - 但前提是您的服务器确实在调用 <code>cron.php</code>。如果此项未运行，以上所有功能都无法正常工作。'),
('zh', 'featuretour', 'tour_step5_heading', '设置 Cron 任务'),
('zh', 'featuretour', 'tour_step6_body', '从下拉菜单中选择一个网站，即可一目了然地查看其排名趋势、热门关键词和最近动态。'),
('zh', 'featuretour', 'tour_step6_heading', '您的仪表盘'),
('zh', 'featuretour', 'tour_step6_info', '您连接的每个工具（Analytics、社交媒体、评论...）在这里也会拥有自己的仪表盘标签页。'),
('zh', 'featuretour', 'tour_step7_body', '<strong>工具</strong>菜单是实际工作发生的地方 - 十二种工具集于一处。点击任意卡片即可在新标签页中打开：'),
('zh', 'featuretour', 'tour_step8_body', '<strong>插件</strong>菜单可将 SEO Panel 的功能扩展到核心工具之外。点击任意卡片即可在新标签页中打开：'),
('zh', 'featuretour', 'tour_step9_body', '以上就是整体布局。一旦真实数据开始出现，以上内容将变得更加清晰易懂。'),
('zh', 'featuretour', 'tour_step9_heading', '一切就绪'),
('zh', 'featuretour', 'tour_step9_info', '想再看一次吗？在顶部菜单栏中查找<strong>设置导览</strong>。'),
('zh', 'featuretour', 'tour_system_desc', '语言、时区、分页设置及其他应用全局默认设置'),
('zh', 'featuretour', 'tour_website_added', '网站和主要关键词已添加。'),
('zh', 'featuretour', 'tour_website_plural', '个网站'),
('zh', 'featuretour', 'tour_website_singular', '个网站'),
('ja', 'featuretour', 'tour_add_website_btn', 'ウェブサイトを追加'),
('ja', 'featuretour', 'tour_admin_only_cron', 'これはサーバー設定のため、アカウントの管理者のみが設定できます。現在の状態：'),
('ja', 'featuretour', 'tour_admin_only_settings', 'これらはアカウント全体の設定のため、管理者のみが変更できます - ここでは操作の必要はありません。'),
('ja', 'featuretour', 'tour_admin_only_spapi', 'これはアカウント全体の設定のため、管理者のみが登録できます。現在の状態：'),
('ja', 'featuretour', 'tour_back', '戻る'),
('ja', 'featuretour', 'tour_connected', '接続済み'),
('ja', 'featuretour', 'tour_cron_desc', 'サーバーの crontab に追加する正確なコマンド'),
('ja', 'featuretour', 'tour_dashboard_desc', 'ログイン後に表示される画面'),
('ja', 'featuretour', 'tour_detected', '検出済み'),
('ja', 'featuretour', 'tour_dfs_desc', '順位チェックと SERP データを提供するデータプロバイダー'),
('ja', 'featuretour', 'tour_field_keyword', 'メインキーワード'),
('ja', 'featuretour', 'tour_field_review', 'レビューリンク'),
('ja', 'featuretour', 'tour_field_social', 'ソーシャルメディア'),
('ja', 'featuretour', 'tour_field_website_name', 'ウェブサイト名'),
('ja', 'featuretour', 'tour_generic_error', '問題が発生しました。もう一度お試しください。'),
('ja', 'featuretour', 'tour_get_started', '開始する'),
('ja', 'featuretour', 'tour_google_desc', 'Analytics と Search Console を接続'),
('ja', 'featuretour', 'tour_important', '重要'),
('ja', 'featuretour', 'tour_keyword_plural', '個のキーワード'),
('ja', 'featuretour', 'tour_keyword_singular', '個のキーワード'),
('ja', 'featuretour', 'tour_localai_desc', 'AI機能を自分のOllamaサーバーに接続'),
('ja', 'featuretour', 'tour_mail_desc', '定期レポート、パスワードリセット、登録メールはすべてここを経由します'),
('ja', 'featuretour', 'tour_more_websites', '+ さらに %d %s - 上のリンクからまとめて管理できます'),
('ja', 'featuretour', 'tour_moz_desc', 'ドメインオーソリティ、ページオーソリティ、スパムスコア'),
('ja', 'featuretour', 'tour_next', '次へ'),
('ja', 'featuretour', 'tour_no_plugins', '現在、このアカウントで利用できるプラグインはありません。'),
('ja', 'featuretour', 'tour_none_option', '-- なし --'),
('ja', 'featuretour', 'tour_not_added_suffix', '追加されませんでした：%s'),
('ja', 'featuretour', 'tour_not_detected', '未検出'),
('ja', 'featuretour', 'tour_not_set_up', '未設定'),
('ja', 'featuretour', 'tour_optional_badge', '任意'),
('ja', 'featuretour', 'tour_optional_extras', '任意の追加項目'),
('ja', 'featuretour', 'tour_optional_option', '-- 任意 --'),
('ja', 'featuretour', 'tour_placeholder_keyword', '例：seo ソフト'),
('ja', 'featuretour', 'tour_placeholder_profile_url', 'プロフィールURL'),
('ja', 'featuretour', 'tour_placeholder_review_url', 'レビューページのURL'),
('ja', 'featuretour', 'tour_placeholder_website_name', '例：自社サイト'),
('ja', 'featuretour', 'tour_placeholder_website_url', 'https://example.com'),
('ja', 'featuretour', 'tour_proxy_desc', 'クロールとディレクトリ送信に使用するプロキシ'),
('ja', 'featuretour', 'tour_refresh', '更新'),
('ja', 'featuretour', 'tour_refresh_tooltip', '別のタブで何かを保存しましたか？更新して接続バッジを最新にします'),
('ja', 'featuretour', 'tour_review_hint', 'URLにはプラットフォーム名を含める必要があります。例：Yelpのリンクには「yelp」を含めてください。'),
('ja', 'featuretour', 'tour_review_hint_dynamic', 'URLには「%s」を含める必要があります（例：%sのリンクにはその単語を含めてください）。'),
('ja', 'featuretour', 'tour_review_not_added', 'レビューリンク'),
('ja', 'featuretour', 'tour_seopanel_api_desc', '順位追跡とSERPデータが数分で利用可能に'),
('ja', 'featuretour', 'tour_seopanel_api_label', 'Seo Panel API'),
('ja', 'featuretour', 'tour_skip', 'スキップ'),
('ja', 'featuretour', 'tour_skip_tooltip', 'このツアーをスキップ'),
('ja', 'featuretour', 'tour_social_not_added', 'ソーシャルメディアリンク'),
('ja', 'featuretour', 'tour_step1_body', 'SEO Panelは順位を追跡し、サイトを監査し、被リンクをチェックし、AI回答エンジンでの表示状況を監視します - すべてを1つのセルフホスト型コントロールルームで行えます。'),
('ja', 'featuretour', 'tour_step1_heading', 'SEO Panelへようこそ'),
('ja', 'featuretour', 'tour_step1_info', 'これは各機能の場所を紹介する短いツアーで、途中で最初のウェブサイトも設定します。いつでもスキップでき、後で上部メニューの<strong>セットアップツアー</strong>リンクから再度開けます。'),
('ja', 'featuretour', 'tour_step2_already_set', '設定完了です - ここでは操作の必要はありません。'),
('ja', 'featuretour', 'tour_step2_heading', '最初のウェブサイトを追加'),
('ja', 'featuretour', 'tour_step2_intro', '以下の機能（ダッシュボード、順位追跡、監査）はすべて、少なくとも1つのウェブサイトが必要です。数秒で完了します：'),
('ja', 'featuretour', 'tour_step3_body', '自分でDataForSEOやMOZのキーを取得しなくても、実際の順位とSERPデータをすぐに利用できる最速の方法です - 無料登録、クレジットカード不要。'),
('ja', 'featuretour', 'tour_step3_heading', 'Seo Panel API'),
('ja', 'featuretour', 'tour_step4_body', 'ツールメニューに進む前に済ませておく価値があります。これらの接続がないと、複数のツールにまだ実データが表示されません。行をクリックすると新しいタブで直接そこへ移動できます：'),
('ja', 'featuretour', 'tour_step4_heading', '設定：連携機能の場所'),
('ja', 'featuretour', 'tour_step5_body', 'SEO Panelはスケジュールに従って順位チェック、監査の実行、レポートの生成を行いますが、これはサーバーが実際に<code>cron.php</code>を呼び出している場合のみ機能します。これが動作していないと、上記のすべてが意味を持ちません。'),
('ja', 'featuretour', 'tour_step5_heading', 'Cronジョブを設定'),
('ja', 'featuretour', 'tour_step6_body', 'ドロップダウンからウェブサイトを選択すると、順位の推移、上位キーワード、最近のアクティビティを一目で確認できます。'),
('ja', 'featuretour', 'tour_step6_heading', 'あなたのダッシュボード'),
('ja', 'featuretour', 'tour_step6_info', '接続した各ツール（Analytics、ソーシャルメディア、レビューなど）もここに専用のダッシュボードタブを持ちます。'),
('ja', 'featuretour', 'tour_step7_body', '<strong>ツール</strong>メニューは実際の作業が行われる場所です - 12種類のツールが1か所にまとまっています。カードをクリックすると新しいタブで開きます：'),
('ja', 'featuretour', 'tour_step8_body', '<strong>プラグイン</strong>メニューはコアツールを超えてSEO Panelを拡張します。カードをクリックすると新しいタブで開きます：'),
('ja', 'featuretour', 'tour_step9_body', '以上が全体像です。実際のデータが入り始めると、上記の内容がより理解しやすくなります。'),
('ja', 'featuretour', 'tour_step9_heading', '準備完了です'),
('ja', 'featuretour', 'tour_step9_info', 'もう一度見たいですか？上部メニューバーの<strong>セットアップツアー</strong>をお探しください。'),
('ja', 'featuretour', 'tour_system_desc', '言語、タイムゾーン、ページネーションなど、アプリ全体のデフォルト設定'),
('ja', 'featuretour', 'tour_website_added', 'ウェブサイトとメインキーワードを追加しました。'),
('ja', 'featuretour', 'tour_website_plural', '個のウェブサイト'),
('ja', 'featuretour', 'tour_website_singular', '個のウェブサイト'),
('ar', 'featuretour', 'tour_add_website_btn', 'إضافة موقع'),
('ar', 'featuretour', 'tour_admin_only_cron', 'هذا إعداد خاص بالخادم، ولا يمكن تهيئته إلا من قبل مسؤول حسابك. الحالة الحالية:'),
('ar', 'featuretour', 'tour_admin_only_settings', 'هذه إعدادات على مستوى الحساب بالكامل، ولا يمكن تغييرها إلا من قبل مسؤول - لا يوجد شيء لفعله هنا.'),
('ar', 'featuretour', 'tour_admin_only_spapi', 'هذا إعداد على مستوى الحساب بالكامل، ولا يمكن تسجيله إلا من قبل مسؤول. الحالة الحالية:'),
('ar', 'featuretour', 'tour_back', 'رجوع'),
('ar', 'featuretour', 'tour_connected', 'متصل'),
('ar', 'featuretour', 'tour_cron_desc', 'الأمر الدقيق لإضافته إلى crontab الخاص بخادمك'),
('ar', 'featuretour', 'tour_dashboard_desc', 'الشاشة التي تصل إليها بعد تسجيل الدخول'),
('ar', 'featuretour', 'tour_detected', 'تم الاكتشاف'),
('ar', 'featuretour', 'tour_dfs_desc', 'مزود البيانات وراء فحص الترتيب وبيانات SERP'),
('ar', 'featuretour', 'tour_field_keyword', 'الكلمة المفتاحية الرئيسية'),
('ar', 'featuretour', 'tour_field_review', 'رابط التقييم'),
('ar', 'featuretour', 'tour_field_social', 'وسائل التواصل الاجتماعي'),
('ar', 'featuretour', 'tour_field_website_name', 'اسم الموقع'),
('ar', 'featuretour', 'tour_generic_error', 'حدث خطأ ما. يرجى المحاولة مرة أخرى.'),
('ar', 'featuretour', 'tour_get_started', 'ابدأ الآن'),
('ar', 'featuretour', 'tour_google_desc', 'اربط Analytics و Search Console'),
('ar', 'featuretour', 'tour_important', 'مهم'),
('ar', 'featuretour', 'tour_keyword_plural', 'كلمات مفتاحية'),
('ar', 'featuretour', 'tour_keyword_singular', 'كلمة مفتاحية'),
('ar', 'featuretour', 'tour_localai_desc', 'وجّه ميزات الذكاء الاصطناعي إلى خادم Ollama الخاص بك'),
('ar', 'featuretour', 'tour_mail_desc', 'التقارير المجدولة وإعادة تعيين كلمة المرور ورسائل التسجيل تمر جميعها من هنا'),
('ar', 'featuretour', 'tour_more_websites', '+ %d %s إضافية - انقر أعلاه لإدارتها جميعًا'),
('ar', 'featuretour', 'tour_moz_desc', 'سلطة النطاق، سلطة الصفحة، ودرجة السبام'),
('ar', 'featuretour', 'tour_next', 'التالي'),
('ar', 'featuretour', 'tour_no_plugins', 'لا توجد إضافات متاحة لحسابك حاليًا.'),
('ar', 'featuretour', 'tour_none_option', '-- لا شيء --'),
('ar', 'featuretour', 'tour_not_added_suffix', 'لم تتم الإضافة: %s'),
('ar', 'featuretour', 'tour_not_detected', 'لم يتم الاكتشاف بعد'),
('ar', 'featuretour', 'tour_not_set_up', 'غير مُهيأ'),
('ar', 'featuretour', 'tour_optional_badge', 'اختياري'),
('ar', 'featuretour', 'tour_optional_extras', 'إضافات اختيارية'),
('ar', 'featuretour', 'tour_optional_option', '-- اختياري --'),
('ar', 'featuretour', 'tour_placeholder_keyword', 'مثال: برنامج seo'),
('ar', 'featuretour', 'tour_placeholder_profile_url', 'رابط الملف الشخصي'),
('ar', 'featuretour', 'tour_placeholder_review_url', 'رابط صفحة التقييمات'),
('ar', 'featuretour', 'tour_placeholder_website_name', 'مثال: شركتي'),
('ar', 'featuretour', 'tour_placeholder_website_url', 'https://example.com'),
('ar', 'featuretour', 'tour_proxy_desc', 'البروكسيات المستخدمة للزحف وإرسال الدلائل'),
('ar', 'featuretour', 'tour_refresh', 'تحديث'),
('ar', 'featuretour', 'tour_refresh_tooltip', 'هل قمت للتو بحفظ شيء في تبويب آخر؟ حدّث لتحديث شارات الاتصال'),
('ar', 'featuretour', 'tour_review_hint', 'يجب أن يحتوي الرابط على اسم المنصة، مثال: يجب أن يتضمن رابط Yelp كلمة \"yelp\".'),
('ar', 'featuretour', 'tour_review_hint_dynamic', 'يجب أن يحتوي الرابط على \"%s\" (مثال: يجب أن يتضمن رابط %s تلك الكلمة).'),
('ar', 'featuretour', 'tour_review_not_added', 'رابط التقييم'),
('ar', 'featuretour', 'tour_seopanel_api_desc', 'بيانات الترتيب و SERP في الوقت الفعلي، جاهزة خلال دقيقتين'),
('ar', 'featuretour', 'tour_seopanel_api_label', 'واجهة برمجة Seo Panel'),
('ar', 'featuretour', 'tour_skip', 'تخطي'),
('ar', 'featuretour', 'tour_skip_tooltip', 'تخطي هذه الجولة'),
('ar', 'featuretour', 'tour_social_not_added', 'رابط التواصل الاجتماعي'),
('ar', 'featuretour', 'tour_step1_body', 'يتتبع SEO Panel الترتيب، ويدقق مواقعك، ويتحقق من الروابط الخلفية، ويراقب ظهورك في محركات إجابات الذكاء الاصطناعي - كل ذلك من غرفة تحكم واحدة مستضافة ذاتيًا.'),
('ar', 'featuretour', 'tour_step1_heading', 'مرحبًا بك في SEO Panel'),
('ar', 'featuretour', 'tour_step1_info', 'هذه جولة قصيرة توضح أماكن كل شيء، وتقوم أيضًا بإعداد موقعك الأول في الطريق. تخطّها في أي وقت، أو أعد فتحها لاحقًا من رابط <strong>جولة الإعداد</strong> في القائمة العلوية.'),
('ar', 'featuretour', 'tour_step2_already_set', 'كل شيء جاهز - لا يوجد شيء لفعله هنا.'),
('ar', 'featuretour', 'tour_step2_heading', 'أضف موقعك الأول'),
('ar', 'featuretour', 'tour_step2_intro', 'كل ما يلي - لوحة التحكم، تتبع الترتيب، عمليات التدقيق - يحتاج إلى موقع واحد على الأقل ليعمل. يستغرق ذلك بضع ثوانٍ:'),
('ar', 'featuretour', 'tour_step3_body', 'أسرع طريقة للحصول على بيانات ترتيب و SERP حقيقية دون البحث عن مفاتيح DataForSEO أو MOZ الخاصة بك - تسجيل مجاني، بدون بطاقة ائتمان.'),
('ar', 'featuretour', 'tour_step3_heading', 'واجهة برمجة Seo Panel'),
('ar', 'featuretour', 'tour_step4_body', 'يستحق القيام به قبل قائمة الأدوات: بدون هذه الاتصالات، لن تحتوي عدة أدوات على بيانات حقيقية لعرضها بعد. انقر على أي صف للانتقال إليه مباشرة في تبويب جديد:'),
('ar', 'featuretour', 'tour_step4_heading', 'الإعدادات: أين تعيش تكاملاتك'),
('ar', 'featuretour', 'tour_step5_body', 'يتحقق SEO Panel من الترتيب، ويجري عمليات تدقيق، وينشئ تقارير وفق جدول زمني - ولكن فقط بمجرد أن يستدعي خادمك فعليًا <code>cron.php</code>. لا شيء مما سبق مهم إذا لم يكن هذا يعمل.'),
('ar', 'featuretour', 'tour_step5_heading', 'إعداد مهمة Cron'),
('ar', 'featuretour', 'tour_step6_body', 'اختر موقعًا من القائمة المنسدلة لرؤية اتجاهات الترتيب والكلمات المفتاحية الرئيسية والنشاط الأخير بنظرة واحدة.'),
('ar', 'featuretour', 'tour_step6_heading', 'لوحة التحكم الخاصة بك'),
('ar', 'featuretour', 'tour_step6_info', 'كل أداة تقوم بتوصيلها (Analytics، وسائل التواصل الاجتماعي، التقييمات...) تحصل أيضًا على تبويب خاص بها هنا في لوحة التحكم.'),
('ar', 'featuretour', 'tour_step7_body', 'قائمة <strong>الأدوات</strong> هي المكان الذي يحدث فيه العمل الفعلي - اثنتا عشرة أداة في مكان واحد. انقر على أي بطاقة لفتحها في تبويب جديد:'),
('ar', 'featuretour', 'tour_step8_body', 'تعمل قائمة <strong>الإضافات</strong> على توسيع SEO Panel إلى ما هو أبعد من الأدوات الأساسية. انقر على أي بطاقة لفتحها في تبويب جديد:'),
('ar', 'featuretour', 'tour_step9_body', 'هذا هو المخطط العام. كل ما سبق سيصبح أكثر منطقية بمجرد أن تبدأ البيانات الحقيقية في الظهور.'),
('ar', 'featuretour', 'tour_step9_heading', 'كل شيء جاهز'),
('ar', 'featuretour', 'tour_step9_info', 'تريد رؤيتها مرة أخرى؟ ابحث عن <strong>جولة الإعداد</strong> في شريط القائمة العلوي.'),
('ar', 'featuretour', 'tour_system_desc', 'اللغة، المنطقة الزمنية، الترقيم، وإعدادات افتراضية أخرى على مستوى التطبيق'),
('ar', 'featuretour', 'tour_website_added', 'تمت إضافة الموقع والكلمة المفتاحية الرئيسية.'),
('ar', 'featuretour', 'tour_website_plural', 'مواقع'),
('ar', 'featuretour', 'tour_website_singular', 'موقع');

-- "Setup Tour" itself (the modal header and the top-menu reopen link,
-- both read from the existing common category via $spText['common']
-- ['Setup Tour']) - translated to match the tour body text above,
-- which references this same label by name (e.g. "look for Setup Tour
-- in the top menu").
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('es', 'common', 'Setup Tour', 'Recorrido de configuración'),
('fr', 'common', 'Setup Tour', 'Visite guidée'),
('de', 'common', 'Setup Tour', 'Einrichtungstour'),
('pt-br', 'common', 'Setup Tour', 'Tour de Configuração'),
('it', 'common', 'Setup Tour', 'Tour di configurazione'),
('ru', 'common', 'Setup Tour', 'Обучающий тур'),
('zh', 'common', 'Setup Tour', '设置导览'),
('ja', 'common', 'Setup Tour', 'セットアップツアー'),
('ar', 'common', 'Setup Tour', 'جولة الإعداد');
-- Setup Tour additions: Site Auditor's own Cron Command row and the
-- external ping trigger hint in the Cron step (siteauditorcron.php runs
-- separately from cron.php, and not every host allows a real crontab -
-- see feature_tour_popup.ctp.php step 5). 'External ping trigger' and
-- 'Scheduler Health' only had an 'en' row (added with the Zero-Setup
-- Scheduler feature itself, before any translator touched non-English
-- languages) - both are interpolated into the tour text above via %s,
-- so they're translated here too rather than left showing English.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('es', 'featuretour', 'tour_cron_ping_hint', '¿No puedes configurar un crontab real en tu servidor?'),
('fr', 'featuretour', 'tour_cron_ping_hint', 'Vous ne pouvez pas configurer un vrai crontab sur votre hébergeur ?'),
('de', 'featuretour', 'tour_cron_ping_hint', 'Kein echtes Crontab auf Ihrem Server möglich?'),
('pt-br', 'featuretour', 'tour_cron_ping_hint', 'Não consegue configurar um crontab real no seu servidor?'),
('it', 'featuretour', 'tour_cron_ping_hint', 'Non riesci a configurare un vero crontab sul tuo host?'),
('ru', 'featuretour', 'tour_cron_ping_hint', 'Не можете настроить настоящий crontab на своём хосте?'),
('zh', 'featuretour', 'tour_cron_ping_hint', '无法在您的主机上设置真正的 crontab？'),
('ja', 'featuretour', 'tour_cron_ping_hint', 'サーバーで実際のcrontabを設定できませんか？'),
('ar', 'featuretour', 'tour_cron_ping_hint', 'لا يمكنك إعداد crontab حقيقي على خادمك؟'),
('es', 'featuretour', 'tour_cron_ping_hint_link', 'Usa %s en su lugar.'),
('fr', 'featuretour', 'tour_cron_ping_hint_link', 'Utilisez %s à la place.'),
('de', 'featuretour', 'tour_cron_ping_hint_link', 'Verwenden Sie stattdessen %s.'),
('pt-br', 'featuretour', 'tour_cron_ping_hint_link', 'Use %s em vez disso.'),
('it', 'featuretour', 'tour_cron_ping_hint_link', 'Usa %s invece.'),
('ru', 'featuretour', 'tour_cron_ping_hint_link', 'Используйте %s вместо этого.'),
('zh', 'featuretour', 'tour_cron_ping_hint_link', '请改用%s。'),
('ja', 'featuretour', 'tour_cron_ping_hint_link', '代わりに%sを使用してください。'),
('ar', 'featuretour', 'tour_cron_ping_hint_link', 'استخدم %s بدلاً من ذلك.'),
('es', 'featuretour', 'tour_sa_cron_title', 'Comando Cron de %s'),
('fr', 'featuretour', 'tour_sa_cron_title', 'Commande Cron de %s'),
('de', 'featuretour', 'tour_sa_cron_title', '%s Cron-Befehl'),
('pt-br', 'featuretour', 'tour_sa_cron_title', 'Comando Cron do %s'),
('it', 'featuretour', 'tour_sa_cron_title', 'Comando Cron di %s'),
('ru', 'featuretour', 'tour_sa_cron_title', 'Команда Cron для %s'),
('zh', 'featuretour', 'tour_sa_cron_title', '%s Cron 命令'),
('ja', 'featuretour', 'tour_sa_cron_title', '%s のCronコマンド'),
('ar', 'featuretour', 'tour_sa_cron_title', 'أمر Cron الخاص بـ %s'),
('es', 'featuretour', 'tour_sa_cron_desc', 'Un comando independiente para auditorías del sitio programadas'),
('fr', 'featuretour', 'tour_sa_cron_desc', 'Une commande distincte pour les audits de site planifiés'),
('de', 'featuretour', 'tour_sa_cron_desc', 'Ein separater Befehl für geplante Website-Prüfungen'),
('pt-br', 'featuretour', 'tour_sa_cron_desc', 'Um comando separado para auditorias de site agendadas'),
('it', 'featuretour', 'tour_sa_cron_desc', 'Un comando separato per le verifiche del sito pianificate'),
('ru', 'featuretour', 'tour_sa_cron_desc', 'Отдельная команда для запланированных аудитов сайта'),
('zh', 'featuretour', 'tour_sa_cron_desc', '用于定期站点审计的独立命令'),
('ja', 'featuretour', 'tour_sa_cron_desc', '定期的なサイト監査のための別コマンド'),
('ar', 'featuretour', 'tour_sa_cron_desc', 'أمر منفصل لعمليات تدقيق الموقع المجدولة'),
('es', 'panel', 'External ping trigger', 'Activador de ping externo'),
('fr', 'panel', 'External ping trigger', 'Déclencheur ping externe'),
('de', 'panel', 'External ping trigger', 'Externer Ping-Trigger'),
('pt-br', 'panel', 'External ping trigger', 'Gatilho de ping externo'),
('it', 'panel', 'External ping trigger', 'Trigger ping esterno'),
('ru', 'panel', 'External ping trigger', 'Внешний пинг-триггер'),
('zh', 'panel', 'External ping trigger', '外部 Ping 触发器'),
('ja', 'panel', 'External ping trigger', '外部Pingトリガー'),
('ar', 'panel', 'External ping trigger', 'محفز الـ ping الخارجي'),
('es', 'panel', 'Scheduler Health', 'Estado del planificador'),
('fr', 'panel', 'Scheduler Health', 'État du planificateur'),
('de', 'panel', 'Scheduler Health', 'Planer-Status'),
('pt-br', 'panel', 'Scheduler Health', 'Saúde do agendador'),
('it', 'panel', 'Scheduler Health', 'Stato dello scheduler'),
('ru', 'panel', 'Scheduler Health', 'Состояние планировщика'),
('zh', 'panel', 'Scheduler Health', '调度器状态'),
('ja', 'panel', 'Scheduler Health', 'スケジューラの状態'),
('ar', 'panel', 'Scheduler Health', 'حالة المجدول');

--
-- MetaTagGenerator: persist what it generates (canonical URL, viewport,
-- OG/Twitter Card, rating/distribution/robots/revisit-after/expires,
-- charset, language) directly on the website record instead of only
-- producing a copy-paste snippet - see the plugin's own createmetatag()
-- for how these are written.
--
ALTER TABLE `websites` ADD COLUMN `canonical_url` varchar(255) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `viewport` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `websites` ADD COLUMN `copyright` varchar(255) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `expires` varchar(50) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `rating` varchar(20) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `distribution` varchar(20) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `robots` varchar(30) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `revisit_after` varchar(20) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `og_title` varchar(100) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `og_description` varchar(300) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `og_image` varchar(255) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `og_url` varchar(255) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `twitter_card` varchar(30) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `meta_charset` varchar(20) DEFAULT NULL;
ALTER TABLE `websites` ADD COLUMN `lang_code` varchar(10) DEFAULT NULL;

--
-- Two of the scheduler's own prune queries (pruneOldJobQueueRows(),
-- pruneOldJobTimingRows()'s first DELETE) ran full table scans on their
-- own highest-write-volume tables - no existing index covers a bare
-- updated_at/started_at predicate.
--
ALTER TABLE `job_queue` ADD KEY `status_updated` (`status`,`updated_at`);
ALTER TABLE `cron_job_timing` ADD KEY `started_at` (`started_at`);
