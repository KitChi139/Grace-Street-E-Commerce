<?php 
include('../components/connect.php'); // Assuming connect.php is in the same directory
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;

$sql = "SELECT id,
               CONCAT_WS(' ', NULLIF(first_name,''), NULLIF(last_name,'')) AS name,
               email,
               role,
               is_active
        FROM grace_user";
$result = mysqli_query($con, $sql);

if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $users = [];
}

?>