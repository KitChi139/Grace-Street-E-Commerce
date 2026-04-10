<?php
include 'components/connect.php';

if (isset($_GET['confirmation']) && $_GET['confirmation'] === 'yes') {
    $deleteQuery = $con->prepare("DELETE FROM cart");
    $success = $deleteQuery->execute();
    $deleteQuery->close();

    if ($success) {
        echo "<script>
            alert('All items successfully removed from the cart.');
            window.location.href = 'cart.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Error deleting all items from the cart.');
            window.location.href = 'cart.php';
        </script>";
        exit();
    }
} else {
    header("Location: cart.php");
    exit();
}

$con->close();
?>
