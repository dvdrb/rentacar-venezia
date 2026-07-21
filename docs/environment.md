# Environment

- Inspected WordPress root: `/Users/dvdrb/Local Sites/rentacar-venezia-local/app/public`
- Installation shape: LocalWP `app/public` document root; `wp-admin`, `wp-includes`, and `wp-content` present.
- WordPress core version: 7.0.2 (`wp-includes/version.php:19`).
- LocalWP PHP runtimes found: 7.4.30 and 8.2.29. Existing theme compatibility must be tested before using PHP 8.2.
- Installed themes: `autocar` 1.1.3, Twenty Twenty-Three 1.6, Twenty Twenty-Four 1.5, Twenty Twenty-Five 1.4.
- Active theme: `autocar` 1.1.3; template and stylesheet are both `autocar`, with no parent. **Verified by WordPress runtime.**
- LocalWP runtime: PHP-FPM is PHP 7.4.30 with a site-specific `php.ini`/Local bootstrap that supplies the MySQL socket. Earlier terminal checks failed because they invoked the PHP binary without that configuration. The site is healthy at `http://rentacar-venezia-local.local/`; no production host was accessed.
- Installed plugins: ACF, ACF Repeater, ACFML, All-in-One WP Migration, Classic Editor, Classic Widgets, Click to Chat for WhatsApp, Contact Form 7, GTM4WP, LiteSpeed Cache, Regenerate Thumbnails, WPML core/String Translation/Translation Management, UpdraftPlus, WPForms Lite, and Update Theme and Plugins from ZIP File.
- `wp-content/mu-plugins/` is absent.
- The LocalWP WordPress root is not a Git repository.

## Compatibility clues

The legacy theme uses many short open tags, for example `legacy/active-theme/single.php:6`, requiring `short_open_tag` support. It uses legacy PHP-style constructors in classes such as `new_general_gps` (`functions.php:587–613`), and directly invokes `mysqli_real_escape_string` without its required connection argument (`single.php:2–4`). These patterns make PHP 8 compatibility a high-risk verification item.

## Verified WordPress / WPML configuration

- Home and site URL: `http://rentacar-venezia-local.local/`; permalink structure `/%postname%/`; category/tag bases unset.
- Static homepage: ID **3158**, `HOME PAGE`, Italian, published, URL `/`. Posts page is unset (`0`). Time zone is unnamed with UTC offset 0.
- WPML default: Italian (`it`); enabled languages: Italian, English (`en`), Romanian (`ro`), Russian (`ru`). URLs use language directories (mode `1`) and browser redirect is disabled.
- WPML translates `cars`, posts, pages, attachments, blocks, navigation, templates, and template parts. No custom-field translation preferences were returned in sanitized settings.
