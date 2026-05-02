<?php
include('./components/connect.php');
require 'GoogleAuthenticator.php';   // Make sure this file is in the same folder

// Redirect if user is already 2FA verified
if (isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === true) {
    $role_name = strtolower(trim($_SESSION['role'] ?? 'customer'));
    if ($role_name === 'admin') {
        header("Location: ./admin/overview.php");
    } else if ($role_name === 'employee') {
        header("Location: ./seller/dashboard.php");
    } else {
        header("Location: home.php");
    }
    exit;
}

// Redirect if user is not logged in
if (!isset($_SESSION['user-id']) || empty($_SESSION['user-id'])) {
    header("Location: login.php");
    exit;
}

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

// Generate a temporary/current TOTP code for convenience (temporary use only)
$display_code = $GA->getCode($secret);

$message = '';

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
        
        // Ensure session is saved before redirect
        session_write_close();

        // Role-based redirection
        $role_name = strtolower(trim($_SESSION['role'] ?? 'user'));
        
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
        form {
            width: 100%;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
        .loginuser-container1 {
            text-align: center;
            max-width: 400px;
            margin: 40px auto 175px;
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
            box-sizing: border-box;
            padding: 12px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 5px;
            border: 0.5px solid #E8DED2;
            border-radius: 5px;
            margin: 15px 0;
            background: rgba(232,222,210,0.2);
            color: #2C2825;
            font-family: 'Jost', sans-serif;
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
            box-sizing: border-box;
        }
        .verify-btn:hover {
            background: #8B6F56;
        }
        .temp-code {
            margin: 12px 0 6px;
            padding: 10px 14px;
            background: rgba(232,222,210,0.3) !important;
            border: 0.5px dashed #D4C5B0 !important;
            font-family: 'Courier New', monospace;
            font-size: 22px;
            letter-spacing: 6px;
            text-align: center;
            color: #2C2825 !important;;
            border-radius: 6px;
        }
        .temp-note {
            font-size: 12px;
            color: #A09486;
            text-align: center;
            margin-bottom: 8px;
        }
        .loginuser-container1 form {
            width: 100%;
            box-sizing: border-box;
            border: none;
            background: transparent;
            padding: 0;
            margin: 0;
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
            
            <?php if (!empty($display_code)): ?>
                <div class="temp-note">Temporary code (for convenience only). Do not enable in production.</div>
                <div class="temp-code"><?= htmlspecialchars($display_code) ?></div>
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
