<?php
include('components/connect.php');

$defaults = [
    'pw_min_length' => '12',
    'pw_min_uppercase' => '1',
    'pw_min_lowercase' => '1',
    'pw_min_numbers' => '1',
    'pw_min_symbols' => '1',
    'max_login_attempts' => '3'
];

// Create table if not exists
mysqli_query($con, "CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
)");

foreach ($defaults as $key => $value) {
    mysqli_query($con, "INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')");
}

echo "Password complexity settings initialized/updated.";
?>
