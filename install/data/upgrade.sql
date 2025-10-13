--
-- Seo Panel 4.12.0 changes
--

update `settings` set set_val='4.12.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE searchengines SET url = REPLACE(url, 'http://', 'https://') WHERE url LIKE 'http://%';

UPDATE searchengines SET url = REPLACE(url, '&num=[--num--]', '') WHERE  url LIKE '%google%';

UPDATE searchengines SET url = REPLACE(url, '&as_qdr=all&gws_rd=cr&nfpr=1', '') WHERE  url LIKE '%google%';

ALTER TABLE `searchengines` ADD `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP; 


INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'keyword', 'Ranking Trends', 'Ranking Trends', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'keyword', 'Keyword Ranking Trends', 'Keyword Ranking Trends', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'keyword', 'Keywords Tracked', 'Keywords Tracked', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'keyword', 'Top Keywords', 'Top Keywords', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'label', 'Recent Activity', 'Recent Activity', CURRENT_TIMESTAMP);