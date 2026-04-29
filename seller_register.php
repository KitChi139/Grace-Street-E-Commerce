<?php
include('components/connect.php');
include('components/password_validation.php');
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

$message = "";
$status = "";

if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $owner_name = $first_name . ' ' . $last_name;
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $pass = $_POST['pass'];
    $cpass = $_POST['cpass'];
    $captcha = $_POST['captcha'];

    // Dynamic password validation
    $pw_check = validatePassword($pass, $con);

    if (!$pw_check['valid']) {
        $message = $pw_check['message'];
        $status = "error";
    } elseif ($pass !== $cpass) {
        $message = "Confirm password does not match!";
        $status = "error";
    } else {
        // Captcha validation
        $captcha_valid = false;
        if (extension_loaded('gd')) {
            if (isset($_SESSION['captcha_phrase']) && strtr(strtolower($captcha), '01', 'ol') === strtr(strtolower($_SESSION['captcha_phrase']), '01', 'ol')) {
                $captcha_valid = true;
            } else {
                $message = "Incorrect Captcha answer!";
                $status = "error";
            }
        } else {
            if (isset($_SESSION['captcha_answer']) && $captcha == $_SESSION['captcha_answer']) {
                $captcha_valid = true;
            } else {
                $message = "Incorrect Captcha answer!";
                $status = "error";
            }
        }

        if ($captcha_valid) {
            // Check if email already exists in applications or users
            $check_email = mysqli_query($con, "SELECT email.email FROM email WHERE email.email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $message = "Email already registered or application pending!";
                $status = "error";
            } else {
                // Handle file upload
                if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                    $filename = $_FILES['document']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowed)) {
                        $message = "Invalid file type. Only PDF, JPG, and PNG allowed.";
                        $status = "error";
                    } else {
                        $upload_dir = 'uploads/seller_docs/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $new_filename = uniqid('doc_') . '.' . $ext;
                        $target_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {
                            // Hash password
                            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                            // Insert into email table first
                            mysqli_query($con, "INSERT INTO email (email) VALUES ('$email')");
                            $emailID = mysqli_insert_id($con);

                            // Insert into seller_applications
                            $stmt = $con->prepare("INSERT INTO seller_applications (owner_name, username, emailID, password, contact_number, address, document_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssissss", $owner_name, $username, $emailID, $hashed_pass, $contact, $address, $target_path);
                            
                            if ($stmt->execute()) {
                                $message = "Application submitted successfully! Please wait for admin approval.";
                                $status = "success";
                                // Reset captcha
                                unset($_SESSION['captcha_phrase']);
                                unset($_SESSION['captcha_answer']);
                            } else {
                                $message = "Failed to submit application. Please try again.";
                                $status = "error";
                            }
                        } else {
                            $message = "Failed to upload document.";
                            $status = "error";
                        }
                    }
                } else {
                    $message = "Please upload a valid ID or permit.";
                    $status = "error";
                }
            }
        }
    }
    // Reset captcha on failure if needed, or it will be regenerated on page load
    unset($_SESSION['captcha_phrase']);
    unset($_SESSION['captcha_answer']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration - Grace Street</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .seller-reg-container {
        max-width: 700px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    .seller-reg-container label {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
        color: #333;
    }
    .box {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }
    .btn {
        width: 100%;
        padding: 12px;
        background: #000;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover {
        background: #333;
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
</style>
<body>
    <?php include 'additional/loginheader.php'; ?>
    
    <section>
        <div class="seller-reg-container">
            <form action="" method="post" enctype="multipart/form-data">
                <h1 style="text-align: center; margin-bottom: 30px;font-family: 'Cormorant Garamond', serif;font-size: 2.5rem;">Apply as Seller</h1>
                
                <!-- Row 1: First name | Last name -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name" class="box">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name" class="box">
                    </div>
                </div>

                <!-- Row 2: Username -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required placeholder="Enter your username" class="box">
                    </div>
                </div>

                <!-- Row 3: Email | Contact Number -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email" class="box">
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number:</label>
                        <input type="text" id="contact" name="contact" required placeholder="Enter your contact number" class="box">
                    </div>
                </div>

                <!-- Row 4: Address -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" required placeholder="Enter your complete address" class="box" style="height: 100px;"></textarea>
                    </div>
                </div>

                <!-- Row 5: Password -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="pass" required placeholder="Create a password" class="box">
                    </div>
                </div>

                <!-- Row 6: Confirm Password -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" class="box">
                    </div>
                </div>

                <div id="password-requirements" style="font-size: 14px; margin-bottom: 20px; ">
                    <p id="length-req" style="color: red; <?php echo ($min_length == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_length; ?> characters long</p>
                    <p id="upper-req" style="color: red; <?php echo ($min_upper == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_upper; ?> CAPITAL letter<?php echo ($min_upper > 1) ? 's' : ''; ?></p>
                    <p id="lower-req" style="color: red; <?php echo ($min_lower == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_lower; ?> small letter<?php echo ($min_lower > 1) ? 's' : ''; ?></p>
                    <p id="number-req" style="color: red; <?php echo ($min_numbers == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_numbers; ?> number<?php echo ($min_numbers > 1) ? 's' : ''; ?></p>
                    <p id="special-req" style="color: red; <?php echo ($min_symbols == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_symbols; ?> special character<?php echo ($min_symbols > 1) ? 's' : ''; ?> (~@#$%^&*()!?)</p>
                </div>

                <label>Upload Documents (Valid ID or Permit):</label>
                <input type="file" name="document" required class="box" accept=".pdf,.jpg,.jpeg,.png">
                <p style="font-size: 12px; color: #666; margin-bottom: 20px;">Accepted formats: PDF, JPG, PNG</p>

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

                <input type="submit" value="Apply as Seller" name="submit" class="btn" id="register_btn" disabled>
                
                <p style="text-align: center; margin-top: 20px;">
                    Already have a seller account? <a href="login.php">Login</a>
                </p>
            </form>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>

    <script>
        function refreshCaptcha() {
            document.getElementById('captcha-img').src = 'captcha_gen.php?' + Math.random();
        }

        // TOS Checkbox logic
        const tosCheckbox = document.getElementById('tos_checkbox');
        const registerBtn = document.getElementById('register_btn');
        
        tosCheckbox.addEventListener('change', function() {
            registerBtn.disabled = !this.checked;
        });

        const passwordInput = document.querySelector('input[name="pass"]');
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

    <?php if ($message): ?>
    <script>
        Swal.fire({
            icon: '<?= $status ?>',
            title: '<?= $status == "success" ? "Success!" : "Oops..." ?>',
            text: '<?= $message ?>',
            confirmButtonColor: '#000'
        }).then((result) => {
            if (result.isConfirmed && '<?= $status ?>' === 'success') {
                window.location.href = 'login.php';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
