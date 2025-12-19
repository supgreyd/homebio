<?php
/**
 * AJAX Helper Functions
 *
 * Provides reusable functions for AJAX request handling
 * to reduce code duplication across AJAX handlers.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify AJAX request and optionally check login status
 *
 * This function consolidates the common pattern of verifying nonces
 * and checking login status that appears in most AJAX handlers.
 *
 * @param string $nonce_action The nonce action to verify against (default: 'homebio_nonce')
 * @param bool   $require_login Whether to require user to be logged in (default: true)
 * @param array  $nonce_keys Array of possible POST keys containing the nonce
 * @return int|false Returns user ID on success, false if login not required and user not logged in
 */
function homebio_verify_ajax_request($nonce_action = 'homebio_nonce', $require_login = true, $nonce_keys = ['nonce', 'cabinet_nonce', 'password_nonce']) {
    // Try to find nonce in various POST keys
    $nonce = '';
    foreach ($nonce_keys as $key) {
        if (isset($_POST[$key]) && !empty($_POST[$key])) {
            $nonce = $_POST[$key];
            break;
        }
    }

    // Verify nonce
    if (empty($nonce) || !wp_verify_nonce($nonce, $nonce_action)) {
        wp_send_json_error(['message' => __('Security check failed', 'homebio')]);
    }

    // Check login if required
    if ($require_login && !is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    return is_user_logged_in() ? get_current_user_id() : false;
}

/**
 * Send a standardized success response
 *
 * @param string $message Success message
 * @param array  $data Additional data to include in response
 */
function homebio_ajax_success($message, $data = []) {
    $response = array_merge(['message' => $message], $data);
    wp_send_json_success($response);
}

/**
 * Send a standardized error response
 *
 * @param string $message Error message
 * @param array  $data Additional data to include in response
 */
function homebio_ajax_error($message, $data = []) {
    $response = array_merge(['message' => $message], $data);
    wp_send_json_error($response);
}

/**
 * Get and validate an integer from POST data
 *
 * @param string $key The POST key to retrieve
 * @param int    $default Default value if not set
 * @return int
 */
function homebio_get_post_int($key, $default = 0) {
    return isset($_POST[$key]) ? intval($_POST[$key]) : $default;
}

/**
 * Get and sanitize a string from POST data
 *
 * @param string $key The POST key to retrieve
 * @param string $default Default value if not set
 * @return string
 */
function homebio_get_post_string($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $default;
}

/**
 * Check rate limiting for sensitive operations
 *
 * @param string $action The action being rate limited (e.g., 'password_change')
 * @param int    $user_id User ID to check
 * @param int    $max_attempts Maximum attempts allowed
 * @param int    $window_minutes Time window in minutes
 * @return bool True if within limit, false if exceeded
 */
function homebio_check_rate_limit($action, $user_id, $max_attempts = 5, $window_minutes = 15) {
    $transient_key = "homebio_{$action}_{$user_id}";
    $attempts = get_transient($transient_key);

    if ($attempts !== false && $attempts >= $max_attempts) {
        return false;
    }

    return true;
}

/**
 * Increment rate limit counter
 *
 * @param string $action The action being rate limited
 * @param int    $user_id User ID
 * @param int    $window_minutes Time window in minutes
 */
function homebio_increment_rate_limit($action, $user_id, $window_minutes = 15) {
    $transient_key = "homebio_{$action}_{$user_id}";
    $attempts = get_transient($transient_key);

    if ($attempts === false) {
        $attempts = 0;
    }

    set_transient($transient_key, $attempts + 1, $window_minutes * MINUTE_IN_SECONDS);
}

/**
 * Reset rate limit counter (e.g., after successful operation)
 *
 * @param string $action The action
 * @param int    $user_id User ID
 */
function homebio_reset_rate_limit($action, $user_id) {
    $transient_key = "homebio_{$action}_{$user_id}";
    delete_transient($transient_key);
}

/**
 * Log debug information (only when WP_DEBUG is enabled)
 *
 * @param string $message Log message
 * @param mixed  $data Optional data to log
 */
function homebio_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $log_message = '[HomeBio] ' . $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}
