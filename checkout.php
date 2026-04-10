<?php
error_reporting(E_ALL & ~E_NOTICE); // Suppress notices

@session_start(); // Suppress "session_start() already active" notice
include 'components/connect.php';

$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['YourName'];
    $email = $_POST['YourEmail'];
    $number = $_POST['YourNumber'];
    $address = $_POST['Address'];
    $paymentMethod = $_POST['PaymentMethod'];
    $status = $_POST['status']; 

    $userEmail = $_SESSION['user-email'];
    $sql = "SELECT * FROM cart WHERE User_Email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalSubtotal = 0;
    $totalProducts = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productName = $row['Product_Name'];
            $productPrice = $row['Product_Price'];
            $productQuantity = $row['Product_Quantity'];
            $subtotal = $productPrice * $productQuantity;
            $totalSubtotal += $subtotal;

            // Collecting product details
            $totalProducts [] = '<span style="display: block; margin-bottom: 10px;">- ' . $productName . ' PHP' . $productPrice . '(' . $productQuantity . ')</span>';

            // Subtract product quantity from product_stock
            $updateStockSql = "UPDATE product_list SET product_stock_s = product_stock_s - ? WHERE product_name = ?";
            $updateStockStmt = $con->prepare($updateStockSql);
            $updateStockStmt->bind_param("is", $productQuantity, $productName);
            $updateStockStmt->execute();
        }
    }

    // Convert array to string for database
    $productsString = implode($totalProducts);

    // Insert into orders table
    $insertSql = "INSERT INTO orders (Name, Email, Number, Address, Method, Total_Products, Total_Price, Placed_on, Order_Status, order_email) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?)";
    $stmt = $con->prepare($insertSql);
    $stmt->bind_param("sssssssss", $name, $email, $number, $address, $paymentMethod, $productsString, $totalSubtotal, $status, $userEmail);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Delete records from the cart table
        $deleteSql = "DELETE FROM cart WHERE User_Email = ?";
        $stmt = $con->prepare($deleteSql);
        $stmt->bind_param("s", $userEmail);
        if ($stmt->execute()) {
            $successMessage = "Order placed successfully!";
        } else {
            $successMessage = "Error deleting cart items: " . $stmt->error;
        }
    } else {
        $successMessage = "Error placing order: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Checkout</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="checkout_container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to place the order?');">
                <div class="checkout_content">
                    <div class="checkout_header">
                        <div style="background-color: black; color: white; padding: 10px;">
                            <h1 class="color: white; padding: 20px;">Your orders</h1>
                        </div>
                        <div class="checkout_orders" style="padding-bottom: 20px; padding: 20px;">
                            <div class="checkout_item" >
                                <?php
                                
                                if(isset($_SESSION['user-email'])) {
                                    // Fetch data from the database
                                    $userEmail = $_SESSION['user-email'];
                                    $query = "SELECT * FROM cart WHERE User_Email = ?";
                                    $stmt = $con->prepare($query);
                                    $stmt->bind_param("s", $userEmail);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    // Initialize total subtotal
                                    $totalSubtotal = 0;

                                    // Check if there are any rows returned
                                    if ($result->num_rows > 0) {
                                        // Loop through each row of the result
                                        while ($row = $result->fetch_assoc()) {
                                            // Extract data from the current row
                                            $productName = $row['Product_Name'];
                                            $productPrice = $row['Product_Price'];
                                            $productQuantity = $row['Product_Quantity'];
                                            $productImage = $row['Product_Image'];
                                            $subtotal = $productPrice * $productQuantity;

                                            // Add subtotal to total subtotal
                                            $totalSubtotal += $subtotal;

                                            // Generate HTML for each product
                                            echo '<div class="order_product">';
                                            echo '<div class="order_image">';
                                            $imagePath = 'uploads/images/' . $productImage;
                                            if (file_exists($imagePath)) {
                                                echo '<img src="' . $imagePath . '" alt="Product Image">';
                                            } else {
                                                echo '<p>Image not found</p>';
                                            }
                                            echo '</div>';
                                            echo '<div class="order_content">';
                                            echo '<h1>' . $productName . '</h1>';
                                            echo '<p>PHP ' . number_format($productPrice, 2) . ' (' . $productQuantity . ')</p>';
                                            echo '<p style="font-size: 13px;">SUBTOTAL PHP ' . number_format($subtotal, 2) . '</p>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        // If no rows are returned, display a message
                                        echo 'No products found.';
                                    }
                                } else {
                                    echo "<p>Please log in to view your cart.</p>";
                                }

                                $con->close();
                                ?>
                            </div>
                            <div class="order_total">
                                <p>Total Price:</p>
                                <h1>PHP <?php echo number_format($totalSubtotal, 2); ?></h1>
                            </div>
                        </div>
                    </div>
                    <div class="checkout_placeorder">
                        <div class="placeorder_header" style="background-color: black; color:white; padding: 10px;">
                                <h1>Place your orders</h1> 
                            </div>
                    <div class="checkout_inputs">
                        <label for="YourName">Your Name:</label><br>
                        <input type="text" id="YourName" name="YourName" required placeholder="e.g. John Doe">
                        
                        <label for="YourEmail">Your Email:</label><br>
                        <input type="text" id="YourEmail" name="YourEmail" required placeholder="e.g. john@example.com">
                        
                        <label for="YourNumber">Your Number:</label><br>
                        <input type="text" id="YourNumber" name="YourNumber" required placeholder="e.g. 123-456-7890">
                        
                        <label for="Address">Address:</label><br>
                        <input type="text" id="Address" name="Address" required placeholder="e.g. 123 Main Street, New York, NY 12345">
                        
                        <label for="PaymentMethod">Payment Method:</label><br>
                        <select id="PaymentMethod" name="PaymentMethod" required>
                            <option value="" disabled selected>Select Payment Method</option>
                            <option value="cash_on_delivery">Cash on delivery</option>
                        </select>

                        <input type="hidden" name="status" value="0">

                        <input type="submit" value="Submit" style="cursor: pointer;">
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>

    <script>
        <?php if (!empty($successMessage)): ?>
            alert("<?php echo $successMessage; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
