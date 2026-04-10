<?php
include 'components/connect.php';

if(isset($_GET['ID'])) {
    $ID = $_GET['ID'];
    $deleteQuery = $con->prepare("DELETE FROM wishlist WHERE ID = ?");
    $deleteQuery->bind_param("i", $ID);
    
    if ($deleteQuery->execute()) {
        echo "<script>
            alert('Item successfully removed from your wishlist.');
            window.location.href = 'wishlist.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Error removing item from wishlist.');
            window.location.href = 'wishlist.php';
        </script>";
        exit();
    }
} else {
    header("Location: wishlist.php");
    exit();
}

$deleteQuery->close();
$con->close();
?>
