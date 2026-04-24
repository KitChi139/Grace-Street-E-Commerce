

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick View</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Add JavaScript for alert -->
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php

        include('./components/connect.php');

        $saved_successfully = false;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_SESSION['user-id'])) {
                echo "<script>alert('You must log in first'); window.location='login.php';</script>";
                exit;
            } else {
                if (isset($_POST['pid'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['quantity'])) {
                    $pid = $_POST['pid'];
                    $userId = $_SESSION['user-id'];
                    $quantity = $_POST['quantity'];

                    // Get inventoryID (assuming first available)
                    $invQuery = $con->prepare("SELECT inventoryID FROM inventory WHERE proID = ? LIMIT 1");
                    $invQuery->bind_param("i", $pid);
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

                        echo "<script>alert('Data saved successfully!'); window.location='cart.php';</script>";
                        exit;
                    } else {
                        $sql = "INSERT INTO cart (userID, inventoryID, quantity) VALUES (?, ?, ?)";
                        $stmt = $con->prepare($sql);
                        $stmt->bind_param("iii", $userId, $inventoryID, $quantity);
                        $success = $stmt->execute();
                        $stmt->close();

                        echo "<script>alert('Data saved successfully!'); window.location='cart.php';</script>";
                        exit;
                    }
                }
            }
        }
        ?>
    <section>
        
    <?php
     $pid = $_GET['pid'];
     $select_products = $con->prepare("SELECT proID, name, image, price, description FROM `product` WHERE proID = ?"); 
     $select_products->bind_param("i", $pid);
     $select_products->execute();
     $select_products->store_result();

     if($select_products->num_rows > 0){
      $select_products->bind_result($id, $name, $image, $price,$description);
      while($select_products->fetch()){
   ?>
        <form action=""  method="POST" class="box" onsubmit="showAlert()">
            <div class="view-container">
                <div class="view-item">
                    <input type="hidden" name="pid" value="<?= $id; ?>">
                    <input type="hidden" name="productImage" value="<?= $image; ?>">
                    <input type="hidden" name="productName" value="<?= $name; ?>">
                    <input type="hidden" name="productPrice" value="<?= $price; ?>">
                    <div class="view-image">
                        <img src="uploads/images/<?php echo $image;?>" alt="Product Image">
                    </div>
                    <div class="view-info">
                        <div class="info-header" style="text-align: center; padding: 10px;">
                            <p style="margin: 0; font-size: 12px;">Product Name</p>
                            <h1 style="margin: 0;"><?php echo $name;?></h1>
                        </div>
                        <div class="info-body" style="padding-left: 10px;">
                            <div class="info-content">
                                <p style="color: #8c8989;">Description:</p>
                                <p style="max-height: calc(1.2em * 4); overflow: hidden;"><?php echo $description;?></p>
                            </div>    
                            <div class="info-numbers" >
                                <div class="info-price">
                                    <p style="display: inline-block; margin: 0; font-size: 13px; line-height:19px; color: #8c8989;">PHP<br> <span style="color:black; font-size: 20px; font-weight: 500;"><?php echo $price;?></span></p>
                                </div>
                                <div class="info-quantity">
                                    <p style="margin: 0; color:#8c8989; font-size: 13px;">Quantity: </p>
                                    <input type="number" name="quantity" value="1" min="1" max="999" style="width: 40px;" class="product-quantity">
                                </div>
                            </div>
                            <div class="info-btn" style="text-align: center;">
                                <button style="margin-top:10px; width: 100%; padding: 10px; font-size: 12px; background-color: black; color: white; border: none;" type="submit">Add To Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form> 
        <?php
      }
   }
   ?>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
