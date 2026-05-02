<?php
declare(strict_types=1);

include __DIR__ . '/components/connect.php';
include __DIR__ . '/components/courier_auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$auth = courier_auth_check();
if (!$auth['ok']) {
    http_response_code($auth['msg'] === 'Unauthorized' ? 401 : 403);
    echo json_encode(['success' => false, 'error' => $auth['msg']]);
    exit;
}

$mainOrderId = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;
if ($mainOrderId === false || $mainOrderId < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid order id']);
    exit;
}

$courierId = $auth['user_id'];

$stmt = $con->prepare(
    "UPDATE main_order 
     SET status = 'In Transit', courierID = ? 
     WHERE mainOrderID = ? AND status = 'Ready'"
);
$stmt->bind_param('ii', $courierId, $mainOrderId);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

if ($stmt->affected_rows < 1) {
    echo json_encode(['success' => false, 'error' => 'Order not available for pickup (must be Ready for Pickup).']);
    exit;
}

echo json_encode(['success' => true]);
