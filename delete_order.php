<?php
include('./components/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["order_id"])) {
        $orderId = mysqli_real_escape_string($con, $_POST["order_id"]);

        $sql = "DELETE FROM orders WHERE ID = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $orderId);

        if ($stmt->execute()) {
            echo "Order removed successfully";
        } else {
            echo "Error removing order: " . $stmt->error;
        }
    } else {
        echo "Order ID is required";
    }
} else {
    echo "Only POST requests are allowed";
}
?>
