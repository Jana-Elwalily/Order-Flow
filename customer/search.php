<?php
header('Content-Type: application/json');
session_start();

require_once 'order_fn.php';

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$type  = $_GET['type']  ?? '';
$value = $_GET['value'] ?? '';

if (!$type || !$value) {
    echo json_encode(['success' => false, 'message' => 'Missing search type or value']);
    exit;
}

echo json_encode(searchProducts($type, $value));
?>
