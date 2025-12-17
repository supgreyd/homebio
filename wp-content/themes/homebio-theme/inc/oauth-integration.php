<?php
/**
 * OAuth Integration
 *
 * Handles Google OAuth integration, user registration, and welcome emails
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Set default role for new social login users
 */
function homebio_set_default_user_role($user_id) {
    $user = new WP_User($user_id);

    // Set subscriber role for new users
    if (empty($user->roles)) {
        $user->set_role('subscriber');
    }
}
add_action('nsl_register_new_user', 'homebio_set_default_user_role');
add_action('user_register', 'homebio_set_default_user_role');

/**
 * Send welcome email to new users
 */
function homebio_send_welcome_email($user_id) {
    $user = get_userdata($user_id);

    if (!$user) {
        return;
    }

    $to = $user->user_email;
    $subject = sprintf(
        /* translators: %s: Site name */
        __('Welcome to %s!', 'homebio'),
        get_bloginfo('name')
    );

    $message = homebio_get_welcome_email_content($user);
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    wp_mail($to, $subject, $message, $headers);
}
add_action('nsl_register_new_user', 'homebio_send_welcome_email', 20);

/**
 * Get welcome email HTML content
 */
function homebio_get_welcome_email_content($user) {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    $cabinet_url = home_url('/user-cabinet');
    $properties_url = get_post_type_archive_link('property');
    $first_name = $user->first_name ?: $user->display_name;

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f8fafc;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="padding: 40px 40px 20px; text-align: center; border-bottom: 1px solid #e2e8f0;">
                                <h1 style="margin: 0; font-size: 24px; color: #2563eb;">
                                    <?php echo esc_html($site_name); ?>
                                </h1>
                            </td>
                        </tr>

                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="margin: 0 0 20px; font-size: 20px; color: #1e293b;">
                                    <?php
                                    printf(
                                        /* translators: %s: User's first name */
                                        esc_html__('Welcome, %s!', 'homebio'),
                                        esc_html($first_name)
                                    );
                                    ?>
                                </h2>

                                <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #475569;">
                                    <?php esc_html_e('Thank you for joining HomeBio. Your account has been created successfully.', 'homebio'); ?>
                                </p>

                                <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #475569;">
                                    <?php esc_html_e('With your account, you can:', 'homebio'); ?>
                                </p>

                                <ul style="margin: 0 0 30px; padding-left: 20px; color: #475569;">
                                    <li style="margin-bottom: 10px;"><?php esc_html_e('Save your favorite properties', 'homebio'); ?></li>
                                    <li style="margin-bottom: 10px;"><?php esc_html_e('Manage your profile and preferences', 'homebio'); ?></li>
                                    <li style="margin-bottom: 10px;"><?php esc_html_e('Get updates on new properties', 'homebio'); ?></li>
                                </ul>

                                <!-- CTA Buttons -->
                                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                            <a href="<?php echo esc_url($properties_url); ?>"
                                               style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500;">
                                                <?php esc_html_e('Browse Properties', 'homebio'); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url($cabinet_url); ?>"
                                               style="display: inline-block; padding: 12px 24px; background-color: #e2e8f0; color: #475569; text-decoration: none; border-radius: 6px; font-weight: 500;">
                                                <?php esc_html_e('My Cabinet', 'homebio'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding: 30px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
                                <p style="margin: 0 0 10px; font-size: 14px; color: #64748b; text-align: center;">
                                    <?php
                                    printf(
                                        /* translators: %s: Site URL */
                                        esc_html__('This email was sent from %s', 'homebio'),
                                        '<a href="' . esc_url($site_url) . '" style="color: #2563eb;">' . esc_html($site_name) . '</a>'
                                    );
                                    ?>
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                                    &copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>.
                                    <?php esc_html_e('All rights reserved.', 'homebio'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Redirect to custom login page instead of wp-login.php
 */
function homebio_redirect_login_page() {
    $login_page = home_url('/login');
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if ($page_viewed === 'wp-login.php' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check for specific actions that should use wp-login.php
        if (isset($_GET['action'])) {
            $allowed_actions = ['logout', 'lostpassword', 'rp', 'resetpass'];
            if (in_array($_GET['action'], $allowed_actions)) {
                return;
            }
        }

        $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';

        if ($redirect_to) {
            wp_redirect(add_query_arg('redirect_to', urlencode($redirect_to), $login_page));
        } else {
            wp_redirect($login_page);
        }
        exit;
    }
}
add_action('init', 'homebio_redirect_login_page');

/**
 * Redirect failed login back to custom login page
 */
function homebio_login_failed() {
    $login_page = home_url('/login');
    wp_redirect($login_page . '?login=failed');
    exit;
}
add_action('wp_login_failed', 'homebio_login_failed');

/**
 * Handle empty login/password
 */
function homebio_verify_login_credentials($user, $username, $password) {
    $login_page = home_url('/login');

    if (empty($username) || empty($password)) {
        wp_redirect($login_page . '?login=empty');
        exit;
    }

    return $user;
}
add_filter('authenticate', 'homebio_verify_login_credentials', 1, 3);

/**
 * Redirect after logout
 */
function homebio_logout_redirect() {
    wp_redirect(home_url());
    exit;
}
add_action('wp_logout', 'homebio_logout_redirect');

/**
 * Store OAuth provider info in user meta
 */
function homebio_store_oauth_provider($user_id, $provider) {
    update_user_meta($user_id, 'oauth_provider', $provider);
    update_user_meta($user_id, 'oauth_registered_at', current_time('mysql'));
}
add_action('nsl_register_new_user', function($user_id) {
    homebio_store_oauth_provider($user_id, 'google');
}, 10);

/**
 * Check if user registered via OAuth
 */
function homebio_is_oauth_user($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    return !empty(get_user_meta($user_id, 'oauth_provider', true));
}

/**
 * Get OAuth provider for user
 */
function homebio_get_oauth_provider($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    return get_user_meta($user_id, 'oauth_provider', true);
}

/**
 * Sync Google profile data on login
 */
function homebio_sync_oauth_profile($user_id, $social_user) {
    // Update user meta with social profile data
    if (isset($social_user->first_name)) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($social_user->first_name));
    }

    if (isset($social_user->last_name)) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($social_user->last_name));
    }

    // Store profile picture URL if available
    if (isset($social_user->profile_picture)) {
        update_user_meta($user_id, 'oauth_profile_picture', esc_url_raw($social_user->profile_picture));
    }
}
// Hook into Nextend Social Login if available
if (function_exists('NSL\Persistent\Storage')) {
    add_action('nsl_login', 'homebio_sync_oauth_profile', 10, 2);
}

/**
 * Add login page body class
 */
function homebio_login_body_class($classes) {
    if (is_page_template('page-login.php')) {
        $classes[] = 'login-page-template';
    }
    if (is_page_template('page-register.php')) {
        $classes[] = 'register-page-template';
    }
    return $classes;
}
add_filter('body_class', 'homebio_login_body_class');

/**
 * Process registration form
 */
function homebio_process_registration($data) {
    $errors = new WP_Error();

    // Sanitize input
    $first_name = isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '';
    $last_name = isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '';
    $email = isset($data['user_email']) ? sanitize_email($data['user_email']) : '';
    $password = isset($data['user_pass']) ? $data['user_pass'] : '';
    $password_confirm = isset($data['user_pass_confirm']) ? $data['user_pass_confirm'] : '';
    $agree_terms = isset($data['agree_terms']) ? (bool) $data['agree_terms'] : false;

    // Validate first name
    if (empty($first_name)) {
        $errors->add('first_name', __('Please enter your first name.', 'homebio'));
    }

    // Validate last name
    if (empty($last_name)) {
        $errors->add('last_name', __('Please enter your last name.', 'homebio'));
    }

    // Validate email
    if (empty($email)) {
        $errors->add('email_empty', __('Please enter your email address.', 'homebio'));
    } elseif (!is_email($email)) {
        $errors->add('email_invalid', __('Please enter a valid email address.', 'homebio'));
    } elseif (email_exists($email)) {
        $errors->add('email_exists', __('This email is already registered. Please sign in instead.', 'homebio'));
    }

    // Validate password
    if (empty($password)) {
        $errors->add('password_empty', __('Please enter a password.', 'homebio'));
    } elseif (strlen($password) < 8) {
        $errors->add('password_short', __('Password must be at least 8 characters.', 'homebio'));
    } elseif ($password !== $password_confirm) {
        $errors->add('password_mismatch', __('Passwords do not match.', 'homebio'));
    }

    // Validate terms agreement
    if (!$agree_terms) {
        $errors->add('terms', __('You must agree to the Terms of Service and Privacy Policy.', 'homebio'));
    }

    // Return errors if any
    if ($errors->has_errors()) {
        return $errors;
    }

    // Create username from email
    $username = sanitize_user(current(explode('@', $email)), true);

    // Ensure username is unique
    $base_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $base_username . $counter;
        $counter++;
    }

    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    // Update user meta
    wp_update_user([
        'ID'           => $user_id,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => $first_name . ' ' . $last_name,
    ]);

    // Set user role
    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    // Store registration method
    update_user_meta($user_id, 'registration_method', 'email');
    update_user_meta($user_id, 'registered_at', current_time('mysql'));

    // Send welcome email
    homebio_send_welcome_email($user_id);

    // Send WordPress new user notification to admin
    wp_new_user_notification($user_id, null, 'admin');

    return $user_id;
}

/**
 * Enable user registration via settings
 * This can be set in Settings > General > Membership
 */
function homebio_check_registration_enabled() {
    // You can force enable registration here if needed
    // add_filter('option_users_can_register', '__return_true');
}
add_action('init', 'homebio_check_registration_enabled');

/**
 * Redirect WordPress registration to custom page
 */
function homebio_redirect_registration_page() {
    $register_page = home_url('/register');
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php?action=register') !== false) {
        wp_redirect($register_page);
        exit;
    }
}
add_action('init', 'homebio_redirect_registration_page');
