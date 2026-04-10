<?php
include 'components/connect.php';

if(isset($_POST['productId']) && isset($_POST['quantity'])) {
    $productId = mysqli_real_escape_string($con, $_POST['productId']);
    $quantity = mysqli_real_escape_string($con, $_POST['quantity']);

    $query = "UPDATE cart SET Product_Quantity = '$quantity' WHERE ID = '$productId'";
    $result = mysqli_query($con, $query);

    if($result) {
        echo "<script>
        window.location.href = 'cart.php';
        </script>";
    } else {
        // Debug: Output the SQL query and any MySQL error
        echo "Error updating quantity: " . mysqli_error($con);
        echo "SQL Query: " . $query;
    }
} else {
    echo "ProductId or quantity not provided";
}
?>
