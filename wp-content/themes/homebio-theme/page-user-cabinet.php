<?php
/**
 * Template Name: User Cabinet
 *
 * Custom user profile/cabinet page with sidebar navigation.
 * This template uses modular template parts for better maintainability.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect non-logged-in users to login
if (!is_user_logged_in()) {
    $redirect_url = add_query_arg(
        'redirect_to',
        urlencode(get_permalink()),
        home_url('/login')
    );
    wp_redirect($redirect_url);
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';

// Validate tab name
$valid_tabs = ['settings', 'security', 'favorites', 'notifications'];
if (!in_array($active_tab, $valid_tabs)) {
    $active_tab = 'settings';
}

get_header();
?>

<main id="primary" class="site-main">
    <div class="cabinet-page">
        <div class="container">
            <div class="cabinet-layout">
                <?php get_template_part('template-parts/cabinet/sidebar'); ?>

                <div class="cabinet-content">
                    <?php get_template_part('template-parts/cabinet/tab', $active_tab); ?>
                </div>
            </div>
        </div>
    </div>

    <?php get_template_part('template-parts/cabinet/modal-delete-account'); ?>
</main>

<?php
get_footer();
