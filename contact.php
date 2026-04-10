
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Contact</title>

    <!-- css connection -->
    <link rel="stylesheet" href="css/style.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>    
    <section>
        <div class="contact-container">
            <?php
                include 'components/connect.php';

                $successMessage = "";


                if(isset($_SESSION['user-email']) && isset($_POST['send'])) {
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $number = $_POST['number'];
                    $msg = $_POST['msg'];

                    // Save data to the database
                    $sql = "INSERT INTO contact (name, email, number, message) VALUES ('$name', '$email', '$number', '$msg')";
                    if(mysqli_query($con, $sql)) {
                        $successMessage = "Message sent successfully.";
                    } else {
                        $successMessage = "Error: " . $sql . "<br>" . mysqli_error($con);
                    }
                }

            if(!isset($_SESSION['user-email'])) {
                echo '<div style="text-align: center;">
                    <p>Please log in to view your cart.</p>
                    <a href="login.php"><button style="cursor: pointer; width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Login</button></a>
                </div>';
            } else {
                echo '<form action="" method="post">
                    <h1 style="text-align: center;">Get in touch</h1>
                    <input type="text" name="name" placeholder="Enter your name" required maxlength="20" class="box">
                    <input type="hidden" name="email" value="' . $_SESSION['user-email'] . '">
                    <input type="number" name="number" min="0" max="9999999999" placeholder="Enter your number" required onkeypress="if(this.value.length == 10) return false;" class="box">
                    <textarea name="msg" class="box" placeholder="Enter your message" cols="30" rows="10"></textarea>
                    <input type="submit" value="Send message" name="send" class="btn">
                </form>';
            }
            ?>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <!-- Display success message as an alert -->
    <?php if (!empty($successMessage)): ?>
        <script>
            alert("<?php echo $successMessage; ?>");
        </script>
    <?php endif; ?>
</body>
</html>
