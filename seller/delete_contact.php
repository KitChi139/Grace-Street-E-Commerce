<?php
include('../components/connect.php');

if(isset($_POST['contact_id'])) {
    $contactId = $_POST['contact_id'];
    $deleteSql = "DELETE FROM contact WHERE id = $contactId";
    if(mysqli_query($con, $deleteSql)) {
        echo "Contact entry deleted successfully.";
    } else {
        echo "Error deleting contact entry: " . mysqli_error($con);
    }
} else {
    echo "Contact ID not provided.";
}
?>