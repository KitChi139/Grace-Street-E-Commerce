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
    
    // Default values if not set
    $min_length = $settings['pw_min_length'] ?? 12;
    $min_upper = $settings['pw_min_uppercase'] ?? 1;
    $min_lower = $settings['pw_min_lowercase'] ?? 1;
    $min_numbers = $settings['pw_min_numbers'] ?? 1;
    $min_symbols = $settings['pw_min_symbols'] ?? 1;

    if(isset($_POST['submit'])){
        $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
        $username = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $contact = mysqli_real_escape_string($con, $_POST['contact']);
        $address = mysqli_real_escape_string($con, $_POST['address']);
        $pass = $_POST['pass'];
        $cpass = $_POST['cpass'];
        $captcha = $_POST['captcha'];

        // Dynamic password validation
        $pw_check = validatePassword($pass, $con);

        if(!$pw_check['valid']) {
            $error_msg = $pw_check['message'];
        }

        elseif ($pass !== $cpass) {
            $error_msg = "Confirm password does not match!";
        }

        elseif (extension_loaded('gd')) {
            if (!isset($_SESSION['captcha_phrase']) || strtr(strtolower($captcha), '01', 'ol') !== strtr(strtolower($_SESSION['captcha_phrase']), '01', 'ol')) {
                $error_msg = "Incorrect Captcha answer!";
            } else {
                $captcha_valid = true;
            }
        } else {
            if (!isset($_SESSION['captcha_answer']) || $captcha != $_SESSION['captcha_answer']) {
                $error_msg = "Incorrect Captcha answer!";
            } else {
                $captcha_valid = true;
            }
        }

        if (isset($captcha_valid) && $captcha_valid) {

            $select = mysqli_query($con, "SELECT grace_user.*, email.email FROM grace_user JOIN email ON grace_user.emailID = email.emailID WHERE grace_user.username = '$username' OR email.email = '$email'") or die('Query failed');

            if (mysqli_num_rows($select) > 0) {
                $error_msg = "User already exists";
            } else {
                // Insert email first
                mysqli_query($con, "INSERT INTO email (email) VALUES ('$email')");
                $emailID = mysqli_insert_id($con);

                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                // Generate a 6-digit OTP
                $activation_token = sprintf("%06d", mt_rand(1, 999999));
                
                $insert_query = "INSERT INTO grace_user (first_name, last_name, username, emailID, password, contact_number, address, is_active, activation_token, roleID) 
                                 VALUES ('$first_name', '$last_name', '$username', '$emailID', '$hashed_pass', '$contact', '$address', 0, '$activation_token', 3)"; // Assuming 3 is 'user' role
                
                if(mysqli_query($con, $insert_query)){
                    
                    $activation_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/activate.php?token=$activation_token";
                    $subject = "Account Activation - Grace Street";
                    $message = "Hello $first_name $last_name,\n\nYour account activation code is: $activation_token\n\nYou can also click the link below to activate your account:\n$activation_link\n\nThank you!";
                    
                    $sent = false;
                    if (file_exists('PHPMailer/src/PHPMailer.php')) {
                        include_once('components/mailer.php');
                        $sent = sendEmail($email, $subject, $message);
                    } else {
                        
                        $headers = "From: noreply@gracestreet.com";
                        $sent = @mail($email, $subject, $message, $headers);
                    }

                  
                    // Reset captcha for next time
                    unset($_SESSION['captcha_phrase']);
                    unset($_SESSION['captcha_answer']);
                    
                    if ($sent) {
                        $success_msg = "Registered successfully! Please check your email for the 6-digit activation code.";
                        $redirect_to = "activate.php?email=" . urlencode($email);
                    } else {
                        $success_msg = "Registered successfully, but email could not be sent. Your activation code is: $activation_token";
                        $redirect_to = "activate.php?email=" . urlencode($email);
                    }
                } else {
                    $error_msg = "Registration failed. Please try again.";
                }
            }
        }
        
        // Reset captcha on failure
        unset($_SESSION['captcha_phrase']);
        unset($_SESSION['captcha_answer']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>

    
    <link rel="stylesheet" href="Css/style.css">

   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .registeruser-container {
        max-width: 700px; /* Increased width to accommodate grid */
        margin: 50px auto;
        padding: 30px;
    }
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }
    .form-group {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .form-group.full-width {
        width: 100%;
    }
    .registeruser-container label {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
    }
    .password-container {
        position: relative;
        width: 100%;
    }
    .password-container .box {
        margin-bottom: 0;
        padding-right: 40px;
        width: 100%;
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
    .captcha-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .tos-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .tos-container input {
        width: auto !important;
        margin-bottom: 0 !important;
        cursor: pointer;
    }
    .btn:disabled {
        background-color: #7e7e7eff !important;
        cursor: not-allowed;
    }
    .box {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }
</style>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="registeruser-container">
        <form action="" method="post">
            <h1 style="text-align: center; margin-bottom: 30px;">Register now</h1>
            
            <!-- Row 1: First name | Last name -->
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name" maxlength="50" class="box">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name" maxlength="50" class="box">
                </div>
            </div>

            <!-- Row 2: Username -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="name" required placeholder="Enter your username" maxlength="20" class="box">
                </div>
            </div>
            
            <!-- Row 3: Email | Contact Number -->
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                </div>
                <div class="form-group">
                    <label for="contact">Contact Number:</label>
                    <input type="text" id="contact" name="contact" required placeholder="Enter your contact number" maxlength="20" class="box">
                </div>
            </div>

            <!-- Row 4: Address -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="address">Address:</label>
                    <textarea style="background-color: rgba(232, 222, 210, 0.3);" id="address" name="address" required placeholder="Enter your complete address" class="box" style="height: 100px;"></textarea>
                </div>
            </div>

            <!-- Row 5: Password -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" id="password" name="pass" required placeholder="Enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                </div>
            </div>

            <!-- Row 6: Confirm Password -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                        <i class="fas fa-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                </div>
            </div>

            <div id="password-requirements" style="font-size: 14px; margin-bottom: 20px; ">
                <p id="length-req" style="color: red; <?php echo ($min_length == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_length; ?> characters long</p>
                <p id="upper-req" style="color: red; <?php echo ($min_upper == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_upper; ?> CAPITAL letter<?php echo ($min_upper > 1) ? 's' : ''; ?></p>
                <p id="lower-req" style="color: red; <?php echo ($min_lower == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_lower; ?> small letter<?php echo ($min_lower > 1) ? 's' : ''; ?></p>
                <p id="number-req" style="color: red; <?php echo ($min_numbers == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_numbers; ?> number<?php echo ($min_numbers > 1) ? 's' : ''; ?></p>
                <p id="special-req" style="color: red; <?php echo ($min_symbols == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_symbols; ?> special character<?php echo ($min_symbols > 1) ? 's' : ''; ?> (~@#$%^&*()!?)</p>
            </div>
            
           
            
            <?php if (extension_loaded('gd')): ?>
                <label for="captcha">Captcha:</label>
                <div class="captcha-container">
                    <img src="captcha_gen.php" id="captcha-img" alt="Captcha" style="margin-right: 10px;  border: 1px solid #ccc; border-radius: 5px; ">
                    <button type="button" onclick="refreshCaptcha()" style="padding: 5px 10px; cursor: pointer; background: #eee; border: 1px solid #ccc; border-radius: 5px;">Refresh</button>
                </div>
                <input type="text" id="captcha" name="captcha" required placeholder="Enter captcha" class="box">
            <?php else: 
                $num1 = rand(1, 10);
                $num2 = rand(1, 10);
                $_SESSION['captcha_answer'] = $num1 + $num2;
                $_SESSION['captcha_question'] = "$num1 + $num2 = ?";
            ?>
                <label for="captcha">Captcha: <?php echo $_SESSION['captcha_question']; ?></label>
                <div class="captcha-container">
                    <input type="number" id="captcha" name="captcha" required placeholder="Enter result" class="box" style="margin-bottom: 0;">
                </div>
            <?php endif; ?>

            <div class="tos-container">
                <input type="checkbox" id="tos_checkbox">
                <label for="tos_checkbox">I agree to the Terms of Service and Privacy Policy</label>
            </div>

            <input type="submit" value="Register now" class="btn" name="submit" id="register_btn" disabled>
            
            <p style="text-align: center;">Already have an account? <a href="login.php">Login</a></p>
        </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script>
        function refreshCaptcha() {
            document.getElementById('captcha-img').src = 'captcha_gen.php?' + Math.random();
        }

        // Toggle Password Visibility
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

        // TOS Checkbox logic
        const tosCheckbox = document.getElementById('tos_checkbox');
        const registerBtn = document.getElementById('register_btn');
        
        tosCheckbox.addEventListener('change', function() {
            registerBtn.disabled = !this.checked;
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

        <?php if (isset($success_msg)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $success_msg; ?>',
            confirmButtonColor: '#000'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo $redirect_to; ?>';
            }
        });
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $error_msg; ?>',
            confirmButtonColor: '#000'
        });
        <?php endif; ?>
    </script>
</body>
</html>
