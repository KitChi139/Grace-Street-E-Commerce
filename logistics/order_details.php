<?php
include('../components/connect.php');
header('Content-Type: application/json');
$mainOrderID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$mainOrderID) {
    echo json_encode(["error" => "Invalid order ID"]);
    exit;
}

$stmt = $con->prepare("
    SELECT 
        mo.mainOrderID,
        mo.total_price,
        mo.status AS main_status,
        gu.first_name,
        gu.last_name

    FROM main_order mo
    JOIN grace_user gu ON mo.userID = gu.userID
    WHERE mo.mainOrderID = ?
");
$stmt->bind_param("i", $mainOrderID);
$stmt->execute();
$main = $stmt->get_result()->fetch_assoc();

$stmt = $con->prepare("
    SELECT 
        o.orderID,
        o.status,
        o.price,
        o.sellerID,
        gu.first_name AS seller_first_name,
        gu.last_name AS seller_last_name
    FROM orders o
    JOIN grace_user gu ON o.sellerID = gu.userID
    WHERE o.mainOrderID = ?
");
$stmt->bind_param("i", $mainOrderID);
$stmt->execute();
$sellerOrders = $stmt->get_result();

$data = [
    "main" => $main,
    "sellers" => []
];

while ($row = $sellerOrders->fetch_assoc()) {
    $orderID = $row['orderID'];

    $stmt2 = $con->prepare("
        SELECT 
            p.name,
            oi.quantity,
            s.sizes
        FROM order_items oi
        JOIN inventory i ON oi.inventoryID = i.inventoryID
        JOIN sizes s ON i.sizeID = s.sizeID
        JOIN product p ON i.proID = p.proID
        WHERE oi.orderID = ?
    ");
    $stmt2->bind_param("i", $orderID);
    $stmt2->execute();
    $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    $row['items'] = $items;
    $data['sellers'][] = $row;
}

echo json_encode($data);