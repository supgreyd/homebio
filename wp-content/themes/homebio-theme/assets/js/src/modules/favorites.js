/**
 * Favorites Module
 *
 * Handles favorite button toggle, AJAX updates, and count badges.
 *
 * @module modules/favorites
 */

import { $$, delegate, getData, toggleClass } from '../utils/dom.js';
import { ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

/**
 * Initialize favorites functionality
 */
export function init() {
    // Use event delegation for favorite buttons
    delegate(document.body, '.property-card__favorite', 'click', handleFavoriteClick);
}

/**
 * Handle favorite button click
 *
 * @param {Event} e - Click event
 * @param {Element} button - The favorite button
 */
async function handleFavoriteClick(e, button) {
    e.preventDefault();
    e.stopPropagation();

    const propertyId = getData(button, 'property-id');
    if (!propertyId) return;

    // Prevent double-clicking
    if (button.disabled) return;
    button.disabled = true;

    try {
        const response = await ajaxPost('toggle_favorite', {
            property_id: propertyId
        });

        if (response.success) {
            // Toggle active state
            toggleClass(button, 'is-active', response.data.is_favorite);

            // Update heart icon fill
            const svg = button.querySelector('svg path');
            if (svg) {
                svg.setAttribute('fill', response.data.is_favorite ? 'currentColor' : 'none');
            }

            // Update count badges
            updateFavoriteCount(response.data.count);

            // Show notification
            showNotification(response.data.message, 'success');
        } else {
            // Handle login required
            if (response.data?.login_required) {
                showNotification(
                    window.homebioAjax?.strings?.loginRequired || 'Please log in to save favorites',
                    'warning'
                );
            } else {
                showNotification(response.data?.message || 'Error updating favorite', 'error');
            }
        }
    } catch (error) {
        console.error('Favorite toggle error:', error);
        showNotification('Error updating favorite', 'error');
    } finally {
        button.disabled = false;
    }
}

/**
 * Update favorite count badges throughout the page
 *
 * @param {number} count - New favorite count
 */
function updateFavoriteCount(count) {
    // Update header badge
    const headerBadge = document.querySelector('.favorites-badge');
    if (headerBadge) {
        headerBadge.textContent = count;
        headerBadge.style.display = count > 0 ? '' : 'none';
    }

    // Update sidebar count
    const sidebarCount = document.querySelector('.cabinet-nav-count');
    if (sidebarCount) {
        sidebarCount.textContent = count;
    }
}

export default { init };
