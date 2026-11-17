# SEO Panel 7.1.0 Release Notes

**Release Date:** November 2026

---

## Overview

SEO Panel 7.1.0 is a focused release: a new UI convenience for managing plugins and themes, plus fixes carried over from real-world upgrade reports on 7.0.0.

---

## New Features

### Upload-to-Install/Upgrade for Plugins and Themes

- **Settings > Seo Plugins Manager**: new "Upload Plugin" button lets an admin upload a plugin zip directly from the browser to install it (if new) or upgrade it in place (if already registered) - no FTP/SSH access needed.
- **Settings > Themes Manager**: same, via a new "Upload Theme" button.
- Upgrade and install results report the actual version transition (e.g. "upgraded successfully from version 2.0.0 to 2.5.0"), not just a generic success message.
- Built on shared, hardened upload-handling logic: zip-slip protection (rejects any entry trying to escape the extraction directory), a 250MB uncompressed-size cap and 5000-entry cap against zip bombs, and a backup-before-replace step so a bad upload can be rolled back.

---

## Bug Fixes

- **Upgrade failure on sites without the SeoDiary plugin installed**: upgrading from 6.0.0 to 7.0.0 could log "Table 'sd_settings' doesn't exist" - a core upgrade step was mistakenly trying to convert that optional plugin's own table, which only exists on sites that have it installed. Reported via a Softaculous support ticket; fixed and verified against a real 6.0.0 database.
- **Themes Manager's "Activate" button doing nothing**: clicking Activate on a non-current theme silently just reloaded the list with no visible error, no change in the active theme. Caused by a routing change that stripped the parameter the old link depended on. Fixed, and now covered by a new end-to-end regression test (no prior test caught this, since the only existing coverage called the activation method directly, bypassing the exact code path the bug was in).
- **Simple theme's `theme.xml` was a copy-paste of Classic's** (wrong name/description), now corrected.

---

## Database Changes

No new tables or columns - this release needed no schema changes of its own.

- `SP_VERSION_NUMBER` bumped to `7.1.0`.
- Reorganized how the upgrade SQL files themselves are structured: 7.0.0's full upgrade content is now properly archived under `install/data/upgrade_v6.0.0_v7.0.0.sql` (with the sd_settings fix applied there, since that's what actually runs for anyone still on 6.0.0), and the generic `install/data/upgrade.sql` is reset to hold only this release's own change going forward, matching the project's established convention.

---

## Upgrade Instructions

1. Backup your database before upgrading.
2. Upload the new files to your SEO Panel installation.
3. Run the upgrade from `install/upgrade.php` (or let the Seo Plugins/Themes Manager's own upload feature handle plugin/theme files specifically).
4. Clear your browser cache.

---

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (MySQL 8.0 recommended)
- Apache/Nginx web server
- Minimum 512MB RAM

---

## Known Issues

- None reported at this time.

---

## Credits

Thank you to everyone who reported issues on 7.0.0, including the Softaculous team.

---

**Full Changelog:** Compare changes from v7.0.0 to v7.1.0 in the repository.
