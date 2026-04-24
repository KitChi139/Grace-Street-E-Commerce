<?php
session_start();
include('../components/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =========================
    // INPUTS
    // =========================
    $product_name = trim($_POST['product_name']);
    $product_stock_small = (int)$_POST['small_stock'];
    $product_stock_medium = (int)$_POST['medium_stock'];
    $product_stock_large = (int)$_POST['large_stock'];
    $product_stock_xlarge = (int)$_POST['xlarge_stock'];
    $product_stock_xxlarge = (int)$_POST['xxlarge_stock'];
    $product_price = (float)$_POST['product_price'];
    $product_status = trim($_POST['product_status']);
    $product_description = trim($_POST['Description']);
    $product_gender = trim($_POST['product_gender']);
    $current_date = date('Y-m-d H:i:s');

    // =========================
    // FILE VALIDATION
    // =========================
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024;

    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== 0) {
        die("File upload error.");
    }

    $file = $_FILES['product_image'];

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
        INSERT INTO product_list 
        (product_image, product_name, product_stock_s, product_stock_m, product_stock_l, product_stock_xl, product_stock_xxl, product_price, product_status, description, gender, Date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $null = NULL;

    $stmt->bind_param(
        "bssiiiiidsss",
        $null,
        $product_name,
        $product_stock_small,
        $product_stock_medium,
        $product_stock_large,
        $product_stock_xlarge,
        $product_stock_xxlarge,
        $product_price,
        $product_status,
        $product_description,
        $product_gender,
        $current_date
    );

    // ✔ Send blob properly
    $stmt->send_long_data(0, $imageData);

    if ($stmt->execute()) {
        $_SESSION['product_added'] = true;
        header("Location: products.php");
        exit();
    } else {
        echo "DB Error: " . $stmt->error;
    }
}
?>