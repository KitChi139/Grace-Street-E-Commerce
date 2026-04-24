<?php
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

// Log the payload for debugging (remove in production)
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . $payload . "\n", FILE_APPEND);

if ($data && isset($data['data']['attributes']['type']) && $data['data']['attributes']['type'] == 'checkout_session.payment.paid') {

    $orderID = $data['data']['attributes']['metadata']['order_id'] ?? null;

    if ($orderID) {
        include 'components/connect.php';

        // 1. Mark order as PAID
        $stmt = $con->prepare("UPDATE orders SET status='Paid' WHERE orderID=?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        // 2. Deduct inventory
        $stmt = $con->prepare("SELECT * FROM order_items WHERE orderID=?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $inventoryID = $row['inventoryID'];
            $qty = $row['quantity'];

            $update = $con->prepare("UPDATE inventory SET stock = stock - ? WHERE inventoryID=?");
            $update->bind_param("ii", $qty, $inventoryID);
            $update->execute();
        }
    }
}

// Respond to PayMongo
http_response_code(200);
echo "OK";
?>