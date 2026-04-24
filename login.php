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
            echo "<script>alert('Please activate your account first. Check your email for the activation link.');</script>";
        } 
        // 2. Verify password (using password_verify for new users and sha1 for old users)
        elseif (password_verify($pass, $row['password']) || sha1($pass) === $row['password']) {
            // If it was sha1, we should ideally rehash it here, but let's keep it simple
            $_SESSION['user-id'] = $row['userID'];
            $_SESSION['user-email'] = $email;
            $_SESSION['code'] = false;     
            
            
            // Fetch role name from roles table
            $role_id = $row['roleID'];
            $role_query = mysqli_query($con, "SELECT role FROM roles WHERE roleID = '$role_id'");
            $role_row = mysqli_fetch_assoc($role_query);
            $role_name = $role_row['role'];

            if ($role_name === 'admin') {
                header('Location: ./admin/overview.php');
                exit(); 
            }else if($role_name === 'employee'){
                header('Location: ./seller/dashboard.php');
                exit(); 
            } else {
                header("Location: login-authenticator.php");
                exit(); 
            }
        } else {
            echo "<script>alert('Incorrect password or email');</script>";
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


    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
        width: 89.6%;
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
