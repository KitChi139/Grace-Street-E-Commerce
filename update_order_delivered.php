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

if (!isset($_FILES['proof']) || !is_uploaded_file($_FILES['proof']['tmp_name'])) {
    echo json_encode(['success' => false, 'error' => 'Proof of delivery image is required.']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['proof']['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
if (!isset($allowed[$mime])) {
    echo json_encode(['success' => false, 'error' => 'Invalid image type. Use JPEG, PNG, or WebP.']);
    exit;
}

$maxBytes = 5 * 1024 * 1024;
if ($_FILES['proof']['size'] > $maxBytes) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5MB).']);
    exit;
}

$courierId = $auth['user_id'];

$check = $con->prepare(
    "SELECT mainOrderID FROM main_order 
     WHERE mainOrderID = ? AND status = 'In Transit' AND courierID = ?"
);
$check->bind_param('ii', $mainOrderId, $courierId);
$check->execute();
$res = $check->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order must be On Route and assigned to you.']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/proofs';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        echo json_encode(['success' => false, 'error' => 'Could not create upload directory.']);
        exit;
    }
}

$ext = $allowed[$mime];
$basename = 'pod_' . $mainOrderId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$destFs = $uploadDir . DIRECTORY_SEPARATOR . $basename;
if (!move_uploaded_file($_FILES['proof']['tmp_name'], $destFs)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save upload.']);
    exit;
}

$relativePath = 'uploads/proofs/' . $basename;

$upd = $con->prepare(
    "UPDATE main_order 
     SET status = 'Completed', delivery_proof_image = ? 
     WHERE mainOrderID = ? AND status = 'In Transit' AND courierID = ?"
);
$upd->bind_param('sii', $relativePath, $mainOrderId, $courierId);

if (!$upd->execute() || $upd->affected_rows < 1) {
    @unlink($destFs);
    echo json_encode(['success' => false, 'error' => 'Could not update order.']);
    exit;
}

echo json_encode(['success' => true, 'proof_path' => $relativePath]);
