<?php

/**
 * Validates a password against dynamic system settings.
 * Returns an array: ['valid' => boolean, 'message' => string]
 */
function validatePassword($password, $con) {
    // Fetch settings
    $settings = [];
    $setting_query = mysqli_query($con, "SELECT * FROM system_settings WHERE setting_key LIKE 'pw_%'");
    while($row = mysqli_fetch_assoc($setting_query)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $errors = [];

    // Length check
    if (strlen($password) < (int)$settings['pw_min_length']) {
        $errors[] = "at least " . $settings['pw_min_length'] . " characters";
    }

    // Uppercase check
    $min_upper = (int)($settings['pw_min_uppercase'] ?? 0);
    if ($min_upper > 0) {
        preg_match_all('/[A-Z]/', $password, $matches);
        if (count($matches[0]) < $min_upper) {
            $errors[] = "at least $min_upper CAPITAL letter" . ($min_upper > 1 ? "s" : "");
        }
    }

    // Lowercase check
    $min_lower = (int)($settings['pw_min_lowercase'] ?? 0);
    if ($min_lower > 0) {
        preg_match_all('/[a-z]/', $password, $matches);
        if (count($matches[0]) < $min_lower) {
            $errors[] = "at least $min_lower small letter" . ($min_lower > 1 ? "s" : "");
        }
    }

    // Number check
    $min_numbers = (int)($settings['pw_min_numbers'] ?? 0);
    if ($min_numbers > 0) {
        preg_match_all('/[0-9]/', $password, $matches);
        if (count($matches[0]) < $min_numbers) {
            $errors[] = "at least $min_numbers number" . ($min_numbers > 1 ? "s" : "");
        }
    }

    // Symbol check
    $min_symbols = (int)($settings['pw_min_symbols'] ?? 0);
    if ($min_symbols > 0) {
        preg_match_all('/[@$!%*?&]/', $password, $matches);
        if (count($matches[0]) < $min_symbols) {
            $errors[] = "at least $min_symbols special character" . ($min_symbols > 1 ? "s" : "") . " (@$!%*?&)";
        }
    }

    if (empty($errors)) {
        return ['valid' => true, 'message' => ''];
    } else {
        $msg = "Password must contain " . implode(", ", $errors) . ".";
        return ['valid' => false, 'message' => $msg];
    }
}
?>
