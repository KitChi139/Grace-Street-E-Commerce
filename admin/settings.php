<?php
include('../components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin role
$user_id = $_SESSION['user-id'] ?? $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: ../login.php');
    exit();
}

// Fetch user and their role
$role_check = mysqli_query($con, "SELECT r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'");
$role_row = mysqli_fetch_assoc($role_check);

if (!$role_row || strtolower($role_row['role']) !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = "";

// Handle settings update
if (isset($_POST['update_settings'])) {
    $max_attempts = mysqli_real_escape_string($con, $_POST['max_login_attempts']);
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$max_attempts' WHERE setting_key = 'max_login_attempts'");
    
    // Password complexity settings
    $min_len = mysqli_real_escape_string($con, $_POST['pw_min_length']);
    $min_upper = mysqli_real_escape_string($con, $_POST['pw_min_uppercase']);
    $min_lower = mysqli_real_escape_string($con, $_POST['pw_min_lowercase']);
    $min_num = mysqli_real_escape_string($con, $_POST['pw_min_numbers']);
    $min_sym = mysqli_real_escape_string($con, $_POST['pw_min_symbols']);
    
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$min_len' WHERE setting_key = 'pw_min_length'");
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$min_upper' WHERE setting_key = 'pw_min_uppercase'");
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$min_lower' WHERE setting_key = 'pw_min_lowercase'");
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$min_num' WHERE setting_key = 'pw_min_numbers'");
    mysqli_query($con, "UPDATE system_settings SET setting_value = '$min_sym' WHERE setting_key = 'pw_min_symbols'");

    $message = "Settings updated successfully!";
}

// Fetch current settings
$settings = [];
$setting_query = mysqli_query($con, "SELECT * FROM system_settings");
while($row = mysqli_fetch_assoc($setting_query)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$current_max = $settings['max_login_attempts'] ?? 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings</title>
    <link rel="stylesheet" href="./styles/user_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <?php include 'dashboard_header.php'; ?>
    <main>
        <h1 class="page-title">System Settings</h1>
        <p class="page-subtitle">Configure global platform parameters</p>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="panel" style="max-width: 600px; margin-bottom: 20px;">
            <div class="panel-header">
                <div class="panel-title">Security & Password Policy</div>
            </div>
            <form method="post" style="padding: 20px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600;">Maximum Login Attempts</label>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Failed attempts before account lock.</p>
                    <input type="number" name="max_login_attempts" value="<?php echo $current_max; ?>" min="1" max="10" style="width: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 15px; font-weight: 600;">Password Complexity Requirements</label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; display: block; margin-bottom: 5px;">Minimum Length</label>
                            <input type="number" name="pw_min_length" value="<?php echo $settings['pw_min_length'] ?? 8; ?>" min="4" max="32" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; display: block; margin-bottom: 5px;">Min CAPITAL Letters</label>
                            <input type="number" name="pw_min_uppercase" value="<?php echo $settings['pw_min_uppercase'] ?? 0; ?>" min="0" max="10" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; display: block; margin-bottom: 5px;">Min small letters</label>
                            <input type="number" name="pw_min_lowercase" value="<?php echo $settings['pw_min_lowercase'] ?? 0; ?>" min="0" max="10" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; display: block; margin-bottom: 5px;">Min numbers</label>
                            <input type="number" name="pw_min_numbers" value="<?php echo $settings['pw_min_numbers'] ?? 0; ?>" min="0" max="10" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; display: block; margin-bottom: 5px;">Min special characters</label>
                            <input type="number" name="pw_min_symbols" value="<?php echo $settings['pw_min_symbols'] ?? 0; ?>" min="0" max="10" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update_settings" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">Save Security Policy</button>
            </form>
        </div>
    </main>
</body>
</html>
