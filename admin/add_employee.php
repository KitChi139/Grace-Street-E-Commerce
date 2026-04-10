<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correcting undefined index error for product_stock
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = sha1($_POST['password']);
    $role = $_POST['role'];
   

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
