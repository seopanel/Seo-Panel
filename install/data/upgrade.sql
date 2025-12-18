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

-- Add Social Media language text for dashboard navigation
INSERT INTO texts (lang_code, category, label, content) VALUES
('en', 'label', 'Social Media', 'Social Media'),
('ar', 'label', 'Social Media', 'وسائل التواصل الاجتماعي'),
('bg', 'label', 'Social Media', 'Социални медии'),
('bs', 'label', 'Social Media', 'Društveni mediji'),
('ca', 'label', 'Social Media', 'Xarxes socials'),
('cn', 'label', 'Social Media', '社交媒体'),
('cs', 'label', 'Social Media', 'Sociální média'),
('da', 'label', 'Social Media', 'Sociale medier'),
('de', 'label', 'Social Media', 'Soziale Medien'),
('el', 'label', 'Social Media', 'Κοινωνικά Μέσα'),
('es', 'label', 'Social Media', 'Redes Sociales'),
('es-ar', 'label', 'Social Media', 'Redes Sociales'),
('fa', 'label', 'Social Media', 'رسانه های اجتماعی'),
('fi', 'label', 'Social Media', 'Sosiaalinen media'),
('fr', 'label', 'Social Media', 'Médias Sociaux'),
('he', 'label', 'Social Media', 'מדיה חברתית'),
('hi', 'label', 'Social Media', 'सोशल मीडिया'),
('hr', 'label', 'Social Media', 'Društveni mediji'),
('hu', 'label', 'Social Media', 'Közösségi média'),
('hy', 'label', 'Social Media', 'Սոցիալական Մեդիա'),
('id', 'label', 'Social Media', 'Media Sosial'),
('it', 'label', 'Social Media', 'Social Media'),
('ja', 'label', 'Social Media', 'ソーシャルメディア'),
('ko', 'label', 'Social Media', '소셜 미디어'),
('lt', 'label', 'Social Media', 'Socialinė žiniasklaida'),
('mk', 'label', 'Social Media', 'Социјални медиуми'),
('nl', 'label', 'Social Media', 'Sociale Media'),
('no', 'label', 'Social Media', 'Sosiale medier'),
('pl', 'label', 'Social Media', 'Media społecznościowe'),
('pt', 'label', 'Social Media', 'Redes Sociais'),
('pt-br', 'label', 'Social Media', 'Redes Sociais'),
('ro', 'label', 'Social Media', 'Social Media'),
('ru', 'label', 'Social Media', 'Социальные сети'),
('sk', 'label', 'Social Media', 'Sociálne médiá'),
('sl', 'label', 'Social Media', 'Družbeni mediji'),
('sq', 'label', 'Social Media', 'Media Sociale'),
('sr', 'label', 'Social Media', 'Друштвени медији'),
('sv', 'label', 'Social Media', 'Sociala medier'),
('sw', 'label', 'Social Media', 'Mitandao ya Kijamii'),
('th', 'label', 'Social Media', 'สื่อสังคม'),
('tl', 'label', 'Social Media', 'Social Media'),
('tr', 'label', 'Social Media', 'Sosyal Medya'),
('uk', 'label', 'Social Media', 'Соціальні мережі'),
('vn', 'label', 'Social Media', 'Mạng xã hội'),
('zh', 'label', 'Social Media', '社交媒体')
ON DUPLICATE KEY UPDATE content=VALUES(content);

-- Add Overview language text for dashboard navigation
INSERT INTO texts (lang_code, category, label, content) VALUES
('en', 'label', 'Overview', 'Overview'),
('ar', 'label', 'Overview', 'نظرة عامة'),
('bg', 'label', 'Overview', 'Общ преглед'),
('bs', 'label', 'Overview', 'Pregled'),
('ca', 'label', 'Overview', 'Resum'),
('cn', 'label', 'Overview', '概述'),
('cs', 'label', 'Overview', 'Přehled'),
('da', 'label', 'Overview', 'Oversigt'),
('de', 'label', 'Overview', 'Übersicht'),
('el', 'label', 'Overview', 'Επισκόπηση'),
('es', 'label', 'Overview', 'Resumen'),
('es-ar', 'label', 'Overview', 'Resumen'),
('fa', 'label', 'Overview', 'بررسی اجمالی'),
('fi', 'label', 'Overview', 'Yleiskatsaus'),
('fr', 'label', 'Overview', 'Aperçu'),
('he', 'label', 'Overview', 'סקירה כללית'),
('hi', 'label', 'Overview', 'अवलोकन'),
('hr', 'label', 'Overview', 'Pregled'),
('hu', 'label', 'Overview', 'Áttekintés'),
('hy', 'label', 'Overview', 'Ակնարկ'),
('id', 'label', 'Overview', 'Ikhtisar'),
('it', 'label', 'Overview', 'Panoramica'),
('ja', 'label', 'Overview', '概要'),
('ko', 'label', 'Overview', '개요'),
('lt', 'label', 'Overview', 'Apžvalga'),
('mk', 'label', 'Overview', 'Преглед'),
('nl', 'label', 'Overview', 'Overzicht'),
('no', 'label', 'Overview', 'Oversikt'),
('pl', 'label', 'Overview', 'Przegląd'),
('pt', 'label', 'Overview', 'Visão Geral'),
('pt-br', 'label', 'Overview', 'Visão Geral'),
('ro', 'label', 'Overview', 'Prezentare generală'),
('ru', 'label', 'Overview', 'Обзор'),
('sk', 'label', 'Overview', 'Prehľad'),
('sl', 'label', 'Overview', 'Pregled'),
('sq', 'label', 'Overview', 'Përmbledhje'),
('sr', 'label', 'Overview', 'Преглед'),
('sv', 'label', 'Overview', 'Översikt'),
('sw', 'label', 'Overview', 'Muhtasari'),
('th', 'label', 'Overview', 'ภาพรวม'),
('tl', 'label', 'Overview', 'Pangkalahatang-ideya'),
('tr', 'label', 'Overview', 'Genel Bakış'),
('uk', 'label', 'Overview', 'Огляд'),
('vn', 'label', 'Overview', 'Tổng quan'),
('zh', 'label', 'Overview', '概述')
ON DUPLICATE KEY UPDATE content=VALUES(content);

-- Add Social Media dashboard language texts under category 'socialmedia'
INSERT INTO texts (lang_code, category, label, content) VALUES
-- Social Media Statistics (selected languages)
('en', 'socialmedia', 'Social Media Statistics', 'Social Media Statistics'),
('ar', 'socialmedia', 'Social Media Statistics', 'إحصائيات وسائل التواصل الاجتماعي'),
('de', 'socialmedia', 'Social Media Statistics', 'Social-Media-Statistiken'),
('es', 'socialmedia', 'Social Media Statistics', 'Estadísticas de Redes Sociales'),
('fr', 'socialmedia', 'Social Media Statistics', 'Statistiques des médias sociaux'),
('ru', 'socialmedia', 'Social Media Statistics', 'Статистика социальных сетей'),
('zh', 'socialmedia', 'Social Media Statistics', '社交媒体统计'),

-- Total Social Media Links
('en', 'socialmedia', 'Total Social Media Links', 'Total Social Media Links'),
('ar', 'socialmedia', 'Total Social Media Links', 'إجمالي روابط وسائل التواصل الاجتماعي'),
('de', 'socialmedia', 'Total Social Media Links', 'Gesamt Social-Media-Links'),
('es', 'socialmedia', 'Total Social Media Links', 'Enlaces totales de redes sociales'),
('fr', 'socialmedia', 'Total Social Media Links', 'Total des liens de médias sociaux'),
('ru', 'socialmedia', 'Total Social Media Links', 'Всего ссылок социальных сетей'),
('zh', 'socialmedia', 'Total Social Media Links', '社交媒体链接总数'),

-- Total Followers
('en', 'socialmedia', 'Total Followers', 'Total Followers'),
('ar', 'socialmedia', 'Total Followers', 'إجمالي المتابعين'),
('de', 'socialmedia', 'Total Followers', 'Gesamtanzahl Follower'),
('es', 'socialmedia', 'Total Followers', 'Seguidores totales'),
('fr', 'socialmedia', 'Total Followers', 'Total des abonnés'),
('ru', 'socialmedia', 'Total Followers', 'Всего подписчиков'),
('zh', 'socialmedia', 'Total Followers', '粉丝总数'),

-- Total Likes
('en', 'socialmedia', 'Total Likes', 'Total Likes'),
('ar', 'socialmedia', 'Total Likes', 'إجمالي الإعجابات'),
('de', 'socialmedia', 'Total Likes', 'Gesamtanzahl Likes'),
('es', 'socialmedia', 'Total Likes', 'Me gusta totales'),
('fr', 'socialmedia', 'Total Likes', 'Total des j\'aime'),
('ru', 'socialmedia', 'Total Likes', 'Всего лайков'),
('zh', 'socialmedia', 'Total Likes', '点赞总数'),

-- Followers by Platform
('en', 'socialmedia', 'Followers by Platform', 'Followers by Platform'),
('ar', 'socialmedia', 'Followers by Platform', 'المتابعون حسب المنصة'),
('de', 'socialmedia', 'Followers by Platform', 'Follower nach Plattform'),
('es', 'socialmedia', 'Followers by Platform', 'Seguidores por plataforma'),
('fr', 'socialmedia', 'Followers by Platform', 'Abonnés par plateforme'),
('ru', 'socialmedia', 'Followers by Platform', 'Подписчики по платформам'),
('zh', 'socialmedia', 'Followers by Platform', '各平台粉丝数'),

-- Likes by Platform
('en', 'socialmedia', 'Likes by Platform', 'Likes by Platform'),
('ar', 'socialmedia', 'Likes by Platform', 'الإعجابات حسب المنصة'),
('de', 'socialmedia', 'Likes by Platform', 'Likes nach Plattform'),
('es', 'socialmedia', 'Likes by Platform', 'Me gusta por plataforma'),
('fr', 'socialmedia', 'Likes by Platform', 'J\'aime par plateforme'),
('ru', 'socialmedia', 'Likes by Platform', 'Лайки по платформам'),
('zh', 'socialmedia', 'Likes by Platform', '各平台点赞数'),

-- Social Media Growth Trends
('en', 'socialmedia', 'Social Media Growth Trends', 'Social Media Growth Trends'),
('ar', 'socialmedia', 'Social Media Growth Trends', 'اتجاهات نمو وسائل التواصل الاجتماعي'),
('de', 'socialmedia', 'Social Media Growth Trends', 'Social-Media-Wachstumstrends'),
('es', 'socialmedia', 'Social Media Growth Trends', 'Tendencias de crecimiento en redes sociales'),
('fr', 'socialmedia', 'Social Media Growth Trends', 'Tendances de croissance des médias sociaux'),
('ru', 'socialmedia', 'Social Media Growth Trends', 'Тренды роста социальных сетей'),
('zh', 'socialmedia', 'Social Media Growth Trends', '社交媒体增长趋势'),

-- Top Social Media Profiles
('en', 'socialmedia', 'Top Social Media Profiles', 'Top Social Media Profiles'),
('ar', 'socialmedia', 'Top Social Media Profiles', 'أفضل ملفات وسائل التواصل الاجتماعي'),
('de', 'socialmedia', 'Top Social Media Profiles', 'Top Social-Media-Profile'),
('es', 'socialmedia', 'Top Social Media Profiles', 'Principales perfiles de redes sociales'),
('fr', 'socialmedia', 'Top Social Media Profiles', 'Meilleurs profils de médias sociaux'),
('ru', 'socialmedia', 'Top Social Media Profiles', 'Топ профилей социальных сетей'),
('zh', 'socialmedia', 'Top Social Media Profiles', '热门社交媒体资料'),

-- Social Media Profile Details
('en', 'socialmedia', 'Social Media Profile Details', 'Social Media Profile Details'),
('ar', 'socialmedia', 'Social Media Profile Details', 'تفاصيل ملف وسائل التواصل الاجتماعي'),
('de', 'socialmedia', 'Social Media Profile Details', 'Social-Media-Profildetails'),
('es', 'socialmedia', 'Social Media Profile Details', 'Detalles del perfil de redes sociales'),
('fr', 'socialmedia', 'Social Media Profile Details', 'Détails du profil de médias sociaux'),
('ru', 'socialmedia', 'Social Media Profile Details', 'Детали профиля социальных сетей'),
('zh', 'socialmedia', 'Social Media Profile Details', '社交媒体资料详情'),

-- Platform
('en', 'socialmedia', 'Platform', 'Platform'),
('ar', 'socialmedia', 'Platform', 'المنصة'),
('de', 'socialmedia', 'Platform', 'Plattform'),
('es', 'socialmedia', 'Platform', 'Plataforma'),
('fr', 'socialmedia', 'Platform', 'Plateforme'),
('ru', 'socialmedia', 'Platform', 'Платформа'),
('zh', 'socialmedia', 'Platform', '平台'),

-- Last Checked
('en', 'socialmedia', 'Last Checked', 'Last Checked'),
('ar', 'socialmedia', 'Last Checked', 'آخر فحص'),
('de', 'socialmedia', 'Last Checked', 'Zuletzt geprüft'),
('es', 'socialmedia', 'Last Checked', 'Última verificación'),
('fr', 'socialmedia', 'Last Checked', 'Dernière vérification'),
('ru', 'socialmedia', 'Last Checked', 'Последняя проверка'),
('zh', 'socialmedia', 'Last Checked', '最后检查'),

-- No social media data available message
('en', 'socialmedia', 'No social media data available', 'No social media data available. <a href="social_media.php">Add social media links</a> to start tracking your social media performance.'),
('ar', 'socialmedia', 'No social media data available', 'لا توجد بيانات وسائل التواصل الاجتماعي. <a href="social_media.php">أضف روابط وسائل التواصل الاجتماعي</a> لبدء تتبع أداء وسائل التواصل الاجتماعي الخاصة بك.'),
('de', 'socialmedia', 'No social media data available', 'Keine Social-Media-Daten verfügbar. <a href="social_media.php">Fügen Sie Social-Media-Links hinzu</a>, um Ihre Social-Media-Leistung zu verfolgen.'),
('es', 'socialmedia', 'No social media data available', 'No hay datos de redes sociales disponibles. <a href="social_media.php">Agregue enlaces de redes sociales</a> para comenzar a rastrear su rendimiento en redes sociales.'),
('fr', 'socialmedia', 'No social media data available', 'Aucune donnée de médias sociaux disponible. <a href="social_media.php">Ajoutez des liens de médias sociaux</a> pour commencer à suivre vos performances sur les médias sociaux.'),
('ru', 'socialmedia', 'No social media data available', 'Данные социальных сетей недоступны. <a href="social_media.php">Добавьте ссылки на социальные сети</a>, чтобы начать отслеживать эффективность социальных сетей.'),
('zh', 'socialmedia', 'No social media data available', '暂无社交媒体数据。<a href="social_media.php">添加社交媒体链接</a>以开始跟踪您的社交媒体表现。')
ON DUPLICATE KEY UPDATE content=VALUES(content);
