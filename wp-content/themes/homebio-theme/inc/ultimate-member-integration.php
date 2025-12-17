<?php
/**
 * Ultimate Member Integration
 *
 * Customizes Ultimate Member for HomeBio theme
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if Ultimate Member is active
 */
function homebio_is_um_active() {
    return class_exists('UM') && function_exists('UM');
}

/**
 * Add custom profile tabs
 */
function homebio_um_add_profile_tabs($tabs) {
    $tabs['favorites'] = [
        'name'   => __('Favorites', 'homebio'),
        'icon'   => 'um-faicon-heart',
        'custom' => true,
    ];

    $tabs['language'] = [
        'name'   => __('Language', 'homebio'),
        'icon'   => 'um-faicon-globe',
        'custom' => true,
    ];

    return $tabs;
}
add_filter('um_profile_tabs', 'homebio_um_add_profile_tabs', 1000);

/**
 * Enable custom tabs
 */
function homebio_um_enabled_profile_tabs($tabs) {
    $tabs['favorites'] = true;
    $tabs['language'] = true;
    return $tabs;
}
add_filter('um_user_profile_tabs', 'homebio_um_enabled_profile_tabs', 1000);

/**
 * Favorites tab content
 */
function homebio_um_profile_content_favorites($args) {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = um_profile_id();
    $current_user_id = get_current_user_id();

    // Only show favorites to the profile owner
    if ($user_id !== $current_user_id) {
        echo '<div class="um-profile-note">';
        echo '<p>' . esc_html__('Favorites are private.', 'homebio') . '</p>';
        echo '</div>';
        return;
    }

    $favorites_query = homebio_get_favorite_properties($user_id);
    ?>
    <div class="um-favorites-tab">
        <div class="um-favorites-header">
            <h3><?php esc_html_e('My Favorite Properties', 'homebio'); ?></h3>
            <p><?php esc_html_e('Properties you have saved for later.', 'homebio'); ?></p>
        </div>

        <?php if ($favorites_query->have_posts()) : ?>
            <div class="um-favorites-grid properties-grid">
                <?php
                while ($favorites_query->have_posts()) :
                    $favorites_query->the_post();
                    get_template_part('template-parts/property', 'card');
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <div class="um-favorites-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <h4><?php esc_html_e('No favorites yet', 'homebio'); ?></h4>
                <p><?php esc_html_e('Start browsing properties and click the heart icon to save your favorites.', 'homebio'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="um-button">
                    <?php esc_html_e('Browse Properties', 'homebio'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action('um_profile_content_favorites', 'homebio_um_profile_content_favorites');

/**
 * Language tab content
 */
function homebio_um_profile_content_language($args) {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = um_profile_id();
    $current_user_id = get_current_user_id();

    // Only allow the profile owner to change language
    if ($user_id !== $current_user_id) {
        echo '<div class="um-profile-note">';
        echo '<p>' . esc_html__('Language settings are private.', 'homebio') . '</p>';
        echo '</div>';
        return;
    }

    $current_language = homebio_get_user_language($user_id);
    $languages = [
        'en_US' => __('English', 'homebio'),
        'bg_BG' => __('Bulgarian (Български)', 'homebio'),
        'ru_RU' => __('Russian (Русский)', 'homebio'),
        'uk'    => __('Ukrainian (Українська)', 'homebio'),
    ];

    // Handle form submission
    if (isset($_POST['homebio_language_nonce']) && wp_verify_nonce($_POST['homebio_language_nonce'], 'homebio_save_language')) {
        if (isset($_POST['preferred_language'])) {
            $new_language = sanitize_text_field($_POST['preferred_language']);
            if (array_key_exists($new_language, $languages)) {
                homebio_save_user_language($user_id, $new_language);
                $current_language = $new_language;
                echo '<div class="um-field-success">' . esc_html__('Language preference saved!', 'homebio') . '</div>';
            }
        }
    }
    ?>
    <div class="um-language-tab">
        <div class="um-language-header">
            <h3><?php esc_html_e('Language Preferences', 'homebio'); ?></h3>
            <p><?php esc_html_e('Choose your preferred language for the website.', 'homebio'); ?></p>
        </div>

        <form method="post" class="um-language-form">
            <?php wp_nonce_field('homebio_save_language', 'homebio_language_nonce'); ?>

            <div class="um-language-options">
                <?php foreach ($languages as $code => $label) : ?>
                    <label class="um-language-option <?php echo $current_language === $code ? 'selected' : ''; ?>">
                        <input type="radio" name="preferred_language" value="<?php echo esc_attr($code); ?>"
                               <?php checked($current_language, $code); ?>>
                        <span class="um-language-label">
                            <span class="um-language-flag"><?php echo homebio_get_language_flag($code); ?></span>
                            <span class="um-language-name"><?php echo esc_html($label); ?></span>
                        </span>
                        <?php if ($current_language === $code) : ?>
                            <span class="um-language-current"><?php esc_html_e('Current', 'homebio'); ?></span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="um-language-submit">
                <button type="submit" class="um-button">
                    <?php esc_html_e('Save Language', 'homebio'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php
}
add_action('um_profile_content_language', 'homebio_um_profile_content_language');

/**
 * Get language flag emoji
 */
function homebio_get_language_flag($locale) {
    $flags = [
        'en_US' => '🇺🇸',
        'bg_BG' => '🇧🇬',
        'ru_RU' => '🇷🇺',
        'uk'    => '🇺🇦',
    ];

    return isset($flags[$locale]) ? $flags[$locale] : '🌐';
}

/**
 * Add custom fields to Ultimate Member registration form
 */
function homebio_um_add_registration_fields($fields) {
    // Phone field
    $fields['phone'] = [
        'title'       => __('Phone Number', 'homebio'),
        'metakey'     => 'phone',
        'type'        => 'text',
        'label'       => __('Phone Number', 'homebio'),
        'required'    => 0,
        'public'      => 1,
        'editable'    => 1,
        'validate'    => 'phone_number',
        'icon'        => 'um-faicon-phone',
    ];

    return $fields;
}
add_filter('um_predefined_fields_hook', 'homebio_um_add_registration_fields');

/**
 * Customize Ultimate Member profile menu
 */
function homebio_um_profile_menu_order($tabs) {
    // Reorder tabs
    $new_order = [];

    // Define preferred order
    $order = ['main', 'favorites', 'language'];

    foreach ($order as $key) {
        if (isset($tabs[$key])) {
            $new_order[$key] = $tabs[$key];
        }
    }

    // Add remaining tabs
    foreach ($tabs as $key => $tab) {
        if (!isset($new_order[$key])) {
            $new_order[$key] = $tab;
        }
    }

    return $new_order;
}
add_filter('um_profile_tabs', 'homebio_um_profile_menu_order', 2000);

/**
 * Add favorites count to profile tab
 */
function homebio_um_profile_tab_favorites_count($args) {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = um_profile_id();
    $count = homebio_get_favorites_count($user_id);

    if ($count > 0) {
        echo '<span class="um-tab-count">' . intval($count) . '</span>';
    }
}
add_action('um_profile_tab_favorites', 'homebio_um_profile_tab_favorites_count');

/**
 * Redirect Ultimate Member pages to our custom pages
 * Only runs if UM pages still exist
 */
function homebio_um_redirect_core_pages() {
    if (!homebio_is_um_active()) {
        return;
    }

    // Only redirect if UM core pages exist and we're on them
    // Skip if we're already on our custom pages
    $current_url = home_url($_SERVER['REQUEST_URI']);
    $login_url = home_url('/login');
    $register_url = home_url('/register');

    // Don't redirect if already on custom pages
    if (strpos($current_url, '/login') !== false || strpos($current_url, '/register') !== false) {
        return;
    }

    // Check if um_is_core_page function exists and pages exist
    if (function_exists('um_is_core_page')) {
        // Redirect UM login to our custom login
        if (um_is_core_page('login') && !is_user_logged_in()) {
            $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';

            if ($redirect_to) {
                $login_url = add_query_arg('redirect_to', urlencode($redirect_to), $login_url);
            }

            wp_redirect($login_url);
            exit;
        }

        // Redirect UM register to our custom register
        if (um_is_core_page('register') && !is_user_logged_in()) {
            wp_redirect($register_url);
            exit;
        }
    }
}
add_action('template_redirect', 'homebio_um_redirect_core_pages', 1);

/**
 * Override UM login URL
 */
function homebio_um_login_url($url) {
    return home_url('/login');
}
add_filter('um_login_url', 'homebio_um_login_url');

/**
 * Override UM register URL
 */
function homebio_um_register_url($url) {
    return home_url('/register');
}
add_filter('um_register_url', 'homebio_um_register_url');

/**
 * Override UM logout redirect
 */
function homebio_um_logout_redirect_url($url) {
    return home_url('/');
}
add_filter('um_logout_redirect_url', 'homebio_um_logout_redirect_url');

/**
 * After login redirect to profile or intended page
 */
function homebio_um_login_redirect($redirect_to, $status) {
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
        return esc_url_raw($_GET['redirect_to']);
    }

    // Default: redirect to user profile
    return um_user_profile_url();
}
add_filter('um_login_redirect_url', 'homebio_um_login_redirect', 10, 2);

/**
 * After registration redirect
 */
function homebio_um_registration_redirect($redirect_to, $status, $user_id) {
    // Redirect to profile after registration
    return um_user_profile_url();
}
add_filter('um_registration_redirect_url', 'homebio_um_registration_redirect', 10, 3);

/**
 * Style Ultimate Member forms
 */
function homebio_um_add_custom_class($classes) {
    $classes .= ' homebio-um-form';
    return $classes;
}
add_filter('um_form_official_classes', 'homebio_um_add_custom_class');

/**
 * Add security section content
 */
function homebio_um_account_tab_security_content() {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();
    $oauth_provider = homebio_get_oauth_provider($user_id);
    ?>
    <div class="um-security-section">
        <h4><?php esc_html_e('Connected Accounts', 'homebio'); ?></h4>

        <div class="um-connected-accounts">
            <?php if ($oauth_provider === 'google') : ?>
                <div class="um-connected-account connected">
                    <span class="um-account-icon google">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </span>
                    <span class="um-account-info">
                        <strong>Google</strong>
                        <span class="um-account-status"><?php esc_html_e('Connected', 'homebio'); ?></span>
                    </span>
                </div>
            <?php else : ?>
                <div class="um-connected-account not-connected">
                    <span class="um-account-icon google">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#ccc" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#ccc" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#ccc" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#ccc" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </span>
                    <span class="um-account-info">
                        <strong>Google</strong>
                        <span class="um-account-status not-connected"><?php esc_html_e('Not connected', 'homebio'); ?></span>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Add delete account functionality
 */
function homebio_um_account_delete_content() {
    if (!is_user_logged_in()) {
        return;
    }
    ?>
    <div class="um-delete-account-section">
        <h4><?php esc_html_e('Delete Account', 'homebio'); ?></h4>
        <p class="um-delete-warning">
            <?php esc_html_e('Once you delete your account, all your data including favorites will be permanently removed. This action cannot be undone.', 'homebio'); ?>
        </p>
        <button type="button" class="um-button um-button-danger" id="homebio-delete-account-btn">
            <?php esc_html_e('Delete My Account', 'homebio'); ?>
        </button>
    </div>

    <div id="homebio-delete-modal" class="um-modal" style="display: none;">
        <div class="um-modal-content">
            <h4><?php esc_html_e('Are you sure?', 'homebio'); ?></h4>
            <p><?php esc_html_e('This will permanently delete your account and all associated data.', 'homebio'); ?></p>
            <div class="um-modal-actions">
                <button type="button" class="um-button um-button-secondary" id="homebio-cancel-delete">
                    <?php esc_html_e('Cancel', 'homebio'); ?>
                </button>
                <button type="button" class="um-button um-button-danger" id="homebio-confirm-delete">
                    <?php esc_html_e('Yes, Delete My Account', 'homebio'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

// Note: Delete account and cabinet URL functions moved to user-cabinet.php
