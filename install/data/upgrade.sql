--
-- Seo Panel 4.12.0 changes
--

update `settings` set set_val='4.12.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE searchengines SET url = REPLACE(url, 'http://', 'https://') WHERE url LIKE 'http://%';