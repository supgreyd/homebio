/**
 * AJAX Utility Module
 *
 * Provides a reusable AJAX helper for making WordPress AJAX requests.
 *
 * @module utils/ajax
 */

/**
 * Make a POST request to WordPress AJAX endpoint
 *
 * @param {string} action - WordPress AJAX action name
 * @param {Object} data - Additional data to send
 * @returns {Promise<Object>} Response data
 */
export async function ajaxPost(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('nonce', window.homebioAjax?.nonce || '');

    // Append additional data
    Object.entries(data).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            formData.append(key, value);
        }
    });

    try {
        const response = await fetch(window.homebioAjax?.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

/**
 * Make a POST request with FormData (for file uploads)
 *
 * @param {string} action - WordPress AJAX action name
 * @param {FormData} formData - FormData object with file and other data
 * @returns {Promise<Object>} Response data
 */
export async function ajaxUpload(action, formData) {
    formData.append('action', action);
    formData.append('nonce', window.homebioAjax?.nonce || '');

    try {
        const response = await fetch(window.homebioAjax?.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Upload Error:', error);
        throw error;
    }
}

export default {
    post: ajaxPost,
    upload: ajaxUpload
};
