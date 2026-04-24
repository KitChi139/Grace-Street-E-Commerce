<?php
date_default_timezone_set('Asia/Manila');
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$email = $_SESSION['reset_email'] ?? '';

if (empty($email)) {
    header("Location: forgot-password-send.php");
    exit();
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($con, trim($_POST['otp']));

    if (empty($entered_otp)) {
        echo "<script>alert('OTP is required.');</script>";
    } else {
       $query = mysqli_query($con, "SELECT u.*, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE e.email = '$email' AND u.otp = '" . mysqli_real_escape_string($con, $entered_otp) . "' AND u.otp_expiry > NOW()") or die('query failed: ' . mysqli_error($con));
        if (mysqli_num_rows($query) > 0) {
              $row = mysqli_fetch_assoc($query);
              mysqli_query($con, "UPDATE grace_user SET otp = NULL, otp_expiry = NULL WHERE userID = " . $row['userID']);
            header("Location: forgot-password-reset.php?email=" . urlencode($email));
            exit();
        } else {
            echo "<script>alert('Invalid or expired OTP. Please try again.');</script>";
        }
    }
}
?>

?>


<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="loginuser-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Verify OTP</h1>
                
                <p style="text-align:center; margin-bottom:15px;">
                    We sent an OTP to <strong><?= htmlspecialchars($email) ?></strong>
                </p>

                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" 
                       placeholder="Enter 6-digit OTP" maxlength="6" 
                       class="box" inputmode="numeric" required>

                <input type="submit" value="Verify OTP" class="btn" name="verify_otp">

                <p style="text-align: center; margin-top:15px;">
                    Didn't receive OTP? <a href="forgot-password-send.php">Resend</a>
                </p>
            </form>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
