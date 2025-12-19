<?php
/**
 * Cabinet Notifications Tab Template Part
 *
 * Displays user notifications about favorite property updates.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$notifications = homebio_get_user_notifications($current_user->ID);
$unread_count = homebio_get_unread_notifications_count($current_user->ID);
$email_enabled = get_user_meta($current_user->ID, 'homebio_email_notifications', true);
$is_email_enabled = $email_enabled !== 'disabled';
?>

<div class="cabinet-section">
    <div class="notifications-header">
        <div>
            <h1><?php esc_html_e('Notifications', 'homebio'); ?></h1>
            <p class="cabinet-section-desc"><?php esc_html_e('Updates about your favorite properties', 'homebio'); ?></p>
        </div>
        <?php if (!empty($notifications)) : ?>
            <div class="notifications-actions">
                <?php if ($unread_count > 0) : ?>
                    <button type="button" class="btn btn-secondary btn-sm" id="mark-all-read-btn">
                        <?php esc_html_e('Mark all as read', 'homebio'); ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline btn-sm" id="delete-all-notifications-btn">
                    <?php esc_html_e('Clear all', 'homebio'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Email notifications toggle -->
    <div class="notifications-settings">
        <label class="toggle-switch">
            <input type="checkbox" id="email-notifications-toggle"
                   <?php checked($is_email_enabled, true); ?>>
            <span class="toggle-slider"></span>
            <span class="toggle-label"><?php esc_html_e('Email notifications', 'homebio'); ?></span>
        </label>
        <small class="form-hint"><?php esc_html_e('Receive email when your favorite properties are updated', 'homebio'); ?></small>
    </div>

    <?php if (!empty($notifications)) : ?>
        <div class="notifications-list" id="notifications-list">
            <?php foreach ($notifications as $notification) : ?>
                <div class="notification-item <?php echo $notification['read'] ? 'read' : 'unread'; ?>"
                     data-notification-id="<?php echo esc_attr($notification['id']); ?>">
                    <div class="notification-icon">
                        <?php
                        if (!empty($notification['changes'])) {
                            echo homebio_get_notification_icon($notification['changes'][0]['type']);
                        } else {
                            echo homebio_get_notification_icon('info');
                        }
                        ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">
                            <a href="<?php echo esc_url($notification['property_url']); ?>">
                                <?php echo esc_html($notification['property_title']); ?>
                            </a>
                            <?php if (!$notification['read']) : ?>
                                <span class="notification-badge"><?php esc_html_e('New', 'homebio'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="notification-changes">
                            <?php foreach ($notification['changes'] as $change) : ?>
                                <div class="notification-change">
                                    <span class="change-label"><?php echo esc_html($change['label']); ?>:</span>
                                    <span class="change-old"><?php echo esc_html($change['old']); ?></span>
                                    <span class="change-arrow">&rarr;</span>
                                    <span class="change-new"><?php echo esc_html($change['new']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="notification-date">
                            <?php echo esc_html(homebio_format_notification_date($notification['date'])); ?>
                        </div>
                    </div>
                    <div class="notification-actions">
                        <?php if (!$notification['read']) : ?>
                            <button type="button" class="notification-action mark-read-btn"
                                    title="<?php esc_attr_e('Mark as read', 'homebio'); ?>">
                                <?php homebio_icon_e('check', 16); ?>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="notification-action delete-notification-btn"
                                title="<?php esc_attr_e('Delete', 'homebio'); ?>">
                            <?php homebio_icon_e('x', 16); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <?php
        get_template_part('template-parts/cabinet/empty-state', null, [
            'icon'        => 'bell',
            'title'       => __('No notifications yet', 'homebio'),
            'message'     => __('When properties in your favorites are updated, you\'ll see notifications here.', 'homebio'),
            'button_url'  => get_post_type_archive_link('property'),
            'button_text' => __('Browse Properties', 'homebio'),
        ]);
        ?>
    <?php endif; ?>
</div>
