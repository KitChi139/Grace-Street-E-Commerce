<?php
include 'components/connect.php';

if(isset($_GET['productId'])) {
    $productId = $_GET['productId'];
    $deleteQuery = $con->prepare("DELETE FROM cart WHERE cartID = ?");
    $deleteQuery->bind_param("i", $productId);
    $success = $deleteQuery->execute();
    $deleteQuery->close();
    
    if ($success) {
        header("Location: cart.php?removed=1");
    } else {
        header("Location: cart.php?removed=0");
    }
    exit();
} else {
    header("Location: cart.php");
    exit();
}

$con->close();
?>