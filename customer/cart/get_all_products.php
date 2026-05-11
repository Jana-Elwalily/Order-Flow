<?php
header('Content-Type: application/json');
session_start();
require_once '../order_fn.php';

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

echo json_encode(getAllAvailableProducts());
?>