/**
 * Notifications Module
 *
 * Handles notification list actions: mark as read, delete, email toggle.
 *
 * @module modules/notifications
 */

import { $, $$, delegate, getData, toggleClass } from '../utils/dom.js';
import { ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

/**
 * Initialize notifications functionality
 */
export function init() {
    initMarkAsRead();
    initDelete();
    initMarkAllRead();
    initDeleteAll();
    initEmailToggle();
}

/**
 * Initialize mark as read functionality
 */
function initMarkAsRead() {
    delegate(document.body, '.mark-read-btn', 'click', async (e, button) => {
        const item = button.closest('.notification-item');
        const notificationId = getData(item, 'notification-id');

        if (!notificationId) return;

        try {
            const response = await ajaxPost('mark_notification_read', {
                notification_id: notificationId
            });

            if (response.success) {
                item.classList.remove('unread');
                item.classList.add('read');

                // Remove the badge and mark button
                const badge = item.querySelector('.notification-badge');
                if (badge) badge.remove();
                button.remove();

                // Update count
                updateUnreadCount(response.data.unread_count);
            }
        } catch (error) {
            console.error('Mark read error:', error);
        }
    });
}

/**
 * Initialize delete notification functionality
 */
function initDelete() {
    delegate(document.body, '.delete-notification-btn', 'click', async (e, button) => {
        const item = button.closest('.notification-item');
        const notificationId = getData(item, 'notification-id');

        if (!notificationId) return;

        try {
            const response = await ajaxPost('delete_notification', {
                notification_id: notificationId
            });

            if (response.success) {
                // Animate removal
                item.style.transform = 'scale(0.95)';
                item.style.opacity = '0';

                setTimeout(() => {
                    item.remove();
                    updateUnreadCount(response.data.unread_count);
                    checkEmptyState();
                }, 200);
            }
        } catch (error) {
            console.error('Delete error:', error);
        }
    });
}

/**
 * Initialize mark all as read
 */
function initMarkAllRead() {
    const btn = $('#mark-all-read-btn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        try {
            const response = await ajaxPost('mark_all_notifications_read');

            if (response.success) {
                // Update all notification items
                $$('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');

                    const badge = item.querySelector('.notification-badge');
                    if (badge) badge.remove();

                    const markBtn = item.querySelector('.mark-read-btn');
                    if (markBtn) markBtn.remove();
                });

                updateUnreadCount(0);
                btn.style.display = 'none';

                showNotification('All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Mark all read error:', error);
        }
    });
}

/**
 * Initialize delete all notifications
 */
function initDeleteAll() {
    const btn = $('#delete-all-notifications-btn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to delete all notifications?')) {
            return;
        }

        try {
            const response = await ajaxPost('delete_all_notifications');

            if (response.success) {
                const list = $('#notifications-list');
                if (list) {
                    list.innerHTML = '';
                }

                updateUnreadCount(0);
                checkEmptyState();

                showNotification('All notifications deleted', 'success');
            }
        } catch (error) {
            console.error('Delete all error:', error);
        }
    });
}

/**
 * Initialize email notifications toggle
 */
function initEmailToggle() {
    const toggle = $('#email-notifications-toggle');
    if (!toggle) return;

    toggle.addEventListener('change', async () => {
        const enabled = toggle.checked;

        try {
            const response = await ajaxPost('toggle_email_notifications', {
                enabled: enabled ? '1' : '0'
            });

            if (response.success) {
                showNotification(
                    enabled ? 'Email notifications enabled' : 'Email notifications disabled',
                    'success'
                );
            } else {
                // Revert toggle
                toggle.checked = !enabled;
                showNotification('Failed to update setting', 'error');
            }
        } catch (error) {
            toggle.checked = !enabled;
            console.error('Toggle error:', error);
        }
    });
}

/**
 * Update unread notification count in UI
 *
 * @param {number} count - New unread count
 */
function updateUnreadCount(count) {
    // Update sidebar count
    const sidebarCount = $('.cabinet-nav-item[href*="notifications"] .cabinet-nav-count');
    if (sidebarCount) {
        sidebarCount.textContent = count;
        sidebarCount.style.display = count > 0 ? '' : 'none';
    }

    // Update header badge if exists
    const headerBadge = $('.notification-count');
    if (headerBadge) {
        headerBadge.textContent = count;
        headerBadge.style.display = count > 0 ? '' : 'none';
    }
}

/**
 * Check if notification list is empty and show empty state
 */
function checkEmptyState() {
    const list = $('#notifications-list');
    if (!list) return;

    if (list.children.length === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <h3>No notifications yet</h3>
            <p>When properties in your favorites are updated, you'll see notifications here.</p>
        `;

        list.parentNode.replaceChild(emptyState, list);

        // Hide action buttons
        const actions = $('.notifications-actions');
        if (actions) actions.style.display = 'none';
    }
}

export default { init };
