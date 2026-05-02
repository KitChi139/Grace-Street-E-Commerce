<?php
include('../components/connect.php');
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false]);
    exit;
}

$stmt = $con->prepare("UPDATE main_order SET status = 'Ready' WHERE mainOrderID = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}