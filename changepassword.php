<?php
include './components/connect.php';


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    

    $update_profile = $con->prepare("UPDATE `grace_user` SET username = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $user_id]);

    $prev_pass = $_POST['prev_pass'];
    $old_pass_raw = $_POST['old_password'];
    $new_pass_raw = $_POST['new_pass'];
    $cpass_raw = $_POST['cpass'];

    $uppercase = preg_match('@[A-Z]@', $new_pass_raw);
    $lowercase = preg_match('@[a-z]@', $new_pass_raw);
    $number    = preg_match('@[0-9]@', $new_pass_raw);
    $specialChars = preg_match('@[^\w]@', $new_pass_raw);

    if (empty($old_pass_raw)) {
        echo "<script>alert('Please enter old password!');</script>";
    } elseif (!(password_verify($old_pass_raw, $prev_pass) || sha1($old_pass_raw) === $prev_pass)) {
        echo "<script>alert('Old password not matched!');</script>";
    } elseif (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($new_pass_raw) < 12) {
        echo "<script>alert('New password must be at least 12 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
    } elseif ($new_pass_raw != $cpass_raw) {
        echo "<script>alert('Confirm password not matched!');</script>";
    } else {
        $hashed_new_pass = password_hash($new_pass_raw, PASSWORD_DEFAULT);
        $update_admin_pass = $con->prepare("UPDATE `grace_user` SET password = ? WHERE id = ?");
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
                    <p id="length-req" style="color: red;">❌ At least 12 characters long</p>
                    <p id="upper-req" style="color: red;">❌ At least one uppercase letter</p>
                    <p id="lower-req" style="color: red;">❌ At least one lowercase letter</p>
                    <p id="number-req" style="color: red;">❌ At least one number</p>
                    <p id="special-req" style="color: red;">❌ At least one special character</p>
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
            
            // Uppercase
            if (/[A-Z]/.test(val)) {
                upperReq.innerHTML = '✅ At least one uppercase letter';
                upperReq.style.color = 'green';
            } else {
                upperReq.innerHTML = '❌ At least one uppercase letter';
                upperReq.style.color = 'red';
            }
            
            // Lowercase
            if (/[a-z]/.test(val)) {
                lowerReq.innerHTML = '✅ At least one lowercase letter';
                lowerReq.style.color = 'green';
            } else {
                lowerReq.innerHTML = '❌ At least one lowercase letter';
                lowerReq.style.color = 'red';
            }
            
            // Number
            if (/[0-9]/.test(val)) {
                numberReq.innerHTML = '✅ At least one number';
                numberReq.style.color = 'green';
            } else {
                numberReq.innerHTML = '❌ At least one number';
                numberReq.style.color = 'red';
            }
            
            // Special character
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
