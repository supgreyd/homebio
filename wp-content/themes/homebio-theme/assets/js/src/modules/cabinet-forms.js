/**
 * Cabinet Forms Module
 *
 * Handles cabinet settings form, password change form submissions.
 *
 * @module modules/cabinet-forms
 */

import { $, $$, on } from '../utils/dom.js';
import { ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

/**
 * Initialize cabinet forms
 */
export function init() {
    initSettingsForm();
    initPasswordForm();
}

/**
 * Initialize settings form
 */
function initSettingsForm() {
    const form = $('#cabinet-settings-form');
    if (!form) return;

    on(form, 'submit', async (e) => {
        e.preventDefault();
        const response = await submitForm(form, 'update_settings', 'cabinet_nonce');

        // Update user name in UI if data is returned
        if (response?.success && response.data?.user) {
            updateUserDisplay(response.data.user);
        }
    });
}

/**
 * Update user display name in header and sidebar
 *
 * @param {Object} user - User data object
 */
function updateUserDisplay(user) {
    const displayName = user.first_name || user.display_name;

    // Update all elements with data-user-display-name attribute
    $$('[data-user-display-name]').forEach((el) => {
        el.textContent = displayName;
    });

    // Update email if changed
    if (user.email) {
        $$('[data-user-email]').forEach((el) => {
            el.textContent = user.email;
        });
    }
}

/**
 * Initialize password form
 */
function initPasswordForm() {
    const form = $('#cabinet-password-form');
    if (!form) return;

    on(form, 'submit', async (e) => {
        e.preventDefault();

        // Validate password match
        const newPassword = form.querySelector('[name="new_password"]')?.value;
        const confirmPassword = form.querySelector('[name="confirm_password"]')?.value;

        if (newPassword !== confirmPassword) {
            showNotification('Passwords do not match', 'error');
            return;
        }

        await submitForm(form, 'change_password', 'password_nonce');

        // Clear password fields on success
        form.reset();
    });
}

/**
 * Submit form via AJAX
 *
 * @param {HTMLFormElement} form - Form element
 * @param {string} action - AJAX action name
 * @param {string} nonceField - Name of nonce field in form
 * @returns {Object|null} Response object or null on error
 */
async function submitForm(form, action, nonceField) {
    const submitBtn = form.querySelector('[type="submit"]');
    const messageEl = form.querySelector('.form-message');
    const originalText = submitBtn?.textContent;
    let response = null;

    // Set loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }

    try {
        // Build form data
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        response = await ajaxPost(action, data);

        if (response.success) {
            showNotification(response.data.message, 'success');
            if (messageEl) {
                messageEl.textContent = response.data.message;
                messageEl.className = 'form-message success';
            }
        } else {
            showNotification(response.data?.message || 'Error saving', 'error');
            if (messageEl) {
                messageEl.textContent = response.data?.message || 'Error';
                messageEl.className = 'form-message error';
            }
        }
    } catch (error) {
        console.error('Form submit error:', error);
        showNotification('Error saving changes', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    return response;
}

export default { init };
