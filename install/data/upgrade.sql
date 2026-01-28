--
-- Seo Panel 5.2.0 changes
--
update `settings` set set_val='5.2.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

INSERT IGNORE INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`) VALUES
('Enable for Review Checker', 'SP_ENABLE_DFS_REVIEW', '0', 'dataforseo', 'bool', 1);

INSERT IGNORE INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES
('en', 'settings', 'SP_ENABLE_DFS_REVIEW', 'Enable for Review Checker');

