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
</head>
<style>
    .password-container {
        position: relative;
        margin-bottom: 20px;
        width: 96.6%;
    }
    .password-container .box {
        margin-bottom: 0;
        padding-right: 40px;
        width: 92.6%;
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
    <?php include 'additional/header.php'; ?>

    
<?php
    include('./components/connect.php');

    if(isset($_POST['submit'])){
        $user_id = $_SESSION['user-id'];
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
                
                // Update names (Note: assuming first_name/last_name might not exist in new schema, so using username primarily)
                mysqli_query($con, "UPDATE grace_user SET username = '$username' WHERE userID = '$user_id'") or die('Query failed');

                // If new password is provided, update it
                if (!empty($new_pass)) {
                    // Password Complexity Check
                    $uppercase = preg_match('@[A-Z]@', $new_pass);
                    $lowercase = preg_match('@[a-z]@', $new_pass);
                    $number    = preg_match('@[0-9]@', $new_pass);
                    $specialChars = preg_match('@[^\w]@', $new_pass);

                    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($new_pass) < 12) {
                        echo "<script>alert('New password must be at least 12 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
                    } elseif ($new_pass !== $confirm_pass) {
                        echo "<script>alert('New password and confirm password do not match!');</script>";
                    } else {
                        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        mysqli_query($con, "UPDATE grace_user SET password = '$hashed_pass' WHERE userID = '$user_id'") or die('Query failed');
                        echo "<script>alert('Profile and password updated successfully!');</script>";
                    }
                } else {
                    echo "<script>alert('Profile updated successfully!');</script>";
                }
                echo "<script>window.location.href = 'update_profile.php';</script>";
                exit();
            } else {
                echo "<script>alert('Old password is incorrect!');</script>";
            }
        }
    }
?>

    <section>
        <div class="update-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Update profile</h1>

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
                    <p id="length-req" style="color: red;">❌ At least 12 characters long</p>
                    <p id="upper-req" style="color: red;">❌ At least one uppercase letter</p>
                    <p id="lower-req" style="color: red;">❌ At least one lowercase letter</p>
                    <p id="number-req" style="color: red;">❌ At least one number</p>
                    <p id="special-req" style="color: red;">❌ At least one special character</p>
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

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            
            
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
