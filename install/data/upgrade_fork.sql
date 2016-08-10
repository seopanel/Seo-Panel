--
-- Table structure for table `meta_reports`
--

CREATE TABLE IF NOT EXISTS `meta_reports` (
  `meta_id` int(11) NOT NULL,
  `related_to_id` int(11) NOT NULL,
  `meta_key` varchar(255) COLLATE utf8_bin NOT NULL,
  `meta_value` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for table `meta_reports`
--
ALTER TABLE `meta_reports`
  ADD PRIMARY KEY (`meta_id`);

--
-- AUTO_INCREMENT for table `meta_reports`
--
ALTER TABLE `meta_reports`
  MODIFY `meta_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `meta_websites`
--

CREATE TABLE IF NOT EXISTS `meta_websites` (
  `meta_id` int(11) NOT NULL,
  `related_to_id` int(11) NOT NULL,
  `meta_key` varchar(255) COLLATE utf8_bin NOT NULL,
  `meta_value` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for table `meta_websites`
--
ALTER TABLE `meta_websites`
  ADD PRIMARY KEY (`meta_id`);

--
-- AUTO_INCREMENT for table `meta_websites`
--
ALTER TABLE `meta_websites`
  MODIFY `meta_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `meta_projects`
--

CREATE TABLE IF NOT EXISTS `meta_projects` (
  `meta_id` int(11) NOT NULL,
  `related_to_id` int(11) NOT NULL,
  `meta_key` varchar(255) COLLATE utf8_bin NOT NULL,
  `meta_value` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for table `meta_projects`
--
ALTER TABLE `meta_projects`
  ADD PRIMARY KEY (`meta_id`);

--
-- AUTO_INCREMENT for table `meta_projects`
--
ALTER TABLE `meta_projects`
  MODIFY `meta_id` int(11) NOT NULL AUTO_INCREMENT;

