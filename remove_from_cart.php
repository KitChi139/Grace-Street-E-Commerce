<?php
include 'components/connect.php';

if(isset($_GET['productId'])) {
    $productId = $_GET['productId'];
    $deleteQuery = $con->prepare("DELETE FROM cart WHERE ID = ?");
    $deleteQuery->bind_param("i", $productId);
    $success = $deleteQuery->execute();
    $deleteQuery->close();
    
    if ($success) {
        echo "<script>
            alert('Item successfully removed from the cart.');
            window.location.href = 'cart.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Error removing item from cart.');
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
