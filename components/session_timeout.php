<?php
/**
 * Session Timeout and Auto-Logout Logic
 * This file should be included in connect.php to ensure it runs on every request.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timeout duration (e.g., 30 minutes = 1800 seconds)
// You can adjust this value as needed.
$timeout_duration = 1800; 

$user_id = $_SESSION['user-id'] ?? $_SESSION['user_id'] ?? null;

if ($user_id) {
    // 2FA Enforcement Logic
    // Check if 2FA is verified for the logged-in user
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    $is_2fa_verified = $_SESSION['2fa_verified'] ?? false;
    
    // Pages that are allowed even if 2FA is not yet verified
    $allowed_pages = [
        'login-authenticator.php', 
        'login.php', 
        'logout.php', 
        'register.php', 
        'activate.php', 
        'resend_activation.php',
        'forgot-password-send.php',
        'forgot-password-verify.php',
        'forgot-password-reset.php',
        'captcha_gen.php',
        'seller_register.php',
        'admin_login.php'
    ];
    
    // Normalize current script name to handle subdirectories correctly for the check
    $is_allowed = false;
    foreach ($allowed_pages as $page) {
        if (strpos($_SERVER['SCRIPT_NAME'], $page) !== false) {
            $is_allowed = true;
            break;
        }
    }
    
    // If user is logged in but not 2FA verified, and trying to access a restricted page
    if (!$is_2fa_verified && !$is_allowed) {
        // Determine redirect path to login-authenticator.php
        if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/seller/') !== false) {
            $redirect_2fa = "../login-authenticator.php";
        } else {
            $redirect_2fa = "login-authenticator.php";
        }
        header("Location: $redirect_2fa");
        exit();
    }

    // Check if last activity is set
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];
        
        if ($elapsed_time > $timeout_duration) {
            // Session expired
            session_unset();
            session_destroy();
            
            // Determine the redirect path to login.php
            $current_script = $_SERVER['SCRIPT_NAME'];
            
            // Check if we are in a subdirectory (admin or seller)
            if (strpos($current_script, '/admin/') !== false || strpos($current_script, '/seller/') !== false) {
                $redirect_url = "../login.php?timeout=1";
            } else {
                $redirect_url = "login.php?timeout=1";
            }
            
            // If the current page IS login.php, don't redirect (avoid infinite loop)
            if (basename($current_script) !== 'login.php' && basename($current_script) !== 'admin_login.php') {
                header("Location: $redirect_url");
                exit();
            }
        }
    }
    
    // Update last activity time stamp
    $_SESSION['last_activity'] = time();
}
?>
