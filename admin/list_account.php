<?php 
include('../components/connect.php'); // Assuming connect.php is in the same directory
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;

$sql = "SELECT u.userID AS id,
               u.username AS name,
               e.email,
               r.role,
               u.is_active
        FROM grace_user u
        JOIN email e ON u.emailID = e.emailID
        JOIN roles r ON u.roleID = r.roleID";
$result = mysqli_query($con, $sql);

if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $users = [];
}

?>
