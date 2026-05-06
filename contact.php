
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Contact</title>

    <!-- css connection -->
    <link rel="stylesheet" href="css/style.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>    
    <section>
        <div class="contact-container">
            <?php
                include 'components/connect.php';
                include_once 'components/mailer.php';

                $successMessage = "";
                $errorMessage = "";


                if(isset($_SESSION['user-id']) && isset($_POST['send'])) {
                    $email = mysqli_real_escape_string($con, $_POST['email']);
                    $msg = mysqli_real_escape_string($con, $_POST['msg']);

                    // Get emailID for this email
                    $email_res = mysqli_query($con, "SELECT emailID FROM email WHERE email = '$email'");
                    if(mysqli_num_rows($email_res) > 0) {
                        $email_row = mysqli_fetch_assoc($email_res);
                        $emailID = $email_row['emailID'];
                    } else {
                        mysqli_query($con, "INSERT INTO email (email) VALUES ('$email')");
                        $emailID = mysqli_insert_id($con);
                    }

                    // Save data to the database
                    $sql = "INSERT INTO contact (emailID, message) VALUES ('$emailID', '$msg')";
                    if(mysqli_query($con, $sql)) {
                        // Send email using PHPMailer
                        $to = 'didiaaa666@gmail.com';
                        $subject = "New Contact Message from $email";
                        $body = "You have received a new message from your website contact form.<br><br>".
                                "<b>User Email:</b> $email<br>".
                                "<b>Message:</b><br>$msg";
                        
                        if(sendEmail($to, $subject, $body)) {
                            $successMessage = "Message sent successfully and email notification sent to admin.";
                        } else {
                            $successMessage = "Message saved, but email notification failed.";
                        }
                    } else {
                        $errorMessage = "Error: " . mysqli_error($con);
                    }
                }

            if(!isset($_SESSION['user-id'])) {
                echo '<div style="text-align: center;">
                    <p>Please log in to contact us.</p>
                    <a href="login.php"><button style="cursor: pointer; width: 25vh; border: none; padding: 15px 30px; background-color: #2C2825; color: #F7F3EE; font-family: Jost, sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; transition: background-color 0.25s;">Login</button></a>
                </div>';
            } else {
                // Fetch email from email table using user-id session
                $user_id = $_SESSION['user-id'];
                $email_query = mysqli_query($con, "SELECT e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE u.userID = '$user_id'");
                $user_email = "";
                if(mysqli_num_rows($email_query) > 0) {
                    $email_row = mysqli_fetch_assoc($email_query);
                    $user_email = $email_row['email'];
                }

                echo '<form action="" method="post">
                    <h1 style="text-align: center;font-weight: 600">Get in touch</h1>
                    <label for="email" style="font-size: 1rem;">Your Email:</label>
                    <input type="email" name="email" value="' . $user_email . '" class="box" readonly>
                    <label for="msg" style="font-size: 1rem;">Message:</label>
                    <textarea name="msg" class="box" placeholder="Enter your message" cols="30" rows="10" required></textarea>
                    <input type="submit" value="Send message" name="send" class="btn">
                </form>';
            }
            ?>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    
    <script>
        <?php if (!empty($successMessage)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Sent!',
                text: '<?php echo $successMessage; ?>',
                confirmButtonColor: '#2C2825'
            });
        });
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $errorMessage; ?>',
                confirmButtonColor: '#2C2825'
            });
        });
    <?php endif; ?>
    </script>
</body>
</html>
