--
-- Seo Panel 5.0.0 changes
--

update `settings` set set_val='5.0.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE searchengines SET url = REPLACE(url, 'http://', 'https://') WHERE url LIKE 'http://%';

UPDATE searchengines SET url = REPLACE(url, '&num=[--num--]', '') WHERE  url LIKE '%google%';

UPDATE searchengines SET url = REPLACE(url, '&as_qdr=all&gws_rd=cr&nfpr=1', '') WHERE  url LIKE '%google%';

ALTER TABLE `searchengines` ADD `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `texts` CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `rankresults` ADD `spam_score` FLOAT NOT NULL DEFAULT '0' AFTER `moz_rank`;

ALTER TABLE `rankresults` CHANGE `moz_rank` `moz_rank` FLOAT NOT NULL DEFAULT '0';
ALTER TABLE `rankresults` CHANGE `domain_authority` `domain_authority` FLOAT NOT NULL DEFAULT '0',
CHANGE `page_authority` `page_authority` FLOAT NOT NULL DEFAULT '0';

ALTER TABLE `directories` ADD `spam_score` FLOAT NOT NULL DEFAULT '0' AFTER `pagerank`;

ALTER TABLE `auditorreports` ADD `spam_score` FLOAT NOT NULL DEFAULT '0' AFTER `page_authority`;

ALTER TABLE `backlinkresults` ADD `external_pages_to_page` INT NOT NULL DEFAULT '0' AFTER `alexa`;

ALTER TABLE `backlinkresults` ADD `external_pages_to_root_domain` INT NOT NULL DEFAULT '0' AFTER `external_pages_to_page`;

ALTER TABLE `backlinkresults` CHANGE `google` `google` INT NOT NULL DEFAULT '0', CHANGE `msn` `msn` INT NOT NULL DEFAULT '0'; 

update `settings` set display=0 WHERE `set_name` LIKE 'SP_MOZ_API_ACCESS_ID';