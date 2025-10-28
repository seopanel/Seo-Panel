# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SEO Panel is an open-source SEO control panel for managing search engine optimization of multiple websites. It's a PHP-based web application using MySQL for data storage and includes a plugin architecture for extensibility.

Version: 5.0.0

## Core Architecture

### MVC-Like Structure

The application follows a custom MVC-like pattern:

- **Entry Points**: Root-level PHP files (e.g., `websites.php`, `keywords.php`, `rank.php`) serve as entry points for different sections
- **Controllers**: Located in `controllers/*.ctrl.php`, all extend the base `Controller` class from `libs/controller.class.php`
- **Views**: Located in `themes/{theme_name}/views/`, using `.ctp.php` extension (CakePHP-style)
- **Models**: Database interaction is handled directly in controllers using the `Database` class

### Bootstrap Process

1. Entry point includes `includes/sp-load.php`
2. `sp-load.php` loads configuration from `config/sp-config.php`
3. Defines path constants (SP_ABSPATH, SP_CTRLPATH, SP_LIBPATH, etc.)
4. Establishes database connection
5. Loads system settings from database into constants
6. Sets timezone and theme paths
7. Applies SQL injection prevention filters
8. Creates `Seopanel` superclass instance

### Key Base Classes

- **Controller** (`libs/controller.class.php`): Base class for all controllers
  - Provides database access via `$this->db`
  - View rendering via `$this->render()`
  - Session management via `$this->session`
  - Validation via `$this->validate`
  - Spider/crawler via `$this->spider`
- **Database** (`libs/database.class.php`): Database abstraction layer
- **View** (`libs/view.class.php`): Template rendering
- **Spider** (`libs/spider.class.php`): Web crawling and content fetching
- **Seopanel** (`libs/seopanel.class.php`): Core application logic

### Plugin System

Plugins are self-contained modules in `plugins/{PluginName}/`:
- Main controller: `{PluginName}.ctrl.php`
- Configuration: `conf.php`
- Views: `themes/{theme_name}/views/`
- Assets: `js/`, `css/`

Active plugins:
- **ArticleSubmitter**: Article submission and spinning
- **QuickWebProxy**: Web proxy functionality
- **SeoDiary**: SEO diary/notes
- **MetaTagGenerator**: Meta tag generation tools

### Controller Naming Convention

Controllers follow a strict naming pattern:
- File: `{name}.ctrl.php` (e.g., `website.ctrl.php`)
- Class: `{Name}Controller` extends `Controller` (e.g., `class WebsiteController extends Controller`)
- Methods starting with `__` are internal/helper methods (e.g., `__getAllWebsites()`)

## Common Development Commands

### Running the Application

Start XAMPP/LAMP stack:
```bash
sudo /opt/lampp/lampp start
```

Access in browser: `http://localhost/sp`

### Database Access

Configuration in `config/sp-config.php`:
- Database: `seopanel`
- Default user: `root`
- Password: (empty)
- Host: `localhost`

### Cron Jobs

Execute cron job manually:
```bash
php /opt/lampp/htdocs/sp/cron.php
```

Execute for specific user:
```bash
php /opt/lampp/htdocs/sp/cron.php [user_id]
```

Execute for specific user and SEO tool:
```bash
php /opt/lampp/htdocs/sp/cron.php [user_id] [seo_tool_id]
```

Other cron scripts:
- `directorycheckercron.php` - Directory submission checker
- `siteauditorcron.php` - Site auditor
- `proxycheckercron.php` - Proxy checker

### Git Workflow

Main branch: `develop`
Current branch: `google_search_fix_sp4_12_0`

## Key Configuration

### Path Constants

Defined in `includes/sp-load.php`:
- `SP_ABSPATH` - Absolute filesystem path
- `SP_WEBPATH` - Web URL path (from config)
- `SP_CTRLPATH` - Controllers directory
- `SP_LIBPATH` - Libraries directory
- `SP_TMPPATH` - Temporary files
- `SP_PLUGINPATH` - Plugins directory
- `SP_THEMEPATH` - Themes directory
- `SP_VIEWPATH` - Active theme views
- `SP_CSSPATH` - Active theme CSS
- `SP_JSPATH` - JavaScript path
- `SP_IMGPATH` - Images path

### Database Settings

Set in `config/sp-config.php`:
- `DB_NAME` - Database name
- `DB_USER` - Database user
- `DB_PASSWORD` - Database password
- `DB_HOST` - Database host
- `DB_ENGINE` - Database engine (mysql)
- `SP_DEBUG` - Debug mode (1 = on)
- `SP_TIMEOUT` - Session timeout in seconds
- `SP_INSTALLED` - Installed version

### Security Features

- SQL injection prevention enabled via `SP_PREVENT_SQL_INJECTION`
- Input sanitization in `sp-load.php` (lines 125-151)
- Session-based authentication with timeout
- Admin role checking via `isAdmin()` and `checkAdminLoggedIn()`

## Core Features

### Main Modules

1. **Keyword Position Checker** (`rank.php`, `controllers/rank.ctrl.php`)
2. **Backlinks Checker** (`backlinks.php`, `controllers/backlink.ctrl.php`)
3. **Site Auditor** (`siteauditor.php`, `controllers/siteauditor.ctrl.php`)
4. **Moz Rank Checker** (`moz.php`, `controllers/moz.ctrl.php`)
5. **Search Engine Saturation** (`saturationchecker.php`, `controllers/saturationchecker.ctrl.php`)
6. **Google Analytics Integration** (`analytics.php`, `controllers/analytics.ctrl.php`)
7. **Webmaster Tools** (`webmaster-tools.php`, `controllers/webmaster.ctrl.php`)
8. **Social Media** (`social_media.php`, `controllers/social_media.ctrl.php`)
9. **Review Manager** (`review.php`, `controllers/review_manager.ctrl.php`)

### Data Model

Core entities:
- **websites** - Managed websites
- **keywords** - Keywords tracked per website
- **users** - User accounts
- **searchengines** - Search engines for rank checking
- **settings** - System configuration (loaded as constants)
- **rankresults** - Keyword ranking results
- **backlinkresults** - Backlink check results
- **saturationresults** - Saturation check results

### Third-Party Integrations

Located in `libs/`:
- **Google API Client** (`google-api-php-client/`) - For Analytics and Search Console
- **SendGrid** (`sendgrid-php/`) - Email sending
- **mPDF** (`mpdf_lib/`) - PDF generation
- **DataForSEO** (`dataforseo/`) - SEO data API
- **pChart** (`pchart.class.php`) - Chart generation

## Development Notes

### Adding a New Controller

1. Create `controllers/{name}.ctrl.php`
2. Define class: `class {Name}Controller extends Controller {}`
3. Create corresponding entry point: `{name}.php`
4. Entry point should include `sp-load.php` and the controller
5. Add views in `themes/classic/views/{name}/`

### Working with Database

Use the database helper:
```php
// Select
$list = $this->db->select($sql);
$single = $this->db->select($sql, true);

// Insert/Update/Delete
$this->db->query($sql);

// Helper methods
$this->dbHelper->getAllRows('table_name', 'where_condition');
$this->dbHelper->getRow('table_name', 'where_condition');
$this->dbHelper->insertRow('table_name', $dataArray);
$this->dbHelper->updateRow('table_name', $dataArray, 'where_condition');
```

### Rendering Views

```php
// Set variables for view
$this->set('variableName', $value);

// Render with default layout
$this->render('viewname');

// Render without layout
$this->render('viewname', '');

// Plugin render
$this->pluginRender('viewname');
```

### Multi-language Support

Text translations stored in `texts` table:
```php
// Get language texts
$texts = $this->getLanguageTexts('category', 'lang_code');

// Access in view
$_SESSION['text']['common']['Label']
$_SESSION['text']['label']['Label']
```

### User Authentication

```php
// Check if logged in
$userId = isLoggedIn();

// Check admin
if (isAdmin()) { }

// Require admin
checkAdminLoggedIn();
```

### Creating Components

Components are reusable controllers in `controllers/components/`:
```php
$component = $this->createComponent('ComponentName');
```

### Working with Spider/Crawler

```php
$spider = New Spider();
$result = $spider->getContent($url);
if (!empty($result['page'])) {
    // Process $result['page']
}
```

## Important Files

- `includes/sp-load.php` - Bootstrap file, included by all entry points
- `includes/sp-common.php` - Common helper functions
- `config/sp-config.php` - Main configuration (database, paths, version)
- `libs/controller.class.php` - Base controller class
- `libs/database.class.php` - Database abstraction
- `libs/validation.class.php` - Form validation
- `libs/spider.class.php` - Web crawling functionality

## Installation Notes

Standard installation:
1. Upload files to web directory
2. Set `config/sp-config.php` permissions to 666
3. Set `tmp/` directory to 777
4. Run `install/index.php` in browser
5. After install, set `config/sp-config.php` to 644
6. Remove `install/` directory

Docker installation available via `docker-compose.yml`

## API Integration

API endpoints in `api/` directory:
- REST-style API for external integrations
- Managed through `controllers/api.ctrl.php`
- User tokens in `user_token` table

## Current Work Context

Based on git status, currently working on:
- Branch: `google_search_fix_sp4_12_0`
- Modified: `config/sp-config.php`
- Recent commits focus on search engine configuration and DataForSEO integration
