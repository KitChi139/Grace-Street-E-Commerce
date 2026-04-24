<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correcting undefined index error for product_stock
    $name = $_POST['name'];
    $product_stock_s = isset($_POST['product_stock_s']) ? $_POST['product_stock_s'] : '';
    $product_stock_m = isset($_POST['product_stock_m']) ? $_POST['product_stock_m'] : '';
    $product_stock_l = isset($_POST['product_stock_l']) ? $_POST['product_stock_l'] : '';
    $product_stock_xl = isset($_POST['product_stock_xl']) ? $_POST['product_stock_xl'] : '';
    $product_stock_xxl = isset($_POST['product_stock_xxl']) ? $_POST['product_stock_xxl'] : '';
    $image = $_FILES['image']['name'];
    $price = $_POST['price'];
    $product_discount = $_POST['product_discount'];
    $status = $_POST['status'];
    $product_description = $_POST['Description'];
    $product_gender = $_POST['product_gender'];
    // Get the current date and time
    $current_date = date('Y-m-d H:i:s');

    // Upload the image file to a folder on your server
    $target_dir = "../uploads/images/"; // Corrected the target directory path
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert the values into the database, including the current date
    $sql = "INSERT INTO product (image, name, price, product_discount, status, description, gender, Date) 
            VALUES ('$image', '$name', '$price', '$product_discount', '$status', '$product_description', '$product_gender', '$current_date')";

    if (mysqli_query($con, $sql)) {
        $proID = mysqli_insert_id($con);

        // Insert into inventory for each size
        $stocks = [
            'S' => $product_stock_s,
            'M' => $product_stock_m,
            'L' => $product_stock_l,
            'XL' => $product_stock_xl,
            'XXL' => $product_stock_xxl
        ];

        foreach ($stocks as $sizeName => $stockAmount) {
            $sizeQuery = mysqli_query($con, "SELECT sizeID FROM sizes WHERE sizes = '$sizeName'");
            if ($sizeRow = mysqli_fetch_assoc($sizeQuery)) {
                $sizeID = $sizeRow['sizeID'];
                $invStatus = ($stockAmount > 0) ? 'In Stock' : 'Empty';
                mysqli_query($con, "INSERT INTO inventory (proID, sizeID, stock, status) VALUES ('$proID', '$sizeID', '$stockAmount', '$invStatus')");
            }
        }

        $_SESSION['product_added'] = true; // Set session variable
        header("Location: products.php"); // Redirect to products page after successful insertion
        exit(); // Stop further execution of the script
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
}

?>
