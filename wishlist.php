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
                            if (!isset($_SESSION['user-id'])) {
                                echo "<script>alert('You must log in first'); window.location='login.php';</script>";
                                exit;
                            } else {
                                if (isset($_POST['productId'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['productQuantity'])) {
                                    $productId = $_POST['productId']; // This is actually wishID from the query below
                                    $userId = $_SESSION['user-id'];
                                    $quantity = $_POST['productQuantity'];
                                    
                                    // We need the proID for this wishlist item
                                    $getProID = $con->prepare("SELECT proID FROM wishlist WHERE wishID = ?");
                                    $getProID->bind_param("i", $productId);
                                    $getProID->execute();
                                    $proRes = $getProID->get_result();
                                    $proRow = $proRes->fetch_assoc();
                                    $proID = $proRow['proID'];

                                    // Get inventoryID (assuming first available)
                                    $invQuery = $con->prepare("SELECT inventoryID FROM inventory WHERE proID = ? LIMIT 1");
                                    $invQuery->bind_param("i", $proID);
                                    $invQuery->execute();
                                    $invRes = $invQuery->get_result();
                                    $invRow = $invRes->fetch_assoc();
                                    $inventoryID = $invRow['inventoryID'];
                        
                                    $existingProductQuery = $con->prepare("SELECT * FROM cart WHERE userID = ? AND inventoryID = ? LIMIT 1");
                                    $existingProductQuery->bind_param("ii", $userId, $inventoryID);
                                    $existingProductQuery->execute();
                                    $existingProductResult = $existingProductQuery->get_result();
                        
                                    if ($existingProductResult->num_rows > 0) {
                                        $existingProduct = $existingProductResult->fetch_assoc();
                                        $newQuantity = $existingProduct['quantity'] + $quantity;
                        
                                        $updateQuery = $con->prepare("UPDATE cart SET quantity = ? WHERE userID = ? AND inventoryID = ?");
                                        $updateQuery->bind_param("iii", $newQuantity, $userId, $inventoryID);
                                        $success = $updateQuery->execute();
                                        $updateQuery->close();
                                    } else {
                                        $sql = "INSERT INTO cart (userID, inventoryID, quantity) VALUES (?, ?, ?)";
                                        $stmt = $con->prepare($sql);
                                        $stmt->bind_param("iii", $userId, $inventoryID, $quantity);
                                        $success = $stmt->execute();
                                        $stmt->close();
                                    }

                                    // Delete from wishlist
                                    $deleteSql = "DELETE FROM wishlist WHERE wishID = ?";
                                    $deleteStmt = $con->prepare($deleteSql);
                                    $deleteStmt->bind_param("i", $productId);
                                    $deleteStmt->execute();
                                    $deleteStmt->close();
                    
                                    echo "<script>alert('Data saved successfully!'); window.location='cart.php';</script>";
                                    exit;
                                }
                            }
                        }
                        
                        
                        if(isset($_SESSION['user-id'])){
                            $userId = $_SESSION['user-id'];
                            $sql = "SELECT wishlist.*, product.name AS Wishlist_Name, product.price AS Wishlist_Price, product.image AS Wishlist_Image, wishlist.wishID AS ID 
                                    FROM wishlist 
                                    JOIN product ON wishlist.proID = product.proID 
                                    WHERE wishlist.userID = ?";
                            $stmt = $con->prepare($sql);
                            $stmt->bind_param("i", $userId);
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
