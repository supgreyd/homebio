/**
 * HomeBio Theme - Main JavaScript Entry Point
 *
 * This file imports and initializes all JavaScript modules.
 * Use a build tool (esbuild, Rollup, etc.) to bundle for production.
 *
 * @package HomeBio
 * @version 1.1.0
 */

// Utility modules
import notification from './utils/notification.js';

// Feature modules
import mobileMenu from './modules/mobile-menu.js';
import userDropdown from './modules/user-dropdown.js';
import favorites from './modules/favorites.js';
import cabinetForms from './modules/cabinet-forms.js';
import avatarUpload from './modules/avatar-upload.js';
import notifications from './modules/notifications.js';
import deleteAccount from './modules/delete-account.js';
import removeFavorite from './modules/remove-favorite.js';
import languageSwitcher from './modules/language-switcher.js';

/**
 * Initialize all modules on DOMContentLoaded
 */
function init() {
    // Core UI modules (always initialize)
    mobileMenu.init();
    userDropdown.init();
    favorites.init();
    languageSwitcher.init();

    // Cabinet-specific modules (only on cabinet pages)
    if (document.querySelector('.cabinet-page')) {
        cabinetForms.init();
        avatarUpload.init();
        notifications.init();
        deleteAccount.init();
        removeFavorite.init();
    }

    console.log('[HomeBio] All modules initialized');
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Export for external use if needed
export {
    notification,
    mobileMenu,
    userDropdown,
    favorites,
    cabinetForms,
    avatarUpload,
    notifications,
    deleteAccount,
    removeFavorite,
    languageSwitcher
};
