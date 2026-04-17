<?php

include('./components/connect.php');
include('./components/mailerFP.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$email = $_SESSION['reset_email'] ?? '';
if (empty($email)) {
    header("Location: forgot-password-send.php");
    exit();
}

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pass = $_POST['pass'];

    $select = mysqli_query($con, "SELECT * FROM grace_user WHERE email = '$email'") or die('query failed');
    if(mysqli_num_rows($select) > 0){
        $row = mysqli_fetch_assoc($select);
    
        if (password_verify($pass, $row['password']) || sha1($pass) === $row['password']) {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($con, "UPDATE grace_user SET password = '$hashed_password' WHERE email = '$email'") or die('query failed');
            unset($_SESSION['reset_email']);
            echo "<script>alert('Password reset successful. Please login with your new password.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Password reset failed. Please try again.');</script>";
        }
    }else{
        echo "<script>alert('Incorrect password or email');</script>";
    }
}

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
                <h1 style="text-align: center;">Reset Password</h1>
                
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" required placeholder="Enter your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="submit" value="Reset Password" class="btn" name="reset_password">
            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
