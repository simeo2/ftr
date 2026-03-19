# Free Trustpilot Reviews for WP

A robust, production-ready WordPress plugin to fetch, cache, and cleanly display your Trustpilot reviews. Built specifically to bypass `wp_options` bloat and heavy external libraries, this plugin relies entirely on custom isolated SQL tables, Transient caching, and a zero-dependency vanilla JS slider.

## 🚀 Features
* **Isolated SQL Tables:** Reviews, logs, and settings are strictly saved to custom `wp_ftr_*` database tables for massive scalability.
* **Zero-Dependency Slider:** No SwiperJS, no jQuery. The carousel uses native CSS `scroll-snap` combined with lightweight vanilla JS drag-to-scroll mechanics.
* **Auto-Translation (EN to TR):** Automatically hooks into an API to translate English reviews to Turkish during the scraping process, bypassing limitations of standard translation plugins.
* **Transient Caching:** All shortcode frontend output is securely cached to minimize database queries on high-traffic sites.
* **Cron Fetch Locks:** Utilizes strict SQL transactions and transient locks to prevent race conditions during automated syncing.
* **Admin Dashboard:** Includes granular logging, server status checks, and a manual translation override editor.

## 📦 Installation
1. Download the repository as a `.zip` file.
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress dashboard.
3. Upload the zip and click **Activate**. *(Note: Activation dynamically generates the custom SQL tables).*
4. Navigate to the new **Trustpilot Reviews** tab in your WordPress sidebar.
5. Enter your public Trustpilot URL and click **Fetch Reviews**.

## 💻 Shortcodes

Display an interactive, drag-to-scroll carousel (defaults to the 10 newest reviews):
`[ftr_slider limit="10"]`

Display a responsive grid archive of all fetched reviews:
`[ftr_all limit="20"]`

Display only specific, hand-picked reviews by ID:
`[ftr id="3,7,12"]`

Exclude specific reviews from any output:
`[ftr_slider no-id="4,5"]`

## 🛠️ Built With
* Native WordPress DB (`$wpdb`)
* Native WP Transients API
* Vanilla JS (ES6+)
* CSS Grid & CSS Flexbox