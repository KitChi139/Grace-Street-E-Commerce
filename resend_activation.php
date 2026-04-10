<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$info = "";
$link = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $query = "SELECT * FROM grace_user WHERE email = '$email' LIMIT 1";
    $res = mysqli_query($con, $query);
    if (mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        if ((int)$user['is_active'] === 1) {
            $info = "This account is already activated. You can login.";
        } else {
            $token = bin2hex(random_bytes(16));
            $update = "UPDATE grace_user SET activation_token = '$token' WHERE id = " . (int)$user['id'];
            if (mysqli_query($con, $update)) {
                $activation_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/activate.php?token=$token";
                $subject = "Account Activation - Grace Street";
                $message = "Hello,\n\nPlease click the link below to activate your account:\n$activation_link\n\nThank you!";
                
                $sent = false;
                if (file_exists('PHPMailer/src/PHPMailer.php')) {
                    include_once('components/mailer.php');
                    $sent = sendEmail($email, $subject, $message);
                } else {
                    $headers = "From: noreply@gracestreet.com";
                    $sent = @mail($email, $subject, $message, $headers);
                }

                if ($sent) {
                    $info = "Activation email sent. Please check your inbox.";
                } else {
                    $info = "Email sending is not available on this server.";
                    $link = $activation_link;
                }
            } else {
                $info = "Failed to generate new activation link. Please try again.";
            }
        }
    } else {
        $info = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Activation</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .resend-container { height:auto; padding:100px 0; background-color: rgba(201,201,201,0.1); }
        .resend-container form { display:flex; flex-direction:column; justify-content:center; border-radius:20px; border:2px solid rgb(201,201,201); padding:40px; width:60vh; margin:0 auto; }
        .resend-container form input { margin-bottom:20px; padding:15px; border:none; background-color: rgba(201,201,201,0.3); }
        .resend-container input[type="submit"] { background-color:black; color:white; cursor:pointer; }
        .info { text-align:center; margin: 10px auto; width:60vh; }
        .info a { word-break: break-all; }
    </style>
    </head>
<body>
    <?php include 'additional/loginheader.php'; ?>
    <section>
        <div class="resend-container">
            <form action="" method="post">
                <h1 style="text-align:center;">Resend Activation Link</h1>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="submit" value="Send" class="btn" name="submit">
                <p style="text-align:center;">Back to <a href="login.php">Login</a></p>
            </form>
            <?php if (!empty($info)): ?>
                <div class="info">
                    <p><?php echo htmlspecialchars($info); ?></p>
                    <?php if (!empty($link)): ?>
                        <p><a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($link); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>

