--
-- Seo Panel 3.8.0 changes
--

--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) COLLATE utf8_unicode_ci NOT NULL,
  `iso_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `symbol` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `unicode` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `paypal` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=68 ;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`id`, `name`, `iso_code`, `symbol`, `unicode`, `position`, `paypal`, `status`) VALUES
(1, 'United Arab Emirates Dirham', 'AED', 'د.إ', '', 'after', 0, 1),
(2, 'Netherlands Antillian Guilder', 'ANG', 'ƒ', '&#x0192;', 'before', 0, 1),
(3, '', 'AOA', 'AOA', 'AOA', 'before', 0, 1),
(4, 'Argentine Peso', 'ARS', '$', '&#36;', 'before', 0, 1),
(5, 'Australian Dollar', 'AUD', '$', '&#36;', 'before', 1, 1),
(6, '', 'BAM', 'KM', 'KM', 'before', 0, 1),
(7, 'Barbados Dollar', 'BBD', '$', '&#36;', 'before', 0, 1),
(8, '', 'BGL', 'лв', '&#1083;&', 'before', 0, 1),
(9, 'Bahraini Dinar', 'BHD', 'BD', 'BD', 'after', 0, 1),
(10, 'Brunei Dollar', 'BND', '$', '&#36;', 'before', 0, 1),
(11, 'Brazilian Real', 'BRL', 'R$', 'R&#36;', 'before', 1, 1),
(12, 'Canadian Dollar', 'CAD', '$', '&#36;', 'before', 1, 1),
(13, 'Swiss Franc', 'CHF', 'Fr', 'Fr', 'before', 1, 1),
(14, 'Chilean Unidades de Fomento', 'CLF', 'UF', 'UF', 'after', 0, 1),
(15, 'Chilean Peso', 'CLP', '$', '&#36;', 'before', 0, 1),
(16, 'Yuan (Chinese) Renminbi', 'CNY', '¥', '&#165;', 'before', 0, 1),
(17, 'Colombian Peso', 'COP', '$', '&#36;', 'before', 0, 1),
(18, 'Costa Rican Colon', 'CRC', '₡', '&#x20A1;', 'before', 0, 1),
(19, 'Czech Republic Koruna', 'CZK', 'Kč', 'K&#269;', 'after', 1, 1),
(20, 'Danish Krone', 'DKK', 'kr', 'kr', 'before', 1, 1),
(21, 'Estonian Kroon (EEK)', 'EEK', 'KR', 'KR', 'before', 0, 1),
(22, 'Egyptian Pound', 'EGP', 'E£', 'E&#163;', 'before', 0, 1),
(23, 'Euro', 'EUR', '€', '&#128;', 'before', 1, 1),
(24, 'Fiji Dollar', 'FJD', 'FJ$', 'FJ&#36;', 'before', 0, 1),
(25, 'British Pound', 'GBP', '£', '&#163;', 'before', 1, 1),
(26, 'Guatemalan Quetzal', 'GTQ', 'Q', 'Q', 'before', 0, 1),
(27, 'Hong Kong Dollar', 'HKD', '$', '&#36;', 'before', 1, 1),
(28, '', 'HRK', 'kn', 'kn', 'before', 0, 1),
(29, 'Hungarian Forint', 'HUF', 'Ft', 'Ft', 'before', 1, 1),
(30, 'Indonesian Rupiah', 'IDR', 'Rp', 'Rp', 'before', 0, 1),
(31, 'Israeli Shekel', 'ILS', '₪', '&#x20AA;', 'before', 1, 1),
(32, 'Indian Rupee', 'INR', 'Rs', 'Rs', 'before', 0, 1),
(33, 'Jordanian Dinar', 'JOD', 'د.ا', '', '', 0, 1),
(34, 'Japanese Yen', 'JPY', '¥', '&#165;', 'before', 1, 1),
(35, 'Kenyan Schilling', 'KES', 'KSh', 'Ksh', 'before', 0, 1),
(36, '(South) Korean Won', 'KRW', '₩', '&#x20A9;', 'before', 0, 1),
(37, 'Kuwaiti Dinar', 'KWD', 'KD', 'KD', 'after', 0, 1),
(38, 'Cayman Islands Dollar', 'KYD', '$', '&#36;', 'before', 0, 1),
(39, '', 'LTL', 'Lt', 'Lt', 'before', 0, 1),
(40, '', 'LVL', 'Ls', 'Ls', 'before', 0, 1),
(41, 'Moroccan Dirham', 'MAD', 'د.م', '', '', 0, 1),
(42, 'Maldive Rufiyaa', 'MVR', 'Rf', 'Rf', 'before', 0, 1),
(43, '', 'MXN', '$', '&#36;', 'before', 1, 1),
(44, 'Malaysian Ringgit', 'MYR', 'RM', 'RM', 'before', 1, 1),
(45, 'Nigerian Naira', 'NGN', '₦', '&#x20A6;', 'before', 0, 1),
(46, 'Norwegian Kroner', 'NOK', 'kr', 'kr', 'before', 1, 1),
(47, 'New Zealand Dollar', 'NZD', '$', '&#36;', 'before', 1, 1),
(48, 'Omani Rial', 'OMR', 'OMR', '&#65020;', 'after', 0, 1),
(49, 'Peruvian Nuevo Sol', 'PEN', 'S/.', 'S/.', 'before', 0, 1),
(50, 'Philippine Peso', 'PHP', '₱', '&#x20B1;', 'before', 1, 1),
(51, 'Polish Zloty', 'PLN', 'zł', 'Z&#322;', 'before', 1, 1),
(52, 'Qatari Rial', 'QAR', 'QAR', '&#65020;', 'after', 0, 1),
(53, 'Romanian Leu', 'RON', 'lei', 'lei', 'before', 0, 1),
(54, 'Russian Ruble', 'RUB', 'руб', '&#1088;&', 'before', 1, 1),
(55, 'Saudi Arabian Riyal', 'SAR', 'SAR', '&#65020;', 'after', 0, 1),
(56, 'Swedish Krona', 'SEK', 'kr', 'kr', 'before', 1, 1),
(57, 'Singapore Dollar', 'SGD', '$', '&#36;', 'before', 1, 1),
(58, 'Thai Baht', 'THB', '฿', '&#322;', 'before', 1, 1),
(59, 'Turkish Lira', 'TRY', 'TL', 'TL', 'before', 1, 1),
(60, 'Trinidad and Tobago Dollar', 'TTD', '$', '&#36;', 'before', 0, 1),
(61, 'Taiwan Dollar', 'TWD', '$', '&#36;', 'before', 0, 1),
(62, '', 'UAH', '₴', '&#8372;', 'before', 0, 1),
(63, 'US Dollar', 'USD', '$', '&#36;', 'before', 1, 1),
(64, 'Venezualan Bolivar', 'VEF', 'Bs ', 'Bs.', 'before', 0, 1),
(65, 'Vietnamese Dong', 'VND', '₫', '&#x20AB;', 'before', 0, 1),
(66, 'East Caribbean Dollar', 'XCD', '$', '&#36;', 'before', 0, 1),
(67, 'South African Rand', 'ZAR', 'R', 'R', 'before', 0, 1);

INSERT INTO `settings` (`set_label`, `set_name`, `set_val`, `set_category`, `set_type`, `display`)  
VALUES ('Currency', 'SP_PAYMENT_CURRENCY', 'USD', 'system', 'medium', '1');

--
-- Table structure for table `user_specs`
--

CREATE TABLE IF NOT EXISTS `user_specs` (
`id` int(11) NOT NULL,
  `user_type_id` int(11) NOT NULL,
  `spec_column` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `spec_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `user_specs`
--
ALTER TABLE `user_specs`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `user_specs`
--
ALTER TABLE `user_specs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

alter table usertypes add column status tinyint(4) default 1;
insert into user_specs(user_type_id,spec_column,spec_value) values (2,'keywordcount',50),(2,'websitecount',10),(2,'price',0);

ALTER TABLE `backlinkresults` ADD `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `keywordcrontracker` ADD `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `searchresultdetails` ADD `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `rankresults` ADD `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `saturationresults` ADD `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `users` ADD `expiry_date` DATE NULL DEFAULT NULL ;

UPDATE `searchengines` SET `regex` = '<li.*?class="?g.*?><h3 class="r"><a href="\\/url\\?q=(.*?)&amp;sa=U.*?>(.*?)<\\/a>.*?<\\/div><span.*?>(.*?)<\\/span>'  WHERE `url` LIKE '%google%';

delete from featured_directories where id in (2,3);
