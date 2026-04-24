<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$status = "";
$email_display = $_GET['email'] ?? '';

// Handle direct link activation (GET token)
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($con, $_GET['token']);
    $query = "SELECT * FROM grace_user WHERE activation_token = '$token' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if ($user['is_active'] == 1) {
            $message = "Account already activated. Please login.";
            $status = "info";
        } else {
            $update_query = "UPDATE grace_user SET is_active = 1, activation_token = NULL WHERE userID = " . $user['userID'];
            if (mysqli_query($con, $update_query)) {
                $message = "Account activated successfully! You can now login.";
                $status = "success";
            } else {
                $message = "Activation failed. Please contact support.";
                $status = "error";
            }
        }
    } else {
        $message = "Invalid activation token.";
        $status = "error";
    }
}

// Handle OTP form submission
if (isset($_POST['submit_otp'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $otp = mysqli_real_escape_string($con, $_POST['otp']);

    $query = "SELECT u.* FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE e.email = '$email' AND u.activation_token = '$otp' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $update_query = "UPDATE grace_user SET is_active = 1, activation_token = NULL WHERE userID = " . $user['userID'];
        if (mysqli_query($con, $update_query)) {
            $message = "Account activated successfully! You can now login.";
            $status = "success";
        } else {
            $message = "Activation failed. Please try again.";
            $status = "error";
        }
    } else {
        $message = "Incorrect activation code or email.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation - Grace Street</title>
    <link rel="stylesheet" href="Css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .activate-container {
            max-width: 500px;
            margin: 100px auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .activate-container h1 { margin-bottom: 20px; color: #333; }
        .activate-container p { margin-bottom: 30px; color: #666; font-size: 16px; }
        .box {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            text-align: center;
            letter-spacing: 2px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover { background: #333; }
        .resend-link { margin-top: 20px; display: block; color: #d35400; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'additional/loginheader.php'; ?>

    <section>
        <div class="activate-container">
            <h1>Activate Your Account</h1>
            <p>Please enter the 6-digit code sent to your email address.</p>
            
            <form action="" method="post">
                <input type="email" name="email" value="<?= htmlspecialchars($email_display) ?>" required placeholder="Enter your email" class="box" style="letter-spacing: normal;">
                <input type="text" name="otp" required placeholder="Enter 6-digit code" maxlength="6" class="box">
                <input type="submit" name="submit_otp" value="Verify & Activate" class="btn">
            </form>

            <a href="resend_activation.php" class="resend-link">Didn't receive the code? Resend it</a>
            <p style="margin-top: 20px;">Back to <a href="login.php">Login</a></p>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>

    <?php if ($message): ?>
    <script>
        Swal.fire({
            icon: '<?= $status ?>',
            title: '<?= ucfirst($status) ?>',
            text: '<?= $message ?>',
            confirmButtonColor: '#000'
        }).then((result) => {
            if (result.isConfirmed && ('<?= $status ?>' === 'success' || '<?= $status ?>' === 'info')) {
                window.location.href = 'login.php';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
