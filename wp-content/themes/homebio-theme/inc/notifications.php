<?php
/**
 * HomeBio Notifications System
 *
 * Handles notifications for property changes in user favorites
 *
 * @package HomeBio
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user notifications
 *
 * @param int $user_id User ID
 * @param bool $unread_only Only get unread notifications
 * @return array Notifications array
 */
function homebio_get_user_notifications($user_id, $unread_only = false) {
    $notifications = get_user_meta($user_id, 'homebio_notifications', true);

    if (!is_array($notifications)) {
        return [];
    }

    // Sort by date, newest first
    usort($notifications, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    if ($unread_only) {
        $notifications = array_filter($notifications, function($notification) {
            return empty($notification['read']);
        });
    }

    return $notifications;
}

/**
 * Get unread notifications count
 *
 * @param int $user_id User ID
 * @return int Count of unread notifications
 */
function homebio_get_unread_notifications_count($user_id) {
    $notifications = homebio_get_user_notifications($user_id, true);
    return count($notifications);
}

/**
 * Add notification for a user
 *
 * @param int $user_id User ID
 * @param array $notification Notification data
 * @return bool Success
 */
function homebio_add_notification($user_id, $notification) {
    $notifications = get_user_meta($user_id, 'homebio_notifications', true);

    if (!is_array($notifications)) {
        $notifications = [];
    }

    // Add unique ID and timestamp
    $notification['id'] = uniqid('notif_');
    $notification['date'] = current_time('mysql');
    $notification['read'] = false;

    // Add to beginning of array
    array_unshift($notifications, $notification);

    // Keep only last 50 notifications
    $notifications = array_slice($notifications, 0, 50);

    return update_user_meta($user_id, 'homebio_notifications', $notifications);
}

/**
 * Mark notification as read
 *
 * @param int $user_id User ID
 * @param string $notification_id Notification ID
 * @return bool Success
 */
function homebio_mark_notification_read($user_id, $notification_id) {
    $notifications = get_user_meta($user_id, 'homebio_notifications', true);

    if (!is_array($notifications)) {
        return false;
    }

    foreach ($notifications as &$notification) {
        if ($notification['id'] === $notification_id) {
            $notification['read'] = true;
            break;
        }
    }

    return update_user_meta($user_id, 'homebio_notifications', $notifications);
}

/**
 * Mark all notifications as read
 *
 * @param int $user_id User ID
 * @return bool Success
 */
function homebio_mark_all_notifications_read($user_id) {
    $notifications = get_user_meta($user_id, 'homebio_notifications', true);

    if (!is_array($notifications)) {
        return false;
    }

    foreach ($notifications as &$notification) {
        $notification['read'] = true;
    }

    return update_user_meta($user_id, 'homebio_notifications', $notifications);
}

/**
 * Delete a notification
 *
 * @param int $user_id User ID
 * @param string $notification_id Notification ID
 * @return bool Success
 */
function homebio_delete_notification($user_id, $notification_id) {
    $notifications = get_user_meta($user_id, 'homebio_notifications', true);

    if (!is_array($notifications)) {
        return false;
    }

    $notifications = array_filter($notifications, function($notification) use ($notification_id) {
        return $notification['id'] !== $notification_id;
    });

    return update_user_meta($user_id, 'homebio_notifications', array_values($notifications));
}

/**
 * Delete all notifications
 *
 * @param int $user_id User ID
 * @return bool Success
 */
function homebio_delete_all_notifications($user_id) {
    return delete_user_meta($user_id, 'homebio_notifications');
}

/**
 * Get users who have a property in favorites
 *
 * @param int $property_id Property ID
 * @return array User IDs
 */
function homebio_get_users_with_favorite($property_id) {
    global $wpdb;

    // Favorites are stored as serialized arrays with meta key 'favorite_properties'
    // Format: a:2:{i:0;i:123;i:1;i:456;} where 123, 456 are property IDs
    $user_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta}
         WHERE meta_key = 'favorite_properties'
         AND meta_value LIKE %s",
        '%i:' . intval($property_id) . ';%'
    ));

    return array_unique($user_ids);
}

/**
 * Store original property meta before any updates
 * This runs on admin_init to capture the original state before save
 */
function homebio_capture_property_meta_on_edit() {
    // Only on post edit screen
    if (!is_admin()) {
        return;
    }

    // Check if we're editing a post
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    if (!$post_id) {
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0;
    }

    if (!$post_id) {
        return;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'property') {
        return;
    }

    // Store the original data (always refresh on page load)
    $original_data = [
        'title' => $post->post_title,
        'price' => get_post_meta($post_id, '_property_price', true),
        'area' => get_post_meta($post_id, '_property_area', true),
        'bedrooms' => get_post_meta($post_id, '_property_bedrooms', true),
        'bathrooms' => get_post_meta($post_id, '_property_bathrooms', true),
        'address' => get_post_meta($post_id, '_property_address', true),
        'status' => $post->post_status,
    ];

    set_transient('homebio_property_original_' . $post_id, $original_data, 300);
}
add_action('admin_init', 'homebio_capture_property_meta_on_edit');

/**
 * Detect property changes and create notifications
 * Using shutdown to ensure all meta has been saved
 */
function homebio_detect_property_changes($post_id, $post, $update) {
    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Only for property post type
    if ($post->post_type !== 'property') {
        return;
    }

    // Only for updates, not new posts
    if (!$update) {
        return;
    }

    // Only for published properties
    if ($post->post_status !== 'publish') {
        return;
    }

    // Schedule the comparison to run after all meta is saved
    // Store post_id in a global to process on shutdown
    global $homebio_properties_to_check;
    if (!isset($homebio_properties_to_check)) {
        $homebio_properties_to_check = [];
    }
    $homebio_properties_to_check[$post_id] = $post;
}
add_action('save_post_property', 'homebio_detect_property_changes', 999, 3);

/**
 * Process property changes on shutdown (after all meta saved)
 */
function homebio_process_property_changes_on_shutdown() {
    global $homebio_properties_to_check;

    if (empty($homebio_properties_to_check)) {
        return;
    }

    foreach ($homebio_properties_to_check as $post_id => $post) {
        homebio_compare_and_notify($post_id, $post);
    }
}
add_action('shutdown', 'homebio_process_property_changes_on_shutdown', 1);

/**
 * Compare property data and create notifications
 */
function homebio_compare_and_notify($post_id, $post) {
    // Get original data from transient
    $original_data = get_transient('homebio_property_original_' . $post_id);

    // Delete the transient
    delete_transient('homebio_property_original_' . $post_id);

    if (!$original_data) {
        return;
    }

    // Get current data (fresh from database)
    $current_data = [
        'title' => $post->post_title,
        'price' => get_post_meta($post_id, '_property_price', true),
        'area' => get_post_meta($post_id, '_property_area', true),
        'bedrooms' => get_post_meta($post_id, '_property_bedrooms', true),
        'bathrooms' => get_post_meta($post_id, '_property_bathrooms', true),
        'address' => get_post_meta($post_id, '_property_address', true),
        'status' => $post->post_status,
    ];

    // Detect changes
    $changes = [];

    if ($original_data['price'] != $current_data['price']) {
        $old_price = homebio_format_price($original_data['price']);
        $new_price = homebio_format_price($current_data['price']);
        $changes[] = [
            'type' => 'price',
            'label' => __('Price', 'homebio'),
            'old' => $old_price,
            'new' => $new_price,
        ];
    }

    if ($original_data['area'] != $current_data['area']) {
        $changes[] = [
            'type' => 'area',
            'label' => __('Area', 'homebio'),
            'old' => $original_data['area'] . ' m²',
            'new' => $current_data['area'] . ' m²',
        ];
    }

    if ($original_data['bedrooms'] != $current_data['bedrooms']) {
        $changes[] = [
            'type' => 'bedrooms',
            'label' => __('Bedrooms', 'homebio'),
            'old' => $original_data['bedrooms'],
            'new' => $current_data['bedrooms'],
        ];
    }

    if ($original_data['bathrooms'] != $current_data['bathrooms']) {
        $changes[] = [
            'type' => 'bathrooms',
            'label' => __('Bathrooms', 'homebio'),
            'old' => $original_data['bathrooms'],
            'new' => $current_data['bathrooms'],
        ];
    }

    if ($original_data['address'] != $current_data['address']) {
        $changes[] = [
            'type' => 'address',
            'label' => __('Address', 'homebio'),
            'old' => $original_data['address'],
            'new' => $current_data['address'],
        ];
    }

    // If no relevant changes, return
    if (empty($changes)) {
        return;
    }

    // Get users who have this property in favorites
    $user_ids = homebio_get_users_with_favorite($post_id);

    if (empty($user_ids)) {
        return;
    }

    // Create notification for each user
    foreach ($user_ids as $user_id) {
        $notification = [
            'type' => 'property_update',
            'property_id' => $post_id,
            'property_title' => $post->post_title,
            'property_url' => get_permalink($post_id),
            'changes' => $changes,
        ];

        homebio_add_notification($user_id, $notification);

        // Send email notification
        homebio_send_notification_email($user_id, $notification);
    }
}

/**
 * Format price for display
 */
function homebio_format_price($price) {
    if (empty($price)) {
        return '-';
    }
    return '€' . number_format($price, 0, ',', ' ');
}

/**
 * Send email notification
 *
 * @param int $user_id User ID
 * @param array $notification Notification data
 */
function homebio_send_notification_email($user_id, $notification) {
    $user = get_user_by('id', $user_id);

    if (!$user || empty($user->user_email)) {
        return;
    }

    // Check if user has email notifications enabled (default: enabled)
    $email_enabled = get_user_meta($user_id, 'homebio_email_notifications', true);
    if ($email_enabled === 'disabled') {
        return;
    }

    $site_name = get_bloginfo('name');
    $subject = sprintf(
        /* translators: 1: Site name, 2: Property title */
        __('[%1$s] Property Updated: %2$s', 'homebio'),
        $site_name,
        $notification['property_title']
    );

    // Build email body
    $message = sprintf(
        /* translators: %s: User first name */
        __('Hello %s,', 'homebio'),
        $user->first_name ?: $user->display_name
    ) . "\n\n";

    $message .= sprintf(
        /* translators: %s: Property title */
        __('A property in your favorites has been updated: %s', 'homebio'),
        $notification['property_title']
    ) . "\n\n";

    $message .= __('Changes:', 'homebio') . "\n";

    foreach ($notification['changes'] as $change) {
        $message .= sprintf(
            "• %s: %s → %s\n",
            $change['label'],
            $change['old'],
            $change['new']
        );
    }

    $message .= "\n" . sprintf(
        /* translators: %s: Property URL */
        __('View property: %s', 'homebio'),
        $notification['property_url']
    ) . "\n\n";

    $message .= sprintf(
        /* translators: %s: Cabinet URL */
        __('Manage your notifications: %s', 'homebio'),
        homebio_get_profile_url() . '?tab=notifications'
    ) . "\n\n";

    $message .= __('Best regards,', 'homebio') . "\n";
    $message .= $site_name;

    // Set email headers
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
    ];

    wp_mail($user->user_email, $subject, $message, $headers);
}

/**
 * AJAX: Mark notification as read
 */
function homebio_ajax_mark_notification_read() {
    check_ajax_referer('homebio_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $notification_id = sanitize_text_field($_POST['notification_id'] ?? '');

    if (empty($notification_id)) {
        wp_send_json_error(['message' => __('Invalid notification', 'homebio')]);
    }

    $user_id = get_current_user_id();
    $result = homebio_mark_notification_read($user_id, $notification_id);

    if ($result) {
        wp_send_json_success([
            'message' => __('Notification marked as read', 'homebio'),
            'unread_count' => homebio_get_unread_notifications_count($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => __('Could not update notification', 'homebio')]);
    }
}
add_action('wp_ajax_homebio_mark_notification_read', 'homebio_ajax_mark_notification_read');

/**
 * AJAX: Mark all notifications as read
 */
function homebio_ajax_mark_all_notifications_read() {
    check_ajax_referer('homebio_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $user_id = get_current_user_id();
    $result = homebio_mark_all_notifications_read($user_id);

    if ($result !== false) {
        wp_send_json_success([
            'message' => __('All notifications marked as read', 'homebio'),
            'unread_count' => 0,
        ]);
    } else {
        wp_send_json_error(['message' => __('Could not update notifications', 'homebio')]);
    }
}
add_action('wp_ajax_homebio_mark_all_notifications_read', 'homebio_ajax_mark_all_notifications_read');

/**
 * AJAX: Delete notification
 */
function homebio_ajax_delete_notification() {
    check_ajax_referer('homebio_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $notification_id = sanitize_text_field($_POST['notification_id'] ?? '');

    if (empty($notification_id)) {
        wp_send_json_error(['message' => __('Invalid notification', 'homebio')]);
    }

    $user_id = get_current_user_id();
    $result = homebio_delete_notification($user_id, $notification_id);

    if ($result) {
        wp_send_json_success([
            'message' => __('Notification deleted', 'homebio'),
            'unread_count' => homebio_get_unread_notifications_count($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => __('Could not delete notification', 'homebio')]);
    }
}
add_action('wp_ajax_homebio_delete_notification', 'homebio_ajax_delete_notification');

/**
 * AJAX: Delete all notifications
 */
function homebio_ajax_delete_all_notifications() {
    check_ajax_referer('homebio_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $user_id = get_current_user_id();
    $result = homebio_delete_all_notifications($user_id);

    wp_send_json_success([
        'message' => __('All notifications deleted', 'homebio'),
        'unread_count' => 0,
    ]);
}
add_action('wp_ajax_homebio_delete_all_notifications', 'homebio_ajax_delete_all_notifications');

/**
 * AJAX: Toggle email notifications
 */
function homebio_ajax_toggle_email_notifications() {
    check_ajax_referer('homebio_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in', 'homebio')]);
    }

    $enabled = sanitize_text_field($_POST['enabled'] ?? 'enabled');
    $user_id = get_current_user_id();

    update_user_meta($user_id, 'homebio_email_notifications', $enabled);

    wp_send_json_success([
        'message' => $enabled === 'enabled'
            ? __('Email notifications enabled', 'homebio')
            : __('Email notifications disabled', 'homebio'),
        'enabled' => $enabled === 'enabled',
    ]);
}
add_action('wp_ajax_homebio_toggle_email_notifications', 'homebio_ajax_toggle_email_notifications');

/**
 * Get notification icon based on change type
 */
function homebio_get_notification_icon($type) {
    $icons = [
        'price' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'area' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>',
        'bedrooms' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v11m0-4h18m0 4V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2"></path></svg>',
        'bathrooms' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path><line x1="10" y1="5" x2="8" y2="7"></line><line x1="2" y1="12" x2="22" y2="12"></line></svg>',
        'address' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
    ];

    return $icons[$type] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
}

/**
 * Format notification date
 */
function homebio_format_notification_date($date) {
    $timestamp = strtotime($date);
    $now = current_time('timestamp');
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return __('Just now', 'homebio');
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return sprintf(
            /* translators: %d: Number of minutes */
            _n('%d minute ago', '%d minutes ago', $minutes, 'homebio'),
            $minutes
        );
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return sprintf(
            /* translators: %d: Number of hours */
            _n('%d hour ago', '%d hours ago', $hours, 'homebio'),
            $hours
        );
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return sprintf(
            /* translators: %d: Number of days */
            _n('%d day ago', '%d days ago', $days, 'homebio'),
            $days
        );
    } else {
        return date_i18n(get_option('date_format'), $timestamp);
    }
}
