<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<style>
    .ftr-header h1 { font-size: 26px; font-weight: 600; margin: 0 0 20px 0; color: #1d2327; display: flex; align-items: center; gap: 10px; }
    .ftr-badge { background: #2271b1; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: 500; }
    .ftr-tab-content { display: none; background: #fff; padding: 30px; border: 1px solid #c3c4c7; border-top: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .ftr-tab-content.active { display: block; }
    .ftr-card { border: 1px solid #e2e4e7; border-radius: 8px; padding: 24px; margin-bottom: 24px; background: #f8f9fa; }
    .ftr-card h2 { margin-top: 0; border-bottom: 1px solid #e2e4e7; padding-bottom: 10px; margin-bottom: 20px; font-size: 18px; }
    .ftr-shortcode-box { background: #fff; border: 1px solid #e2e4e7; border-left: 4px solid #2271b1; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
    .ftr-shortcode-box code.primary { font-size: 16px; font-weight: 700; background: none; padding: 0; color: #2271b1; display: block; margin-bottom: 8px; }
    .ftr-shortcode-box p { margin: 0 0 10px 0; color: #50575e; font-size: 14px; }
    .ftr-param { background: #f0f0f1; padding: 10px 15px; border-radius: 4px; font-size: 13px; color: #3c434a; }
    .ftr-param code { background: #fff; border: 1px solid #dcdde1; color: #d63638; }
    .ftr-status-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
    .ftr-status-box { background: #fff; border: 1px solid #e2e4e7; padding: 15px; border-radius: 6px; text-align: center; }
    .ftr-status-box h4 { margin: 0 0 5px 0; font-size: 12px; color: #646970; text-transform: uppercase; }
    .ftr-status-box p { margin: 0; font-size: 16px; font-weight: 600; color: #1d2327; }
</style>

<div class="wrap ftr-wrap">
    <div class="ftr-header">
        <h1>⭐ Free Trustpilot Reviews for WP - <?php echo esc_html($business_name); ?> <span class="ftr-badge">v1.5.1</span></h1>
    </div>

    <?php if ( $fetch_result ) : ?>
        <div class="notice notice-<?php echo $fetch_result['success'] ? 'success' : 'error'; ?> is-dismissible"><p><strong>System:</strong> <?php echo esc_html( $fetch_result['message'] ); ?></p></div>
    <?php endif; ?>

    <?php if ( $is_wizard ) : ?>
        <div class="ftr-card" style="max-width: 700px; margin-top: 20px;">
            <h2>🚀 Welcome to Setup</h2>
            <p>Let's get your Trustpilot reviews syncing. Enter your public URL below and run the first fetch.</p>
            <form method="post" action="">
                <?php wp_nonce_field( 'ftr_fetch_action', 'ftr_fetch_nonce' ); ?>
                <input type="hidden" name="ftr_manual_fetch" value="1">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" style="padding-left: 0;">Trustpilot URL</th>
                        <td><input type="url" name="target_url" value="<?php echo esc_attr( $target_url ); ?>" placeholder="https://www.trustpilot.com/review/YourCompany.com" class="regular-text" required style="width: 100%;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="padding-left: 0;">Sync Interval</th>
                        <td>
                            <input type="number" name="sync_hours" value="<?php echo esc_attr( $sync_hours ); ?>" min="1" max="168" class="small-text" required />
                            <span class="description">Hours (Recommended: 24 to 36)</span>
                        </td>
                    </tr>
                </table>
                <p class="submit"><?php submit_button( 'Fetch Reviews & Complete Setup', 'primary', 'submit', false ); ?></p>
            </form>
        </div>

    <?php else : ?>
        <h2 class="nav-tab-wrapper ftr-nav-tabs">
            <a href="#setup" class="nav-tab" data-tab="setup">⚙️ Setup & Sync</a>
            <a href="#shortcodes" class="nav-tab" data-tab="shortcodes">📖 Shortcodes</a>
            <a href="#css" class="nav-tab" data-tab="css">🎨 Custom CSS</a>
            <a href="#reviews" class="nav-tab" data-tab="reviews">📋 Stored Reviews</a>
            <a href="#logs" class="nav-tab" data-tab="logs">⏱️ Sync Logs</a>
            <a href="#help" class="nav-tab" data-tab="help">🆘 Help & Dev</a>
        </h2>

        <div id="tab-setup" class="ftr-tab-content">
            <div class="ftr-status-grid">
                <div class="ftr-status-box"><h4>Last Fetch Attempt</h4><p><?php echo esc_html( $status_last_fetch ); ?></p></div>
                <div class="ftr-status-box"><h4>Last Success</h4><p style="color: #00b67a;"><?php echo esc_html( $status_last_success ); ?></p></div>
                <div class="ftr-status-box"><h4>Total Reviews Saved</h4><p><?php echo esc_html( $status_inserted ); ?></p></div>
                <div class="ftr-status-box"><h4>Last Error</h4><p style="color: #d63638; font-size:12px;"><?php echo esc_html( $status_last_error ); ?></p></div>
            </div>

            <div class="ftr-card">
                <h2>Configuration</h2>
                <form method="post" action="">
                    <?php wp_nonce_field( 'ftr_fetch_action', 'ftr_fetch_nonce' ); ?>
                    <input type="hidden" name="ftr_save_settings" value="1">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Trustpilot URL <span class="dashicons dashicons-lock" style="color:#8c8f94; font-size:16px;"></span></th>
                            <td><input type="url" value="<?php echo esc_attr( $target_url ); ?>" class="regular-text" readonly style="background: #f0f0f1; border-color: #8c8f94; color: #8c8f94;" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Sync Interval</th>
                            <td>
                                <input type="number" name="sync_hours" value="<?php echo esc_attr( $sync_hours ); ?>" min="1" max="168" class="small-text" required />
                                <span class="description">Hours between automatic background syncs.</span>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( 'Save Interval Settings' ); ?>
                </form>
            </div>

            <div class="ftr-card">
                <h2>Manual Sync</h2>
                <p><strong>Next automatic sync scheduled in:</strong> <span style="color: #2271b1; font-weight: bold;"><?php echo esc_html( $time_diff ); ?></span></p>
                <form method="post" action="" style="margin-top: 15px;">
                    <?php wp_nonce_field( 'ftr_fetch_action', 'ftr_fetch_nonce' ); ?>
                    <input type="hidden" name="ftr_manual_fetch" value="1">
                    <?php submit_button( 'Fetch Reviews Now', 'secondary' ); ?>
                </form>
            </div>

            <div class="ftr-card" style="border-color: #d63638; background: #fcf0f1;">
                <h2 style="color: #d63638; border-bottom-color: #f7c5c7;">⚠️ Danger Zone</h2>
                <button type="button" class="button button-primary" id="unlock-url-btn" style="background: #d63638; border-color: #d63638;">Unlock & Change URL</button>
                <form method="post" action="" id="change-url-form" style="display: none; margin-top: 15px;">
                    <?php wp_nonce_field( 'ftr_change_url_action', 'ftr_change_url_nonce' ); ?>
                    <input type="hidden" name="ftr_wipe_and_change_url" value="1">
                    <input type="url" name="new_target_url" class="regular-text" required placeholder="https://www.trustpilot.com/review/YourCompany.com" style="margin-bottom: 10px; display: block;" />
                    <?php submit_button( 'Wipe Data & Save New URL', 'primary', 'submit', false, array('style' => 'background: #d63638; border-color: #d63638;') ); ?>
                    <button type="button" class="button" id="cancel-url-btn" style="margin-left: 10px;">Cancel</button>
                </form>

                <form method="post" action="" style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #f7c5c7;">
                    <?php wp_nonce_field( 'ftr_wipe_action', 'ftr_wipe_nonce' ); ?>
                    <input type="hidden" name="ftr_wipe_deactivate" value="1">
                    <button type="submit" class="button button-primary" onclick="return confirm('Drop all tables and deactivate?');" style="background: #000; border-color: #000;">Drop Tables & Deactivate Plugin</button>
                </form>
            </div>
        </div>

        <div id="tab-shortcodes" class="ftr-tab-content">
            <p style="font-size: 15px; margin-bottom: 25px;">All outputs are heavily cached with WP Transients for maximum performance.</p>
            <div class="ftr-shortcode-box">
                <code class="primary">[ftr_slider limit="10"]</code>
                <p>Displays a touch-friendly, drag-to-scroll carousel.</p>
                <div class="ftr-param"><strong>Parameters:</strong> <code>limit="10"</code> (Default 10), <code>no-id="1,2"</code></div>
            </div>
            <div class="ftr-shortcode-box">
                <code class="primary">[ftr_all limit="20"]</code>
                <p>Displays a responsive CSS grid containing your archive of fetched reviews.</p>
                <div class="ftr-param"><strong>Parameters:</strong> <code>limit="20"</code> (Default shows all), <code>no-id="5"</code></div>
            </div>
            <div class="ftr-shortcode-box">
                <code class="primary">[ftr id="3,7,12"]</code>
                <p>Displays only the specific review IDs you request in a grid.</p>
            </div>
        </div>

        <div id="tab-css" class="ftr-tab-content">
            <div class="ftr-card">
                <h2>🎨 Custom CSS</h2>
                <form method="post" action="">
                    <?php wp_nonce_field( 'ftr_fetch_action', 'ftr_fetch_nonce' ); ?>
                    <input type="hidden" name="ftr_save_settings" value="1">
                    <textarea name="custom_css" rows="12" style="width: 100%; font-family: monospace; background: #2c3338; color: #80ebd3; padding: 15px; border-radius: 4px; border: none; margin-bottom: 15px;" placeholder="/* Add your custom CSS here */"><?php echo esc_textarea( $custom_css ); ?></textarea>
                    <?php submit_button( 'Save Custom CSS' ); ?>
                </form>
            </div>
        </div>

        <div id="tab-reviews" class="ftr-tab-content">
            <p>Recent reviews (Max 200 shown here for admin performance). Edit TR translations manually below.</p>
            <form method="post" action="">
                <?php wp_nonce_field( 'ftr_fetch_action', 'ftr_fetch_nonce' ); ?>
                <input type="hidden" name="ftr_save_translations" value="1">
                <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 6%;">ID</th>
                            <th style="width: 12%;">Author</th>
                            <th style="width: 10%;">Rating</th>
                            <th style="width: 36%;">Original Text (EN)</th>
                            <th style="width: 36%;">Front-End Text (TR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty($reviews) ) : ?>
                            <tr><td colspan="5">No reviews fetched yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ( $reviews as $review ) : ?>
                                <tr>
                                    <td><strong style="color: #2271b1;">#<?php echo esc_html( $review['short_id'] ?? '-' ); ?></strong><br><span style="font-size:10px; color:#888;"><?php echo esc_html( date('M j, Y', strtotime($review['date'])) ); ?></span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <?php if ( !empty($review['avatar']) ) : ?>
                                                <img src="<?php echo esc_url($review['avatar']); ?>" style="width: 24px; height: 24px; border-radius: 50%;" alt="">
                                            <?php endif; ?>
                                            <strong><?php echo esc_html( $review['author'] ); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo str_repeat('⭐', $review['rating']); ?></td>
                                    <td style="font-size:12px; color:#50575e;"><?php echo esc_html( $review['text'] ); ?></td>
                                    <td>
                                        <textarea name="translations[<?php echo esc_attr($review['id']); ?>]" rows="4" style="width: 100%; font-size: 13px; padding: 8px;"><?php echo esc_textarea( $review['text_tr'] ); ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ( !empty($reviews) ) submit_button( 'Save Translations', 'primary' ); ?>
            </form>
        </div>

        <div id="tab-logs" class="ftr-tab-content">
            <p>History of automated cron runs and manual fetches. Logs older than 30 days are pruned automatically.</p>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px; font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Time</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 25%;">Message</th>
                        <th style="width: 6%;">HTTP</th>
                        <th style="width: 6%;">Lock</th>
                        <th style="width: 8%;">Parsed</th>
                        <th style="width: 8%;">Inserted</th>
                        <th style="width: 8%;">Existing</th>
                        <th style="width: 8%;">Updated</th>
                        <th style="width: 8%;">Failed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty($logs) ) : ?>
                        <tr><td colspan="10">No logs recorded yet.</td></tr>
                    <?php else : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log['run_time'] ); ?></td>
                                <td>
                                    <?php if($log['status'] === 'success'): ?>
                                        <span class="ftr-badge" style="background:#00b67a;">Success</span>
                                    <?php elseif($log['status'] === 'warning'): ?>
                                        <span class="ftr-badge" style="background:#fbbf24; color:#000;">Warning</span>
                                    <?php else: ?>
                                        <span class="ftr-badge" style="background:#d63638;">Error</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;"><?php echo esc_html( $log['message'] ); ?></td>
                                <td><strong><?php echo esc_html( $log['http_status'] ); ?></strong></td>
                                <td><?php echo $log['lock_hit'] ? '⚠️ Yes' : 'No'; ?></td>
                                <td><?php echo esc_html( $log['parsed_count'] ); ?></td>
                                <td><?php echo esc_html( $log['inserted_count'] ); ?></td>
                                <td><?php echo esc_html( $log['existing_count'] ); ?></td>
                                <td><?php echo esc_html( $log['updated_count'] ); ?></td>
                                <td><?php echo esc_html( $log['failed_count'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-help" class="ftr-tab-content">
            <div class="ftr-card">
                <h2>📖 Plugin Architecture & Documentation</h2>
                <p>Welcome to <strong>Free Trustpilot Reviews for WP</strong>. This plugin is built from the ground up for high performance and zero external dependencies.</p>
                <ul style="list-style-type: disc; margin-left: 20px; color: #50575e; line-height: 1.6;">
                    <li><strong>Custom SQL Tables:</strong> All reviews, logs, and settings are stored in isolated <code>wp_ftr_*</code> tables, entirely bypassing standard <code>wp_options</code> bloat.</li>
                    <li><strong>Transient Caching:</strong> Frontend shortcode output is heavily cached using Native WP Transients. When the cron fetches new reviews, the cache is automatically busted.</li>
                    <li><strong>Fetch Locks:</strong> To prevent overlapping crons and database race conditions, a transient lock is generated the moment a fetch begins, securing the SQL transaction.</li>
                    <li><strong>Native Translations:</strong> Because multi-lingual plugins (like TranslatePress) cannot translate text directly into the primary site language, this plugin hooks into a free Google Translate API endpoint to convert English reviews into Turkish automatically <em>during</em> the scrape. You can manually edit these overrides in the "Stored Reviews" tab.</li>
                </ul>
            </div>
            
            <div class="ftr-card">
                <h2>👨‍💻 GitHub & Contributing</h2>
                <p>Found a bug or want to contribute to the project? Visit the official repository below.</p>
                <a href="https://github.com/simeo2/ftr" target="_blank" class="button button-primary">View on GitHub</a>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.ftr-nav-tabs .nav-tab');
                const contents = document.querySelectorAll('.ftr-tab-content');
                let activeTabId = localStorage.getItem('ftr_active_tab') || 'setup';

                // Check if user clicked a link containing a specific #hash (like #help)
                if (window.location.hash) {
                    const hashTab = window.location.hash.replace('#', '');
                    if (document.querySelector(`.nav-tab[data-tab="${hashTab}"]`)) {
                        activeTabId = hashTab;
                    }
                }

                function switchTab(tabId) {
                    tabs.forEach(t => t.classList.remove('nav-tab-active'));
                    contents.forEach(c => c.classList.remove('active'));
                    const selectedTab = document.querySelector(`.nav-tab[data-tab="${tabId}"]`);
                    const selectedContent = document.getElementById(`tab-${tabId}`);
                    if (selectedTab && selectedContent) {
                        selectedTab.classList.add('nav-tab-active');
                        selectedContent.classList.add('active');
                        localStorage.setItem('ftr_active_tab', tabId);
                    }
                }
                switchTab(activeTabId);
                
                tabs.forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Update URL hash for sharing/linking
                        window.history.pushState(null, null, '#' + this.dataset.tab);
                        switchTab(this.dataset.tab);
                    });
                });

                const unlockBtn = document.getElementById('unlock-url-btn');
                const form = document.getElementById('change-url-form');
                if(unlockBtn && form) {
                    unlockBtn.addEventListener('click', () => { unlockBtn.style.display = 'none'; form.style.display = 'block'; });
                    document.getElementById('cancel-url-btn').addEventListener('click', () => { form.style.display = 'none'; unlockBtn.style.display = 'inline-block'; });
                }
            });
        </script>
    <?php endif; ?>
</div>