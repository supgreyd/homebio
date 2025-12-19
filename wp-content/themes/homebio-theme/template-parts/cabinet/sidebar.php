<?php
/**
 * Cabinet Sidebar Template Part
 *
 * Displays the user cabinet sidebar navigation.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
$first_name = get_user_meta($current_user->ID, 'first_name', true);
$favorites_count = homebio_get_favorites_count($current_user->ID);
$notifications_count = homebio_get_unread_notifications_count($current_user->ID);
?>

<aside class="cabinet-sidebar">
    <div class="cabinet-user-info">
        <?php echo homebio_get_avatar($current_user->ID, 80); ?>
        <div class="cabinet-user-name">
            <?php echo esc_html($first_name ?: $current_user->display_name); ?>
        </div>
        <div class="cabinet-user-email">
            <?php echo esc_html($current_user->user_email); ?>
        </div>
    </div>

    <nav class="cabinet-nav">
        <a href="?tab=settings" class="cabinet-nav-item <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
            <?php homebio_icon_e('user', 20); ?>
            <?php esc_html_e('Settings', 'homebio'); ?>
        </a>
        <a href="?tab=security" class="cabinet-nav-item <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
            <?php homebio_icon_e('lock', 20); ?>
            <?php esc_html_e('Security', 'homebio'); ?>
        </a>
        <a href="?tab=favorites" class="cabinet-nav-item <?php echo $active_tab === 'favorites' ? 'active' : ''; ?>">
            <?php homebio_icon_e('heart', 20); ?>
            <?php esc_html_e('Favorites', 'homebio'); ?>
            <?php if ($favorites_count > 0) : ?>
                <span class="cabinet-nav-count"><?php echo intval($favorites_count); ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=notifications" class="cabinet-nav-item <?php echo $active_tab === 'notifications' ? 'active' : ''; ?>">
            <?php homebio_icon_e('bell', 20); ?>
            <?php esc_html_e('Notifications', 'homebio'); ?>
            <?php if ($notifications_count > 0) : ?>
                <span class="cabinet-nav-count"><?php echo intval($notifications_count); ?></span>
            <?php endif; ?>
        </a>
    </nav>

    <div class="cabinet-logout">
        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="cabinet-logout-btn">
            <?php homebio_icon_e('logout', 20); ?>
            <?php esc_html_e('Logout', 'homebio'); ?>
        </a>
    </div>
</aside>
