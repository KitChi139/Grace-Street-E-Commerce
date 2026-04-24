<?php
session_start();
include 'components/connect.php';

$orderID = $_GET['order_id'] ?? null;

if (!$orderID) {
    die("Invalid order.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
</head>
<body>

<h2>Payment received!</h2>
<p>Your order #<?php echo htmlspecialchars($orderID); ?> is being processed.</p>

</body>
</html>