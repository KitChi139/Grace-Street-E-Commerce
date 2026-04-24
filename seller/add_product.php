<?php
session_start();
include('../components/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =========================
    // INPUTS
    // =========================
    $name = trim($_POST['name']);
    $product_stock_small = (int)$_POST['small_stock'];
    $product_stock_medium = (int)$_POST['medium_stock'];
    $product_stock_large = (int)$_POST['large_stock'];
    $product_stock_xlarge = (int)$_POST['xlarge_stock'];
    $product_stock_xxlarge = (int)$_POST['xxlarge_stock'];
    $price = (float)$_POST['price'];
    $status = trim($_POST['status']);
    $product_description = trim($_POST['Description']);
    $product_gender = trim($_POST['product_gender']);
    $current_date = date('Y-m-d H:i:s');

    // =========================
    // FILE VALIDATION
    // =========================
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024;

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        die("File upload error.");
    }

    $file = $_FILES['image'];

    // ✔ Secure MIME check
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type.");
    }

    // ✔ Size check
    if ($file['size'] > $max_size) {
        die("File too large.");
    }

    // ✔ Real image check
    if (!getimagesize($file['tmp_name'])) {
        die("Not a valid image.");
    }

    // ✔ Read binary
    $imageData = file_get_contents($file['tmp_name']);

    // =========================
    // DB INSERT (BLOB SAFE)
    // =========================
    $stmt = $con->prepare("
        INSERT INTO product 
        (image, name, price, status, description, gender, Date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $null = NULL;

    $stmt->bind_param(
        "bsdssss",
        $null,
        $name,
        $price,
        $status,
        $product_description,
        $product_gender,
        $current_date
    );

    // ✔ Send blob properly
    $stmt->send_long_data(0, $imageData);

    if ($stmt->execute()) {
        $proID = $stmt->insert_id;
        $stmt->close();

        // Insert into inventory for each size
        $stocks = [
            'S' => $product_stock_small,
            'M' => $product_stock_medium,
            'L' => $product_stock_large,
            'XL' => $product_stock_xlarge,
            'XXL' => $product_stock_xxlarge
        ];

        foreach ($stocks as $sizeName => $stockAmount) {
            // Get sizeID
            $sizeStmt = $con->prepare("SELECT sizeID FROM sizes WHERE sizes = ?");
            $sizeStmt->bind_param("s", $sizeName);
            $sizeStmt->execute();
            $sizeRes = $sizeStmt->get_result();
            if ($sizeRow = $sizeRes->fetch_assoc()) {
                $sizeID = $sizeRow['sizeID'];
                $invStatus = ($stockAmount > 0) ? 'In Stock' : 'Empty';
                $invStmt = $con->prepare("INSERT INTO inventory (proID, sizeID, stock, status) VALUES (?, ?, ?, ?)");
                $invStmt->bind_param("iiis", $proID, $sizeID, $stockAmount, $invStatus);
                $invStmt->execute();
                $invStmt->close();
            }
            $sizeStmt->close();
        }

        $_SESSION['product_added'] = true;
        header("Location: products.php");
        exit();
    } else {
        echo "DB Error: " . $stmt->error;
    }
}
?>
