/**
 * Avatar Upload Module
 *
 * Handles avatar file upload, preview, and removal.
 *
 * @module modules/avatar-upload
 */

import { $, on } from '../utils/dom.js';
import { ajaxUpload, ajaxPost } from '../utils/ajax.js';
import { showNotification } from '../utils/notification.js';

const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

/**
 * Initialize avatar upload functionality
 */
export function init() {
    const input = $('#avatar-input');
    const removeBtn = $('#remove-avatar-btn');

    if (input) {
        on(input, 'change', handleFileSelect);
    }

    if (removeBtn) {
        on(removeBtn, 'click', handleRemove);
    }
}

/**
 * Handle file selection
 *
 * @param {Event} e - Change event
 */
async function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validate file type
    if (!ALLOWED_TYPES.includes(file.type)) {
        showNotification('Invalid file type. Please upload JPG, PNG, GIF, or WebP.', 'error');
        e.target.value = '';
        return;
    }

    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
        showNotification('File too large. Maximum size is 2MB.', 'error');
        e.target.value = '';
        return;
    }

    // Show preview immediately
    showPreview(file);

    // Upload file
    await uploadAvatar(file);

    // Reset input
    e.target.value = '';
}

/**
 * Show image preview
 *
 * @param {File} file - Selected file
 */
function showPreview(file) {
    const preview = $('#avatar-preview');
    if (!preview) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = preview.querySelector('img') || document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Avatar preview';
        if (!preview.contains(img)) {
            preview.innerHTML = '';
            preview.appendChild(img);
        }
    };
    reader.readAsDataURL(file);
}

/**
 * Upload avatar to server
 *
 * @param {File} file - File to upload
 */
async function uploadAvatar(file) {
    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const response = await ajaxUpload('upload_avatar', formData);

        if (response.success) {
            showNotification(response.data.message, 'success');

            // Update all avatar instances
            updateAllAvatars(response.data.avatar_url);

            // Show remove button if not visible
            const removeBtn = $('#remove-avatar-btn');
            if (removeBtn) {
                removeBtn.style.display = '';
            }
        } else {
            showNotification(response.data?.message || 'Upload failed', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    }
}

/**
 * Handle avatar removal
 */
async function handleRemove() {
    try {
        const response = await ajaxPost('remove_avatar');

        if (response.success) {
            showNotification(response.data.message, 'success');

            // Update all avatar instances
            updateAllAvatars(response.data.avatar_url);

            // Hide remove button
            const removeBtn = $('#remove-avatar-btn');
            if (removeBtn) {
                removeBtn.style.display = 'none';
            }
        } else {
            showNotification(response.data?.message || 'Failed to remove avatar', 'error');
        }
    } catch (error) {
        console.error('Remove error:', error);
        showNotification('Failed to remove avatar', 'error');
    }
}

/**
 * Update all avatar images on the page
 *
 * @param {string} url - New avatar URL
 */
function updateAllAvatars(url) {
    // Add cache buster
    const cacheBustedUrl = url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now();

    // Update preview
    const preview = $('#avatar-preview img');
    if (preview) {
        preview.src = cacheBustedUrl;
    }

    // Update sidebar avatar
    const sidebarAvatar = $('.cabinet-user-info img');
    if (sidebarAvatar) {
        sidebarAvatar.src = cacheBustedUrl;
    }

    // Update header avatar
    const headerAvatar = $('.user-avatar img');
    if (headerAvatar) {
        headerAvatar.src = cacheBustedUrl;
    }
}

export default { init };
