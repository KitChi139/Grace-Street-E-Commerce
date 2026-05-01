<?php
session_start();
include 'components/connect.php';

$orderID = $_GET['order_id'] ?? null;
$userId = $_SESSION['user-id'];

if (!$orderID) {
    die("Invalid order.");
}
$status = "PAID";
$stmt = $con ->prepare("UPDATE orders set status = ? where orderID = ? ");
$stmt->bind_param("si", $status, $orderID);
$stmt->execute();

$stmt = $con->prepare("DELETE FROM cart WHERE userID = ?");
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
$successMessage = "Order placed successfully!";
$con->commit();
} else {
$con->rollback();
throw new Exception("Error deleting cart items: " . $stmt->error);
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <?php
        // Read redirect seconds from query param, with limits
        $redirect_seconds = isset($_GET['redirect_seconds']) ? intval($_GET['redirect_seconds']) : 5;
        if ($redirect_seconds < 1) $redirect_seconds = 1;
        if ($redirect_seconds > 300) $redirect_seconds = 300; // cap at 5 minutes
    ?>
    <script>
        // Redirect to main menu after dynamic seconds
        setTimeout(function(){
            window.location.href = 'home.php';
        }, <?php echo ($redirect_seconds * 1000); ?>);
        // Immediate redirect helper
        function goHome() {
            window.location.href = 'home.php';
        }
    </script>
</head>
<body>

<h2>Payment received!</h2>
<p>Your order #<?php echo htmlspecialchars($orderID); ?> is being processed.</p>
<p>You will be redirected back to the main menu in <?php echo $redirect_seconds; ?> second<?php echo ($redirect_seconds == 1 ? '' : 's'); ?> — or click the button to go now.</p>
<button onclick="goHome()">Go to Main Menu</button>

</body>
</html>