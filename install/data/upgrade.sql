--
-- Seo Panel 7.2.0 changes
--
update `settings` set set_val='7.2.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

-- AI Perception: live-search grounding + citation-level detail (see
-- AiPerceptionController::__callOpenAI()/__callAnthropic()/__callGoogleGemini())
ALTER TABLE `llm_api_keys` ADD COLUMN `live_search` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'when 1, checks against this provider use its live web-search-grounded model/tool instead of the plain chat model - see AiPerceptionController::__callOpenAI()/__callAnthropic()/__callGoogleGemini()' AFTER `api_key`;
ALTER TABLE `llm_perception_results` ADD COLUMN `grounded` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'whether this check used the provider live web-search grounded model/tool rather than its plain chat model' AFTER `sentiment`;
ALTER TABLE `llm_perception_results` ADD COLUMN `citations` text DEFAULT NULL COMMENT 'JSON array of {url,title} the grounded response actually cited - NULL when not grounded or the provider returned none' AFTER `grounded`;

-- AI Perception: add Perplexity as a fourth provider (see
-- AiPerceptionController::__callPerplexity())
ALTER TABLE `llm_api_keys` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity') NOT NULL;
ALTER TABLE `llm_perception_results` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity') NOT NULL;
ALTER TABLE `llm_perception_competitor_results` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity') NOT NULL;

-- AI Perception: add Grok (xAI) as a fifth provider (see
-- AiPerceptionController::__callGrok())
ALTER TABLE `llm_api_keys` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity','grok') NOT NULL;
ALTER TABLE `llm_perception_results` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity','grok') NOT NULL;
ALTER TABLE `llm_perception_competitor_results` MODIFY COLUMN `provider` enum('openai','anthropic','google','perplexity','grok') NOT NULL;

-- AI Perception: mention prominence scoring (see
-- AiPerceptionController::__getMentionProminence())
ALTER TABLE `llm_perception_results` ADD COLUMN `prominence` varchar(10) DEFAULT NULL COMMENT 'early/mid/late - how far into the response a confirmed mention first appears; NULL when not mentioned or the check errored' AFTER `citations`;

-- AI Perception: weekly digest email (see
-- AiPerceptionController::sendWeeklyPerceptionDigests())
ALTER TABLE `reports_settings` ADD COLUMN `ai_perception_email_notification` tinyint(1) NOT NULL DEFAULT '1' AFTER `ai_visibility_last_digest_sent`;
ALTER TABLE `reports_settings` ADD COLUMN `ai_perception_last_digest_sent` date DEFAULT NULL AFTER `ai_perception_email_notification`;
INSERT IGNORE INTO `settings` (`set_label`,`set_name`,`set_val`,`set_category`,`set_type`,`display`) VALUES
('Enable AI Perception email notification','SP_AI_PERCEPTION_EMAIL_NOTIFICATION','1','report','bool',1);
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'report', 'AI Perception email notification', 'AI Perception email notification'),
('en', 'settings', 'SP_AI_PERCEPTION_EMAIL_NOTIFICATION', 'Enable AI Perception email notification'),
('en', 'aivisibility', 'ai_perception_email_body_intro', 'Here''s your AI Perception summary for the past week - how often real AI assistants mentioned your site when asked:'),
('en', 'aivisibility', 'ai_perception_email_body_outro', 'View the full tracking dashboard: [LOGIN_LINK]'),
('en', 'aivisibility', 'ai_perception_email_subject', 'Your AI Perception summary this week'),
('en', 'aivisibility', 'vs. last cycle', 'vs. last cycle'),
('en', 'aivisibility', 'Competitors', 'Competitors'),
('en', 'aivisibility', 'Sentiment', 'Sentiment'),
('en', 'aivisibility', 'positive', 'positive'),
('en', 'aivisibility', 'neutral', 'neutral'),
('en', 'aivisibility', 'negative', 'negative'),
('en', 'aivisibility', 'no change', 'no change'),
('en', 'aivisibility', 'first tracked week', 'first tracked week'),
('en', 'plugin', 'Drag & drop a .zip file here, or click to browse', 'Drag & drop a .zip file here, or click to browse'),
('en', 'theme', 'Drag & drop a .zip file here, or click to browse', 'Drag & drop a .zip file here, or click to browse');

-- SettingsController::showSystemSettings()'s switch never had a
-- 'siteauditor' case, so the Auditor Settings page always titled itself
-- "System Settings" regardless of category - see settings.ctrl.php.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'panel', 'Site Auditor Settings', 'Site Auditor Settings');

-- subtitle line in the shared branded email banner (email/
-- _email_banner.ctp.php) each periodic/digest email now opens with.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'reports', 'Your scheduled report', 'Your scheduled report'),
('en', 'aiinsights', 'AI Insights digest', 'AI Insights digest'),
('en', 'aivisibility', 'AI Visibility digest', 'AI Visibility digest'),
('en', 'aivisibility', 'AI Perception digest', 'AI Perception digest');

-- Chat (Slack/Discord/generic webhook) notifications for the 4 periodic
-- reports, alongside the existing email_notification/ai_*_email_
-- notification toggles - see ReportController::getUserReportSettings()
-- and sendWebhookNotification() (includes/sp-common.php).
ALTER TABLE `reports_settings` ADD COLUMN `webhook_url` varchar(500) DEFAULT NULL AFTER `ai_perception_last_digest_sent`;
ALTER TABLE `reports_settings` ADD COLUMN `webhook_platform` varchar(20) NOT NULL DEFAULT 'slack' AFTER `webhook_url`;
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'report', 'Webhook URL', 'Webhook URL'),
('en', 'report', 'Webhook URL note', 'Sends a short summary (and a link back here) to this URL every time an email notification above would be sent. Leave blank to disable.'),
('en', 'report', 'Platform', 'Platform'),
('en', 'report', 'Send Test Notification', 'Send Test Notification'),
('en', 'report', 'Test notification sent', 'Test notification sent'),
('en', 'report', 'Test notification failed', 'Test notification failed - check the URL and try again'),
('en', 'report', 'Enter a webhook URL first', 'Enter a webhook URL first');

-- Real-time alerts (rank swings + Site Auditor newly-critical issues) -
-- fire immediately via the same webhook above, instead of waiting for the
-- next scheduled digest. See ReportController::saveMatchedKeywordInfo()
-- and AuditorComponent::runReport().
ALTER TABLE `reports_settings` ADD COLUMN `rank_alert_notification` tinyint(1) NOT NULL DEFAULT 0 AFTER `webhook_platform`;
ALTER TABLE `reports_settings` ADD COLUMN `rank_alert_threshold` int(11) NOT NULL DEFAULT 5 AFTER `rank_alert_notification`;
ALTER TABLE `reports_settings` ADD COLUMN `site_issue_alert_notification` tinyint(1) NOT NULL DEFAULT 0 AFTER `rank_alert_threshold`;
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'report', 'Alert on rank changes', 'Alert on rank changes'),
('en', 'report', 'Alert on rank changes note', 'Sends an immediate webhook alert whenever a keyword moves by at least this many positions, or enters/drops out of the ranked results - in addition to, not instead of, the scheduled report above.'),
('en', 'report', 'Position threshold', 'Position threshold'),
('en', 'report', 'Alert on new critical issues', 'Alert on new critical issues'),
('en', 'report', 'Alert on new critical issues note', 'Sends an immediate webhook alert when Site Auditor finds a page newly broken, deindexed, not mobile-friendly, not secure, or blocked by robots.'),
('en', 'report', 'Keyword rank alert', 'Keyword rank alert'),
('en', 'report', 'Site Auditor critical issue alert', 'Site Auditor critical issue alert');

-- White-label client report links - a no-login, read-only URL an agency
-- hands to its own end client, showing that one website's report with the
-- agency's branding instead of SEO Panel's. At most one active (non-revoked)
-- token per website - see WebsiteController::generateReportShareLink().
-- Resolved by client-report.php; rendering is the same live report engine
-- (ReportController::showOverallReportSummary()) used everywhere else.
CREATE TABLE IF NOT EXISTS `report_share_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `last_viewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'website', 'Client Report Link', 'Client Report Link'),
('en', 'website', 'Client Report Link note', 'A no-login link you can hand to your client - it shows this website''s report only, branded with your own logo/name instead of SEO Panel''s.'),
('en', 'website', 'Generate link', 'Generate link'),
('en', 'website', 'Revoke', 'Revoke'),
('en', 'website', 'Copy', 'Copy'),
('en', 'website', 'Link copied', 'Link copied'),
('en', 'website', 'Revoke this link? Anyone using it will lose access immediately.', 'Revoke this link? Anyone using it will lose access immediately.'),
('en', 'label', 'This link is no longer available', 'This link is no longer available');

-- Core Web Vitals (LCP/CLS/INP) per Site Auditor page - a separate,
-- slower cadence lane than the main crawl (see SiteAuditorController::
-- executeCwvCron()/getProjectUrlDueForCwvCheck() and AuditorComponent::
-- runCwvCheck()), reusing the existing PageSpeed Insights API/Google key.
ALTER TABLE `auditorreports` ADD COLUMN `cwv_checked_at` datetime DEFAULT NULL AFTER `fetch_fail_count`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_desktop_lcp_ms` int(11) DEFAULT NULL AFTER `cwv_checked_at`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_desktop_cls` decimal(6,3) DEFAULT NULL AFTER `cwv_desktop_lcp_ms`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_desktop_inp_ms` int(11) DEFAULT NULL AFTER `cwv_desktop_cls`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_mobile_lcp_ms` int(11) DEFAULT NULL AFTER `cwv_desktop_inp_ms`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_mobile_cls` decimal(6,3) DEFAULT NULL AFTER `cwv_mobile_lcp_ms`;
ALTER TABLE `auditorreports` ADD COLUMN `cwv_mobile_inp_ms` int(11) DEFAULT NULL AFTER `cwv_mobile_cls`;
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'siteauditor', 'Core Web Vitals', 'Core Web Vitals'),
('en', 'siteauditor', 'Not yet checked', 'Not yet checked'),
('en', 'siteauditor', 'Not enough data', 'Not enough data'),
('en', 'siteauditor', 'Last checked', 'Last checked'),
('en', 'siteauditor', 'Good', 'Good'),
('en', 'siteauditor', 'Needs Improvement', 'Needs Improvement'),
('en', 'siteauditor', 'Poor', 'Poor');
