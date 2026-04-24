<?php
session_start();

include 'components/connect.php';

if(isset($_POST['productId'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['discountedPrice']) && isset($_SESSION['user-id'])) {
    $productId = $_POST['productId'];
    $userId = $_SESSION['user-id'];

    $existingProductQuery = $con->prepare("SELECT * FROM wishlist WHERE proID = ? AND userID = ? LIMIT 1");
    $existingProductQuery->bind_param("ii", $productId, $userId);
    $existingProductQuery->execute();
    $existingProductResult = $existingProductQuery->get_result();

    if ($existingProductResult->num_rows > 0) {
        echo json_encode(array('success' => false, 'error' => 'Item already exists in wishlist'));
    } else {
        $sql = "INSERT INTO wishlist (userID, proID) VALUES (?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'error' => 'Error adding item to wishlist'));
        }
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'Invalid data received or user not logged in'));
}

$con->close();
?>
