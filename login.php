<?php

include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pass = $_POST['pass'];

    $select = mysqli_query($con, "SELECT grace_user.*, email.email FROM grace_user JOIN email ON grace_user.emailID = email.emailID WHERE email.email = '$email'") or die('query failed');
    if(mysqli_num_rows($select) > 0){
        $row = mysqli_fetch_assoc($select);
        
        // 1. Check if account is active
        if ($row['is_active'] == 0) {
            $user_email_enc = urlencode($email);
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Account Inactive',
                    text: 'Your account is not activated yet. Would you like to enter your activation code?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Activate Now',
                    cancelButtonText: 'Contact Support',
                    confirmButtonColor: '#000'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'activate.php?email=$user_email_enc';
                    }
                });
            </script>";
        } 
        // 2. Verify password
        elseif (password_verify($pass, $row['password']) || sha1($pass) === $row['password']) {
            // Reset failed attempts on success
            $user_id_actual = $row['userID'];
            mysqli_query($con, "UPDATE grace_user SET login_attempts = 0 WHERE userID = '$user_id_actual'");

            $_SESSION['user-id'] = $row['userID'];
            $_SESSION['user-email'] = $email;
            
            // Log successful login
            include_once './components/audit_logger.php';
            log_audit('User Login', $row['userID'], $email, 'Info');
            
            // Fetch role name from roles table
            $role_id = $row['roleID'];
            $role_query = mysqli_query($con, "SELECT role FROM roles WHERE roleID = '$role_id'");
            $role_row = mysqli_fetch_assoc($role_query);
            $role_name = $role_row['role'];
            $_SESSION['role'] = $role_name;

            // Ensure session is saved before redirect
            session_write_close();

            // Redirect all roles to 2FA verification
            header("Location: login-authenticator.php");
            exit(); 
        } else {
            // Fetch max attempts from settings
            $setting_query = mysqli_query($con, "SELECT setting_value FROM system_settings WHERE setting_key = 'max_login_attempts'");
            $setting_row = mysqli_fetch_assoc($setting_query);
            $max_attempts = $setting_row ? (int)$setting_row['setting_value'] : 3;

            // Increment failed attempts
            $user_id_actual = $row['userID'];
            $new_attempts = $row['login_attempts'] + 1;
            
            if ($new_attempts >= $max_attempts) {
                $username_locked = $row['username'];
                mysqli_query($con, "UPDATE grace_user SET login_attempts = $new_attempts, is_active = 0 WHERE userID = '$user_id_actual'");
                mysqli_query($con, "INSERT INTO locked_accounts (username, tries) VALUES ('$username_locked', $new_attempts)");
                echo "<script>alert('Your account has been locked. Please contact support.');</script>";
            } else {
                mysqli_query($con, "UPDATE grace_user SET login_attempts = $new_attempts WHERE userID = '$user_id_actual'");
                echo "<script>alert('Incorrect password or email');</script>";
            }
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


    <link rel="stylesheet" href="Css/style.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    if (isset($_GET['timeout'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Session Expired',
                    text: 'Your session has timed out due to inactivity. Please login again.',
                    confirmButtonColor: '#000'
                });
            });
        </script>";
    }
    ?>
</head>
<style>
    .password-container {
        position: relative;
        margin-bottom: 20px;
        width: 100%;
    }
    .password-container .box {
        margin-bottom: 0;
        padding-right: 40px;
        width: 100%; /* was 89.6% */
        box-sizing: border-box;
    }
    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
        z-index: 10;
    }
</style>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="loginuser-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Login Now</h1>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                
                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                    <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                
                <input type="submit" value="Login Now" class="btn" name="submit">
                <p style="text-align: center;">Forgot your password? <a href="forgot-password-send.php">Reset it here</a></p>
                <p style="text-align: center;">Don't have an account? <a href="register.php">Register</a></p>
                <p style="text-align: center;"><a href="seller_register.php">Sign in as seller?</a></p>
                <p style="text-align: center;">Didn't receive an activation email? <a href="resend_activation.php">Resend activation link</a></p>
            </form>

        </div>
    </section>  
    <?php include 'additional/footer.php'; ?>
    <script>
        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    </script>
</body>
</html>
