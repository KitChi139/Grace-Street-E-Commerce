<?php
include('./components/connect.php');

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($con, $_GET['token']);

    $query = "SELECT * FROM grace_user WHERE activation_token = '$token' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if ($user['is_active'] == 1) {
            echo "<script>alert('Account already activated. Please login.'); window.location.href = 'login.php';</script>";
        } else {
            $update_query = "UPDATE grace_user SET is_active = 1, activation_token = NULL WHERE id = " . $user['id'];
            if (mysqli_query($con, $update_query)) {
                echo "<script>alert('Account activated successfully! You can now login.'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('Activation failed. Please contact support.'); window.location.href = 'register.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid activation token.'); window.location.href = 'register.php';</script>";
    }
} else {
    header("Location: register.php");
}
?>
