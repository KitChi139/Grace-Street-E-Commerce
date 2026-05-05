

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
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php

        include('./components/connect.php');

        $saved_successfully = false;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_SESSION['user-id'])) {
                echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'warning', title: 'Login Required', text: 'You must log in first.', confirmButtonColor: '#2C2825' }); }); window.location='login.php';</script>";
                exit;
            } else {
                if (isset($_POST['pid'], $_POST['productImage'], $_POST['productName'], $_POST['productPrice'], $_POST['quantity'], $_POST['size'])) {
                    $pid = $_POST['pid'];
                    $userId = $_SESSION['user-id'];
                    $quantity = $_POST['quantity'];
                    $sizeID = $_POST['size'];

                    // Get inventoryID for the selected size
                    $invQuery = $con->prepare("SELECT inventoryID FROM inventory WHERE proID = ? AND sizeID = ? LIMIT 1");
                    $invQuery->bind_param("ii", $pid, $sizeID);
                    $invQuery->execute();
                    $invRes = $invQuery->get_result();
                    
                    if ($invRes->num_rows > 0) {
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

                            echo "<script>window.location='cart.php?success=1';</script>";
                            exit;
                        } else {
                            $sql = "INSERT INTO cart (userID, inventoryID, quantity) VALUES (?, ?, ?)";
                            $stmt = $con->prepare($sql);
                            $stmt->bind_param("iii", $userId, $inventoryID, $quantity);
                            $success = $stmt->execute();
                            $stmt->close();

                            echo "<script>window.location='cart.php?success=1';</script>";
                            exit;
                        }
                    } else {
                        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'warning', title: 'Unavailable', text: 'Selected size is currently unavailable.', confirmButtonColor: '#2C2825' }); });</script>";
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
        <form action="" method="POST" class="box">
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
                        <div class="info-header" style="text-align: left; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                            <p style="margin: 0; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: #A09486;">Product Name</p>
                            <h1 style="margin: 5px 0 0 0; font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #2C2825;"><?php echo $name;?></h1>
                        </div>
                        <div class="info-body" style="flex: 1; display: flex; flex-direction: column; gap: 20px;">
                            <div class="info-content">
                                <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #A09486; margin-bottom: 8px;">Description:</p>
                                <p style="font-size: 0.9rem; line-height: 1.5; color: #555;"><?php echo $description;?></p>
                            </div>    
                            <div class="info-numbers" style="margin-top: auto;">
                                <div class="info-price">
                                    <p style="margin: 0; font-size: 0.75rem; color: #A09486;">PRICE</p>
                                    <p style="margin: 0; font-size: 1.5rem; font-weight: 600; color: #2C2825;"><span style="font-size: 0.9rem; font-weight: 400; margin-right: 4px;">PHP</span><?php echo number_format($price, 2);?></p>
                                </div>
                                <div class="info-quantity">
                                    <p style="margin: 0; color: #A09486; font-size: 0.75rem; text-transform: uppercase;">Qty: </p>
                                    <input type="number" name="quantity" value="1" min="1" max="999" style="width: 50px; padding: 8px; border: 1px solid #E8DED2; border-radius: 4px;" class="product-quantity">
                                </div>
                            </div>
                            <div class="info-size">
                                <p style="margin: 0 0 8px 0; color: #A09486; font-size: 0.75rem; text-transform: uppercase;">Select Size: </p>
                                <select name="size" required style="width: 100%; padding: 12px; border: 1px solid #E8DED2; border-radius: 6px; font-family: 'Jost', sans-serif; background-color: #fff; cursor: pointer; font-size: 0.9rem;">
                                    <option value="" disabled selected>Choose a size</option>
                                    <?php
                                    $size_query = $con->prepare("SELECT s.sizeID, s.sizes, i.stock FROM inventory i JOIN sizes s ON i.sizeID = s.sizeID WHERE i.proID = ? AND i.stock > 0");
                                    $size_query->bind_param("i", $id);
                                    $size_query->execute();
                                    $size_result = $size_query->get_result();
                                    while($size_row = $size_result->fetch_assoc()){
                                        echo "<option value='".$size_row['sizeID']."'>".$size_row['sizes']." (".$size_row['stock']." available)</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="info-btn">
                                <button style="width: 100%; padding: 16px; font-family: 'Jost', sans-serif; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; background-color: #2C2825; color: #F7F3EE; border: none; cursor: pointer; transition: background-color 0.3s ease;" type="submit" onmouseover="this.style.backgroundColor='#8B6F56'" onmouseout="this.style.backgroundColor='#2C2825'">Add To Cart</button>
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
