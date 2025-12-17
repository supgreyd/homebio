<?php
/**
 * Compile PO files to MO files
 *
 * Run this script from the command line:
 * php compile-mo.php
 *
 * Supports plural forms for proper translation of bed/beds, bath/baths etc.
 */

// Prevent direct browser access in production
if (php_sapi_name() !== 'cli' && !defined('WP_DEBUG')) {
    die('This script should only be run from the command line.');
}

/**
 * PO to MO compiler with plural support
 */
class PO_To_MO_Compiler {

    /**
     * Parse PO file and return entries
     */
    public function parse_po($file) {
        $contents = file_get_contents($file);
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
                    $this->save_entry($entries, $entry);
                }
                $entry = [];
                $key = null;
                $plural_index = null;
                continue;
            }

            // Parse msgid
            if (strpos($line, 'msgid ') === 0) {
                // Save previous entry if exists
                if (!empty($entry) && isset($entry['msgid'])) {
                    $this->save_entry($entries, $entry);
                }
                $entry = ['msgid' => ''];
                $key = 'msgid';
                $plural_index = null;
                $entry[$key] = $this->extract_string($line, 'msgid ');
                continue;
            }

            // Parse msgid_plural
            if (strpos($line, 'msgid_plural ') === 0) {
                $key = 'msgid_plural';
                $entry[$key] = $this->extract_string($line, 'msgid_plural ');
                continue;
            }

            // Parse msgstr with index (plural)
            if (preg_match('/^msgstr\[(\d+)\] /', $line, $matches)) {
                $plural_index = (int)$matches[0][7]; // Get the number
                preg_match('/^msgstr\[\d+\] (.*)$/', $line, $str_matches);
                $key = 'msgstr';
                if (!isset($entry['msgstr_plural'])) {
                    $entry['msgstr_plural'] = [];
                }
                $idx = (int)substr($matches[0], 7, 1);
                $entry['msgstr_plural'][$idx] = $this->extract_string($line, $matches[0]);
                continue;
            }

            // Parse msgstr (singular)
            if (strpos($line, 'msgstr ') === 0) {
                $key = 'msgstr';
                $plural_index = null;
                $entry[$key] = $this->extract_string($line, 'msgstr ');
                continue;
            }

            // Continuation line
            if ($line[0] === '"' && !empty($entry)) {
                $str = $this->extract_string($line, '');
                if ($plural_index !== null && isset($entry['msgstr_plural'])) {
                    $entry['msgstr_plural'][$plural_index] .= $str;
                } elseif ($key !== null && isset($entry[$key])) {
                    $entry[$key] .= $str;
                }
            }
        }

        // Don't forget the last entry
        if (!empty($entry) && isset($entry['msgid'])) {
            $this->save_entry($entries, $entry);
        }

        return $entries;
    }

    /**
     * Save parsed entry to entries array
     */
    private function save_entry(&$entries, $entry) {
        if (!isset($entry['msgid'])) {
            return;
        }

        // Plural entry
        if (isset($entry['msgid_plural']) && isset($entry['msgstr_plural'])) {
            // For MO files, plural originals are: singular\0plural
            $original = $entry['msgid'] . "\0" . $entry['msgid_plural'];
            // Plural translations are: form0\0form1\0form2...
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
    private function extract_string($line, $prefix) {
        $line = substr($line, strlen($prefix));
        $line = trim($line, '"');
        $line = stripcslashes($line);
        return $line;
    }

    /**
     * Write MO file
     */
    public function write_mo($entries, $file) {
        // Remove empty msgid (header)
        $header = isset($entries['']) ? $entries[''] : '';
        unset($entries['']);

        // Sort entries by original string
        ksort($entries);

        $originals = array_keys($entries);
        $translations = array_values($entries);

        $count = count($originals);

        // Add header as first entry
        if (!empty($header)) {
            array_unshift($originals, '');
            array_unshift($translations, $header);
            $count++;
        }

        // Calculate offsets
        $offset_orig = 28;
        $offset_trans = $offset_orig + $count * 8;
        $offset_strings = $offset_trans + $count * 8;

        // Build string tables
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

        // Write MO file
        $mo = pack('V', 0x950412de); // Magic number
        $mo .= pack('V', 0); // Revision
        $mo .= pack('V', $count); // Number of strings
        $mo .= pack('V', 28); // Offset of original strings table
        $mo .= pack('V', 28 + $count * 8); // Offset of translation strings table
        $mo .= pack('V', 0); // Size of hash table
        $mo .= pack('V', 28 + $count * 16); // Offset of hash table

        // Write original string offsets
        foreach ($orig_offsets as $offset) {
            $mo .= pack('V', $offset[0]); // Length
            $mo .= pack('V', $offset[1]); // Offset
        }

        // Write translation string offsets
        foreach ($trans_offsets as $offset) {
            $mo .= pack('V', $offset[0]); // Length
            $mo .= pack('V', $offset[1]); // Offset
        }

        // Write strings
        $mo .= $orig_table;
        $mo .= $trans_table;

        return file_put_contents($file, $mo) !== false;
    }

    /**
     * Compile PO to MO
     */
    public function compile($po_file, $mo_file = null) {
        if ($mo_file === null) {
            $mo_file = preg_replace('/\.po$/', '.mo', $po_file);
        }

        $entries = $this->parse_po($po_file);
        if ($entries === false) {
            return false;
        }

        return $this->write_mo($entries, $mo_file);
    }
}

// Run compiler
$compiler = new PO_To_MO_Compiler();
$languages_dir = __DIR__;

$po_files = glob($languages_dir . '/*.po');

echo "HomeBio MO Compiler\n";
echo "==================\n\n";

foreach ($po_files as $po_file) {
    $filename = basename($po_file);
    $mo_file = preg_replace('/\.po$/', '.mo', $po_file);

    echo "Compiling $filename... ";

    if ($compiler->compile($po_file, $mo_file)) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

echo "\nDone!\n";
