/**
 * Mobile Menu Module
 *
 * Handles mobile menu toggle, overlay, and keyboard navigation.
 *
 * @module modules/mobile-menu
 */

import { $, on, toggleClass } from '../utils/dom.js';

/**
 * Initialize mobile menu functionality
 */
export function init() {
    const menuToggle = $('.menu-toggle');
    const mobileMenu = $('.mobile-menu');
    const menuClose = $('.mobile-menu__close');
    const menuOverlay = $('.mobile-menu__overlay');

    if (!menuToggle || !mobileMenu) return;

    // Toggle menu
    on(menuToggle, 'click', () => {
        toggleMenu(mobileMenu, menuOverlay, true);
    });

    // Close menu
    if (menuClose) {
        on(menuClose, 'click', () => {
            toggleMenu(mobileMenu, menuOverlay, false);
        });
    }

    // Close on overlay click
    if (menuOverlay) {
        on(menuOverlay, 'click', () => {
            toggleMenu(mobileMenu, menuOverlay, false);
        });
    }

    // Close on Escape key
    on(document, 'keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu.classList.contains('is-open')) {
            toggleMenu(mobileMenu, menuOverlay, false);
        }
    });
}

/**
 * Toggle mobile menu state
 *
 * @param {Element} menu - Mobile menu element
 * @param {Element} overlay - Menu overlay element
 * @param {boolean} open - Whether to open or close
 */
function toggleMenu(menu, overlay, open) {
    toggleClass(menu, 'is-open', open);
    if (overlay) {
        toggleClass(overlay, 'is-open', open);
    }

    // Prevent body scroll when menu is open
    document.body.style.overflow = open ? 'hidden' : '';

    // Accessibility
    menu.setAttribute('aria-hidden', !open);
}

export default { init };
