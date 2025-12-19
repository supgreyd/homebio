<?php
/**
 * Cabinet Security Tab Template Part
 *
 * Displays security settings including password change and account deletion.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$oauth_provider = get_user_meta($current_user->ID, 'oauth_provider', true);
?>

<div class="cabinet-section">
    <h1><?php esc_html_e('Security', 'homebio'); ?></h1>
    <p class="cabinet-section-desc"><?php esc_html_e('Manage your account security settings', 'homebio'); ?></p>

    <?php if ($oauth_provider) : ?>
        <!-- OAuth User -->
        <div class="security-card">
            <div class="security-card-icon google">
                <?php echo homebio_icon('google', 24, ['fill' => 'none', 'stroke' => 'none']); ?>
            </div>
            <div class="security-card-content">
                <h3><?php esc_html_e('Google Account', 'homebio'); ?></h3>
                <p><?php esc_html_e('Your account is connected to Google. Password is managed through your Google account.', 'homebio'); ?></p>
                <span class="security-status connected">
                    <?php homebio_icon_e('check', 16); ?>
                    <?php esc_html_e('Connected', 'homebio'); ?>
                </span>
            </div>
        </div>
    <?php else : ?>
        <!-- Regular User - Password Change -->
        <div class="security-card">
            <h3><?php esc_html_e('Change Password', 'homebio'); ?></h3>
            <form id="cabinet-password-form" class="cabinet-form">
                <?php wp_nonce_field('homebio_password_nonce', 'password_nonce'); ?>

                <div class="form-group">
                    <label for="current_password"><?php esc_html_e('Current Password', 'homebio'); ?></label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password"><?php esc_html_e('New Password', 'homebio'); ?></label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required>
                    <small class="form-hint"><?php esc_html_e('Minimum 8 characters', 'homebio'); ?></small>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><?php esc_html_e('Confirm New Password', 'homebio'); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php esc_html_e('Update Password', 'homebio'); ?>
                    </button>
                    <span class="form-message"></span>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Delete Account -->
    <div class="security-card danger">
        <h3><?php esc_html_e('Delete Account', 'homebio'); ?></h3>
        <p><?php esc_html_e('Once you delete your account, all your data will be permanently removed. This action cannot be undone.', 'homebio'); ?></p>
        <button type="button" class="btn btn-danger" id="delete-account-btn">
            <?php esc_html_e('Delete My Account', 'homebio'); ?>
        </button>
    </div>
</div>
