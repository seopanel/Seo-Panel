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
('Initial Setup Wizard', 'SP_SETUP_WIZARD', '1', 'system', 'bool', 0);

-- 'settings'-category label for the above, kept even while hidden so it's
-- ready whenever this is switched back to display=1 in a future version.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_SETUP_WIZARD', 'Initial Setup Wizard');

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
('AI Overview reference retention (days)', 'SP_AIO_RETENTION_DAYS', '90', 'report', 'small', 1),
('AI Overview rolling window (observations)', 'SP_AIO_ROLLING_WINDOW', '7', 'report', 'small', 1),
('AI Overview data considered stale after (days)', 'SP_AIO_STALE_DAYS', '7', 'report', 'small', 1),
('AI Overview subdomain match policy (registrable or exact)', 'SP_AIO_SUBDOMAIN_MATCH', 'registrable', 'report', 'small', 1);

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

-- Temporary rollout flag: old monolithic *Cron() bodies vs. new
-- enqueue+drain bodies, selected per tool inside routeCronJob(). Defaults
-- off for existing installs; flipped on (and eventually deleted, along with
-- the old bodies) once real installs confirm clean job_queue behavior.
INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable resumable job queue for cron execution', 'SP_JOB_QUEUE_ENABLED', '0', 'report', 'small', 0);

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
