<?php
/**
 * Custom Post Types Registration
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Property custom post type
 */
function homebio_register_property_post_type() {
    $labels = [
        'name'                  => _x('Properties', 'Post type general name', 'homebio'),
        'singular_name'         => _x('Property', 'Post type singular name', 'homebio'),
        'menu_name'             => _x('Properties', 'Admin Menu text', 'homebio'),
        'name_admin_bar'        => _x('Property', 'Add New on Toolbar', 'homebio'),
        'add_new'               => __('Add New', 'homebio'),
        'add_new_item'          => __('Add New Property', 'homebio'),
        'new_item'              => __('New Property', 'homebio'),
        'edit_item'             => __('Edit Property', 'homebio'),
        'view_item'             => __('View Property', 'homebio'),
        'all_items'             => __('All Properties', 'homebio'),
        'search_items'          => __('Search Properties', 'homebio'),
        'parent_item_colon'     => __('Parent Properties:', 'homebio'),
        'not_found'             => __('No properties found.', 'homebio'),
        'not_found_in_trash'    => __('No properties found in Trash.', 'homebio'),
        'featured_image'        => _x('Property Image', 'Overrides the "Featured Image" phrase', 'homebio'),
        'set_featured_image'    => _x('Set property image', 'Overrides the "Set featured image" phrase', 'homebio'),
        'remove_featured_image' => _x('Remove property image', 'Overrides the "Remove featured image" phrase', 'homebio'),
        'use_featured_image'    => _x('Use as property image', 'Overrides the "Use as featured image" phrase', 'homebio'),
        'archives'              => _x('Property archives', 'The post type archive label', 'homebio'),
        'insert_into_item'      => _x('Insert into property', 'Overrides the "Insert into post" phrase', 'homebio'),
        'uploaded_to_this_item' => _x('Uploaded to this property', 'Overrides the "Uploaded to this post" phrase', 'homebio'),
        'filter_items_list'     => _x('Filter properties list', 'Screen reader text', 'homebio'),
        'items_list_navigation' => _x('Properties list navigation', 'Screen reader text', 'homebio'),
        'items_list'            => _x('Properties list', 'Screen reader text', 'homebio'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'properties', 'with_front' => false],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-building',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'       => true,
    ];

    register_post_type('property', $args);
}
add_action('init', 'homebio_register_property_post_type');

/**
 * Register Property Category taxonomy
 */
function homebio_register_property_taxonomies() {
    // Property Type (Apartment, House, Commercial, etc.)
    $type_labels = [
        'name'              => _x('Property Types', 'taxonomy general name', 'homebio'),
        'singular_name'     => _x('Property Type', 'taxonomy singular name', 'homebio'),
        'search_items'      => __('Search Property Types', 'homebio'),
        'all_items'         => __('All Property Types', 'homebio'),
        'parent_item'       => __('Parent Property Type', 'homebio'),
        'parent_item_colon' => __('Parent Property Type:', 'homebio'),
        'edit_item'         => __('Edit Property Type', 'homebio'),
        'update_item'       => __('Update Property Type', 'homebio'),
        'add_new_item'      => __('Add New Property Type', 'homebio'),
        'new_item_name'     => __('New Property Type Name', 'homebio'),
        'menu_name'         => __('Property Types', 'homebio'),
    ];

    register_taxonomy('property_type', ['property'], [
        'hierarchical'      => true,
        'labels'            => $type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'property-type'],
        'show_in_rest'      => true,
    ]);

    // Location taxonomy
    $location_labels = [
        'name'              => _x('Locations', 'taxonomy general name', 'homebio'),
        'singular_name'     => _x('Location', 'taxonomy singular name', 'homebio'),
        'search_items'      => __('Search Locations', 'homebio'),
        'all_items'         => __('All Locations', 'homebio'),
        'parent_item'       => __('Parent Location', 'homebio'),
        'parent_item_colon' => __('Parent Location:', 'homebio'),
        'edit_item'         => __('Edit Location', 'homebio'),
        'update_item'       => __('Update Location', 'homebio'),
        'add_new_item'      => __('Add New Location', 'homebio'),
        'new_item_name'     => __('New Location Name', 'homebio'),
        'menu_name'         => __('Locations', 'homebio'),
    ];

    register_taxonomy('property_location', ['property'], [
        'hierarchical'      => true,
        'labels'            => $location_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'location'],
        'show_in_rest'      => true,
    ]);
}
add_action('init', 'homebio_register_property_taxonomies');

/**
 * Register custom meta fields for properties
 * Note: For more complex fields, use ACF plugin
 */
function homebio_register_property_meta() {
    $meta_fields = [
        'property_price' => [
            'type'         => 'number',
            'description'  => 'Property price',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_address' => [
            'type'         => 'string',
            'description'  => 'Property address',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_area' => [
            'type'         => 'number',
            'description'  => 'Property area in square meters',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_rooms' => [
            'type'         => 'integer',
            'description'  => 'Number of rooms',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_bedrooms' => [
            'type'         => 'integer',
            'description'  => 'Number of bedrooms',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_bathrooms' => [
            'type'         => 'integer',
            'description'  => 'Number of bathrooms',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_year_built' => [
            'type'         => 'integer',
            'description'  => 'Year the property was built',
            'single'       => true,
            'show_in_rest' => true,
        ],
        'property_gallery' => [
            'type'         => 'array',
            'description'  => 'Property image gallery IDs',
            'single'       => true,
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'integer'],
                ],
            ],
        ],
    ];

    foreach ($meta_fields as $key => $args) {
        register_post_meta('property', $key, $args);
    }
}
add_action('init', 'homebio_register_property_meta');

/**
 * Add custom meta boxes for property details
 */
function homebio_add_property_meta_boxes() {
    add_meta_box(
        'property_details',
        __('Property Details', 'homebio'),
        'homebio_property_details_callback',
        'property',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'homebio_add_property_meta_boxes');

/**
 * Property details meta box callback
 */
function homebio_property_details_callback($post) {
    wp_nonce_field('homebio_property_details', 'homebio_property_details_nonce');

    $price     = get_post_meta($post->ID, 'property_price', true);
    $address   = get_post_meta($post->ID, 'property_address', true);
    $area      = get_post_meta($post->ID, 'property_area', true);
    $rooms     = get_post_meta($post->ID, 'property_rooms', true);
    $bedrooms  = get_post_meta($post->ID, 'property_bedrooms', true);
    $bathrooms = get_post_meta($post->ID, 'property_bathrooms', true);
    $year      = get_post_meta($post->ID, 'property_year_built', true);
    ?>
    <style>
        .property-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .property-meta-field { margin-bottom: 10px; }
        .property-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; }
        .property-meta-field input { width: 100%; }
    </style>
    <div class="property-meta-grid">
        <div class="property-meta-field">
            <label for="property_price"><?php esc_html_e('Price', 'homebio'); ?></label>
            <input type="number" id="property_price" name="property_price"
                   value="<?php echo esc_attr($price); ?>" step="0.01">
        </div>
        <div class="property-meta-field">
            <label for="property_address"><?php esc_html_e('Address', 'homebio'); ?></label>
            <input type="text" id="property_address" name="property_address"
                   value="<?php echo esc_attr($address); ?>">
        </div>
        <div class="property-meta-field">
            <label for="property_area"><?php esc_html_e('Area (m²)', 'homebio'); ?></label>
            <input type="number" id="property_area" name="property_area"
                   value="<?php echo esc_attr($area); ?>" step="0.01">
        </div>
        <div class="property-meta-field">
            <label for="property_rooms"><?php esc_html_e('Rooms', 'homebio'); ?></label>
            <input type="number" id="property_rooms" name="property_rooms"
                   value="<?php echo esc_attr($rooms); ?>" min="0">
        </div>
        <div class="property-meta-field">
            <label for="property_bedrooms"><?php esc_html_e('Bedrooms', 'homebio'); ?></label>
            <input type="number" id="property_bedrooms" name="property_bedrooms"
                   value="<?php echo esc_attr($bedrooms); ?>" min="0">
        </div>
        <div class="property-meta-field">
            <label for="property_bathrooms"><?php esc_html_e('Bathrooms', 'homebio'); ?></label>
            <input type="number" id="property_bathrooms" name="property_bathrooms"
                   value="<?php echo esc_attr($bathrooms); ?>" min="0">
        </div>
        <div class="property-meta-field">
            <label for="property_year_built"><?php esc_html_e('Year Built', 'homebio'); ?></label>
            <input type="number" id="property_year_built" name="property_year_built"
                   value="<?php echo esc_attr($year); ?>" min="1800" max="<?php echo date('Y'); ?>">
        </div>
    </div>
    <?php
}

/**
 * Save property meta data
 */
function homebio_save_property_meta($post_id) {
    // Verify nonce
    if (!isset($_POST['homebio_property_details_nonce']) ||
        !wp_verify_nonce($_POST['homebio_property_details_nonce'], 'homebio_property_details')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save fields
    $fields = [
        'property_price',
        'property_address',
        'property_area',
        'property_rooms',
        'property_bedrooms',
        'property_bathrooms',
        'property_year_built',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
}
add_action('save_post_property', 'homebio_save_property_meta');

/**
 * Flush rewrite rules on theme activation
 */
function homebio_rewrite_flush() {
    homebio_register_property_post_type();
    homebio_register_property_taxonomies();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'homebio_rewrite_flush');
