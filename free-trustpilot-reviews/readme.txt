=== Free Trustpilot Reviews for WP ===
Contributors: Simeon Zahariev
Tags: trustpilot, reviews, slider, shortcode, custom tables
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.5.1
License: GPLv2 or later

A robust, production-ready plugin to fetch, cache, and display Trustpilot reviews utilizing isolated custom tables and native WP Transients.

== Description ==

Free Trustpilot Reviews for WP securely scrapes and stores your Trustpilot reviews into isolated custom database tables, completely bypassing wp_options autoload bloat. 

It features a premium, zero-dependency vanilla JS drag-slider, auto-translation capabilities via API, robust cron background syncing, and heavy Transient caching for maximum frontend performance.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress. (This will generate the required custom SQL tables).
3. Navigate to the new Trustpilot Reviews menu in the admin dashboard.
4. Enter your Trustpilot URL and click "Fetch Reviews".
5. Use [ftr_slider], [ftr_all], or [ftr id="1,2"] to display your reviews anywhere on your site.

== Changelog ==

= 1.5.1 =
* Enhancement: Refactored scraper into a Singleton/Static service.
* Enhancement: Centralized and bulletproofed Cron scheduling on activation and settings save.
* Fix: Eliminated short_id race conditions using strict SQL transactions.
* Fix: Added missing ftr_logs table drop to the uninstall routine.
* Documentation: Added GitHub repository links and documentation tab.

= 1.5.0 =
* Feature: Added dedicated SQL table for Fetch Logs with 30-day auto-pruning.
* Feature: Added Server Status dashboard in WP Admin.
* Feature: Added Fetch Locking via transients to prevent overlapping cron runs.
* Performance: Shifted shortcode filtering to the SQL layer.
* Performance: Added Output Caching via WP Transients.

= 1.4.5 =
* Feature: Added automatic EN to TR translation hook during the fetch cycle.
* Feature: Added a manual translation override editor in the admin dashboard.
* Enhancement: Migrated all storage from wp_options to custom SQL tables.
* Enhancement: Added Vanilla JS Drag-to-Scroll mechanics to the slider.
* Design: Added custom Webkit scrollbars and fixed heights for long review text.

= 1.4.0 =
* Enhancement: Removed SwiperJS dependency entirely.
* Feature: Rebuilt slider using Native CSS Scroll-Snap and Vanilla JS.

= 1.3.0 =
* Feature: Built the Admin UI dashboard.
* Feature: Added Manual Fetch and Danger Zone wipe options.

= 1.2.0 =
* Feature: Initial release of the SwiperJS slider frontend.

= 1.1.0 =
* Feature: Added baseline shortcodes and grid output.

= 1.0.0 =
* Feature: Initial parsing of Trustpilot Next.js JSON-LD data.

= 0.0.1 =
* Initial project prototype.