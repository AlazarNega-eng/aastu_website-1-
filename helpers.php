<?php
// helpers.php

if (!function_exists('getValue')) { // Prevent redeclaration errors
    /**
     * Safely get a value from an array (like form data) and sanitize for HTML output.
     *
     * @param string $field The key to look for in the array.
     * @param array|null $data The array containing the data (e.g., $_POST, $db_row).
     * @param mixed $default The default value to return if the key is not found or data is not an array.
     * @return string The sanitized value or the sanitized default value.
     */
    function getValue(string $field, ?array $data, $default = ''): string {
        // Return default immediately if data isn't a valid array
        if (!is_array($data)) {
            return htmlspecialchars((string)$default); // Ensure default is string for htmlspecialchars
        }
        // Check if the key exists and the value is not null
        $value = $data[$field] ?? $default; // Use null coalescing operator

        // Ensure the value is treated as a string before sanitizing
        return htmlspecialchars((string)$value);
    }
}

// Add any other reusable helper functions here...

// NO closing ?> tag needed