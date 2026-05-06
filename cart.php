<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Cart</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .swal-cancel-styled {
        border: 0.5px solid #2C2825 !important;
        color: #2C2825 !important;
        background-color: #F7F3EE !important;
    }
    .swal-cancel-styled:hover {
        background-color: #2C2825 !important;
        color: #F7F3EE !important;
    }
</style>
<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="cart-container">
            <h1 style="text-align: center; font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: 3.8rem; letter-spacing: 0.04em; color: #2C2825;">Shopping Cart</h1>
            <div class="cart-content">
                <div class="cart-product">
                    <?php
                    include 'components/connect.php';
                    if(isset($_SESSION['user-id'])) {
                        $userId = $_SESSION['user-id'];
                        $query = "SELECT cart.*, product.name, product.price, product.image, cart.quantity AS Product_Quantity, cart.cartID AS ID, sizes.sizes 
                                  FROM cart 
                                  JOIN inventory ON cart.inventoryID = inventory.inventoryID 
                                  JOIN product ON inventory.proID = product.proID 
                                  JOIN sizes ON inventory.sizeID = sizes.sizeID
                                  WHERE cart.userID = ?";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $totalPrice = 0;

                        if ($result->num_rows > 0) {
                            while ($product = $result->fetch_assoc()) {
                                $originalprice = $product['price'];
                                $subtotal = $originalprice * $product['Product_Quantity'];
                                $totalPrice += $subtotal;
                                ?>  
                                <div class="cart-item" data-product-id="<?php echo $product['ID']; ?>" data-price="<?php echo $originalprice; ?>" style="position: relative;">
                                    <div style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                                        <input type="checkbox" class="item-checkbox" checked value="<?php echo $product['ID']; ?>" style="width: 20px; height: 20px; cursor: pointer;">
                                    </div>
                                    <div class="cart-img">
                                        <?php
                                        $imagePath = 'uploads/images/' . $product['image'];
                                        if (file_exists($imagePath)) {
                                            ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Product Image">
                                        <?php } else { ?>
                                            <p>Image not found</p>
                                        <?php } ?>
                                    </div>
                                    <h2 style="font-size: 15px; margin-top:5px;"><?php echo htmlspecialchars($product['name']); ?></h2>
                                    <p style="font-size: 13px; color: #8c8b8b; margin: 3px 0;">Size: <span style="color: #2C2825; font-weight: 500;"><?php echo htmlspecialchars($product['sizes']); ?></span></p>
                                    <p class="item-price" style="font-size: 14px;">PHP <?php echo htmlspecialchars($product['price']); ?></p>
                                    <div class="input-group" style="width: 100%; padding: 3px; display: flex; align-items: center;">
                                        <p style="font-size: 12px; margin-right:5px; color: #adadad;">Qty: </p>
                                        <input type="number" style="width: 5vh;" min="1" max="1000" step="1" value="<?php echo htmlspecialchars($product['Product_Quantity']); ?>" class="quantity-input" name="quantity">
                                        <button onclick="editQuantity(<?php echo $product['ID']; ?>)" style="height: 25px; padding:2.50px; border-radius: 2px; width: fit-content; background-color: black; margin-left: 2px; cursor: pointer; border: 1px solid black;"><i class="fa-solid fa-pen-to-square" style="color: white; font-size: 14px;"></i></button>
                                    </div>
                                    <p style="font-size: 14px;">Subtotal: <?php echo htmlspecialchars($subtotal); ?></p>
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $product['ID']; ?>)" style="cursor:pointer; border:0.5px solid #2C2825; background:transparent; color:#2C2825; font-family:'Jost',sans-serif; font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; padding:8px 0; width:100%; transition:background-color 0.25s, color 0.25s;" onmouseover="this.style.backgroundColor='#2C2825';this.style.color='#F7F3EE';" onmouseout="this.style.backgroundColor='transparent';this.style.color='#2C2825';">Remove</button>
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
                            <a href="Login.php"><button style="cursor: pointer; width: 25vh; border: none; padding: 15px 30px; background-color: #2C2825; color: #F7F3EE; font-family: Jost, sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; transition: background-color 0.25s;">Login</button></a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php if(isset($_SESSION['user-id'])): ?>
                <div class="total-price" style="text-align: center;">
                    <p style="margin: 0; margin-top: 20px; color:#8c8b8b; font-size: 13px;">Total Price:</p>
                    <p id="totalPriceDisplay" style="margin: 0; font-size: 25px;">PHP <?php echo number_format($totalPrice, 2); ?></p>
                </div>
                
                <div class="cart-btn">
                    <?php if (isset($result) && $result->num_rows > 0) { ?>
                        <button id="checkoutBtn" style="cursor: pointer; border: none; padding: 15px 30px; background-color: #2C2825; color: #F7F3EE; font-family: Jost, sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; transition: background-color 0.25s;">Proceed To Checkout</button>
                    <?php } else { ?>
                        <button disabled>Proceed To Checkout</button>
                    <?php } ?>
                    <button onclick="deleteAllItems()" onmouseover="this.style.backgroundColor='#2C2825';this.style.color='#F7F3EE';" onmouseout="this.style.backgroundColor='transparent';this.style.color='#2C2825';" style="cursor: pointer; border: 0.5px solid #2C2825; padding: 15px 30px; background-color: transparent; color: #2C2825; font-family: Jost, sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; transition: background-color 0.25s;">Delete All</button>
                </div>  
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
    <script src="scripts/cart_functions.js"></script>
    <script>
        const params = new URLSearchParams(window.location.search);
        if (params.get('removed') === '1') {
    Swal.fire({
        title: 'Item Removed',
        icon: 'success',
        confirmButtonColor: '#2C2825',
        timer: 2000,
        showConfirmButton: false
    });
}
if (params.get('deleted_all') === '1') {
    Swal.fire({
        title: 'All items deleted!',
        icon: 'success',
        confirmButtonColor: '#2C2825',
        timer: 2000,
        showConfirmButton: false
    });
}
if (params.get('deleted_all') === '0') {
    Swal.fire({
        title: 'Something went wrong',
        text: 'Error deleting items from cart.',
        icon: 'error',
        confirmButtonColor: '#2C2825',
    });
}
if (params.get('removed') === '0') {
    Swal.fire({
        title: 'Something went wrong',
        text: 'Error removing item from cart.',
        icon: 'error',
        confirmButtonColor: '#2C2825',
    });
}
    if (params.get('success') === '1') {
        Swal.fire({
            title: 'Added to Cart!',
            icon: 'success',
            confirmButtonColor: '#2C2825',
            timer: 2000,
            showConfirmButton: false
        });
    }

    function updateTotalPrice() {
        let total = 0;
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        checkedItems.forEach(checkbox => {
            const cartItem = checkbox.closest('.cart-item');
            const price = parseFloat(cartItem.getAttribute('data-price'));
            const quantity = parseInt(cartItem.querySelector('.quantity-input').value);
            total += price * quantity;
        });
        document.getElementById('totalPriceDisplay').textContent = 'PHP ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = checkedItems.length === 0;
            checkoutBtn.style.opacity = checkedItems.length === 0 ? '0.5' : '1';
            checkoutBtn.style.cursor = checkedItems.length === 0 ? 'not-allowed' : 'pointer';
        }
    }

    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', updateTotalPrice);
    });

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length > 0) {
                window.location.href = 'checkout.php?selected_items=' + selectedIds.join(',');
            } else {
                Swal.fire({
                    title: 'No items selected',
                    text: 'Please select at least one item to checkout.',
                    icon: 'warning',
                    confirmButtonColor: '#2C2825',
                });
            }
        });
    }

    function editQuantity(productId) {
        var newQuantity = document.querySelector('[data-product-id="' + productId + '"] .quantity-input').value;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_quantity.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                window.location.href = "cart.php";
            }
        };
        xhr.send("productId=" + productId + "&quantity=" + newQuantity);
    }

    function removeFromCart(productId) {
        Swal.fire({
            title: 'Remove item?',
            text: 'Are you sure you want to remove this item from the cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2C2825',
            cancelButtonColor: '#F7F3EE',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel',
            customClass: {
                cancelButton: 'swal-cancel-styled'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "remove_from_cart.php?productId=" + productId;
            }
        });
    }

    function deleteAllItems() {
        Swal.fire({
            title: 'Delete everything?',
            text: 'Are you sure you want to delete all items from the cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2C2825',
            cancelButtonColor: '#F7F3EE',
            confirmButtonText: 'Yes, delete all',
            cancelButtonText: 'Cancel',
            customClass: {
                cancelButton: 'swal-cancel-styled'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "delete_all.php?confirmation=yes";
            }
        });
    }

    function calculateTotal() {
        Swal.fire({
            title: 'Total Price',
            text: 'PHP <?php echo number_format($totalPrice, 2); ?>',
            icon: 'info',
            confirmButtonColor: '#2C2825',
        });
    }
    </script>
</body>
</html>
