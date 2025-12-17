/**
 * HomeBio Theme JavaScript
 *
 * @package HomeBio
 */

(function() {
    'use strict';

    /**
     * Initialize when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initFavorites();
        initForms();
        initLanguageSwitcher();
    });

    /**
     * Mobile menu toggle
     */
    function initMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navigation = document.querySelector('.main-navigation');

        if (!menuToggle || !navigation) return;

        menuToggle.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            navigation.classList.toggle('is-open');
        });
    }

    /**
     * Favorites functionality
     */
    function initFavorites() {
        const favoriteButtons = document.querySelectorAll('.property-card__favorite');

        favoriteButtons.forEach(function(button) {
            button.addEventListener('click', handleFavoriteClick);
        });
    }

    /**
     * Handle favorite button click
     */
    function handleFavoriteClick(e) {
        e.preventDefault();

        const button = e.currentTarget;
        const propertyId = button.dataset.propertyId;

        if (!propertyId) return;

        // Disable button during request
        button.disabled = true;

        // Send AJAX request
        const formData = new FormData();
        formData.append('action', 'toggle_favorite');
        formData.append('property_id', propertyId);
        formData.append('nonce', homebioAjax.nonce);

        fetch(homebioAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle active class
                button.classList.toggle('is-active', data.data.is_favorite);

                // Update SVG fill
                const svg = button.querySelector('svg path');
                if (svg) {
                    svg.setAttribute('fill', data.data.is_favorite ? 'currentColor' : 'none');
                }

                // Update aria-label
                button.setAttribute('aria-label',
                    data.data.is_favorite
                        ? homebioAjax.strings.removedFromFavorites
                        : homebioAjax.strings.addedToFavorites
                );

                // Show notification
                showNotification(data.data.message, 'success');
            } else {
                // Handle login required
                if (data.data && data.data.login_required) {
                    showNotification(homebioAjax.strings.loginRequired, 'warning');
                    // Optionally redirect to login
                    // window.location.href = '/login?redirect_to=' + encodeURIComponent(window.location.href);
                } else {
                    showNotification(data.data.message || 'Error occurred', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Favorite toggle error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
        });
    }

    /**
     * Initialize forms (profile, language)
     */
    function initForms() {
        // Profile form
        const profileForm = document.getElementById('profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', handleProfileSubmit);
        }

        // Language form
        const languageForm = document.getElementById('language-form');
        if (languageForm) {
            languageForm.addEventListener('submit', handleLanguageSubmit);
        }
    }

    /**
     * Handle profile form submit
     */
    function handleProfileSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        formData.append('action', 'update_profile');
        formData.append('nonce', homebioAjax.nonce);

        fetch(homebioAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.data.message, 'success');
            } else {
                showNotification(data.data.message || 'Error saving profile', 'error');
            }
        })
        .catch(error => {
            console.error('Profile update error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    /**
     * Handle language form submit
     */
    function handleLanguageSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        formData.append('action', 'update_profile');
        formData.append('nonce', homebioAjax.nonce);

        fetch(homebioAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.data.message, 'success');
                // Reload page to apply language change
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.data.message || 'Error saving language', 'error');
            }
        })
        .catch(error => {
            console.error('Language update error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    /**
     * Initialize language switcher (header)
     */
    function initLanguageSwitcher() {
        const languageSelector = document.getElementById('language-selector');

        if (!languageSelector) return;

        languageSelector.addEventListener('change', function() {
            // This will be handled by WPML/Polylang when installed
            // For now, just show that it's working
            const selectedLang = this.value;
            console.log('Language changed to:', selectedLang);

            // When translation plugin is installed, uncomment:
            // window.location.href = getTranslatedUrl(selectedLang);
        });
    }

    /**
     * Show notification toast
     */
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelector('.homebio-notification');
        if (existing) {
            existing.remove();
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `homebio-notification homebio-notification--${type}`;
        notification.textContent = message;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        // Set background color based on type
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        // Add animation keyframes if not already added
        if (!document.getElementById('homebio-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'homebio-notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        // Add to DOM
        document.body.appendChild(notification);

        // Remove after delay
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

})();
