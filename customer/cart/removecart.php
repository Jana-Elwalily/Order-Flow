<?php
header('Content-Type: application/json');
session_start();
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$product_id = $_POST['product_id'] ?? '';

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

echo json_encode(removeFromCart($_SESSION['customer_id'], $product_id));
?>