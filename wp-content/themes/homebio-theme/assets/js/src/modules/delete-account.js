/**
 * Delete Account Module
 *
 * Handles account deletion confirmation modal.
 *
 * @module modules/delete-account
 */

import { $, on } from '../utils/dom.js';
import { ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

/**
 * Initialize delete account functionality
 */
export function init() {
    const deleteBtn = $('#delete-account-btn');
    const modal = $('#delete-account-modal');
    const cancelBtn = $('#cancel-delete');
    const confirmBtn = $('#confirm-delete');

    if (!deleteBtn || !modal) return;

    // Open modal
    on(deleteBtn, 'click', () => {
        showModal(modal, true);
    });

    // Close modal on cancel
    if (cancelBtn) {
        on(cancelBtn, 'click', () => {
            showModal(modal, false);
        });
    }

    // Close modal on overlay click
    const overlay = modal.querySelector('.modal-overlay');
    if (overlay) {
        on(overlay, 'click', () => {
            showModal(modal, false);
        });
    }

    // Confirm delete
    if (confirmBtn) {
        on(confirmBtn, 'click', handleDelete);
    }

    // Close on Escape key
    on(document, 'keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display !== 'none') {
            showModal(modal, false);
        }
    });
}

/**
 * Show or hide modal
 *
 * @param {Element} modal - Modal element
 * @param {boolean} show - Whether to show or hide
 */
function showModal(modal, show) {
    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : '';
}

/**
 * Handle account deletion
 */
async function handleDelete() {
    const modal = $('#delete-account-modal');
    const confirmBtn = $('#confirm-delete');

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';
    }

    try {
        const response = await ajaxPost('delete_account');

        if (response.success) {
            showNotification('Account deleted. Redirecting...', 'success');

            // Redirect to home after short delay
            setTimeout(() => {
                window.location.href = response.data?.redirect || '/';
            }, 1500);
        } else {
            showNotification(response.data?.message || 'Failed to delete account', 'error');
            showModal(modal, false);
        }
    } catch (error) {
        console.error('Delete account error:', error);
        showNotification('Failed to delete account', 'error');
        showModal(modal, false);
    } finally {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Yes, Delete My Account';
        }
    }
}

export default { init };
