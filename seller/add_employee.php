<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correcting undefined index error for product_stock
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = sha1($_POST['password']);
    $role = $_POST['role'];
   

    // Insert the values into the database, including the current date
    $select = mysqli_query($con, "SELECT u.*, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID WHERE u.username = '$name' OR e.email = '$email'") or die('Query failed');

    if (mysqli_num_rows($select) > 0) {
        echo "<script>alert('User already exists');</script>";
    } else {
        // Insert email first
        mysqli_query($con, "INSERT INTO email (email) VALUES ('$email')");
        $emailID = mysqli_insert_id($con);

        // Get roleID
        $role_res = mysqli_query($con, "SELECT roleID FROM roles WHERE role = '$role'");
        $role_row = mysqli_fetch_assoc($role_res);
        $roleID = $role_row['roleID'];

        mysqli_query($con, "INSERT INTO grace_user (username, emailID, password, roleID, is_active) VALUES ('$name', '$emailID', '$password', '$roleID', 1)") or die('Query failed');

        // Alert and redirect using JavaScript
        echo "<script>alert('Employee Added Successfully'); window.location.href = 'employee.php';</script>";
        exit();
    }
}

?>
