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
