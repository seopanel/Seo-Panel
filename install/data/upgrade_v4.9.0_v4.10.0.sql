--
-- Seo Panel 4.10.0 changes
--

update `featured_directories` set status=0 WHERE `directory_name` LIKE 'directorymaximizer.com';
UPDATE `seoplugins` SET `version` = '2.1.0' WHERE name='QuickWebProxy';
INSERT INTO `qwp_settings` (`set_label`, `set_name`, `set_val`, `set_type`) VALUES ('Blocked Urls In Proxy', 'QWP_PROXY_BLOCK_URLS', '', 'text');
