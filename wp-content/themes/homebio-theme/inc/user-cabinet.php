<?php
/**
 * User Cabinet Functionality
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Restrict cabinet access to logged-in users
 */
function homebio_restrict_cabinet_access() {
    // Get cabinet page slug
    $cabinet_slugs = ['user-cabinet', 'my-cabinet', 'cabinet'];

    if (is_page($cabinet_slugs) && !is_user_logged_in()) {
        $redirect_url = add_query_arg(
            'redirect_to',
            urlencode($_SERVER['REQUEST_URI']),
            home_url('/login')
        );
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('template_redirect', 'homebio_restrict_cabinet_access');

/**
 * Redirect logged-in users away from login page
 */
function homebio_redirect_logged_in_users() {
    if (is_page('login') && is_user_logged_in()) {
        wp_redirect(home_url('/user-cabinet'));
        exit;
    }
}
add_action('template_redirect', 'homebio_redirect_logged_in_users');

/**
 * Custom login redirect
 */
function homebio_login_redirect($redirect_to, $request, $user) {
    // Check if redirect_to is set in the request
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
        return esc_url_raw($_GET['redirect_to']);
    }

    // Default redirect for users
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        }
        return home_url('/user-cabinet');
    }

    return $redirect_to;
}
add_filter('login_redirect', 'homebio_login_redirect', 10, 3);

/**
 * Save user language preference
 */
function homebio_save_user_language($user_id, $language) {
    update_user_meta($user_id, 'preferred_language', sanitize_text_field($language));
}

/**
 * Get user language preference
 */
function homebio_get_user_language($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return get_locale();
    }

    $language = get_user_meta($user_id, 'preferred_language', true);
    return $language ? $language : get_locale();
}

/**
 * Apply user language preference on login
 */
function homebio_apply_user_language($user_login, $user) {
    $language = homebio_get_user_language($user->ID);

    if ($language && function_exists('switch_to_locale')) {
        switch_to_locale($language);
    }
}
add_action('wp_login', 'homebio_apply_user_language', 10, 2);

/**
 * Language switcher output
 */
function homebio_language_switcher() {
    // This will be populated when WPML or Polylang is installed
    // For now, output a placeholder

    $languages = [
        'en_US' => 'EN',
        'bg_BG' => 'BG',
        'ru_RU' => 'RU',
        'uk'    => 'UA',
    ];

    $current_lang = get_locale();
    ?>
    <div class="language-switcher">
        <select id="language-selector" class="language-select">
            <?php foreach ($languages as $code => $label) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($current_lang, $code); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * AJAX handler for updating user profile
 */
function homebio_ajax_update_profile() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'homebio_nonce')) {
        wp_send_json_error(['message' => __('Security check failed', 'homebio')]);
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $user_id = get_current_user_id();
    $errors = [];

    // Update first name
    if (isset($_POST['first_name'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        update_user_meta($user_id, 'first_name', $first_name);
    }

    // Update last name
    if (isset($_POST['last_name'])) {
        $last_name = sanitize_text_field($_POST['last_name']);
        update_user_meta($user_id, 'last_name', $last_name);
    }

    // Update phone
    if (isset($_POST['phone'])) {
        $phone = sanitize_text_field($_POST['phone']);
        update_user_meta($user_id, 'phone', $phone);
    }

    // Update language preference
    if (isset($_POST['language'])) {
        homebio_save_user_language($user_id, $_POST['language']);
    }

    if (empty($errors)) {
        wp_send_json_success(['message' => __('Profile updated successfully', 'homebio')]);
    } else {
        wp_send_json_error(['message' => implode(', ', $errors)]);
    }
}
add_action('wp_ajax_update_profile', 'homebio_ajax_update_profile');

/**
 * Get cabinet navigation items
 */
function homebio_get_cabinet_nav() {
    return [
        'profile' => [
            'label' => __('Personal Info', 'homebio'),
            'icon'  => 'dashicons-admin-users',
            'slug'  => 'profile',
        ],
        'security' => [
            'label' => __('Security', 'homebio'),
            'icon'  => 'dashicons-shield',
            'slug'  => 'security',
        ],
        'language' => [
            'label' => __('Language', 'homebio'),
            'icon'  => 'dashicons-translation',
            'slug'  => 'language',
        ],
        'favorites' => [
            'label' => __('Favorites', 'homebio'),
            'icon'  => 'dashicons-heart',
            'slug'  => 'favorites',
            'count' => homebio_get_favorites_count(),
        ],
    ];
}

/**
 * User cabinet shortcode
 */
function homebio_cabinet_shortcode($atts) {
    if (!is_user_logged_in()) {
        return sprintf(
            '<p>%s <a href="%s">%s</a></p>',
            esc_html__('Please', 'homebio'),
            esc_url(home_url('/login')),
            esc_html__('log in', 'homebio')
        );
    }

    $current_user = wp_get_current_user();
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
    $nav_items = homebio_get_cabinet_nav();

    ob_start();
    ?>
    <div class="user-cabinet">
        <nav class="cabinet-nav">
            <ul>
                <?php foreach ($nav_items as $key => $item) : ?>
                    <li class="<?php echo $active_tab === $item['slug'] ? 'active' : ''; ?>">
                        <a href="?tab=<?php echo esc_attr($item['slug']); ?>">
                            <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                            <?php echo esc_html($item['label']); ?>
                            <?php if (isset($item['count']) && $item['count'] > 0) : ?>
                                <span class="count"><?php echo intval($item['count']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="cabinet-content">
            <?php
            switch ($active_tab) {
                case 'security':
                    homebio_cabinet_security_tab($current_user);
                    break;
                case 'language':
                    homebio_cabinet_language_tab($current_user);
                    break;
                case 'favorites':
                    homebio_cabinet_favorites_tab($current_user);
                    break;
                default:
                    homebio_cabinet_profile_tab($current_user);
                    break;
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('user_cabinet', 'homebio_cabinet_shortcode');

/**
 * Profile tab content
 */
function homebio_cabinet_profile_tab($user) {
    ?>
    <h2><?php esc_html_e('Personal Information', 'homebio'); ?></h2>
    <form id="profile-form" class="cabinet-form">
        <div class="form-group">
            <label for="first_name"><?php esc_html_e('First Name', 'homebio'); ?></label>
            <input type="text" id="first_name" name="first_name"
                   value="<?php echo esc_attr($user->first_name); ?>">
        </div>
        <div class="form-group">
            <label for="last_name"><?php esc_html_e('Last Name', 'homebio'); ?></label>
            <input type="text" id="last_name" name="last_name"
                   value="<?php echo esc_attr($user->last_name); ?>">
        </div>
        <div class="form-group">
            <label for="email"><?php esc_html_e('Email', 'homebio'); ?></label>
            <input type="email" id="email" name="email"
                   value="<?php echo esc_attr($user->user_email); ?>" readonly>
            <small><?php esc_html_e('Email is managed by your Google account', 'homebio'); ?></small>
        </div>
        <div class="form-group">
            <label for="phone"><?php esc_html_e('Phone Number', 'homebio'); ?></label>
            <input type="tel" id="phone" name="phone"
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            <?php esc_html_e('Save Changes', 'homebio'); ?>
        </button>
    </form>
    <?php
}

/**
 * Security tab content
 */
function homebio_cabinet_security_tab($user) {
    ?>
    <h2><?php esc_html_e('Security Settings', 'homebio'); ?></h2>
    <div class="security-section">
        <h3><?php esc_html_e('Connected Accounts', 'homebio'); ?></h3>
        <div class="connected-account">
            <span class="account-icon google"></span>
            <span class="account-info">
                <strong>Google</strong>
                <span><?php echo esc_html($user->user_email); ?></span>
            </span>
            <span class="account-status connected">
                <?php esc_html_e('Connected', 'homebio'); ?>
            </span>
        </div>
    </div>
    <div class="security-section">
        <h3><?php esc_html_e('Delete Account', 'homebio'); ?></h3>
        <p><?php esc_html_e('Once you delete your account, there is no going back. Please be certain.', 'homebio'); ?></p>
        <button class="btn btn-danger" id="delete-account-btn">
            <?php esc_html_e('Delete Account', 'homebio'); ?>
        </button>
    </div>
    <?php
}

/**
 * Language tab content
 */
function homebio_cabinet_language_tab($user) {
    $current_language = homebio_get_user_language($user->ID);
    $languages = [
        'en_US' => __('English', 'homebio'),
        'bg_BG' => __('Bulgarian', 'homebio'),
        'ru_RU' => __('Russian', 'homebio'),
        'uk'    => __('Ukrainian', 'homebio'),
    ];
    ?>
    <h2><?php esc_html_e('Language Preferences', 'homebio'); ?></h2>
    <form id="language-form" class="cabinet-form">
        <div class="form-group">
            <label for="language"><?php esc_html_e('Preferred Language', 'homebio'); ?></label>
            <select id="language" name="language">
                <?php foreach ($languages as $code => $label) : ?>
                    <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <?php esc_html_e('Save Preference', 'homebio'); ?>
        </button>
    </form>
    <?php
}

/**
 * Favorites tab content
 */
function homebio_cabinet_favorites_tab($user) {
    $favorites_query = homebio_get_favorite_properties($user->ID);
    ?>
    <h2><?php esc_html_e('My Favorites', 'homebio'); ?></h2>

    <?php if ($favorites_query->have_posts()) : ?>
        <div class="properties-grid">
            <?php while ($favorites_query->have_posts()) : $favorites_query->the_post(); ?>
                <?php get_template_part('template-parts/property', 'card'); ?>
            <?php endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="no-favorites">
            <p><?php esc_html_e('You haven\'t saved any properties yet.', 'homebio'); ?></p>
            <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn btn-primary">
                <?php esc_html_e('Browse Properties', 'homebio'); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php
}
