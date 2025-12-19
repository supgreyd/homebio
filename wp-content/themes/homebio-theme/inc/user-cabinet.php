<?php
/**
 * User Cabinet Functionality
 *
 * Handles user profile management, settings updates, avatar upload,
 * and account operations via AJAX.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the cabinet URL
 *
 * @return string Cabinet page URL
 */
function homebio_get_cabinet_url() {
    return home_url('/user-cabinet/');
}

/**
 * Custom login redirect
 *
 * Redirects users to appropriate location after login.
 * Admins go to wp-admin, regular users go to the cabinet.
 *
 * @param string $redirect_to Default redirect URL
 * @param string $request     Requested redirect URL
 * @param WP_User|WP_Error $user User object or error
 * @return string Redirect URL
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
        return homebio_get_cabinet_url();
    }

    return $redirect_to;
}
add_filter('login_redirect', 'homebio_login_redirect', 10, 3);

/**
 * Save user language preference
 *
 * @param int    $user_id  User ID
 * @param string $language Language code
 */
function homebio_save_user_language($user_id, $language) {
    update_user_meta($user_id, 'preferred_language', sanitize_text_field($language));
}

/**
 * Get user language preference
 *
 * @param int|null $user_id User ID (defaults to current user)
 * @return string Language code
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
 *
 * @param string  $user_login Username
 * @param WP_User $user       User object
 */
function homebio_apply_user_language($user_login, $user) {
    $language = homebio_get_user_language($user->ID);

    if ($language && function_exists('switch_to_locale')) {
        switch_to_locale($language);
    }
}
add_action('wp_login', 'homebio_apply_user_language', 10, 2);

/**
 * AJAX handler for updating user settings
 */
function homebio_ajax_update_settings() {
    // Use helper for nonce verification and login check
    $user_id = homebio_verify_ajax_request('homebio_nonce', true, ['nonce', 'cabinet_nonce']);

    // Update user meta fields
    $fields = ['first_name', 'last_name', 'phone', 'birth_date'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Update display name
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    if ($first_name || $last_name) {
        wp_update_user([
            'ID'           => $user_id,
            'display_name' => trim($first_name . ' ' . $last_name),
        ]);
    }

    homebio_ajax_success(__('Settings saved successfully', 'homebio'));
}
add_action('wp_ajax_update_settings', 'homebio_ajax_update_settings');

/**
 * AJAX handler for changing password
 *
 * Includes rate limiting to prevent brute force attempts.
 */
function homebio_ajax_change_password() {
    // Use helper for nonce verification and login check
    $user_id = homebio_verify_ajax_request('homebio_nonce', true, ['nonce', 'password_nonce']);

    // Check rate limiting
    if (!homebio_check_rate_limit('password_change', $user_id, 5, 15)) {
        homebio_ajax_error(__('Too many attempts. Please try again in 15 minutes.', 'homebio'));
    }

    $user = wp_get_current_user();
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Verify current password
    if (!wp_check_password($current_password, $user->user_pass, $user->ID)) {
        homebio_increment_rate_limit('password_change', $user_id, 15);
        homebio_ajax_error(__('Current password is incorrect', 'homebio'));
    }

    // Validate new password length
    if (strlen($new_password) < 8) {
        homebio_ajax_error(__('New password must be at least 8 characters', 'homebio'));
    }

    // Confirm passwords match
    if ($new_password !== $confirm_password) {
        homebio_ajax_error(__('Passwords do not match', 'homebio'));
    }

    // Update password
    wp_set_password($new_password, $user->ID);

    // Re-authenticate user
    wp_set_auth_cookie($user->ID);

    // Reset rate limit on successful change
    homebio_reset_rate_limit('password_change', $user_id);

    homebio_ajax_success(__('Password changed successfully', 'homebio'));
}
add_action('wp_ajax_change_password', 'homebio_ajax_change_password');

/**
 * AJAX handler for deleting account
 */
function homebio_ajax_delete_account() {
    $user_id = homebio_verify_ajax_request();
    $user = get_userdata($user_id);

    // Don't allow admins to delete themselves this way
    if (in_array('administrator', $user->roles)) {
        homebio_ajax_error(__('Administrators cannot delete their account this way', 'homebio'));
    }

    // Clean up user data
    delete_user_meta($user_id, 'homebio_favorites');
    delete_user_meta($user_id, 'homebio_notifications');
    delete_user_meta($user_id, 'homebio_custom_avatar');
    delete_user_meta($user_id, 'homebio_custom_avatar_id');

    // Delete user
    require_once(ABSPATH . 'wp-admin/includes/user.php');
    $deleted = wp_delete_user($user_id);

    if ($deleted) {
        homebio_ajax_success(__('Account deleted successfully', 'homebio'), [
            'redirect' => home_url(),
        ]);
    } else {
        homebio_ajax_error(__('Failed to delete account', 'homebio'));
    }
}
add_action('wp_ajax_delete_account', 'homebio_ajax_delete_account');

/**
 * AJAX handler for removing favorite from cabinet
 */
function homebio_ajax_remove_favorite() {
    $user_id = homebio_verify_ajax_request();
    $property_id = homebio_get_post_int('property_id');

    if (!$property_id) {
        homebio_ajax_error(__('Invalid property', 'homebio'));
    }

    // Use the existing helper function to remove favorite
    homebio_remove_favorite($property_id, $user_id);

    // Get updated count
    $count = homebio_get_favorites_count($user_id);

    homebio_ajax_success(__('Removed from favorites', 'homebio'), [
        'count' => $count,
    ]);
}
add_action('wp_ajax_remove_favorite', 'homebio_ajax_remove_favorite');

/**
 * AJAX handler for avatar upload
 */
function homebio_ajax_upload_avatar() {
    $user_id = homebio_verify_ajax_request();

    // Check for file upload errors
    if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $error_message = __('No file uploaded', 'homebio');
        if (!empty($_FILES['avatar']['error'])) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => __('File exceeds server limit', 'homebio'),
                UPLOAD_ERR_FORM_SIZE  => __('File exceeds form limit', 'homebio'),
                UPLOAD_ERR_PARTIAL    => __('File only partially uploaded', 'homebio'),
                UPLOAD_ERR_NO_FILE    => __('No file was uploaded', 'homebio'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing temp folder', 'homebio'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file', 'homebio'),
            ];
            $error_code = $_FILES['avatar']['error'];
            if (isset($upload_errors[$error_code])) {
                $error_message = $upload_errors[$error_code];
            }
        }
        homebio_ajax_error($error_message);
    }

    $file = $_FILES['avatar'];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        homebio_ajax_error(__('Invalid file type. Please upload JPG, PNG, GIF, or WebP.', 'homebio'));
    }

    // Validate file size (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        homebio_ajax_error(__('File too large. Maximum size is 2MB.', 'homebio'));
    }

    // Load WordPress media functions
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    // Delete old avatar if exists
    $old_avatar_id = get_user_meta($user_id, 'homebio_custom_avatar_id', true);
    if ($old_avatar_id) {
        wp_delete_attachment($old_avatar_id, true);
    }

    // Upload file
    $upload_overrides = [
        'test_form' => false,
        'test_type' => true,
        'mimes'     => [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
        ],
    ];

    $uploaded_file = wp_handle_upload($file, $upload_overrides);

    if (isset($uploaded_file['error'])) {
        homebio_ajax_error($uploaded_file['error']);
    }

    // Create attachment
    $attachment = [
        'post_mime_type' => $uploaded_file['type'],
        'post_title'     => sanitize_file_name(pathinfo($uploaded_file['file'], PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_author'    => $user_id,
    ];

    $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);

    if (is_wp_error($attachment_id)) {
        homebio_ajax_error($attachment_id->get_error_message());
    }

    // Generate attachment metadata
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
    wp_update_attachment_metadata($attachment_id, $attachment_data);

    // Get the attachment URL
    $avatar_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
    if (!$avatar_url) {
        $avatar_url = $uploaded_file['url'];
    }

    // Save avatar meta
    update_user_meta($user_id, 'homebio_custom_avatar', $avatar_url);
    update_user_meta($user_id, 'homebio_custom_avatar_id', $attachment_id);
    update_user_meta($user_id, 'homebio_avatar_updated', time());

    // Clear user meta cache
    wp_cache_delete($user_id, 'user_meta');
    clean_user_cache($user_id);

    homebio_ajax_success(__('Avatar updated successfully', 'homebio'), [
        'avatar_url'    => $avatar_url,
        'attachment_id' => $attachment_id,
    ]);
}
add_action('wp_ajax_upload_avatar', 'homebio_ajax_upload_avatar');

/**
 * AJAX handler for removing avatar
 */
function homebio_ajax_remove_avatar() {
    $user_id = homebio_verify_ajax_request();

    // Delete avatar attachment
    $avatar_id = get_user_meta($user_id, 'homebio_custom_avatar_id', true);
    if ($avatar_id) {
        wp_delete_attachment($avatar_id, true);
    }

    // Remove meta
    delete_user_meta($user_id, 'homebio_custom_avatar');
    delete_user_meta($user_id, 'homebio_custom_avatar_id');
    delete_user_meta($user_id, 'homebio_avatar_updated');

    // Get default gravatar URL
    $default_avatar = get_avatar_url($user_id, ['size' => 120]);

    homebio_ajax_success(__('Avatar removed', 'homebio'), [
        'avatar_url' => $default_avatar,
    ]);
}
add_action('wp_ajax_remove_avatar', 'homebio_ajax_remove_avatar');

/**
 * Get user ID from various input types
 *
 * @param mixed $id_or_email User ID, email, or object
 * @return int|null User ID or null
 */
function homebio_get_user_id_from_input($id_or_email) {
    $user_id = null;

    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif ($id_or_email instanceof WP_User) {
        $user_id = $id_or_email->ID;
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user_id = (int) $id_or_email->user_id;
        } elseif (!empty($id_or_email->ID)) {
            $user_id = (int) $id_or_email->ID;
        } elseif (!empty($id_or_email->comment_author_email)) {
            $user = get_user_by('email', $id_or_email->comment_author_email);
            if ($user) {
                $user_id = $user->ID;
            }
        }
    } elseif (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        if ($user) {
            $user_id = $user->ID;
        }
    }

    return $user_id;
}

/**
 * Get avatar HTML for user
 *
 * Direct function that bypasses WordPress filters.
 * Use this as fallback if get_avatar() doesn't work correctly.
 *
 * @param int    $user_id User ID
 * @param int    $size    Avatar size in pixels
 * @param string $alt     Alt text
 * @return string Avatar HTML
 */
function homebio_get_avatar($user_id, $size = 96, $alt = '') {
    $custom_url = homebio_get_custom_avatar_url($user_id);

    if ($custom_url) {
        return sprintf(
            '<img alt="%s" src="%s" class="avatar avatar-%d photo" height="%d" width="%d" loading="lazy" />',
            esc_attr($alt),
            esc_url($custom_url),
            (int) $size,
            (int) $size,
            (int) $size
        );
    }

    // Fall back to WordPress default
    return get_avatar($user_id, $size, '', $alt);
}

/**
 * Get custom avatar URL for user
 *
 * @param int $user_id User ID
 * @return string|false Avatar URL or false
 */
function homebio_get_custom_avatar_url($user_id) {
    if (!$user_id) {
        return false;
    }

    $avatar_id = get_user_meta($user_id, 'homebio_custom_avatar_id', true);

    if (empty($avatar_id)) {
        return false;
    }

    $avatar_id = absint($avatar_id);

    if ($avatar_id <= 0 || get_post_type($avatar_id) !== 'attachment') {
        return false;
    }

    // Try thumbnail first, then full size, then stored URL
    $url = wp_get_attachment_image_url($avatar_id, 'thumbnail')
        ?: wp_get_attachment_url($avatar_id)
        ?: get_user_meta($user_id, 'homebio_custom_avatar', true);

    // Add cache buster
    if ($url) {
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $url .= $separator . 'v=' . get_user_meta($user_id, 'homebio_avatar_updated', true);
    }

    return $url;
}

/**
 * Filter avatar data before avatar is generated
 *
 * Priority 1 ensures this runs before other plugins.
 *
 * @param array $args        Avatar arguments
 * @param mixed $id_or_email User identifier
 * @return array Modified arguments
 */
function homebio_pre_get_avatar_data($args, $id_or_email) {
    // Prevent recursion
    static $is_checking = false;
    if ($is_checking) {
        return $args;
    }
    $is_checking = true;

    $user_id = homebio_get_user_id_from_input($id_or_email);

    if ($user_id) {
        $custom_url = homebio_get_custom_avatar_url($user_id);

        if ($custom_url) {
            $args['url'] = $custom_url;
            $args['found_avatar'] = true;
        }
    }

    $is_checking = false;
    return $args;
}
add_filter('pre_get_avatar_data', 'homebio_pre_get_avatar_data', 1, 2);

/**
 * Override default avatar with custom uploaded avatar (backup filter)
 *
 * @param string $avatar      Default avatar HTML
 * @param mixed  $id_or_email User identifier
 * @param int    $size        Avatar size
 * @param string $default     Default avatar URL
 * @param string $alt         Alt text
 * @param array  $args        Additional arguments
 * @return string Avatar HTML
 */
function homebio_custom_avatar($avatar, $id_or_email, $size, $default, $alt, $args) {
    $user_id = homebio_get_user_id_from_input($id_or_email);

    if (!$user_id) {
        return $avatar;
    }

    $custom_avatar_url = homebio_get_custom_avatar_url($user_id);

    if ($custom_avatar_url) {
        $class = isset($args['class']) ? $args['class'] : 'avatar';
        if (is_array($class)) {
            $class = implode(' ', $class);
        }
        $avatar = sprintf(
            '<img alt="%s" src="%s" class="%s" height="%d" width="%d" loading="lazy" />',
            esc_attr($alt),
            esc_url($custom_avatar_url),
            esc_attr($class . ' avatar-' . $size . ' photo'),
            (int) $size,
            (int) $size
        );
    }

    return $avatar;
}
add_filter('get_avatar', 'homebio_custom_avatar', 99, 6);
