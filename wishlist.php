<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'additional/header.php'; ?>

    <section>
        <div class="wishlist-container" style="text-align: center; padding: 18px;">
            <h2>Your Wishlist</h2>
            <div style="display: flex; flex-wrap: wrap; justify-content: center;">
                <div class="cart-product" style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; max-width: 1000px;">
                    <?php
                        include('./components/connect.php');

                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            if (!isset($_SESSION['user-email']) || !isset($_SESSION['user-id'])) {
                                echo "<script>alert('You must log in first'); window.location='login.php';</script>";
                                exit;
                            } else {
                                if (isset($_POST['productId'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['productQuantity']) && isset($_SESSION['user-email']) && isset($_SESSION['user-id'])) {
                                    $productId = $_POST['productId'];
                                    $productImage = $_POST['productImage'];
                                    $productName = $_POST['productName'];
                                    $productPrice = $_POST['productPrice'];
                                    $userEmail = $_SESSION['user-email'];
                                    $userId = $_SESSION['user-id'];
                                    $quantity = $_POST['productQuantity'];
                        
                                    $existingProductQuery = $con->prepare("SELECT * FROM cart WHERE Product_Name = ? AND user_email = ? LIMIT 1");
                                    $existingProductQuery->bind_param("ss", $productName, $userEmail);
                                    $existingProductQuery->execute();
                                    $existingProductResult = $existingProductQuery->get_result();
                        
                                    if ($existingProductResult->num_rows > 0) {
                                        $existingProduct = $existingProductResult->fetch_assoc();
                                        $newQuantity = $existingProduct['Product_Quantity'] + $quantity;
                        
                                        $updateQuery = $con->prepare("UPDATE cart SET Product_Quantity = ? WHERE Product_Name = ? AND user_email = ?");
                                        $updateQuery->bind_param("iss", $newQuantity, $productName, $userEmail);
                                        $success = $updateQuery->execute();
                                        $updateQuery->close();
                        
                                        $deleteSql = "DELETE FROM wishlist WHERE ID = ?";
                                        $deleteStmt = $con->prepare($deleteSql);
                                        $deleteStmt->bind_param("i", $productId);
                                        $deleteStmt->execute();
                                        $deleteStmt->close();
                        
                                        echo "<script>alert('Data saved successfully!'); window.location='cart.php';</script>";
                                        exit;
                                    } else {
                                        $totalPrice = $productPrice * $quantity;
                                        $sql = "INSERT INTO cart (Product_Name, Product_Price, Product_Quantity, Product_Image, user_id, user_email) VALUES (?, ?, ?, ?, ?, ?)";
                                        $stmt = $con->prepare($sql);
                                        $stmt->bind_param("sdisss", $productName, $totalPrice, $quantity, $productImage, $userId, $userEmail);
                                        $success = $stmt->execute();
                                        $stmt->close();
                        
                                        $deleteSql = "DELETE FROM wishlist WHERE ID = ?";
                                        $deleteStmt = $con->prepare($deleteSql);
                                        $deleteStmt->bind_param("i", $productId);
                                        $deleteStmt->execute();
                                        $deleteStmt->close();
                        
                                        echo "<script>alert('Data saved successfully!'); window.location='cart.php';</script>";
                                        exit;
                                    }
                                }
                            }
                        }
                        
                        
                        if(isset($_SESSION['user-email'])){
                            $useremail = $_SESSION['user-email'];
                            $sql = "SELECT * FROM wishlist WHERE user_email = ?";
                            $stmt = $con->prepare($sql);
                            $stmt->bind_param("s", $useremail);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if($result->num_rows > 0){
                                while($row = $result->fetch_assoc()){
                                    ?>
                                    <div class="cart-item" style="padding: 10px; border: 1px solid #ccc;  height: 328px;">
                                        <form action="" method="post">
                                            <input type="hidden" name="productId" value="<?php echo $row['ID']; ?>">
                                            <input type="hidden" name="productImage" value="<?php echo $row['Wishlist_Image']; ?>">
                                            <input type="hidden" name="productName" value="<?php echo $row['Wishlist_Name']; ?>">
                                            <input type="hidden" name="productPrice" value="<?php echo $row['Wishlist_Price']; ?>">
                                            
                                            <div class="cart-img" style="width: 100%; height: 155px;">
                                                <img src="uploads/images/<?php echo $row['Wishlist_Image'];?>" alt="" style="max-width: 100%; max-height: 100%;">
                                            </div>
                                            <h2 style="font-size: 15px; margin-top: 5px;"><?php echo $row['Wishlist_Name']; ?></h2>
                                            <p style="font-size: 14px;"><?php echo $row['Wishlist_Price']; ?></p>
                                            <div class="input-group" style="display: flex; align-items: center; width: 100%;">
                                                <p style="font-size: 14px; text-align: right; margin-right:45px; color: #bababa;">Quantity:</p>
                                                <input type="number" name="productQuantity" value="1" min="1" max="1000" style="width: 50px;">
                                            </div>
                                            <div class="wishlist-btn" style="display: flex; flex-direction: column; margin-top: 10px;">
                                                <button type="submit" name="addToCart" style="cursor: pointer; padding: 10px; background-color: black; color: white; font-size: 12px; border: none; border-radius: 5px; margin-bottom: 5px;">Add To Cart</button>
                                                <button type="button" onclick="removeFromWishlist(<?php echo $row['ID']; ?>)" style="cursor: pointer; padding: 10px; background-color: white; border: 1px solid black; color: black; border-radius: 5px;">Remove From Wishlist</button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo "<p>Your Wishlist is empty.</p>";
                            }
                        } else {
                            echo '<div style="text-align: center;">
                                  <p>Please log in to view your wishlist.</p>
                                  <a href="login.php"><button style="cursor: pointer; width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Login</button></a>
                                  </div>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>

    <script>
        function removeFromWishlist(ID) {
            if(confirm("Are you sure you want to remove this item from your wishlist?")) {
                window.location.href = "remove_from_wishlist.php?ID=" + ID;
            }
        }
    </script>
</body>
</html>
