<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correcting undefined index error for product_stock
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $cpass = $_POST['cpassword'];
    $role = $_POST['role'];

    $uppercase = preg_match('@[A-Z]@', $pass);
    $lowercase = preg_match('@[a-z]@', $pass);
    $number    = preg_match('@[0-9]@', $pass);
    $specialChars = preg_match('@[^\w]@', $pass);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($pass) < 12) {
        echo "<script>alert('Password must be at least 12 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.'); window.history.back();</script>";
        exit();
    }

    if ($pass !== $cpass) {
        echo "<script>alert('Confirm password does not match!'); window.history.back();</script>";
        exit();
    }

    $password = password_hash($pass, PASSWORD_DEFAULT);
   

    // Insert the values into the database, including the current date
    $select = mysqli_query($con, "SELECT * FROM grace_user WHERE username = '$name' OR email = '$email'") or die('Query failed');

    if (mysqli_num_rows($select) > 0) {
        echo "<script>alert('User already exists');</script>";
    } else {
        mysqli_query($con, "INSERT INTO grace_user (username, email, password, role) VALUES ('$name', '$email', '$password', '$role')") or die('Query failed');

        // Alert and redirect using JavaScript
        echo "<script>alert('Employee Added Successfully'); window.location.href = 'employee.php';</script>";
        exit();
    }
}

?>
