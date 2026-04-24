<?php
session_start();

include 'components/connect.php';

if(isset($_POST['productId'], $_POST['productQuantity']) && isset($_SESSION['user-id'])) {
    $productId = $_POST['productId'];
    $userId = $_SESSION['user-id'];
    $productQuantity = $_POST['productQuantity'];

    // Get the first available inventoryID for this product
    $inventoryQuery = $con->prepare("SELECT inventoryID FROM inventory WHERE proID = ? LIMIT 1");
    $inventoryQuery->bind_param("i", $productId);
    $inventoryQuery->execute();
    $inventoryResult = $inventoryQuery->get_result();
    
    if ($inventoryResult->num_rows > 0) {
        $inventoryRow = $inventoryResult->fetch_assoc();
        $inventoryId = $inventoryRow['inventoryID'];

        $existingProductQuery = $con->prepare("SELECT * FROM cart WHERE userID = ? AND inventoryID = ? LIMIT 1");
        $existingProductQuery->bind_param("ii", $userId, $inventoryId);
        $existingProductQuery->execute();
        $existingProductResult = $existingProductQuery->get_result();

        if ($existingProductResult->num_rows > 0) {
            $existingProduct = $existingProductResult->fetch_assoc();
            $newQuantity = $existingProduct['quantity'] + $productQuantity;

            $updateQuery = $con->prepare("UPDATE cart SET quantity = ? WHERE userID = ? AND inventoryID = ?");
            $updateQuery->bind_param("iii", $newQuantity, $userId, $inventoryId);
            $success = $updateQuery->execute();
            $updateQuery->close();
        } else {
            $sql = "INSERT INTO cart (userID, inventoryID, quantity) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iii", $userId, $inventoryId, $productQuantity);
            $success = $stmt->execute();
            $stmt->close();
        }
    } else {
        $success = false;
        $error = 'Product out of stock or inventory not found';
    }

    if ($success) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => isset($error) ? $error : 'Error adding item to cart'));
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'Invalid data received or user not logged in'));
}

$con->close();
?>
