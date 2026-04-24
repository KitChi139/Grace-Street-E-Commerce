<?php
include './components/connect.php';
include './components/password_validation.php';

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

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    

    $update_profile = $con->prepare("UPDATE `grace_user` SET username = ?, emailID = ? WHERE userID = ?");
    $update_profile->execute([$name, $email, $user_id]);

    $prev_pass = $_POST['prev_pass'];
    $old_pass_raw = $_POST['old_password'];
    $new_pass_raw = $_POST['new_pass'];
    $cpass_raw = $_POST['cpass'];

    // Dynamic password validation
    $pw_check = validatePassword($new_pass_raw, $con);

    if (empty($old_pass_raw)) {
        echo "<script>alert('Please enter old password!');</script>";
    } elseif (!(password_verify($old_pass_raw, $prev_pass) || sha1($old_pass_raw) === $prev_pass)) {
        echo "<script>alert('Old password not matched!');</script>";
    } elseif (!$pw_check['valid']) {
        echo "<script>alert('" . $pw_check['message'] . "');</script>";
    } elseif ($new_pass_raw != $cpass_raw) {
        echo "<script>alert('Confirm password not matched!');</script>";
    } else {
        $hashed_new_pass = password_hash($new_pass_raw, PASSWORD_DEFAULT);
        $update_admin_pass = $con->prepare("UPDATE `grace_user` SET password = ? WHERE userID = ?");
        $update_admin_pass->bind_param("si", $hashed_new_pass, $user_id);
        $update_admin_pass->execute();
        echo "<script>alert('Password updated successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraceStreet/Change Password</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>

<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="update-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Change Password</h1>

                <input type="hidden" name="prev_pass" value="<?= $fetch_user["password"]; ?>">

                <label for="old_password">Old Password</label>
                <input type="password" id="old_password" name="old_password" required placeholder="Enter your old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_pass" required placeholder="Enter your new password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <div id="password-requirements" style="font-size: 14px; margin-bottom: 20px;">
                    <p id="length-req" style="color: red; <?php echo ($min_length == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_length; ?> characters long</p>
                    <p id="upper-req" style="color: red; <?php echo ($min_upper == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_upper; ?> CAPITAL letter<?php echo ($min_upper > 1) ? 's' : ''; ?></p>
                    <p id="lower-req" style="color: red; <?php echo ($min_lower == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_lower; ?> small letter<?php echo ($min_lower > 1) ? 's' : ''; ?></p>
                    <p id="number-req" style="color: red; <?php echo ($min_numbers == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_numbers; ?> number<?php echo ($min_numbers > 1) ? 's' : ''; ?></p>
                    <p id="special-req" style="color: red; <?php echo ($min_symbols == 0) ? 'display:none;' : ''; ?>">❌ At least <?php echo $min_symbols; ?> special character<?php echo ($min_symbols > 1) ? 's' : ''; ?></p>
                </div>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

                <input type="submit" value="Change Password" class="btn" name="submit">

            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script>
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
            
           
            const specialCount = (val.match(/[^A-Za-z0-9]/g) || []).length;
            if (specialCount >= minSymbols) {
                specialReq.innerHTML = `✅ At least ${minSymbols} special character${minSymbols > 1 ? 's' : ''}`;
                specialReq.style.color = 'green';
            } else {
                specialReq.innerHTML = `❌ At least ${minSymbols} special character${minSymbols > 1 ? 's' : ''}`;
                specialReq.style.color = 'red';
            }
        });
    </script>
</body>

</html>
