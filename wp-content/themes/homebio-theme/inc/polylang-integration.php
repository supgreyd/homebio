<?php
/**
 * Language/Localization Integration
 *
 * Provides cookie-based language switching without URL changes.
 * Works with WordPress native translation system and MO files.
 *
 * @package HomeBio
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available languages configuration
 */
function homebio_get_available_languages() {
    return [
        'en_US' => [
            'slug' => 'en',
            'label' => 'EN',
            'name' => 'English',
            'flag' => '🇺🇸',
        ],
        'bg_BG' => [
            'slug' => 'bg',
            'label' => 'BG',
            'name' => 'Български',
            'flag' => '🇧🇬',
        ],
        'ru_RU' => [
            'slug' => 'ru',
            'label' => 'RU',
            'name' => 'Русский',
            'flag' => '🇷🇺',
        ],
        'uk' => [
            'slug' => 'uk',
            'label' => 'UA',
            'name' => 'Українська',
            'flag' => '🇺🇦',
        ],
    ];
}

/**
 * Get current locale from user preference or cookie
 */
function homebio_get_preferred_locale() {
    $available = homebio_get_available_languages();

    // 1. Check user meta (for logged-in users)
    if (is_user_logged_in()) {
        $user_locale = get_user_meta(get_current_user_id(), 'homebio_locale', true);
        if ($user_locale && isset($available[$user_locale])) {
            return $user_locale;
        }
    }

    // 2. Check cookie
    if (isset($_COOKIE['homebio_locale'])) {
        $cookie_locale = sanitize_text_field($_COOKIE['homebio_locale']);
        if (isset($available[$cookie_locale])) {
            return $cookie_locale;
        }
    }

    // 3. Default to site language
    return 'en_US';
}

/**
 * Apply language preference after theme setup
 * Loads the correct translation files based on user preference
 */
function homebio_apply_locale() {
    $locale = homebio_get_preferred_locale();

    if ($locale && $locale !== 'en_US') {
        // Switch WordPress locale first
        if (function_exists('switch_to_locale')) {
            switch_to_locale($locale);
        }

        // Unload current textdomain and reload with correct locale
        unload_textdomain('homebio');

        // Load our theme's translation file for this locale
        $mo_file = HOMEBIO_DIR . '/languages/' . $locale . '.mo';

        if (file_exists($mo_file)) {
            load_textdomain('homebio', $mo_file);
        }
    }
}
// Run after theme setup (which runs at priority 10)
add_action('after_setup_theme', 'homebio_apply_locale', 20);

/**
 * Ensure locale filter returns our preferred locale
 */
function homebio_filter_locale($locale) {
    $preferred = homebio_get_preferred_locale();
    if ($preferred) {
        return $preferred;
    }
    return $locale;
}
add_filter('locale', 'homebio_filter_locale', 10);

/**
 * Output language switcher
 */
function homebio_language_switcher() {
    $languages = homebio_get_available_languages();
    $current_locale = homebio_get_preferred_locale();
    ?>
    <div class="language-switcher">
        <select id="language-selector" class="language-select">
            <?php foreach ($languages as $locale => $data) : ?>
                <option value="<?php echo esc_attr($locale); ?>"
                        <?php selected($locale, $current_locale); ?>>
                    <?php echo esc_html($data['label']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * Alias for compatibility
 */
function homebio_polylang_language_switcher() {
    homebio_language_switcher();
}

/**
 * AJAX handler for language switching
 */
function homebio_ajax_switch_language() {
    check_ajax_referer('homebio_nonce', 'nonce');

    $locale = isset($_POST['locale']) ? sanitize_text_field($_POST['locale']) : '';

    $available = homebio_get_available_languages();

    if (empty($locale) || !isset($available[$locale])) {
        wp_send_json_error(['message' => __('Invalid language', 'homebio')]);
    }

    // Store user preference
    if (is_user_logged_in()) {
        update_user_meta(get_current_user_id(), 'homebio_locale', $locale);
    }

    // Set cookie (works for both logged-in and guests)
    // Use setcookie with proper parameters
    $cookie_set = setcookie(
        'homebio_locale',
        $locale,
        [
            'expires' => time() + YEAR_IN_SECONDS,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN ?: '',
            'secure' => is_ssl(),
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );

    wp_send_json_success([
        'message' => __('Language changed', 'homebio'),
        'locale' => $locale,
        'reload' => true,
    ]);
}
add_action('wp_ajax_switch_language', 'homebio_ajax_switch_language');
add_action('wp_ajax_nopriv_switch_language', 'homebio_ajax_switch_language');

/**
 * Auto-compile MO files from PO files if they don't exist or are outdated
 */
function homebio_auto_compile_mo_files() {
    $languages_dir = HOMEBIO_DIR . '/languages';

    // Only run in admin
    if (!is_admin()) {
        return;
    }

    // Check version - recompile if theme version changed
    $compiled_version = get_option('homebio_mo_compile_version', '');
    $current_version = defined('HOMEBIO_VERSION') ? HOMEBIO_VERSION : '1.0.0';

    // Also check once per day
    $last_check = get_option('homebio_mo_compile_check', 0);
    $needs_check = (time() - $last_check > DAY_IN_SECONDS) || ($compiled_version !== $current_version);

    if (!$needs_check) {
        return;
    }

    update_option('homebio_mo_compile_check', time());
    update_option('homebio_mo_compile_version', $current_version);

    $po_files = glob($languages_dir . '/*.po');

    foreach ($po_files as $po_file) {
        $mo_file = preg_replace('/\.po$/', '.mo', $po_file);

        // Compile if MO doesn't exist or PO is newer or version changed
        if (!file_exists($mo_file) || filemtime($po_file) > filemtime($mo_file) || $compiled_version !== $current_version) {
            homebio_compile_po_to_mo($po_file, $mo_file);
        }
    }
}
add_action('admin_init', 'homebio_auto_compile_mo_files');

/**
 * PO to MO compiler with plural form support
 */
function homebio_compile_po_to_mo($po_file, $mo_file) {
    $contents = file_get_contents($po_file);
    if ($contents === false) {
        return false;
    }

    $entries = [];
    $entry = [];
    $key = null;
    $plural_index = null;

    $lines = explode("\n", $contents);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments
        if (!empty($line) && $line[0] === '#') {
            continue;
        }

        // Empty line - save current entry
        if (empty($line)) {
            if (!empty($entry) && isset($entry['msgid'])) {
                homebio_save_po_entry($entries, $entry);
            }
            $entry = [];
            $key = null;
            $plural_index = null;
            continue;
        }

        // Parse msgid
        if (strpos($line, 'msgid ') === 0) {
            if (!empty($entry) && isset($entry['msgid'])) {
                homebio_save_po_entry($entries, $entry);
            }
            $entry = ['msgid' => ''];
            $key = 'msgid';
            $plural_index = null;
            $entry[$key] = homebio_extract_po_string($line, 'msgid ');
            continue;
        }

        // Parse msgid_plural
        if (strpos($line, 'msgid_plural ') === 0) {
            $key = 'msgid_plural';
            $entry[$key] = homebio_extract_po_string($line, 'msgid_plural ');
            continue;
        }

        // Parse msgstr with index (plural forms)
        if (preg_match('/^msgstr\[(\d+)\]\s/', $line, $matches)) {
            $plural_index = (int)$matches[1];
            $key = 'msgstr_plural';
            if (!isset($entry['msgstr_plural'])) {
                $entry['msgstr_plural'] = [];
            }
            $prefix = 'msgstr[' . $plural_index . '] ';
            $entry['msgstr_plural'][$plural_index] = homebio_extract_po_string($line, $prefix);
            continue;
        }

        // Parse msgstr (singular)
        if (strpos($line, 'msgstr ') === 0) {
            $key = 'msgstr';
            $plural_index = null;
            $entry[$key] = homebio_extract_po_string($line, 'msgstr ');
            continue;
        }

        // Continuation line
        if (!empty($line) && $line[0] === '"' && !empty($entry)) {
            $str = homebio_extract_po_string($line, '');
            if ($plural_index !== null && isset($entry['msgstr_plural'][$plural_index])) {
                $entry['msgstr_plural'][$plural_index] .= $str;
            } elseif ($key !== null && isset($entry[$key])) {
                $entry[$key] .= $str;
            }
        }
    }

    // Don't forget the last entry
    if (!empty($entry) && isset($entry['msgid'])) {
        homebio_save_po_entry($entries, $entry);
    }

    return homebio_write_mo_file($entries, $mo_file);
}

/**
 * Save parsed PO entry to entries array
 */
function homebio_save_po_entry(&$entries, $entry) {
    if (!isset($entry['msgid'])) {
        return;
    }

    // Plural entry
    if (isset($entry['msgid_plural']) && isset($entry['msgstr_plural'])) {
        $original = $entry['msgid'] . "\0" . $entry['msgid_plural'];
        ksort($entry['msgstr_plural']);
        $translation = implode("\0", $entry['msgstr_plural']);
        $entries[$original] = $translation;
    }
    // Singular entry
    elseif (isset($entry['msgstr'])) {
        $entries[$entry['msgid']] = $entry['msgstr'];
    }
}

/**
 * Extract string from PO line
 */
function homebio_extract_po_string($line, $prefix) {
    $line = substr($line, strlen($prefix));
    $line = trim($line, '"');
    $line = stripcslashes($line);
    return $line;
}

/**
 * Write MO file
 */
function homebio_write_mo_file($entries, $file) {
    $header = isset($entries['']) ? $entries[''] : '';
    unset($entries['']);

    ksort($entries);

    if (!empty($header)) {
        $entries = ['' => $header] + $entries;
    }

    $originals = array_keys($entries);
    $translations = array_values($entries);
    $count = count($originals);

    $offset_orig = 28;
    $offset_trans = $offset_orig + $count * 8;
    $offset_strings = $offset_trans + $count * 8;

    $orig_table = '';
    $trans_table = '';
    $orig_offsets = [];
    $trans_offsets = [];
    $current_offset = $offset_strings;

    foreach ($originals as $i => $orig) {
        $orig_offsets[$i] = [strlen($orig), $current_offset];
        $orig_table .= $orig . "\0";
        $current_offset += strlen($orig) + 1;
    }

    foreach ($translations as $i => $trans) {
        $trans_offsets[$i] = [strlen($trans), $current_offset];
        $trans_table .= $trans . "\0";
        $current_offset += strlen($trans) + 1;
    }

    $mo = pack('V', 0x950412de);
    $mo .= pack('V', 0);
    $mo .= pack('V', $count);
    $mo .= pack('V', 28);
    $mo .= pack('V', 28 + $count * 8);
    $mo .= pack('V', 0);
    $mo .= pack('V', 28 + $count * 16);

    foreach ($orig_offsets as $offset) {
        $mo .= pack('V', $offset[0]);
        $mo .= pack('V', $offset[1]);
    }

    foreach ($trans_offsets as $offset) {
        $mo .= pack('V', $offset[0]);
        $mo .= pack('V', $offset[1]);
    }

    $mo .= $orig_table;
    $mo .= $trans_table;

    return file_put_contents($file, $mo) !== false;
}

/**
 * Get current language code (2-letter)
 */
function homebio_get_current_language() {
    $locale = homebio_get_preferred_locale();
    $languages = homebio_get_available_languages();

    if (isset($languages[$locale])) {
        return $languages[$locale]['slug'];
    }

    return 'en';
}

/**
 * Get current locale
 */
function homebio_get_current_locale() {
    return homebio_get_preferred_locale();
}

/**
 * Get available languages (compatibility function)
 */
function homebio_get_languages() {
    return homebio_get_available_languages();
}

/**
 * Check if Polylang is active (for compatibility - returns false now)
 */
function homebio_is_polylang_active() {
    return false;
}
