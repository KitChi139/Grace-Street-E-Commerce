<?php
    include('./components/connect.php');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

   
    if(!isset($_SESSION['captcha_answer'])) {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $_SESSION['captcha_question'] = "$num1 + $num2 = ?";
    }

    if(isset($_POST['submit'])){
        $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
        $username = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $pass = $_POST['pass'];
        $cpass = $_POST['cpass'];
        $captcha = $_POST['captcha'];

        $uppercase = preg_match('@[A-Z]@', $pass);
        $lowercase = preg_match('@[a-z]@', $pass);
        $number    = preg_match('@[0-9]@', $pass);
        $specialChars = preg_match('@[^\w]@', $pass);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($pass) < 8) {
            echo "<script>alert('Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
        }

        elseif ($pass !== $cpass) {
            echo "<script>alert('Confirm password does not match!');</script>";
        }

        elseif ($captcha != $_SESSION['captcha_answer']) {
            echo "<script>alert('Incorrect Captcha answer!');</script>";
        }
        else {

            $select = mysqli_query($con, "SELECT * FROM grace_user WHERE username = '$username' OR email = '$email'") or die('Query failed');

            if (mysqli_num_rows($select) > 0) {
                echo "<script>alert('User already exists');</script>";
            } else {

                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                $activation_token = bin2hex(random_bytes(16));
                
                $insert_query = "INSERT INTO grace_user (first_name, last_name, username, email, password, is_active, activation_token) 
                                 VALUES ('$first_name', '$last_name', '$username', '$email', '$hashed_pass', 0, '$activation_token')";
                
                if(mysqli_query($con, $insert_query)){
                    // 6. Send Link in Email to Activate Account
                    $activation_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/activate.php?token=$activation_token";
                    $subject = "Account Activation - Grace Street";
                    $message = "Hello $first_name $last_name,\n\nPlease click the link below to activate your account:\n$activation_link\n\nThank you!";
                    
                    $sent = false;
                    if (file_exists('PHPMailer/src/PHPMailer.php')) {
                        include_once('components/mailer.php');
                        $sent = sendEmail($email, $subject, $message);
                    } else {
                        // Fallback to mail() but it likely won't work on local XAMPP
                        $headers = "From: noreply@gracestreet.com";
                        $sent = @mail($email, $subject, $message, $headers);
                    }

                    // Reset captcha for next time
                    unset($_SESSION['captcha_answer']);
                    
                    if ($sent) {
                        echo "<script>alert('Registered successfully! Please check your email to activate your account.'); window.location.href = 'login.php';</script>";
                    } else {
                        echo "<script>alert('Registered successfully, but email could not be sent. You can activate your account using this link: $activation_link'); window.location.href = 'login.php';</script>";
                    }
                    exit();
                } else {
                    echo "<script>alert('Registration failed. Please try again.');</script>";
                }
            }
        }
        
        // Reset captcha on failure
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $_SESSION['captcha_question'] = "$num1 + $num2 = ?";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<style>
    .captcha-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .captcha-question {
        background: #f0f0f0;
        padding: 10px;
        border-radius: 5px;
        margin-right: 10px;
        font-weight: bold;
    }
</style>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="registeruser-container">
        <form action="" method="post">
            <h1 style="text-align: center;">Register now</h1>
            
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name" maxlength="50" class="box">

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name" maxlength="50" class="box">

            <label for="username">Username:</label>
            <input type="text" id="username" name="name" required placeholder="Enter your username" maxlength="20" class="box">
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="pass" required placeholder="Enter your password (min. 8 chars, 1 upper, 1 lower, 1 num, 1 special)" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            
            <label for="captcha">Captcha: <?php echo $_SESSION['captcha_question']; ?></label>
            <div class="captcha-container">
                <input type="number" id="captcha" name="captcha" required placeholder="Enter result" class="box" style="margin-bottom: 0;">
            </div>

            <input type="submit" value="Register now" class="btn" name="submit">
            
            <p style="text-align: center;">Already have an account? <a href="login.php">Login</a></p>
        </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
