<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Cart</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="cart-container">
            <h1 style="text-align: center;">Shopping Cart</h1>
            <div class="cart-content">
                <div class="cart-product">
                    <?php
                    include 'components/connect.php';
                    if(isset($_SESSION['user-email'])) {
                        $userEmail = $_SESSION['user-email'];
                        $query = "SELECT * FROM cart WHERE User_Email = ?";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("s", $userEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $totalPrice = 0;

                        if ($result->num_rows > 0) {
                            while ($product = $result->fetch_assoc()) {
                                $originalprice = $product['Product_Price'];
                                $subtotal = $originalprice * $product['Product_Quantity'];
                                $totalPrice += $subtotal;
                                ?>  
                                <div class="cart-item" data-product-id="<?php echo $product['ID']; ?>">
                                    <div class="cart-img">
                                        <?php
                                        $imagePath = 'uploads/images/' . $product['Product_Image'];
                                        if (file_exists($imagePath)) {
                                            ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Product Image">
                                        <?php } else { ?>
                                            <p>Image not found</p>
                                        <?php } ?>
                                    </div>
                                    <h2 style="font-size: 15px; margin-top:5px;"><?php echo htmlspecialchars($product['Product_Name']); ?></h2>
                                    <p class="item-price" style="font-size: 14px;">PHP <?php echo htmlspecialchars($product['Product_Price']); ?></p>
                                    <div class="input-group" style="width: 100%; padding: 3px; display: flex; align-items: center;">
                                        <p style="font-size: 12px; margin-right:5px; color: #adadad;">Qty: </p>
                                        <input type="number" style="width: 5vh;" min="1" max="1000" step="1" value="<?php echo htmlspecialchars($product['Product_Quantity']); ?>" class="quantity-input" name="quantity">
                                        <button onclick="editQuantity(<?php echo $product['ID']; ?>)" style="height: 25px; padding:2.50px; border-radius: 2px; width: fit-content; background-color: black; margin-left: 2px; cursor: pointer; border: 1px solid black;"><img style="filter: invert(100); width: 17px; height: auto;"src="img/edit.svg" alt="Edit"></button>
                                    </div>
                                    <p style="font-size: 14px;">Subtotal: <?php echo htmlspecialchars($subtotal); ?></p>
                                    <button class="remove-btn" style="cursor: pointer;" onclick="removeFromCart(<?php echo $product['ID']; ?>)">Remove from cart</button>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <p>Your cart is empty</p>
                            <?php
                        }
                    } else {
                        ?>
                        <div style="text-align: center;">
                            <p>Please log in to view your cart.</p>
                            <a href="Login.php"><button style="cursor: pointer;  width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Login</button></a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php if(isset($_SESSION['user-email'])): ?>
                <div class="total-price" style="text-align: center;">
                    <p style="margin: 0; margin-top: 20px; color:#8c8b8b; font-size: 13px;">Total Price:</p>
                    <p id="totalPriceDisplay" style="margin: 0; font-size: 25px;">PHP <?php echo number_format($totalPrice, 2); ?></p>
                </div>
                
                <div class="cart-btn">
                    <?php if (isset($result) && $result->num_rows > 0) { ?>
                        <a href="checkout.php"><button style="cursor: pointer;">Proceed To Checkout</button></a>
                    <?php } else { ?>
                        <button disabled>Proceed To Checkout</button>
                    <?php } ?>
                    <button onclick="deleteAllItems()" style="cursor: pointer; background-color: white;">Delete All</button>
                </div>  
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script src="scripts/cart_functions.js"></script>
    <script>
        function editQuantity(productId) {
            var newQuantity = document.querySelector('[data-product-id="' + productId + '"] .quantity-input').value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_quantity.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.trim() === "success") {
                        window.location.href="cart.php";
                    } else {
                        window.location.href="cart.php";
                    }
                }
            };
            xhr.send("productId=" + productId + "&quantity=" + newQuantity);
        }

        function removeFromCart(productId) {
            var confirmation = confirm("Are you sure you want to remove this item from the cart?");
            if (confirmation) {
                window.location.href = "remove_from_cart.php?productId=" + productId;
            }
        }
        
        function deleteAllItems() {
            var confirmation = confirm("Are you sure you want to delete all items from the cart?");
            if (confirmation) {
                window.location.href = "delete_all.php?confirmation=yes";
            }
        }

        function calculateTotal() {
            alert("Total Price: PHP <?php echo number_format($totalPrice, 2); ?>");
        }
    </script>
</body>
</html>
