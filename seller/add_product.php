<?php
session_start();
include('../components/connect.php');
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con->begin_transaction();
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
        $product_gender = (int)$_POST['product_gender'];
        $current_date = date('Y-m-d H:i:s');
        $sellerID = $_SESSION['user-id'];

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


        // Save file (THIS replaces BLOB completely)
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid("prod_", true) . "." . $ext;

        $uploadPath = "../uploads/images/" . $newName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Failed to upload image.");
        }

        // =========================
        // DB INSERT (BLOB SAFE)
        // =========================

        // $stmt = $con->prepare("SELECT genderID FROM gender WHERE genderID = ?");
        // $stmt->bind_param("s", $product_gender);
        // $stmt->execute();
        // $res = $stmt->get_result();
        // $row = $res->fetch_assoc();
        // $genderID = $row['genderID'];
        // if (!$row) {
        //     die("Invalid genderID selected.");
        // }

        // $genderID = $row['genderID'];
        // $stmt->close();
        $stmt = $con->prepare("
            INSERT INTO product 
            (sellerID, name, image, price, status, description, genderID) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $image = "";

        $stmt->bind_param(
            "issdssi",
            $sellerID,
            $name,
            $newName,
            $price,
            $status,
            $product_description,
            $product_gender
        );

        $stmt->execute();
        $proID = $stmt->insert_id;
        $stmt->close();


        $sizeMap = [];

        $sizeQuery = $con->query("SELECT sizeID, sizes FROM sizes");
        while ($row = $sizeQuery->fetch_assoc()) {
            $sizeMap[$row['sizes']] = $row['sizeID'];
        }

        // Insert into inventory for each size
        $stocks = [
            'S' => $product_stock_small,
            'M' => $product_stock_medium,
            'L' => $product_stock_large,
            'XL' => $product_stock_xlarge,
            'XXL' => $product_stock_xxlarge
        ];
        $invStmt = $con->prepare("
            INSERT INTO inventory (proID, sizeID, stock, status)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($stocks as $sizeName => $stockAmount) {
            if (!isset($sizeMap[$sizeName])) continue;
            // Get sizeID

            $sizeID = $sizeMap[$sizeName];
            $invStatus = ($stockAmount > 0) ? 'In Stock' : 'Empty';
            
            $invStmt->bind_param("iiis", $proID, $sizeID, $stockAmount, $invStatus);
            $invStmt->execute();

        }
        $invStmt->close();
        $_SESSION['product_added'] = true;
        $con->commit();
        header("Location: products.php");
        exit();
    }
} catch(Exception $e) {
    echo "DB Error: " . $stmt->error;
    $con->rollback();
}
?>
