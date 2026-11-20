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
