<?php
/**
 * Favorites Functionality
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user's favorite properties
 */
function homebio_get_user_favorites($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return [];
    }

    $favorites = get_user_meta($user_id, 'favorite_properties', true);
    return is_array($favorites) ? $favorites : [];
}

/**
 * Check if a property is in user's favorites
 */
function homebio_is_favorite($property_id, $user_id = null) {
    $favorites = homebio_get_user_favorites($user_id);
    return in_array($property_id, $favorites);
}

/**
 * Add property to favorites
 */
function homebio_add_favorite($property_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $favorites = homebio_get_user_favorites($user_id);

    if (!in_array($property_id, $favorites)) {
        $favorites[] = $property_id;
        update_user_meta($user_id, 'favorite_properties', $favorites);
        return true;
    }

    return false;
}

/**
 * Remove property from favorites
 */
function homebio_remove_favorite($property_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $favorites = homebio_get_user_favorites($user_id);
    $key = array_search($property_id, $favorites);

    if ($key !== false) {
        unset($favorites[$key]);
        $favorites = array_values($favorites); // Re-index array
        update_user_meta($user_id, 'favorite_properties', $favorites);
        return true;
    }

    return false;
}

/**
 * Toggle favorite status
 */
function homebio_toggle_favorite($property_id, $user_id = null) {
    if (homebio_is_favorite($property_id, $user_id)) {
        homebio_remove_favorite($property_id, $user_id);
        return false; // No longer a favorite
    } else {
        homebio_add_favorite($property_id, $user_id);
        return true; // Now a favorite
    }
}

/**
 * AJAX handler for toggling favorites
 */
function homebio_ajax_toggle_favorite() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'homebio_nonce')) {
        wp_send_json_error(['message' => __('Security check failed', 'homebio')]);
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message'      => __('Please log in to save favorites', 'homebio'),
            'login_required' => true,
        ]);
    }

    // Get property ID
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;

    if (!$property_id) {
        wp_send_json_error(['message' => __('Invalid property', 'homebio')]);
    }

    // Verify property exists
    $property = get_post($property_id);
    if (!$property || $property->post_type !== 'property') {
        wp_send_json_error(['message' => __('Property not found', 'homebio')]);
    }

    // Toggle favorite
    $is_favorite = homebio_toggle_favorite($property_id);

    wp_send_json_success([
        'is_favorite' => $is_favorite,
        'message'     => $is_favorite
            ? __('Added to favorites', 'homebio')
            : __('Removed from favorites', 'homebio'),
    ]);
}
add_action('wp_ajax_toggle_favorite', 'homebio_ajax_toggle_favorite');
add_action('wp_ajax_nopriv_toggle_favorite', 'homebio_ajax_toggle_favorite');

/**
 * Get favorite button HTML
 */
function homebio_favorite_button($property_id = null, $echo = true) {
    if (!$property_id) {
        $property_id = get_the_ID();
    }

    $is_favorite = is_user_logged_in() && homebio_is_favorite($property_id);
    $class = $is_favorite ? 'property-card__favorite is-active' : 'property-card__favorite';
    $label = $is_favorite
        ? __('Remove from favorites', 'homebio')
        : __('Add to favorites', 'homebio');

    $html = sprintf(
        '<button class="%s" data-property-id="%d" aria-label="%s" title="%s">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="%s" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </button>',
        esc_attr($class),
        intval($property_id),
        esc_attr($label),
        esc_attr($label),
        $is_favorite ? 'currentColor' : 'none'
    );

    if ($echo) {
        echo $html;
    }

    return $html;
}

/**
 * Get user's favorite properties count
 */
function homebio_get_favorites_count($user_id = null) {
    $favorites = homebio_get_user_favorites($user_id);
    return count($favorites);
}

/**
 * Query favorite properties
 */
function homebio_get_favorite_properties($user_id = null, $args = []) {
    $favorites = homebio_get_user_favorites($user_id);

    if (empty($favorites)) {
        return new WP_Query();
    }

    $default_args = [
        'post_type'      => 'property',
        'post__in'       => $favorites,
        'posts_per_page' => -1,
        'orderby'        => 'post__in',
    ];

    $query_args = wp_parse_args($args, $default_args);

    return new WP_Query($query_args);
}
