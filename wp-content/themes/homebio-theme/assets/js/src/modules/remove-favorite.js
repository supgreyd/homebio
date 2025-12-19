/**
 * Remove Favorite Module
 *
 * Handles removing favorites from the user cabinet favorites grid.
 *
 * @module modules/remove-favorite
 */

import { $, delegate, getData } from '../utils/dom.js';
import { ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

/**
 * Initialize remove favorite functionality
 */
export function init() {
    delegate(document.body, '.favorite-remove', 'click', handleRemove);
}

/**
 * Handle remove button click
 *
 * @param {Event} e - Click event
 * @param {Element} button - The remove button
 */
async function handleRemove(e, button) {
    e.preventDefault();

    const card = button.closest('.favorite-card');
    const propertyId = getData(button, 'property-id') || getData(card, 'property-id');

    if (!propertyId) return;

    // Prevent double-clicking
    if (button.disabled) return;
    button.disabled = true;

    try {
        const response = await ajaxPost('remove_favorite', {
            property_id: propertyId
        });

        if (response.success) {
            // Animate removal
            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0';

            setTimeout(() => {
                card.remove();

                // Update count
                updateFavoriteCount(response.data.count);

                // Check if grid is empty
                checkEmptyState();

                showNotification(response.data.message, 'success');
            }, 300);
        } else {
            showNotification(response.data?.message || 'Failed to remove', 'error');
            button.disabled = false;
        }
    } catch (error) {
        console.error('Remove favorite error:', error);
        showNotification('Failed to remove from favorites', 'error');
        button.disabled = false;
    }
}

/**
 * Update favorite count in UI
 *
 * @param {number} count - New count
 */
function updateFavoriteCount(count) {
    // Update sidebar count
    const sidebarCount = $('.cabinet-nav-item[href*="favorites"] .cabinet-nav-count');
    if (sidebarCount) {
        sidebarCount.textContent = count;
        if (count === 0) {
            sidebarCount.style.display = 'none';
        }
    }

    // Update header badge
    const headerBadge = $('.favorites-badge');
    if (headerBadge) {
        headerBadge.textContent = count;
        if (count === 0) {
            headerBadge.style.display = 'none';
        }
    }
}

/**
 * Check if favorites grid is empty and show empty state
 */
function checkEmptyState() {
    const grid = $('.favorites-grid');
    if (!grid) return;

    if (grid.children.length === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <h3>No favorites yet</h3>
            <p>Start browsing properties and save your favorites here.</p>
            <a href="/properties/" class="btn btn-primary">Browse Properties</a>
        `;

        grid.parentNode.replaceChild(emptyState, grid);
    }
}

export default { init };
