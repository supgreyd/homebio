/**
 * Notification Utility Module
 *
 * Toast notification system using CSS classes for styling.
 *
 * @module utils/notification
 */

/**
 * Show a toast notification
 *
 * @param {string} message - The message to display
 * @param {string} type - Notification type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duration in milliseconds before auto-dismiss
 */
export function showNotification(message, type = 'info', duration = 3000) {
    // Remove existing notifications
    const existing = document.querySelector('.homebio-notification');
    if (existing) {
        existing.remove();
    }

    // Create notification element with CSS classes (styles in style.css)
    const notification = document.createElement('div');
    notification.className = `homebio-notification homebio-notification--${type}`;
    notification.textContent = message;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'polite');

    // Add to DOM
    document.body.appendChild(notification);

    // Remove after delay with slide-out animation
    setTimeout(() => {
        notification.classList.add('homebio-notification--slide-out');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}

/**
 * Show success notification
 *
 * @param {string} message - The message to display
 */
export function showSuccess(message) {
    showNotification(message, 'success');
}

/**
 * Show error notification
 *
 * @param {string} message - The message to display
 */
export function showError(message) {
    showNotification(message, 'error');
}

/**
 * Show warning notification
 *
 * @param {string} message - The message to display
 */
export function showWarning(message) {
    showNotification(message, 'warning');
}

/**
 * Show info notification
 *
 * @param {string} message - The message to display
 */
export function showInfo(message) {
    showNotification(message, 'info');
}

export default {
    show: showNotification,
    success: showSuccess,
    error: showError,
    warning: showWarning,
    info: showInfo
};
