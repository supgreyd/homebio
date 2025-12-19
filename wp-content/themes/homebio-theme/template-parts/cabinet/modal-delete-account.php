<?php
/**
 * Delete Account Modal Template Part
 *
 * Confirmation modal for account deletion.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="delete-account-modal" class="modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <h3><?php esc_html_e('Delete Account', 'homebio'); ?></h3>
        <p><?php esc_html_e('Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed.', 'homebio'); ?></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancel-delete">
                <?php esc_html_e('Cancel', 'homebio'); ?>
            </button>
            <button type="button" class="btn btn-danger" id="confirm-delete">
                <?php esc_html_e('Yes, Delete My Account', 'homebio'); ?>
            </button>
        </div>
    </div>
</div>
