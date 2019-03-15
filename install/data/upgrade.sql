--
-- Seo Panel 3.18.0 changes
--

update `settings` set set_val='3.18.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

ALTER TABLE websites ENGINE = InnoDB;

ALTER TABLE users ENGINE = InnoDB;


CREATE TABLE `social_media_links` (
  `id` int(11) NOT NULL,
  `website_id` int(11) NOT NULL,
  `name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'facebook',
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `social_media_links` ADD PRIMARY KEY (`id`), ADD KEY `social_media_links_web_rel` (`website_id`);
ALTER TABLE `social_media_links` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `social_media_links`
  ADD CONSTRAINT `social_media_links_web_rel` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;


INSERT INTO `texts` (`category`, `label`, `content`) VALUES 
('settings', 'Send Email', 'Send Email'),
('panel', 'Test Email Settings', 'Test Email Settings');


