<?php
include('./components/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["order_id"]) && isset($_POST["new_status"])) {
        $orderId = mysqli_real_escape_string($con, $_POST["order_id"]);
        $newStatus = mysqli_real_escape_string($con, $_POST["new_status"]);

        $sql = "UPDATE orders SET Order_Status = ? WHERE ID = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $newStatus, $orderId);

        if ($stmt->execute()) {
            echo "Order status updated successfully";
        } else {
            echo "Error updating order status: " . $stmt->error;
        }
    } else {
        echo "Order ID and new status are required";
    }
} else {
    echo "Only POST requests are allowed";
}
?>
