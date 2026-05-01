    <?php
    error_reporting(E_ALL & ~E_NOTICE); // Suppress notices

    @session_start(); // Suppress "session_start() already active" notice
    include 'components/connect.php';

    // $successMessage = "";

    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $name = $_POST['YourName'];
    //     $email = $_POST['YourEmail'];
    //     $number = $_POST['YourNumber'];
    //     $address = $_POST['Address'];
    //     $paymentMethod = $_POST['PaymentMethod'];
    //     $status = 'Pending';
        
    //     // Fetch cart items for the logged-in user
    //     $userId = $_SESSION['user-id'];
    //     $query = "SELECT cart.*, product.name, product.price 
    //               FROM cart 
    //               JOIN inventory ON cart.inventoryID = inventory.inventoryID 
    //               JOIN product ON inventory.proID = product.proID 
    //               WHERE cart.userID = ?";
    //     $stmt = $con->prepare($query);
    //     $stmt->bind_param("i", $userId);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $totalSubtotal = 0;
    //     $totalProducts = [];



    //     // Convert array to string for database
    //     $productsString = implode($totalProducts);

    //     // Insert into orders table (Grace Street 1 schema)
    //     $status = 'Pending';
    //     $insertSql = "INSERT INTO orders (userID, status, price, time_ordered) 
    //                   VALUES (?, ?, ?, NOW())";
    //     $stmt = $con->prepare($insertSql);
    //     $stmt->bind_param("isd", $userId, $status, $totalSubtotal);
    //     $stmt->execute();
    //     $orderID = $stmt->insert_id;

        

    //     if ($paymentMethod == "paymongo") {
    //         $amount = $totalSubtotal * 100; // PayMongo uses centavos

    //         $curl = curl_init();

    //         curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_HTTPHEADER => [
    //             "Content-Type: application/json",
    //             "Authorization: Basic " . base64_encode("sk_test_xxxxx:") // your secret key
    //         ],
    //         CURLOPT_POSTFIELDS => json_encode([
    //             "data" => [
    //             "attributes" => [
    //                 "line_items" => [
    //                 [
    //                     "name" => "Order #$orderID",
    //                     "amount" => $amount,
    //                     "currency" => "PHP",
    //                     "quantity" => 1
    //                 ]
    //                 ],
    //                 "payment_method_types" => ["gcash", "paymaya", "card"],
    //                 "success_url" => "http://localhost/success.php?order_id=$orderID",
    //                 "cancel_url" => "http://localhost/cancel.php?order_id=$orderID"
    //             ]
    //             ]
    //         ])
    //         ]);

    //         $response = curl_exec($curl);
    //         curl_close($curl);

    //         $result = json_decode($response, true);

    //         // Redirect user to PayMongo checkout
    //         $checkoutUrl = $result['data']['attributes']['checkout_url'];

    //         header("Location: webhook.php" . $checkoutUrl);
    //         exit;
    //     } else {
    //         // run your existing COD logic
    //     }

    //     // if ($stmt->affected_rows > 0) {
    //     //     // Also insert into order_items if that table exists in your logic
    //     //     // But for now, let's keep it simple as per previous logic
            
    //     //     // Delete records from the cart table
    //     //     $deleteSql = "DELETE FROM cart WHERE userID = ?";
    //     //     $stmt = $con->prepare($deleteSql);
    //     //     $stmt->bind_param("i", $userId);
    //     //     if ($stmt->execute()) {
    //     //         $successMessage = "Order placed successfully!";
    //     //     } else {
    //     //         $successMessage = "Error deleting cart items: " . $stmt->error;
    //     //     }
    //     // } else {
    //     //     $successMessage = "Error placing order: " . $stmt->error;
    //     // }

    //     $stmt->close();
    //     $con->close();
    // }

    $successMessage = "";
    
    // Fetch user details for pre-filling the form
    $userName = "";
    $userEmail = "";
    $userNumber = "";
    $userAddress = "";

    if (isset($_SESSION['user-id'])) {
        $userId = $_SESSION['user-id'];
        $user_query = "SELECT u.first_name, u.last_name, u.username, e.email, u.contact_number, u.address 
                       FROM grace_user u 
                       JOIN email e ON u.emailID = e.emailID 
                       WHERE u.userID = ?";
        $user_stmt = $con->prepare($user_query);
        $user_stmt->bind_param("i", $userId);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_row = $user_result->fetch_assoc()) {
            $userName = trim($user_row['first_name'] . ' ' . $user_row['last_name']);
            if (empty($userName)) {
                $userName = $user_row['username'];
            }
            $userEmail = $user_row['email'];
            $userNumber = $user_row['contact_number'];
            $userAddress = $user_row['address'];
        }
        $user_stmt->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                if (!isset($_SESSION['user-id'])) {
                    throw new Exception("User not logged in");
                }
                $paymentMethod = $_POST['PaymentMethod'];
                $allowedMethods = ['paymongo', 'cod'];
                if (!in_array($paymentMethod, $allowedMethods)) {
                    throw new Exception("Invalid payment method");
                }
                $userId = $_SESSION['user-id'];
                $status = 'Pending';

                $con->begin_transaction();

                // 1. Get cart items
                $stmt = $con->prepare("SELECT cart.*, product.price, inventory.inventoryID, product.sellerID
                                    FROM cart 
                                    JOIN inventory ON cart.inventoryID = inventory.inventoryID 
                                    JOIN product ON inventory.proID = product.proID 
                                    WHERE cart.userID = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                $sellerGroups = [];

                while ($row = $result->fetch_assoc()) {

                    $sellerID = $row['sellerID']; // must come from inventory join

                    $subtotal = $row['price'] * $row['quantity'];

                    $sellerGroups[$sellerID]['items'][] = $row;
                    $sellerGroups[$sellerID]['total'] =
                        ($sellerGroups[$sellerID]['total'] ?? 0) + $subtotal;
                }
                $totalSubtotal = 0;

                foreach ($sellerGroups as $group) {
                    $totalSubtotal += $group['total'];
                }
                $_SESSION['checkout_total'] = $totalSubtotal;
                if (empty($sellerGroups)) {
                    throw new Exception("Cart is empty");
                }

                // 2. Insert ONE order
                $orderIDs = [];

                foreach ($sellerGroups as $sellerID => $group) {

                    $stmt = $con->prepare("
                        INSERT INTO orders (userID, sellerID, status, price, time_ordered)
                        VALUES (?, ?, ?, ?, NOW())
                    ");

                    $stmt->bind_param("iisd", $userId, $sellerID, $status, $group['total']);
                    $stmt->execute();

                    $orderIDs[$sellerID] = $stmt->insert_id;
                }

                // 3. Insert order_items
                $stmt = $con->prepare("INSERT INTO order_items (orderID, inventoryID, quantity, price) 
                                    VALUES (?, ?, ?, ?)");

                foreach ($sellerGroups as $sellerID => $group) {

                    $orderID = $orderIDs[$sellerID];

                    foreach ($group['items'] as $item) {
                        $stmt->bind_param(
                            "iiid",
                            $orderID,
                            $item['inventoryID'],
                            $item['quantity'],
                            $item['price']
                        );
                        $stmt->execute();
                    }
                }

                if ($paymentMethod == "paymongo") {

                    $amount = (int) round($totalSubtotal * 100); 

                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => [
                            "Content-Type: application/json",
                            "Authorization: Basic " . base64_encode("sk_test_QTC4z9JnydE34pMzczadm82M:")
                        ],
                        CURLOPT_POSTFIELDS => json_encode([
                            "data" => [
                                "attributes" => [
                                    "line_items" => [[
                                        "name" => "Order #{$orderID}",  
                                        "amount" => $amount,
                                        "currency" => "PHP",
                                        "quantity" => 1
                                    ]],

                                    "payment_method_types" => ["gcash"],

                                    // ✅ VERY IMPORTANT (for webhook)
                                    "metadata" => [
                                        "order_id" => $orderID
                                    ],

                                    "success_url" => "http://localhost/success.php?order_id={$orderID}",
                                    "cancel_url" => "http://localhost/cancel.php?order_id={$orderID}"
                                ]
                            ]
                        ])
                    ]);

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $paymongo = json_decode($response, true);

                    if (!isset($paymongo['data']['attributes']['checkout_url'])) {
                        $con->rollback();
                        throw new Exception("Error creating PayMongo checkout session: " . ($paymongo['error']['message'] ?? 'Unknown error'));
                    }

                    $checkoutUrl = $paymongo['data']['attributes']['checkout_url'];
                    $_SESSION['pending_order_id'] = $orderID;
                    // ✅ Commit BEFORE redirect
                    $con->commit();

                    // ✅ CORRECT redirect
                    header("Location: " . $checkoutUrl);
                    exit;
                } else {    
                    $stmt = $con->prepare("DELETE FROM cart WHERE userID = ?");
                    $stmt->bind_param("i", $userId);
                    if ($stmt->execute()) {
                        $successMessage = "Order placed successfully!";
                        $con->commit();
                    } else {
                        $con->rollback();
                        throw new Exception("Error deleting cart items: " . $stmt->error);
                    }
                }
                
        } catch (Exception $e) {
            $successMessage = "Error placing order: " . $e->getMessage();
            $con->rollback();
        }
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
                                    
                                    if(isset($_SESSION['user-id'])) {
                                        // Fetch data from the database
                                        $userId = $_SESSION['user-id'];
                                        $query = "SELECT cart.*, product.name, product.price, product.image 
                                                FROM cart 
                                                JOIN inventory ON cart.inventoryID = inventory.inventoryID 
                                                JOIN product ON inventory.proID = product.proID 
                                                WHERE cart.userID = ?";
                                        $stmt = $con->prepare($query);
                                        $stmt->bind_param("i", $userId);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        // Initialize total subtotal
                                        $totalSubtotal = 0;

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {

                                                $productName = $row['name'];
                                                $productPrice = $row['price'];
                                                $productQuantity = $row['quantity'];
                                                $productImage = $row['image'];

                                                $subtotal = $productPrice * $productQuantity;
                                                $totalSubtotal += $subtotal;

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

                                    ?>
                                </div>
                                <div class="order_total">
                                    <p>Total Price:</p>
                                    <h1>PHP <?php echo number_format($_SESSION['checkout_total'] ?? 0, 2); ?></h1>
                                </div>
                            </div>
                        </div>
                        <div class="checkout_placeorder">
                            <div class="placeorder_header" style="background-color: black; color:white; padding: 10px;">
                                    <h1>Place your orders</h1> 
                                </div>
                        <div class="checkout_inputs">
                            <label for="YourName">Your Name:</label><br>
                            <input type="text" id="YourName" name="YourName" required placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($userName); ?>">
                            
                            <label for="YourEmail">Your Email:</label><br>
                            <input type="text" id="YourEmail" name="YourEmail" required placeholder="e.g. john@example.com" value="<?php echo htmlspecialchars($userEmail); ?>">
                            
                            <label for="YourNumber">Your Number:</label><br>
                            <input type="text" id="YourNumber" name="YourNumber" required placeholder="e.g. 123-456-7890" value="<?php echo htmlspecialchars($userNumber); ?>">
                            
                            <label for="Address">Address:</label><br>
                            <input type="text" id="Address" name="Address" required placeholder="e.g. 123 Main Street, New York, NY 12345" value="<?php echo htmlspecialchars($userAddress); ?>">
                            
                            <label for="PaymentMethod">Payment Method:</label><br>
                            <select id="PaymentMethod" name="PaymentMethod" required>
                                <option value="" disabled selected>Select Payment Method</option>
                                <option value="paymongo">GCASH payment  </option>
                                <option value="cod">Cash on Delivery</option>
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
