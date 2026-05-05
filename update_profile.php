<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .password-container {
        position: relative;
        margin-bottom: 20px;
        width: 100%;
    }
    .password-container .box {
        margin-bottom: 0;
        padding-right: 45px;
        width: 100%;
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
    .swal-cancel-styled {
    border: 0.5px solid #2C2825 !important;
    color: #2C2825 !important;
    background-color: #F7F3EE !important;
    }
    .swal-cancel-styled:hover {
        background-color: #2C2825 !important;
        color: #F7F3EE !important;
    }
</style>
<body>
    <?php include 'additional/header.php'; ?>

    
<?php
    include('./components/connect.php');
    include('./components/encryption.php');

    $user_id = $_SESSION['user-id'] ?? $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header('Location: login.php');
        exit();
    }

    // Fetch current user data
    $select_user = mysqli_query($con, "SELECT u.*, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE u.userID = '$user_id'") or die('Query failed');
    $fetch_user = mysqli_fetch_assoc($select_user);

    if(isset($_POST['submit'])){
        $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
        $username = mysqli_real_escape_string($con, $_POST['name']);
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['cpass'];

        $select = mysqli_query($con, "SELECT * FROM grace_user WHERE userID = '$user_id'") or die('Query failed');
        
        if(mysqli_num_rows($select) > 0){
            $row = mysqli_fetch_assoc($select);
            
            // Verify old password
            if (password_verify($old_pass, $row['password']) || sha1($old_pass) === $row['password']) {
                
                // Update names
                mysqli_query($con, "UPDATE grace_user SET first_name = '$first_name', last_name = '$last_name', username = '$username' WHERE userID = '$user_id'") or die('Query failed');

                // If new password is provided, update it
                if (!empty($new_pass)) {
                    // Password Complexity Check
                    include_once('components/password_validation.php');
                    $pw_check = validatePassword($new_pass, $con);

                    if(!$pw_check['valid']) {
                        echo "<script>Swal.fire({ title: 'Invalid Password', text: '" . addslashes($pw_check['message']) . "', icon: 'error', confirmButtonColor: '#2C2825' });</script>";
                    } elseif ($new_pass !== $confirm_pass) {
                        echo "<script>Swal.fire({ title: 'Password Mismatch', text: 'New password and confirm password do not match!', icon: 'error', confirmButtonColor: '#2C2825' });</script>";
                    } else {
                        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        mysqli_query($con, "UPDATE grace_user SET password = '$hashed_pass' WHERE userID = '$user_id'") or die('Query failed');
                        echo "<script>Swal.fire({ title: 'Profile Updated!', text: 'Profile and password updated successfully!', icon: 'success', confirmButtonColor: '#2C2825', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'update_profile.php'; });</script>";
                    }
                } else {
                    echo "<script>Swal.fire({ title: 'Profile Updated!', text: 'Profile updated successfully!', icon: 'success', confirmButtonColor: '#2C2825', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'update_profile.php'; });</script>";
                }
                exit();
            } else {
                echo "<script>Swal.fire({ title: 'Incorrect Password', text: 'Old password is incorrect!', icon: 'error', confirmButtonColor: '#2C2825' });</script>";
            }
        }
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
?>

    <section>
        <div class="update-container">
            <form action="" method="post">
                <h1 style="text-align: center; font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: 3rem;">Update profile</h1>

                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name" maxlength="50" class="box" value="<?= $fetch_user["first_name"]; ?>">

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name" maxlength="50" class="box" value="<?= $fetch_user["last_name"]; ?>">

                <label for="username">Username</label>
                <input type="text" id="username" name="name" required placeholder="Enter your username" maxlength="20" class="box" value="<?= $fetch_user["username"]; ?>">

                <input type="hidden" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" value="<?= $fetch_user["email"]; ?>" oninput="this.value = this.value.replace(/\s/g, '')">

                <input type="hidden" name="prev_pass" value="<?= $fetch_user["password"]; ?>">

                <label for="old_password">Old Password</label>
                <div class="password-container">
                    <input type="password" id="old_password" name="old_password" required placeholder="Enter your old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                    <i class="fas fa-eye-slash toggle-password" id="toggleOldPassword"></i>
                </div>

                <label for="new_password">New Password</label>
                <div class="password-container">
                    <input type="password" id="new_password" name="new_pass" required placeholder="Enter your new password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                    <i class="fas fa-eye-slash toggle-password" id="toggleNewPassword"></i>
                </div>

                <label for="confirm_password">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                    <i class="fas fa-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                </div>
                <div id="password-requirements" style="font-size: 14px; margin-bottom: 20px;">
                    <p id="length-req" style="color: red; <?php echo ($min_length == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_length; ?> characters long</p>
                    <p id="upper-req" style="color: red; <?php echo ($min_upper == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_upper; ?> CAPITAL letter<?php echo ($min_upper > 1) ? 's' : ''; ?></p>
                    <p id="lower-req" style="color: red; <?php echo ($min_lower == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_lower; ?> small letter<?php echo ($min_lower > 1) ? 's' : ''; ?></p>
                    <p id="number-req" style="color: red; <?php echo ($min_numbers == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_numbers; ?> number<?php echo ($min_numbers > 1) ? 's' : ''; ?></p>
                    <p id="special-req" style="color: red; <?php echo ($min_symbols == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_symbols; ?> special character<?php echo ($min_symbols > 1) ? 's' : ''; ?> (~@#$%^&*()!?)</p>
                </div>

                

                <input type="submit" value="Update Profile" class="btn" name="submit">

            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script>
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

        setupPasswordToggle('toggleOldPassword', 'old_password');
        setupPasswordToggle('toggleNewPassword', 'new_password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

        const passwordInput = document.getElementById('new_password');
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
