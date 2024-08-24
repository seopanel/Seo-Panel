--
-- Seo Panel 4.11.0 changes
--

update `settings` set set_val='4.11.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE `currency` SET `symbol` = '€' WHERE `currency`.`id` = 23;
UPDATE `currency` SET `symbol` = 'د.إ' WHERE `currency`.`id` = 1;
UPDATE `currency` SET `symbol` = 'ƒ' WHERE `currency`.`id` = 2;
UPDATE `currency` SET `symbol` = '₩' WHERE `currency`.`id` = 36;
UPDATE `currency` SET `symbol` = '₡' WHERE `currency`.`id` = 18;
UPDATE `currency` SET `symbol` = 'Kč' WHERE `currency`.`id` = 19;
UPDATE `currency` SET `symbol` = 'E£' WHERE `currency`.`id` = 22;
UPDATE `currency` SET `symbol` = '₪' WHERE `currency`.`id` = 31;
UPDATE `currency` SET `symbol` = '¥' WHERE `currency`.`id` = 34;
UPDATE `currency` SET `symbol` = '₦' WHERE `currency`.`id` = 45;
UPDATE `currency` SET `symbol` = '﷼' WHERE `currency`.`id` = 48; 
UPDATE `currency` SET `symbol` = '₱' WHERE `currency`.`id` = 50;
UPDATE `currency` SET `symbol` = 'Zł' WHERE `currency`.`id` = 51;
UPDATE `currency` SET `symbol` = '₽' WHERE `currency`.`id` = 54;
UPDATE `currency` SET `symbol` = 'ł' WHERE `currency`.`id` = 58;
UPDATE `currency` SET `symbol` = '₫' WHERE `currency`.`id` = 65;
UPDATE `currency` SET `symbol` = '¥' WHERE `currency`.`id` = 16;

INSERT INTO `crawl_engines` (`id`, `engine_name`, `engine_category`, `regex1`, `regex2`, `regex3`, `regex4`, `url`, `url_part`, `status`) VALUES
(NULL, 'yelp', 'review', '/reviewCount.*?:(\\d+),/is', '/aggregateRating.*?:([0-9]*\\.?[0-9]+),/is', NULL, NULL, '', NULL, 1),
(NULL, 'trustpilot', 'review', '/\"reviewCount\":\"(\\d+)\"/is', '/\"ratingValue\":\"(\\d+\\.\\d+)\"/is', NULL, NULL, '', NULL, 1),
(NULL, 'tripadvisor', 'review', '/\"reviewCount\":(\\d+)/is', '/\"ratingValue\":\"(\\d+\\.\\d+)\"/is', NULL, NULL, '', NULL, 1),
(NULL, 'reddit', 'social_media', '/subscribers=\"(\\d+)\"/is', NULL, NULL, NULL, '', NULL, 1);

UPDATE `crawl_engines` SET `regex1` = '/\"follower_count\":(\\d+)/is' WHERE engine_name='pinterest' and engine_category='social_media';

UPDATE `searchengines` SET `regex` = '<div.*?class=\"?g.*?>.*?href=\"(.*?)\".*?>.*?<h3.*?>(.*?)<\\/h3>',
`from_pattern` = 'id=\"search\"', `to_pattern` = 'id=\"bottomads\"' where  url LIKE '%google%';
