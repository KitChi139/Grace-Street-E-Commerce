<?php
session_start();
require 'GoogleAuthenticator.php';   // Make sure this file is in the same folder

// Redirect if user is not logged in
if (!isset($_SESSION['user-email']) || empty($_SESSION['user-email'])) {
    header("Location: login.php");
    exit;
}

include('./components/connect.php');

$GA = new PHPGangsta_GoogleAuthenticator();
$useremail = $_SESSION['user-email'];

// Generate secret only once per session
if (!isset($_SESSION['code']) || empty($_SESSION['code'])) {
    $_SESSION['code'] = $GA->createSecret();
}

$secret = $_SESSION['code'];
$issuer = 'AdminIndex';

// Generate QR Code URL
$qrCodeUrl = $GA->getQRCodeGoogleUrl($useremail, $secret, $issuer);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    $code = trim($_POST['code']);
    
    // Verify the code (with 3*30sec tolerance)
    $valid = $GA->verifyCode($secret, $code, 3);
    
    if ($valid) {
        unset($_SESSION['code']);           // Remove secret after successful verification
        $_SESSION['2fa_verified'] = true;
        $_SESSION['verified'] = true;
        
        header("Location: home.php");
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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
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
            padding: 20px;
        }
        img {
            margin: 15px 0;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
        }
    </style>
</head>
<body>

    <?php include 'additional/loginheader.php'; ?>

    <section>
        <div class="loginuser-container1">
            <h2>Two-Factor Authentication</h2>
            <p>Scan this QR code with Google Authenticator app:</p>
            
            <img src="<?= htmlspecialchars($qrCodeUrl) ?>" 
                 alt="Google Authenticator QR Code" 
                 width="220" 
                 height="220">
            
            <hr>
            
            <form method="POST" autocomplete="off">
                <label>
                    Enter the 6-digit code from the app:
                </label>
                
                <input type="text" 
                       name="code" 
                       maxlength="6" 
                       pattern="\d{6}" 
                       inputmode="numeric"
                       required 
                       autofocus>
                
                <button type="submit">Verify Code</button>
            </form>
            
            <?= $message ?>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>

</body>
</html>