<?php
header('Content-Type: application/json');
require_once 'order_fn.php';
$product_id = $_GET['product_id'] ?? '';
if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'product id is required']);
    exit;
}

echo json_encode(getProductDetails($product_id));
?>
