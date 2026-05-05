<?php
include 'components/connect.php';

if (isset($_GET['confirmation']) && $_GET['confirmation'] === 'yes') {
    $deleteQuery = $con->prepare("DELETE FROM cart");
    $success = $deleteQuery->execute();
    $deleteQuery->close();

    if ($success) {
        header("Location: cart.php?deleted_all=1");
    } else {
        header("Location: cart.php?deleted_all=0");
    }
    exit();
} else {
    header("Location: cart.php");
    exit();
}

$con->close();
?>