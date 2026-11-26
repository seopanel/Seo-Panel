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

-- fix: client-report.php hardcoded "Powered by" in HTML instead of going
-- through the texts table, despite the rest of the white-label client
-- report link feature already being translatable.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'website', 'Powered by', 'Powered by');

-- fix: Directory Submission's directorycheckercron.php only ever queried
-- checked=0 (never-yet-checked) directories, and checkDirectoryStatus()
-- set checked=1 unconditionally with no timestamp recorded anywhere - so
-- a directory's working-status/DA/PA/captcha state got checked exactly
-- ONCE, ever, then went stale forever with no way to tell how stale.
-- last_checked lets the cron re-check directories periodically instead.
ALTER TABLE `directories` ADD COLUMN `last_checked` datetime DEFAULT NULL AFTER `checked`;

-- feature: Social Media's new manual "Generate Reports Now" - previously
-- the only SEO tool with no on-demand trigger at all.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'socialmedia', 'Saved social media results of', 'Saved social media results of'),
('en', 'socialmedia', 'No social media links left to generate report for', 'No social media links left to generate report for'),
('en', 'socialmedia', 'Failed crawling social media results of', 'Failed crawling social media results of');

-- feature: Competitor rank tracking on the Keyword Position Summary page -
-- compares each keyword's rank against named competitor domains, computed
-- live from the already-saved searchresults.serp_results JSON (no new
-- crawling/API calls). See ReportController::__getCompetitorRanksBatch().
CREATE TABLE IF NOT EXISTS `rank_competitors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'keyword', 'Competitors', 'Competitors'),
('en', 'keyword', 'Tracked Competitors', 'Tracked Competitors'),
('en', 'keyword', 'Add Competitor', 'Add Competitor'),
('en', 'keyword', 'Competitor name', 'Competitor name'),
('en', 'keyword', 'Domain', 'Domain');

-- feature: Competitor rank-change alerts - extends the existing real-time
-- rank-alert webhook (own site's rank) to also fire when a tracked
-- competitor's rank swings, reusing the same threshold/webhook settings.
ALTER TABLE `reports_settings` ADD COLUMN `competitor_rank_alert_notification` tinyint(1) NOT NULL DEFAULT 0 AFTER `site_issue_alert_notification`;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'report', 'Alert on competitor rank changes', 'Alert on competitor rank changes'),
('en', 'report', 'Alert on competitor rank changes note', 'Sends an immediate webhook alert whenever a tracked competitor''s rank moves by at least the position threshold above, or enters/drops out of the ranked results, for one of your keywords.'),
('en', 'report', 'Competitor rank alert', 'Competitor rank alert');

-- feature: Internal Link Graph / Orphan Page report (Site Auditor) - pure
-- aggregation on auditorreports/auditorpagelinks, no schema change needed.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'siteauditor', 'Internal Link Graph', 'Internal Link Graph'),
('en', 'siteauditor', 'Orphan Pages', 'Orphan Pages'),
('en', 'siteauditor', 'Inbound Internal Links', 'Inbound Internal Links'),
('en', 'siteauditor', 'Outbound Internal Links', 'Outbound Internal Links'),
('en', 'siteauditor', 'Orphan', 'Orphan'),
('en', 'siteauditor', 'Store links in page is required for this report', 'This report needs "Store links in page" enabled on the project - edit the project and turn it on, then re-run the crawl.');

-- feature: Content/on-page optimization score per keyword - links a
-- keyword to a target page and scores title/description/H1/URL/word
-- count against it. h1_text is a new capture (reuses the existing H1
-- regex match crawlMetaData() already runs for heading_structure_ok,
-- just also keeping the text this time).
ALTER TABLE `keywords` ADD COLUMN `target_url` varchar(500) DEFAULT NULL AFTER `searchengines`;
ALTER TABLE `auditorreports` ADD COLUMN `h1_text` varchar(255) DEFAULT NULL AFTER `page_title`;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'keyword', 'Target URL', 'Target URL'),
('en', 'keyword', 'Target URL note', 'Optional - the page on your site that should rank for this keyword. Once that page has been crawled by Site Auditor, its On-Page Score appears on the Keyword Position Summary page.'),
('en', 'report', 'On-Page Score', 'On-Page Score'),
('en', 'report', 'Title contains keyword', 'Title contains keyword'),
('en', 'report', 'Description contains keyword', 'Description contains keyword'),
('en', 'report', 'H1 contains keyword', 'H1 contains keyword'),
('en', 'report', 'URL contains keyword', 'URL contains keyword'),
('en', 'report', 'Content length is adequate', 'Content length is adequate'),
('en', 'report', 'No target URL set', 'No target URL set'),
('en', 'report', 'Target page not crawled yet', 'Target page not crawled yet');

-- feature: Keyword research / discovery tool - seed keyword -> related
-- suggestions (search volume/competition/cpc) via DataForSEO's
-- "Keywords For Keywords" live endpoint. No schema change - a live,
-- not-persisted lookup, same shape as Quick Rank Checker.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'keyword', 'Keyword Research', 'Keyword Research'),
('en', 'keyword', 'Seed Keyword', 'Seed Keyword'),
('en', 'keyword', 'Competition', 'Competition'),
('en', 'keyword', 'Add as Keyword', 'Add as Keyword'),
('en', 'keyword', 'DataForSEO must be configured to use Keyword Research', 'DataForSEO must be configured to use Keyword Research'),
('en', 'keyword', 'Enter a seed keyword and click Proceed to get keyword suggestions', 'Enter a seed keyword and click Proceed to get keyword suggestions');

-- feature: Backlink detail/monitoring - per-link rows (referring domain,
-- source page, anchor text, dofollow, domain rank, first/last seen,
-- live/lost status) via DataForSEO's backlinks/backlinks/live endpoint.
-- DFS's own crawler already tracks lost/live status - no diffing logic
-- needed, just an upsert-by-link-hash on each (slow-cadence, staleness-
-- gated) crawl. Same url_hash dedup pattern as ai_bot_hits/ai_referrals.
CREATE TABLE IF NOT EXISTS `backlinkdetails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `website_id` int(11) unsigned NOT NULL,
  `url_from` varchar(767) NOT NULL,
  `url_from_hash` binary(16) NOT NULL,
  `domain_from` varchar(255) DEFAULT NULL,
  `domain_from_rank` int(11) DEFAULT NULL,
  `url_to` varchar(767) DEFAULT NULL,
  `anchor` varchar(500) DEFAULT NULL,
  `dofollow` tinyint(1) DEFAULT NULL,
  `first_seen` date DEFAULT NULL,
  `last_seen` date DEFAULT NULL,
  `is_lost` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_link` (`website_id`,`url_from_hash`),
  KEY `website_id_is_lost` (`website_id`,`is_lost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `websites` ADD COLUMN `backlink_detail_checked_at` datetime DEFAULT NULL;

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'backlink', 'Backlink Details', 'Backlink Details'),
('en', 'backlink', 'Referring Domain', 'Referring Domain'),
('en', 'backlink', 'Source Page', 'Source Page'),
('en', 'backlink', 'Anchor Text', 'Anchor Text'),
('en', 'backlink', 'Dofollow', 'Dofollow'),
('en', 'backlink', 'Nofollow', 'Nofollow'),
('en', 'backlink', 'Domain Rank', 'Domain Rank'),
('en', 'backlink', 'First Seen', 'First Seen'),
('en', 'backlink', 'Last Seen', 'Last Seen'),
('en', 'backlink', 'Live', 'Live'),
('en', 'backlink', 'Lost', 'Lost'),
('en', 'backlink', 'DataForSEO must be configured to use Backlink Details', 'DataForSEO must be configured to use Backlink Details'),
('en', 'backlink', 'No backlink detail data yet - check back after the next scheduled crawl', 'No backlink detail data yet - check back after the next scheduled crawl');

-- 7.2.0 feature: AI Insights (Recommendations) title/description rendered
-- via RecommendationsController::renderRecommendationText() at display
-- time, instead of being baked into sp_recommendations as plain English at
-- generation time. [bracket] placeholders are filled from each row's own
-- meta JSON; {{plural:}}/{{cond:}}/{{choice:}} tokens resolve a nested
-- fragment label (see that function's own comment for the exact syntax).
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'recommendations', 'webmaster_opportunity_title', 'Keyword "[keyword]" is stuck at avg position [average_position], just off page 1'),
('en', 'recommendations', 'webmaster_opportunity_desc', 'Over the last 30 days ([date_from] to [date_to]) this keyword accumulated [impressions] impressions at an average position of [average_position] (page 2/3). It received [clicks] clicks (avg CTR: [ctr]%). Focused on-page optimisation, internal linking, and quality backlinks could push it onto page 1 and significantly increase traffic.'),
('en', 'recommendations', 'ai_overview_citation_title', 'AI Overview appears for "[keyword]" but you''re not cited'),
('en', 'recommendations', 'ai_overview_citation_desc', 'You rank #[rank] on [searchengine] for "[keyword]" and Google''s AI Overview is showing for this search (citing {{plural:aio_reference_count:ai_overview_citation_sources}}), but your page isn''t one of them. Strengthening this page''s authority and directly answering the query may help it get cited.'),
('en', 'recommendations', 'ai_overview_citation_sources_one', '[aio_reference_count] other source'),
('en', 'recommendations', 'ai_overview_citation_sources_other', '[aio_reference_count] other sources'),
('en', 'recommendations', 'ai_bot_blocked_title', '{{plural:count:ai_bot_blocked_pages}} block AI crawlers via meta tags'),
('en', 'recommendations', 'ai_bot_blocked_desc', 'Site Auditor found {{plural:count:ai_bot_blocked_pages}} with meta tags that block AI bots (GPTBot, ClaudeBot, Google-Extended, PerplexityBot, and similar) from indexing them. If you want these pages to be eligible for citation in AI-generated answers, remove those directives.'),
('en', 'recommendations', 'ai_bot_blocked_pages_one', '1 page'),
('en', 'recommendations', 'ai_bot_blocked_pages_other', '[count] pages'),
('en', 'recommendations', 'ai_bot_silent_with_date_title', 'No AI crawler activity in the last 30 days'),
('en', 'recommendations', 'ai_bot_silent_with_date_desc', 'The last AI crawler visit (GPTBot, ClaudeBot, PerplexityBot, etc.) recorded for this site was on [bot_last_seen_at]. This may mean AI platforms are indexing your content less often.'),
('en', 'recommendations', 'ai_bot_silent_never_title', 'No AI crawler activity in the last 30 days'),
('en', 'recommendations', 'ai_bot_silent_never_desc', 'No AI crawler visit has been recorded for this site yet since the bot collector script was installed.'),
('en', 'recommendations', 'rank_drop_title', 'Keyword "[keyword]" dropped from position [baseline_rank] to [latest_rank]'),
('en', 'recommendations', 'rank_drop_desc', 'On [searchengine], this keyword was at position [baseline_rank] on [baseline_date] and has since dropped to position [latest_rank] (as of [latest_date]). Check for recent content, technical, or backlink changes that might explain the drop.'),
('en', 'recommendations', 'broken_links_title', '{{plural:count:broken_links_n}} found'),
('en', 'recommendations', 'broken_links_desc', 'Site Auditor found {{plural:count:broken_links_n}} on this website''s most recently crawled pages.'),
('en', 'recommendations', 'broken_links_n_one', '1 broken link'),
('en', 'recommendations', 'broken_links_n_other', '[count] broken links'),
('en', 'recommendations', 'https_secure_title', '{{plural:count:https_secure_n}} {{plural:count:https_secure_is}} not served over HTTPS'),
('en', 'recommendations', 'https_secure_desc', 'Site Auditor found {{plural:count:https_secure_n}} that {{plural:count:https_secure_is}} not served over HTTPS.'),
('en', 'recommendations', 'https_secure_n_one', '1 page'),
('en', 'recommendations', 'https_secure_n_other', '[count] pages'),
('en', 'recommendations', 'https_secure_is_one', 'is'),
('en', 'recommendations', 'https_secure_is_other', 'are'),
('en', 'recommendations', 'og_tags_title', '{{plural:count:og_tags_n}} {{plural:count:og_tags_is}} missing Open Graph tags'),
('en', 'recommendations', 'og_tags_desc', 'Site Auditor found {{plural:count:og_tags_n}} missing Open Graph (og:) meta tags, which affects how {{plural:count:og_tags_pron}} when shared on social media.'),
('en', 'recommendations', 'og_tags_n_one', '1 page'),
('en', 'recommendations', 'og_tags_n_other', '[count] pages'),
('en', 'recommendations', 'og_tags_is_one', 'is'),
('en', 'recommendations', 'og_tags_is_other', 'are'),
('en', 'recommendations', 'og_tags_pron_one', 'it appears'),
('en', 'recommendations', 'og_tags_pron_other', 'they appear'),
('en', 'recommendations', 'structured_data_title', '{{plural:count:structured_data_n}} {{plural:count:structured_data_is}} missing structured data'),
('en', 'recommendations', 'structured_data_desc', 'Site Auditor found {{plural:count:structured_data_n}} with no structured data (JSON-LD), which is what AI models like ChatGPT and Google''s AI Overview read to understand what {{plural:count:structured_data_pron}} actually about.'),
('en', 'recommendations', 'structured_data_n_one', '1 page'),
('en', 'recommendations', 'structured_data_n_other', '[count] pages'),
('en', 'recommendations', 'structured_data_is_one', 'is'),
('en', 'recommendations', 'structured_data_is_other', 'are'),
('en', 'recommendations', 'structured_data_pron_one', 'the page is'),
('en', 'recommendations', 'structured_data_pron_other', 'those pages are'),
('en', 'recommendations', 'ai_perception_mention_lost_title', '[provider] stopped mentioning you for "[prompt_text]"'),
('en', 'recommendations', 'ai_perception_mention_lost_desc', 'On [prev_date] your site was mentioned when [provider] was asked this question. As of [latest_date], it''s no longer mentioned.'),
('en', 'recommendations', 'ai_perception_sentiment_negative_title', '[provider]''s sentiment turned negative for "[prompt_text]"'),
('en', 'recommendations', 'ai_perception_sentiment_negative_desc', '[provider]''s answer to this question turned negative between [prev_date] and [latest_date]. Open the AI Perception check to review the response.'),
('en', 'recommendations', 'ai_perception_competitor_title', '[competitor_name] is being recommended by [provider] instead of you'),
('en', 'recommendations', 'ai_perception_competitor_desc', 'For the prompt "[prompt_text]", [provider] mentioned [competitor_name] but not you, as of [checked_date].'),
('en', 'recommendations', 'backlink_drop_title', 'Backlinks dropped [drop_pct]% in the last 30 days'),
('en', 'recommendations', 'backlink_drop_desc', 'This website had [baseline_count] referring pages to its root domain on [baseline_date]; now at [latest_count] as of [latest_date]. Review recently lost or removed backlinks.'),
('en', 'recommendations', 'review_drop_title', '[link_name] rating dropped from [baseline_rating] to [latest_rating]'),
('en', 'recommendations', 'review_drop_desc', 'Your average rating on [link_name] fell over the last 30 days ([baseline_date] to [latest_date]). New negative reviews may need a response.'),
('en', 'recommendations', 'ga_sessions_drop_title', 'Sessions down [drop_pct]% week-over-week'),
('en', 'recommendations', 'ga_sessions_drop_desc', 'This website had [recent_sessions] sessions in the last 7 days vs [prior_sessions] the week before{{cond:prior_goals:ga_sessions_drop_goals}}.'),
('en', 'recommendations', 'ga_sessions_drop_goals', ', with goal completions down from [prior_goals] to [recent_goals]'),
('en', 'recommendations', 'search_console_drop_title', 'Search Console impressions down [drop_pct]% on [source_label]'),
('en', 'recommendations', 'search_console_drop_desc', 'Impressions on [source_label] fell from [prior_impressions] to [recent_impressions] over the last 7 days compared to the week before. This can signal a ranking drop, a de-indexing issue, or reduced search interest.'),
('en', 'recommendations', 'pagespeed_regression_title', '{{choice:is_mobile:pagespeed_metric_mobile:pagespeed_metric_desktop}} PageSpeed score dropped from [prev_score] to [latest_score]'),
('en', 'recommendations', 'pagespeed_regression_desc', 'A recent deploy or added script may be slowing the site down (checked [prev_date] vs [latest_date]). Check Core Web Vitals in the PageSpeed Insights tool for details.'),
('en', 'recommendations', 'pagespeed_metric_mobile', 'Mobile'),
('en', 'recommendations', 'pagespeed_metric_desktop', 'Desktop'),
('en', 'recommendations', 'social_follower_drop_title', '[platform] followers dropped by [lost]'),
('en', 'recommendations', 'social_follower_drop_desc', '[platform] ([link_name]) had [baseline_followers] followers on [baseline_date], now at [latest_followers] as of [latest_date].'),
('en', 'recommendations', 'cron_reliability_title', '[section_label] has failed [failures] of the last 5 cron runs'),
('en', 'recommendations', 'cron_reliability_desc', 'This tool''s scheduled runs are failing repeatedly{{cond:last_error:cron_reliability_last_error}}. Its data may be stale until this is fixed.'),
('en', 'recommendations', 'cron_reliability_last_error', '. Last error: [last_error]'),
('en', 'recommendations', 'job_queue_failure_title', '{{plural:count:job_queue_failure_n}} permanently failed'),
('en', 'recommendations', 'job_queue_failure_n_one', 'A [section_label] job chunk has'),
('en', 'recommendations', 'job_queue_failure_n_other', '[count] [section_label] job chunks have'),
('en', 'recommendations', 'job_queue_failure_desc', 'These chunks have exhausted their retry attempts and will not complete{{cond:last_error:job_queue_failure_last_error}}. This part of the data will remain incomplete until resolved.'),
('en', 'recommendations', 'job_queue_failure_last_error', '. Last error: [last_error]'),
('en', 'recommendations', 'directory_decay_title', '[inactive] of your [total] directory submissions are no longer active'),
('en', 'recommendations', 'directory_decay_desc', 'These backlinks may have been lost. Consider re-submitting or replacing them with active directories.'),
('en', 'recommendations', 'keyword_opportunity_proven_title', 'Untapped opportunity: "[candidate_name]" ([volume]/mo searches, easy to rank)'),
('en', 'recommendations', 'keyword_opportunity_proven_desc', 'You already rank #[proven_rank] for "[proven_name]", proving you can compete in this space. "[candidate_name]" gets an estimated [volume] searches/month with low difficulty ([difficulty]/100) but {{choice:rank:keyword_opportunity_rank_known:keyword_opportunity_rank_unknown}}. Worth targeting.'),
('en', 'recommendations', 'keyword_opportunity_plain_title', 'Untapped keyword opportunity: "[candidate_name]"'),
('en', 'recommendations', 'keyword_opportunity_plain_desc', '"[candidate_name]" gets an estimated [volume] searches/month with low difficulty ([difficulty]/100), but {{choice:rank:keyword_opportunity_rank_known:keyword_opportunity_rank_unknown}}. Worth targeting with dedicated content.'),
('en', 'recommendations', 'keyword_opportunity_rank_known', 'is only ranking at position [rank]'),
('en', 'recommendations', 'keyword_opportunity_rank_unknown', 'isn''t being tracked for rank yet'),
('en', 'recommendations', 'cross_tool_title', 'Rank drops and site auditor issues may be related'),
('en', 'recommendations', 'cross_tool_desc', '{{plural:rank_drop_count:cross_tool_rank_drop}} recently, and Site Auditor currently flags {{plural:auditor_issue_count:cross_tool_issue}} on this website. Technical issues can suppress rankings even when content hasn''t changed - worth checking whether these are connected.'),
('en', 'recommendations', 'cross_tool_rank_drop_one', '1 keyword dropped'),
('en', 'recommendations', 'cross_tool_rank_drop_other', '[rank_drop_count] keywords dropped'),
('en', 'recommendations', 'cross_tool_issue_one', '1 issue'),
('en', 'recommendations', 'cross_tool_issue_other', '[auditor_issue_count] issues');

-- bug fix: Facebook/Twitter/Instagram/Reddit's social-media follower
-- scrapers are confirmed dead (live-tested with this app's own Spider -
-- login walls, a decommissioned API, and fully client-side-rendered
-- pages with no embeddable data) and not fixable without each
-- platform's own paid/OAuth-gated API - this note tells a user BEFORE
-- they save a link that automated checks for it won't work, instead of
-- only finding out after every check silently returns nothing.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'socialmedia', 'Automated checks currently unavailable for this platform', 'Automated checks for this platform currently don''t work due to login/API restrictions on its side. You can still add this link, but stats won''t be collected until this is fixed.');

-- bug fix: doQuickChecker() had no rate limiting at all - a logged-in
-- user could Quick-Check unlimited arbitrary URLs with no stored-link
-- ownership gate, unlike the saved-link workflow's own quota check.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'socialmedia', 'Too many checks - please wait a moment and try again', 'Too many checks - please wait a moment and try again.');

-- bug fix: the Feature Tour's "Add a review link" hint described the
-- OLD, removed stristr() substring check ("must contain the platform's
-- name") - the real check (ReviewManagerController::__isUrlForPlatform())
-- requires the URL's actual host to match a domain allowlist, so a user
-- following the hint literally got rejected with a contradicting error.
-- Only the 'en' row is corrected here - the other 10 languages' existing
-- translations of this same stale claim are left for a future i18n pass,
-- same as any other English-copy fix that outpaces its translations.
UPDATE `texts` SET `content` = 'The URL must be a real page on the platform''s own website - not just any URL that happens to mention its name.' WHERE `lang_code` = 'en' AND `category` = 'featuretour' AND `label` = 'tour_review_hint';
UPDATE `texts` SET `content` = 'The URL must be a real page on %s - not just any URL that mentions its name.' WHERE `lang_code` = 'en' AND `category` = 'featuretour' AND `label` = 'tour_review_hint_dynamic';

-- bug fix: "Generate Reports Now" gave zero upfront indication of how
-- long a run would take - each link costs a real HTTP fetch plus a
-- sleep(), so a website with many pending links could take minutes with
-- no warning at all.
INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'socialmedia', '[count] links will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]', '[count] links will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]'),
('en', 'socialmedia', '[count] links across [website_count] websites will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]', '[count] links across [website_count] websites will be checked - this makes a real HTTP request per link, so it will take roughly [estimate]');
