/**
 * DOM Utility Module
 *
 * Helper functions for DOM manipulation.
 *
 * @module utils/dom
 */

/**
 * Query selector shorthand
 *
 * @param {string} selector - CSS selector
 * @param {Element} context - Parent element to search within
 * @returns {Element|null} Found element or null
 */
export function $(selector, context = document) {
    return context.querySelector(selector);
}

/**
 * Query selector all shorthand
 *
 * @param {string} selector - CSS selector
 * @param {Element} context - Parent element to search within
 * @returns {NodeList} Found elements
 */
export function $$(selector, context = document) {
    return context.querySelectorAll(selector);
}

/**
 * Add event listener to element(s)
 *
 * @param {Element|NodeList|string} target - Element, NodeList, or selector
 * @param {string} event - Event type
 * @param {Function} handler - Event handler
 * @param {Object} options - Event listener options
 */
export function on(target, event, handler, options = {}) {
    const elements = typeof target === 'string' ? $$(target) : (target.length !== undefined ? target : [target]);

    elements.forEach(el => {
        if (el && el.addEventListener) {
            el.addEventListener(event, handler, options);
        }
    });
}

/**
 * Delegate event handling
 *
 * @param {Element} parent - Parent element to attach listener to
 * @param {string} selector - Selector for target elements
 * @param {string} event - Event type
 * @param {Function} handler - Event handler
 */
export function delegate(parent, selector, event, handler) {
    parent.addEventListener(event, (e) => {
        const target = e.target.closest(selector);
        if (target && parent.contains(target)) {
            handler.call(target, e, target);
        }
    });
}

/**
 * Toggle class on element
 *
 * @param {Element} element - Target element
 * @param {string} className - Class to toggle
 * @param {boolean} force - Force add or remove
 * @returns {boolean} Whether class is now present
 */
export function toggleClass(element, className, force) {
    if (!element) return false;
    return element.classList.toggle(className, force);
}

/**
 * Check if element has class
 *
 * @param {Element} element - Target element
 * @param {string} className - Class to check
 * @returns {boolean} Whether class is present
 */
export function hasClass(element, className) {
    if (!element) return false;
    return element.classList.contains(className);
}

/**
 * Get data attribute value
 *
 * @param {Element} element - Target element
 * @param {string} key - Data attribute key (without 'data-' prefix)
 * @returns {string|null} Attribute value
 */
export function getData(element, key) {
    if (!element) return null;
    return element.dataset[key] || element.getAttribute(`data-${key}`);
}

export default {
    $,
    $$,
    on,
    delegate,
    toggleClass,
    hasClass,
    getData
};
