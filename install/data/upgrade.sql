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

CREATE TABLE `analytics_properties` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_name` varchar(120) NOT NULL,
  `account_id` varchar(120) NOT NULL,
  `property_name` varchar(120) NOT NULL,
  `property_id` varchar(120) NOT NULL,
  `datetime_created` datetime NOT NULL DEFAULT current_timestamp(),
  `datetime_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `analytics_properties` ADD PRIMARY KEY (`id`), ADD KEY `analytics_properties_user_id_delete` (`user_id`);
ALTER TABLE `analytics_properties` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `analytics_properties` ADD CONSTRAINT `analytics_properties_user_id_delete` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `backlinkresults` CHANGE `alexa` `alexa` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `rankresults` CHANGE `alexa_rank` `alexa_rank` INT(11) NOT NULL DEFAULT '0'; 

UPDATE `crawl_engines` SET `regex1` = '/\"follower_count\":(\\d+)/is' WHERE engine_name='pinterest' and engine_category='social_media';
UPDATE `crawl_engines` SET `regex2` = '/media_result_group.*?\"Rated (\\d+\\.\\d+) out/is' WHERE engine_name='google' and engine_category='review';

UPDATE `searchengines` SET `regex` = '<div.*?class=\"?g.*?>.*?href=\"(.*?)\".*?>.*?<h3.*?>(.*?)<\\/h3>',
`from_pattern` = 'id=\"search\"', `to_pattern` = 'id=\"bottomads\"' where  url LIKE '%google%';
