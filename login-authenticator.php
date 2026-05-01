<?php
session_start();
require 'GoogleAuthenticator.php';   // Make sure this file is in the same folder

// Redirect if user is not logged in
if (!isset($_SESSION['user-id']) || empty($_SESSION['user-id'])) {
    header("Location: login.php");
    exit;
}

include('./components/connect.php');

$GA = new PHPGangsta_GoogleAuthenticator();
$userId = $_SESSION['user-id'];

// Fetch user data including 2FA secret
$user_query = mysqli_query($con, "SELECT u.two_factor_secret, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE u.userID = '$userId'");
$user_data = mysqli_fetch_assoc($user_query);
$useremail = $user_data['email'] ?? "";
$db_secret = $user_data['two_factor_secret'] ?? null;

$is_first_time = empty($db_secret);

// If secret exists in DB, use it. Otherwise, generate a temporary one for setup.
if (!$is_first_time) {
    $secret = $db_secret;
} else {
    if (!isset($_SESSION['temp_secret']) || empty($_SESSION['temp_secret'])) {
        $_SESSION['temp_secret'] = $GA->createSecret();
    }
    $secret = $_SESSION['temp_secret'];
}

$issuer = 'Grace Street Clothing';

// Generate QR Code URL only if it's the first time
$qrCodeUrl = "";
if ($is_first_time) {
    $qrCodeUrl = $GA->getQRCodeGoogleUrl($useremail, $secret, $issuer);
}

$message = '';

// For testing/display purposes: generate the current TOTP code (changes every 30s)
$current_code = $GA->getCode($secret);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    $code = trim($_POST['code']);
    
    // Verify the code (with 3*30sec tolerance)
    $valid = $GA->verifyCode($secret, $code, 3);
    
    if ($valid) {
        // If it was the first time, save the secret to the database
        if ($is_first_time) {
            mysqli_query($con, "UPDATE grace_user SET two_factor_secret = '$secret' WHERE userID = '$userId'");
            unset($_SESSION['temp_secret']);
        }
        
        $_SESSION['2fa_verified'] = true;
        $_SESSION['verified'] = true;
        
        // Role-based redirection
        $role_name = $_SESSION['role'] ?? 'customer';
        
        if ($role_name === 'admin') {
            header("Location: ./admin/overview.php");
        } else if ($role_name === 'employee') {
            header("Location: ./seller/dashboard.php");
        } else {
            header("Location: home.php");
        }
        exit;
    } else {
        $message = '<p class="error">Invalid code! Please try again.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="Css/style.css">
    
    <style>
        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
        .loginuser-container1 {
            text-align: center;
            max-width: 400px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(247, 243, 238, 0.85);
            border-radius: 12px;
            border: 0.5px solid #D4C5B0;
            box-shadow: 0 8px 24px rgba(44, 40, 37, 1);
        }
        .qr-section {
            margin-bottom: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        img {
            margin: 15px 0;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
        }
        .code-input {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 15px 0;
        }
        .verify-btn {
            width: 100%;
            padding: 15px;
            background: #2C2825;
            color: #F7F3EE;
            border: none;
            border-radius: 0;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: background-color 0.25s;
        }
        .verify-btn:hover {
            background: #8B6F56;
        }
    </style>
</head>
<body>

    <?php include 'additional/loginheader.php'; ?>

    <section>
        <div class="loginuser-container1">
            <h2>Two-Factor Authentication</h2>
            
            <?php if ($is_first_time): ?>
                <div class="qr-section">
                    <p>First-time setup: Scan this QR code with your Google Authenticator app:</p>
                    <img src="<?= htmlspecialchars($qrCodeUrl) ?>" 
                         alt="Google Authenticator QR Code" 
                         width="220" 
                         height="220">
                    <p style="font-size: 12px; color: #666;">Once you scan this, it won't be shown again.</p>
                </div>
            <?php else: ?>
                <div style="margin: 20px 0;">
                    <p>Enter the 6-digit code from your Authenticator app to continue.</p>
                </div>
            <?php endif; ?>
            
            <!-- Display current authenticator code (visible for testing) -->
            <?php if (!empty($current_code)): ?>
                <div style="text-align:center; margin: 12px 0;">
                    <p style="margin:0; font-weight:600; color:#333;">Current authenticator code (visible):</p>
                    <div style="font-size:28px; letter-spacing:8px; background:#f7f7f7; padding:10px 18px; display:inline-block; border-radius:6px; margin-top:8px;">
                        <?= htmlspecialchars($current_code) ?>
                    </div>
                    <p style="font-size:12px; color:#666; margin:6px 0 0;">This code refreshes every 30 seconds.</p>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="text" 
                       name="code" 
                       class="code-input"
                       maxlength="6" 
                       placeholder="000000"
                       pattern="\d{6}" 
                       inputmode="numeric"
                       required 
                       autofocus>
                
                <button type="submit" class="verify-btn">Verify & Continue</button>
            </form>
            
            <?= $message ?>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>

</body>
</html>
