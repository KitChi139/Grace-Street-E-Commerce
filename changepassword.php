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

    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
    $prev_pass = $_POST['prev_pass'];
    $old_pass = sha1($_POST['old_password']);
    $new_pass = sha1($_POST['new_pass']);
    $cpass = sha1($_POST['cpass']);

    if ($old_pass == $empty_pass) {
        echo "<script>alert('Please enter old password!');</script>";
    } elseif ($old_pass != $prev_pass) {
        echo "<script>alert('Old password not matched!');</script>";
    } elseif ($new_pass != $cpass) {
        echo "<script>alert('Confirm password not matched!');</script>";
    } else {
        if ($new_pass != $empty_pass) {
            $update_admin_pass = $con->prepare("UPDATE `grace_user` SET password = ? WHERE id = ?");
            $update_admin_pass->execute([$cpass, $user_id]);
            $prev_pass = $new_pass; // Update $prev_pass with the new password
            echo "<script>alert('Password updated successfully!');</script>";
        } else {
            echo "<script>alert('Please enter a new password!');</script>";
        }
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
                <input type="password" id="new_password" name="new_pass" required placeholder="Enter your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="cpass" required placeholder="Confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

                <input type="submit" value="Change Password" class="btn" name="submit">

            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>

</html>
