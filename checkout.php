    <?php
    error_reporting(E_ALL & ~E_NOTICE); // Suppress notices

    @session_start(); // Suppress "session_start() already active" notice
    include 'components/connect.php';
    include 'components/encryption.php';

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
            $userNumber = decrypt_data($user_row['contact_number']);
            $userAddress = decrypt_data($user_row['address']);
        }
        $user_stmt->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                if (!isset($_SESSION['user-id'])) {
                    throw new Exception("User not logged in");
                }
                
                $selected_items_str = $_POST['selected_items_list'] ?? '';
                if (empty($selected_items_str)) {
                    throw new Exception("No items selected for checkout");
                }
                $selected_items_arr = explode(',', $selected_items_str);
                $placeholders = implode(',', array_fill(0, count($selected_items_arr), '?'));

                $paymentMethod = htmlspecialchars($_POST['PaymentMethod']);
                $allowedMethods = ['paymongo', 'cod'];
                if (!in_array($paymentMethod, $allowedMethods)) {
                    throw new Exception("Invalid payment method");
                }
                $userId = $_SESSION['user-id'];
                $status = 'Pending';

                $con->begin_transaction();

                // 1. Get ONLY selected cart items
                $query = "SELECT cart.*, product.price, inventory.inventoryID, product.sellerID
                          FROM cart 
                          JOIN inventory ON cart.inventoryID = inventory.inventoryID 
                          JOIN product ON inventory.proID = product.proID 
                          WHERE cart.userID = ? AND cart.cartID IN ($placeholders)";
                
                $stmt = $con->prepare($query);
                $types = "i" . str_repeat("i", count($selected_items_arr));
                $stmt->bind_param($types, $userId, ...$selected_items_arr);
                $stmt->execute();
                $result = $stmt->get_result();

                $sellerGroups = [];
                if ($result->num_rows === 0) {
                    throw new Exception("Cart is empty or items not found");
                }

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


                // 2. Insert ONE order

                // 2. Create BULK ORDER (customer_orders)
                $stmt = $con->prepare("
                    INSERT INTO main_order (userID, total_price, status, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->bind_param("ids", $userId, $totalSubtotal, $status);
                $stmt->execute();
                $mainOrderID = $stmt->insert_id;

                // 3. Create SELLER ORDERS (orders)
                foreach ($sellerGroups as $sellerID => $group) {
                    $stmt = $con->prepare("
                        INSERT INTO orders (mainOrderID, userID, sellerID, status, price, time_ordered)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->bind_param("iiisd", $mainOrderID, $userId, $sellerID, $status, $group['total']);
                    $stmt->execute();
                    $orderID = $stmt->insert_id;

                    // 4. Create ITEMS (order_items)
                    foreach ($group['items'] as $item) {
                        $stmt = $con->prepare("
                            INSERT INTO order_items (orderID, inventoryID, quantity, price)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->bind_param("iiid", $orderID, $item['inventoryID'], $item['quantity'], $item['price']);
                        $stmt->execute();
                    }
                }

                if ($paymentMethod == "paymongo") {
                    $amount = $totalSubtotal * 100; // centavos

                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api.paymongo.com/v1/checkout_sessions",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_HTTPHEADER => [
                            "Content-Type: application/json",
                            "Authorization: Basic " . base64_encode("sk_test_QTC4z9JnydE34pMzczadm82M:")
                        ],
                        CURLOPT_POSTFIELDS => json_encode([
                            "data" => [
                                "attributes" => [
                                    "line_items" => [[
                                        "name" => "Order #{$mainOrderID}",
                                        "amount" => $amount,
                                        "currency" => "PHP",
                                        "quantity" => 1
                                    ]],

                                    "payment_method_types" => ["gcash"],

                                    // ✅ VERY IMPORTANT (for webhook)
                                    "metadata" => [
                                        "customer_order_id" => $mainOrderID
                                    ],

                                    "success_url" => "success.php?order_id={$mainOrderID}",
                                    "cancel_url" => "cancel.php?order_id={$mainOrderID}"
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
                    $_SESSION['pending_order_id'] = $mainOrderID;
                    // ✅ Commit BEFORE redirect
                    $con->commit();

                    // ✅ CORRECT redirect
                    header("Location: " . $checkoutUrl);
                    exit;
                } else {    
                    $stmt = $con->prepare("DELETE FROM cart WHERE userID = ? AND cartID IN ($placeholders)");
                    $stmt->bind_param($types, $userId, ...$selected_items_arr);
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>
    <body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="checkout_container" style="max-width: 1100px; margin: 0 auto; padding: 3rem 2.5rem;">
            <h1 style="font-family: 'Cormorant Garamond', serif; font-weight: 300; font-size: 3.8rem; text-align: center; margin-bottom: 2.5rem; color: #2C2825; letter-spacing: 0.04em;">Checkout</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirm('Are you sure you want to place the order?');">
                <div style="display: flex; gap: 2.5rem; align-items: flex-start; flex-wrap: nowrap;">

                    <!-- LEFT: Order Summary -->
                    <div style="flex: 1; min-width: 280px; background: rgba(247,243,238,0.85); border: 0.5px solid #E8DED2; border-radius: 12px; box-shadow: 0 8px 24px rgba(44,40,37,0.10); overflow: hidden;">
                        <div style="padding: 1.2rem 1.5rem; border-bottom: 0.5px solid #E8DED2;">
                            <h2 style="font-family: 'Cormorant Garamond', serif; font-weight: 400; font-size: 1.5rem; margin: 0; color: #2C2825; letter-spacing: 0.04em;">Your Orders</h2>
                        </div>
                        <div class="checkout_item" style="padding: 1.2rem; display: flex; flex-direction: column; gap: 0.75rem; max-height: 400px; overflow-y: auto;">
                            <?php
                            if(isset($_SESSION['user-id'])) {
                                $userId = $_SESSION['user-id'];
                                $selected_items_url = $_GET['selected_items'] ?? '';
                                $totalSubtotal = 0;

                                if (!empty($selected_items_url)) {
                                    $selected_ids = explode(',', $selected_items_url);
                                    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
                                    
                                    $query = "SELECT cart.*, product.name, product.price, product.image 
                                            FROM cart 
                                            JOIN inventory ON cart.inventoryID = inventory.inventoryID 
                                            JOIN product ON inventory.proID = product.proID 
                                            WHERE cart.userID = ? AND cart.cartID IN ($placeholders)";
                                    
                                    $stmt = $con->prepare($query);
                                    $types = "i" . str_repeat("i", count($selected_ids));
                                    $stmt->bind_param($types, $userId, ...$selected_ids);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $productName = $row['name'];
                                            $productPrice = $row['price'];
                                            $productQuantity = $row['quantity'];
                                            $productImage = $row['image'];
                                            $subtotal = $productPrice * $productQuantity;
                                            $totalSubtotal += $subtotal;
                                            echo '
                                            <div style="display:flex; align-items:center; gap:1rem; background:rgba(247,243,238,1); border:0.5px solid #E8DED2; border-radius:8px; padding:0.75rem;">
                                                <div style="width:60px; height:60px; flex-shrink:0; border-radius:6px; overflow:hidden;">
                                                    <img src="uploads/images/' . $productImage . '" alt="' . htmlspecialchars($productName) . '" style="width:100%; height:100%; object-fit:cover;">
                                                </div>
                                                <div style="flex:1; min-width:0;">
                                                    <p style="margin:0; font-family:\'Jost\',sans-serif; font-size:0.85rem; font-weight:500; color:#2C2825; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . htmlspecialchars($productName) . '</p>
                                                    <p style="margin:0; font-size:0.75rem; color:#A09486;">PHP ' . number_format($productPrice, 2) . ' &times; ' . $productQuantity . '</p>
                                                </div>
                                                <p style="margin:0; font-size:0.85rem; font-weight:500; color:#2C2825; white-space:nowrap;">PHP ' . number_format($subtotal, 2) . '</p>
                                            </div>';
                                        }
                                    } else {
                                        echo '<p style="color:#A09486; font-size:0.85rem; text-align:center; padding: 1rem 0;">No products found.</p>';
                                    }
                                } else {
                                    echo '<p style="color:#A09486; font-size:0.85rem; text-align:center; padding: 1rem 0;">No items selected for checkout.</p>';
                                }
                            } else {
                                echo '<p style="color:#A09486; font-size:0.85rem;">Please log in to view your cart.</p>';
                            }
                            ?>
                        </div>
                        <div style="padding: 1rem 1.5rem; border-top: 0.5px solid #E8DED2; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-family:'Jost',sans-serif; font-size:0.8rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486;">Total</span>
                            <span style="font-family:'Cormorant Garamond',serif; font-size:1.6rem; font-weight:400; color:#2C2825;">PHP <?php echo number_format($totalSubtotal, 2); ?></span>
                        </div>
                    </div>

                    <!-- RIGHT: Place Order Form -->
                    <div style="flex: 1; min-width: 280px; background: rgba(247,243,238,0.85); border: 0.5px solid #E8DED2; border-radius: 12px; box-shadow: 0 8px 24px rgba(44,40,37,0.10); overflow: hidden;">
                        <div style="padding: 1.2rem 1.5rem; border-bottom: 0.5px solid #E8DED2;">
                            <h2 style="font-family: 'Cormorant Garamond', serif; font-weight: 400; font-size: 1.5rem; margin: 0; color: #2C2825; letter-spacing: 0.04em;">Place Your Order</h2>
                        </div>
                        <div style="padding: 1.5rem;">
                            <div style="margin-bottom: 1rem;">
                                <label style="font-family:'Jost',sans-serif; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486; display:block; margin-bottom:0.4rem;">Your Name</label>
                                <input type="text" id="YourName" name="YourName" required placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($userName); ?>"
                                    style="width:100%; box-sizing:border-box; padding:12px 14px; border:0.5px solid #E8DED2; background:rgba(232,222,210,0.3); font-family:'Jost',sans-serif; font-size:0.85rem; color:#2C2825; outline:none;">
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="font-family:'Jost',sans-serif; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486; display:block; margin-bottom:0.4rem;">Your Email</label>
                                <input type="text" id="YourEmail" name="YourEmail" required placeholder="e.g. john@example.com" value="<?php echo htmlspecialchars($userEmail); ?>"
                                    style="width:100%; box-sizing:border-box; padding:12px 14px; border:0.5px solid #E8DED2; background:rgba(232,222,210,0.3); font-family:'Jost',sans-serif; font-size:0.85rem; color:#2C2825; outline:none;">
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="font-family:'Jost',sans-serif; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486; display:block; margin-bottom:0.4rem;">Your Number</label>
                                <input type="text" id="YourNumber" name="YourNumber" required placeholder="e.g. 123-456-7890" value="<?php echo htmlspecialchars($userNumber); ?>"
                                    style="width:100%; box-sizing:border-box; padding:12px 14px; border:0.5px solid #E8DED2; background:rgba(232,222,210,0.3); font-family:'Jost',sans-serif; font-size:0.85rem; color:#2C2825; outline:none;">
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="font-family:'Jost',sans-serif; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486; display:block; margin-bottom:0.4rem;">Address</label>
                                <input type="text" id="Address" name="Address" required placeholder="e.g. 123 Main Street" value="<?php echo htmlspecialchars($userAddress); ?>"
                                    style="width:100%; box-sizing:border-box; padding:12px 14px; border:0.5px solid #E8DED2; background:rgba(232,222,210,0.3); font-family:'Jost',sans-serif; font-size:0.85rem; color:#2C2825; outline:none;">
                            </div>
                            <div style="margin-bottom: 1.5rem;">
                                <label style="font-family:'Jost',sans-serif; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; color:#A09486; display:block; margin-bottom:0.4rem;">Payment Method</label>
                                <select id="PaymentMethod" name="PaymentMethod" required
                                    style="width:100%; box-sizing:border-box; padding:12px 14px; border:0.5px solid #E8DED2; background:rgba(232,222,210,0.3); font-family:'Jost',sans-serif; font-size:0.85rem; color:#2C2825; outline:none; appearance:none; cursor:pointer;">
                                    <option value="" disabled selected>Select Payment Method</option>
                                    <option value="paymongo">GCash Payment</option>
                                </select>
                            </div>
                            <input type="hidden" name="selected_items_list" value="<?php echo htmlspecialchars($_GET['selected_items'] ?? ''); ?>">
                            <input type="hidden" name="status" value="0">
                            <input type="submit" value="Place Order"
                                style="width:100%; padding:14px; background:#2C2825; color:#F7F3EE; border:none; font-family:'Jost',sans-serif; font-size:0.8rem; letter-spacing:0.1em; text-transform:uppercase; cursor:pointer; transition:background-color 0.25s;"
                                onmouseover="this.style.backgroundColor='#8B6F56'" onmouseout="this.style.backgroundColor='#2C2825'">
                        </div>
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
