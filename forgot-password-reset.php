<?php

include('./components/connect.php');
include('./components/password_validation.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch system settings for password requirements
$settings = [];
$setting_query = mysqli_query($con, "SELECT * FROM system_settings WHERE setting_key LIKE 'pw_%'");
while($row = mysqli_fetch_assoc($setting_query)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$min_length = $settings['pw_min_length'] ?? 12;
$min_upper = $settings['pw_min_uppercase'] ?? 1;
$min_lower = $settings['pw_min_lowercase'] ?? 1;
$min_numbers = $settings['pw_min_numbers'] ?? 1;
$min_symbols = $settings['pw_min_symbols'] ?? 1;

$email = $_SESSION['reset_email'] ?? '';
if (empty($email)) {
    header("Location: forgot-password-send.php");
    exit();
}

if(isset($_POST['reset_password'])){
    $pass = trim($_POST['pass'] ?? '');
    $confirm_pass = trim($_POST['confirm_pass'] ?? '');

    // Dynamic password validation
    $pw_check = validatePassword($pass, $con);

    if ($pass !== $confirm_pass) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    } elseif (!$pw_check['valid']) {
        echo "<script>alert('" . $pw_check['message'] . "');</script>";
    } else {
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
                    <p id="length-req" style="color: red; <?php echo ($min_length == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_length; ?> characters long</p>
                    <p id="upper-req" style="color: red; <?php echo ($min_upper == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_upper; ?> CAPITAL letter<?php echo ($min_upper > 1) ? 's' : ''; ?></p>
                    <p id="lower-req" style="color: red; <?php echo ($min_lower == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_lower; ?> small letter<?php echo ($min_lower > 1) ? 's' : ''; ?></p>
                    <p id="number-req" style="color: red; <?php echo ($min_numbers == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_numbers; ?> number<?php echo ($min_numbers > 1) ? 's' : ''; ?></p>
                    <p id="special-req" style="color: red; <?php echo ($min_symbols == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_symbols; ?> special character<?php echo ($min_symbols > 1) ? 's' : ''; ?> (~@#$%^&*()!?)</p>
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

        const minLength = <?php echo $min_length; ?>;
        const minUpper = <?php echo $min_upper; ?>;
        const minLower = <?php echo $min_lower; ?>;
        const minNumbers = <?php echo $min_numbers; ?>;
        const minSymbols = <?php echo $min_symbols; ?>;

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            
            // Length
            if (val.length >= minLength) {
                lengthReq.innerHTML = `✅ At least ${minLength} characters long`;
                lengthReq.style.color = 'green';
            } else {
                lengthReq.innerHTML = `❌ At least ${minLength} characters long`;
                lengthReq.style.color = 'red';
            }
            
            const upperCount = (val.match(/[A-Z]/g) || []).length;
            if (upperCount >= minUpper) {
                upperReq.innerHTML = `✅ At least ${minUpper} CAPITAL letter${minUpper > 1 ? 's' : ''}`;
                upperReq.style.color = 'green';
            } else {
                upperReq.innerHTML = `❌ At least ${minUpper} CAPITAL letter${minUpper > 1 ? 's' : ''}`;
                upperReq.style.color = 'red';
            }
            
           
            const lowerCount = (val.match(/[a-z]/g) || []).length;
            if (lowerCount >= minLower) {
                lowerReq.innerHTML = `✅ At least ${minLower} small letter${minLower > 1 ? 's' : ''}`;
                lowerReq.style.color = 'green';
            } else {
                lowerReq.innerHTML = `❌ At least ${minLower} small letter${minLower > 1 ? 's' : ''}`;
                lowerReq.style.color = 'red';
            }
            
            const numberCount = (val.match(/[0-9]/g) || []).length;
            if (numberCount >= minNumbers) {
                numberReq.innerHTML = `✅ At least ${minNumbers} number${minNumbers > 1 ? 's' : ''}`;
                numberReq.style.color = 'green';
            } else {
                numberReq.innerHTML = `❌ At least ${minNumbers} number${minNumbers > 1 ? 's' : ''}`;
                numberReq.style.color = 'red';
            }
            
           
            const specialCount = (val.match(/[~@#$%^&*()!?]/g) || []).length;
            if (specialCount >= minSymbols) {
                specialReq.innerHTML = `✅ At least ${minSymbols} special character${minSymbols > 1 ? 's' : ''} (~@#$%^&*()!?)`;
                specialReq.style.color = 'green';
            } else {
                specialReq.innerHTML = `❌ At least ${minSymbols} special character${minSymbols > 1 ? 's' : ''} (~@#$%^&*()!?)`;
                specialReq.style.color = 'red';
            }
        });
    </script>
</body>
</html>
