<?php
/**
 * HomeBio Theme Functions
 *
 * @package HomeBio
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define theme constants
 */
define('HOMEBIO_VERSION', '1.0.0');
define('HOMEBIO_DIR', get_template_directory());
define('HOMEBIO_URI', get_template_directory_uri());

/**
 * Theme setup
 */
function homebio_setup() {
    // Make theme available for translation
    load_theme_textdomain('homebio', HOMEBIO_DIR . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add custom image sizes for properties
    add_image_size('property-card', 600, 400, true);
    add_image_size('property-gallery', 1200, 800, true);

    // Register navigation menus
    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'homebio'),
        'footer'  => esc_html__('Footer Menu', 'homebio'),
    ]);

    // Switch default core markup to output valid HTML5
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // Add support for custom logo
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Add support for wide alignment
    add_theme_support('align-wide');
}
add_action('after_setup_theme', 'homebio_setup');

/**
 * Enqueue scripts and styles
 */
function homebio_scripts() {
    // Main stylesheet
    wp_enqueue_style(
        'homebio-style',
        get_stylesheet_uri(),
        [],
        HOMEBIO_VERSION
    );

    // Google Fonts - Inter
    wp_enqueue_style(
        'homebio-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        [],
        null
    );

    // Main JavaScript
    wp_enqueue_script(
        'homebio-main',
        HOMEBIO_URI . '/assets/js/main.js',
        [],
        HOMEBIO_VERSION,
        true
    );

    // Localize script with AJAX URL and nonce
    wp_localize_script('homebio-main', 'homebioAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('homebio_nonce'),
        'strings' => [
            'addedToFavorites'   => esc_html__('Added to favorites', 'homebio'),
            'removedFromFavorites' => esc_html__('Removed from favorites', 'homebio'),
            'loginRequired'      => esc_html__('Please log in to save favorites', 'homebio'),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'homebio_scripts');

/**
 * Register widget areas
 */
function homebio_widgets_init() {
    register_sidebar([
        'name'          => esc_html__('Sidebar', 'homebio'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'homebio'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => esc_html__('Footer', 'homebio'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Footer widget area.', 'homebio'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'homebio_widgets_init');

/**
 * Include additional functionality
 */
require_once HOMEBIO_DIR . '/inc/custom-post-types.php';
require_once HOMEBIO_DIR . '/inc/favorites.php';
require_once HOMEBIO_DIR . '/inc/user-cabinet.php';
require_once HOMEBIO_DIR . '/inc/oauth-integration.php';

/**
 * Custom excerpt length
 */
function homebio_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'homebio_excerpt_length');

/**
 * Custom excerpt more
 */
function homebio_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'homebio_excerpt_more');
