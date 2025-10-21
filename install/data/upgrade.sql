--
-- Seo Panel 4.12.0 changes
--

update `settings` set set_val='4.12.0' WHERE `set_name` LIKE 'SP_VERSION_NUMBER';

UPDATE searchengines SET url = REPLACE(url, 'http://', 'https://') WHERE url LIKE 'http://%';

UPDATE searchengines SET url = REPLACE(url, '&num=[--num--]', '') WHERE  url LIKE '%google%';

UPDATE searchengines SET url = REPLACE(url, '&as_qdr=all&gws_rd=cr&nfpr=1', '') WHERE  url LIKE '%google%';

ALTER TABLE `searchengines` ADD `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `texts` CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL; 

update `settings` set display=0 WHERE `set_name` LIKE 'SP_MOZ_API_ACCESS_ID';


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

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'common', 'Rankings', 'Rankings', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'common', 'Range', 'Range', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'common', 'Number', 'Number', CURRENT_TIMESTAMP);


INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Welcome message', 'Welcome back! Please login to your account', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Username placeholder', 'Enter your username', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Password placeholder', 'Enter your password', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'No account text', 'Don\'t have an account?', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Security message', 'Your information is secure and encrypted', CURRENT_TIMESTAMP);



INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Forgot subtitle', 'Enter your email address and we\'ll send you instructions to reset your password', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Verification', 'Verification', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Enter the code', 'Enter the code', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Remember password text', 'Remember your password?', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Email placeholder', 'your@email.com', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'login', 'Sign In', 'Sign In', CURRENT_TIMESTAMP);


INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Register subtitle', 'Join us today and start optimizing your SEO', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Subscription Details', 'Subscription Details', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Account Information', 'Account Information', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Personal Information', 'Personal Information', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'First name placeholder', 'First name', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Last name placeholder', 'Last name', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'register', 'Already have account', 'Already have an account?', CURRENT_TIMESTAMP);


INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Welcome to SEO Panel', 'Welcome to SEO Panel', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Hero subtitle', 'World\'s First Open Source SEO Control Panel for Multiple Websites', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Hero description', 'A complete open source SEO control panel for managing search engine optimization of your websites. SEO Panel is a powerful toolkit that includes the latest SEO tools to increase and track the performance of your websites.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Login to Get Started', 'Login to Get Started', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'View Demo', 'View Demo', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Powerful SEO Features', 'Powerful SEO Features', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Keyword Position Checker desc', 'Track your keyword rankings across multiple search engines with detailed daily reports and beautiful graphs.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Backlinks Checker desc', 'Monitor the number of backlinks from major search engines and track your link building progress over time.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Site Auditor desc', 'Audit all SEO factors of each page and generate XML, HTML, and TEXT sitemaps for search engines.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Rank Checker desc', 'Check Google PageRank, Alexa Rank, and Moz Rank with comprehensive daily tracking and reporting.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Search Engine Saturation desc', 'Find the number of indexed pages across different search engines and monitor your indexing progress.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Plugin Architecture', 'Plugin Architecture', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Plugin Architecture desc', 'Extend functionality with powerful plugins including Article Submitter, Meta Tag Generator, and more.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Directory Submission desc', 'Automatically submit your websites to major free and paid directories with status tracking.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Social Media Integration', 'Social Media Integration', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Social Media Integration desc', 'Integrate with Google Analytics, Search Console, and social media platforms for comprehensive reporting.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Why Choose SEO Panel?', 'Why Choose SEO Panel?', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', '100% Open Source', '100% Open Source', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', '100% Open Source desc', 'Free software released under GNU GPL. Download, customize, and use without any restrictions.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Trusted by Thousands', 'Trusted by Thousands', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Trusted by Thousands desc', 'Since 2010, thousands of webmasters worldwide use SEO Panel to optimize their websites.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Highly Extensible', 'Highly Extensible', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Highly Extensible desc', 'Easily develop and install custom plugins to extend functionality according to your needs.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Multi-Website Support', 'Multi-Website Support', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Multi-Website Support desc', 'Manage SEO for unlimited websites from a single control panel with centralized reporting.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Resources & Support', 'Resources & Support', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Download SEO Panel', 'Download SEO Panel', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Documentation', 'Documentation', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Get Support', 'Get Support', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Browse Plugins', 'Browse Plugins', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Contact Us', 'Contact Us', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Cloud Hosted', 'Cloud Hosted', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'guest', 'Support Development', 'Support Development', CURRENT_TIMESTAMP);


-- Dashboard page texts
INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Keyword Statistics', 'Keyword Statistics', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Top 3', 'Top 3', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Top 10', 'Top 10', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Not Ranked', 'Not Ranked', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Keyword Distribution by Rank', 'Keyword Distribution by Rank', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Keywords by Ranking Position', 'Keywords by Ranking Position', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Ranking Volatility', 'Ranking Volatility', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Keywords with most ranking fluctuations', 'Keywords with most ranking fluctuations', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Top 10 Most Volatile Keywords', 'Top 10 Most Volatile Keywords', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Volatility Score', 'Volatility Score', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Volatility Score (Standard Deviation)', 'Volatility Score (Standard Deviation)', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Best Rank', 'Best Rank', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Worst Rank', 'Worst Rank', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Avg Rank', 'Avg Rank', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Trend', 'Trend', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Volatility', 'Volatility', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'Volatility data requires at least 2 ranking checks within the selected period', 'Volatility data requires at least 2 ranking checks within the selected period.', CURRENT_TIMESTAMP);

INSERT INTO `texts` (`id`, `lang_code`, `category`, `label`, `content`, `changed`)
VALUES (NULL, 'en', 'dashboard', 'positions', 'positions', CURRENT_TIMESTAMP);


