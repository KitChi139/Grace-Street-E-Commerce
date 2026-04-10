<?php
session_start();

include 'components/connect.php';

if(isset($_POST['productId'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['productQuantity']) && isset($_SESSION['user-email']) && isset($_SESSION['user-id'])) {
    $productId = $_POST['productId'];
    $productImage = $_POST['productImage'];
    $productName = $_POST['productName'];
    $productPrice = $_POST['productPrice'];
    $userEmail = $_SESSION['user-email'];
    $userId = $_SESSION['user-id'];
    $productQuantity = $_POST['productQuantity'];

    // Check if discounted price is provided
    $discountedPrice = isset($_POST['discountedPrice']) ? $_POST['discountedPrice'] : $productPrice;
    
    $existingProductQuery = $con->prepare("SELECT * FROM cart WHERE Product_Name = ? AND user_email = ? LIMIT 1");
    $existingProductQuery->bind_param("ss", $productName, $userEmail);
    $existingProductQuery->execute();
    $existingProductResult = $existingProductQuery->get_result();

    if ($existingProductResult->num_rows > 0) {
        $existingProduct = $existingProductResult->fetch_assoc();
        $newQuantity = $existingProduct['Product_Quantity'] + $productQuantity;

        $updateQuery = $con->prepare("UPDATE cart SET Product_Quantity = ? WHERE Product_Name = ? AND user_email = ?");
        $updateQuery->bind_param("iss", $newQuantity, $productName, $userEmail);
        $success = $updateQuery->execute();
        $updateQuery->close();
    } else {
        $totalPrice = $discountedPrice * $productQuantity; // Use discounted price for total price calculation
        $sql = "INSERT INTO cart (Product_Name, Product_Price, Product_Quantity, Product_Image, user_id, user_email) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sdisss", $productName, $totalPrice, $productQuantity, $productImage, $userId, $userEmail);
        $success = $stmt->execute();
        $stmt->close();
    }

    if ($success) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error adding item to cart'));
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'Invalid data received or user not logged in'));
}

$con->close();
?>
