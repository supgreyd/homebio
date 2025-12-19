<?php
/**
 * Cabinet Settings Tab Template Part
 *
 * Displays the user profile settings form.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$first_name = get_user_meta($current_user->ID, 'first_name', true);
$last_name = get_user_meta($current_user->ID, 'last_name', true);
$phone = get_user_meta($current_user->ID, 'phone', true);
$birth_date = get_user_meta($current_user->ID, 'birth_date', true);
$oauth_provider = get_user_meta($current_user->ID, 'oauth_provider', true);
$has_custom_avatar = get_user_meta($current_user->ID, 'homebio_custom_avatar_id', true);
?>

<div class="cabinet-section">
    <h1><?php esc_html_e('Profile Settings', 'homebio'); ?></h1>
    <p class="cabinet-section-desc"><?php esc_html_e('Manage your personal information', 'homebio'); ?></p>

    <!-- Avatar Upload -->
    <div class="avatar-upload-section">
        <div class="avatar-upload">
            <div class="avatar-preview" id="avatar-preview">
                <?php echo homebio_get_avatar($current_user->ID, 120); ?>
            </div>
            <div class="avatar-upload-info">
                <h3><?php esc_html_e('Profile Photo', 'homebio'); ?></h3>
                <p><?php esc_html_e('Upload a photo to personalize your profile.', 'homebio'); ?></p>
                <div class="avatar-upload-actions">
                    <label for="avatar-input" class="btn btn-secondary btn-sm">
                        <?php homebio_icon_e('upload', 16); ?>
                        <?php esc_html_e('Upload Photo', 'homebio'); ?>
                    </label>
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                    <?php if ($has_custom_avatar) : ?>
                        <button type="button" id="remove-avatar-btn" class="btn btn-outline btn-sm">
                            <?php esc_html_e('Remove', 'homebio'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <small class="form-hint"><?php esc_html_e('JPG, PNG, GIF or WebP. Max 2MB.', 'homebio'); ?></small>
            </div>
        </div>
    </div>

    <form id="cabinet-settings-form" class="cabinet-form">
        <?php wp_nonce_field('homebio_cabinet_nonce', 'cabinet_nonce'); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name"><?php esc_html_e('First Name', 'homebio'); ?></label>
                <input type="text" id="first_name" name="first_name"
                       value="<?php echo esc_attr($first_name); ?>">
            </div>
            <div class="form-group">
                <label for="last_name"><?php esc_html_e('Last Name', 'homebio'); ?></label>
                <input type="text" id="last_name" name="last_name"
                       value="<?php echo esc_attr($last_name); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email"><?php esc_html_e('Email Address', 'homebio'); ?></label>
            <input type="email" id="email" name="email"
                   value="<?php echo esc_attr($current_user->user_email); ?>"
                   <?php echo $oauth_provider ? 'readonly' : ''; ?>>
            <?php if ($oauth_provider) : ?>
                <small class="form-hint"><?php esc_html_e('Email is managed by your Google account', 'homebio'); ?></small>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone"><?php esc_html_e('Phone Number', 'homebio'); ?></label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo esc_attr($phone); ?>"
                       placeholder="+359 888 123 456">
            </div>
            <div class="form-group">
                <label for="birth_date"><?php esc_html_e('Date of Birth', 'homebio'); ?></label>
                <input type="date" id="birth_date" name="birth_date"
                       value="<?php echo esc_attr($birth_date); ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?php esc_html_e('Save Changes', 'homebio'); ?>
            </button>
            <span class="form-message"></span>
        </div>
    </form>
</div>
