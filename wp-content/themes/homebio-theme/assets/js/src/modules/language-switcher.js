/**
 * Language Switcher Module
 *
 * Handles language selection and page reload.
 *
 * @module modules/language-switcher
 */

import { $, on } from '../utils/dom.js';

/**
 * Initialize language switcher functionality
 */
export function init() {
    const switcher = $('.language-select');
    if (!switcher) return;

    on(switcher, 'change', handleLanguageChange);
}

/**
 * Handle language selection change
 *
 * @param {Event} e - Change event
 */
function handleLanguageChange(e) {
    const language = e.target.value;
    if (!language) return;

    // Set cookie for language preference
    document.cookie = `homebio_language=${language}; path=/; max-age=31536000`;

    // Reload page to apply new language
    window.location.reload();
}

export default { init };
