<?php
// Include necessary files and connect to the database
include('../components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if ID is provided in the URL
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = (int)$_GET['id'];

    $stmt = $con->prepare("SELECT p.*, 
                        MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s,
                        MAX(CASE WHEN s.sizes = 'M' THEN i.stock ELSE 0 END) AS product_stock_m,
                        MAX(CASE WHEN s.sizes = 'L' THEN i.stock ELSE 0 END) AS product_stock_l,
                        MAX(CASE WHEN s.sizes = 'XL' THEN i.stock ELSE 0 END) AS product_stock_xl,
                        MAX(CASE WHEN s.sizes = 'XXL' THEN i.stock ELSE 0 END) AS product_stock_xxl
                    FROM product p
                    LEFT JOIN inventory i ON p.proID = i.proID
                    LEFT JOIN sizes s ON i.sizeID = s.sizeID
                    WHERE p.proID = ?
                    GROUP BY p.proID");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $productData = $productResult->fetch_assoc();
    $stmt->close();
} else {
    header("Location: products.php");
    exit();
}

if(isset($_POST['productId'])) {
    $productId = (int)$_POST['productId'];
    $productName = mysqli_real_escape_string($con, $_POST['name']);
    $productPrice = (float)$_POST['price'];
    $product_description = mysqli_real_escape_string($con, $_POST['Description']);
    $productGender = (int)$_POST['product_gender'];

    $adjustments = [
        'S' => isset($_POST['product_adjust_stock_s']) ? (int)$_POST['product_adjust_stock_s'] : 0,
        'M' => isset($_POST['product_adjust_stock_m']) ? (int)$_POST['product_adjust_stock_m'] : 0,
        'L' => isset($_POST['product_adjust_stock_l']) ? (int)$_POST['product_adjust_stock_l'] : 0,
        'XL' => isset($_POST['product_adjust_stock_xl']) ? (int)$_POST['product_adjust_stock_xl'] : 0,
        'XXL' => isset($_POST['product_adjust_stock_xxl']) ? (int)$_POST['product_adjust_stock_xxl'] : 0
    ];

    // Retrieve existing image name from the database
    $stmt = $con->prepare("SELECT image FROM product WHERE proID = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $productData = $productResult->fetch_assoc();
    $imageName = $productData['image'];
    $stmt->close();

    // Check if a new image is uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        
        // Move uploaded image to the desired location
        $uploadDirectory = "../uploads/images/";
        $imagePath = $uploadDirectory . $imageName;
        move_uploaded_file($imageTmpName, $imagePath);
    }

    // Update product information in the database
    $updateStmt = $con->prepare("UPDATE product SET name = ?, price = ?, image = ?, description = ?, gender = ? WHERE proID = ?");
    $updateStmt->bind_param("sdisii", $productName, $productPrice, $imageName, $product_description, $productGender, $productId);
    
    if($updateStmt->execute()) {
        // Update inventory for each size using stock adjustment (current + change)
        $stockErrors = [];
        $stockChanges = [];
        foreach ($adjustments as $sizeName => $adjustValue) {
            // Get sizeID
            $sizeStmt = $con->prepare("SELECT sizeID FROM sizes WHERE sizes = ?");
            $sizeStmt->bind_param("s", $sizeName);
            $sizeStmt->execute();
            $sizeRes = $sizeStmt->get_result();
            if ($sizeRow = $sizeRes->fetch_assoc()) {
                $sizeID = $sizeRow['sizeID'];
                
                // Check if entry exists
                $checkInv = $con->prepare("SELECT inventoryID, stock FROM inventory WHERE proID = ? AND sizeID = ?");
                $checkInv->bind_param("ii", $productId, $sizeID);
                $checkInv->execute();
                $checkRes = $checkInv->get_result();
                
                if ($checkRes->num_rows > 0) {
                    $inventoryData = $checkRes->fetch_assoc();
                    $currentStock = (int)$inventoryData['stock'];
                    $newStock = $currentStock + $adjustValue;

                    if ($newStock < 0) {
                        $stockErrors[] = "Size $sizeName cannot go below 0 (current: $currentStock, adjustment: $adjustValue).";
                        $checkInv->close();
                        $sizeStmt->close();
                        continue;
                    }

                    $invStatus = ($newStock > 0) ? 'In Stock' : 'Empty';
                    $invStmt = $con->prepare("UPDATE inventory SET stock = ?, status = ? WHERE proID = ? AND sizeID = ?");
                    $invStmt->bind_param("isii", $newStock, $invStatus, $productId, $sizeID);
                    $stockChanges[] = "Size $sizeName: $currentStock " . ($adjustValue >= 0 ? "+$adjustValue" : "$adjustValue") . " = $newStock";
                } else {
                    $currentStock = 0;
                    $newStock = $currentStock + $adjustValue;

                    if ($newStock < 0) {
                        $stockErrors[] = "Size $sizeName cannot go below 0 (current: 0, adjustment: $adjustValue).";
                        $checkInv->close();
                        $sizeStmt->close();
                        continue;
                    }

                    $invStatus = ($newStock > 0) ? 'In Stock' : 'Empty';
                    $invStmt = $con->prepare("INSERT INTO inventory (proID, sizeID, stock, status) VALUES (?, ?, ?, ?)");
                    $invStmt->bind_param("iiis", $productId, $sizeID, $newStock, $invStatus);
                    $stockChanges[] = "Size $sizeName: 0 " . ($adjustValue >= 0 ? "+$adjustValue" : "$adjustValue") . " = $newStock";
                }
                $invStmt->execute();
                $invStmt->close();
                $checkInv->close();
            }
            $sizeStmt->close();
        }

        if (!empty($stockErrors)) {
            $errorText = implode("\\n", $stockErrors);
            echo "<script>alert('Stock update failed:\\n$errorText'); window.location.href='update_product.php?id=$productId';</script>";
            exit();
        }

        if (!empty($stockChanges) && isset($_SESSION['user-id'])) {
            include_once '../components/audit_logger.php';
            $logMessage = "Inventory adjusted for Product ID $productId | " . implode(" | ", $stockChanges);
            log_audit('Inventory Update', $_SESSION['user-id'], $logMessage, 'Info');
        }

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
                                <label for="name">Product Name</label>
                                <input type="text" id="name" name="name" value="<?php echo $productData['name']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="current_stock_s">Current Stock (S)</label>
                                <input type="number" id="current_stock_s" value="<?php echo (int)$productData['product_stock_s']; ?>" readonly>
                                <label for="adjust_stock_s">Add/Remove Quantity (S)</label>
                                <input type="number" id="adjust_stock_s" name="product_adjust_stock_s" value="0" step="1" required>
                                <small id="preview_stock_s">Preview: <?php echo (int)$productData['product_stock_s']; ?> + 0 = <?php echo (int)$productData['product_stock_s']; ?></small>
                            </div>
                            <div class="product_update_1">
                                <label for="current_stock_m">Current Stock (M)</label>
                                <input type="number" id="current_stock_m" value="<?php echo (int)$productData['product_stock_m']; ?>" readonly>
                                <label for="adjust_stock_m">Add/Remove Quantity (M)</label>
                                <input type="number" id="adjust_stock_m" name="product_adjust_stock_m" value="0" step="1" required>
                                <small id="preview_stock_m">Preview: <?php echo (int)$productData['product_stock_m']; ?> + 0 = <?php echo (int)$productData['product_stock_m']; ?></small>
                            </div>
                            <div class="product_update_1">
                                <label for="current_stock_l">Current Stock (L)</label>
                                <input type="number" id="current_stock_l" value="<?php echo (int)$productData['product_stock_l']; ?>" readonly>
                                <label for="adjust_stock_l">Add/Remove Quantity (L)</label>
                                <input type="number" id="adjust_stock_l" name="product_adjust_stock_l" value="0" step="1" required>
                                <small id="preview_stock_l">Preview: <?php echo (int)$productData['product_stock_l']; ?> + 0 = <?php echo (int)$productData['product_stock_l']; ?></small>
                            </div>
                            <div class="product_update_1">
                                <label for="current_stock_xl">Current Stock (XL)</label>
                                <input type="number" id="current_stock_xl" value="<?php echo (int)$productData['product_stock_xl']; ?>" readonly>
                                <label for="adjust_stock_xl">Add/Remove Quantity (XL)</label>
                                <input type="number" id="adjust_stock_xl" name="product_adjust_stock_xl" value="0" step="1" required>
                                <small id="preview_stock_xl">Preview: <?php echo (int)$productData['product_stock_xl']; ?> + 0 = <?php echo (int)$productData['product_stock_xl']; ?></small>
                            </div>
                            <div class="product_update_1">
                                <label for="current_stock_xxl">Current Stock (XXL)</label>
                                <input type="number" id="current_stock_xxl" value="<?php echo (int)$productData['product_stock_xxl']; ?>" readonly>
                                <label for="adjust_stock_xxl">Add/Remove Quantity (XXL)</label>
                                <input type="number" id="adjust_stock_xxl" name="product_adjust_stock_xxl" value="0" step="1" required>
                                <small id="preview_stock_xxl">Preview: <?php echo (int)$productData['product_stock_xxl']; ?> + 0 = <?php echo (int)$productData['product_stock_xxl']; ?></small>
                            </div>
                            <div class="product_update_1">
                                <label for="price">Product Price</label>
                                <input type="number" id="price" name="price" value="<?php echo $productData['price']; ?>" required>
                            </div>
                            <div class="product_update_1">
                                <label for="image">Product Image</label>
                                <input type="file" id="image" name="image" readonly>
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
            const sizeKeys = ['s', 'm', 'l', 'xl', 'xxl'];
            for (const sizeKey of sizeKeys) {
                const currentStockInput = document.getElementById(`current_stock_${sizeKey}`);
                const adjustStockInput = document.getElementById(`adjust_stock_${sizeKey}`);
                const currentValue = parseInt(currentStockInput.value, 10) || 0;
                const adjustValue = parseInt(adjustStockInput.value, 10) || 0;
                const nextValue = currentValue + adjustValue;

                if (nextValue < 0) {
                    event.preventDefault();
                    alert(`Stock cannot go below 0 for size ${sizeKey.toUpperCase()}.`);
                    return;
                }
            }
            alert('Product Updated');
        });
        document.getElementById('closeProductBtn').addEventListener('click', function(event) {
            // Redirect to products.php when the button is clicked
            window.location.href = 'products.php';
        });

        function bindStockPreview(sizeKey) {
            const currentStockInput = document.getElementById(`current_stock_${sizeKey}`);
            const adjustStockInput = document.getElementById(`adjust_stock_${sizeKey}`);
            const preview = document.getElementById(`preview_stock_${sizeKey}`);

            const renderPreview = () => {
                const currentValue = parseInt(currentStockInput.value, 10) || 0;
                const adjustValue = parseInt(adjustStockInput.value, 10) || 0;
                const nextValue = currentValue + adjustValue;
                const adjustText = adjustValue >= 0 ? `+ ${adjustValue}` : `- ${Math.abs(adjustValue)}`;
                preview.textContent = `Preview: ${currentValue} ${adjustText} = ${nextValue}`;
                preview.style.color = nextValue < 0 ? '#d93025' : '#218838';
            };

            adjustStockInput.addEventListener('input', renderPreview);
            renderPreview();
        }

        ['s', 'm', 'l', 'xl', 'xxl'].forEach(bindStockPreview);
    </script>
</body>
</html>
