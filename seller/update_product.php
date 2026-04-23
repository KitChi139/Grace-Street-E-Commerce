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
    $productStock = $_POST['product_stock'];
    $productPrice = $_POST['product_price'];
    $product_description = $_POST['Description'];
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
    $updateQuery = "UPDATE product_list SET product_name = '$productName', product_stock = '$productStock', product_price = '$productPrice', product_image = '$imageName',description = '$product_description' , gender = '$productGender' WHERE id = $productId";
    
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
                                <label for="total_stock">Total Stock</label>
                                <input type="number" id="total_stock" name="product_stock" value="<?php echo $productData['product_stock']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="product_price">Product Price</label>
                                <input type="number" id="product_price" name="product_price" value="<?php echo $productData['product_price']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="product_image">Product Image</label>
                                <input type="file" id="product_image" name="product_image" readonly>
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
                                <input type="text" id="Description" name="Description" maxlength="100">
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
            // Redirect to products.php when the button is clicked
            window.location.href = 'products.php';
        });
    </script>
</body>
</html>
