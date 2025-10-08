--
-- Seo Panel 4.12.0 changes
--

update `settings` set set_val='4.12.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE searchengines SET url = REPLACE(url, 'http://', 'https://') WHERE url LIKE 'http://%';

UPDATE searchengines SET url = REPLACE(url, '&num=[--num--]', '') WHERE  url LIKE '%google%';

UPDATE searchengines SET url = REPLACE(url, '&as_qdr=all&gws_rd=cr&nfpr=1', '') WHERE  url LIKE '%google%';

ALTER TABLE `searchengines` ADD `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP; 