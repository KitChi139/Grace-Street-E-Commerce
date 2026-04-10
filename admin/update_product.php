<?php
// Include necessary files and connect to the database
include('../components/connect.php');

// Check if ID is provided in the URL
if(isset($_GET['id'])) {
    $productId = $_GET['id'];

    $productQuery = "SELECT * FROM product_list WHERE id = $productId";
    $productResult = mysqli_query($con, $productQuery);
    $productData = mysqli_fetch_assoc($productResult);
}

if(isset($_POST['productId'])) {
    $productId = $_POST['productId'];
    $productName = $_POST['product_name'];
    $productStockS = $_POST['product_stock_s'];
    $productStockM = $_POST['product_stock_m'];
    $productStockL = $_POST['product_stock_l'];
    $productStockXL = $_POST['product_stock_xl'];
    $productStockXXL = $_POST['product_stock_xxl'];
    $productPrice = $_POST['product_price'];
    $productDescription = $_POST['Description'];
    $productGender = $_POST['product_gender'];

    // Retrieve existing image name from the database
    $productQuery = "SELECT product_image FROM product_list WHERE id = $productId";
    $productResult = mysqli_query($con, $productQuery);
    $productData = mysqli_fetch_assoc($productResult);
    $imageName = $productData['product_image'];

    // Check if a new image is uploaded
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['product_image']['tmp_name'];
        $imageName = $_FILES['product_image']['name'];
        
        // Move uploaded image to the desired location
        $uploadDirectory = "../uploads/images/";
        $imagePath = $uploadDirectory . $imageName;
        move_uploaded_file($imageTmpName, $imagePath);
    }

    // Update product information in the database
    $updateQuery = "UPDATE product_list SET 
                    product_name = '$productName', 
                    product_stock_s = '$productStockS', 
                    product_stock_m = '$productStockM', 
                    product_stock_l = '$productStockL', 
                    product_stock_xl = '$productStockXL', 
                    product_stock_xxl = '$productStockXXL', 
                    product_price = '$productPrice', 
                    product_image = '$imageName', 
                    description = '$productDescription', 
                    gender = '$productGender' 
                    WHERE id = $productId";
    
    if(mysqli_query($con, $updateQuery)) {
        // Product updated successfully
        header("Location: products.php"); // Redirect back to products page
        exit();
    } else {
        // Error occurred while updating product
        echo "Error: " . mysqli_error($con);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin/styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Update Product</title>
</head>
<body>
    <div class="update_product_container">
        <div class="update_product_box">
            <div class="update_text-box">
                <h1>UPDATE PRODUCT</h1>
                <div class="products_add_icon">
                    <i class="fa-solid fa-circle-xmark" id="closeProductBtn"></i>
                </div>
            </div>
            <div>
                <form id="updateForm" action="update_product.php" method="POST" enctype="multipart/form-data">
                    <div class="update_this_box">
                        <div class="update_columns">
                            <div class="product_update_1">
                                <label for="product_name">Product Name</label>
                                <input type="text" id="product_name" name="product_name" value="<?php echo $productData['product_name']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="total_stock_s">Stock (S)</label>
                                <input type="number" id="total_stock_s" name="product_stock_s" value="<?php echo $productData['product_stock_s']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="total_stock_m">Stock (M)</label>
                                <input type="number" id="total_stock_m" name="product_stock_m" value="<?php echo $productData['product_stock_m']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="total_stock_l">Stock (L)</label>
                                <input type="number" id="total_stock_l" name="product_stock_l" value="<?php echo $productData['product_stock_l']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="total_stock_xl">Stock (XL)</label>
                                <input type="number" id="total_stock_xl" name="product_stock_xl" value="<?php echo $productData['product_stock_xl']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="total_stock_xxl">Stock (XXL)</label>
                                <input type="number" id="total_stock_xxl" name="product_stock_xxl" value="<?php echo $productData['product_stock_xxl']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="product_price">Product Price</label>
                                <input type="number" id="product_price" name="product_price" value="<?php echo $productData['product_price']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="product_image">Product Image</label>
                                <input type="file" id="product_image" name="product_image">
                            </div>
                            <div class="product_update_1">
                                <label for="product_gender">Gender</label>
                                <select id="product_gender" name="product_gender" required>
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Mens" <?php echo ($productData['gender'] === 'Mens') ? 'selected' : ''; ?>>Mens</option>
                                    <option value="Womens" <?php echo ($productData['gender'] === 'Womens') ? 'selected' : ''; ?>>Womens</option>
                                </select>
                            </div>
                            <div class="product_update_1">
                                <label for="Description">Description</label>
                                <input type="text" id="Description" name="Description" value="<?php echo $productData['description']; ?>" maxlength="100">
                            </div>
                        </div>
                        <div class="update_btn">
                            <input type="hidden" name="productId" value="<?php echo $productId; ?>">
                            <button type="submit" id="updateButton">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('updateForm').addEventListener('submit', function(event) {
            alert('Product Updated');
        });
        document.getElementById('closeProductBtn').addEventListener('click', function(event) {
            window.location.href = 'products.php';
        });
    </script>
</body>
</html>

<style>
    .update_product_container {
        padding: 20px;
        margin: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .update_product_box {
        max-width: 600px;
        margin: auto;
    }
    .update_text-box h1 {
        text-align: center;
        margin-bottom: 20px;
    }
    .products_add_icon {
        position: absolute;
        top: 20px;
        right: 20px;
        cursor: pointer;
    }
    .update_columns {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    .product_update_1 label {
        display: block;
        margin-bottom: 5px;
    }
    .product_update_1 input, .product_update_1 select {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .update_btn {
        text-align: center;
        margin-top: 20px;
    }
    .update_btn button {
        padding: 10px 20px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .update_btn button:hover {
        background-color: #218838;
    }
</style>
