<?php
date_default_timezone_set('Asia/Manila');
include('./components/connect.php');
include('./components/mailerFP.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);

    $select = mysqli_query($con, "SELECT * FROM grace_user WHERE email = '$email'") or die('query failed');
    if(mysqli_num_rows($select) > 0){
        $row = mysqli_fetch_assoc($select);
        
        // 1. Check if account is active
        if ($row['is_active'] == 0) {
            echo "<script>alert('Please activate your account first.');</script>";
        } 
        // 2. Generate OTP and save it in the database
        else {
            $otp = random_int(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // 10 minutes expiry

            $update = mysqli_query($con, "UPDATE grace_user SET otp ='$otp', otp_expiry = '$expiry' WHERE email = '$email'") or die('query failed');

            if ($update) {
                sendOTP($email, $otp);  
                echo "<script>alert('OTP has been sent to your email. (Test OTP: $otp)');</script>";
                // Optional: Store email in session so we know who is verifying
                $_SESSION['reset_email'] = $email;
                header("Location: forgot-password-verify.php");
                exit();
            } else {
                echo "<script>alert('Failed to generate OTP. Please try again.');</script>";
            }
        }
    } else {
        // Security: Don't reveal if email exists or not
        echo "<script>alert('If your email is registered, you will receive an OTP.');</script>";
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
                <h1 style="text-align: center;">Forgot Password</h1>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="submit" value="Send OTP" class="btn" name="submit">
                <p style="text-align: center;">Login <a href="login.php">here</a></p>
            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
