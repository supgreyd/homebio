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
        initUserDropdown();
        initFavorites();
        initCabinetForms();
        initAvatarUpload();
        initLanguageSwitcher();
        initDeleteAccount();
        initRemoveFavorite();
        initNotifications();
    });

    /**
     * Mobile menu toggle (slide-in panel)
     */
    function initMobileMenu() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.querySelector('.mobile-menu__close');
        const mobileMenuOverlay = document.querySelector('.mobile-menu__overlay');

        if (!menuToggle || !mobileMenu) return;

        // Open mobile menu
        menuToggle.addEventListener('click', function() {
            this.setAttribute('aria-expanded', 'true');
            mobileMenu.classList.add('is-open');
            if (mobileMenuOverlay) mobileMenuOverlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });

        // Close mobile menu function
        function closeMobileMenu() {
            menuToggle.setAttribute('aria-expanded', 'false');
            mobileMenu.classList.remove('is-open');
            if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        // Close button
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        // Overlay click
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('is-open')) {
                closeMobileMenu();
            }
        });
    }

    /**
     * User dropdown toggle
     */
    function initUserDropdown() {
        const dropdown = document.querySelector('.user-dropdown');
        const toggle = document.querySelector('.user-dropdown__toggle');
        const menu = document.querySelector('.user-dropdown__menu');

        if (!dropdown || !toggle || !menu) return;

        // Toggle dropdown on click
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            dropdown.classList.toggle('is-open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.classList.remove('is-open');
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && dropdown.classList.contains('is-open')) {
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.classList.remove('is-open');
            }
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

                // Update favorites count in header
                updateFavoritesCount(data.data.count);

                // Show notification
                showNotification(data.data.message, 'success');
            } else {
                // Handle login required
                if (data.data && data.data.login_required) {
                    showNotification(homebioAjax.strings.loginRequired, 'warning');
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
     * Initialize cabinet forms
     */
    function initCabinetForms() {
        // Settings form
        const settingsForm = document.getElementById('cabinet-settings-form');
        if (settingsForm) {
            settingsForm.addEventListener('submit', handleSettingsSubmit);
        }

        // Password form
        const passwordForm = document.getElementById('cabinet-password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', handlePasswordSubmit);
        }
    }

    /**
     * Initialize avatar upload
     */
    function initAvatarUpload() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const removeBtn = document.getElementById('remove-avatar-btn');
        const uploadSection = document.querySelector('.avatar-upload');

        if (!avatarInput || !avatarPreview) return;

        // Handle file selection
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showNotification('Please upload a valid image file (JPG, PNG, GIF, or WebP)', 'error');
                return;
            }

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('Image must be less than 2MB', 'error');
                return;
            }

            // Show preview immediately
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar preview">';
            };
            reader.readAsDataURL(file);

            // Upload the file
            if (uploadSection) uploadSection.classList.add('is-uploading');

            const formData = new FormData();
            formData.append('action', 'upload_avatar');
            formData.append('avatar', file);
            formData.append('nonce', homebioAjax.nonce);

            fetch(homebioAjax.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Avatar upload response:', data);
                if (data.success) {
                    showNotification(data.data.message, 'success');

                    // Update avatar preview with server URL (with cache buster)
                    if (data.data.avatar_url) {
                        const cacheBuster = '?t=' + Date.now();
                        const avatarUrl = data.data.avatar_url + (data.data.avatar_url.includes('?') ? '&t=' : '?t=') + Date.now();
                        avatarPreview.innerHTML = '<img src="' + avatarUrl + '" alt="Avatar">';

                        // Also update sidebar avatar
                        const sidebarAvatar = document.querySelector('.cabinet-user-info img');
                        if (sidebarAvatar) {
                            sidebarAvatar.src = avatarUrl;
                        }

                        // Update header avatar if exists
                        const headerAvatar = document.querySelector('.user-dropdown__toggle img');
                        if (headerAvatar) {
                            headerAvatar.src = avatarUrl;
                        }
                    }

                    // Show remove button if not already visible
                    if (!removeBtn) {
                        const actionsDiv = document.querySelector('.avatar-upload-actions');
                        if (actionsDiv) {
                            const newRemoveBtn = document.createElement('button');
                            newRemoveBtn.type = 'button';
                            newRemoveBtn.id = 'remove-avatar-btn';
                            newRemoveBtn.className = 'btn btn-outline btn-sm';
                            newRemoveBtn.textContent = 'Remove';
                            actionsDiv.appendChild(newRemoveBtn);
                            newRemoveBtn.addEventListener('click', handleRemoveAvatar);
                        }
                    }
                } else {
                    showNotification(data.data.message || 'Error uploading avatar', 'error');
                }
            })
            .catch(error => {
                console.error('Avatar upload error:', error);
                showNotification('An error occurred while uploading', 'error');
            })
            .finally(() => {
                if (uploadSection) uploadSection.classList.remove('is-uploading');
                avatarInput.value = '';
            });
        });

        // Handle remove avatar
        if (removeBtn) {
            removeBtn.addEventListener('click', handleRemoveAvatar);
        }

        function handleRemoveAvatar() {
            const formData = new FormData();
            formData.append('action', 'remove_avatar');
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

                    // Update preview with default gravatar
                    if (data.data.avatar_url) {
                        avatarPreview.innerHTML = '<img src="' + data.data.avatar_url + '" alt="Avatar">';
                    }

                    // Remove the remove button
                    const removeButton = document.getElementById('remove-avatar-btn');
                    if (removeButton) {
                        removeButton.remove();
                    }
                } else {
                    showNotification(data.data.message || 'Error removing avatar', 'error');
                }
            })
            .catch(error => {
                console.error('Remove avatar error:', error);
                showNotification('An error occurred', 'error');
            });
        }
    }

    /**
     * Handle settings form submit
     */
    function handleSettingsSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageEl = form.querySelector('.form-message');
        const originalText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        if (messageEl) messageEl.textContent = '';

        const formData = new FormData(form);
        formData.append('action', 'update_settings');
        formData.append('nonce', homebioAjax.nonce);

        fetch(homebioAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (messageEl) {
                    messageEl.textContent = data.data.message;
                    messageEl.className = 'form-message success';
                }
                showNotification(data.data.message, 'success');
            } else {
                if (messageEl) {
                    messageEl.textContent = data.data.message || 'Error saving settings';
                    messageEl.className = 'form-message error';
                }
                showNotification(data.data.message || 'Error saving settings', 'error');
            }
        })
        .catch(error => {
            console.error('Settings update error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    /**
     * Handle password form submit
     */
    function handlePasswordSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageEl = form.querySelector('.form-message');
        const originalText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
        if (messageEl) messageEl.textContent = '';

        const formData = new FormData(form);
        formData.append('action', 'change_password');
        formData.append('nonce', homebioAjax.nonce);

        fetch(homebioAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (messageEl) {
                    messageEl.textContent = data.data.message;
                    messageEl.className = 'form-message success';
                }
                showNotification(data.data.message, 'success');
                form.reset();
            } else {
                if (messageEl) {
                    messageEl.textContent = data.data.message || 'Error changing password';
                    messageEl.className = 'form-message error';
                }
                showNotification(data.data.message || 'Error changing password', 'error');
            }
        })
        .catch(error => {
            console.error('Password change error:', error);
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
            const selectedLocale = this.value;

            if (typeof homebioAjax === 'undefined') {
                // No AJAX available, just reload
                window.location.reload();
                return;
            }

            const formData = new FormData();
            formData.append('action', 'switch_language');
            formData.append('locale', selectedLocale);
            formData.append('nonce', homebioAjax.nonce);

            fetch(homebioAjax.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the current page to apply new language
                    window.location.reload();
                } else {
                    showNotification(data.data.message || 'Error changing language', 'error');
                }
            })
            .catch(error => {
                console.error('Language switch error:', error);
                window.location.reload();
            });
        });
    }

    /**
     * Update favorites count badge in header
     */
    function updateFavoritesCount(count) {
        const badge = document.querySelector('.favorites-badge');

        if (count > 0) {
            if (badge) {
                // Update existing badge
                badge.textContent = count;
            } else {
                // Create new badge
                const favoritesLink = document.querySelector('.user-dropdown__item[href*="favorites"]');
                if (favoritesLink) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'favorites-badge';
                    newBadge.textContent = count;
                    favoritesLink.appendChild(newBadge);
                }
            }
        } else {
            // Remove badge if count is 0
            if (badge) {
                badge.remove();
            }
        }
    }

    /**
     * Show notification toast
     *
     * Uses CSS classes defined in style.css for styling.
     * No inline styles needed - all styles are in the stylesheet.
     *
     * @param {string} message - The notification message
     * @param {string} type - Notification type: 'success', 'error', 'warning', 'info'
     */
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelector('.homebio-notification');
        if (existing) {
            existing.remove();
        }

        // Create notification element with CSS classes (styles defined in style.css)
        const notification = document.createElement('div');
        notification.className = `homebio-notification homebio-notification--${type}`;
        notification.textContent = message;

        // Add to DOM
        document.body.appendChild(notification);

        // Remove after delay with slide-out animation
        setTimeout(() => {
            notification.classList.add('homebio-notification--slide-out');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Initialize delete account functionality
     */
    function initDeleteAccount() {
        const deleteBtn = document.getElementById('delete-account-btn');
        const modal = document.getElementById('delete-account-modal');

        if (!deleteBtn || !modal) return;

        const cancelBtn = document.getElementById('cancel-delete');
        const confirmBtn = document.getElementById('confirm-delete');
        const overlay = modal.querySelector('.modal-overlay');

        // Open modal
        deleteBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });

        // Close modal on cancel
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        // Close on overlay click
        if (overlay) {
            overlay.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        // Confirm delete
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.textContent = 'Deleting...';

                const formData = new FormData();
                formData.append('action', 'delete_account');
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
                        setTimeout(() => {
                            window.location.href = data.data.redirect || '/';
                        }, 1500);
                    } else {
                        showNotification(data.data.message || 'Error deleting account', 'error');
                        btn.disabled = false;
                        btn.textContent = 'Yes, Delete My Account';
                    }
                })
                .catch(error => {
                    console.error('Delete account error:', error);
                    showNotification('An error occurred', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Yes, Delete My Account';
                });
            });
        }
    }

    /**
     * Initialize remove favorite from cabinet
     */
    function initRemoveFavorite() {
        const removeButtons = document.querySelectorAll('.favorite-remove');

        removeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const propertyId = this.dataset.propertyId;
                const card = this.closest('.favorite-card');

                if (!propertyId || !card) return;

                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'remove_favorite');
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
                        // Animate card removal
                        card.style.transition = 'opacity 0.3s, transform 0.3s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';

                        setTimeout(() => {
                            card.remove();

                            // Update count in sidebar
                            const countEl = document.querySelector('.cabinet-nav-count');
                            if (countEl && data.data.count !== undefined) {
                                if (data.data.count > 0) {
                                    countEl.textContent = data.data.count;
                                } else {
                                    countEl.remove();
                                }
                            }

                            // Update header badge
                            updateFavoritesCount(data.data.count);

                            // Show empty state if no more favorites
                            const grid = document.querySelector('.favorites-grid');
                            if (grid && grid.children.length === 0) {
                                grid.innerHTML = `
                                    <div class="favorites-empty" style="grid-column: 1/-1;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <h3>No favorites yet</h3>
                                        <p>Start browsing properties and save your favorites here.</p>
                                        <a href="/properties/" class="btn btn-primary">Browse Properties</a>
                                    </div>
                                `;
                            }
                        }, 300);

                        showNotification(data.data.message, 'success');
                    } else {
                        showNotification(data.data.message || 'Error removing favorite', 'error');
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Remove favorite error:', error);
                    showNotification('An error occurred', 'error');
                    this.disabled = false;
                });
            });
        });
    }

    /**
     * Initialize notifications functionality
     */
    function initNotifications() {
        // Mark single notification as read
        const markReadBtns = document.querySelectorAll('.mark-read-btn');
        markReadBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                const notificationId = notificationItem.dataset.notificationId;

                if (!notificationId) return;

                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'homebio_mark_notification_read');
                formData.append('notification_id', notificationId);
                formData.append('nonce', homebioAjax.nonce);

                fetch(homebioAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        notificationItem.classList.remove('unread');
                        notificationItem.classList.add('read');

                        // Remove badge
                        const badge = notificationItem.querySelector('.notification-badge');
                        if (badge) badge.remove();

                        // Remove mark as read button
                        this.remove();

                        // Update sidebar count
                        updateNotificationCount(data.data.unread_count);
                    } else {
                        showNotification(data.data.message || 'Error', 'error');
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Mark read error:', error);
                    showNotification('An error occurred', 'error');
                    this.disabled = false;
                });
            });
        });

        // Mark all as read
        const markAllReadBtn = document.getElementById('mark-all-read-btn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'homebio_mark_all_notifications_read');
                formData.append('nonce', homebioAjax.nonce);

                fetch(homebioAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update all notification items
                        document.querySelectorAll('.notification-item.unread').forEach(function(item) {
                            item.classList.remove('unread');
                            item.classList.add('read');

                            // Remove badge
                            const badge = item.querySelector('.notification-badge');
                            if (badge) badge.remove();

                            // Remove mark as read button
                            const markBtn = item.querySelector('.mark-read-btn');
                            if (markBtn) markBtn.remove();
                        });

                        // Update count and remove button
                        updateNotificationCount(0);
                        this.remove();

                        showNotification(data.data.message, 'success');
                    } else {
                        showNotification(data.data.message || 'Error', 'error');
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Mark all read error:', error);
                    showNotification('An error occurred', 'error');
                    this.disabled = false;
                });
            });
        }

        // Delete single notification
        const deleteBtns = document.querySelectorAll('.delete-notification-btn');
        deleteBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                const notificationId = notificationItem.dataset.notificationId;

                if (!notificationId) return;

                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'homebio_delete_notification');
                formData.append('notification_id', notificationId);
                formData.append('nonce', homebioAjax.nonce);

                fetch(homebioAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animate removal
                        notificationItem.style.transition = 'opacity 0.3s, transform 0.3s';
                        notificationItem.style.opacity = '0';
                        notificationItem.style.transform = 'translateX(20px)';

                        setTimeout(function() {
                            notificationItem.remove();

                            // Update count
                            updateNotificationCount(data.data.unread_count);

                            // Show empty state if no more notifications
                            const list = document.getElementById('notifications-list');
                            if (list && list.children.length === 0) {
                                showNotificationsEmptyState();
                            }
                        }, 300);
                    } else {
                        showNotification(data.data.message || 'Error', 'error');
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Delete notification error:', error);
                    showNotification('An error occurred', 'error');
                    this.disabled = false;
                });
            });
        });

        // Delete all notifications
        const deleteAllBtn = document.getElementById('delete-all-notifications-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to delete all notifications?')) {
                    return;
                }

                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'homebio_delete_all_notifications');
                formData.append('nonce', homebioAjax.nonce);

                fetch(homebioAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationCount(0);
                        showNotificationsEmptyState();
                        showNotification(data.data.message, 'success');
                    } else {
                        showNotification(data.data.message || 'Error', 'error');
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Delete all notifications error:', error);
                    showNotification('An error occurred', 'error');
                    this.disabled = false;
                });
            });
        }

        // Email notifications toggle
        const emailToggle = document.getElementById('email-notifications-toggle');
        if (emailToggle) {
            emailToggle.addEventListener('change', function() {
                const enabled = this.checked ? 'enabled' : 'disabled';

                const formData = new FormData();
                formData.append('action', 'homebio_toggle_email_notifications');
                formData.append('enabled', enabled);
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
                        showNotification(data.data.message || 'Error', 'error');
                        // Revert toggle
                        this.checked = !this.checked;
                    }
                })
                .catch(error => {
                    console.error('Toggle email notifications error:', error);
                    showNotification('An error occurred', 'error');
                    // Revert toggle
                    this.checked = !this.checked;
                });
            });
        }
    }

    /**
     * Update notification count in sidebar
     */
    function updateNotificationCount(count) {
        const navItem = document.querySelector('.cabinet-nav-item[href*="notifications"]');
        if (!navItem) return;

        let badge = navItem.querySelector('.cabinet-nav-count');

        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else {
                badge = document.createElement('span');
                badge.className = 'cabinet-nav-count';
                badge.textContent = count;
                navItem.appendChild(badge);
            }
        } else {
            if (badge) {
                badge.remove();
            }
        }
    }

    /**
     * Show empty notifications state
     */
    function showNotificationsEmptyState() {
        const section = document.querySelector('.cabinet-section');
        if (!section) return;

        // Remove header actions and list
        const actionsDiv = document.querySelector('.notifications-actions');
        if (actionsDiv) actionsDiv.remove();

        const list = document.getElementById('notifications-list');
        if (list) list.remove();

        // Add empty state
        const settings = document.querySelector('.notifications-settings');
        if (settings) {
            settings.insertAdjacentHTML('afterend', `
                <div class="notifications-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <h3>No notifications yet</h3>
                    <p>When properties in your favorites are updated, you'll see notifications here.</p>
                    <a href="/properties/" class="btn btn-primary">Browse Properties</a>
                </div>
            `);
        }
    }

})();
