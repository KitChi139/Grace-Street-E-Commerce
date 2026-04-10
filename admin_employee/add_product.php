<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Correcting undefined index error for product_stock
    $product_name = $_POST['product_name'];
    $product_stock = isset($_POST['product_stock']) ? $_POST['product_stock'] : '';
    $product_image = $_FILES['product_image']['name'];
    $product_price = $_POST['product_price'];
    $product_status = $_POST['product_status'];
    $product_description = $_POST['Description'];
    $product_gender = $_POST['product_gender'];
    // Get the current date and time
    $current_date = date('Y-m-d H:i:s');

    // Upload the image file to a folder on your server
    $target_dir = "../uploads/images/"; // Corrected the target directory path
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
    move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file);

    // Insert the values into the database, including the current date
    $sql = "INSERT INTO product_list (product_image, product_name, product_stock, product_price, product_status, description, gender, Date) 
            VALUES ('$product_image', '$product_name', '$product_stock', '$product_price', '$product_status', '$product_description' , '$product_gender', '$current_date')";

    if (mysqli_query($con, $sql)) {
        $_SESSION['product_added'] = true; // Set session variable
        header("Location: products.php"); // Redirect to products page after successful insertion
        exit(); // Stop further execution of the script
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
}

?>
