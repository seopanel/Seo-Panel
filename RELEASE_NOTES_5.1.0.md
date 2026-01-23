# SEO Panel 5.1.0 Release Notes

**Release Date:** January 2026

---

## Overview

SEO Panel 5.1.0 brings significant enhancements to the Site Auditor module, improved database compatibility, and a refreshed user interface for notifications.

---

## New Features

### Site Auditor Enhancements

- **AI Robot Detection**: New tracking for pages blocked by AI crawlers/robots
- **Mobile-Friendly Detection**: Identifies mobile-friendly pages during audits
- **HTTPS Security Check**: Tracks secure (HTTPS) vs insecure pages
- **Open Graph Tags Detection**: Checks for presence of OG meta tags for social sharing
- **Twitter Cards Detection**: Identifies pages with Twitter Card meta tags
- **Robots.txt Blocking**: Detects pages blocked by robots.txt directives
- **Canonical URL Support**: Tracks canonical URLs for duplicate content management
- **Page Discovery Tracking**: New field to track how pages were discovered (crawl, sitemap, robots, canonical, import)

### Sitemap Support

- **XML Sitemap Parsing**: New sitemap management system for auditor projects
- **Multiple Sitemap Types**: Support for XML, TXT, and Sitemap Index files
- **Sitemap URL Configuration**: Configure sitemap URLs per project for comprehensive crawling

### New Settings

- **Sample API Data Mode**: Test functionality without consuming API credits (useful for development/testing)
- **Exclude File Extensions**: System-wide setting to exclude specific file types from site audits (zip, images, videos, documents, etc.)
- **Project-Level Extension Exclusions**: Per-project file extension exclusion settings

### New Language Translations

Added translation support for **14 new languages**, bringing total supported languages to 45:

- Albanian
- Armenian
- Bosnian
- Catalan
- Croatian
- Finnish
- Hindi
- Korean
- Macedonian
- Norwegian
- Swahili
- Tagalog
- Thai
- Ukrainian

---

## Improvements

### API Updates

- **DataForSEO API**: Updated integration to support latest API version with improved data accuracy and response handling
- **Moz API**: Updated to latest Moz Links API version with enhanced metrics support including spam score tracking

### Database Enhancements

- **UTF8MB4 Character Support**: Full Unicode support including emojis for page titles, descriptions, and keywords
- **Extended URL Storage**: Link URLs now support TEXT data type for very long tracking URLs and query strings
- **Extended Link Fields**: Link anchor text and title fields expanded to TEXT for better compatibility

### User Interface

- **Enhanced Main Dashboard**: Added quick access links for:
  - Site Auditor
  - Social Media
  - Review Manager
- **Redesigned News Alert Banner**: Modern, professional notification banner with:
  - Warm amber gradient background
  - Orange accent stripe
  - Improved typography and contrast
  - Smooth close button animation

---

## Bug Fixes

- Fixed "Data too long for column" error when storing long URLs in auditor page links
- Fixed duplicate column errors during fresh installations
- Removed redundant ALTER TABLE statements that caused installation failures on existing databases

---

## Database Changes

### New Tables

- `auditorsitemaps` - Stores sitemap configurations for auditor projects

### Modified Tables

- `auditorreports` - Added columns: `ai_robot_allowed`, `mobile_friendly`, `https_secure`, `has_og_tags`, `has_twitter_cards`, `blocked_by_robots`, `canonical_url`, `discovered_via`
- `auditorprojects` - Added columns: `exclude_extensions`, `sitemap_url`
- `auditorpagelinks` - Changed `link_url`, `link_anchor`, `link_title` to TEXT type with UTF8MB4 support

### New Settings

| Setting Name | Description | Default |
|--------------|-------------|---------|
| SP_USE_SAMPLE_API_DATA | Enable sample API data for testing | 0 (disabled) |
| SA_EXCLUDE_FILE_EXTENSIONS | File extensions to exclude from audits | zip,gz,tar,png,jpg,... |

---

## Upgrade Instructions

1. Backup your database before upgrading
2. Upload the new files to your SEO Panel installation
3. Run the upgrade script from `install/data/upgrade.sql`
4. Clear your browser cache

---

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (MySQL 8.0 recommended)
- Apache/Nginx web server
- Minimum 512MB RAM

---

## Known Issues

- None reported at this time

---

## Credits

Thank you to all contributors and users who provided feedback for this release.

---

**Full Changelog:** Compare changes from v5.0.0 to v5.1.0 in the repository.
