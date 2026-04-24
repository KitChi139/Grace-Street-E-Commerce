<?php

include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$email = $_SESSION['reset_email'] ?? '';
if (empty($email)) {
    header("Location: forgot-password-send.php");
    exit();
}

if(isset($_POST['reset_password'])){
    $pass = trim($_POST['pass'] ?? '');
    $confirm_pass = trim($_POST['confirm_pass'] ?? '');

    if ($pass !== $confirm_pass) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    }
    if (strlen($pass) < 12) {
        echo "<script>alert('Password must be at least 12 characters long.');</script>";
    }
    if (!preg_match('/[A-Z]/', $pass)) {
        echo "<script>alert('Password must contain at least one uppercase letter.');</script>";
    }
    if (!preg_match('/[a-z]/', $pass)) {
        echo "<script>alert('Password must contain at least one lowercase letter.');</script>";
    }
    if (!preg_match('/[0-9]/', $pass)) {
        echo "<script>alert('Password must contain at least one number.');</script>";
    }
    if (!preg_match('/[\W_]/', $pass)) {
        echo "<script>alert('Password must contain at least one special character.');</script>";
    }

    $select = mysqli_query($con, "SELECT u.*, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE e.email = '$email'") or die('query failed');
    if(mysqli_num_rows($select) > 0){
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $row = mysqli_fetch_assoc($select);
          $update = mysqli_query($con, "UPDATE grace_user SET password = '$hashed_password' WHERE userID = " . $row['userID']) or die('query failed');
        if ($update) {
            echo "<script>alert('Password has been reset successfully. You can now log in with your new password.');</script>";
            unset($_SESSION['reset_email']);
            header("Location: login.php");
            exit();
        } else {
            echo "<script>alert('Failed to reset password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Failed to reset password. Please try again.');</script>";
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .password-container {
            position: relative;
            width: 100%;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }  
        .password-container .box {
            padding-right: 40px;
            width: 90%;
        }
    </style>
</head>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="loginuser-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Reset Password</h1>
                
                <p style="text-align:center; margin-bottom:20px;">
                    For email: <strong><?= htmlspecialchars($email) ?></strong>
                </p>

                <div class="password-container">
                    <label for="password">New Password:</label><br>
                    <input type="password" id="password" name="pass" 
                           required placeholder="Enter new password" maxlength="20" class="box">
                    <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>

                <div class="password-container">
                    <label for="confirm_password">Confirm New Password:</label><br>
                    <input type="password" id="confirm_password" name="confirm_pass" 
                           required placeholder="Confirm new password" maxlength="20" class="box">

                    <i class="fas fa-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                </div>
                <div id="password-requirements" style="font-size: 14px; margin-bottom: 20px; ">
                    <p id="length-req" style="color: red;">❌ At least 12 characters long</p>
                    <p id="upper-req" style="color: red;">❌ At least one uppercase letter</p>
                    <p id="lower-req" style="color: red;">❌ At least one lowercase letter</p>
                    <p id="number-req" style="color: red;">❌ At least one number</p>
                    <p id="special-req" style="color: red;">❌ At least one special character</p>
                </div>

                <input type="submit" value="Reset Password" class="btn" name="reset_password">
            </form>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script>
        // Toggle password visibility for both fields
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const input = this.previousElementSibling;   // Get the input field before the icon
                
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                }
            });
        });

        const passwordInput = document.getElementById('password');
        const lengthReq = document.getElementById('length-req');
        const upperReq = document.getElementById('upper-req');
        const lowerReq = document.getElementById('lower-req');
        const numberReq = document.getElementById('number-req');
        const specialReq = document.getElementById('special-req');

         passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            
            // Length
            if (val.length >= 12) {
                lengthReq.innerHTML = '✅ At least 12 characters long';
                lengthReq.style.color = 'green';
            } else {
                lengthReq.innerHTML = '❌ At least 12 characters long';
                lengthReq.style.color = 'red';
            }
            
            if (/[A-Z]/.test(val)) {
                upperReq.innerHTML = '✅ At least one uppercase letter';
                upperReq.style.color = 'green';
            } else {
                upperReq.innerHTML = '❌ At least one uppercase letter';
                upperReq.style.color = 'red';
            }
            
           
            if (/[a-z]/.test(val)) {
                lowerReq.innerHTML = '✅ At least one lowercase letter';
                lowerReq.style.color = 'green';
            } else {
                lowerReq.innerHTML = '❌ At least one lowercase letter';
                lowerReq.style.color = 'red';
            }
            
            if (/[0-9]/.test(val)) {
                numberReq.innerHTML = '✅ At least one number';
                numberReq.style.color = 'green';
            } else {
                numberReq.innerHTML = '❌ At least one number';
                numberReq.style.color = 'red';
            }
            
           
            if (/[^A-Za-z0-9]/.test(val)) {
                specialReq.innerHTML = '✅ At least one special character';
                specialReq.style.color = 'green';
            } else {
                specialReq.innerHTML = '❌ At least one special character';
                specialReq.style.color = 'red';
            }
        });
    </script>
</body>
</html>
