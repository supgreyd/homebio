/**
 * User Dropdown Module
 *
 * Handles user dropdown menu toggle and outside click detection.
 *
 * @module modules/user-dropdown
 */

import { $, on, toggleClass } from '../utils/dom.js';

/**
 * Initialize user dropdown functionality
 */
export function init() {
    const dropdown = $('.user-dropdown');
    const toggle = $('.user-dropdown__toggle');

    if (!dropdown || !toggle) return;

    // Toggle dropdown
    on(toggle, 'click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('is-open');
        toggleDropdown(dropdown, !isOpen);
    });

    // Close on outside click
    on(document, 'click', (e) => {
        if (!dropdown.contains(e.target)) {
            toggleDropdown(dropdown, false);
        }
    });

    // Close on Escape key
    on(document, 'keydown', (e) => {
        if (e.key === 'Escape' && dropdown.classList.contains('is-open')) {
            toggleDropdown(dropdown, false);
        }
    });
}

/**
 * Toggle dropdown state
 *
 * @param {Element} dropdown - Dropdown element
 * @param {boolean} open - Whether to open or close
 */
function toggleDropdown(dropdown, open) {
    toggleClass(dropdown, 'is-open', open);

    const toggle = dropdown.querySelector('.user-dropdown__toggle');
    if (toggle) {
        toggle.setAttribute('aria-expanded', open);
    }
}

export default { init };
